<?php

namespace App\Http\Controllers;

use App\Enums\CaseStatus;
use App\Enums\CaseType;
use App\Http\Requests\StoreCaseRequest;
use App\Models\Annex;
use App\Models\AuditEvent;
use App\Models\CaseAssignment;
use App\Models\ClinicalCase;
use App\Models\CytologyDiagnosis;
use App\Models\Pathologist;
use App\Models\Patient;
use App\Models\ReportPdf;
use App\Models\Template;
use App\Services\AuditLogger;
use App\Services\CaseCreator;
use App\Services\CaseNumberGenerator;
use App\Services\ReportPdfService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CaseController extends Controller
{
    public function index(Request $request): View
    {
        $query = ClinicalCase::query()
            ->with(['patient:id,full_name,document_number', 'assignments'])
            ->withCount('assignments');

        if ($type = $request->query('tipo')) {
            $query->where('type', $type);
        }
        if ($status = $request->query('estado')) {
            $query->where('status', $status);
        }
        if ($year = $request->query('anio')) {
            $query->where('year_no', $year);
        }

        $query->search($request->query('search'));

        $cases = $query
            ->orderByDesc('received_at')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('cases.index', compact('cases'));
    }

    public function create(Patient $patient, CaseNumberGenerator $numbers): View
    {
        return view('cases.create', [
            'patient' => $patient,
            'nextBiopsy' => $numbers->next(CaseType::Biopsy),
            'nextCytology' => $numbers->next(CaseType::Cytology),
            'today' => now()->toDateString(),
        ]);
    }

    public function store(
        StoreCaseRequest $request,
        Patient $patient,
        CaseCreator $creator,
        AuditLogger $audit
    ): RedirectResponse {
        $type = CaseType::from((string) $request->input('type'));

        $case = $creator->create(
            $patient,
            $type,
            (string) $request->input('case_code'),
            $request->input('received_at') ?: null,
            $request->input('material') ?: null,
            $request->input('clinical_diagnosis') ?: null,
        );

        $audit->log('case.created', $case, [
            'code' => $case->case_code,
            'type' => $case->type->value,
        ]);

        return redirect()
            ->route('cases.edit', $case)
            ->with('success', 'Estudio ' . $case->codeLabel() . ' creado. Complete el informe.');
    }

    public function show(ClinicalCase $case): View
    {
        $case->load(['patient', 'assignments.pathologist', 'histology', 'cytology', 'pdfs']);

        $history = AuditEvent::query()
            ->where('subject_type', ClinicalCase::class)
            ->where('subject_id', $case->id)
            ->with('actor:id,name')
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        return view('cases.show', compact('case', 'history'));
    }

    public function edit(ClinicalCase $case): View
    {
        $case->load(['patient', 'assignments', 'histology', 'cytology']);

        $templates = Template::query()
            ->whereIn('module', [
                Template::MODULE_MACRO,
                Template::MODULE_MICRO,
                Template::MODULE_DIAGNOSIS,
            ])
            ->orderBy('name')
            ->get()
            ->groupBy('module');

        $diagnoses = CytologyDiagnosis::query()->orderBy('sort_order')->orderBy('code')->get();
        $annexes = Annex::query()->orderBy('sort_order')->orderBy('code')->get();
        $pathologists = Pathologist::query()->orderBy('name')->get();

        return view('cases.edit', [
            'case' => $case,
            'templates' => $templates,
            'diagnoses' => $diagnoses,
            'annexes' => $annexes,
            'pathologists' => $pathologists,
            'locked' => $case->status === CaseStatus::Delivered,
        ]);
    }

    public function update(Request $request, ClinicalCase $case, AuditLogger $audit): RedirectResponse
    {
        if ($case->status === CaseStatus::Delivered) {
            return redirect()
                ->route('cases.show', $case)
                ->with('error', 'El informe está emitido y no se puede modificar. Un administrador debe reabrirlo.');
        }

        $isBiopsy = $case->isBiopsy();

        $data = $request->validate([
            'received_at' => ['nullable', 'date'],
            'sent_at' => ['nullable', 'date', 'after_or_equal:received_at'],
            'material' => ['nullable', 'string', 'max:' . ($isBiopsy ? 100 : 250)],
            'clinical_diagnosis' => ['nullable', 'string', 'max:' . ($isBiopsy ? 100 : 60)],
            'lesion_site' => ['nullable', 'string', 'max:100'],
            'macro_description' => ['nullable', 'string', 'max:20000'],
            'micro_description' => ['nullable', 'string', 'max:30000'],
            'micro_diagnosis' => ['nullable', 'string', 'max:20000'],
            'cyto_description' => ['nullable', 'string', 'max:30000'],
            'cyto_diagnosis' => ['nullable', 'string', 'max:20000'],
            'quality' => ['nullable', 'string', 'max:5000'],
            'notes' => ['nullable', 'string', 'max:20000'],
            'superficial' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'intermediate' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'parabasal' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'conclusion' => ['nullable', 'string', 'max:5000'],
            'pathologist_1' => ['nullable', 'string', 'max:100'],
            'pathologist_2' => ['nullable', 'string', 'max:100', Rule::notIn([$request->input('pathologist_1')])],
        ]);

        DB::transaction(function () use ($case, $data, $isBiopsy, $audit) {
            $report = $case->report();

            if (! $report) {
                $report = $isBiopsy
                    ? $case->histology()->create([])
                    : $case->cytology()->create([]);
            }

            $payload = collect($data)->only([
                'material', 'clinical_diagnosis', 'macro_description', 'notes',
            ])->all();

            if ($isBiopsy) {
                $payload = array_merge($payload, collect($data)->only([
                    'lesion_site', 'micro_description', 'micro_diagnosis',
                ])->all());
            } else {
                $payload = array_merge($payload, collect($data)->only([
                    'cyto_description', 'cyto_diagnosis', 'quality',
                    'superficial', 'intermediate', 'parabasal', 'conclusion',
                ])->all());
            }

            $report->fill($payload)->save();

            foreach ([1 => 'pathologist_1', 2 => 'pathologist_2'] as $role => $field) {
                $name = trim((string) ($data[$field] ?? ''));

                if ($name === '') {
                    CaseAssignment::where('case_id', $case->id)->where('role', $role)->delete();
                    continue;
                }

                $pathologist = Pathologist::firstOrCreate(['name' => $name]);

                CaseAssignment::updateOrCreate(
                    ['case_id' => $case->id, 'role' => $role],
                    ['pathologist_id' => $pathologist->id, 'pathologist_name' => $pathologist->name]
                );
            }

            $case->update([
                'received_at' => $data['received_at'] ?? $case->received_at,
                'sent_at' => $data['sent_at'] ?? null,
            ]);

            // Emisión: el informe terminado pasa a Entregado y habilita la
            // impresión. Solo aplica a casos nuevos; el histórico no se revalida.
            if ($case->legacy_source === null) {
                $case->refresh();
                $wasDelivered = $case->status === CaseStatus::Delivered;

                if ($case->isComplete()) {
                    $case->update([
                        'status' => CaseStatus::Delivered,
                        'completed_at' => $case->completed_at ?? now(),
                    ]);
                } else {
                    $case->update([
                        'status' => CaseStatus::InProgress,
                        'completed_at' => null,
                    ]);
                }

                $nowDelivered = $case->status === CaseStatus::Delivered;

                if ($nowDelivered && ! $wasDelivered) {
                    $audit->log('case.emitted', $case, ['code' => $case->case_code]);
                } elseif (! $nowDelivered && $wasDelivered) {
                    $audit->log('case.reverted', $case, ['missing' => $case->missingForEmission()]);
                }
            }

            $audit->log('case.updated', $case, ['fields' => array_keys($payload)]);
        });

        $case->refresh();

        // Al emitirse se archiva el PDF firmado (hash + versión) como copia legal.
        if ($case->status === CaseStatus::Delivered) {
            try {
                $pdf = app(ReportPdfService::class)->generate($case, auth()->id());
                $audit->log('case.pdf_generated', $case, [
                    'hash' => $pdf->hash,
                    'version' => $pdf->version,
                ]);
            } catch (\Throwable $e) {
                report($e);
            }

            return redirect()
                ->route('cases.show', $case)
                ->with('success', 'Informe completo: el estudio quedó Entregado, se archivó el PDF y ya se puede imprimir.');
        }

        $missing = $case->missingForEmission();

        return redirect()
            ->route('cases.edit', $case)
            ->with('warning', 'Guardado como borrador. Falta para poder imprimir: ' . implode(', ', $missing) . '.');
    }

    public function downloadPdf(ClinicalCase $case, ReportPdfService $service): BinaryFileResponse
    {
        /** @var ReportPdf|null $pdf */
        $pdf = $case->latestPdf();

        abort_if(! $pdf, 404, 'Este estudio no tiene PDF archivado.');

        $path = $service->path($pdf);

        abort_unless(File::exists($path), 404, 'El archivo del informe no está disponible.');

        return response()->download(
            $path,
            sprintf('%s-%s.pdf', $case->codeLabel(), $pdf->version),
            ['Content-Type' => 'application/pdf']
        );
    }

    public function editHeader(ClinicalCase $case): View
    {
        $case->load('patient');

        return view('cases.header', compact('case'));
    }

    /**
     * Corrige número y fecha de recepción. El tipo NO se cambia: el informe
     * ya tiene la estructura del tipo original.
     */
    public function updateHeader(
        Request $request,
        ClinicalCase $case,
        CaseNumberGenerator $numbers,
        AuditLogger $audit
    ): RedirectResponse {
        $data = $request->validate([
            'case_code' => [
                'required',
                'string',
                Rule::unique('cases', 'case_code')
                    ->where(fn ($q) => $q->where('type', $case->type->value))
                    ->ignore($case->id),
            ],
            'received_at' => ['nullable', 'date', 'before_or_equal:today'],
        ]);

        $parsed = $numbers->parse($case->type, (string) $data['case_code']);

        if (! $parsed) {
            throw ValidationException::withMessages([
                'case_code' => 'El número no respeta el formato del tipo de estudio.',
            ]);
        }

        $before = $case->case_code;

        $case->update([
            'case_code' => trim((string) $data['case_code']),
            'sequence_no' => $parsed[0],
            'year_no' => $parsed[1],
            'received_at' => $data['received_at'] ?: $case->received_at,
        ]);

        $audit->log('case.header_updated', $case, [
            'from' => $before,
            'to' => $case->case_code,
        ]);

        return redirect()
            ->route('cases.show', $case)
            ->with('success', 'Datos del estudio actualizados.');
    }

    public function reopen(ClinicalCase $case, AuditLogger $audit): RedirectResponse
    {
        abort_unless($case->status === CaseStatus::Delivered, 422, 'El estudio no está emitido.');

        $case->update([
            'status' => CaseStatus::InProgress,
            'completed_at' => null,
        ]);

        $audit->log('case.reopened', $case, ['code' => $case->case_code]);

        return redirect()
            ->route('cases.edit', $case)
            ->with('success', 'Estudio reabierto para corrección. Al guardar completo vuelve a quedar Entregado.');
    }

    public function print(ClinicalCase $case, AuditLogger $audit): View|RedirectResponse
    {
        if (! $case->isPrintable()) {
            return redirect()
                ->route('cases.edit', $case)
                ->with('error', 'El informe aún no está completo. Complete los campos y guarde para habilitar la impresión.');
        }

        $case->load(['patient', 'assignments.pathologist', 'histology', 'cytology']);

        $audit->log('case.printed', $case, ['code' => $case->case_code]);

        return view('cases.print', compact('case'));
    }
}

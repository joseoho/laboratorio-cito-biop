<?php

namespace App\Http\Controllers;

use App\Enums\CaseStatus;
use App\Enums\CaseType;
use App\Http\Requests\StoreCaseRequest;
use App\Models\Annex;
use App\Models\CaseAssignment;
use App\Models\ClinicalCase;
use App\Models\CytologyDiagnosis;
use App\Models\Pathologist;
use App\Models\Patient;
use App\Models\Template;
use App\Services\CaseCreator;
use App\Services\CaseNumberGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

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

    public function store(StoreCaseRequest $request, Patient $patient, CaseCreator $creator): RedirectResponse
    {
        $type = CaseType::from((string) $request->input('type'));

        $case = $creator->create(
            $patient,
            $type,
            (string) $request->input('case_code'),
            $request->input('received_at') ?: null,
            $request->input('material') ?: null,
            $request->input('clinical_diagnosis') ?: null,
        );

        return redirect()
            ->route('cases.edit', $case)
            ->with('success', 'Estudio ' . $case->codeLabel() . ' creado. Complete el informe.');
    }

    public function show(ClinicalCase $case): View
    {
        $case->load(['patient', 'assignments.pathologist', 'histology', 'cytology']);

        return view('cases.show', compact('case'));
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

        return view('cases.edit', compact('case', 'templates', 'diagnoses', 'annexes', 'pathologists'));
    }

    public function update(Request $request, ClinicalCase $case): RedirectResponse
    {
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

        DB::transaction(function () use ($case, $data, $isBiopsy) {
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
            }
        });

        $case->refresh();

        if ($case->status === CaseStatus::Delivered) {
            return redirect()
                ->route('cases.show', $case)
                ->with('success', 'Informe completo: el estudio quedó Entregado y ya se puede imprimir.');
        }

        $missing = $case->missingForEmission();

        return redirect()
            ->route('cases.edit', $case)
            ->with('warning', 'Guardado como borrador. Falta para poder imprimir: ' . implode(', ', $missing) . '.');
    }

    public function print(ClinicalCase $case): View|RedirectResponse
    {
        if (! $case->isPrintable()) {
            return redirect()
                ->route('cases.edit', $case)
                ->with('error', 'El informe aún no está completo. Complete los campos y guarde para habilitar la impresión.');
        }

        $case->load(['patient', 'assignments.pathologist', 'histology', 'cytology']);

        return view('cases.print', compact('case'));
    }
}

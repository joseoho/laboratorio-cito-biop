<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Origin;
use App\Models\Patient;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PatientController extends Controller
{
    private const PLACEHOLDERS = ['NS', 'SN', 'S/N', '?', 'MENOR', 'NC', 'N/A', 'NA'];

    public function index(Request $request): View
    {
        $term = (string) $request->query('search', '');
        $type = (string) $request->query('tipo', 'ci');

        $patients = Patient::query()
            ->with(['cases' => fn ($q) => $q->select('id', 'patient_id', 'type', 'case_code', 'status')])
            ->withCount('cases')
            ->search($term, $type)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('patients.index', compact('patients', 'term', 'type'));
    }

    public function create(): View
    {
        return view('patients.create', $this->catalogs());
    }

    public function store(Request $request, AuditLogger $audit): RedirectResponse
    {
        $patient = Patient::create($this->payload($this->validateData($request)));

        $audit->log('patient.created', $patient, ['full_name' => $patient->full_name]);

        return redirect()
            ->route('patients.show', $patient)
            ->with('success', 'Paciente registrado. Ahora puede crearle un estudio.');
    }

    public function show(Patient $patient): View
    {
        $patient->load([
            'doctor',
            'origin',
            'cases.assignments',
            'cases.histology',
            'cases.cytology',
        ]);

        return view('patients.show', compact('patient'));
    }

    public function edit(Patient $patient): View
    {
        $patient->load(['doctor', 'origin']);

        return view('patients.edit', array_merge(
            ['patient' => $patient],
            $this->catalogs()
        ));
    }

    public function update(Request $request, Patient $patient, AuditLogger $audit): RedirectResponse
    {
        $patient->update($this->payload($this->validateData($request)));

        $audit->log('patient.updated', $patient, $patient->getChanges());

        return redirect()
            ->route('patients.show', $patient)
            ->with('success', 'Paciente actualizado.');
    }

    /**
     * @return array{doctors: \Illuminate\Support\Collection, origins: \Illuminate\Support\Collection}
     */
    private function catalogs(): array
    {
        return [
            'doctors' => Doctor::query()->whereNotNull('name')->orderBy('name')->get(),
            'origins' => Origin::query()->orderBy('legacy_code')->get(),
        ];
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'full_name' => ['required', 'string', 'max:120'],
            'document_number' => ['nullable', 'string', 'max:50'],
            'sex' => ['nullable', Rule::in(['F', 'M', 'X'])],
            'age_at_entry' => ['nullable', 'integer', 'min:0', 'max:120'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:200'],
            'email' => ['nullable', 'email', 'max:150'],
            'doctor_id' => ['nullable', 'integer', 'exists:doctors,id'],
            'origin_id' => ['nullable', 'integer', 'exists:origins,id'],
        ], [
            'full_name.required' => 'El nombre del paciente es obligatorio.',
        ]);
    }

    private function payload(array $data): array
    {
        $document = trim((string) ($data['document_number'] ?? ''));

        return [
            'document_number' => $document !== '' ? $document : null,
            'document_is_placeholder' => in_array(mb_strtoupper($document), self::PLACEHOLDERS, true),
            'full_name' => trim($data['full_name']),
            'sex' => $data['sex'] ?? null,
            'age_at_entry' => $data['age_at_entry'] ?? null,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'email' => $data['email'] ?? null,
            'doctor_id' => $data['doctor_id'] ?? null,
            'origin_id' => $data['origin_id'] ?? null,
        ];
    }
}

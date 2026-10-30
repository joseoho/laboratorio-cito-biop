<?php

namespace Tests\Feature;

use App\Enums\CaseStatus;
use App\Enums\CaseType;
use App\Models\ClinicalCase;
use App\Models\Patient;
use App\Models\User;
use App\Services\CaseNumberGenerator;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RolesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::beginTransaction();
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        parent::tearDown();
    }

    private function user(string $role): User
    {
        return User::create([
            'name' => ucfirst($role) . ' Test',
            'email' => $role . '-' . uniqid() . '@laboratorio.local',
            'password' => bcrypt('secret'),
            'email_verified_at' => now(),
            'role' => $role,
        ]);
    }

    private function patient(): Patient
    {
        return Patient::create([
            'full_name' => 'PACIENTE ROLES ' . strtoupper(\Illuminate\Support\Str::random(6)),
        ]);
    }

    private function emittedCase(): ClinicalCase
    {
        $patient = $this->patient();
        $code = app(CaseNumberGenerator::class)->next(CaseType::Biopsy);

        $case = ClinicalCase::create([
            'patient_id' => $patient->id,
            'type' => CaseType::Biopsy,
            'case_code' => $code,
            'sequence_no' => (int) substr($code, 0, 4),
            'year_no' => 2000 + (int) substr($code, -2),
            'status' => CaseStatus::Delivered,
            'received_at' => now(),
            'completed_at' => now(),
        ]);

        $case->histology()->create([
            'material' => 'Piel',
            'macro_description' => 'Lesión.',
            'micro_diagnosis' => 'Diagnóstico.',
        ]);

        return $case;
    }

    public function test_operator_can_manage_patients_and_reports(): void
    {
        $operator = $this->user(User::ROLE_OPERADOR);
        $case = ClinicalCase::query()->firstOrFail();

        $this->actingAs($operator)->get('/pacientes/crear')->assertOk();
        $this->actingAs($operator)->get('/estudios/' . $case->id . '/editar')->assertOk();
        $this->actingAs($operator)->get('/reportes')->assertOk();
    }

    public function test_operator_cannot_reopen_emitted_reports(): void
    {
        $case = $this->emittedCase();

        $this->actingAs($this->user(User::ROLE_OPERADOR))
            ->post('/estudios/' . $case->id . '/reabrir')
            ->assertForbidden();
    }

    public function test_operator_cannot_access_quarantine_or_backups(): void
    {
        $operator = $this->user(User::ROLE_OPERADOR);

        $this->actingAs($operator)->get('/cuarentena')->assertForbidden();
        $this->actingAs($operator)->get('/respaldos')->assertForbidden();
    }

    public function test_admin_can_access_everything(): void
    {
        $admin = $this->user(User::ROLE_ADMIN);
        $case = ClinicalCase::query()->firstOrFail();

        $this->actingAs($admin)->get('/pacientes/crear')->assertOk();
        $this->actingAs($admin)->get('/estudios/' . $case->id . '/editar')->assertOk();
        $this->actingAs($admin)->get('/cuarentena')->assertOk();
        $this->actingAs($admin)->get('/respaldos')->assertOk();
    }

    public function test_patient_creation_is_audited(): void
    {
        $operator = $this->user(User::ROLE_OPERADOR);

        $this->actingAs($operator)->post('/pacientes', [
            'full_name' => 'PACIENTE AUDITADO',
        ]);

        $patient = Patient::query()->where('full_name', 'PACIENTE AUDITADO')->firstOrFail();

        $this->assertDatabaseHas('audit_events', [
            'action' => 'patient.created',
            'subject_type' => Patient::class,
            'subject_id' => $patient->id,
            'actor_id' => $operator->id,
        ]);
    }

    public function test_case_creation_is_audited(): void
    {
        $operator = $this->user(User::ROLE_OPERADOR);
        $patient = $this->patient();
        $code = app(CaseNumberGenerator::class)->next(CaseType::Biopsy);

        $this->actingAs($operator)->post('/pacientes/' . $patient->id . '/estudios', [
            'type' => 'biopsy',
            'case_code' => $code,
        ]);

        $case = ClinicalCase::query()->where('case_code', $code)->firstOrFail();

        $this->assertDatabaseHas('audit_events', [
            'action' => 'case.created',
            'subject_id' => $case->id,
            'actor_id' => $operator->id,
        ]);
    }

    public function test_print_is_audited(): void
    {
        $case = $this->emittedCase();
        $operator = $this->user(User::ROLE_OPERADOR);

        $this->actingAs($operator)
            ->get('/estudios/' . $case->id . '/imprimir')
            ->assertOk();

        $this->assertDatabaseHas('audit_events', [
            'action' => 'case.printed',
            'subject_id' => $case->id,
            'actor_id' => $operator->id,
        ]);
    }
}

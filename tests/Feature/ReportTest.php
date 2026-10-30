<?php

namespace Tests\Feature;

use App\Enums\CaseStatus;
use App\Enums\CaseType;
use App\Models\CaseAssignment;
use App\Models\ClinicalCase;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReportTest extends TestCase
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
            'name' => ucfirst($role) . ' Reportes',
            'email' => $role . '-reportes-' . uniqid() . '@laboratorio.local',
            'password' => bcrypt('secret'),
            'email_verified_at' => now(),
            'role' => $role,
        ]);
    }

    private function patient(): Patient
    {
        return Patient::create([
            'full_name' => 'PACIENTE REPORTE ' . strtoupper(\Illuminate\Support\Str::random(6)),
        ]);
    }

    private function case(string $type, CaseStatus $status, string $date, ?Patient $patient = null): ClinicalCase
    {
        $patient ??= $this->patient();

        return ClinicalCase::create([
            'patient_id' => $patient->id,
            'type' => $type,
            'case_code' => (string) random_int(100000, 999999),
            'status' => $status,
            'received_at' => $date,
            'completed_at' => $status === CaseStatus::Delivered ? $date : null,
        ]);
    }

    public function test_admin_sees_report_page(): void
    {
        $this->actingAs($this->user(User::ROLE_ADMIN))
            ->get('/reportes')
            ->assertOk()
            ->assertSee('Reportes')
            ->assertSee('Producción por mes');
    }

    public function test_recepcion_is_forbidden(): void
    {
        $this->actingAs($this->user(User::ROLE_RECEPCION))
            ->get('/reportes')
            ->assertForbidden();
    }

    public function test_totals_for_a_year(): void
    {
        $this->case('biopsy', CaseStatus::Delivered, '2099-03-10 09:00:00');
        $this->case('cytology', CaseStatus::InProgress, '2099-03-12 09:00:00');

        $response = $this->actingAs($this->user(User::ROLE_ADMIN))
            ->get('/reportes?anio=2099');

        $response->assertOk();
        $response->assertViewHas('totals', function ($totals) {
            return (int) $totals->total === 2
                && (int) $totals->biopsies === 1
                && (int) $totals->cytologies === 1
                && (int) $totals->delivered === 1
                && (int) $totals->in_progress === 1;
        });
        $response->assertViewHas('monthly', fn ($m) => ($m[3] ?? 0) === 2);
    }

    public function test_type_filter(): void
    {
        $this->case('biopsy', CaseStatus::Delivered, '2099-05-10 09:00:00');
        $this->case('cytology', CaseStatus::InProgress, '2099-05-11 09:00:00');

        $response = $this->actingAs($this->user(User::ROLE_PATOLOGO))
            ->get('/reportes?anio=2099&tipo=biopsy');

        $response->assertOk();
        $response->assertViewHas('totals', fn ($t) => (int) $t->total === 1);
    }

    public function test_pending_aging_counts(): void
    {
        $this->case('biopsy', CaseStatus::InProgress, '2099-04-01 09:00:00');

        $response = $this->actingAs($this->user(User::ROLE_AUDITOR))
            ->get('/reportes?anio=2099');

        $response->assertViewHas('pending', fn ($p) => (int) $p->total === 1);
    }

    public function test_pathologist_productivity(): void
    {
        $case = $this->case('biopsy', CaseStatus::Delivered, '2099-06-01 09:00:00');

        CaseAssignment::create([
            'case_id' => $case->id,
            'pathologist_name' => 'Dr. Prueba Reportes',
            'role' => 1,
        ]);

        $response = $this->actingAs($this->user(User::ROLE_ADMIN))
            ->get('/reportes?anio=2099');

        $response->assertViewHas('pathologists', function ($rows) {
            $row = $rows->firstWhere('name', 'Dr. Prueba Reportes');

            return $row !== null && (int) $row->cases === 1 && (int) $row->delivered === 1;
        });
    }

    public function test_without_pathologist_counter(): void
    {
        $this->case('biopsy', CaseStatus::InProgress, '2099-07-01 09:00:00');

        $response = $this->actingAs($this->user(User::ROLE_ADMIN))
            ->get('/reportes?anio=2099');

        $response->assertViewHas('withoutPathologist', fn ($n) => $n === 1);
    }
}

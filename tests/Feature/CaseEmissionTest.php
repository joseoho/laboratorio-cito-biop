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

/**
 * Regla del laboratorio: el informe completo pasa a Entregado y habilita
 * la impresión. No se exige patólogo (el legado casi nunca lo llena).
 */
class CaseEmissionTest extends TestCase
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

    private function user(): User
    {
        return User::query()->firstOrFail();
    }

    private function patient(): Patient
    {
        return Patient::create([
            'full_name' => 'PACIENTE EMISION ' . strtoupper(\Illuminate\Support\Str::random(6)),
            'sex' => 'F',
        ]);
    }

    private function biopsyCase(Patient $patient, array $report = []): ClinicalCase
    {
        $code = app(CaseNumberGenerator::class)->next(CaseType::Biopsy);

        $case = ClinicalCase::create([
            'patient_id' => $patient->id,
            'type' => CaseType::Biopsy,
            'case_code' => $code,
            'sequence_no' => (int) substr($code, 0, 4),
            'year_no' => 2000 + (int) substr($code, -2),
            'status' => CaseStatus::Received,
            'received_at' => now(),
        ]);

        $case->histology()->create($report);

        return $case;
    }

    public function test_incomplete_report_stays_in_progress(): void
    {
        $case = $this->biopsyCase($this->patient());

        $response = $this->actingAs($this->user())->put('/estudios/' . $case->id, [
            'macro_description' => 'Solo macro',
        ]);

        $response->assertRedirect(route('cases.edit', $case));
        $response->assertSessionHas('warning');

        $case->refresh();
        $this->assertSame(CaseStatus::InProgress, $case->status);
        $this->assertNull($case->completed_at);
        $this->assertNotSame([], $case->missingForEmission());
    }

    public function test_complete_report_becomes_delivered(): void
    {
        $case = $this->biopsyCase($this->patient());

        $response = $this->actingAs($this->user())->put('/estudios/' . $case->id, [
            'material' => 'Fragmento de piel',
            'macro_description' => 'Fragmento de 1 cm, superficie irregular.',
            'micro_diagnosis' => 'Carcinoma basocelular.',
        ]);

        $response->assertRedirect(route('cases.show', $case));
        $response->assertSessionHas('success');

        $case->refresh();
        $this->assertSame(CaseStatus::Delivered, $case->status);
        $this->assertNotNull($case->completed_at);
        $this->assertTrue($case->isComplete());
        $this->assertTrue($case->isPrintable());
    }

    public function test_print_is_blocked_until_emitted(): void
    {
        $case = $this->biopsyCase($this->patient());

        $this->actingAs($this->user())
            ->get('/estudios/' . $case->id . '/imprimir')
            ->assertRedirect(route('cases.edit', $case))
            ->assertSessionHas('error');
    }

    public function test_print_is_allowed_after_emission(): void
    {
        $case = $this->biopsyCase($this->patient(), [
            'material' => 'Fragmento de piel',
            'macro_description' => 'Lesión de 1 cm.',
            'micro_diagnosis' => 'Carcinoma basocelular.',
        ]);

        $this->actingAs($this->user())->put('/estudios/' . $case->id, [
            'material' => 'Fragmento de piel',
            'macro_description' => 'Lesión de 1 cm.',
            'micro_diagnosis' => 'Carcinoma basocelular.',
        ]);

        $this->actingAs($this->user())
            ->get('/estudios/' . $case->id . '/imprimir')
            ->assertOk()
            ->assertSee('BIOPSIA');
    }

    public function test_legacy_in_progress_case_remains_printable(): void
    {
        $legacy = ClinicalCase::query()
            ->whereNotNull('legacy_source')
            ->where('status', CaseStatus::InProgress)
            ->firstOrFail();

        $this->actingAs($this->user())
            ->get('/estudios/' . $legacy->id . '/imprimir')
            ->assertOk();
    }

    public function test_delivered_report_is_locked_and_admin_can_reopen(): void
    {
        $case = $this->biopsyCase($this->patient(), [
            'material' => 'Fragmento de piel',
            'macro_description' => 'Lesión de 1 cm.',
            'micro_diagnosis' => 'Carcinoma basocelular.',
        ]);

        $this->actingAs($this->user())->put('/estudios/' . $case->id, [
            'material' => 'Fragmento de piel',
            'macro_description' => 'Lesión de 1 cm.',
            'micro_diagnosis' => 'Carcinoma basocelular.',
        ]);

        $this->assertSame(CaseStatus::Delivered, $case->fresh()->status);

        // Emitido: cualquier edición se rechaza.
        $this->actingAs($this->user())
            ->put('/estudios/' . $case->id, [
                'material' => 'Fragmento de piel',
                'macro_description' => 'Lesión de 1 cm.',
                'micro_diagnosis' => '',
            ])
            ->assertRedirect(route('cases.show', $case))
            ->assertSessionHas('error');

        $this->assertSame(CaseStatus::Delivered, $case->fresh()->status);

        // Un administrador lo reabre y queda auditado.
        $this->actingAs($this->user())
            ->post('/estudios/' . $case->id . '/reabrir')
            ->assertRedirect(route('cases.edit', $case));

        $case->refresh();
        $this->assertSame(CaseStatus::InProgress, $case->status);
        $this->assertNull($case->completed_at);
        $this->assertDatabaseHas('audit_events', [
            'action' => 'case.reopened',
            'subject_id' => $case->id,
        ]);

        // Ya reabierto, se puede guardar incompleto.
        $this->actingAs($this->user())->put('/estudios/' . $case->id, [
            'material' => 'Fragmento de piel',
            'macro_description' => 'Lesión de 1 cm.',
            'micro_diagnosis' => '',
        ]);

        $case->refresh();
        $this->assertSame(CaseStatus::InProgress, $case->status);
        $this->assertFalse($case->isPrintable());
        $this->assertNotSame([], $case->missingForEmission());
    }
}

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

class CaseCreateTest extends TestCase
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
            'full_name' => 'PACIENTE DE PRUEBA',
            'document_number' => 'V-99999999',
            'sex' => 'F',
            'age_at_entry' => 30,
        ]);
    }

    public function test_create_form_renders_with_suggested_number(): void
    {
        $patient = $this->patient();
        $expected = app(CaseNumberGenerator::class)->next(CaseType::Biopsy);

        $this->actingAs($this->user())
            ->get('/pacientes/' . $patient->id . '/estudios/crear')
            ->assertOk()
            ->assertSee('Nuevo estudio')
            ->assertSee($expected);
    }

    public function test_store_creates_case_and_empty_report(): void
    {
        $patient = $this->patient();
        $code = app(CaseNumberGenerator::class)->next(CaseType::Biopsy);

        $response = $this->actingAs($this->user())->post('/pacientes/' . $patient->id . '/estudios', [
            'type' => 'biopsy',
            'case_code' => $code,
            'received_at' => now()->toDateString(),
            'material' => 'Fragmento de piel',
            'clinical_diagnosis' => 'Lesión dérmica',
        ]);

        $case = ClinicalCase::query()->where('case_code', $code)->firstOrFail();

        $response->assertRedirect(route('cases.edit', $case));
        $this->assertSame($patient->id, $case->patient_id);
        $this->assertSame(CaseType::Biopsy, $case->type);
        $this->assertSame(CaseStatus::Received, $case->status);
        $this->assertSame(2000 + (int) substr($code, -2), $case->year_no);
        $this->assertSame((int) substr($code, 0, 4), $case->sequence_no);
        $this->assertNull($case->legacy_source);
        $this->assertDatabaseHas('histology_reports', [
            'case_id' => $case->id,
            'material' => 'Fragmento de piel',
        ]);
    }

    public function test_store_creates_cytology_case(): void
    {
        $patient = $this->patient();
        $code = app(CaseNumberGenerator::class)->next(CaseType::Cytology);

        $this->actingAs($this->user())->post('/pacientes/' . $patient->id . '/estudios', [
            'type' => 'cytology',
            'case_code' => $code,
        ]);

        $case = ClinicalCase::query()->where('case_code', $code)->firstOrFail();

        $this->assertSame(CaseType::Cytology, $case->type);
        $this->assertDatabaseHas('cytology_reports', ['case_id' => $case->id]);
    }

    public function test_duplicate_number_is_rejected(): void
    {
        $patient = $this->patient();
        $existing = ClinicalCase::query()
            ->where('type', 'biopsy')
            ->whereNotNull('case_code')
            ->firstOrFail();

        $this->actingAs($this->user())
            ->from('/pacientes/' . $patient->id . '/estudios/crear')
            ->post('/pacientes/' . $patient->id . '/estudios', [
                'type' => 'biopsy',
                'case_code' => $existing->case_code,
            ])
            ->assertSessionHasErrors('case_code');
    }

    public function test_wrong_length_is_rejected(): void
    {
        $patient = $this->patient();

        $this->actingAs($this->user())
            ->from('/pacientes/' . $patient->id . '/estudios/crear')
            ->post('/pacientes/' . $patient->id . '/estudios', [
                'type' => 'biopsy',
                'case_code' => '123',
            ])
            ->assertSessionHasErrors('case_code');
    }

    public function test_future_received_date_is_rejected(): void
    {
        $patient = $this->patient();
        $code = app(CaseNumberGenerator::class)->next(CaseType::Biopsy);

        $this->actingAs($this->user())
            ->from('/pacientes/' . $patient->id . '/estudios/crear')
            ->post('/pacientes/' . $patient->id . '/estudios', [
                'type' => 'biopsy',
                'case_code' => $code,
                'received_at' => now()->addDay()->toDateString(),
            ])
            ->assertSessionHasErrors('received_at');
    }

    public function test_created_case_opens_in_editor_and_accepts_report(): void
    {
        $patient = $this->patient();
        $code = app(CaseNumberGenerator::class)->next(CaseType::Cytology);

        $this->actingAs($this->user())->post('/pacientes/' . $patient->id . '/estudios', [
            'type' => 'cytology',
            'case_code' => $code,
        ]);

        $case = ClinicalCase::query()->where('case_code', $code)->firstOrFail();

        $this->actingAs($this->user())
            ->get('/estudios/' . $case->id . '/editar')
            ->assertOk()
            ->assertSee($code);

        $this->actingAs($this->user())->put('/estudios/' . $case->id, [
            'cyto_description' => 'Extendido con células escamosas maduras.',
            'cyto_diagnosis' => 'Negativo para lesión intraepitelial.',
            'quality' => 'Muestra adecuada.',
        ])->assertRedirect(route('cases.show', $case));

        $this->assertSame(CaseStatus::Delivered, $case->fresh()->status);
        $this->assertNotNull($case->fresh()->completed_at);
        $this->assertSame('Negativo para lesión intraepitelial.', $case->fresh()->cytology->cyto_diagnosis);
    }
}

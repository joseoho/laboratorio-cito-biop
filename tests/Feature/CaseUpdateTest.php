<?php

namespace Tests\Feature;

use App\Models\ClinicalCase;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\DB;

class CaseUpdateTest extends TestCase
{
    private function user(): User
    {
        return User::first() ?? User::create([
            'name' => 'Test',
            'email' => 'test-update@laboratorio.local',
            'password' => bcrypt('secret'),
            'email_verified_at' => now(),
        ]);
    }

    public function test_login_page_renders(): void
    {
        $this->get('/login')->assertOk()->assertSee('Iniciar sesión');
    }

    public function test_update_report_persists_inside_transaction(): void
    {
        $case = ClinicalCase::query()->where('type', 'biopsy')->has('histology')->firstOrFail();
        $report = $case->histology;

        DB::beginTransaction();

        try {
            $response = $this->actingAs($this->user())->put('/estudios/' . $case->id, [
                'received_at' => $case->received_at?->format('Y-m-d'),
                'sent_at' => $case->sent_at?->format('Y-m-d'),
                'material' => $report->material,
                'clinical_diagnosis' => $report->clinical_diagnosis,
                'lesion_site' => $report->lesion_site,
                'macro_description' => 'MACRO DE PRUEBA',
                'micro_description' => $report->micro_description,
                'micro_diagnosis' => $report->micro_diagnosis,
                'notes' => $report->notes,
            ]);

            $response->assertRedirect();

            $this->assertDatabaseHas('histology_reports', [
                'case_id' => $case->id,
                'macro_description' => 'MACRO DE PRUEBA',
            ]);
        } finally {
            DB::rollBack();
        }

        $this->assertSame($report->macro_description, $report->fresh()->macro_description);
    }

    public function test_update_rejects_sent_date_before_received(): void
    {
        $case = ClinicalCase::query()->where('type', 'cytology')->has('cytology')->firstOrFail();

        $this->actingAs($this->user())
            ->from(route('cases.edit', $case))
            ->put('/estudios/' . $case->id, [
                'received_at' => '2020-05-10',
                'sent_at' => '2020-05-01',
            ])
            ->assertSessionHasErrors([
                'sent_at' => 'La fecha de envío debe ser igual o posterior a la de recepción.',
            ]);
    }

    public function test_same_pathologist_cannot_be_titular_and_cosigner(): void
    {
        $case = ClinicalCase::query()->where('type', 'biopsy')->has('histology')->firstOrFail();

        $this->actingAs($this->user())
            ->from(route('cases.edit', $case))
            ->put('/estudios/' . $case->id, [
                'pathologist_1' => 'Dr. Juan Carlos Becker',
                'pathologist_2' => 'Dr. Juan Carlos Becker',
            ])
            ->assertSessionHasErrors([
                'pathologist_2' => 'El cosignante debe ser distinto del patólogo titular.',
            ]);
    }
}

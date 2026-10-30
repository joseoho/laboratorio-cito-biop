<?php

namespace Tests\Feature;

use App\Enums\CaseStatus;
use App\Enums\CaseType;
use App\Models\ClinicalCase;
use App\Models\Patient;
use App\Models\ReportPdf;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PdfArchiveTest extends TestCase
{
    /** @var string[] */
    private array $generated = [];

    protected function setUp(): void
    {
        parent::setUp();
        DB::beginTransaction();
    }

    protected function tearDown(): void
    {
        foreach ($this->generated as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }

        DB::rollBack();
        parent::tearDown();
    }

    private function user(): User
    {
        return User::query()->firstOrFail();
    }

    private function completeCase(): ClinicalCase
    {
        $patient = Patient::create([
            'full_name' => 'PACIENTE PDF ' . strtoupper(\Illuminate\Support\Str::random(6)),
        ]);

        $case = ClinicalCase::create([
            'patient_id' => $patient->id,
            'type' => CaseType::Biopsy,
            'case_code' => (string) random_int(100000, 999999),
            'status' => CaseStatus::Received,
            'received_at' => now(),
        ]);

        $case->histology()->create([
            'material' => 'Fragmento de piel',
            'macro_description' => 'Lesión de 1 cm.',
            'micro_diagnosis' => 'Carcinoma basocelular.',
        ]);

        return $case;
    }

    public function test_emission_archives_pdf_with_hash(): void
    {
        $case = $this->completeCase();

        $response = $this->actingAs($this->user())->put('/estudios/' . $case->id, [
            'material' => 'Fragmento de piel',
            'macro_description' => 'Lesión de 1 cm.',
            'micro_diagnosis' => 'Carcinoma basocelular.',
        ]);

        $response->assertRedirect(route('cases.show', $case));

        $pdf = ReportPdf::query()->where('case_id', $case->id)->firstOrFail();
        $path = storage_path('app/private/reports/' . $pdf->path);
        $this->generated[] = $path;

        $this->assertFileExists($path);
        $this->assertSame(64, strlen($pdf->hash));
        $this->assertSame(hash('sha256', file_get_contents($path)), $pdf->hash);
        $this->assertSame(1, (int) $pdf->version);

        $this->assertDatabaseHas('audit_events', [
            'action' => 'case.pdf_generated',
            'subject_id' => $case->id,
        ]);
    }

    public function test_pdf_can_be_downloaded(): void
    {
        $case = $this->completeCase();

        $this->actingAs($this->user())->put('/estudios/' . $case->id, [
            'material' => 'Fragmento de piel',
            'macro_description' => 'Lesión de 1 cm.',
            'micro_diagnosis' => 'Carcinoma basocelular.',
        ]);

        $pdf = ReportPdf::query()->where('case_id', $case->id)->firstOrFail();
        $this->generated[] = storage_path('app/private/reports/' . $pdf->path);

        $this->actingAs($this->user())
            ->get('/estudios/' . $case->id . '/pdf')
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_case_without_pdf_returns_404(): void
    {
        $case = ClinicalCase::query()
            ->whereNotNull('legacy_source')
            ->firstOrFail();

        $this->actingAs($this->user())
            ->get('/estudios/' . $case->id . '/pdf')
            ->assertNotFound();
    }

    public function test_pdf_is_not_created_for_draft(): void
    {
        $patient = Patient::create([
            'full_name' => 'PACIENTE BORRADOR ' . strtoupper(\Illuminate\Support\Str::random(6)),
        ]);

        $case = ClinicalCase::create([
            'patient_id' => $patient->id,
            'type' => CaseType::Biopsy,
            'case_code' => (string) random_int(100000, 999999),
            'status' => CaseStatus::Received,
            'received_at' => now(),
        ]);

        $case->histology()->create([]);

        // Solo macro: no emite.
        $this->actingAs($this->user())->put('/estudios/' . $case->id, [
            'macro_description' => 'Solo macro.',
        ]);

        $this->assertDatabaseMissing('report_pdfs', ['case_id' => $case->id]);
    }
}

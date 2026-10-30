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

class CaseHeaderTest extends TestCase
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

    private function case(): ClinicalCase
    {
        $patient = Patient::create([
            'full_name' => 'PACIENTE CABECERA ' . strtoupper(\Illuminate\Support\Str::random(6)),
        ]);

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

        $case->histology()->create([]);

        return $case;
    }

    public function test_header_form_renders(): void
    {
        $case = $this->case();

        $this->actingAs($this->user())
            ->get('/estudios/' . $case->id . '/datos')
            ->assertOk()
            ->assertSee('Datos del estudio')
            ->assertSee($case->case_code);
    }

    public function test_update_recalculates_sequence_and_year(): void
    {
        $case = $this->case();

        $response = $this->actingAs($this->user())->put('/estudios/' . $case->id . '/datos', [
            'case_code' => '077720',
            'received_at' => '2020-06-15',
        ]);

        $response->assertRedirect(route('cases.show', $case));

        $case->refresh();
        $this->assertSame('077720', $case->case_code);
        $this->assertSame(777, $case->sequence_no);
        $this->assertSame(2020, $case->year_no);
        $this->assertSame('2020-06-15', $case->received_at?->format('Y-m-d'));
    }

    public function test_duplicate_number_is_rejected(): void
    {
        $case = $this->case();
        $other = $this->case();

        $this->actingAs($this->user())
            ->from('/estudios/' . $case->id . '/datos')
            ->put('/estudios/' . $case->id . '/datos', [
                'case_code' => $other->case_code,
            ])
            ->assertSessionHasErrors('case_code');

        $this->assertNotSame($other->case_code, $case->fresh()->case_code);
    }

    public function test_invalid_format_is_rejected(): void
    {
        $case = $this->case();

        $this->actingAs($this->user())
            ->from('/estudios/' . $case->id . '/datos')
            ->put('/estudios/' . $case->id . '/datos', [
                'case_code' => '123',
            ])
            ->assertSessionHasErrors('case_code');
    }

    public function test_future_received_date_is_rejected(): void
    {
        $case = $this->case();

        $this->actingAs($this->user())
            ->from('/estudios/' . $case->id . '/datos')
            ->put('/estudios/' . $case->id . '/datos', [
                'case_code' => $case->case_code,
                'received_at' => now()->addDay()->toDateString(),
            ])
            ->assertSessionHasErrors('received_at');
    }
}

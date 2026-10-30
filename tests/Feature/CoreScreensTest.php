<?php

namespace Tests\Feature;

use App\Models\ClinicalCase;
use App\Models\Patient;
use App\Models\User;
use Tests\TestCase;

class CoreScreensTest extends TestCase
{
    private function user(): User
    {
        return User::first() ?? User::create([
            'name' => 'Test',
            'email' => 'test-smoke@laboratorio.local',
            'password' => bcrypt('secret'),
            'email_verified_at' => now(),
        ]);
    }

    public function test_patient_list_renders(): void
    {
        $this->actingAs($this->user())
            ->get('/pacientes')
            ->assertOk()
            ->assertSee('Pacientes');
    }

    public function test_patient_search_by_name_renders(): void
    {
        $patient = Patient::query()->whereNotNull('full_name')->firstOrFail();

        $this->actingAs($this->user())
            ->get('/pacientes?search=' . urlencode($patient->full_name) . '&tipo=ci')
            ->assertOk();
    }

    public function test_patient_detail_renders_with_historical_cases(): void
    {
        $patient = Patient::query()->has('cases')->firstOrFail();

        $this->actingAs($this->user())
            ->get('/pacientes/' . $patient->id)
            ->assertOk()
            ->assertSee($patient->display_name);
    }

    public function test_case_list_renders(): void
    {
        $this->actingAs($this->user())
            ->get('/estudios')
            ->assertOk()
            ->assertSee('Estudios');
    }

    public function test_case_detail_renders(): void
    {
        $case = ClinicalCase::query()->whereNotNull('case_code')->firstOrFail();

        $this->actingAs($this->user())
            ->get('/estudios/' . $case->id)
            ->assertOk()
            ->assertSee($case->codeLabel());
    }

    public function test_case_editor_renders_for_both_types(): void
    {
        $user = $this->user();

        foreach (['biopsy', 'cytology'] as $type) {
            $case = ClinicalCase::query()->where('type', $type)->firstOrFail();

            $this->actingAs($user)
                ->get('/estudios/' . $case->id . '/editar')
                ->assertOk()
                ->assertSee('Guardar informe');
        }
    }

    public function test_case_print_renders(): void
    {
        $case = ClinicalCase::query()->whereNotNull('case_code')->firstOrFail();

        $this->actingAs($this->user())
            ->get('/estudios/' . $case->id . '/imprimir')
            ->assertOk()
            ->assertSee('LABORATORIO BECOMARCA');
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/pacientes')->assertRedirect('/login');
    }
}

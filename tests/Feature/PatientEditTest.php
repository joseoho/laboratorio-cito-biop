<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PatientEditTest extends TestCase
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
            'full_name' => 'PACIENTE EDITABLE',
            'document_number' => 'V-11111111',
            'sex' => 'F',
            'age_at_entry' => 30,
            'phone' => '0414-0000000',
        ]);
    }

    public function test_edit_form_renders_with_current_values(): void
    {
        $patient = $this->patient();

        $this->actingAs($this->user())
            ->get('/pacientes/' . $patient->id . '/editar')
            ->assertOk()
            ->assertSee('PACIENTE EDITABLE')
            ->assertSee('V-11111111')
            ->assertSee('Guardar cambios');
    }

    public function test_update_changes_personal_data(): void
    {
        $patient = $this->patient();
        $doctor = Doctor::query()->whereNotNull('name')->firstOrFail();

        $response = $this->actingAs($this->user())->put('/pacientes/' . $patient->id, [
            'full_name' => 'PACIENTE CORREGIDO',
            'document_number' => 'V-22222222',
            'sex' => 'M',
            'age_at_entry' => 44,
            'phone' => '0416-1111111',
            'address' => 'Nueva dirección',
            'doctor_id' => $doctor->id,
        ]);

        $response->assertRedirect(route('patients.show', $patient));

        $patient->refresh();
        $this->assertSame('PACIENTE CORREGIDO', $patient->full_name);
        $this->assertSame('V-22222222', $patient->document_number);
        $this->assertSame('M', $patient->sex);
        $this->assertSame(44, $patient->age_at_entry);
        $this->assertSame($doctor->id, $patient->doctor_id);
        $this->assertFalse($patient->document_is_placeholder);
    }

    public function test_placeholder_flag_is_recomputed_on_update(): void
    {
        $patient = $this->patient();

        $this->actingAs($this->user())->put('/pacientes/' . $patient->id, [
            'full_name' => 'PACIENTE EDITABLE',
            'document_number' => 'SN',
        ]);

        $this->assertTrue($patient->fresh()->document_is_placeholder);
    }

    public function test_update_requires_name(): void
    {
        $patient = $this->patient();

        $this->actingAs($this->user())
            ->from('/pacientes/' . $patient->id . '/editar')
            ->put('/pacientes/' . $patient->id, ['full_name' => ''])
            ->assertSessionHasErrors('full_name');
    }

    public function test_legacy_link_is_untouched_on_update(): void
    {
        $patient = Patient::query()->whereNotNull('legacy_id')->firstOrFail();
        $legacyId = $patient->legacy_id;

        $this->actingAs($this->user())->put('/pacientes/' . $patient->id, [
            'full_name' => 'NOMBRE CORREGIDO LEGADO',
        ]);

        $this->assertSame($legacyId, $patient->fresh()->legacy_id);
        $this->assertSame('NOMBRE CORREGIDO LEGADO', $patient->fresh()->full_name);
    }
}

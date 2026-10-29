<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\Origin;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PatientCreateTest extends TestCase
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

    public function test_create_form_renders(): void
    {
        $this->actingAs($this->user())
            ->get('/pacientes/crear')
            ->assertOk()
            ->assertSee('Nuevo paciente')
            ->assertSee('Nombre completo');
    }

    public function test_store_creates_patient_with_referral(): void
    {
        $doctor = Doctor::query()->whereNotNull('name')->firstOrFail();
        $origin = Origin::query()->firstOrFail();
        $name = 'TEST PACIENTE ' . strtoupper(\Illuminate\Support\Str::random(8));

        $response = $this->actingAs($this->user())->post('/pacientes', [
            'full_name' => $name,
            'document_number' => 'V-12345678',
            'sex' => 'F',
            'age_at_entry' => 41,
            'phone' => '0414-1234567',
            'address' => 'San Cristóbal',
            'email' => 'ana@example.test',
            'doctor_id' => $doctor->id,
            'origin_id' => $origin->id,
        ]);

        $patient = Patient::query()->where('full_name', $name)->firstOrFail();

        $response->assertRedirect(route('patients.show', $patient));
        $this->assertSame('V-12345678', $patient->document_number);
        $this->assertSame('F', $patient->sex);
        $this->assertSame(41, $patient->age_at_entry);
        $this->assertSame($doctor->id, $patient->doctor_id);
        $this->assertSame($origin->id, $patient->origin_id);
        $this->assertFalse($patient->document_is_placeholder);
        $this->assertNotNull($patient->created_at);
    }

    public function test_store_requires_name(): void
    {
        $this->actingAs($this->user())
            ->from('/pacientes/crear')
            ->post('/pacientes', ['document_number' => 'V-1'])
            ->assertSessionHasErrors('full_name');
    }

    public function test_placeholder_document_is_flagged(): void
    {
        $name = 'TEST SIN CEDULA ' . strtoupper(\Illuminate\Support\Str::random(8));

        $this->actingAs($this->user())->post('/pacientes', [
            'full_name' => $name,
            'document_number' => 'ns',
        ]);

        $patient = Patient::query()->where('full_name', $name)->firstOrFail();

        $this->assertTrue($patient->document_is_placeholder);
    }
}

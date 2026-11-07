<?php

namespace Tests\Feature;

use App\Models\ClinicalCase;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SignatureTest extends TestCase
{
    private const PNG_1PX = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

    private function admin(): User
    {
        return User::where('role', User::ROLE_ADMIN)->firstOrFail();
    }

    public function test_admin_can_upload_the_signature(): void
    {
        Storage::fake('local');

        $this->actingAs($this->admin())
            ->post(route('signature.store'), [
                'image' => UploadedFile::fake()->image('firma.png', 300, 100),
            ])
            ->assertRedirect();

        Storage::disk('local')->assertExists('firma/firma.png');
    }

    public function test_operator_cannot_open_the_signature_screen(): void
    {
        $operator = User::where('role', User::ROLE_OPERADOR)->firstOrFail();

        $this->actingAs($operator)
            ->get(route('signature.index'))
            ->assertForbidden();
    }

    public function test_signed_case_shows_the_signature_when_printing(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('firma/firma.png', base64_decode(self::PNG_1PX));

        $case = ClinicalCase::query()->whereNotNull('patient_id')->firstOrFail();
        $case->forceFill(['signed' => true, 'signed_at' => now()])->save();

        try {
            $this->actingAs($this->admin())
                ->get(route('cases.print', $case))
                ->assertOk()
                ->assertSee('data:image/png;base64', false);
        } finally {
            $case->forceFill(['signed' => false, 'signed_at' => null])->save();
        }
    }

    public function test_unsigned_case_does_not_show_a_signature(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('firma/firma.png', base64_decode(self::PNG_1PX));

        $case = ClinicalCase::query()->whereNotNull('patient_id')->firstOrFail();
        $case->forceFill(['signed' => false, 'signed_at' => null])->save();

        $this->actingAs($this->admin())
            ->get(route('cases.print', $case))
            ->assertOk()
            ->assertDontSee('data:image/png;base64', false);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Quarantine;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class QuarantineTest extends TestCase
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
            'name' => ucfirst($role) . ' Cuarentena',
            'email' => $role . '-quarantine-' . uniqid() . '@laboratorio.local',
            'password' => bcrypt('secret'),
            'email_verified_at' => now(),
            'role' => $role,
        ]);
    }

    public function test_admin_sees_quarantine_page(): void
    {
        $this->actingAs($this->user(User::ROLE_ADMIN))
            ->get('/cuarentena')
            ->assertOk()
            ->assertSee('Cuarentena de datos')
            ->assertSee('SIN_ESTUDIO');
    }

    public function test_patologo_is_forbidden(): void
    {
        $this->actingAs($this->user(User::ROLE_PATOLOGO))
            ->get('/cuarentena')
            ->assertForbidden();
    }

    public function test_resolve_marks_reviewed_and_audits(): void
    {
        $admin = $this->user(User::ROLE_ADMIN);
        $item = Quarantine::query()->where('status', 'pending')->firstOrFail();

        $this->actingAs($admin)
            ->post('/cuarentena/' . $item->id . '/resolver', [
                'status' => 'reviewed',
                'note' => 'Verificado con la operadora',
            ])
            ->assertRedirect();

        $item->refresh();
        $this->assertSame('reviewed', $item->status);
        $this->assertSame($admin->id, $item->reviewed_by);
        $this->assertNotNull($item->reviewed_at);
        $this->assertSame('Verificado con la operadora', $item->note);
        $this->assertDatabaseHas('audit_events', [
            'action' => 'quarantine.reviewed',
            'subject_id' => $item->id,
            'actor_id' => $admin->id,
        ]);
    }

    public function test_filter_by_bucket(): void
    {
        $response = $this->actingAs($this->user(User::ROLE_ADMIN))
            ->get('/cuarentena?bucket=SIN_ESTUDIO&estado=all');

        $response->assertOk();
        $response->assertViewHas('items', function ($items) {
            return $items->every(fn ($item) => $item->bucket === 'SIN_ESTUDIO');
        });
    }

    public function test_pending_filter_hides_reviewed(): void
    {
        $admin = $this->user(User::ROLE_ADMIN);
        $item = Quarantine::query()->where('status', 'pending')->firstOrFail();

        $item->update(['status' => 'reviewed', 'reviewed_by' => $admin->id, 'reviewed_at' => now()]);

        $response = $this->actingAs($admin)->get('/cuarentena?estado=pending');

        $response->assertViewHas('items', function ($items) use ($item) {
            return $items->every(fn ($row) => $row->id !== $item->id);
        });
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
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

    private function user(string $role, ?string $email = null): User
    {
        return User::create([
            'name' => ucfirst($role) . ' Usuarios',
            'email' => $email ?? ($role . '-' . uniqid() . '@laboratorio.local'),
            'password' => bcrypt('secret123'),
            'email_verified_at' => now(),
            'role' => $role,
        ]);
    }

    public function test_admin_sees_user_list(): void
    {
        $admin = $this->user(User::ROLE_ADMIN);

        $this->actingAs($admin)
            ->get('/usuarios')
            ->assertOk()
            ->assertSee('Usuarios')
            ->assertSee($admin->name)
            ->assertSee('Nuevo usuario');
    }

    public function test_operator_cannot_manage_users(): void
    {
        $operator = $this->user(User::ROLE_OPERADOR);
        $target = $this->user(User::ROLE_OPERADOR);

        $this->actingAs($operator)->get('/usuarios')->assertForbidden();
        $this->actingAs($operator)->get('/usuarios/crear')->assertForbidden();
        $this->actingAs($operator)->post('/usuarios', [])->assertForbidden();
        $this->actingAs($operator)->get('/usuarios/' . $target->id . '/editar')->assertForbidden();
        $this->actingAs($operator)->put('/usuarios/' . $target->id, [])->assertForbidden();
        $this->actingAs($operator)->delete('/usuarios/' . $target->id)->assertForbidden();
    }

    public function test_admin_creates_user_with_hashed_password_and_audit(): void
    {
        $admin = $this->user(User::ROLE_ADMIN);

        $response = $this->actingAs($admin)->post('/usuarios', [
            'name' => 'Operadora Nueva',
            'email' => 'operadora.nueva@laboratorio.local',
            'role' => User::ROLE_OPERADOR,
            'password' => 'clave-segura-123',
            'password_confirmation' => 'clave-segura-123',
        ]);

        $response->assertRedirect(route('users.index'));

        $created = User::query()->where('email', 'operadora.nueva@laboratorio.local')->firstOrFail();

        $this->assertSame('Operadora Nueva', $created->name);
        $this->assertSame(User::ROLE_OPERADOR, $created->role);
        $this->assertTrue(Hash::check('clave-segura-123', $created->password));
        $this->assertDatabaseHas('audit_events', [
            'action' => 'user.created',
            'subject_id' => $created->id,
            'actor_id' => $admin->id,
        ]);
    }

    public function test_duplicate_email_is_rejected(): void
    {
        $admin = $this->user(User::ROLE_ADMIN);
        $existing = $this->user(User::ROLE_OPERADOR, 'repetido@laboratorio.local');

        $this->actingAs($admin)
            ->from('/usuarios/crear')
            ->post('/usuarios', [
                'name' => 'Otro',
                'email' => $existing->email,
                'role' => User::ROLE_OPERADOR,
                'password' => 'clave-segura-123',
                'password_confirmation' => 'clave-segura-123',
            ])
            ->assertSessionHasErrors('email');
    }

    public function test_short_password_is_rejected(): void
    {
        $admin = $this->user(User::ROLE_ADMIN);

        $this->actingAs($admin)
            ->from('/usuarios/crear')
            ->post('/usuarios', [
                'name' => 'Corto',
                'email' => 'corto-' . uniqid() . '@laboratorio.local',
                'role' => User::ROLE_OPERADOR,
                'password' => '123',
                'password_confirmation' => '123',
            ])
            ->assertSessionHasErrors('password');
    }

    public function test_update_without_password_keeps_it(): void
    {
        $admin = $this->user(User::ROLE_ADMIN);
        $operator = $this->user(User::ROLE_OPERADOR);
        $originalHash = $operator->password;

        $this->actingAs($admin)->put('/usuarios/' . $operator->id, [
            'name' => 'Nombre Corregido',
            'email' => $operator->email,
            'role' => User::ROLE_OPERADOR,
        ])->assertRedirect(route('users.index'));

        $operator->refresh();
        $this->assertSame('Nombre Corregido', $operator->name);
        $this->assertSame($originalHash, $operator->password);
    }

    public function test_update_can_change_password(): void
    {
        $admin = $this->user(User::ROLE_ADMIN);
        $operator = $this->user(User::ROLE_OPERADOR);

        $this->actingAs($admin)->put('/usuarios/' . $operator->id, [
            'name' => $operator->name,
            'email' => $operator->email,
            'role' => User::ROLE_OPERADOR,
            'password' => 'nueva-clave-123',
            'password_confirmation' => 'nueva-clave-123',
        ]);

        $this->assertTrue(Hash::check('nueva-clave-123', $operator->fresh()->password));
        $this->assertDatabaseHas('audit_events', [
            'action' => 'user.updated',
            'subject_id' => $operator->id,
        ]);
    }

    public function test_admin_cannot_change_own_role(): void
    {
        $admin = $this->user(User::ROLE_ADMIN);

        $this->actingAs($admin)
            ->from('/usuarios/' . $admin->id . '/editar')
            ->put('/usuarios/' . $admin->id, [
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => User::ROLE_OPERADOR,
            ])
            ->assertSessionHasErrors('role');

        $this->assertSame(User::ROLE_ADMIN, $admin->fresh()->role);
    }

    public function test_admin_cannot_delete_self(): void
    {
        $admin = $this->user(User::ROLE_ADMIN);

        $this->actingAs($admin)
            ->delete('/usuarios/' . $admin->id)
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('error');

        $this->assertNotNull($admin->fresh());
    }

    public function test_admin_deletes_operator_and_audits(): void
    {
        $admin = $this->user(User::ROLE_ADMIN);
        $operator = $this->user(User::ROLE_OPERADOR);
        $operatorId = $operator->id;

        $this->actingAs($admin)
            ->delete('/usuarios/' . $operator->id)
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('success');

        $this->assertNull(User::query()->find($operatorId));
        $this->assertDatabaseHas('audit_events', [
            'action' => 'user.deleted',
            'subject_id' => $operatorId,
            'actor_id' => $admin->id,
        ]);
    }
}

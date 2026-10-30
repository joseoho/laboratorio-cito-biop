<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class BackupTest extends TestCase
{
    private ?string $dummy = null;

    protected function setUp(): void
    {
        parent::setUp();
        DB::beginTransaction();
    }

    protected function tearDown(): void
    {
        if ($this->dummy && is_file($this->dummy)) {
            @unlink($this->dummy);
        }

        DB::rollBack();
        parent::tearDown();
    }

    private function user(string $role): User
    {
        return User::create([
            'name' => ucfirst($role) . ' Backup',
            'email' => $role . '-backup-' . uniqid() . '@laboratorio.local',
            'password' => bcrypt('secret'),
            'email_verified_at' => now(),
            'role' => $role,
        ]);
    }

    public function test_admin_sees_backup_page(): void
    {
        $this->actingAs($this->user(User::ROLE_ADMIN))
            ->get('/respaldos')
            ->assertOk()
            ->assertSee('Respaldos')
            ->assertSee('Generar respaldo ahora');
    }

    public function test_operator_cannot_access_backups(): void
    {
        $this->actingAs($this->user(User::ROLE_OPERADOR))
            ->get('/respaldos')
            ->assertForbidden();
    }

    public function test_existing_backup_can_be_downloaded(): void
    {
        $dir = storage_path('backups');
        File::ensureDirectoryExists($dir);

        $this->dummy = $dir . DIRECTORY_SEPARATOR . 'lapt_core-20200101-000000.sql.gz';
        File::put($this->dummy, 'dummy-backup');

        $this->actingAs($this->user(User::ROLE_ADMIN))
            ->get('/respaldos/lapt_core-20200101-000000.sql.gz')
            ->assertOk();
    }

    public function test_unknown_or_malicious_filename_is_rejected(): void
    {
        $admin = $this->user(User::ROLE_ADMIN);

        $this->actingAs($admin)->get('/respaldos/lapt_core-20200101-000000.sql.gz')->assertNotFound();
        $this->actingAs($admin)->get('/respaldos/..%2F..%2F.env')->assertNotFound();
        $this->actingAs($admin)->get('/respaldos/otra-cosa.sql.gz')->assertNotFound();
    }
}

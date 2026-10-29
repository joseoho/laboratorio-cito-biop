<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Usuario inicial. El registro público está deshabilitado: los usuarios
     * se crean desde aquí o por un administrador.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@laboratorio.local')],
            [
                'name' => 'Administrador',
                'password' => Hash::make(env('ADMIN_PASSWORD', 'cambiar123')),
                'email_verified_at' => now(),
            ]
        );
    }
}

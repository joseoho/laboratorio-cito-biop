<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('operador')->change();
        });

        // El laboratorio trabaja con dos roles: administrador y operador.
        DB::table('users')
            ->whereIn('role', ['recepcion', 'patologo', 'auditor'])
            ->update(['role' => 'operador']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('recepcion')->change();
        });
    }
};

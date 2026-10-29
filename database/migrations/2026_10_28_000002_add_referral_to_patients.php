<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Referencia médica: el legado la guardaba en Pacientes.Dr y Pacientes.Procedencia.
        if (! Schema::hasColumn('patients', 'doctor_id')) {
            Schema::table('patients', function (Blueprint $table) {
                $table->foreignId('doctor_id')->nullable()->after('email')
                    ->constrained('doctors')->nullOnDelete();
                $table->foreignId('origin_id')->nullable()->after('doctor_id')
                    ->constrained('origins')->nullOnDelete();
            });
        }

        // Los estudios creados desde la app no tienen fila de origen en Access.
        // La clave única (legacy_source, legacy_row_id) admite varios NULL.
        Schema::table('cases', function (Blueprint $table) {
            $table->string('legacy_source', 20)->nullable()->change();
            $table->unsignedBigInteger('legacy_row_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->string('legacy_source', 20)->nullable(false)->change();
            $table->unsignedBigInteger('legacy_row_id')->nullable(false)->change();
        });

        if (Schema::hasColumn('patients', 'doctor_id')) {
            Schema::table('patients', function (Blueprint $table) {
                $table->dropConstrainedForeignId('doctor_id');
                $table->dropConstrainedForeignId('origin_id');
            });
        }
    }
};

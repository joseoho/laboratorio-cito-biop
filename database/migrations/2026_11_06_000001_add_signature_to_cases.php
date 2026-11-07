<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Firma digitalizada del informe: la casilla "Firma" del formulario.
 * Si esta activa, la impresion y el PDF muestran la firma escaneada.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('cases')) {
            return;
        }

        Schema::table('cases', function (Blueprint $table) {
            if (! Schema::hasColumn('cases', 'signed')) {
                $table->boolean('signed')->default(false)->after('status');
                $table->timestamp('signed_at')->nullable()->after('signed');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            if (Schema::hasColumn('cases', 'signed')) {
                $table->dropColumn(['signed', 'signed_at']);
            }
        });
    }
};

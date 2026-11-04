<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Los estudios viejos tienen year_no en NULL (o distinto del año real de recepción),
 * porque se calculaba parseando el código legado. Eso hacía que el filtro por año y
 * el reporte de producción no los mostraran (2001-2003, 2025 y filas sueltas).
 *
 * Se completa con el año de received_at, que es la fecha real del estudio.
 * Es un dato derivado: no se pierde información.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('cases')) {
            return;
        }

        DB::statement("
            UPDATE cases
               SET year_no = YEAR(received_at)
             WHERE received_at IS NOT NULL
               AND (year_no IS NULL OR year_no <> YEAR(received_at))
        ");
    }

    public function down(): void
    {
        // No se revierte: es un dato derivado y reconstruible.
    }
};

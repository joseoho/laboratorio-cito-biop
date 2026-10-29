<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Modelo clínico destino (equivalente a etl/transform/001_core_schema.sql).
 * Cada tabla se crea solo si no existe: permite aplicar esta migración sobre
 * `lapt_core` (ya poblada por el ETL) sin tocar datos, y construir una BD
 * limpia desde cero en desarrollo.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('patients')) {
            Schema::create('patients', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('legacy_id')->nullable()->unique();
                $table->string('document_number', 50)->nullable()->index();
                $table->boolean('document_is_placeholder')->default(false);
                $table->string('full_name', 120)->nullable()->index();
                $table->enum('sex', ['F', 'M', 'X'])->nullable();
                $table->string('legacy_sex_raw', 5)->nullable();
                $table->unsignedSmallInteger('age_at_entry')->nullable();
                $table->string('legacy_age_text', 50)->nullable();
                $table->date('birth_date')->nullable();
                $table->string('phone_code', 10)->nullable();
                $table->string('phone', 20)->nullable();
                $table->string('address', 200)->nullable();
                $table->string('email', 150)->nullable();
                $table->dateTime('created_at')->nullable()->index();
                $table->string('flags', 255)->nullable();
                $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            });
        }

        if (! Schema::hasTable('cases')) {
            Schema::create('cases', function (Blueprint $table) {
                $table->id();
                $table->foreignId('patient_id')->constrained('patients')->restrictOnDelete();
                $table->enum('type', ['biopsy', 'cytology']);
                $table->string('case_code', 20)->nullable();
                $table->unsignedInteger('sequence_no')->nullable();
                $table->unsignedSmallInteger('year_no')->nullable();
                $table->enum('status', ['received', 'in_progress', 'delivered', 'cancelled'])->default('received');
                $table->dateTime('received_at')->nullable();
                $table->dateTime('sent_at')->nullable();
                $table->dateTime('completed_at')->nullable();
                $table->string('legacy_source', 20);
                $table->unsignedBigInteger('legacy_row_id');
                $table->string('flags', 255)->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->unique(['legacy_source', 'legacy_row_id'], 'uq_cases_legacy');
                $table->index(['type', 'year_no', 'sequence_no'], 'ix_cases_number');
                $table->index('case_code');
                $table->index('status');
                $table->index('received_at');
            });
        }

        if (! Schema::hasTable('histology_reports')) {
            Schema::create('histology_reports', function (Blueprint $table) {
                $table->foreignId('case_id')->primary()->constrained('cases')->cascadeOnDelete();
                $table->string('material', 100)->nullable();
                $table->string('clinical_diagnosis', 100)->nullable();
                $table->string('lesion_site', 100)->nullable();
                $table->longText('macro_description')->nullable();
                $table->longText('micro_description')->nullable();
                $table->longText('micro_diagnosis')->nullable();
                $table->longText('notes')->nullable();
            });
        }

        if (! Schema::hasTable('cytology_reports')) {
            Schema::create('cytology_reports', function (Blueprint $table) {
                $table->foreignId('case_id')->primary()->constrained('cases')->cascadeOnDelete();
                $table->string('material', 250)->nullable();
                $table->string('clinical_diagnosis', 60)->nullable();
                $table->longText('macro_description')->nullable();
                $table->longText('cyto_description')->nullable();
                $table->longText('cyto_diagnosis')->nullable();
                $table->longText('quality')->nullable();
                $table->longText('notes')->nullable();
                $table->smallInteger('superficial')->nullable();
                $table->smallInteger('intermediate')->nullable();
                $table->smallInteger('parabasal')->nullable();
                $table->longText('conclusion')->nullable();
            });
        }

        if (! Schema::hasTable('pathologists')) {
            Schema::create('pathologists', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100)->unique();
            });
        }

        if (! Schema::hasTable('case_assignments')) {
            Schema::create('case_assignments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('case_id')->constrained('cases')->cascadeOnDelete();
                $table->foreignId('pathologist_id')->nullable()->constrained('pathologists')->nullOnDelete();
                $table->string('pathologist_name', 100)->nullable();
                $table->unsignedTinyInteger('role');
                $table->index('pathologist_id');
            });
        }

        if (! Schema::hasTable('doctors')) {
            Schema::create('doctors', function (Blueprint $table) {
                $table->id();
                $table->string('legacy_code', 50)->unique();
                $table->string('name', 100)->nullable();
                $table->string('phone', 50)->nullable();
            });
        }

        if (! Schema::hasTable('origins')) {
            Schema::create('origins', function (Blueprint $table) {
                $table->id();
                $table->string('legacy_code', 50)->unique();
                $table->string('price_list', 50)->nullable();
            });
        }

        if (! Schema::hasTable('templates')) {
            Schema::create('templates', function (Blueprint $table) {
                $table->id();
                $table->string('module', 10)->nullable()->index();
                $table->string('name', 50)->nullable();
                $table->longText('content')->nullable();
            });
        }

        if (! Schema::hasTable('cytology_diagnosis_catalog')) {
            Schema::create('cytology_diagnosis_catalog', function (Blueprint $table) {
                $table->id();
                $table->string('code', 50)->unique();
                $table->longText('description')->nullable();
                $table->longText('diagnosis')->nullable();
                $table->integer('sort_order')->nullable();
            });
        }

        if (! Schema::hasTable('annexes')) {
            Schema::create('annexes', function (Blueprint $table) {
                $table->id();
                $table->string('code', 50)->unique();
                $table->longText('text')->nullable();
                $table->integer('sort_order')->nullable();
            });
        }

        if (! Schema::hasTable('quarantine')) {
            Schema::create('quarantine', function (Blueprint $table) {
                $table->id();
                $table->string('bucket', 40)->index();
                $table->string('legacy_table', 40);
                $table->unsignedBigInteger('legacy_row_id')->nullable();
                $table->string('legacy_key', 60)->nullable()->index();
                $table->string('detail', 255)->nullable();
                $table->json('payload')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('quarantine');
        Schema::dropIfExists('annexes');
        Schema::dropIfExists('cytology_diagnosis_catalog');
        Schema::dropIfExists('templates');
        Schema::dropIfExists('origins');
        Schema::dropIfExists('doctors');
        Schema::dropIfExists('case_assignments');
        Schema::dropIfExists('pathologists');
        Schema::dropIfExists('cytology_reports');
        Schema::dropIfExists('histology_reports');
        Schema::dropIfExists('cases');
        Schema::dropIfExists('patients');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_pdfs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('cases')->cascadeOnDelete();
            $table->string('path', 255);
            $table->char('hash', 64);
            $table->unsignedInteger('version')->default(1);
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['case_id', 'created_at'], 'ix_pdfs_case');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_pdfs');
    }
};

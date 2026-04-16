<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_leaves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('hr_employees')->cascadeOnDelete();
            $table->string('type'); // vacaciones|incapacidad_imss|incapacidad_laboral|permiso_con_goce|permiso_sin_goce|maternidad|paternidad|duelo
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('business_days')->default(0);
            $table->text('reason')->nullable();
            $table->string('status')->default('pending'); // pending|approved|rejected|cancelled
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->string('imss_certificate_number', 30)->nullable(); // folio incapacidad IMSS
            $table->string('file_path')->nullable(); // certificado médico / incapacidad
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'employee_id', 'status']);
            $table->index(['company_id', 'start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_leaves');
    }
};

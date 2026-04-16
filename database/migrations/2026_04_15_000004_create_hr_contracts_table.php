<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('hr_employees')->cascadeOnDelete();
            $table->string('contract_number')->nullable();
            $table->string('type')->default('indefinido'); // indefinido|temporal|honorarios|obra_determinada|capacitacion_inicial
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('salary', 12, 2);
            $table->string('salary_period')->default('monthly'); // daily|weekly|biweekly|monthly
            $table->string('work_shift')->nullable(); // matutino|vespertino|nocturno|mixto
            $table->integer('work_hours_per_week')->default(48);
            $table->json('benefits')->nullable(); // {aguinaldo_days:15, vacation_days:6, vacation_premium_pct:25, food_voucher:0, ...}
            $table->string('status')->default('draft'); // draft|active|expired|terminated
            $table->string('file_path')->nullable(); // ruta al PDF del contrato
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'employee_id']);
            $table->index(['company_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_contracts');
    }
};

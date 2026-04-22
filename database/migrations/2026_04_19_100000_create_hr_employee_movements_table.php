<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_employee_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('hr_employees')->cascadeOnDelete();
            $table->foreignId('registered_by')->nullable()->constrained('users')->nullOnDelete();

            $table->enum('movement_type', [
                'alta',
                'baja',
                'ascenso',
                'descenso',
                'cambio_salario',
                'traslado',
                'cambio_contrato',
                'suspension',
                'reactivacion',
                'otro',
            ]);

            $table->date('effective_date');
            $table->json('previous_value')->nullable(); // {salary, position, department, status, ...}
            $table->json('new_value')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['employee_id', 'effective_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_employee_movements');
    }
};

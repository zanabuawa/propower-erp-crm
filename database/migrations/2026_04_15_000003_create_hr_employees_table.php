<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('hr_departments')->nullOnDelete();
            $table->foreignId('position_id')->nullable()->constrained('hr_positions')->nullOnDelete();

            // Identificación
            $table->string('employee_number', 30)->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('second_last_name')->nullable();
            $table->string('curp', 18)->nullable();
            $table->string('rfc', 13)->nullable();
            $table->string('nss', 11)->nullable(); // Número Seguro Social IMSS

            // Contacto
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('gender')->nullable(); // masculino|femenino|otro

            // Dirección
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code', 10)->nullable();

            // Laboral
            $table->date('hire_date');
            $table->date('termination_date')->nullable();
            $table->string('contract_type')->default('indefinido'); // indefinido|temporal|honorarios|obra_determinada|capacitacion_inicial
            $table->decimal('salary', 12, 2)->default(0);
            $table->string('salary_period')->default('monthly'); // daily|weekly|biweekly|monthly
            $table->string('work_shift')->nullable(); // matutino|vespertino|nocturno|mixto
            $table->string('status')->default('active'); // active|inactive|on_leave|suspended

            // Pago
            $table->string('payment_method')->default('transferencia'); // efectivo|transferencia|cheque
            $table->string('bank')->nullable();
            $table->string('bank_account', 20)->nullable();
            $table->string('clabe', 18)->nullable();

            // IMSS / INFONAVIT
            $table->string('imss_regime')->nullable(); // régimen de seguridad social
            $table->decimal('daily_salary_imss', 12, 2)->nullable(); // Salario Diario Integrado
            $table->string('infonavit_credit', 20)->nullable();

            // Contacto de emergencia
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone', 20)->nullable();
            $table->string('emergency_contact_relationship')->nullable();

            // Archivos
            $table->string('photo')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'department_id']);
            $table->unique(['company_id', 'employee_number']);
        });

        // Now add manager_id FK to hr_departments
        Schema::table('hr_departments', function (Blueprint $table) {
            $table->foreign('manager_id')->references('id')->on('hr_employees')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('hr_departments', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
        });
        Schema::dropIfExists('hr_employees');
    }
};

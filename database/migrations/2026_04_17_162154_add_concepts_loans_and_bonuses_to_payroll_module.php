<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Ampliar tabla de nóminas con metadatos de gestión
        Schema::table('hr_payrolls', function (Blueprint $table) {
            $table->text('description')->nullable()->after('notes');
            $table->json('labels')->nullable()->after('description'); // Etiquetas
            $table->json('checklist')->nullable()->after('labels'); // Lista de verificación
            $table->json('members')->nullable()->after('checklist'); // Miembros asignados (IDs de usuarios)
        });

        // 2. Catálogo de Conceptos de Nómina
        Schema::create('hr_payroll_concepts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('name');
            $table->string('code')->nullable(); // Código contable o SAT
            $table->enum('type', ['perception', 'deduction'])->default('perception');
            $table->boolean('is_taxable')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Control de Préstamos a Empleados
        Schema::create('hr_employee_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('hr_employees')->onDelete('cascade');
            $table->decimal('amount', 12, 2);
            $table->decimal('balance', 12, 2); // Saldo actual
            $table->decimal('installment_amount', 12, 2); // Cuota por periodo
            $table->string('reason')->nullable();
            $table->date('loan_date');
            $table->enum('status', ['active', 'paid', 'cancelled'])->default('active');
            $table->timestamps();
        });

        // 4. Registro de Bonos
        Schema::create('hr_employee_bonuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('hr_employees')->onDelete('cascade');
            $table->foreignId('concept_id')->nullable()->constrained('hr_payroll_concepts')->onDelete('set null');
            $table->decimal('amount', 12, 2);
            $table->string('reason')->nullable();
            $table->date('apply_at'); // Fecha o mes en que se aplica
            $table->boolean('is_applied')->default(false); // Si ya se sumó a una nómina
            $table->foreignId('payroll_item_id')->nullable()->constrained('hr_payroll_items')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_employee_bonuses');
        Schema::dropIfExists('hr_employee_loans');
        Schema::dropIfExists('hr_payroll_concepts');
        Schema::table('hr_payrolls', function (Blueprint $table) {
            $table->dropColumn(['description', 'labels', 'checklist', 'members']);
        });
    }
};

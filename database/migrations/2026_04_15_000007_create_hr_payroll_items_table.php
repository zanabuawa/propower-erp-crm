<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_payroll_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id')->constrained('hr_payrolls')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('hr_employees')->cascadeOnDelete();

            // Periodo
            $table->decimal('days_worked', 5, 2)->default(0);
            $table->decimal('daily_salary', 12, 2)->default(0);

            // Percepciones
            $table->decimal('base_salary', 12, 2)->default(0);         // días trabajados × salario diario
            $table->decimal('overtime_hours', 5, 2)->default(0);
            $table->decimal('overtime_amount', 12, 2)->default(0);
            $table->decimal('sunday_premium', 12, 2)->default(0);      // prima dominical
            $table->decimal('holiday_worked', 12, 2)->default(0);      // días festivos laborados
            $table->decimal('vacation_days_paid', 5, 2)->default(0);   // días de vacaciones pagadas
            $table->decimal('vacation_premium', 12, 2)->default(0);    // prima vacacional
            $table->decimal('christmas_bonus', 12, 2)->default(0);     // aguinaldo proporcional
            $table->decimal('food_voucher', 12, 2)->default(0);        // vales de despensa
            $table->json('other_perceptions')->nullable();               // [{concept, amount}]
            $table->decimal('gross_salary', 12, 2)->default(0);        // total percepciones

            // Deducciones
            $table->decimal('ispt', 12, 2)->default(0);                // ISR
            $table->decimal('imss_employee', 12, 2)->default(0);       // Cuota obrera IMSS
            $table->decimal('infonavit_payment', 12, 2)->default(0);   // Descuento INFONAVIT
            $table->decimal('loan_payment', 12, 2)->default(0);        // Préstamos empresa
            $table->json('other_deductions')->nullable();                // [{concept, amount}]
            $table->decimal('total_deductions', 12, 2)->default(0);

            // Aportaciones patronales (no afecta neto pero se registra)
            $table->decimal('employer_imss', 12, 2)->default(0);
            $table->decimal('employer_infonavit', 12, 2)->default(0);
            $table->decimal('employer_retirement', 12, 2)->default(0); // SAR

            $table->decimal('net_salary', 12, 2)->default(0);

            // CFDI Nómina (Facturapi)
            $table->string('status')->default('pending'); // pending|stamped|error
            $table->string('facturapi_id')->nullable();
            $table->string('cfdi_uuid')->nullable();
            $table->string('cfdi_xml_path')->nullable();
            $table->string('cfdi_pdf_path')->nullable();
            $table->text('stamp_error')->nullable();

            $table->timestamps();

            $table->unique(['payroll_id', 'employee_id']);
            $table->index('employee_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_payroll_items');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->string('name');
            $table->enum('period_type', ['mensual', 'trimestral', 'semestral', 'anual'])->default('mensual');
            $table->year('year');
            $table->unsignedTinyInteger('period_number')->nullable()->comment('Mes 1-12 o trimestre 1-4');
            $table->enum('category', ['ingresos', 'egresos', 'proyecto', 'departamento', 'otro'])->default('otro');
            $table->decimal('amount_planned', 15, 2)->default(0);
            $table->decimal('amount_actual', 15, 2)->default(0);
            $table->string('currency', 3)->default('MXN');
            $table->enum('status', ['borrador', 'aprobado', 'cerrado'])->default('borrador');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_budgets');
    }
};

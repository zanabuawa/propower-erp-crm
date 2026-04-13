<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('task_id')->nullable()->constrained('project_tasks')->nullOnDelete();
            $table->foreignId('registered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('concept');
            $table->enum('category', ['material', 'mano_obra', 'subcontrato', 'transporte', 'viaje', 'otro'])->default('otro');
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('MXN');
            $table->date('expense_date');
            $table->string('reference')->nullable()->comment('Folio factura o recibo');
            $table->enum('status', ['pendiente', 'aprobado', 'rechazado', 'pagado'])->default('pendiente');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_expenses');
    }
};

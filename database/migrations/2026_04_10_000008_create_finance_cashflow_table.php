<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_cashflow', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('finance_accounts')->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('budget_id')->nullable()->constrained('finance_budgets')->nullOnDelete();
            $table->string('concept');
            $table->enum('type', ['proyectado', 'real'])->default('proyectado');
            $table->enum('flow', ['entrada', 'salida'])->default('entrada');
            $table->enum('category', ['operacion', 'inversion', 'financiamiento'])->default('operacion');
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('MXN');
            $table->date('expected_date');
            $table->date('realized_date')->nullable();
            $table->boolean('is_realized')->default(false);
            $table->string('reference')->nullable()->index();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_cashflow');
    }
};

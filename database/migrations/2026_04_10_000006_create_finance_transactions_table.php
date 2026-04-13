<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('finance_accounts')->cascadeOnDelete();
            $table->foreignId('transfer_to_account_id')->nullable()->constrained('finance_accounts')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('registered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('folio')->unique();
            $table->enum('type', ['ingreso', 'egreso', 'transferencia'])->default('ingreso');
            $table->string('concept');
            $table->enum('category', ['venta', 'compra', 'nomina', 'impuesto', 'prestamo', 'inversion', 'proyecto', 'otro'])->default('otro');
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('MXN');
            $table->decimal('exchange_rate', 10, 6)->default(1);
            $table->date('transaction_date');
            $table->string('reference')->nullable()->comment('Folio factura, recibo, etc.');
            $table->enum('status', ['pendiente', 'confirmado', 'cancelado'])->default('confirmado');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_transactions');
    }
};

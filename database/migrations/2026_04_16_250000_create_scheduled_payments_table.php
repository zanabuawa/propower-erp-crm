<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scheduled_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('finance_account_id')->nullable();
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->unsignedBigInteger('purchase_invoice_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('executed_by')->nullable();
            $table->unsignedBigInteger('finance_transaction_id')->nullable(); // TX generado al ejecutar

            $table->string('folio', 30)->unique();
            $table->string('concept', 255);
            $table->enum('category', ['proveedor', 'nomina', 'impuesto', 'servicio', 'prestamo', 'inversion', 'otro'])->default('otro');
            $table->enum('frequency', ['once', 'weekly', 'biweekly', 'monthly', 'quarterly', 'annual'])->default('once');
            $table->enum('status', ['pending', 'paid', 'cancelled', 'overdue'])->default('pending');

            $table->decimal('amount', 14, 2);
            $table->string('currency', 3)->default('MXN');
            $table->decimal('exchange_rate', 10, 6)->default(1);

            $table->date('scheduled_date');      // próxima fecha de pago
            $table->date('end_date')->nullable(); // fin de recurrencia
            $table->timestamp('paid_at')->nullable();

            $table->string('reference', 100)->nullable();
            $table->text('notes')->nullable();

            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('finance_account_id')->references('id')->on('finance_accounts')->nullOnDelete();
            $table->foreign('supplier_id')->references('id')->on('suppliers')->nullOnDelete();
            $table->foreign('purchase_invoice_id')->references('id')->on('purchase_invoices')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('executed_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('finance_transaction_id')->references('id')->on('finance_transactions')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduled_payments');
    }
};

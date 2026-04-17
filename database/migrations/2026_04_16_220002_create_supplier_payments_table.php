<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('purchase_invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('finance_account_id')->nullable()
                ->constrained('finance_accounts')->nullOnDelete();

            $table->string('folio')->unique();
            $table->enum('currency', ['MXN', 'USD'])->default('MXN');
            $table->enum('payment_method', ['transfer', 'check', 'cash', 'credit_card'])->default('transfer');
            $table->enum('status', ['pending', 'applied', 'cancelled'])->default('applied');
            $table->decimal('amount', 12, 2);
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_payments');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::create('sale_payments', function (Blueprint $table) {
        $table->id();
        $table->foreignId('company_id')->constrained()->cascadeOnDelete();
        $table->foreignId('sale_invoice_id')->constrained()->cascadeOnDelete();
        $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
        $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
        $table->string('folio')->nullable();
        $table->enum('currency', ['MXN', 'USD'])->default('MXN');
        $table->enum('payment_method', ['cash', 'transfer', 'card', 'check', 'credit'])->default('cash');
        $table->enum('status', ['pending', 'applied', 'cancelled'])->default('applied');
        $table->decimal('amount', 12, 2);
        $table->string('reference')->nullable();
        $table->text('notes')->nullable();
        $table->timestamp('paid_at')->useCurrent();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_payments');
    }
};

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
    Schema::create('purchase_invoices', function (Blueprint $table) {
        $table->id();
        $table->foreignId('company_id')->constrained()->cascadeOnDelete();
        $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
        $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
        $table->string('folio');
        $table->string('supplier_invoice_number')->nullable();
        $table->enum('currency', ['MXN', 'USD'])->default('MXN');
        $table->decimal('subtotal', 12, 2)->default(0);
        $table->decimal('tax', 12, 2)->default(0);
        $table->decimal('total', 12, 2)->default(0);
        $table->enum('status', ['pending', 'paid', 'cancelled'])->default('pending');
        $table->timestamp('issued_at')->nullable();
        $table->timestamp('due_at')->nullable();
        $table->timestamp('paid_at')->nullable();
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_invoices');
    }
};

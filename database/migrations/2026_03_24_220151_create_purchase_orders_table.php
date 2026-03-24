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
    Schema::create('purchase_orders', function (Blueprint $table) {
        $table->id();
        $table->foreignId('company_id')->constrained()->cascadeOnDelete();
        $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
        $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
        $table->foreignId('purchase_requisition_id')->nullable()->constrained()->nullOnDelete();
        $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
        $table->string('folio')->nullable();
        $table->enum('currency', ['MXN', 'USD'])->default('MXN');
        $table->enum('status', [
            'draft',
            'sent',
            'partial_received',
            'received',
            'invoiced',
            'cancelled',
        ])->default('draft');
        $table->decimal('subtotal', 12, 2)->default(0);
        $table->decimal('tax', 12, 2)->default(0);
        $table->decimal('total', 12, 2)->default(0);
        $table->integer('payment_terms')->default(0);
        $table->foreignId('supplier_bank_account_id')->nullable()->constrained()->nullOnDelete();
        $table->text('notes')->nullable();
        $table->timestamp('expected_at')->nullable();
        $table->timestamps();
        $table->softDeletes();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};

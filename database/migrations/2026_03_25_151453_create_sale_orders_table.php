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
    Schema::create('sale_orders', function (Blueprint $table) {
        $table->id();
        $table->foreignId('company_id')->constrained()->cascadeOnDelete();
        $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
        $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
        $table->foreignId('sale_quotation_id')->nullable()->constrained()->nullOnDelete();
        $table->foreignId('price_list_id')->nullable()->constrained()->nullOnDelete();
        $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
        $table->string('folio')->nullable();
        $table->enum('currency', ['MXN', 'USD'])->default('MXN');
        $table->enum('status', ['draft', 'confirmed', 'partial_delivered', 'delivered', 'invoiced', 'cancelled'])->default('draft');
        $table->enum('payment_method', ['cash', 'transfer', 'card', 'check', 'credit'])->default('cash');
        $table->integer('payment_terms')->default(0);
        $table->decimal('subtotal', 12, 2)->default(0);
        $table->decimal('discount_amount', 12, 2)->default(0);
        $table->decimal('tax', 12, 2)->default(0);
        $table->decimal('total', 12, 2)->default(0);
        $table->text('notes')->nullable();
        $table->timestamp('required_at')->nullable();
        $table->timestamps();
        $table->softDeletes();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_orders');
    }
};

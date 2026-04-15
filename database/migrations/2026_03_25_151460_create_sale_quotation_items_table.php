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
    Schema::create('sale_quotation_items', function (Blueprint $table) {
        $table->id();
        $table->foreignId('sale_quotation_id')->constrained()->cascadeOnDelete();
        $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
        $table->string('description');
        $table->decimal('quantity', 10, 2);
        $table->decimal('unit_price', 12, 2);
        $table->decimal('discount_pct', 5, 2)->default(0);
        $table->decimal('discount_amount', 12, 2)->default(0);
        $table->decimal('tax_rate', 5, 2)->default(16);
        $table->decimal('subtotal', 12, 2)->default(0);
        $table->string('unit')->nullable();
        $table->text('notes')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_quotation_items');
    }
};

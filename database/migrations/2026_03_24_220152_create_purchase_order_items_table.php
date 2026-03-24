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
    Schema::create('purchase_order_items', function (Blueprint $table) {
        $table->id();
        $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
        $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
        $table->string('description');
        $table->decimal('quantity', 10, 2);
        $table->decimal('quantity_received', 10, 2)->default(0);
        $table->decimal('unit_price', 12, 2);
        $table->decimal('tax_rate', 5, 2)->default(16);
        $table->decimal('subtotal', 12, 2)->default(0);
        $table->string('unit')->nullable();
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
    }
};

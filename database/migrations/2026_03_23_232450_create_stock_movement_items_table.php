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
    Schema::create('stock_movement_items', function (Blueprint $table) {
        $table->id();
        $table->foreignId('stock_movement_id')->constrained()->cascadeOnDelete();
        $table->foreignId('product_id')->constrained()->cascadeOnDelete();
        $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
        $table->foreignId('warehouse_destination_id')->nullable()->constrained('warehouses')->nullOnDelete();
        $table->decimal('quantity', 10, 2);
        $table->decimal('unit_price', 12, 2)->default(0);
        $table->decimal('quantity_before', 10, 2)->default(0);
        $table->decimal('quantity_after', 10, 2)->default(0);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movement_items');
    }
};

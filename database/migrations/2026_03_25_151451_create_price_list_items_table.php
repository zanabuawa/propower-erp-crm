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
    Schema::create('price_list_items', function (Blueprint $table) {
        $table->id();
        $table->foreignId('price_list_id')->constrained()->cascadeOnDelete();
        $table->foreignId('product_id')->constrained()->cascadeOnDelete();
        $table->decimal('price', 12, 2);
        $table->decimal('discount_pct', 5, 2)->default(0);
        $table->timestamps();
        $table->unique(['price_list_id', 'product_id']);
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_list_items');
    }
};

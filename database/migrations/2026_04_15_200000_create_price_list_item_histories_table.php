<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_list_item_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('price_list_id')->constrained()->cascadeOnDelete();
            $table->decimal('old_price', 12, 4)->nullable();       // null = primera vez que se registra
            $table->decimal('new_price', 12, 4);
            $table->decimal('old_discount_pct', 6, 2)->default(0);
            $table->decimal('new_discount_pct', 6, 2)->default(0);
            $table->foreignId('changed_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('changed_at')->useCurrent();
            $table->timestamps();

            $table->index(['product_id', 'price_list_id', 'changed_at'], 'plih_product_pricelist_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_list_item_histories');
    }
};

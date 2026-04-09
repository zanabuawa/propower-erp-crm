<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_movement_items', function (Blueprint $table) {
            $table->foreignId('lot_id')->nullable()->after('warehouse_destination_id')
                ->constrained('product_lots')->nullOnDelete();
        });

        Schema::table('sale_delivery_items', function (Blueprint $table) {
            $table->foreignId('lot_id')->nullable()->after('warehouse_id')
                ->constrained('product_lots')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('stock_movement_items', function (Blueprint $table) {
            $table->dropForeign(['lot_id']);
            $table->dropColumn('lot_id');
        });

        Schema::table('sale_delivery_items', function (Blueprint $table) {
            $table->dropForeign(['lot_id']);
            $table->dropColumn('lot_id');
        });
    }
};

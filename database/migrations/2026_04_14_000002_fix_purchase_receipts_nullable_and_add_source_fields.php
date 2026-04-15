<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Hacer purchase_order_id nullable (recepciones sin OC: devoluciones, transferencias, defectuosos)
        Schema::table('purchase_receipts', function (Blueprint $table) {
            $table->foreignId('purchase_order_id')->nullable()->change();

            $table->foreignId('sale_delivery_id')
                ->nullable()
                ->after('purchase_order_id')
                ->constrained('sale_deliveries')
                ->nullOnDelete();

            $table->foreignId('origin_movement_id')
                ->nullable()
                ->after('sale_delivery_id')
                ->constrained('stock_movements')
                ->nullOnDelete();
        });

        // Hacer purchase_order_item_id nullable (ítems sin OC vinculada)
        Schema::table('purchase_receipt_items', function (Blueprint $table) {
            $table->foreignId('purchase_order_item_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('purchase_receipts', function (Blueprint $table) {
            $table->dropForeign(['sale_delivery_id']);
            $table->dropForeign(['origin_movement_id']);
            $table->dropColumn(['sale_delivery_id', 'origin_movement_id']);
            $table->foreignId('purchase_order_id')->nullable(false)->change();
        });

        Schema::table('purchase_receipt_items', function (Blueprint $table) {
            $table->foreignId('purchase_order_item_id')->nullable(false)->change();
        });
    }
};

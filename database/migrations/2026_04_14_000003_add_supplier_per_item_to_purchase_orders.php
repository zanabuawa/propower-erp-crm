<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Una OC ya no requiere un único proveedor en el encabezado
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->foreignId('supplier_id')->nullable()->change();
            $table->foreignId('supplier_bank_account_id')->nullable()->change();
        });

        // Cada ítem de OC puede tener su propio proveedor
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->foreignId('supplier_id')
                ->nullable()
                ->after('purchase_order_id')
                ->constrained('suppliers')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropColumn('supplier_id');
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->foreignId('supplier_id')->nullable(false)->change();
            $table->foreignId('supplier_bank_account_id')->nullable(false)->change();
        });
    }
};

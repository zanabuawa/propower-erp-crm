<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['sale_quotation_items', 'sale_order_items', 'sale_invoice_items'] as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    $table->decimal('ieps_rate', 6, 4)->default(0)->after('tax_rate');
                    $table->decimal('ieps_amount', 14, 2)->default(0)->after('ieps_rate');
                });
            }
        }

        // Totales IEPS en las cabeceras
        foreach (['sale_quotations', 'sale_orders', 'sale_invoices'] as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    $table->decimal('ieps', 14, 2)->default(0)->after('tax');
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['sale_quotation_items', 'sale_order_items', 'sale_invoice_items'] as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropColumn(['ieps_rate', 'ieps_amount']);
                });
            }
        }
        foreach (['sale_quotations', 'sale_orders', 'sale_invoices'] as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropColumn('ieps');
                });
            }
        }
    }
};

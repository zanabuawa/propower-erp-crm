<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL: modify enum to add 'paid'
        DB::statement("ALTER TABLE purchase_orders MODIFY COLUMN status ENUM(
            'draft','sent','waiting_delivery','partial_received',
            'received','invoiced','paid','cancelled'
        ) NOT NULL DEFAULT 'draft'");

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->decimal('paid_amount', 14, 2)->default(0)->after('total');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn('paid_amount');
        });

        DB::statement("ALTER TABLE purchase_orders MODIFY COLUMN status ENUM(
            'draft','sent','waiting_delivery','partial_received',
            'received','invoiced','cancelled'
        ) NOT NULL DEFAULT 'draft'");
    }
};

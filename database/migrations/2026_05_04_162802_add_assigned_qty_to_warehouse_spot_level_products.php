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
        Schema::table('warehouse_spot_level_products', function (Blueprint $table) {
            // Expected/allocated units at this location (informational, not tied to stock)
            $table->unsignedInteger('assigned_qty')->nullable()->after('sort_order');
        });
    }

    public function down(): void
    {
        Schema::table('warehouse_spot_level_products', function (Blueprint $table) {
            $table->dropColumn('assigned_qty');
        });
    }
};

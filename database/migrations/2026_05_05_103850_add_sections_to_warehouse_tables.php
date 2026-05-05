<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warehouse_spots', function (Blueprint $table) {
            $table->unsignedTinyInteger('sections_count')->default(1)->after('levels_count');
        });

        Schema::table('warehouse_spot_level_products', function (Blueprint $table) {
            $table->unsignedTinyInteger('section')->default(1)->after('assigned_qty');
        });
    }

    public function down(): void
    {
        Schema::table('warehouse_spots', function (Blueprint $table) {
            $table->dropColumn('sections_count');
        });
        Schema::table('warehouse_spot_level_products', function (Blueprint $table) {
            $table->dropColumn('section');
        });
    }
};

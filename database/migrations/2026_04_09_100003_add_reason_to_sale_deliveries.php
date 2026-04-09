<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_deliveries', function (Blueprint $table) {
            $table->string('reason', 40)->default('sale_order')->after('status');
            // sale_order, internal_use, scrap, return_to_supplier, other
        });
    }

    public function down(): void
    {
        Schema::table('sale_deliveries', function (Blueprint $table) {
            $table->dropColumn('reason');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('segment')->nullable()->after('status');          // A, B, C, D
            $table->string('zone')->nullable()->after('segment');            // zona geográfica
            $table->string('customer_category')->nullable()->after('zone');  // distribuidor, mayorista, minorista, usuario_final, gobierno
            $table->decimal('annual_revenue', 14, 2)->nullable()->after('customer_category'); // para segmentar por volumen
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['segment', 'zone', 'customer_category', 'annual_revenue']);
        });
    }
};

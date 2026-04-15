<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('purchase_price_includes_iva')
                ->default(false)
                ->after('purchase_price')
                ->comment('Si true, el precio de obtención ya incluye IVA 16%');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('purchase_price_includes_iva');
        });
    }
};

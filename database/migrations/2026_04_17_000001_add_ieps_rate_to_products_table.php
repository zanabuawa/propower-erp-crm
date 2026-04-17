<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // IEPS: Impuesto Especial sobre Producción y Servicios (Art. 2 LIEPS)
            // 0 para la mayoría de productos; 8% bebidas alcohólicas, 6% bebidas azucaradas, etc.
            $table->decimal('ieps_rate', 6, 4)->default(0)->after('sale_price');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('ieps_rate');
        });
    }
};

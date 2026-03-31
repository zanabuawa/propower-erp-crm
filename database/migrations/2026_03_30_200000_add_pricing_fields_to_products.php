<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('supplier_id')->nullable()->after('unit_of_measure_id')->constrained()->nullOnDelete();
            $table->decimal('profit_margin', 8, 4)->default(0)->after('purchase_price')
                ->comment('Margen de utilidad en porcentaje (%)');
            $table->decimal('operational_costs', 8, 4)->default(0)->after('profit_margin')
                ->comment('Gastos de operacion en porcentaje (%) sobre precio de obtencion');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropColumn(['supplier_id', 'profit_margin', 'operational_costs']);
        });
    }
};

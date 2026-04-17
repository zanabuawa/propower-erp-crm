<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fixed_assets', function (Blueprint $table) {
            $table->string('depreciation_method')->nullable()->after('acquisition_cost'); // linea_recta, doble_saldo, suma_digitos
            $table->unsignedSmallInteger('useful_life_years')->nullable()->after('depreciation_method');
            $table->decimal('salvage_value', 14, 2)->default(0)->after('useful_life_years');
            $table->decimal('fiscal_rate', 5, 4)->nullable()->after('salvage_value'); // tasa anual SAT ej: 0.25
            $table->decimal('accumulated_depreciation', 14, 2)->default(0)->after('fiscal_rate');
            $table->decimal('current_book_value', 14, 2)->nullable()->after('accumulated_depreciation');
            $table->date('last_depreciation_date')->nullable()->after('current_book_value');
        });
    }

    public function down(): void
    {
        Schema::table('fixed_assets', function (Blueprint $table) {
            $table->dropColumn([
                'depreciation_method', 'useful_life_years', 'salvage_value',
                'fiscal_rate', 'accumulated_depreciation', 'current_book_value',
                'last_depreciation_date',
            ]);
        });
    }
};

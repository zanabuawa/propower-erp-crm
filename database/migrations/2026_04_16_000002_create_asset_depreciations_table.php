<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_depreciations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fixed_asset_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month'); // 1-12
            $table->string('method'); // linea_recta, doble_saldo, suma_digitos, fiscal
            $table->decimal('book_value_start', 14, 2);
            $table->decimal('depreciation_amount', 14, 2);
            $table->decimal('accumulated_depreciation', 14, 2);
            $table->decimal('book_value_end', 14, 2);
            $table->boolean('is_fiscal')->default(false); // true = depreciación fiscal SAT
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->unique(['fixed_asset_id', 'year', 'month', 'is_fiscal'], 'unique_depreciation_period');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_depreciations');
    }
};

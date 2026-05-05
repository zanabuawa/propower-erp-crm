<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /*
    |--------------------------------------------------------------------------
    | Ubicación física de productos
    |--------------------------------------------------------------------------
    | Esta tabla responde: ¿DÓNDE dentro del almacén está almacenado este producto?
    | La CANTIDAD sigue en la tabla `stocks` (product_id + warehouse_id).
    | Un producto puede estar asignado a varios niveles (stock repartido),
    | y un nivel puede tener varios productos.
    */

    public function up(): void
    {
        Schema::create('warehouse_spot_level_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_spot_level_id')
                ->constrained('warehouse_spot_levels')
                ->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();

            // Orden de visualización dentro del nivel
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->string('notes', 255)->nullable();
            $table->timestamps();

            // Un producto aparece una sola vez por nivel
            $table->unique(['warehouse_spot_level_id', 'product_id'], 'unique_product_per_level');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_spot_level_products');
    }
};

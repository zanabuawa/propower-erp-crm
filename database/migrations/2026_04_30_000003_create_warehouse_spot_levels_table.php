<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouse_spot_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_spot_id')->constrained()->cascadeOnDelete();

            // 1 = nivel inferior (piso), N = nivel superior
            $table->unsignedTinyInteger('level_number');

            // Etiqueta personalizada: "Piso", "Nivel A", "Repisa 1", etc.
            $table->string('label', 100)->nullable();

            // Dimensiones específicas del nivel (puede diferir del spot si se personaliza)
            $table->unsignedSmallInteger('height_cm')->nullable();      // sobreescribe level_height_cm del spot
            $table->unsignedSmallInteger('capacity_units')->nullable(); // capacidad máxima en unidades

            // Estado del nivel
            $table->boolean('is_active')->default(true);  // false = nivel bloqueado/vacío reservado
            $table->string('notes', 500)->nullable();

            $table->timestamps();

            $table->unique(['warehouse_spot_id', 'level_number']);
            $table->index('warehouse_spot_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_spot_levels');
    }
};

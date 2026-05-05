<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouse_spots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_layout_id')->constrained('warehouse_layouts')->cascadeOnDelete();

            // Tipo de elemento
            $table->enum('type', [
                'estanteria',   // Estantería de niveles
                'rack',         // Rack industrial (pallets)
                'armario',      // Armario/gabinete cerrado
                'mesa',         // Mesa de trabajo
                'area',         // Área delimitada (zona de recepción, etc.)
                'otro',
            ])->default('estanteria');

            $table->string('label');             // Ej: "Estantería A", "Rack 01"
            $table->string('code', 20)->nullable(); // Código corto para etiquetas: EA-01

            // Posición en la cuadrícula (esquina superior-izquierda del elemento)
            $table->unsignedSmallInteger('col');
            $table->unsignedSmallInteger('row');

            // Tamaño en celdas de cuadrícula
            $table->unsignedSmallInteger('width_cells')->default(2);
            $table->unsignedSmallInteger('depth_cells')->default(1);

            // Rotación: 0=normal, 90=girado 90°, 180=invertido, 270=girado 270°
            $table->unsignedSmallInteger('rotation')->default(0);

            // Propiedades físicas del elemento
            $table->unsignedSmallInteger('levels_count')->default(3);     // Número de niveles/repisas
            $table->unsignedSmallInteger('level_height_cm')->default(40); // Alto por nivel en cm
            $table->unsignedSmallInteger('total_height_cm')->nullable();  // Alto total (calculado o manual)

            // Apariencia en el plano
            $table->string('color', 7)->default('#6366F1');
            $table->boolean('is_locked')->default(false); // Evitar mover accidentalmente

            $table->string('notes', 500)->nullable();
            $table->timestamps();

            // Un almacén no puede tener dos spots con el mismo código
            $table->unique(['warehouse_id', 'code']);
            // Índice para consultas por layout
            $table->index(['warehouse_layout_id', 'col', 'row']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_spots');
    }
};

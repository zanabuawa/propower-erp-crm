<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouse_layouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->unique()->constrained()->cascadeOnDelete();

            // Grid config
            $table->unsignedSmallInteger('grid_cols')->default(24);
            $table->unsignedSmallInteger('grid_rows')->default(20);
            $table->unsignedSmallInteger('cell_size_cm')->default(50); // 1 celda = 50 cm

            // Forma del almacén: array de {col, row} que forman el polígono cerrado
            // Ejemplo: [{col:0,row:0},{col:10,row:0},{col:10,row:5},{col:0,row:5}]
            $table->json('polygon_points')->default('[]');

            // Apariencia
            $table->string('background_color', 7)->default('#F1F5F9');
            $table->string('wall_color', 7)->default('#1E293B');
            $table->string('floor_color', 7)->default('#FFFFFF');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_layouts');
    }
};

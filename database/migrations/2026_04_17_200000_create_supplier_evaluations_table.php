<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('purchase_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('evaluated_by')->constrained('users')->cascadeOnDelete();

            // Dimensiones de evaluación (1-5)
            $table->unsignedTinyInteger('score_price')->default(3);       // Competitividad de precio
            $table->unsignedTinyInteger('score_quality')->default(3);     // Calidad del producto/servicio
            $table->unsignedTinyInteger('score_delivery')->default(3);    // Cumplimiento de tiempo de entrega
            $table->unsignedTinyInteger('score_compliance')->default(3);  // Documentación y cumplimiento general

            // Score global calculado (promedio ponderado guardado)
            $table->decimal('score_overall', 4, 2)->default(0);

            $table->text('notes')->nullable();
            $table->date('evaluated_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_evaluations');
    }
};

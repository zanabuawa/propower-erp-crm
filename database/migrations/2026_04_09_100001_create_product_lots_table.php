<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_lots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->string('lot_number', 40);
            $table->string('barcode', 60)->nullable();
            $table->decimal('initial_quantity', 12, 4)->default(0);
            $table->decimal('quantity', 12, 4)->default(0); // disponible actual
            $table->decimal('unit_cost', 12, 4)->default(0);
            $table->date('entry_date');
            $table->date('expiry_date')->nullable();
            $table->string('reference', 255)->nullable(); // folio de OC, movimiento origen
            $table->string('status', 20)->default('active'); // active, depleted, expired
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['lot_number', 'product_id', 'warehouse_id']);
            $table->unique('barcode');
            $table->index(['product_id', 'warehouse_id', 'status', 'entry_date']);
            $table->index(['company_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_lots');
    }
};

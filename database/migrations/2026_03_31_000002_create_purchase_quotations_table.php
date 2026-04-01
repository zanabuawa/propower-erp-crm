<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('purchase_requisition_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['preliminary', 'final'])->default('preliminary');
            $table->enum('status', ['pending', 'confirmed', 'returned', 'rejected', 'authorized'])->default('pending');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->text('notes')->nullable();          // Notas de compras para el solicitante
            $table->text('requester_notes')->nullable(); // Notas del solicitante al devolver
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_quotations');
    }
};

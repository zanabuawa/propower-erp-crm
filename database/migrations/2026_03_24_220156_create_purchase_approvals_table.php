<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
 public function up(): void
{
    Schema::create('purchase_approvals', function (Blueprint $table) {
        $table->id();
        $table->foreignId('purchase_requisition_id')->constrained()->cascadeOnDelete();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->enum('role', ['compras', 'administracion', 'gerencia']);
        $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
        $table->text('comments')->nullable();
        $table->timestamp('responded_at')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_approvals');
    }
};

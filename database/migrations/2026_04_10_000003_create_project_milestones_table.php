<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('due_date');
            $table->date('completed_at')->nullable();
            $table->enum('status', ['pendiente', 'completado', 'vencido'])->default('pendiente');
            $table->decimal('payment_amount', 15, 2)->nullable()->comment('Monto asociado a este hito');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_milestones');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_prospects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('position_id')->nullable()->constrained('hr_positions')->nullOnDelete();

            $table->string('first_name');
            $table->string('last_name');
            $table->string('second_last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            
            $table->string('source')->nullable(); // linkedin, recomendacion, etc.
            $table->string('cv_path')->nullable();
            $table->text('initial_notes')->nullable();
            
            $table->dateTime('interview_date')->nullable();
            $table->string('status')->default('nuevo'); // nuevo, entrevista, evaluacion, rechazado, contratado

            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_prospects');
    }
};

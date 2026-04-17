<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_prospect_interviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prospect_id')->constrained('hr_prospects')->cascadeOnDelete();
            $table->foreignId('interviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('interview_date');
            $table->string('interview_type'); // presencial, virtual
            $table->string('status')->default('agendada'); // agendada, realizada, cancelada
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('hr_prospect_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prospect_id')->constrained('hr_prospects')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('content');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_prospect_notes');
        Schema::dropIfExists('hr_prospect_interviews');
    }
};

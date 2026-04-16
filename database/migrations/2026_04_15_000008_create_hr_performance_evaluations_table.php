<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_performance_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('hr_employees')->cascadeOnDelete();
            $table->foreignId('evaluator_id')->constrained('users');
            $table->string('period'); // e.g. "2026-Q1", "2026-01", "2026-S1"
            $table->date('evaluation_date');
            // Categorías evaluadas (0-100 cada una)
            $table->json('categories')->nullable(); // {attendance:90, performance:85, teamwork:80, initiative:75, communication:88}
            $table->decimal('overall_score', 5, 2)->nullable();
            $table->text('strengths')->nullable();
            $table->text('areas_for_improvement')->nullable();
            $table->text('goals_next_period')->nullable();
            $table->string('status')->default('draft'); // draft|submitted|acknowledged
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_performance_evaluations');
    }
};

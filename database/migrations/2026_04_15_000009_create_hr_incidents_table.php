<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('hr_employees')->cascadeOnDelete();
            $table->string('type'); // tardanza|falta_injustificada|comportamiento|accidente_trabajo|incumplimiento|otro
            $table->date('incident_date');
            $table->text('description');
            $table->string('severity')->default('low'); // low|medium|high|critical
            $table->text('action_taken')->nullable();
            $table->boolean('resolved')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->string('file_path')->nullable(); // evidencia
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'employee_id']);
            $table->index(['company_id', 'incident_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_incidents');
    }
};

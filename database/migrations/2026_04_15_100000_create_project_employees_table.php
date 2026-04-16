<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('hr_employees')->cascadeOnDelete();
            $table->string('role')->nullable();          // Rol dentro del proyecto
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('hours_assigned', 8, 2)->nullable(); // Horas asignadas al proyecto
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['project_id', 'employee_id']); // Un empleado una vez por proyecto
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_employees');
    }
};

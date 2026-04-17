<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Ampliar tabla de empleados
        Schema::table('hr_employees', function (Blueprint $table) {
            $table->foreignId('supervisor_id')->nullable()->constrained('hr_employees')->onDelete('set null');
            $table->boolean('is_external')->default(false)->after('status');
        });

        // 2. Historial Académico
        Schema::create('hr_employee_education', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('hr_employees')->onDelete('cascade');
            $table->string('institution');
            $table->string('degree'); // Licenciatura, Maestría, etc.
            $table->string('field_of_study')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_completed')->default(false);
            $table->string('certificate_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 3. Catálogo de Cursos (Capacitación)
        Schema::create('hr_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('provider')->nullable(); // Interno o Externo
            $table->enum('type', ['internal', 'external'])->default('internal');
            $table->integer('duration_hours')->nullable();
            $table->decimal('cost', 12, 2)->default(0);
            $table->timestamps();
        });

        // 4. Historial de Capacitación por Empleado
        Schema::create('hr_employee_training', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('hr_employees')->onDelete('cascade');
            $table->foreignId('course_id')->constrained('hr_courses')->onDelete('cascade');
            $table->date('completion_date')->nullable();
            $table->date('expiry_date')->nullable(); // Control de vigencia (ej: DC3)
            $table->string('score')->nullable();
            $table->string('certificate_path')->nullable();
            $table->enum('status', ['planned', 'in_progress', 'completed', 'failed'])->default('completed');
            $table->timestamps();
        });

        // 5. Documentos Digitales y Vigencias
        Schema::create('hr_employee_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('hr_employees')->onDelete('cascade');
            $table->string('document_type'); // INE, CURP, RFC, Contrato, Licencia, etc.
            $table->string('file_path');
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable(); // Control de vigencias
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_employee_documents');
        Schema::dropIfExists('hr_employee_training');
        Schema::dropIfExists('hr_courses');
        Schema::dropIfExists('hr_employee_education');
        Schema::table('hr_employees', function (Blueprint $table) {
            $table->dropForeign(['supervisor_id']);
            $table->dropColumn(['supervisor_id', 'is_external']);
        });
    }
};

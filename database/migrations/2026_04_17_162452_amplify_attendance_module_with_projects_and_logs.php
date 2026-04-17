<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Sesiones de Asistencia (Gestión grupal)
        Schema::create('hr_attendance_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('name'); // Ej: Asistencia Semana 16 - Proyecto X
            $table->date('start_date');
            $table->date('end_date');
            $table->text('description')->nullable();
            $table->json('labels')->nullable(); // Etiquetas
            $table->json('checklist')->nullable(); // Lista de verificación
            $table->json('members')->nullable(); // Usuarios responsables
            $table->string('status')->default('open');
            $table->timestamps();
        });

        // 2. Registros Crudos de Relojes Checadores
        Schema::create('hr_attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('device_id')->nullable(); // ID del biométrico
            $table->string('external_employee_id'); // ID del empleado en el reloj
            $table->timestamp('timestamp');
            $table->string('type'); // in, out, break
            $table->json('raw_data')->nullable(); // Datos completos del log
            $table->boolean('is_processed')->default(false);
            $table->timestamps();
        });

        // 3. Ampliar tabla de asistencias actual
        Schema::table('hr_attendances', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->after('employee_id')->constrained('projects')->onDelete('set null');
            $table->foreignId('session_id')->nullable()->after('project_id')->constrained('hr_attendance_sessions')->onDelete('set null');
            $table->string('device_id')->nullable()->after('session_id'); // Dispositivo de origen
            $table->foreignId('raw_log_in_id')->nullable()->after('device_id')->constrained('hr_attendance_logs')->onDelete('set null');
            $table->foreignId('raw_log_out_id')->nullable()->after('raw_log_in_id')->constrained('hr_attendance_logs')->onDelete('set null');
            $table->json('metadata')->nullable()->after('notes'); // Para etiquetas o checklists específicos por registro
        });
    }

    public function down(): void
    {
        Schema::table('hr_attendances', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropForeign(['session_id']);
            $table->dropForeign(['raw_log_in_id']);
            $table->dropForeign(['raw_log_out_id']);
            $table->dropColumn(['project_id', 'session_id', 'device_id', 'raw_log_in_id', 'raw_log_out_id', 'metadata']);
        });
        Schema::dropIfExists('hr_attendance_logs');
        Schema::dropIfExists('hr_attendance_sessions');
    }
};

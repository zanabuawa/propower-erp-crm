<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Jerarquía de Departamentos
        Schema::table('hr_departments', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('company_id')->constrained('hr_departments')->onDelete('set null');
        });

        // 2. Perfiles de Puesto y Control de Plantilla
        Schema::table('hr_positions', function (Blueprint $table) {
            $table->text('responsibilities')->nullable()->after('description');
            $table->text('requirements')->nullable()->after('responsibilities');
            $table->integer('authorized_headcount')->default(0)->after('requirements'); // Plazas autorizadas
        });

        // 3. Rotación de Personal
        Schema::table('hr_employees', function (Blueprint $table) {
            $table->string('termination_reason')->nullable()->after('termination_date');
            $table->enum('termination_type', ['voluntary', 'involuntary'])->nullable()->after('termination_reason');
        });

        // 4. Planeación por Proyecto
        Schema::table('hr_job_openings', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->after('branch_id')->constrained('projects')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('hr_job_openings', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropColumn('project_id');
        });
        Schema::table('hr_employees', function (Blueprint $table) {
            $table->dropColumn(['termination_reason', 'termination_type']);
        });
        Schema::table('hr_positions', function (Blueprint $table) {
            $table->dropColumn(['responsibilities', 'requirements', 'authorized_headcount']);
        });
        Schema::table('hr_departments', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn('parent_id');
        });
    }
};

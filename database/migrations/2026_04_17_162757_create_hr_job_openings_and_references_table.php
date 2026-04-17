<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Registro de Vacantes
        Schema::create('hr_job_openings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('position_id')->constrained('hr_positions')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null');
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('requirements')->nullable();
            $table->text('benefits')->nullable();
            $table->integer('quantity')->default(1);
            $table->string('salary_range')->nullable();
            $table->enum('type', ['internal', 'external', 'mixed'])->default('external');
            $table->enum('status', ['open', 'closed', 'paused', 'cancelled'])->default('open');
            $table->date('published_at')->nullable();
            $table->date('closing_date')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Vincular Candidatos a Vacantes
        Schema::table('hr_prospects', function (Blueprint $table) {
            $table->foreignId('job_opening_id')->nullable()->after('position_id')->constrained('hr_job_openings')->onDelete('set null');
        });

        // 3. Referencias Laborales de Candidatos
        Schema::create('hr_prospect_references', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prospect_id')->constrained('hr_prospects')->onDelete('cascade');
            $table->string('name');
            $table->string('company')->nullable();
            $table->string('position')->nullable();
            $table->string('phone')->nullable();
            $table->string('relationship')->nullable(); // Ej: Jefe directo, Colega
            $table->text('notes')->nullable(); // Resultado de la validación de la referencia
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_prospect_references');
        Schema::table('hr_prospects', function (Blueprint $table) {
            $table->dropForeign(['job_opening_id']);
            $table->dropColumn('job_opening_id');
        });
        Schema::dropIfExists('hr_job_openings');
    }
};

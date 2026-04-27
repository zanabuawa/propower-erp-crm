<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Permisos de trabajo
        Schema::create('work_permits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tender_id')->nullable()->constrained('tenders')->nullOnDelete();
            $table->enum('type', ['altura', 'excavacion', 'electrico', 'confinado', 'caliente', 'general'])->default('general');
            $table->string('description');
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('valid_from');
            $table->date('valid_until');
            $table->enum('status', ['activo', 'vencido', 'cancelado'])->default('activo');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['project_id', 'status']);
        });

        // Reportes semanales de obra
        Schema::create('work_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tender_id')->nullable()->constrained('tenders')->nullOnDelete();
            $table->date('week_start');
            $table->date('week_end');
            $table->unsignedTinyInteger('progress_pct')->default(0);
            $table->text('activities')->nullable();
            $table->text('issues')->nullable();
            $table->text('next_week_plan')->nullable();
            $table->string('weather_conditions', 100)->nullable();
            $table->unsignedSmallInteger('workers_count')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['project_id', 'week_start']);
        });

        // Reportes fotográficos
        Schema::create('work_photo_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tender_id')->nullable()->constrained('tenders')->nullOnDelete();
            $table->date('report_date');
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('photos')->nullable();
            $table->string('location', 200)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['project_id', 'report_date']);
        });

        // Libranzas / estimaciones de obra
        Schema::create('work_libranzas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tender_id')->nullable()->constrained('tenders')->nullOnDelete();
            $table->unsignedSmallInteger('number')->default(1);
            $table->string('concept');
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('amount', 16, 2)->default(0);
            $table->decimal('advance_pct', 5, 2)->default(0);
            $table->enum('status', ['borrador', 'enviada', 'aprobada', 'pagada'])->default('borrador');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['project_id', 'status']);
        });

        // Programas de obra
        Schema::create('work_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tender_id')->nullable()->constrained('tenders')->nullOnDelete();
            $table->string('name');
            $table->unsignedSmallInteger('version')->default(1);
            $table->enum('status', ['borrador', 'vigente', 'historico'])->default('borrador');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'status']);
        });

        // Actividades del programa de obra
        Schema::create('work_program_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('work_programs')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('work_program_activities')->nullOnDelete();
            $table->string('name');
            $table->string('unit', 20)->nullable();
            $table->decimal('quantity', 12, 2)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->unsignedTinyInteger('progress_pct')->default(0);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_program_activities');
        Schema::dropIfExists('work_programs');
        Schema::dropIfExists('work_libranzas');
        Schema::dropIfExists('work_photo_reports');
        Schema::dropIfExists('work_reports');
        Schema::dropIfExists('work_permits');
    }
};

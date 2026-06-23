<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_incident_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tender_id')->nullable()->constrained('tenders')->nullOnDelete();
            $table->date('incident_date');
            $table->string('title', 200);
            $table->string('location', 200)->nullable();
            $table->text('description');
            $table->text('actions_taken')->nullable();
            $table->string('responsible_name', 160)->nullable();
            $table->enum('status', ['abierta', 'en_revision', 'cerrada'])->default('abierta');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['project_id', 'incident_date']);
            $table->index(['project_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_incident_reports');
    }
};

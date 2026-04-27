<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('tender_id')->nullable()->constrained('tenders')->nullOnDelete();
            $table->date('visit_date');
            $table->enum('visit_type', ['reconocimiento', 'supervision', 'entrega', 'cliente', 'interna'])->default('supervision');
            $table->string('purpose');
            $table->string('address', 300)->nullable();
            $table->string('location_notes', 300)->nullable();
            $table->json('attendees')->nullable();
            $table->text('report')->nullable();
            $table->json('photos')->nullable();
            $table->enum('status', ['programada', 'realizada', 'cancelada'])->default('programada');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'visit_date']);
            $table->index(['company_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_visits');
    }
};

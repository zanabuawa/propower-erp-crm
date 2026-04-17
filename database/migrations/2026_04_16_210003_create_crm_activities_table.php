<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();          // quien creó
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete(); // responsable
            $table->foreignId('prospect_id')->nullable()->constrained('sales_prospects')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('opportunity_id')->nullable()->constrained('sales_opportunities')->nullOnDelete();
            $table->string('type');      // call, email, meeting, task, whatsapp, visit, note
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('reminder_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('status')->default('pending'); // pending, completed, cancelled
            $table->text('outcome')->nullable();          // resultado después de completar
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_activities');
    }
};

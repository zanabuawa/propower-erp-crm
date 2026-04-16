<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_vacation_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('hr_employees')->cascadeOnDelete();
            $table->year('year');
            $table->decimal('days_earned', 5, 2)->default(0);          // según LFT por antigüedad
            $table->decimal('days_used', 5, 2)->default(0);            // vacaciones ya disfrutadas
            $table->decimal('days_pending_approval', 5, 2)->default(0); // solicitudes pendientes
            $table->decimal('days_available', 5, 2)->default(0);       // earned - used - pending
            $table->timestamps();

            $table->unique(['employee_id', 'year']);
            $table->index(['company_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_vacation_balances');
    }
};

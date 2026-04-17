<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fixed_asset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('technician_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('folio')->unique();
            $table->string('type');              // preventive, corrective, calibration, inspection
            $table->string('status')->default('scheduled'); // scheduled, in_progress, completed, cancelled
            $table->date('scheduled_date');
            $table->date('completed_date')->nullable();
            $table->string('technician_name')->nullable();   // técnico externo
            $table->string('provider')->nullable();          // empresa proveedora
            $table->decimal('cost', 14, 2)->nullable();
            $table->date('next_scheduled_date')->nullable();
            $table->unsignedTinyInteger('interval_months')->nullable(); // frecuencia para preventivos
            $table->text('work_performed')->nullable();
            $table->text('parts_replaced')->nullable();
            $table->text('observations')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_maintenances');
    }
};

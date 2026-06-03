<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->string('folio')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type')->default('email');      // email, whatsapp, sms, social_media, event, phone, other
            $table->string('status')->default('draft');    // draft, active, paused, completed, cancelled
            $table->text('target_audience')->nullable();
            $table->decimal('budget', 12, 2)->nullable();
            $table->decimal('spent', 12, 2)->nullable();
            $table->date('start_at')->nullable();
            $table->date('end_at')->nullable();
            $table->unsignedInteger('leads_generated')->default(0);
            $table->unsignedInteger('conversions')->default(0);
            $table->decimal('revenue_generated', 12, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_campaigns');
    }
};

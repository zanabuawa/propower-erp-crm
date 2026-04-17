<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_prospects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('contact_name')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_position')->nullable();
            $table->string('source')->nullable();        // inbound, referral, cold_call, social, website, event, other
            $table->string('status')->default('new');    // new, contacted, qualified, disqualified, converted
            $table->decimal('estimated_value', 14, 2)->default(0);
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->text('description')->nullable();
            $table->date('next_follow_up')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->foreignId('converted_to_customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_prospects');
    }
};

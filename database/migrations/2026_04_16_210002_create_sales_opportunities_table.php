<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_opportunities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('prospect_id')->nullable()->constrained('sales_prospects')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('title');
            $table->string('stage')->default('qualification'); // qualification, proposal, negotiation, won, lost
            $table->unsignedTinyInteger('probability')->default(10); // 0-100
            $table->decimal('estimated_value', 14, 2)->default(0);
            $table->date('expected_close_date')->nullable();
            $table->text('description')->nullable();
            $table->string('lost_reason')->nullable();
            $table->timestamp('won_at')->nullable();
            $table->timestamp('lost_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_opportunities');
    }
};

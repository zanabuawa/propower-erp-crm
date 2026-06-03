<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('sale_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('sale_invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->string('folio')->unique();
            $table->string('subject');
            $table->text('description')->nullable();
            $table->string('type')->default('support');      // support, warranty, complaint, inquiry, return
            $table->string('priority')->default('medium');   // low, medium, high, urgent
            $table->string('status')->default('open');       // open, in_progress, waiting, resolved, closed
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('crm_ticket_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('crm_tickets')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained();
            $table->text('body');
            $table->boolean('is_internal')->default(false); // nota interna vs respuesta visible
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_ticket_messages');
        Schema::dropIfExists('crm_tickets');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::create('sale_invoices', function (Blueprint $table) {
        $table->id();
        $table->foreignId('company_id')->constrained()->cascadeOnDelete();
        $table->foreignId('sale_order_id')->nullable()->constrained()->nullOnDelete();
        $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
        $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
        $table->string('folio')->nullable();
        $table->string('cfdi_uuid')->nullable();
        $table->string('cfdi_xml')->nullable();
        $table->string('cfdi_pdf')->nullable();
        $table->enum('type', ['internal', 'cfdi'])->default('internal');
        $table->enum('currency', ['MXN', 'USD'])->default('MXN');
        $table->enum('status', ['draft', 'stamped', 'paid', 'cancelled'])->default('draft');
        $table->enum('payment_method', ['cash', 'transfer', 'card', 'check', 'credit'])->default('cash');
        $table->decimal('subtotal', 12, 2)->default(0);
        $table->decimal('discount_amount', 12, 2)->default(0);
        $table->decimal('tax', 12, 2)->default(0);
        $table->decimal('total', 12, 2)->default(0);
        $table->decimal('paid_amount', 12, 2)->default(0);
        $table->text('notes')->nullable();
        $table->timestamp('issued_at')->nullable();
        $table->timestamp('due_at')->nullable();
        $table->timestamps();
        $table->softDeletes();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_invoices');
    }
};

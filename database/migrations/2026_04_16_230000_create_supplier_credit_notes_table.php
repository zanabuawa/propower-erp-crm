<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_credit_notes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('purchase_invoice_id')->nullable(); // factura a la que aplica
            $table->unsignedBigInteger('supplier_id');
            $table->unsignedBigInteger('created_by')->nullable();

            $table->string('folio', 30)->unique();
            $table->string('supplier_credit_note_number', 100)->nullable(); // N° del proveedor
            $table->string('currency', 3)->default('MXN');

            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('tax', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            $table->decimal('applied_amount', 14, 2)->default(0); // monto ya aplicado

            $table->enum('status', ['draft', 'applied', 'partial', 'cancelled'])->default('draft');
            $table->enum('reason', ['return', 'price_adjustment', 'duplicate', 'error', 'other'])->default('other');

            $table->text('notes')->nullable();
            $table->date('issued_at');
            $table->timestamp('applied_at')->nullable();

            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('purchase_invoice_id')->references('id')->on('purchase_invoices')->nullOnDelete();
            $table->foreign('supplier_id')->references('id')->on('suppliers');
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('supplier_credit_note_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_credit_note_id');
            $table->unsignedBigInteger('product_id')->nullable();

            $table->string('description', 255);
            $table->decimal('quantity', 12, 3)->default(1);
            $table->decimal('unit_price', 14, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(16);
            $table->decimal('subtotal', 14, 2)->default(0);

            $table->foreign('supplier_credit_note_id')->references('id')->on('supplier_credit_notes')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_credit_note_items');
        Schema::dropIfExists('supplier_credit_notes');
    }
};

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
    Schema::create('sale_quotations', function (Blueprint $table) {
        $table->id();
        $table->foreignId('company_id')->constrained()->cascadeOnDelete();
        $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
        $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
        $table->foreignId('price_list_id')->nullable()->constrained()->nullOnDelete();
        $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
        $table->string('folio')->nullable();
        $table->enum('currency', ['MXN', 'USD'])->default('MXN');
        $table->enum('status', ['draft', 'sent', 'accepted', 'rejected', 'expired'])->default('draft');
        $table->decimal('subtotal', 12, 2)->default(0);
        $table->decimal('discount_amount', 12, 2)->default(0);
        $table->decimal('tax', 12, 2)->default(0);
        $table->decimal('total', 12, 2)->default(0);
        $table->integer('valid_days')->default(15);
        $table->date('valid_until')->nullable();
        $table->text('notes')->nullable();
        $table->text('terms')->nullable();
        $table->timestamps();
        $table->softDeletes();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_quotations');
    }
};

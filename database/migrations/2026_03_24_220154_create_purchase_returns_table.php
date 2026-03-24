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
    Schema::create('purchase_returns', function (Blueprint $table) {
        $table->id();
        $table->foreignId('company_id')->constrained()->cascadeOnDelete();
        $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
        $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
        $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
        $table->string('folio')->nullable();
        $table->enum('status', ['draft', 'sent', 'confirmed', 'cancelled'])->default('draft');
        $table->text('reason')->nullable();
        $table->decimal('total', 12, 2)->default(0);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_returns');
    }
};

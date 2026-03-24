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
    Schema::create('purchase_requisitions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('company_id')->constrained()->cascadeOnDelete();
        $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
        $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
        $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
        $table->string('folio')->nullable();
        $table->enum('currency', ['MXN', 'USD'])->default('MXN');
        $table->enum('status', [
            'draft',
            'pending_quote',
            'quoted',
            'pending_approval',
            'approved',
            'rejected',
            'cancelled',
        ])->default('draft');
        $table->text('justification')->nullable();
        $table->text('quote_response')->nullable();
        $table->decimal('quoted_amount', 12, 2)->default(0);
        $table->timestamp('needed_by')->nullable();
        $table->timestamp('quoted_at')->nullable();
        $table->timestamps();
        $table->softDeletes();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_requisitions');
    }
};

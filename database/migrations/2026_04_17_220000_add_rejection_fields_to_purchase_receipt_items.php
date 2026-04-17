<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_receipt_items', function (Blueprint $table) {
            $table->decimal('quantity_rejected', 10, 2)->default(0)->after('quantity_received');
            $table->string('rejection_reason')->nullable()->after('quantity_rejected');
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete()->after('rejection_reason');
            $table->timestamp('rejected_at')->nullable()->after('rejected_by');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_receipt_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('rejected_by');
            $table->dropColumn(['quantity_rejected', 'rejection_reason', 'rejected_at']);
        });
    }
};

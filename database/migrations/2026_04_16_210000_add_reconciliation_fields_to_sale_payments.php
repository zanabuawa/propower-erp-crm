<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_payments', function (Blueprint $table) {
            $table->timestamp('reconciled_at')->nullable()->after('paid_at');
            $table->foreignId('reconciled_by')->nullable()->after('reconciled_at')
                ->constrained('users')->nullOnDelete();
            $table->text('reconciliation_note')->nullable()->after('reconciled_by');
        });
    }

    public function down(): void
    {
        Schema::table('sale_payments', function (Blueprint $table) {
            $table->dropForeign(['reconciled_by']);
            $table->dropColumn(['reconciled_at', 'reconciled_by', 'reconciliation_note']);
        });
    }
};

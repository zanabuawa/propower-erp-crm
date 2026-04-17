<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            // Motivo del ajuste (solo aplica cuando type = 'adjustment')
            $table->string('adjustment_reason')->nullable()->after('notes');
            // Aprobación
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete()->after('adjustment_reason');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            // Afectación contable
            $table->foreignId('finance_account_id')->nullable()->constrained('finance_accounts')->nullOnDelete()->after('approved_at');
            $table->foreignId('finance_transaction_id')->nullable()->constrained('finance_transactions')->nullOnDelete()->after('finance_account_id');
        });
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['finance_account_id']);
            $table->dropForeign(['finance_transaction_id']);
            $table->dropColumn([
                'adjustment_reason', 'approved_by', 'approved_at',
                'finance_account_id', 'finance_transaction_id',
            ]);
        });
    }
};

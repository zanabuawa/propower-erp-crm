<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supplier_payments', function (Blueprint $table) {
            $table->timestamp('reconciled_at')->nullable()->after('paid_at');
            $table->unsignedBigInteger('reconciled_by')->nullable()->after('reconciled_at');
            $table->text('reconciliation_note')->nullable()->after('reconciled_by');

            $table->foreign('reconciled_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('supplier_payments', function (Blueprint $table) {
            $table->dropForeign(['reconciled_by']);
            $table->dropColumn(['reconciled_at', 'reconciled_by', 'reconciliation_note']);
        });
    }
};

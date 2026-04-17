<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Historial diario de saldo por cuenta
        Schema::create('finance_daily_balances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('finance_account_id');
            $table->date('balance_date');
            $table->decimal('opening_balance', 14, 2)->default(0);
            $table->decimal('total_income',    14, 2)->default(0);
            $table->decimal('total_expense',   14, 2)->default(0);
            $table->decimal('closing_balance', 14, 2)->default(0);
            $table->unsignedSmallInteger('transaction_count')->default(0);

            $table->foreign('finance_account_id')->references('id')->on('finance_accounts')->cascadeOnDelete();
            $table->unique(['finance_account_id', 'balance_date']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_daily_balances');
    }
};

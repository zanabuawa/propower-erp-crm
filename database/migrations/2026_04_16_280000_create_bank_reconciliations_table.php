<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cabecera del proceso de conciliación (una por período/cuenta)
        Schema::create('bank_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('finance_account_id');
            $table->unsignedBigInteger('created_by')->nullable();

            $table->string('folio', 30)->unique();
            $table->date('period_from');
            $table->date('period_to');
            $table->enum('status', ['draft', 'reviewed', 'closed'])->default('draft');

            $table->decimal('bank_opening_balance', 14, 2)->default(0);
            $table->decimal('bank_closing_balance', 14, 2)->default(0);
            $table->decimal('book_opening_balance', 14, 2)->default(0);
            $table->decimal('book_closing_balance', 14, 2)->default(0);
            $table->decimal('difference',           14, 2)->default(0);

            $table->text('notes')->nullable();
            $table->timestamp('closed_at')->nullable();

            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('finance_account_id')->references('id')->on('finance_accounts');
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();

            $table->timestamps();
        });

        // Líneas del estado de cuenta bancario importado
        Schema::create('bank_statement_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bank_reconciliation_id');
            $table->unsignedBigInteger('finance_transaction_id')->nullable(); // match interno

            $table->date('transaction_date');
            $table->string('description', 500)->nullable();
            $table->string('reference', 150)->nullable();
            $table->decimal('amount', 14, 2);          // positivo = crédito, negativo = débito
            $table->decimal('balance', 14, 2)->default(0);
            $table->enum('flow', ['credit', 'debit']);
            $table->enum('match_status', ['unmatched', 'matched', 'manual'])->default('unmatched');

            $table->foreign('bank_reconciliation_id')->references('id')->on('bank_reconciliations')->cascadeOnDelete();
            $table->foreign('finance_transaction_id')->references('id')->on('finance_transactions')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_statement_lines');
        Schema::dropIfExists('bank_reconciliations');
    }
};

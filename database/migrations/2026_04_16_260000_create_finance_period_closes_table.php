<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_period_closes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('closed_by')->nullable();
            $table->unsignedBigInteger('reopened_by')->nullable();

            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');   // 1-12
            $table->string('period_label', 20);     // "Abril 2026"

            $table->enum('status', ['open', 'reviewing', 'closed'])->default('open');

            // Saldos al cierre
            $table->decimal('total_income', 14, 2)->default(0);
            $table->decimal('total_expense', 14, 2)->default(0);
            $table->decimal('net_result', 14, 2)->default(0);
            $table->decimal('opening_cash', 14, 2)->default(0);
            $table->decimal('closing_cash', 14, 2)->default(0);

            // Checklist items en JSON: [{ key, label, done, done_at, done_by }]
            $table->json('checklist')->nullable();

            $table->text('notes')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('reopened_at')->nullable();

            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('closed_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('reopened_by')->references('id')->on('users')->nullOnDelete();

            $table->unique(['company_id', 'year', 'month']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_period_closes');
    }
};

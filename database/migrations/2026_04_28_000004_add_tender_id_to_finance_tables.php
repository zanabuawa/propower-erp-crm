<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('finance_transactions', function (Blueprint $table) {
            $table->foreignId('tender_id')->nullable()->after('project_id')
                ->constrained('tenders')->nullOnDelete();
            $table->foreignId('libranza_id')->nullable()->after('tender_id')
                ->constrained('work_libranzas')->nullOnDelete();
        });

        Schema::table('finance_cashflow', function (Blueprint $table) {
            $table->foreignId('tender_id')->nullable()->after('project_id')
                ->constrained('tenders')->nullOnDelete();
            $table->foreignId('libranza_id')->nullable()->after('tender_id')
                ->constrained('work_libranzas')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('finance_transactions', function (Blueprint $table) {
            $table->dropForeign(['tender_id']);
            $table->dropForeign(['libranza_id']);
            $table->dropColumn(['tender_id', 'libranza_id']);
        });

        Schema::table('finance_cashflow', function (Blueprint $table) {
            $table->dropForeign(['tender_id']);
            $table->dropForeign(['libranza_id']);
            $table->dropColumn(['tender_id', 'libranza_id']);
        });
    }
};

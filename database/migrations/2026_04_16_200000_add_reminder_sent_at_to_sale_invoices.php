<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_invoices', function (Blueprint $table) {
            $table->timestamp('reminder_sent_at')->nullable()->after('due_at');
            $table->unsignedSmallInteger('reminder_count')->default(0)->after('reminder_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('sale_invoices', function (Blueprint $table) {
            $table->dropColumn(['reminder_sent_at', 'reminder_count']);
        });
    }
};

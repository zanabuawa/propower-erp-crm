<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_invoices', function (Blueprint $table) {
            $table->decimal('global_discount_pct', 8, 4)->default(0)->after('discount_amount');
            $table->string('approval_status')->nullable()->after('status');
            $table->unsignedBigInteger('approval_id')->nullable()->after('approval_status');
        });
    }

    public function down(): void
    {
        Schema::table('sale_invoices', function (Blueprint $table) {
            $table->dropColumn(['global_discount_pct', 'approval_status', 'approval_id']);
        });
    }
};

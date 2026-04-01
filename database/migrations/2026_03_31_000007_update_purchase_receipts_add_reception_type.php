<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_receipts', function (Blueprint $table) {
            $table->enum('reception_type', ['purchase', 'return', 'transfer', 'defective'])
                  ->default('purchase')
                  ->after('status');
            $table->decimal('operating_expenses', 12, 2)->default(0)->after('reception_type');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_receipts', function (Blueprint $table) {
            $table->dropColumn(['reception_type', 'operating_expenses']);
        });
    }
};

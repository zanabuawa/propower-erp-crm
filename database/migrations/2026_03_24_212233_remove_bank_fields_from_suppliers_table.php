<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::table('suppliers', function (Blueprint $table) {
        $table->dropColumn(['bank_name', 'bank_account', 'bank_clabe']);
    });
}

public function down(): void
{
    Schema::table('suppliers', function (Blueprint $table) {
        $table->string('bank_name')->nullable();
        $table->string('bank_account')->nullable();
        $table->string('bank_clabe', 18)->nullable();
    });
}
};

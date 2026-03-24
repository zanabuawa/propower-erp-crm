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
    Schema::create('purchase_settings', function (Blueprint $table) {
        $table->id();
        $table->foreignId('company_id')->unique()->constrained()->cascadeOnDelete();
        $table->enum('currency', ['MXN', 'USD'])->default('MXN');
        $table->decimal('level1_amount', 12, 2)->default(2000);
        $table->decimal('level2_amount', 12, 2)->default(10000);
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_settings');
    }
};

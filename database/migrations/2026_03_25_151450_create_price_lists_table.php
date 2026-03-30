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
    Schema::create('price_lists', function (Blueprint $table) {
        $table->id();
        $table->foreignId('company_id')->constrained()->cascadeOnDelete();
        $table->string('name');
        $table->enum('currency', ['MXN', 'USD'])->default('MXN');
        $table->boolean('is_default')->default(false);
        $table->boolean('is_active')->default(true);
        $table->date('valid_from')->nullable();
        $table->date('valid_to')->nullable();
        $table->timestamps();
    });

    Schema::create('customer_price_lists', function (Blueprint $table) {
        $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
        $table->foreignId('price_list_id')->constrained()->cascadeOnDelete();
        $table->primary(['customer_id', 'price_list_id']);
    });
}

public function down(): void
{
    Schema::dropIfExists('customer_price_lists');
    Schema::dropIfExists('price_lists');
}
};

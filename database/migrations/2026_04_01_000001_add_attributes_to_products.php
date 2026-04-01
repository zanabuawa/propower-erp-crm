<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('subcategory_id')
                  ->nullable()
                  ->after('category_id')
                  ->constrained('categories')
                  ->nullOnDelete();
            $table->string('brand', 100)->nullable()->after('description');
            $table->string('model', 100)->nullable()->after('brand');
            $table->string('color', 60)->nullable()->after('model');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['subcategory_id']);
            $table->dropColumn(['subcategory_id', 'brand', 'model', 'color']);
        });
    }
};

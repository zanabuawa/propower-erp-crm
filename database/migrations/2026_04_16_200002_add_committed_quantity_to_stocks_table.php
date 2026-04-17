<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            // Cantidad reservada por órdenes confirmadas aún no entregadas
            $table->decimal('committed_quantity', 10, 4)->default(0)->after('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->dropColumn('committed_quantity');
        });
    }
};

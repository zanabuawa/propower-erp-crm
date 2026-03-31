<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_contacts', function (Blueprint $table) {
            $table->renameColumn('last_name', 'paternal_surname');
        });

        Schema::table('customer_contacts', function (Blueprint $table) {
            $table->string('maternal_surname', 100)->nullable()->after('paternal_surname');
            $table->string('alias', 100)->nullable()->after('first_name');
        });
    }

    public function down(): void
    {
        Schema::table('customer_contacts', function (Blueprint $table) {
            $table->dropColumn(['maternal_surname', 'alias']);
        });

        Schema::table('customer_contacts', function (Blueprint $table) {
            $table->renameColumn('paternal_surname', 'last_name');
        });
    }
};

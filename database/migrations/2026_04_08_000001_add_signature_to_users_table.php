<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->longText('signature')->nullable()->after('avatar');           // base64 PNG
            $table->timestamp('signature_updated_at')->nullable()->after('signature');
        });

        Schema::table('purchase_quotation_approvals', function (Blueprint $table) {
            $table->string('signature_hash', 64)->nullable()->after('signature'); // HMAC-SHA256
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['signature', 'signature_updated_at']);
        });

        Schema::table('purchase_quotation_approvals', function (Blueprint $table) {
            $table->dropColumn('signature_hash');
        });
    }
};

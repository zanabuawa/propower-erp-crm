<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cambiar status de ENUM a VARCHAR para poder agregar nuevos estados sin re-migrar
        DB::statement("
            ALTER TABLE purchase_requisitions
            MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'submitted'
        ");

        Schema::table('purchase_requisitions', function (Blueprint $table) {
            $table->text('requester_notes')->nullable()->after('quote_response');
            $table->text('reject_reason')->nullable()->after('requester_notes');
            $table->foreignId('rejected_by')->nullable()->after('reviewed_by')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable()->after('quoted_at');
            $table->timestamp('confirmed_at')->nullable()->after('submitted_at');
            $table->timestamp('rejected_at')->nullable()->after('confirmed_at');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_requisitions', function (Blueprint $table) {
            $table->dropForeign(['rejected_by']);
            $table->dropColumn([
                'requester_notes', 'reject_reason', 'rejected_by',
                'submitted_at', 'confirmed_at', 'rejected_at',
            ]);
        });

        DB::statement("
            ALTER TABLE purchase_requisitions
            MODIFY COLUMN status ENUM('draft','pending_quote','quoted','pending_approval','approved','rejected','cancelled')
            NOT NULL DEFAULT 'draft'
        ");
    }
};

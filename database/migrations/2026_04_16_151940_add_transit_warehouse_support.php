<?php

use App\Models\Company;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Make branch_id nullable so the transit warehouse is not tied to a branch
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->foreignId('branch_id')->nullable()->change();
            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
            $table->boolean('is_transit')->default(false)->after('is_defective');
        });

        // Create one transit warehouse per existing company
        foreach (Company::all() as $company) {
            \App\Models\Warehouse::firstOrCreate(
                ['company_id' => $company->id, 'is_transit' => true],
                [
                    'name'      => 'Almacén de Transferencias',
                    'code'      => 'TRANSIT',
                    'is_active' => true,
                ]
            );
        }
    }

    public function down(): void
    {
        \App\Models\Warehouse::where('is_transit', true)->delete();

        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->foreignId('branch_id')->nullable(false)->change();
            $table->foreign('branch_id')->references('id')->on('branches')->cascadeOnDelete();
            $table->dropColumn('is_transit');
        });
    }
};

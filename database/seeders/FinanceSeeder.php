<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use App\Models\FinanceAccount;
use App\Models\FinanceBudget;
use Illuminate\Database\Seeder;

class FinanceSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        $matriz  = Branch::where('code', 'MAT')->first();

        // ── Cuentas financieras ──────────────────────────────────────────────
        $accounts = [
            [
                'code'            => 'CAJ-001',
                'name'            => 'Caja General',
                'type'            => 'caja',
                'currency'        => 'MXN',
                'opening_balance' => 25000.00,
                'current_balance' => 25000.00,
                'notes'           => 'Efectivo en caja de la sucursal matriz.',
            ],
            [
                'code'            => 'BNK-BBVA',
                'name'            => 'Banco BBVA — Cuenta Corriente',
                'type'            => 'banco',
                'bank_name'       => 'BBVA México',
                'account_number'  => '0123456789',
                'clabe'           => '012680012345678901',
                'currency'        => 'MXN',
                'opening_balance' => 380000.00,
                'current_balance' => 380000.00,
                'notes'           => 'Cuenta principal de operaciones.',
            ],
            [
                'code'            => 'BNK-BANAMEX',
                'name'            => 'Banco Banamex — Cuenta Empresarial',
                'type'            => 'banco',
                'bank_name'       => 'Citibanamex',
                'account_number'  => '9876543210',
                'clabe'           => '002680098765432101',
                'currency'        => 'MXN',
                'opening_balance' => 120000.00,
                'current_balance' => 120000.00,
                'notes'           => 'Cuenta secundaria para nómina y gastos fijos.',
            ],
            [
                'code'            => 'BNK-USD',
                'name'            => 'Cuenta Dólares BBVA',
                'type'            => 'banco',
                'bank_name'       => 'BBVA México',
                'account_number'  => '5555666677',
                'currency'        => 'USD',
                'opening_balance' => 5000.00,
                'current_balance' => 5000.00,
                'notes'           => 'Cuenta en dólares para compras de importación.',
            ],
            [
                'code'            => 'CXC-001',
                'name'            => 'Cuentas por Cobrar Clientes',
                'type'            => 'otro',
                'currency'        => 'MXN',
                'opening_balance' => 0.00,
                'current_balance' => 0.00,
            ],
            [
                'code'            => 'CXP-001',
                'name'            => 'Cuentas por Pagar Proveedores',
                'type'            => 'otro',
                'currency'        => 'MXN',
                'opening_balance' => 0.00,
                'current_balance' => 0.00,
            ],
        ];

        foreach ($accounts as $data) {
            FinanceAccount::firstOrCreate(
                ['company_id' => $company->id, 'code' => $data['code']],
                array_merge($data, [
                    'company_id' => $company->id,
                    'branch_id'  => $matriz?->id,
                    'is_active'  => true,
                ])
            );
        }

        // ── Presupuestos ─────────────────────────────────────────────────────
        $budgets = [
            [
                'name'           => 'Presupuesto Operativo 2026',
                'period_type'    => 'anual',
                'year'           => 2026,
                'period_number'  => null,
                'category'       => 'egresos',
                'amount_planned' => 1500000.00,
                'amount_actual'  => 0.00,
                'status'         => 'aprobado',
                'notes'          => 'Presupuesto de gastos operativos para el ejercicio 2026.',
            ],
            [
                'name'           => 'Presupuesto Compras Q2 2026',
                'period_type'    => 'trimestral',
                'year'           => 2026,
                'period_number'  => 2,
                'category'       => 'egresos',
                'amount_planned' => 450000.00,
                'amount_actual'  => 0.00,
                'status'         => 'aprobado',
                'notes'          => 'Presupuesto para adquisición de materiales segundo trimestre.',
            ],
        ];

        foreach ($budgets as $data) {
            FinanceBudget::firstOrCreate(
                ['company_id' => $company->id, 'name' => $data['name']],
                array_merge($data, ['company_id' => $company->id])
            );
        }
    }
}

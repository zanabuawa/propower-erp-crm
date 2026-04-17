<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use App\Models\HrDepartment;
use App\Models\HrEmployee;
use App\Models\HrPosition;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class HrSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        if (!$company) {
            $this->call(CompanySeeder::class);
            $company = Company::first();
        }

        $branch = Branch::where('company_id', $company->id)->first();

        // 1. Departamentos
        $depts = [
            ['name' => 'Dirección General', 'code' => 'DIR', 'description' => 'Alta dirección'],
            ['name' => 'Administración y Finanzas', 'code' => 'ADM', 'description' => 'Gestión administrativa'],
            ['name' => 'Recursos Humanos', 'code' => 'RH', 'description' => 'Gestión de talento humano'],
            ['name' => 'Ventas y Mercadotecnia', 'code' => 'VTA', 'description' => 'Comercialización'],
            ['name' => 'Operaciones / Almacén', 'code' => 'OPS', 'description' => 'Logística y almacén'],
        ];

        $createdDepts = [];
        foreach ($depts as $dept) {
            $createdDepts[$dept['code']] = HrDepartment::firstOrCreate(
                ['company_id' => $company->id, 'code' => $dept['code']],
                ['name' => $dept['name'], 'description' => $dept['description']]
            );
        }

        // 2. Puestos
        $positions = [
            ['dept' => 'DIR', 'name' => 'Director General', 'code' => 'DIR-01', 'salary_type' => 'monthly', 'min' => 45000, 'max' => 80000],
            ['dept' => 'ADM', 'name' => 'Contador General', 'code' => 'ADM-01', 'salary_type' => 'monthly', 'min' => 20000, 'max' => 35000],
            ['dept' => 'ADM', 'name' => 'Auxiliar Contable', 'code' => 'ADM-02', 'salary_type' => 'monthly', 'min' => 12000, 'max' => 18000],
            ['dept' => 'RH',  'name' => 'Gerente de RH', 'code' => 'RH-01', 'salary_type' => 'monthly', 'min' => 25000, 'max' => 40000],
            ['dept' => 'RH',  'name' => 'Asistente de RH', 'code' => 'RH-02', 'salary_type' => 'monthly', 'min' => 10000, 'max' => 15000],
            ['dept' => 'VTA', 'name' => 'Gerente de Ventas', 'code' => 'VTA-01', 'salary_type' => 'monthly', 'min' => 25000, 'max' => 45000],
            ['dept' => 'VTA', 'name' => 'Ejecutivo de Ventas', 'code' => 'VTA-02', 'salary_type' => 'monthly', 'min' => 8000, 'max' => 25000],
            ['dept' => 'OPS', 'name' => 'Jefe de Almacén', 'code' => 'OPS-01', 'salary_type' => 'monthly', 'min' => 15000, 'max' => 22000],
            ['dept' => 'OPS', 'name' => 'Almacenista', 'code' => 'OPS-02', 'salary_type' => 'monthly', 'min' => 8000, 'max' => 12000],
        ];

        $createdPositions = [];
        foreach ($positions as $pos) {
            $createdPositions[$pos['code']] = HrPosition::firstOrCreate(
                ['company_id' => $company->id, 'code' => $pos['code']],
                [
                    'department_id' => $createdDepts[$pos['dept']]->id,
                    'name' => $pos['name'],
                    'salary_type' => $pos['salary_type'],
                    'min_salary' => $pos['min'],
                    'max_salary' => $pos['max'],
                    'authorized_headcount' => 5,
                ]
            );
        }

        // 3. Empleados vinculados a usuarios existentes
        $userMappings = [
            'gerente@miempresa.com'   => ['code' => 'DIR-01', 'num' => 'EMP-001'],
            'comprador@miempresa.com' => ['code' => 'ADM-01', 'num' => 'EMP-002'],
            'vendedor@miempresa.com'  => ['code' => 'VTA-02', 'num' => 'EMP-003'],
            'vendedor2@miempresa.com' => ['code' => 'VTA-02', 'num' => 'EMP-004'],
            'almacen@miempresa.com'   => ['code' => 'OPS-02', 'num' => 'EMP-005'],
        ];

        foreach ($userMappings as $email => $data) {
            $user = User::where('email', $email)->first();
            if ($user) {
                $pos = $createdPositions[$data['code']];
                
                // Dividir el nombre
                $nameParts = explode(' ', $user->name);
                $firstName = $nameParts[0];
                $lastName = $nameParts[1] ?? 'Apellido';
                $secondLastName = $nameParts[2] ?? null;

                HrEmployee::firstOrCreate(
                    ['company_id' => $company->id, 'employee_number' => $data['num']],
                    [
                        'user_id' => $user->id,
                        'branch_id' => $user->branch_id ?? $branch->id,
                        'department_id' => $pos->department_id,
                        'position_id' => $pos->id,
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'second_last_name' => $secondLastName,
                        'email' => $user->email,
                        'hire_date' => now()->subYears(rand(1, 5)),
                        'salary' => $pos->min_salary + 5000,
                        'status' => 'active',
                    ]
                );
            }
        }

        // 4. Algunos empleados extra sin usuario (para relleno)
        $extras = [
            ['first' => 'Juan', 'last' => 'Pérez', 'pos' => 'OPS-02', 'num' => 'EMP-006'],
            ['first' => 'María', 'last' => 'García', 'pos' => 'RH-01', 'num' => 'EMP-007'],
            ['first' => 'Ricardo', 'last' => 'Sánchez', 'pos' => 'RH-02', 'num' => 'EMP-008'],
        ];

        foreach ($extras as $extra) {
            $pos = $createdPositions[$extra['pos']];
            HrEmployee::firstOrCreate(
                ['company_id' => $company->id, 'employee_number' => $extra['num']],
                [
                    'branch_id' => $branch->id,
                    'department_id' => $pos->department_id,
                    'position_id' => $pos->id,
                    'first_name' => $extra['first'],
                    'last_name' => $extra['last'],
                    'email' => Str::lower($extra['first']) . '.' . Str::lower($extra['last']) . '@miempresa.com',
                    'hire_date' => now()->subMonths(rand(1, 24)),
                    'salary' => $pos->min_salary,
                    'status' => 'active',
                ]
            );
        }
    }
}

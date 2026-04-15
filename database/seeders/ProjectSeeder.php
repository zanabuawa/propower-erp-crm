<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Project;
use App\Models\ProjectExpense;
use App\Models\ProjectMilestone;
use App\Models\ProjectTask;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $branch   = Branch::where('code', 'MAT')->first();
        $gerente  = User::where('email', 'gerente@miempresa.com')->first();
        $vendedor = User::where('email', 'vendedor@miempresa.com')->first();
        $empleado = User::where('email', 'empleado@miempresa.com')->first();
        $cliente1 = Customer::where('rfc', 'CPH850215FFF')->first(); // Constructora Pérez
        $cliente2 = Customer::where('rfc', 'PIC981105JJJ')->first(); // Planta Industrial Chihuahua

        // ── Proyecto 1: Instalación eléctrica planta industrial (activo) ──────
        if (! Project::where('code', 'PROY-000001')->exists()) {
            $project1 = Project::create([
                'branch_id'           => $branch?->id,
                'customer_id'         => $cliente2?->id,
                'responsible_user_id' => $gerente->id,
                'code'                => 'PROY-000001',
                'name'                => 'Instalación Eléctrica Planta Industrial Chihuahua',
                'description'         => 'Instalación del sistema eléctrico completo para la nueva nave industrial. Incluye tableros, canalizaciones, circuitos de fuerza y alumbrado.',
                'type'                => 'externo',
                'status'              => 'activo',
                'start_date'          => now()->subDays(30)->toDateString(),
                'end_date'            => now()->addDays(45)->toDateString(),
                'budget'              => 280000.00,
                'cost_actual'         => 95400.00,
                'progress'            => 35,
                'currency'            => 'MXN',
                'notes'               => 'Cliente requiere certificación NOM-001-SEDE-2012 al finalizar.',
            ]);

            // Hitos
            $m1 = ProjectMilestone::create([
                'project_id'     => $project1->id,
                'name'           => 'Acometida y tablero principal',
                'description'    => 'Instalación de acometida desde CFE, tablero principal y medidor.',
                'due_date'       => now()->subDays(10)->toDateString(),
                'completed_at'   => now()->subDays(8)->toDateString(),
                'status'         => 'completado',
                'payment_amount' => 56000.00,
                'sort_order'     => 1,
            ]);

            ProjectMilestone::create([
                'project_id'     => $project1->id,
                'name'           => 'Canalizaciones y cableado',
                'description'    => 'Instalación de conduit EMT, bandejas y cableado de circuitos.',
                'due_date'       => now()->addDays(15)->toDateString(),
                'status'         => 'pendiente',
                'payment_amount' => 84000.00,
                'sort_order'     => 2,
            ]);

            ProjectMilestone::create([
                'project_id'     => $project1->id,
                'name'           => 'Tableros secundarios y centros de carga',
                'description'    => 'Montaje e interconexión de tableros secundarios en cada zona.',
                'due_date'       => now()->addDays(30)->toDateString(),
                'status'         => 'pendiente',
                'payment_amount' => 70000.00,
                'sort_order'     => 3,
            ]);

            ProjectMilestone::create([
                'project_id'     => $project1->id,
                'name'           => 'Pruebas y entrega final',
                'description'    => 'Pruebas de aislamiento, puesta en marcha y entrega de planos finales.',
                'due_date'       => now()->addDays(45)->toDateString(),
                'status'         => 'pendiente',
                'payment_amount' => 70000.00,
                'sort_order'     => 4,
            ]);

            // Tareas
            $t1 = ProjectTask::create([
                'project_id'      => $project1->id,
                'title'           => 'Levantamiento topográfico y diseño',
                'description'     => 'Medición de áreas, trayectorias y diseño del diagrama unifilar.',
                'status'          => 'completada',
                'priority'        => 'alta',
                'assigned_to'     => $gerente->id,
                'due_date'        => now()->subDays(25)->toDateString(),
                'completed_at'    => now()->subDays(23)->toDateString(),
                'progress'        => 100,
                'estimated_hours' => 16.00,
                'actual_hours'    => 18.00,
                'sort_order'      => 1,
            ]);

            $t2 = ProjectTask::create([
                'project_id'      => $project1->id,
                'title'           => 'Instalación acometida y tablero principal',
                'description'     => 'Obra civil para entrada, montaje de tablero 400A y medidor.',
                'status'          => 'completada',
                'priority'        => 'alta',
                'assigned_to'     => $empleado->id,
                'due_date'        => now()->subDays(10)->toDateString(),
                'completed_at'    => now()->subDays(8)->toDateString(),
                'progress'        => 100,
                'estimated_hours' => 24.00,
                'actual_hours'    => 26.00,
                'sort_order'      => 2,
            ]);

            $t3 = ProjectTask::create([
                'project_id'      => $project1->id,
                'title'           => 'Instalación de conduit EMT zona A',
                'description'     => 'Montaje de 300 mt de conduit EMT 3/4" en zona de producción A.',
                'status'          => 'en_progreso',
                'priority'        => 'alta',
                'assigned_to'     => $empleado->id,
                'due_date'        => now()->addDays(7)->toDateString(),
                'progress'        => 60,
                'estimated_hours' => 32.00,
                'actual_hours'    => 20.00,
                'sort_order'      => 3,
            ]);

            ProjectTask::create([
                'project_id'      => $project1->id,
                'title'           => 'Instalación de conduit EMT zona B',
                'description'     => 'Montaje de 180 mt de conduit EMT 3/4" en zona de almacén.',
                'status'          => 'pendiente',
                'priority'        => 'media',
                'assigned_to'     => $empleado->id,
                'due_date'        => now()->addDays(15)->toDateString(),
                'progress'        => 0,
                'estimated_hours' => 20.00,
                'actual_hours'    => 0,
                'sort_order'      => 4,
            ]);

            ProjectTask::create([
                'project_id'      => $project1->id,
                'title'           => 'Cableado circuitos de fuerza',
                'status'          => 'pendiente',
                'priority'        => 'alta',
                'assigned_to'     => $empleado->id,
                'due_date'        => now()->addDays(22)->toDateString(),
                'progress'        => 0,
                'estimated_hours' => 40.00,
                'actual_hours'    => 0,
                'sort_order'      => 5,
            ]);

            ProjectTask::create([
                'project_id'      => $project1->id,
                'title'           => 'Pruebas de continuidad y aislamiento',
                'status'          => 'pendiente',
                'priority'        => 'alta',
                'assigned_to'     => $gerente->id,
                'due_date'        => now()->addDays(42)->toDateString(),
                'progress'        => 0,
                'estimated_hours' => 12.00,
                'actual_hours'    => 0,
                'sort_order'      => 6,
            ]);

            // Gastos
            ProjectExpense::create([
                'project_id'    => $project1->id,
                'task_id'       => $t2->id,
                'registered_by' => $gerente->id,
                'concept'       => 'Renta de grúa pluma para instalación tablero principal',
                'category'      => 'subcontrato',
                'amount'        => 4500.00,
                'currency'      => 'MXN',
                'expense_date'  => now()->subDays(9)->toDateString(),
                'reference'     => 'FAC-RENTA-001',
                'status'        => 'aprobado',
            ]);

            ProjectExpense::create([
                'project_id'    => $project1->id,
                'task_id'       => $t3->id,
                'registered_by' => $empleado->id,
                'concept'       => 'Transporte de materiales a obra',
                'category'      => 'transporte',
                'amount'        => 1200.00,
                'currency'      => 'MXN',
                'expense_date'  => now()->subDays(5)->toDateString(),
                'reference'     => 'REMISION-007',
                'status'        => 'aprobado',
            ]);

            // Miembros
            $project1->members()->syncWithoutDetaching([
                $gerente->id  => ['role' => 'lider',       'is_active' => true, 'joined_at' => now()->subDays(31)],
                $empleado->id => ['role' => 'otro',        'is_active' => true, 'joined_at' => now()->subDays(31)],
                $vendedor->id => ['role' => 'observador',  'is_active' => true, 'joined_at' => now()->subDays(31)],
            ]);
        }

        // ── Proyecto 2: Mantenimiento preventivo anual (completado) ──────────
        if (! Project::where('code', 'PROY-000002')->exists()) {
            $project2 = Project::create([
                'branch_id'           => $branch?->id,
                'customer_id'         => $cliente1?->id,
                'responsible_user_id' => $gerente->id,
                'code'                => 'PROY-000002',
                'name'                => 'Mantenimiento Preventivo Eléctrico — Constructora Pérez',
                'description'         => 'Revisión y mantenimiento preventivo del sistema eléctrico de las instalaciones de la constructora.',
                'type'                => 'externo',
                'status'              => 'completado',
                'start_date'          => now()->subDays(60)->toDateString(),
                'end_date'            => now()->subDays(50)->toDateString(),
                'budget'              => 18000.00,
                'cost_actual'         => 16800.00,
                'progress'            => 100,
                'currency'            => 'MXN',
                'notes'               => 'Proyecto completado satisfactoriamente. Cliente solicitó cotización para siguiente año.',
            ]);

            ProjectMilestone::create([
                'project_id'     => $project2->id,
                'name'           => 'Diagnóstico inicial',
                'due_date'       => now()->subDays(58)->toDateString(),
                'completed_at'   => now()->subDays(58)->toDateString(),
                'status'         => 'completado',
                'payment_amount' => 3600.00,
                'sort_order'     => 1,
            ]);

            ProjectMilestone::create([
                'project_id'     => $project2->id,
                'name'           => 'Ejecución y entrega',
                'due_date'       => now()->subDays(50)->toDateString(),
                'completed_at'   => now()->subDays(50)->toDateString(),
                'status'         => 'completado',
                'payment_amount' => 14400.00,
                'sort_order'     => 2,
            ]);

            $pt1 = ProjectTask::create([
                'project_id'      => $project2->id,
                'title'           => 'Diagnóstico de instalaciones',
                'status'          => 'completada',
                'priority'        => 'media',
                'assigned_to'     => $empleado->id,
                'due_date'        => now()->subDays(58)->toDateString(),
                'completed_at'    => now()->subDays(58)->toDateString(),
                'progress'        => 100,
                'estimated_hours' => 8.00,
                'actual_hours'    => 9.00,
                'sort_order'      => 1,
            ]);

            $pt2 = ProjectTask::create([
                'project_id'      => $project2->id,
                'title'           => 'Mantenimiento tableros y conexiones',
                'status'          => 'completada',
                'priority'        => 'alta',
                'assigned_to'     => $empleado->id,
                'due_date'        => now()->subDays(52)->toDateString(),
                'completed_at'    => now()->subDays(51)->toDateString(),
                'progress'        => 100,
                'estimated_hours' => 16.00,
                'actual_hours'    => 15.00,
                'sort_order'      => 2,
            ]);

            ProjectTask::create([
                'project_id'      => $project2->id,
                'title'           => 'Pruebas de tierras físicas y entrega',
                'status'          => 'completada',
                'priority'        => 'alta',
                'assigned_to'     => $gerente->id,
                'due_date'        => now()->subDays(50)->toDateString(),
                'completed_at'    => now()->subDays(50)->toDateString(),
                'progress'        => 100,
                'estimated_hours' => 8.00,
                'actual_hours'    => 8.00,
                'sort_order'      => 3,
            ]);

            ProjectExpense::create([
                'project_id'    => $project2->id,
                'task_id'       => $pt2->id,
                'registered_by' => $empleado->id,
                'concept'       => 'Materiales consumibles (terminales, cintillas, limpiadores)',
                'category'      => 'material',
                'amount'        => 1800.00,
                'currency'      => 'MXN',
                'expense_date'  => now()->subDays(55)->toDateString(),
                'status'        => 'aprobado',
            ]);

            $project2->members()->syncWithoutDetaching([
                $gerente->id  => ['role' => 'lider', 'is_active' => false, 'joined_at' => now()->subDays(61), 'left_at' => now()->subDays(49)],
                $empleado->id => ['role' => 'otro',  'is_active' => false, 'joined_at' => now()->subDays(61), 'left_at' => now()->subDays(49)],
            ]);
        }
    }
}

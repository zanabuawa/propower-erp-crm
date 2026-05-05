<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectsReportPrintController extends Controller
{
    public function __invoke(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $query = Project::with(['customer', 'responsible', 'branch'])
            ->whereHas('branch', fn($q) => $q->where('company_id', $companyId))
            ->orWhereNull('branch_id');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $projects = $query->orderBy('status')->orderByDesc('start_date')->get();

        $statusLabel = match($request->status) {
            'borrador'   => 'Borrador',
            'activo'     => 'Activo',
            'pausado'    => 'Pausado',
            'completado' => 'Completado',
            'cancelado'  => 'Cancelado',
            default      => 'Todos los estados',
        };

        $typeLabel = match($request->type) {
            'interno'    => 'Interno',
            'externo'    => 'Externo',
            'licitacion' => 'Licitación',
            default      => 'Todos los tipos',
        };

        $company = Company::find($companyId);

        $activeCount    = $projects->where('status', 'activo')->count();
        $completedCount = $projects->where('status', 'completado')->count();
        $totalBudget    = $projects->sum('budget');
        $totalCost      = $projects->sum('cost_actual');

        return view('print.projects-report', compact(
            'projects', 'company', 'statusLabel', 'typeLabel',
            'activeCount', 'completedCount', 'totalBudget', 'totalCost'
        ));
    }
}

<?php

namespace App\Http\Controllers\Tenders;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Tender;
use Illuminate\Http\Request;

class TendersReportPrintController extends Controller
{
    public function __invoke(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $query = Tender::with(['customer', 'responsibleUser', 'branch'])
            ->where('company_id', $companyId);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q
                ->where('folio', 'like', "%{$s}%")
                ->orWhere('name', 'like', "%{$s}%")
            );
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $tenders = $query->orderByDesc('submission_date')->get();

        $statusLabel = $request->filled('status')
            ? (Tender::STATUSES[$request->status] ?? $request->status)
            : 'Todos los estados';

        $typeLabel = $request->filled('type')
            ? (Tender::TYPES[$request->type] ?? $request->type)
            : 'Todos los tipos';

        $company = Company::find($companyId);

        $adjudicadasCount  = $tenders->where('status', 'adjudicada')->count();
        $totalEstimado     = $tenders->sum('estimated_budget');
        $totalAdjudicado   = $tenders->where('status', 'adjudicada')->sum('awarded_amount');
        $tasaExito         = $tenders->count() > 0
            ? round($adjudicadasCount / $tenders->count() * 100, 1)
            : 0;

        return view('print.tenders-report', compact(
            'tenders', 'company', 'statusLabel', 'typeLabel',
            'adjudicadasCount', 'totalEstimado', 'totalAdjudicado', 'tasaExito'
        ));
    }
}

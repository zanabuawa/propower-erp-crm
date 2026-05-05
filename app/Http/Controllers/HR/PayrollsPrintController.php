<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\HrPayroll;
use Illuminate\Http\Request;

class PayrollsPrintController extends Controller
{
    public function __invoke(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $query = HrPayroll::with(['createdBy', 'approvedBy'])
            ->where('company_id', $companyId);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('period_type')) {
            $query->where('period_type', $request->period_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('period_start', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('period_end', '<=', $request->date_to);
        }

        $payrolls = $query->orderByDesc('period_start')->get();

        $statusLabel = $request->filled('status')
            ? (HrPayroll::STATUSES[$request->status] ?? $request->status)
            : 'Todos';

        $periodLabel = $request->filled('period_type')
            ? (HrPayroll::PERIOD_TYPES[$request->period_type] ?? $request->period_type)
            : 'Todos';

        $company = Company::find($companyId);

        $totalGross      = $payrolls->sum('total_gross');
        $totalNet        = $payrolls->sum('total_net');
        $totalDeductions = $payrolls->sum('total_deductions');
        $totalEmployers  = $payrolls->sum('total_employer_imss');

        return view('print.hr-payrolls', compact(
            'payrolls', 'company', 'statusLabel', 'periodLabel',
            'totalGross', 'totalNet', 'totalDeductions', 'totalEmployers'
        ));
    }
}

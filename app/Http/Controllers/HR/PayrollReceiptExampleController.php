<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HrEmployee;
use App\Services\HrPayrollCalculator;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PayrollReceiptExampleController extends Controller
{
    public function __invoke(Request $request, HrEmployee $employee, HrPayrollCalculator $calculator)
    {
        abort_unless($employee->company_id === auth()->user()->company_id, 403);

        $periodStart = $request->input('period_start')
            ? Carbon::parse($request->input('period_start'))
            : now()->copy()->startOfWeek();

        $periodEnd = $request->input('period_end')
            ? Carbon::parse($request->input('period_end'))
            : now()->copy()->endOfWeek();

        $items = $calculator->calculate(
            $employee->company_id,
            $periodStart->format('Y-m-d'),
            $periodEnd->format('Y-m-d'),
            $employee->id,
        );

        $item = $items[$employee->id] ?? null;

        abort_if(! $item, 404);

        $employee->load(['company', 'branch', 'department', 'position', 'user']);

        return view('print.hr-payroll-receipt', [
            'employee' => $employee,
            'company' => $employee->company,
            'item' => $item,
            'periodStart' => $periodStart,
            'periodEnd' => $periodEnd,
            'paymentDate' => $request->input('payment_date')
                ? Carbon::parse($request->input('payment_date'))
                : now(),
            'responsible' => auth()->user(),
            'isExample' => true,
        ]);
    }
}

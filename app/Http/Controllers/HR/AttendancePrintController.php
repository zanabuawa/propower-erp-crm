<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\HrAttendance;
use App\Models\HrEmployee;
use Illuminate\Http\Request;

class AttendancePrintController extends Controller
{
    public function __invoke(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $query = HrAttendance::with(['employee.department', 'employee.position'])
            ->where('company_id', $companyId);

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $attendances = $query->orderBy('date', 'desc')->orderBy('employee_id')->get();

        $statusLabel = $request->filled('status')
            ? (HrAttendance::STATUSES[$request->status] ?? $request->status)
            : 'Todos';

        $filterEmployee = $request->filled('employee_id')
            ? HrEmployee::find($request->employee_id)
            : null;

        $company = Company::find($companyId);

        $summary = [];
        foreach (HrAttendance::STATUSES as $key => $label) {
            $summary[$key] = [
                'label' => $label,
                'count' => $attendances->where('status', $key)->count(),
            ];
        }

        $totalHours     = $attendances->sum('worked_hours');
        $totalOvertime  = $attendances->sum('overtime_hours');

        return view('print.hr-attendance', compact(
            'attendances', 'company', 'statusLabel', 'filterEmployee',
            'summary', 'totalHours', 'totalOvertime'
        ));
    }
}

<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\HrDepartment;
use App\Models\HrEmployee;
use Illuminate\Http\Request;

class EmployeesPrintController extends Controller
{
    public function __invoke(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $query = HrEmployee::with(['department', 'position'])
            ->where('company_id', $companyId);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q
                ->where('first_name', 'like', "%{$s}%")
                ->orWhere('last_name', 'like', "%{$s}%")
                ->orWhere('employee_number', 'like', "%{$s}%")
                ->orWhere('rfc', 'like', "%{$s}%")
            );
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $employees = $query->orderBy('last_name')->orderBy('first_name')->get();

        $statusLabel = $request->filled('status')
            ? (HrEmployee::STATUSES[$request->status] ?? $request->status)
            : 'Todos';

        $department = $request->filled('department_id')
            ? HrDepartment::find($request->department_id)
            : null;

        $company = Company::find($companyId);

        $activeCount   = $employees->where('status', 'active')->count();
        $onLeaveCount  = $employees->where('status', 'on_leave')->count();
        $inactiveCount = $employees->whereIn('status', ['inactive', 'suspended', 'terminated'])->count();

        return view('print.hr-employees', compact(
            'employees', 'company', 'statusLabel', 'department',
            'activeCount', 'onLeaveCount', 'inactiveCount'
        ));
    }
}

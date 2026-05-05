<?php

namespace App\Http\Controllers\Customers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomersReportPrintController extends Controller
{
    public function __invoke(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $query = Customer::with(['phones', 'emails', 'assignedTo'])
            ->where('company_id', $companyId);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q
                ->where('name', 'like', "%{$s}%")
                ->orWhere('rfc', 'like', "%{$s}%")
            );
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('segment')) {
            $query->where('segment', $request->segment);
        }

        $customers = $query->orderBy('name')->get();

        $statusLabel = match($request->status) {
            'prospect' => 'Prospectos',
            'active'   => 'Activos',
            'inactive' => 'Inactivos',
            default    => 'Todos',
        };

        $segmentLabel = $request->filled('segment') ? 'Segmento ' . strtoupper($request->segment) : 'Todos los segmentos';

        $company = Company::find($companyId);

        $activeCount   = $customers->where('status', 'active')->count();
        $prospectCount = $customers->where('status', 'prospect')->count();
        $inactiveCount = $customers->where('status', 'inactive')->count();

        return view('print.customers-report', compact(
            'customers', 'company', 'statusLabel', 'segmentLabel',
            'activeCount', 'prospectCount', 'inactiveCount'
        ));
    }
}

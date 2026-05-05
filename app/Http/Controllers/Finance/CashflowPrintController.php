<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\FinanceAccount;
use App\Models\FinanceCashflow;
use Illuminate\Http\Request;

class CashflowPrintController extends Controller
{
    public function __invoke(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $query = FinanceCashflow::with(['account', 'project', 'tender'])
            ->whereHas('account', fn($q) => $q->where('company_id', $companyId));

        if ($request->filled('flow')) {
            $query->where('flow', $request->flow);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('account_id')) {
            $query->where('account_id', $request->account_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('expected_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('expected_date', '<=', $request->date_to);
        }

        $cashflows = $query->orderBy('expected_date')->get();

        $flowLabel = match($request->flow) {
            'entrada' => 'Entradas',
            'salida'  => 'Salidas',
            default   => 'Entradas y salidas',
        };

        $typeLabel = match($request->type) {
            'proyectado' => 'Proyectado',
            'real'       => 'Real',
            default      => 'Todos',
        };

        $filterAccount = $request->filled('account_id')
            ? FinanceAccount::find($request->account_id)
            : null;

        $company = Company::find($companyId);

        $totalEntradas  = $cashflows->where('flow', 'entrada')->sum('amount');
        $totalSalidas   = $cashflows->where('flow', 'salida')->sum('amount');
        $saldoNeto      = $totalEntradas - $totalSalidas;
        $pendingCount   = $cashflows->where('is_realized', false)->count();
        $realizedCount  = $cashflows->where('is_realized', true)->count();

        return view('print.finance-cashflow', compact(
            'cashflows', 'company', 'flowLabel', 'typeLabel', 'filterAccount',
            'totalEntradas', 'totalSalidas', 'saldoNeto', 'pendingCount', 'realizedCount'
        ));
    }
}

<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\FinanceAccount;
use App\Models\FinanceTransaction;
use Illuminate\Http\Request;

class TransactionsPrintController extends Controller
{
    public function __invoke(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $query = FinanceTransaction::with(['account', 'project', 'tender'])
            ->whereHas('account', fn($q) => $q->where('company_id', $companyId));

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q
                ->where('folio', 'like', "%{$s}%")
                ->orWhere('concept', 'like', "%{$s}%")
                ->orWhere('reference', 'like', "%{$s}%")
            );
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('account_id')) {
            $query->where('account_id', $request->account_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('transaction_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('transaction_date', '<=', $request->date_to);
        }

        $transactions = $query->orderByDesc('transaction_date')->get();

        $typeLabel = match($request->type) {
            'ingreso'       => 'Ingresos',
            'egreso'        => 'Egresos',
            'transferencia' => 'Transferencias',
            default         => 'Todos los tipos',
        };

        $filterAccount = $request->filled('account_id')
            ? FinanceAccount::find($request->account_id)
            : null;

        $company = Company::find($companyId);

        $totalIngresos      = $transactions->where('type', 'ingreso')->sum('amount');
        $totalEgresos       = $transactions->where('type', 'egreso')->sum('amount');
        $totalTransferencias = $transactions->where('type', 'transferencia')->sum('amount');
        $saldoNeto          = $totalIngresos - $totalEgresos;

        return view('print.finance-transactions', compact(
            'transactions', 'company', 'typeLabel', 'filterAccount',
            'totalIngresos', 'totalEgresos', 'totalTransferencias', 'saldoNeto'
        ));
    }
}

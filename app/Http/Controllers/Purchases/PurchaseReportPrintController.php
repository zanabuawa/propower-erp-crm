<?php

namespace App\Http\Controllers\Purchases;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;

class PurchaseReportPrintController extends Controller
{
    public function __invoke(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $query = PurchaseOrder::with(['supplier', 'createdBy', 'branch'])
            ->withCount('items')
            ->where('company_id', $companyId);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q
                ->where('folio', 'like', "%{$s}%")
                ->orWhereHas('supplier', fn($q2) => $q2->where('name', 'like', "%{$s}%"))
            );
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->latest()->get();

        // Totales por moneda y por estatus
        $totalMXN  = $orders->where('currency', 'MXN')->sum('total');
        $totalUSD  = $orders->where('currency', 'USD')->sum('total');
        $byStatus  = $orders->groupBy('status')->map->count();

        $statusLabel = $request->filled('status')
            ? (PurchaseOrder::STATUS[$request->status] ?? $request->status)
            : 'Todos';

        return view('print.purchases-report', compact(
            'orders', 'totalMXN', 'totalUSD', 'byStatus', 'statusLabel'
        ));
    }
}

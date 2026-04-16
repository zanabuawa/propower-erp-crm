<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Stock;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class WarehouseStockPrintController extends Controller
{
    public function __invoke(Request $request)
    {
        $companyId   = auth()->user()->company_id;
        $warehouseId = $request->integer('warehouse_id');

        $warehouse = Warehouse::where('company_id', $companyId)
            ->where('id', $warehouseId)
            ->firstOrFail();

        $query = Stock::with(['product.category'])
            ->where('warehouse_id', $warehouseId)
            ->whereHas('product', fn($q) => $q->where('company_id', $companyId)->where('is_active', true));

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('product', fn($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%")
                ->orWhere('barcode', 'like', "%{$search}%")
            );
        }

        if ($request->filled('category_id')) {
            $query->whereHas('product', fn($q) => $q->where('category_id', $request->category_id));
        }

        $stocks = $query->get()->sortBy('product.name');

        if ($request->stock_filter === 'out') {
            $stocks = $stocks->filter(fn($s) => $s->quantity <= 0);
        } elseif ($request->stock_filter === 'low') {
            $stocks = $stocks->filter(fn($s) => $s->quantity > 0 && $s->quantity <= $s->product->min_stock);
        } elseif ($request->stock_filter === 'ok') {
            $stocks = $stocks->filter(fn($s) => $s->quantity > $s->product->min_stock);
        }

        $totalValue = $stocks->sum(fn($s) => $s->quantity * (float) $s->product->purchase_price);
        $outCount   = $stocks->filter(fn($s) => $s->quantity <= 0)->count();
        $lowCount   = $stocks->filter(fn($s) => $s->quantity > 0 && $s->quantity <= $s->product->min_stock)->count();

        $category = $request->filled('category_id')
            ? Category::find($request->category_id)
            : null;

        $stockFilterLabel = match($request->stock_filter) {
            'out'   => 'Sin existencias',
            'low'   => 'Stock bajo',
            'ok'    => 'Stock óptimo',
            default => 'Todos',
        };

        return view('print.warehouse-stock', compact(
            'warehouse', 'stocks', 'totalValue', 'outCount', 'lowCount',
            'category', 'stockFilterLabel'
        ));
    }
}

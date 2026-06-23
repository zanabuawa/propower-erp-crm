<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Company;
use App\Models\Product;
use Illuminate\Http\Request;

class InventoryGeneralPrintController extends Controller
{
    public function __invoke(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $query = Product::with(['category', 'unitOfMeasure', 'stocks'])
            ->where('company_id', $companyId)
            ->where('is_active', true);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('sku', 'like', '%' . $search . '%')
                  ->orWhere('barcode', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('type_filter')) {
            $query->where('type', $request->type_filter);
        }

        $products = $query->orderBy('name')->get()->map(function (Product $p) {
            $p->total_qty = $p->type === 'service' ? null : $p->stocks->sum('quantity');
            return $p;
        });

        if ($request->stock_filter === 'out') {
            $products = $products->filter(fn($p) => $p->type !== 'service' && $p->total_qty <= 0);
        } elseif ($request->stock_filter === 'low') {
            $products = $products->filter(fn($p) => $p->type !== 'service' && $p->total_qty > 0 && $p->total_qty <= $p->min_stock);
        } elseif ($request->stock_filter === 'ok') {
            $products = $products->filter(fn($p) => $p->type !== 'service' && $p->total_qty > $p->min_stock);
        }

        $stockProducts = $products->filter(fn($p) => $p->type !== 'service');
        $serviceCount = $products->filter(fn($p) => $p->type === 'service')->count();
        $totalValue = $stockProducts->sum(fn($p) => $p->total_qty * (float) $p->purchase_price);
        $outCount   = $stockProducts->filter(fn($p) => $p->total_qty <= 0)->count();
        $lowCount   = $stockProducts->filter(fn($p) => $p->total_qty > 0 && $p->total_qty <= $p->min_stock)->count();

        $category = $request->filled('category_id')
            ? Category::find($request->category_id)
            : null;

        $stockFilterLabel = match($request->stock_filter) {
            'out'   => 'Sin existencias',
            'low'   => 'Stock bajo',
            'ok'    => 'Stock óptimo',
            default => 'Todos',
        };

        $typeFilterLabel = match($request->type_filter) {
            'product' => 'Productos',
            'service' => 'Servicios',
            default => 'Productos y servicios',
        };

        $company = Company::find($companyId);

        return view('print.inventory-general', compact(
            'products', 'totalValue', 'outCount', 'lowCount',
            'serviceCount', 'category', 'stockFilterLabel', 'typeFilterLabel', 'company'
        ));
    }
}

<?php

namespace App\Livewire\Inventory;

use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMovementItem;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class ReorderRecommendations extends Component
{
    public int    $analysisPeriodMonths = 3;  // meses históricos para calcular consumo
    public float  $leadTimeDays         = 7;   // días de plazo de entrega del proveedor
    public int    $safetyStockDays      = 7;   // días de stock de seguridad extra
    public string $filterCategory       = '';

    public function render()
    {
        $companyId = auth()->user()->company_id;
        $days      = $this->analysisPeriodMonths * 30;
        $since     = now()->subDays($days);

        $products = Product::with(['stocks', 'category', 'supplier', 'unitOfMeasure'])
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->where('type', '!=', 'service')
            ->when($this->filterCategory, fn($q) => $q->where('category_id', $this->filterCategory))
            ->get();

        $recommendations = $products->map(function (Product $product) use ($since, $days) {
            $totalStock      = (float) $product->stocks->sum('quantity');
            $committedStock  = (float) $product->stocks->sum('committed_quantity');
            $availableStock  = max(0, $totalStock - $committedStock);
            $minStock        = (float) $product->min_stock;
            $maxStock        = (float) $product->max_stock;

            // Consumo total en el período histórico (solo salidas: exit, sale, scrap, etc.)
            $consumed = StockMovementItem::whereHas('movement', function ($q) use ($product, $since) {
                $q->where('company_id', $product->company_id)
                  ->whereIn('type', ['exit', 'return'])
                  ->where('moved_at', '>=', $since)
                  ->where('status', 'confirmed');
            })->where('product_id', $product->id)->sum('quantity');

            $avgDailyConsumption = $days > 0 ? (float) $consumed / $days : 0;

            // Stock de ciclo = consumo durante el plazo de entrega + seguridad
            $reorderPoint    = round($avgDailyConsumption * ($this->leadTimeDays + $this->safetyStockDays), 2);
            $suggestedQty    = max(0, round(($maxStock ?: $reorderPoint * 3) - $availableStock, 2));
            $daysOfStock     = $avgDailyConsumption > 0 ? round($availableStock / $avgDailyConsumption) : null;
            $needsReorder    = $availableStock <= $reorderPoint && $avgDailyConsumption > 0;

            return [
                'product'              => $product,
                'total_stock'          => $totalStock,
                'committed_stock'      => $committedStock,
                'available_stock'      => $availableStock,
                'min_stock'            => $minStock,
                'max_stock'            => $maxStock,
                'avg_daily_consumption'=> round($avgDailyConsumption, 4),
                'reorder_point'        => $reorderPoint,
                'suggested_qty'        => $suggestedQty,
                'days_of_stock'        => $daysOfStock,
                'needs_reorder'        => $needsReorder,
                'consumed_period'      => (float) $consumed,
            ];
        })
        ->filter(fn($r) => $r['needs_reorder'] || $r['available_stock'] <= $r['min_stock'])
        ->sortBy('days_of_stock')
        ->values();

        $categories = \App\Models\Category::where('company_id', $companyId)
            ->where('is_active', true)->orderBy('name')->get();

        return view('livewire.inventory.reorder-recommendations',
            compact('recommendations', 'categories'));
    }
}

<?php

namespace App\Livewire\Inventory;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovementItem;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class DemandAnalysis extends Component
{
    public int    $analysisPeriodMonths = 3;
    public string $filterCategory      = '';
    public string $demandTier          = '';    // high, medium, low, zero
    public string $sortBy              = 'avg_daily'; // avg_daily, total_consumed, trend_pct
    public string $sortDir             = 'desc';

    public function toggleSort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy  = $column;
            $this->sortDir = 'desc';
        }
    }

    public function render()
    {
        $companyId = auth()->user()->company_id;
        $days      = $this->analysisPeriodMonths * 30;
        $since     = now()->subDays($days);

        // Dividimos el período en dos mitades para calcular tendencia
        $midPoint  = now()->subDays((int) ($days / 2));

        $products = Product::with(['category', 'unitOfMeasure', 'supplier'])
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->where('type', '!=', 'service')
            ->when($this->filterCategory, fn($q) => $q->where('category_id', $this->filterCategory))
            ->get();

        $data = $products->map(function (Product $product) use ($since, $midPoint, $days, $companyId) {
            $baseQuery = fn() => StockMovementItem::whereHas('movement', function ($q) use ($product, $since, $companyId) {
                $q->where('company_id', $companyId)
                  ->whereIn('type', ['exit', 'adjustment'])
                  ->where('moved_at', '>=', $since)
                  ->where('status', 'confirmed');
            })->where('product_id', $product->id);

            $totalConsumed = (float) $baseQuery()->sum('quantity');

            // Primera mitad del período
            $firstHalf = (float) StockMovementItem::whereHas('movement', function ($q) use ($product, $since, $midPoint, $companyId) {
                $q->where('company_id', $companyId)
                  ->whereIn('type', ['exit', 'adjustment'])
                  ->where('moved_at', '>=', $since)
                  ->where('moved_at', '<', $midPoint)
                  ->where('status', 'confirmed');
            })->where('product_id', $product->id)->sum('quantity');

            // Segunda mitad del período
            $secondHalf = (float) StockMovementItem::whereHas('movement', function ($q) use ($product, $midPoint, $companyId) {
                $q->where('company_id', $companyId)
                  ->whereIn('type', ['exit', 'adjustment'])
                  ->where('moved_at', '>=', $midPoint)
                  ->where('status', 'confirmed');
            })->where('product_id', $product->id)->sum('quantity');

            $avgDailyConsumption = $days > 0 ? $totalConsumed / $days : 0;

            // Tendencia: % de cambio entre primera y segunda mitad
            $trendPct = null;
            if ($firstHalf > 0) {
                $trendPct = round((($secondHalf - $firstHalf) / $firstHalf) * 100, 1);
            } elseif ($secondHalf > 0) {
                $trendPct = 100.0; // pasó de 0 a tener demanda
            }

            // Número de movimientos distintos (frecuencia)
            $movementCount = StockMovementItem::whereHas('movement', function ($q) use ($product, $since, $companyId) {
                $q->where('company_id', $companyId)
                  ->whereIn('type', ['exit', 'adjustment'])
                  ->where('moved_at', '>=', $since)
                  ->where('status', 'confirmed');
            })->where('product_id', $product->id)->count();

            return [
                'product'        => $product,
                'total_consumed' => $totalConsumed,
                'avg_daily'      => round($avgDailyConsumption, 4),
                'first_half'     => $firstHalf,
                'second_half'    => $secondHalf,
                'trend_pct'      => $trendPct,
                'movement_count' => $movementCount,
                'unit'           => $product->unitOfMeasure?->abbreviation ?? '',
            ];
        });

        // Calcular umbrales para clasificar: alta = top 20%, media = 20-60%, baja = 60-90%, sin = 0
        $nonZero     = $data->where('total_consumed', '>', 0)->sortByDesc('total_consumed')->values();
        $total       = $nonZero->count();
        $highCutoff  = $total > 0 ? (int) ceil($total * 0.2) : 0;
        $mediumCutoff= $total > 0 ? (int) ceil($total * 0.6) : 0;

        $highIds   = $nonZero->take($highCutoff)->pluck('product.id');
        $mediumIds = $nonZero->slice($highCutoff, $mediumCutoff - $highCutoff)->pluck('product.id');

        $data = $data->map(function ($row) use ($highIds, $mediumIds) {
            $row['tier'] = match(true) {
                $row['total_consumed'] === 0.0        => 'zero',
                $highIds->contains($row['product']->id)  => 'high',
                $mediumIds->contains($row['product']->id)=> 'medium',
                default                               => 'low',
            };
            return $row;
        });

        $summary = [
            'high'   => $data->where('tier', 'high')->count(),
            'medium' => $data->where('tier', 'medium')->count(),
            'low'    => $data->where('tier', 'low')->count(),
            'zero'   => $data->where('tier', 'zero')->count(),
            'total_consumed' => $data->sum('total_consumed'),
        ];

        if ($this->demandTier) {
            $data = $data->where('tier', $this->demandTier);
        }

        $data = $this->sortDir === 'asc'
            ? $data->sortBy($this->sortBy, SORT_REGULAR, false)
            : $data->sortByDesc($this->sortBy);

        $categories = Category::where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get();

        return view('livewire.inventory.demand-analysis', compact('data', 'summary', 'categories'));
    }
}

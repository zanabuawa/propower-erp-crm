<?php

namespace App\Livewire\Inventory;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovementItem;
use App\Models\Warehouse;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class InventoryTurnover extends Component
{
    public int    $analysisPeriodMonths = 6;
    public string $filterCategory      = '';
    public string $filterWarehouse     = '';
    public string $sortBy              = 'turnover_ratio'; // turnover_ratio, days_in_stock, consumed
    public string $sortDir             = 'asc';           // productos de baja rotación primero
    public string $tierFilter          = '';              // high, medium, low, dead

    public function toggleSort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy  = $column;
            $this->sortDir = 'asc';
        }
    }

    public function render()
    {
        $companyId = auth()->user()->company_id;
        $days      = $this->analysisPeriodMonths * 30;
        $since     = now()->subDays($days);

        $products = Product::with(['stocks', 'category', 'unitOfMeasure'])
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->where('type', '!=', 'service')
            ->when($this->filterCategory, fn($q) => $q->where('category_id', $this->filterCategory))
            ->when($this->filterWarehouse, fn($q) => $q->whereHas('stocks', fn($s) => $s->where('warehouse_id', $this->filterWarehouse)))
            ->get();

        $data = $products->map(function (Product $product) use ($since, $days, $companyId) {
            // Stock actual (filtrado por almacén si aplica)
            $stockQuery = $product->stocks;
            if ($this->filterWarehouse) {
                $stockQuery = $stockQuery->where('warehouse_id', (int) $this->filterWarehouse);
            }
            $currentStock = (float) $stockQuery->sum('quantity');

            // Unidades consumidas en el período (salidas confirmadas)
            $consumed = (float) StockMovementItem::whereHas('movement', function ($q) use ($product, $since, $companyId) {
                $q->where('company_id', $companyId)
                  ->whereIn('type', ['exit', 'adjustment'])
                  ->where('moved_at', '>=', $since)
                  ->where('status', 'confirmed');
            })->where('product_id', $product->id)->sum('quantity');

            // Unidades que entraron en el período (para calcular stock promedio)
            $entries = (float) StockMovementItem::whereHas('movement', function ($q) use ($product, $since, $companyId) {
                $q->where('company_id', $companyId)
                  ->whereIn('type', ['entry', 'return'])
                  ->where('moved_at', '>=', $since)
                  ->where('status', 'confirmed');
            })->where('product_id', $product->id)->sum('quantity');

            // Stock promedio aproximado: (stock inicial + stock final) / 2
            // Stock inicial = stock actual - entradas + consumos
            $stockStart  = max(0, $currentStock - $entries + $consumed);
            $avgStock    = ($stockStart + $currentStock) / 2;

            // Rotación = consumo / stock promedio (anualizada al período)
            $turnoverRatio = $avgStock > 0
                ? round($consumed / $avgStock, 2)
                : ($consumed > 0 ? 999 : 0); // vendió todo sin stock = rotación alta

            // Días en stock = stock actual / consumo diario promedio
            $avgDailyConsumption = $days > 0 ? $consumed / $days : 0;
            $daysInStock         = $avgDailyConsumption > 0
                ? (int) round($currentStock / $avgDailyConsumption)
                : null;

            // Clasificación de rotación
            $tier = match(true) {
                $consumed === 0.0                   => 'dead',
                $turnoverRatio >= 4                 => 'high',
                $turnoverRatio >= 1                 => 'medium',
                default                             => 'low',
            };

            return [
                'product'       => $product,
                'current_stock' => $currentStock,
                'avg_stock'     => round($avgStock, 2),
                'consumed'      => $consumed,
                'entries'       => $entries,
                'turnover_ratio'=> $turnoverRatio,
                'days_in_stock' => $daysInStock,
                'tier'          => $tier,
                'unit'          => $product->unitOfMeasure?->abbreviation ?? '',
            ];
        });

        // Resumen por tier
        $summary = [
            'high'   => $data->where('tier', 'high')->count(),
            'medium' => $data->where('tier', 'medium')->count(),
            'low'    => $data->where('tier', 'low')->count(),
            'dead'   => $data->where('tier', 'dead')->count(),
        ];

        // Filtrar por tier si se seleccionó
        if ($this->tierFilter) {
            $data = $data->where('tier', $this->tierFilter);
        }

        // Ordenar
        $data = $this->sortDir === 'asc'
            ? $data->sortBy($this->sortBy)
            : $data->sortByDesc($this->sortBy);

        $categories = Category::where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get();

        return view('livewire.inventory.inventory-turnover', compact(
            'data', 'summary', 'categories', 'warehouses'
        ));
    }
}

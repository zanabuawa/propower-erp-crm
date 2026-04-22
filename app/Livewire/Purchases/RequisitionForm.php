<?php

namespace App\Livewire\Purchases;

use App\Models\Product;
use App\Models\PurchaseRequisition;
use App\Models\Stock;
use App\Notifications\PurchaseNotification;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;

#[Layout('layouts.app')]
class RequisitionForm extends Component
{
    public string $justification     = '';
    public string $currency          = 'MXN';
    public string $needed_by         = '';
    public string $requisition_type  = 'material';
    public string $priority          = 'normal';
    public string $expense_type      = '';
    public string $project_name      = '';
    public array  $items             = [];
    public string $productSearch     = '';
    public array  $productResults    = [];

    public function mount(): void
    {
        $this->needed_by = now()->addDays(7)->format('Y-m-d');
        $this->items = [$this->blankItem()];
    }

    private function blankItem(string $itemType = 'product'): array
    {
        return [
            'product_id'  => null,
            'item_type'   => $itemType,
            'description' => '',
            'quantity'    => 1,
            'unit_price'  => 0,
            'unit'        => '',
            'notes'       => '',
            'stock_info'  => null,
        ];
    }

    public function updatedProductSearch(): void
    {
        if (strlen($this->productSearch) < 2) {
            $this->productResults = [];
            return;
        }

        $companyId = auth()->user()->company_id;

        $products = Product::where('company_id', $companyId)
            ->where('is_active', true)
            ->where(fn($q) => $q
                ->where('name', 'like', "%{$this->productSearch}%")
                ->orWhere('sku', 'like', "%{$this->productSearch}%")
                ->orWhere('barcode', 'like', "%{$this->productSearch}%"))
            ->limit(8)
            ->get(['id', 'name', 'sku', 'barcode', 'purchase_price']);

        // Fetch total available stock per product
        $productIds = $products->pluck('id');
        $stockTotals = Stock::whereIn('product_id', $productIds)
            ->whereHas('warehouse', fn($q) => $q->where('company_id', $companyId)->where('is_defective', false))
            ->groupBy('product_id')
            ->selectRaw('product_id, SUM(quantity) as total_qty, SUM(committed_quantity) as total_committed')
            ->get()
            ->keyBy('product_id');

        $this->productResults = $products->map(function (Product $p) use ($stockTotals) {
            $stock    = $stockTotals->get($p->id);
            $totalQty = $stock ? (float) $stock->total_qty : 0;
            $committed = $stock ? (float) $stock->total_committed : 0;
            $available = max(0, $totalQty - $committed);
            return [
                'id'             => $p->id,
                'name'           => $p->name,
                'sku'            => $p->sku,
                'barcode'        => $p->barcode,
                'purchase_price' => $p->purchase_price,
                'stock_total'    => $totalQty,
                'stock_available'=> $available,
            ];
        })->toArray();
    }

    #[On('product-picked')]
    public function productPicked(int $productId): void
    {
        $product = Product::find($productId);
        if (!$product) return;

        $item = $this->blankItem('product');
        $item['product_id']  = $product->id;
        $item['description'] = $product->name;
        $item['quantity']    = 1;
        $item['unit_price']  = $product->purchase_price;
        $item['unit']        = $product->unit_of_measure?->name ?? 'pz';
        $item['stock_info']  = $this->getStockInfo($product->id);
        $this->items[] = $item;
    }

    public function addProduct(int $productId): void
    {
        $product = Product::find($productId);
        if (!$product) return;

        $newItem = $this->blankItem('product');
        $newItem['product_id']  = $product->id;
        $newItem['description'] = $product->name;
        $newItem['quantity']    = 1;
        $newItem['unit_price']  = $product->purchase_price;
        $newItem['unit']        = $product->unit_of_measure?->name ?? 'pz';
        $newItem['stock_info']  = $this->getStockInfo($product->id);

        // Buscar si hay una partida vacía para reemplazarla
        $emptyIndex = collect($this->items)->search(function($item) {
            return empty($item['product_id']) && (empty($item['description']) || $item['description'] === '');
        });

        if ($emptyIndex !== false) {
            $this->items[$emptyIndex] = $newItem;
        } else {
            $this->items[] = $newItem;
        }

        $this->productSearch  = '';
        $this->productResults = [];
    }

    public function addItem(string $type = 'product'): void
    {
        $this->items[] = $this->blankItem($type);
    }

    public function removeItem(int $index): void
    {
        array_splice($this->items, $index, 1);
        $this->items = array_values($this->items);
    }

    private function getStockInfo(int $productId): ?array
    {
        $companyId = auth()->user()->company_id;
        $stock = Stock::where('product_id', $productId)
            ->whereHas('warehouse', fn($q) => $q->where('company_id', $companyId)->where('is_defective', false))
            ->selectRaw('SUM(quantity) as total_qty, SUM(committed_quantity) as total_committed')
            ->first();

        if (!$stock) return ['total' => 0, 'available' => 0];
        $total     = (float) $stock->total_qty;
        $committed = (float) $stock->total_committed;
        return ['total' => $total, 'available' => max(0, $total - $committed)];
    }

    public function rules(): array
    {
        return [
            'justification'       => 'required|string',
            'currency'            => 'required|in:MXN,USD',
            'needed_by'           => 'required|date',
            'requisition_type'    => 'required|in:material,service,tool,asset,mixed',
            'priority'            => 'required|in:low,normal,high,urgent',
            'expense_type'        => 'nullable|in:operational,capital,maintenance,project,other',
            'project_name'        => 'nullable|string|max:150',
            'items'               => 'required|array|min:1',
            'items.*.item_type'   => 'required|in:product,service,tool,asset,other',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity'    => 'required|numeric|min:0.01',
            'items.*.unit_price'  => 'required|numeric|min:0',
        ];
    }

    public function save(): void
    {
        $this->validate();

        DB::transaction(function () {
            $folio = 'REQ-' . str_pad(
                PurchaseRequisition::where('company_id', auth()->user()->company_id)->count() + 1,
                6, '0', STR_PAD_LEFT
            );

            $requisition = PurchaseRequisition::create([
                'company_id'       => auth()->user()->company_id,
                'branch_id'        => auth()->user()->branch_id,
                'requested_by'     => auth()->id(),
                'folio'            => $folio,
                'currency'         => $this->currency,
                'requisition_type' => $this->requisition_type,
                'priority'         => $this->priority,
                'expense_type'     => $this->expense_type ?: null,
                'project_name'     => $this->project_name ?: null,
                'status'           => 'submitted',
                'justification'    => $this->justification,
                'needed_by'        => $this->needed_by,
                'submitted_at'     => now(),
            ]);

            foreach ($this->items as $item) {
                $requisition->items()->create([
                    'product_id'  => $item['product_id'] ?? null,
                    'item_type'   => $item['item_type'] ?? 'product',
                    'description' => $item['description'],
                    'quantity'    => $item['quantity'],
                    'unit_price'  => $item['unit_price'],
                    'unit'        => $item['unit'] ?? '',
                    'notes'       => $item['notes'] ?? '',
                ]);
            }

            // Notificar a compradores
            $compradores = User::where('company_id', auth()->user()->company_id)
                ->whereHas('roles', fn($q) => $q->where('name', 'comprador'))
                ->get();

            $priorityLabel = PurchaseRequisition::PRIORITY[$this->priority] ?? $this->priority;
            $typeLabel     = PurchaseRequisition::REQUISITION_TYPES[$this->requisition_type] ?? $this->requisition_type;

            foreach ($compradores as $user) {
                $user->notify(new PurchaseNotification(
                    title: "Nueva requisición [{$priorityLabel}]",
                    message: "Se creó la requisición {$folio} ({$typeLabel}) por " . auth()->user()->name . '. Requiere cotización preliminar.',
                    type: 'requisition_submitted',
                    requisitionId: $requisition->id,
                ));
            }
        });

        session()->flash('success', 'Requisición enviada a compras correctamente.');
        $this->redirect(route('purchases.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.purchases.requisition-form');
    }
}

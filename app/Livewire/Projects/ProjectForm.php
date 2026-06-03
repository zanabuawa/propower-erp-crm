<?php

namespace App\Livewire\Projects;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Project;
use App\Models\ProjectMaterial;
use App\Models\PurchaseRequisition;
use App\Models\PurchaseRequisitionItem;
use App\Models\SaleOrder;
use App\Models\Stock;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class ProjectForm extends Component
{
    public ?Project $project = null;

    // ── Campos del proyecto ───────────────────────────────────────────────────
    public string $code                = '';
    public string $name                = '';
    public string $description         = '';
    public string $type                = 'externo';
    public string $status              = 'borrador';
    public ?int   $customer_id         = null;
    public ?int   $sale_order_id       = null;
    public string $contract_reference  = '';
    public ?int   $branch_id           = null;
    public ?int   $responsible_user_id = null;
    public string $start_date          = '';
    public string $end_date            = '';
    public string $budget              = '';
    public string $currency            = 'MXN';
    public string $notes               = '';

    // ── Líneas de inventario (filas en el formulario) ─────────────────────────
    public array $materialLines = [];

    // ── Catálogo de selección ─────────────────────────────────────────────────
    public bool   $showCatalog       = false;
    public string $catalogSearch     = '';
    public array  $selectedIds       = [];
    public ?int   $defaultWarehouse  = null;
    public ?int   $catalogCategoryId = null;
    public ?int   $catalogSupplierId = null;

    public function mount(?Project $project = null): void
    {
        if ($project && $project->exists) {
            $this->project             = $project;
            $this->code                = $project->code;
            $this->name                = $project->name;
            $this->description         = $project->description ?? '';
            $this->type                = $project->type;
            $this->status              = $project->status;
            $this->customer_id         = $project->customer_id;
            $this->sale_order_id       = $project->sale_order_id;
            $this->contract_reference  = $project->contract_reference ?? '';
            $this->branch_id           = $project->branch_id;
            $this->responsible_user_id = $project->responsible_user_id;
            $this->start_date          = $project->start_date?->format('Y-m-d') ?? '';
            $this->end_date            = $project->end_date?->format('Y-m-d') ?? '';
            $this->budget              = $project->budget ?? '';
            $this->currency            = $project->currency;
            $this->notes               = $project->notes ?? '';
        } else {
            $this->code = 'PROJ-' . str_pad(Project::count() + 1, 4, '0', STR_PAD_LEFT);
        }
    }

    // ── Catálogo ──────────────────────────────────────────────────────────────

    public function openCatalog(): void
    {
        $this->selectedIds        = [];
        $this->catalogSearch      = '';
        $this->catalogCategoryId  = null;
        $this->catalogSupplierId  = null;
        $this->showCatalog        = true;
    }

    public function toggleProduct(int $id): void
    {
        if (in_array($id, $this->selectedIds)) {
            $this->selectedIds = array_values(array_filter($this->selectedIds, fn($v) => $v !== $id));
        } else {
            $this->selectedIds[] = $id;
        }
    }

    public function addSelectedToLines(): void
    {
        if (empty($this->selectedIds)) {
            $this->showCatalog = false;
            return;
        }

        $products = Product::whereIn('id', $this->selectedIds)->get();

        // IDs ya presentes en las líneas actuales para no duplicar
        $existingProductIds = array_filter(array_column($this->materialLines, 'product_id'));

        foreach ($products as $product) {
            if (in_array($product->id, $existingProductIds)) {
                continue;
            }

            $this->materialLines[] = [
                'product_id'      => $product->id,
                'warehouse_id'    => $this->defaultWarehouse,
                'name'            => $product->name,
                'resource_type'   => 'material',
                'unit'            => $product->unitOfMeasure?->name ?? '',
                'quantity_needed' => '1',
                'unit_cost'       => (string) ($product->sale_price ?? 0),
                'notes'           => '',
            ];
        }

        $this->selectedIds  = [];
        $this->showCatalog  = false;
    }

    // ── Líneas ────────────────────────────────────────────────────────────────

    public function removeMaterialLine(int $index): void
    {
        array_splice($this->materialLines, $index, 1);
        $this->materialLines = array_values($this->materialLines);
    }

    public function updatedMaterialLines($value, $key): void
    {
        [$index, $field] = array_pad(explode('.', $key, 2), 2, '');

        if ($field === 'product_id' && $value) {
            $product = Product::find((int) $value);
            if ($product) {
                if (empty($this->materialLines[$index]['name'])) {
                    $this->materialLines[$index]['name'] = $product->name;
                }
                if (empty($this->materialLines[$index]['unit'])) {
                    $this->materialLines[$index]['unit'] = $product->unitOfMeasure?->name ?? '';
                }
                if (empty($this->materialLines[$index]['unit_cost']) || $this->materialLines[$index]['unit_cost'] == '0') {
                    $this->materialLines[$index]['unit_cost'] = (string) ($product->sale_price ?? 0);
                }
            }
        }
    }

    // ── Validación ────────────────────────────────────────────────────────────

    public function rules(): array
    {
        return [
            'code'                => 'required|string|max:50|unique:projects,code,' . ($this->project?->id ?? 'NULL'),
            'name'                => 'required|string|max:255',
            'description'         => 'nullable|string',
            'type'                => 'required|in:interno,externo,licitacion,mantenimiento,instalacion,servicio',
            'status'              => 'required|in:borrador,activo,pausado,completado,cancelado',
            'customer_id'         => 'nullable|exists:customers,id',
            'sale_order_id'       => 'nullable|exists:sale_orders,id',
            'contract_reference'  => 'nullable|string|max:100',
            'branch_id'           => 'nullable|exists:branches,id',
            'responsible_user_id' => 'nullable|exists:users,id',
            'start_date'          => 'nullable|date',
            'end_date'            => 'nullable|date|after_or_equal:start_date',
            'budget'              => 'nullable|numeric|min:0',
            'currency'            => 'required|string|size:3',
            'notes'               => 'nullable|string|max:1000',
            'materialLines.*.name'            => 'nullable|string|max:255',
            'materialLines.*.quantity_needed' => 'nullable|numeric|min:0.001',
            'materialLines.*.unit_cost'       => 'nullable|numeric|min:0',
            'materialLines.*.warehouse_id'    => 'nullable|exists:warehouses,id',
            'materialLines.*.product_id'      => 'nullable|exists:products,id',
        ];
    }

    // ── Guardar ───────────────────────────────────────────────────────────────

    public function save(): void
    {
        $this->validate();

        $isNew = !($this->project && $this->project->exists);

        DB::transaction(function () use ($isNew) {
            $data = [
                'code'                => $this->code,
                'name'                => $this->name,
                'description'         => $this->description ?: null,
                'type'                => $this->type,
                'status'              => $this->status,
                'customer_id'         => $this->customer_id,
                'sale_order_id'       => $this->sale_order_id,
                'contract_reference'  => $this->contract_reference ?: null,
                'branch_id'           => $this->branch_id,
                'responsible_user_id' => $this->responsible_user_id,
                'start_date'          => $this->start_date ?: null,
                'end_date'            => $this->end_date ?: null,
                'budget'              => $this->budget ?: 0,
                'currency'            => $this->currency,
                'notes'               => $this->notes ?: null,
            ];

            if ($isNew) {
                $project = Project::create($data);
            } else {
                $this->project->update($data);
                $project = $this->project;
            }

            $this->processMaterialLines($project);
        });

        session()->flash('success', $isNew
            ? 'Proyecto creado con inventario procesado.'
            : 'Proyecto actualizado correctamente.');

        $this->redirect(route('projects.index'), navigate: true);
    }

    // ── Procesar inventario ───────────────────────────────────────────────────

    private function processMaterialLines(Project $project): void
    {
        $companyId = auth()->user()->company_id;

        foreach ($this->materialLines as $line) {
            $name   = trim($line['name'] ?? '');
            $needed = (float) ($line['quantity_needed'] ?? 0);

            if ($name === '' || $needed <= 0) {
                continue;
            }

            $productId    = $line['product_id']    ? (int) $line['product_id']    : null;
            $warehouseId  = $line['warehouse_id']   ? (int) $line['warehouse_id']  : null;
            $resourceType = $line['resource_type']  ?? 'material';

            $quantityReserved = 0.0;
            $status           = 'pendiente';
            $requisitionId    = null;

            if ($productId && $warehouseId) {
                $stock = Stock::firstOrCreate(
                    ['product_id' => $productId, 'warehouse_id' => $warehouseId],
                    ['quantity' => 0, 'committed_quantity' => 0]
                );
                $available = (float) $stock->available_quantity;

                if ($available >= $needed) {
                    $stock->commit($needed);
                    $quantityReserved = $needed;
                    $status           = 'reservado';
                } else {
                    if ($available > 0) {
                        $stock->commit($available);
                        $quantityReserved = $available;
                    }
                    $requisitionId = $this->createRequisition($project, $companyId, $line, $needed - $quantityReserved);
                    $status        = 'solicitado';
                }
            }

            ProjectMaterial::create([
                'project_id'              => $project->id,
                'product_id'              => $productId,
                'warehouse_id'            => $warehouseId,
                'name'                    => $name,
                'resource_type'           => $resourceType,
                'unit'                    => $line['unit'] ?: null,
                'quantity_needed'         => $needed,
                'quantity_reserved'       => $quantityReserved,
                'quantity_used'           => 0,
                'unit_cost'               => (float) ($line['unit_cost'] ?? 0),
                'status'                  => $status,
                'notes'                   => $line['notes'] ?: null,
                'purchase_requisition_id' => $requisitionId,
            ]);
        }
    }

    private function createRequisition(Project $project, int $companyId, array $line, float $shortfall): int
    {
        $resourceType = $line['resource_type'] ?? 'material';
        $reqType      = in_array($resourceType, ['equipo', 'herramienta']) ? 'tool' : 'material';

        $folio = 'REQ-' . str_pad(
            PurchaseRequisition::where('company_id', $companyId)->count() + 1,
            5, '0', STR_PAD_LEFT
        );

        $req = PurchaseRequisition::create([
            'company_id'       => $companyId,
            'project_id'       => $project->id,
            'project_name'     => $project->name,
            'branch_id'        => $project->branch_id,
            'requested_by'     => auth()->id(),
            'folio'            => $folio,
            'currency'         => $project->currency ?? 'MXN',
            'requisition_type' => $reqType,
            'priority'         => 'normal',
            'expense_type'     => 'project',
            'status'           => 'draft',
            'justification'    => "Inventario para proyecto {$project->code}: {$project->name}",
        ]);

        PurchaseRequisitionItem::create([
            'purchase_requisition_id' => $req->id,
            'product_id'              => $line['product_id'] ?: null,
            'item_type'               => $reqType === 'tool' ? 'tool' : 'product',
            'description'             => trim($line['name']) . ($line['notes'] ? ' — ' . $line['notes'] : ''),
            'quantity'                => $shortfall,
            'unit'                    => $line['unit'] ?: null,
            'unit_price'              => (float) ($line['unit_cost'] ?? 0),
        ]);

        return $req->id;
    }

    // ── Render ────────────────────────────────────────────────────────────────

    public function render()
    {
        $companyId = auth()->user()->company_id;

        $branches   = Branch::where('company_id', $companyId)->orderBy('name')->get();
        $customers  = Customer::where('company_id', $companyId)->orderBy('name')->get();
        $users      = User::where('company_id', $companyId)->orderBy('name')->get();
        $saleOrders = SaleOrder::where('company_id', $companyId)
                               ->whereIn('status', ['confirmado', 'en_proceso', 'facturado'])
                               ->orderByDesc('id')
                               ->get(['id', 'folio', 'customer_id']);
        $warehouses = Warehouse::where('company_id', $companyId)
                               ->orderBy('name')
                               ->get(['id', 'name']);

        // Productos del catálogo (solo cuando el modal está abierto)
        $catalogProducts = $this->showCatalog
            ? Product::where('company_id', $companyId)
                ->where('is_active', true)
                ->when($this->catalogSearch, fn($q) =>
                    $q->where(function ($q) {
                        $q->where('name', 'like', '%' . $this->catalogSearch . '%')
                          ->orWhere('sku',     'like', '%' . $this->catalogSearch . '%')
                          ->orWhere('barcode', 'like', '%' . $this->catalogSearch . '%');
                    })
                )
                ->when($this->catalogCategoryId, fn($q) => $q->where('category_id', $this->catalogCategoryId))
                ->when($this->catalogSupplierId,  fn($q) => $q->where('supplier_id',  $this->catalogSupplierId))
                ->with(['category', 'primaryImage'])
                ->orderBy('name')
                ->limit(60)
                ->get()
            : collect();

        $catalogCategories = $this->showCatalog
            ? Category::whereNull('parent_id')->where('is_active', true)->orderBy('name')->get(['id', 'name'])
            : collect();

        $catalogSuppliers = $this->showCatalog
            ? Supplier::where('company_id', $companyId)->where('status', 'active')->orderBy('name')->get(['id', 'name'])
            : collect();

        // Stock disponible por línea
        $stockPerLine = [];
        foreach ($this->materialLines as $i => $line) {
            if (!empty($line['product_id']) && !empty($line['warehouse_id'])) {
                $stock = Stock::where('product_id', (int) $line['product_id'])
                              ->where('warehouse_id', (int) $line['warehouse_id'])
                              ->first();
                $stockPerLine[$i] = $stock ? (float) $stock->available_quantity : 0.0;
            }
        }

        // Materiales existentes (solo edición)
        $existingMaterials = $this->project?->exists
            ? $this->project->materials()->with(['product', 'warehouse'])->orderBy('name')->get()
            : collect();

        return view('livewire.projects.project-form',
            compact('branches', 'customers', 'users', 'saleOrders',
                    'warehouses', 'catalogProducts', 'catalogCategories', 'catalogSuppliers',
                    'stockPerLine', 'existingMaterials'));
    }
}

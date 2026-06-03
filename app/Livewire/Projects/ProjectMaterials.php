<?php

namespace App\Livewire\Projects;

use App\Models\Product;
use App\Models\Project;
use App\Models\ProjectMaterial;
use App\Models\ProjectTask;
use App\Models\PurchaseRequisition;
use App\Models\PurchaseRequisitionItem;
use App\Models\Stock;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ProjectMaterials extends Component
{
    public Project $project;

    public bool $showModal = false;
    public ?int $editingId = null;

    // Form fields
    public ?int   $product_id    = null;
    public ?int   $warehouse_id  = null;
    public string $name          = '';
    public string $resource_type = 'material';
    public string $unit          = '';
    public string $quantity_needed = '1';
    public string $quantity_used   = '0';
    public string $unit_cost       = '0';
    public string $status          = 'pendiente';
    public ?int   $task_id         = null;
    public string $notes           = '';

    // Filters
    public string $filterType   = '';
    public string $filterStatus = '';

    public function mount(Project $project): void
    {
        $this->project = $project;
    }

    public function openCreate(): void
    {
        $this->reset('editingId', 'product_id', 'warehouse_id', 'name', 'unit', 'notes', 'task_id');
        $this->resource_type   = 'material';
        $this->quantity_needed = '1';
        $this->quantity_used   = '0';
        $this->unit_cost       = '0';
        $this->status          = 'pendiente';
        $this->showModal       = true;
    }

    public function openEdit(int $id): void
    {
        $m = ProjectMaterial::findOrFail($id);
        $this->editingId       = $m->id;
        $this->product_id      = $m->product_id;
        $this->warehouse_id    = $m->warehouse_id;
        $this->name            = $m->name;
        $this->resource_type   = $m->resource_type;
        $this->unit            = $m->unit ?? '';
        $this->quantity_needed = (string) $m->quantity_needed;
        $this->quantity_used   = (string) $m->quantity_used;
        $this->unit_cost       = (string) $m->unit_cost;
        $this->status          = $m->status;
        $this->task_id         = $m->task_id;
        $this->notes           = $m->notes ?? '';
        $this->showModal       = true;
    }

    public function updatedProductId(?int $value): void
    {
        if ($value) {
            $product = Product::find($value);
            if ($product) {
                $this->name      = $this->name ?: $product->name;
                $this->unit      = $this->unit ?: ($product->unitOfMeasure?->name ?? '');
                $this->unit_cost = $this->unit_cost == '0' ? (string) ($product->sale_price ?? 0) : $this->unit_cost;
            }
        }
    }

    public function save(): void
    {
        $this->validate([
            'name'            => 'required|string|max:255',
            'resource_type'   => 'required|in:material,equipo,herramienta,otro',
            'warehouse_id'    => 'nullable|exists:warehouses,id',
            'quantity_needed' => 'required|numeric|min:0.0001',
            'quantity_used'   => 'required|numeric|min:0',
            'unit_cost'       => 'required|numeric|min:0',
            'task_id'         => 'nullable|exists:project_tasks,id',
        ]);

        DB::transaction(function () {
            $needed = (float) $this->quantity_needed;

            // ── Liberar reserva anterior al editar ──────────────────────────
            if ($this->editingId) {
                $prev = ProjectMaterial::findOrFail($this->editingId);
                $this->releaseStockCommit($prev);
            }

            // ── Determinar reserva y estado ─────────────────────────────────
            $quantityReserved = 0.0;
            $newStatus        = $this->status;
            $requisitionId    = null;

            if ($this->product_id && $this->warehouse_id && in_array($newStatus, ['pendiente', 'reservado', 'solicitado'])) {
                $stock     = Stock::where('product_id', $this->product_id)
                                  ->where('warehouse_id', $this->warehouse_id)
                                  ->first();
                $available = $stock ? (float) $stock->available_quantity : 0.0;

                if ($available >= $needed) {
                    // Stock suficiente — reservar todo
                    $stock->commit($needed);
                    $quantityReserved = $needed;
                    $newStatus        = 'reservado';
                } else {
                    // Stock insuficiente — reservar lo que hay y levantar requisición
                    if ($available > 0) {
                        $stock->commit($available);
                        $quantityReserved = $available;
                    }

                    $shortfall     = $needed - $quantityReserved;
                    $requisitionId = $this->createAutoRequisition($shortfall);
                    $newStatus     = 'solicitado';
                }
            }

            $data = [
                'project_id'              => $this->project->id,
                'product_id'              => $this->product_id,
                'warehouse_id'            => $this->warehouse_id,
                'name'                    => $this->name,
                'resource_type'           => $this->resource_type,
                'unit'                    => $this->unit ?: null,
                'quantity_needed'         => $needed,
                'quantity_reserved'       => $quantityReserved,
                'quantity_used'           => $this->quantity_used,
                'unit_cost'               => $this->unit_cost,
                'status'                  => $newStatus,
                'task_id'                 => $this->task_id,
                'notes'                   => $this->notes ?: null,
            ];

            if ($requisitionId) {
                $data['purchase_requisition_id'] = $requisitionId;
            }

            if ($this->editingId) {
                $prev = ProjectMaterial::findOrFail($this->editingId);
                // Conservar requisición previa si no se creó una nueva
                if (!$requisitionId && $prev->purchase_requisition_id) {
                    $data['purchase_requisition_id'] = $prev->purchase_requisition_id;
                }
                $prev->update($data);
            } else {
                ProjectMaterial::create($data);
            }
        });

        $this->showModal = false;
    }

    public function updateStatus(int $id, string $status): void
    {
        ProjectMaterial::findOrFail($id)->update(['status' => $status]);
    }

    public function delete(int $id): void
    {
        $material = ProjectMaterial::findOrFail($id);
        $this->releaseStockCommit($material);
        $material->delete();
    }

    // ── Solicitar compra manualmente (para pendiente sin producto/almacén) ────

    public function requestPurchase(int $id): void
    {
        $material = ProjectMaterial::findOrFail($id);

        if ($material->purchase_requisition_id) {
            session()->flash('info', 'Ya existe una requisición para este recurso.');
            return;
        }

        $shortfall     = (float) $material->quantity_needed - (float) $material->quantity_reserved;
        $requisitionId = $this->createAutoRequisitionForMaterial($material, $shortfall);

        $material->update([
            'purchase_requisition_id' => $requisitionId,
            'status'                  => 'solicitado',
        ]);

        session()->flash('success', 'Requisición de compra creada correctamente.');
    }

    // ── Helpers privados ──────────────────────────────────────────────────────

    private function releaseStockCommit(ProjectMaterial $material): void
    {
        if ((float) $material->quantity_reserved > 0 && $material->warehouse_id && $material->product_id) {
            $stock = Stock::where('product_id', $material->product_id)
                          ->where('warehouse_id', $material->warehouse_id)
                          ->first();
            if ($stock) {
                $stock->release((float) $material->quantity_reserved);
            }
        }
    }

    private function createAutoRequisition(float $shortfall): int
    {
        $companyId = auth()->user()->company_id;
        $reqType   = match($this->resource_type) {
            'equipo', 'herramienta' => 'tool',
            default                  => 'material',
        };

        $folio = 'REQ-' . str_pad(
            PurchaseRequisition::where('company_id', $companyId)->count() + 1,
            5, '0', STR_PAD_LEFT
        );

        $req = PurchaseRequisition::create([
            'company_id'       => $companyId,
            'project_id'       => $this->project->id,
            'project_name'     => $this->project->name,
            'branch_id'        => $this->project->branch_id,
            'requested_by'     => auth()->id(),
            'folio'            => $folio,
            'currency'         => $this->project->currency ?? 'MXN',
            'requisition_type' => $reqType,
            'priority'         => 'normal',
            'expense_type'     => 'project',
            'status'           => 'draft',
            'justification'    => "Faltante de inventario para proyecto {$this->project->code}: {$this->project->name}",
        ]);

        PurchaseRequisitionItem::create([
            'purchase_requisition_id' => $req->id,
            'product_id'              => $this->product_id,
            'item_type'               => $reqType === 'tool' ? 'tool' : 'product',
            'description'             => $this->name . ($this->notes ? ' — ' . $this->notes : ''),
            'quantity'                => $shortfall,
            'unit'                    => $this->unit,
            'unit_price'              => $this->unit_cost,
        ]);

        return $req->id;
    }

    private function createAutoRequisitionForMaterial(ProjectMaterial $material, float $shortfall): int
    {
        $companyId = auth()->user()->company_id;
        $reqType   = match($material->resource_type) {
            'equipo', 'herramienta' => 'tool',
            default                  => 'material',
        };

        $folio = 'REQ-' . str_pad(
            PurchaseRequisition::where('company_id', $companyId)->count() + 1,
            5, '0', STR_PAD_LEFT
        );

        $req = PurchaseRequisition::create([
            'company_id'       => $companyId,
            'project_id'       => $material->project_id,
            'project_name'     => $material->project->name,
            'branch_id'        => $material->project->branch_id,
            'requested_by'     => auth()->id(),
            'folio'            => $folio,
            'currency'         => $material->project->currency ?? 'MXN',
            'requisition_type' => $reqType,
            'priority'         => 'normal',
            'expense_type'     => 'project',
            'status'           => 'draft',
            'justification'    => "Requerido para proyecto {$material->project->code}: {$material->project->name}",
        ]);

        PurchaseRequisitionItem::create([
            'purchase_requisition_id' => $req->id,
            'product_id'              => $material->product_id,
            'item_type'               => $reqType === 'tool' ? 'tool' : 'product',
            'description'             => $material->name . ($material->notes ? ' — ' . $material->notes : ''),
            'quantity'                => $shortfall,
            'unit'                    => $material->unit,
            'unit_price'              => $material->unit_cost,
        ]);

        return $req->id;
    }

    public function render()
    {
        $materials = $this->project->materials()
            ->with(['product', 'task', 'warehouse', 'purchaseRequisition'])
            ->when($this->filterType,   fn($q) => $q->where('resource_type', $this->filterType))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->orderBy('resource_type')
            ->orderBy('name')
            ->get();

        // Stock disponible para el producto+almacén seleccionado en el modal
        $stockAvailable = null;
        if ($this->product_id && $this->warehouse_id) {
            $stock          = Stock::where('product_id', $this->product_id)
                                   ->where('warehouse_id', $this->warehouse_id)
                                   ->first();
            $stockAvailable = $stock ? (float) $stock->available_quantity : 0.0;
        }

        $summary = [
            'total_cost'  => $materials->sum(fn($m) => $m->quantity_needed * $m->unit_cost),
            'total_used'  => $materials->sum(fn($m) => $m->quantity_used   * $m->unit_cost),
            'pendiente'   => $materials->where('status', 'pendiente')->count(),
            'reservado'   => $materials->where('status', 'reservado')->count(),
            'solicitado'  => $materials->where('status', 'solicitado')->count(),
            'adquirido'   => $materials->where('status', 'adquirido')->count(),
        ];

        $tasks      = $this->project->tasks()->whereNull('parent_task_id')->orderBy('sort_order')->get(['id', 'title']);
        $products   = Product::where('company_id', auth()->user()->company_id)
                             ->where('is_active', true)
                             ->orderBy('name')
                             ->get(['id', 'name', 'sale_price']);
        $warehouses = Warehouse::where('company_id', auth()->user()->company_id)
                               ->orderBy('name')
                               ->get(['id', 'name']);

        return view('livewire.projects.project-materials',
            compact('materials', 'summary', 'tasks', 'products', 'warehouses', 'stockAvailable'));
    }
}

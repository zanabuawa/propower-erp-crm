<?php

namespace App\Livewire\Projects;

use App\Models\Product;
use App\Models\Project;
use App\Models\ProjectMaterial;
use App\Models\ProjectTask;
use App\Models\PurchaseRequisition;
use App\Models\PurchaseRequisitionItem;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ProjectMaterials extends Component
{
    public Project $project;

    public bool $showModal = false;
    public ?int $editingId = null;

    // Form fields
    public ?int  $product_id = null;
    public string $name = '';
    public string $resource_type = 'material';
    public string $unit = '';
    public string $quantity_needed = '1';
    public string $quantity_used = '0';
    public string $unit_cost = '0';
    public string $status = 'pendiente';
    public ?int  $task_id = null;
    public string $notes = '';

    // Filters
    public string $filterType = '';
    public string $filterStatus = '';

    public function mount(Project $project): void
    {
        $this->project = $project;
    }

    public function openCreate(): void
    {
        $this->reset('editingId', 'product_id', 'name', 'unit', 'notes', 'task_id');
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
        $this->name            = $m->name;
        $this->resource_type   = $m->resource_type;
        $this->unit            = $m->unit ?? '';
        $this->quantity_needed = $m->quantity_needed;
        $this->quantity_used   = $m->quantity_used;
        $this->unit_cost       = $m->unit_cost;
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
                $this->unit_cost = $this->unit_cost == '0' ? ($product->sale_price ?? 0) : $this->unit_cost;
            }
        }
    }

    public function save(): void
    {
        $this->validate([
            'name'           => 'required|string|max:255',
            'resource_type'  => 'required|in:material,equipo,herramienta,otro',
            'quantity_needed'=> 'required|numeric|min:0',
            'quantity_used'  => 'required|numeric|min:0',
            'unit_cost'      => 'required|numeric|min:0',
            'status'         => 'required|in:pendiente,solicitado,adquirido,utilizado,devuelto',
            'task_id'        => 'nullable|exists:project_tasks,id',
        ]);

        $data = [
            'project_id'      => $this->project->id,
            'product_id'      => $this->product_id,
            'name'            => $this->name,
            'resource_type'   => $this->resource_type,
            'unit'            => $this->unit ?: null,
            'quantity_needed' => $this->quantity_needed,
            'quantity_used'   => $this->quantity_used,
            'unit_cost'       => $this->unit_cost,
            'status'          => $this->status,
            'task_id'         => $this->task_id,
            'notes'           => $this->notes ?: null,
        ];

        if ($this->editingId) {
            ProjectMaterial::findOrFail($this->editingId)->update($data);
        } else {
            ProjectMaterial::create($data);
        }

        $this->showModal = false;
    }

    public function updateStatus(int $id, string $status): void
    {
        ProjectMaterial::findOrFail($id)->update(['status' => $status]);
    }

    public function delete(int $id): void
    {
        ProjectMaterial::findOrFail($id)->delete();
    }

    // ── Solicitar compra ───────────────────────────────────────────────────────

    public function requestPurchase(int $id): void
    {
        $material = ProjectMaterial::findOrFail($id);

        if ($material->purchase_requisition_id) {
            session()->flash('info', 'Ya existe una requisición para este recurso.');
            return;
        }

        $companyId = auth()->user()->company_id;

        // Determine requisition type from resource_type
        $reqType = match($material->resource_type) {
            'equipo', 'herramienta' => 'tool',
            default                  => 'material',
        };

        $folio = 'REQ-PROJ-' . str_pad(
            PurchaseRequisition::where('company_id', $companyId)->count() + 1, 4, '0', STR_PAD_LEFT
        );

        $req = PurchaseRequisition::create([
            'company_id'       => $companyId,
            'project_id'       => $this->project->id,
            'project_name'     => $this->project->name,
            'branch_id'        => $this->project->branch_id,
            'requested_by'     => auth()->id(),
            'folio'            => $folio,
            'currency'         => $this->project->currency,
            'requisition_type' => $reqType,
            'priority'         => 'normal',
            'expense_type'     => 'project',
            'status'           => 'draft',
            'justification'    => "Requerido para proyecto {$this->project->code}: {$this->project->name}",
        ]);

        PurchaseRequisitionItem::create([
            'purchase_requisition_id' => $req->id,
            'product_id'              => $material->product_id,
            'item_type'               => $reqType,
            'description'             => $material->name . ($material->notes ? ' — ' . $material->notes : ''),
            'quantity'                => $material->quantity_needed,
            'unit'                    => $material->unit,
            'unit_price'              => $material->unit_cost,
        ]);

        $material->update([
            'purchase_requisition_id' => $req->id,
            'status'                  => 'solicitado',
        ]);

        session()->flash('success', "Requisición {$folio} creada correctamente.");
    }

    public function render()
    {
        $materials = $this->project->materials()
            ->with(['product', 'task'])
            ->when($this->filterType,   fn($q) => $q->where('resource_type', $this->filterType))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->orderBy('resource_type')
            ->orderBy('name')
            ->get();

        $summary = [
            'total_cost'   => $materials->sum(fn($m) => $m->quantity_needed * $m->unit_cost),
            'total_used'   => $materials->sum(fn($m) => $m->quantity_used   * $m->unit_cost),
            'pending'      => $materials->where('status', 'pendiente')->count(),
            'acquired'     => $materials->where('status', 'adquirido')->count(),
        ];

        $tasks    = $this->project->tasks()->whereNull('parent_task_id')->orderBy('sort_order')->get(['id', 'title']);
        $products = Product::where('company_id', auth()->user()->company_id)
                           ->where('is_active', true)
                           ->orderBy('name')
                           ->get(['id', 'name', 'sale_price']);

        return view('livewire.projects.project-materials', compact('materials', 'summary', 'tasks', 'products'));
    }
}

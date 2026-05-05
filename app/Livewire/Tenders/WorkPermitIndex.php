<?php

namespace App\Livewire\Tenders;

use App\Models\Project;
use App\Models\Tender;
use App\Models\User;
use App\Models\WorkPermit;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Permisos de Trabajo')]
class WorkPermitIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatus = '';
    public string $filterType = '';
    public bool $showModal = false;
    public ?int $editingId = null;

    public ?int $project_id = null;
    public ?int $tender_id = null;
    public string $type = 'general';
    public string $description = '';
    public ?int $issued_by = null;
    public string $valid_from = '';
    public string $valid_until = '';
    public string $status = 'activo';
    public string $notes = '';

    public function updatedSearch(): void { $this->resetPage(); }

    public function openModal(?int $id = null): void
    {
        $this->resetForm();
        $this->valid_from  = now()->format('Y-m-d');
        $this->valid_until = now()->addDays(7)->format('Y-m-d');
        $this->issued_by   = auth()->id();
        if ($id) {
            $permit = WorkPermit::findOrFail($id);
            $this->editingId    = $id;
            $this->project_id   = $permit->project_id;
            $this->tender_id    = $permit->tender_id;
            $this->type         = $permit->type;
            $this->description  = $permit->description;
            $this->issued_by    = $permit->issued_by;
            $this->valid_from   = $permit->valid_from->format('Y-m-d');
            $this->valid_until  = $permit->valid_until->format('Y-m-d');
            $this->status       = $permit->status;
            $this->notes        = $permit->notes ?? '';
        }
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'project_id'  => 'required|exists:projects,id',
            'type'        => 'required|in:' . implode(',', array_keys(WorkPermit::TYPES)),
            'description' => 'required|string|max:500',
            'valid_from'  => 'required|date',
            'valid_until' => 'required|date|after_or_equal:valid_from',
        ]);

        $data = [
            'project_id'  => $this->project_id,
            'tender_id'   => $this->tender_id ?: null,
            'type'        => $this->type,
            'description' => $this->description,
            'issued_by'   => $this->issued_by,
            'valid_from'  => $this->valid_from,
            'valid_until' => $this->valid_until,
            'status'      => $this->status,
            'notes'       => $this->notes ?: null,
        ];

        if ($this->editingId) {
            WorkPermit::findOrFail($this->editingId)->update($data);
        } else {
            WorkPermit::create($data);
        }

        $this->showModal = false;
        $this->resetForm();
        session()->flash('success', 'Permiso guardado.');
    }

    public function delete(int $id): void
    {
        WorkPermit::findOrFail($id)->delete();
        session()->flash('success', 'Permiso eliminado.');
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->project_id = null; $this->tender_id = null;
        $this->type = 'general'; $this->description = '';
        $this->issued_by = null; $this->valid_from = '';
        $this->valid_until = ''; $this->status = 'activo'; $this->notes = '';
    }

    public function render()
    {
        $companyId = auth()->user()->company_id;
        $permits = WorkPermit::whereHas('project', fn($q) => $q->whereHas('branch', fn($bq) => $bq->where('company_id', $companyId)))
            ->when($this->search, fn($q) => $q->where('description', 'like', '%' . $this->search . '%'))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterType,   fn($q) => $q->where('type', $this->filterType))
            ->with(['project', 'tender', 'issuedBy'])
            ->latest()
            ->paginate(15);

        return view('livewire.tenders.work-permit-index', [
            'permits'  => $permits,
            'projects' => Project::whereHas('branch', fn($q) => $q->where('company_id', $companyId))->orderBy('name')->get(),
            'tenders'  => Tender::where('company_id', $companyId)->orderBy('name')->get(),
            'users'    => User::where('company_id', $companyId)->orderBy('name')->get(),
            'types'    => WorkPermit::TYPES,
            'statuses' => WorkPermit::STATUSES,
        ]);
    }
}

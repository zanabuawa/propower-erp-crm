<?php

namespace App\Livewire\Tenders;

use App\Models\Project;
use App\Models\WorkPermit;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Permisos de Trabajo')]
class WorkPermitIndex extends Component
{
    use WithFileUploads, WithPagination;

    public string $search = '';
    public ?int $filterProject = null;
    public ?int $contextProjectId = null;
    public bool $embedded = false;
    public bool $showModal = false;
    public ?int $editingId = null;

    public ?int $project_id = null;
    public string $document_name = '';
    public string $document_date = '';
    public mixed $document = null;
    public ?string $currentDocumentName = null;

    public function mount(?Project $project = null, bool $embedded = false): void
    {
        $this->embedded = $embedded;
        $projectId = $project?->id ?: (request()->integer('project_id') ?: null);

        if ($projectId) {
            $companyId = auth()->user()->company_id;
            $exists = Project::whereKey($projectId)
                ->whereHas('branch', fn ($query) => $query->where('company_id', $companyId))
                ->exists();

            $this->filterProject = $exists ? $projectId : null;
            $this->contextProjectId = $project?->id && $exists ? $project->id : null;
        }
    }

    public function updatedSearch(): void { $this->resetPage(); }

    public function openModal(?int $id = null): void
    {
        $this->resetForm();
        $this->project_id  = $this->filterProject;
        $this->document_date = now()->toDateString();
        if ($id) {
            $permit = WorkPermit::query()
                ->when($this->contextProjectId, fn ($query) => $query->where('project_id', $this->contextProjectId))
                ->findOrFail($id);
            $this->editingId    = $id;
            $this->project_id   = $permit->project_id;
            $this->document_name = $permit->description;
            $this->document_date = $permit->valid_from?->toDateString() ?? now()->toDateString();
            $this->currentDocumentName = $permit->document_original_name;
        }
        $this->showModal = true;
    }

    public function save(): void
    {
        if ($this->contextProjectId) {
            $this->project_id = $this->contextProjectId;
        }

        $this->validate([
            'project_id'  => 'required|exists:projects,id',
            'document_name' => 'required|string|max:255',
            'document_date' => 'required|date',
            'document'    => ($this->editingId ? 'nullable' : 'required') . '|file|mimes:pdf,jpg,jpeg,png,webp|max:10240',
        ]);

        $documentPath = null;
        $documentOriginalName = null;

        if ($this->document) {
            $documentOriginalName = $this->document->getClientOriginalName();
            $documentPath = $this->document->store('works/permits', 'public');
        }

        $data = [
            'project_id'  => $this->project_id,
            'type'        => 'general',
            'description' => $this->document_name,
            'issued_by'   => auth()->id(),
            'valid_from'  => $this->document_date,
            'valid_until' => $this->document_date,
            'status'      => 'activo',
        ];

        if ($documentPath) {
            $data['document_path'] = $documentPath;
            $data['document_original_name'] = $documentOriginalName;
        }

        if ($this->editingId) {
            WorkPermit::query()
                ->when($this->contextProjectId, fn ($query) => $query->where('project_id', $this->contextProjectId))
                ->findOrFail($this->editingId)
                ->update($data);
        } else {
            WorkPermit::create($data);
        }

        $this->showModal = false;
        $this->resetForm();
        session()->flash('success', 'Documento de permiso guardado.');
    }

    public function delete(int $id): void
    {
        WorkPermit::query()
            ->when($this->contextProjectId, fn ($query) => $query->where('project_id', $this->contextProjectId))
            ->findOrFail($id)
            ->delete();
        session()->flash('success', 'Permiso eliminado.');
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->project_id = null;
        $this->document_name = '';
        $this->document_date = '';
        $this->document = null;
        $this->currentDocumentName = null;
    }

    public function render()
    {
        if ($this->contextProjectId) {
            $this->filterProject = $this->contextProjectId;
        }

        $companyId = auth()->user()->company_id;
        $permits = WorkPermit::whereHas('project', fn($q) => $q->whereHas('branch', fn($bq) => $bq->where('company_id', $companyId)))
            ->when($this->search, fn($q) => $q->where(function ($query) {
                $query->where('description', 'like', '%' . $this->search . '%')
                    ->orWhere('document_original_name', 'like', '%' . $this->search . '%');
            }))
            ->when($this->filterProject, fn($q) => $q->where('project_id', $this->filterProject))
            ->with(['project', 'issuedBy'])
            ->latest()
            ->paginate(15, pageName: 'permitsPage');

        return view('livewire.tenders.work-permit-index', [
            'permits'  => $permits,
            'projects' => Project::whereHas('branch', fn($q) => $q->where('company_id', $companyId))->orderBy('name')->get(),
        ]);
    }
}

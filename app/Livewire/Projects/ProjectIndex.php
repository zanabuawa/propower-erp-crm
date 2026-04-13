<?php

namespace App\Livewire\Projects;

use App\Models\Branch;
use App\Models\Project;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ProjectIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatus = '';
    public string $filterType = '';
    public ?int $filterBranch = null;

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilterStatus(): void { $this->resetPage(); }
    public function updatingFilterType(): void { $this->resetPage(); }
    public function updatingFilterBranch(): void { $this->resetPage(); }

    public function render()
    {
        $projects = Project::with(['customer', 'branch', 'responsible'])
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->where('code', 'like', "%{$this->search}%")
                  ->orWhere('name', 'like', "%{$this->search}%");
            }))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterType, fn($q) => $q->where('type', $this->filterType))
            ->when($this->filterBranch, fn($q) => $q->where('branch_id', $this->filterBranch))
            ->orderByDesc('id')
            ->paginate(20);

        $branches = Branch::where('company_id', auth()->user()->company_id)->orderBy('name')->get();

        return view('livewire.projects.project-index', compact('projects', 'branches'));
    }
}

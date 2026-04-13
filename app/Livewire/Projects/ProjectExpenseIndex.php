<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use App\Models\ProjectExpense;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ProjectExpenseIndex extends Component
{
    use WithPagination;

    public Project $project;

    public string $search = '';
    public string $filterCategory = '';
    public string $filterStatus = '';

    // Formulario inline
    public bool $showForm = false;
    public ?int $editingId = null;
    public string $concept = '';
    public string $category = 'otro';
    public string $amount = '';
    public string $currency = 'MXN';
    public string $expense_date = '';
    public string $reference = '';
    public string $status = 'pendiente';
    public string $notes = '';

    public function mount(Project $project): void
    {
        $this->project      = $project;
        $this->expense_date = now()->format('Y-m-d');
    }

    public function updatingSearch(): void { $this->resetPage(); }

    public function edit(int $id): void
    {
        $expense = ProjectExpense::findOrFail($id);
        $this->editingId    = $id;
        $this->concept      = $expense->concept;
        $this->category     = $expense->category;
        $this->amount       = $expense->amount;
        $this->currency     = $expense->currency;
        $this->expense_date = $expense->expense_date->format('Y-m-d');
        $this->reference    = $expense->reference ?? '';
        $this->status       = $expense->status;
        $this->notes        = $expense->notes ?? '';
        $this->showForm     = true;
    }

    public function save(): void
    {
        $this->validate([
            'concept'      => 'required|string|max:255',
            'category'     => 'required|in:material,mano_obra,subcontrato,transporte,viaje,otro',
            'amount'       => 'required|numeric|min:0.01',
            'currency'     => 'required|string|size:3',
            'expense_date' => 'required|date',
            'reference'    => 'nullable|string|max:100',
            'status'       => 'required|in:pendiente,aprobado,rechazado,pagado',
            'notes'        => 'nullable|string|max:500',
        ]);

        $data = [
            'project_id'    => $this->project->id,
            'registered_by' => auth()->id(),
            'concept'       => $this->concept,
            'category'      => $this->category,
            'amount'        => $this->amount,
            'currency'      => $this->currency,
            'expense_date'  => $this->expense_date,
            'reference'     => $this->reference ?: null,
            'status'        => $this->status,
            'notes'         => $this->notes ?: null,
        ];

        if ($this->editingId) {
            ProjectExpense::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Gasto actualizado.');
        } else {
            ProjectExpense::create($data);
            // Recalcular costo real del proyecto
            $total = $this->project->expenses()->where('status', '!=', 'rechazado')->sum('amount');
            $this->project->update(['cost_actual' => $total]);
            session()->flash('success', 'Gasto registrado.');
        }

        $this->resetForm();
    }

    public function delete(int $id): void
    {
        ProjectExpense::findOrFail($id)->delete();
        $total = $this->project->expenses()->where('status', '!=', 'rechazado')->sum('amount');
        $this->project->update(['cost_actual' => $total]);
        session()->flash('success', 'Gasto eliminado.');
    }

    public function resetForm(): void
    {
        $this->reset('concept', 'category', 'amount', 'currency', 'reference', 'status', 'notes', 'editingId', 'showForm');
        $this->expense_date = now()->format('Y-m-d');
        $this->currency     = 'MXN';
        $this->category     = 'otro';
        $this->status       = 'pendiente';
    }

    public function render()
    {
        $expenses = ProjectExpense::with('registeredBy')
            ->where('project_id', $this->project->id)
            ->when($this->search, fn($q) => $q->where('concept', 'like', "%{$this->search}%"))
            ->when($this->filterCategory, fn($q) => $q->where('category', $this->filterCategory))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->orderByDesc('expense_date')
            ->paginate(20);

        $totalByStatus = ProjectExpense::where('project_id', $this->project->id)
            ->selectRaw('status, sum(amount) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('livewire.projects.project-expense-index', compact('expenses', 'totalByStatus'));
    }
}

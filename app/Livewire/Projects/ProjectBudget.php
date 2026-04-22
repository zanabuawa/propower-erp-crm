<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use App\Models\ProjectBudgetLine;
use App\Models\ProjectBudgetVersion;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ProjectBudget extends Component
{
    public Project $project;

    // Version management
    public bool $showVersionModal = false;
    public ?int $editingVersionId = null;
    public string $versionName = '';
    public string $versionDescription = '';
    public string $versionNotes = '';

    // Active version for viewing/editing lines
    public ?int $activeVersionId = null;

    // Line management
    public bool $showLineModal = false;
    public ?int $editingLineId = null;
    public string $lineCategory = 'material';
    public string $lineConcept = '';
    public string $lineDescription = '';
    public string $lineUnit = '';
    public string $lineQuantity = '1';
    public string $lineUnitCost = '0';
    public string $lineNotes = '';

    public function mount(Project $project): void
    {
        $this->project = $project;

        $active = $project->budgetVersions()->where('status', 'vigente')->first()
                ?? $project->budgetVersions()->first();
        $this->activeVersionId = $active?->id;
    }

    // ── Versiones ──────────────────────────────────────────────────────────────

    public function openVersionCreate(): void
    {
        $this->reset('editingVersionId', 'versionName', 'versionDescription', 'versionNotes');
        $this->showVersionModal = true;
    }

    public function openVersionEdit(int $id): void
    {
        $v = ProjectBudgetVersion::findOrFail($id);
        $this->editingVersionId  = $v->id;
        $this->versionName       = $v->name;
        $this->versionDescription = $v->description ?? '';
        $this->versionNotes      = $v->notes ?? '';
        $this->showVersionModal  = true;
    }

    public function saveVersion(): void
    {
        $this->validate([
            'versionName' => 'required|string|max:150',
        ]);

        if ($this->editingVersionId) {
            ProjectBudgetVersion::findOrFail($this->editingVersionId)->update([
                'name'        => $this->versionName,
                'description' => $this->versionDescription ?: null,
                'notes'       => $this->versionNotes ?: null,
            ]);
        } else {
            $nextVersion = $this->project->budgetVersions()->max('version') + 1;
            $v = ProjectBudgetVersion::create([
                'project_id'  => $this->project->id,
                'version'     => $nextVersion,
                'name'        => $this->versionName,
                'description' => $this->versionDescription ?: null,
                'notes'       => $this->versionNotes ?: null,
                'status'      => 'borrador',
            ]);
            $this->activeVersionId = $v->id;
        }

        $this->showVersionModal = false;
    }

    public function activateVersion(int $id): void
    {
        // Mark all others as historico, this one as vigente
        $this->project->budgetVersions()
            ->where('id', '!=', $id)
            ->where('status', 'vigente')
            ->update(['status' => 'historico']);

        ProjectBudgetVersion::findOrFail($id)->update([
            'status'      => 'vigente',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        // Sync project budget from active version total
        $total = ProjectBudgetLine::where('version_id', $id)->sum('budgeted_amount');
        $this->project->update(['budget' => $total]);

        $this->activeVersionId = $id;
    }

    public function duplicateVersion(int $id): void
    {
        $source = ProjectBudgetVersion::with('lines')->findOrFail($id);
        $nextVersion = $this->project->budgetVersions()->max('version') + 1;

        $newVersion = ProjectBudgetVersion::create([
            'project_id'  => $this->project->id,
            'version'     => $nextVersion,
            'name'        => $source->name . ' (copia)',
            'description' => $source->description,
            'notes'       => $source->notes,
            'status'      => 'borrador',
        ]);

        foreach ($source->lines as $line) {
            $newVersion->lines()->create($line->only([
                'project_id', 'category', 'concept', 'description',
                'unit', 'quantity', 'unit_cost', 'budgeted_amount', 'notes', 'sort_order',
            ]));
        }

        $this->activeVersionId = $newVersion->id;
    }

    // ── Líneas ─────────────────────────────────────────────────────────────────

    public function openLineCreate(): void
    {
        $this->reset('editingLineId', 'lineConcept', 'lineDescription', 'lineUnit', 'lineNotes');
        $this->lineCategory = 'material';
        $this->lineQuantity = '1';
        $this->lineUnitCost = '0';
        $this->showLineModal = true;
    }

    public function openLineEdit(int $id): void
    {
        $line = ProjectBudgetLine::findOrFail($id);
        $this->editingLineId   = $line->id;
        $this->lineCategory    = $line->category;
        $this->lineConcept     = $line->concept;
        $this->lineDescription = $line->description ?? '';
        $this->lineUnit        = $line->unit ?? '';
        $this->lineQuantity    = $line->quantity;
        $this->lineUnitCost    = $line->unit_cost;
        $this->lineNotes       = $line->notes ?? '';
        $this->showLineModal   = true;
    }

    public function saveLine(): void
    {
        $this->validate([
            'lineCategory' => 'required|in:material,mano_obra,subcontrato,viaticos,indirectos,otros',
            'lineConcept'  => 'required|string|max:255',
            'lineQuantity' => 'required|numeric|min:0',
            'lineUnitCost' => 'required|numeric|min:0',
        ]);

        $amount = (float)$this->lineQuantity * (float)$this->lineUnitCost;

        $data = [
            'project_id'       => $this->project->id,
            'version_id'       => $this->activeVersionId,
            'category'         => $this->lineCategory,
            'concept'          => $this->lineConcept,
            'description'      => $this->lineDescription ?: null,
            'unit'             => $this->lineUnit ?: null,
            'quantity'         => $this->lineQuantity,
            'unit_cost'        => $this->lineUnitCost,
            'budgeted_amount'  => $amount,
            'notes'            => $this->lineNotes ?: null,
        ];

        if ($this->editingLineId) {
            ProjectBudgetLine::findOrFail($this->editingLineId)->update($data);
        } else {
            ProjectBudgetLine::create($data);
        }

        $this->showLineModal = false;
    }

    public function deleteLine(int $id): void
    {
        ProjectBudgetLine::findOrFail($id)->delete();
    }

    // ── Render ─────────────────────────────────────────────────────────────────

    public function render()
    {
        $versions = $this->project->budgetVersions()->withCount('lines')->get();

        $activeVersion = $this->activeVersionId
            ? ProjectBudgetVersion::with('lines')->find($this->activeVersionId)
            : null;

        // Executed amounts by category (from expenses, non-rejected)
        $executed = $this->project->expenses()
            ->where('status', '!=', 'rechazado')
            ->select('category', DB::raw('sum(amount) as total'))
            ->groupBy('category')
            ->get()
            ->pluck('total', 'category');

        // Map expense categories to budget categories (some share names)
        $expenseCategoryMap = [
            'material'    => 'material',
            'mano_obra'   => 'mano_obra',
            'subcontrato' => 'subcontrato',
            'transporte'  => 'viaticos',
            'viaje'       => 'viaticos',
            'otro'        => 'otros',
        ];

        $executedByBudgetCat = [];
        foreach ($executed as $cat => $total) {
            $budgetCat = $expenseCategoryMap[$cat] ?? 'otros';
            $executedByBudgetCat[$budgetCat] = ($executedByBudgetCat[$budgetCat] ?? 0) + $total;
        }

        // Comparison by category using active version lines
        $comparison = [];
        if ($activeVersion) {
            $grouped = $activeVersion->lines->groupBy('category');
            foreach (ProjectBudgetLine::$categoryLabels as $cat => $label) {
                $budgeted = $grouped->get($cat)?->sum('budgeted_amount') ?? 0;
                $exec = $executedByBudgetCat[$cat] ?? 0;
                if ($budgeted > 0 || $exec > 0) {
                    $comparison[] = [
                        'category'  => $cat,
                        'label'     => $label,
                        'budgeted'  => $budgeted,
                        'executed'  => $exec,
                        'variance'  => $budgeted - $exec,
                        'pct'       => $budgeted > 0 ? round(($exec / $budgeted) * 100, 1) : null,
                    ];
                }
            }
        }

        return view('livewire.projects.project-budget', compact('versions', 'activeVersion', 'comparison'));
    }
}

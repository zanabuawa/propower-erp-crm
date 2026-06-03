<?php

namespace App\Livewire\HR;

use App\Models\Branch;
use App\Models\HrDepartment;
use App\Models\HrPosition;
use App\Models\HrPositionHeadcount;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Formulario de Puesto')]
class PositionForm extends Component
{
    public ?HrPosition $position = null;

    public string $name                 = '';
    public string $code                 = '';
    public string $description          = '';
    public string $responsibilities     = '';
    public string $requirements         = '';
    public int    $authorized_headcount = 1;
    public ?int   $department_id        = null;
    public string $salary_type          = 'monthly';
    public string $min_salary           = '';
    public string $max_salary           = '';
    public bool   $is_active            = true;

    /** [ branch_id => headcount ] */
    public array $branchHeadcounts = [];

    public function mount(?HrPosition $position = null): void
    {
        if ($position && $position->exists) {
            $this->position             = $position;
            $this->name                 = $position->name;
            $this->code                 = $position->code ?? '';
            $this->description          = $position->description ?? '';
            $this->responsibilities     = $position->responsibilities ?? '';
            $this->requirements         = $position->requirements ?? '';
            $this->authorized_headcount = $position->authorized_headcount ?? 1;
            $this->department_id        = $position->department_id;
            $this->salary_type          = $position->salary_type;
            $this->min_salary           = (string) ($position->min_salary ?? '');
            $this->max_salary           = (string) ($position->max_salary ?? '');
            $this->is_active            = $position->is_active;

            // Load existing headcounts keyed by branch_id
            $this->branchHeadcounts = $position->headcounts()
                ->pluck('headcount', 'branch_id')
                ->map(fn($v) => (int) $v)
                ->toArray();
        }

        $this->syncBranchHeadcounts();
    }

    /** Ensure every branch has an entry (default 0) */
    private function syncBranchHeadcounts(): void
    {
        $companyId = auth()->user()->company_id;
        $branches  = Branch::where('company_id', $companyId)
            ->where('is_active', true)
            ->pluck('id');

        foreach ($branches as $id) {
            if (!array_key_exists($id, $this->branchHeadcounts)) {
                $this->branchHeadcounts[$id] = 0;
            }
        }
    }

    public function updatedBranchHeadcounts(): void
    {
        // Auto-sync authorized_headcount to the sum of branch values
        $this->authorized_headcount = (int) array_sum($this->branchHeadcounts);
    }

    public function rules(): array
    {
        return [
            'name'                 => 'required|string|max:100',
            'department_id'        => 'required|exists:hr_departments,id',
            'salary_type'          => 'required|in:' . implode(',', array_keys(HrPosition::SALARY_TYPES)),
            'min_salary'           => 'nullable|numeric|min:0',
            'max_salary'           => 'nullable|numeric|min:0',
            'authorized_headcount' => 'required|integer|min:0',
            'description'          => 'nullable|string',
            'responsibilities'     => 'nullable|string',
            'requirements'         => 'nullable|string',
            'is_active'            => 'boolean',
            'branchHeadcounts.*'   => 'integer|min:0',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'company_id'           => auth()->user()->company_id,
            'name'                 => $this->name,
            'code'                 => $this->code ?: null,
            'description'          => $this->description ?: null,
            'responsibilities'     => $this->responsibilities ?: null,
            'requirements'         => $this->requirements ?: null,
            'authorized_headcount' => $this->authorized_headcount,
            'department_id'        => $this->department_id,
            'salary_type'          => $this->salary_type,
            'min_salary'           => $this->min_salary ?: null,
            'max_salary'           => $this->max_salary ?: null,
            'is_active'            => $this->is_active,
        ];

        if ($this->position && $this->position->exists) {
            $this->position->update($data);
            $positionId = $this->position->id;
        } else {
            $positionId = HrPosition::create($data)->id;
        }

        // Sync branch headcounts
        $companyId = auth()->user()->company_id;
        foreach ($this->branchHeadcounts as $branchId => $headcount) {
            if ((int) $headcount > 0) {
                HrPositionHeadcount::updateOrCreate(
                    ['position_id' => $positionId, 'branch_id' => $branchId],
                    ['company_id'  => $companyId,  'headcount'  => (int) $headcount]
                );
            } else {
                HrPositionHeadcount::where('position_id', $positionId)
                    ->where('branch_id', $branchId)
                    ->delete();
            }
        }

        session()->flash('success', $this->position?->exists
            ? 'Puesto actualizado correctamente.'
            : 'Puesto creado correctamente.'
        );

        $this->redirect(route('hr.positions.index'), navigate: true);
    }

    public function render()
    {
        $departments = HrDepartment::where('is_active', true)->orderBy('name')->get();
        $branches    = Branch::where('company_id', auth()->user()->company_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('livewire.hr.position-form', compact('departments', 'branches'));
    }
}

<?php

namespace App\Livewire\HR;

use App\Models\Branch;
use App\Models\HrEmployee;
use App\Models\HrJobOpening;
use App\Models\HrPosition;
use App\Models\HrPositionHeadcount;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Vacantes')]
class JobOpeningIndex extends Component
{
    use WithPagination;

    public string $search       = '';
    public string $filterStatus = '';
    public string $filterType   = '';
    public string $filterBranch = '';

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilterStatus(): void { $this->resetPage(); }
    public function updatingFilterType(): void { $this->resetPage(); }
    public function updatingFilterBranch(): void { $this->resetPage(); }

    public function toggleStatus(int $id): void
    {
        $o = HrJobOpening::findOrFail($id);
        $o->update(['status' => $o->status === 'open' ? 'paused' : 'open']);
    }

    public function close(int $id): void
    {
        HrJobOpening::findOrFail($id)->update(['status' => 'closed']);
    }

    public function delete(int $id): void
    {
        HrJobOpening::findOrFail($id)->delete();
        session()->flash('success', 'Vacante eliminada.');
    }

    public function render()
    {
        $companyId = auth()->user()->company_id;

        $openings = HrJobOpening::with(['position', 'branch'])
            ->withCount([
                'prospects',
                'prospects as active_prospects_count' => fn ($q) => $q->whereNotIn('status', ['rechazado', 'contratado']),
                'prospects as hired_prospects_count' => fn ($q) => $q->where('status', 'contratado'),
            ])
            ->where('company_id', $companyId)
            ->when($this->search,       fn($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterType,   fn($q) => $q->where('type',   $this->filterType))
            ->when($this->filterBranch, fn($q) => $q->where('branch_id', $this->filterBranch))
            ->latest()
            ->paginate(12);

        $stats = HrJobOpening::where('company_id', $companyId)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        // Headcount matrix: pull from position headcounts defined per branch
        $headcounts = HrPositionHeadcount::with(['position', 'branch'])
            ->where('company_id', $companyId)
            ->orderBy('branch_id')
            ->get()
            ->map(function ($hc) {
                $filled = HrEmployee::where('position_id', $hc->position_id)
                    ->where('branch_id', $hc->branch_id)
                    ->whereIn('status', ['active', 'on_leave'])
                    ->count();
                $recruiting = (int) HrJobOpening::where('position_id', $hc->position_id)
                    ->where('branch_id', $hc->branch_id)
                    ->whereIn('status', ['open', 'paused'])
                    ->sum('quantity');
                return [
                    'position_id' => $hc->position_id,
                    'branch_id'   => $hc->branch_id,
                    'position'    => $hc->position->name,
                    'branch'      => $hc->branch->name,
                    'headcount'   => $hc->headcount,
                    'filled'      => $filled,
                    'recruiting'  => $recruiting,
                    'available'   => max(0, $hc->headcount - $filled - $recruiting),
                ];
            });

        $branches = Branch::where('company_id', $companyId)->orderBy('name')->get(['id', 'name']);

        return view('livewire.hr.job-opening-index', compact(
            'openings', 'stats', 'headcounts', 'branches'
        ));
    }
}

<?php

namespace App\Livewire\HR;

use App\Models\Branch;
use App\Models\HrJobOpening;
use App\Models\HrPosition;
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

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilterStatus(): void { $this->resetPage(); }
    public function updatingFilterType(): void { $this->resetPage(); }

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
        $openings = HrJobOpening::with(['position', 'branch'])
            ->withCount('prospects')
            ->where('company_id', auth()->user()->company_id)
            ->when($this->search, fn($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterType,   fn($q) => $q->where('type',   $this->filterType))
            ->latest()
            ->paginate(12);

        $stats = HrJobOpening::where('company_id', auth()->user()->company_id)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('livewire.hr.job-opening-index', compact('openings', 'stats'));
    }
}

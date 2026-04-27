<?php

namespace App\Livewire\Tenders;

use App\Models\Project;
use App\Models\SiteVisit;
use App\Models\Tender;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Visitas de Campo')]
class SiteVisitIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatus = '';
    public string $filterType = '';

    public function updatedSearch(): void   { $this->resetPage(); }
    public function updatedFilterStatus(): void { $this->resetPage(); }

    public function delete(int $id): void
    {
        SiteVisit::findOrFail($id)->delete();
        session()->flash('success', 'Visita eliminada.');
    }

    public function render()
    {
        $companyId = auth()->user()->company_id;
        $visits = SiteVisit::where('company_id', $companyId)
            ->when($this->search, fn($q) => $q->where(fn($q2) =>
                $q2->where('purpose', 'like', '%' . $this->search . '%')
                   ->orWhere('address', 'like', '%' . $this->search . '%')
            ))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterType,   fn($q) => $q->where('visit_type', $this->filterType))
            ->with(['project', 'tender', 'createdBy'])
            ->latest('visit_date')
            ->paginate(15);

        return view('livewire.tenders.site-visit-index', [
            'visits'   => $visits,
            'statuses' => SiteVisit::STATUSES,
            'types'    => SiteVisit::TYPES,
        ]);
    }
}

<?php

namespace App\Livewire\Sales;

use App\Models\SalesProspect;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class CrmProspectIndex extends Component
{
    use WithPagination;

    public string $search        = '';
    public string $filterStatus  = '';
    public string $filterSource  = '';
    public string $filterUser    = '';

    public function updatingSearch(): void       { $this->resetPage(); }
    public function updatingFilterStatus(): void  { $this->resetPage(); }
    public function updatingFilterSource(): void  { $this->resetPage(); }
    public function updatingFilterUser(): void    { $this->resetPage(); }

    public function render()
    {
        $companyId = auth()->user()->company_id;

        $prospects = SalesProspect::query()
            ->where('company_id', $companyId)
            ->when($this->search, fn($q) => $q->where(fn($q2) => $q2
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('contact_name', 'like', "%{$this->search}%")
                ->orWhere('contact_email', 'like', "%{$this->search}%")))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterSource, fn($q) => $q->where('source', $this->filterSource))
            ->when($this->filterUser, fn($q) => $q->where('assigned_to', $this->filterUser))
            ->with(['assignedTo', 'opportunities'])
            ->withCount('activities')
            ->orderByRaw("FIELD(status,'new','contacted','qualified','disqualified','converted')")
            ->orderBy('next_follow_up')
            ->paginate(20);

        $summary = [
            'new'          => SalesProspect::where('company_id', $companyId)->where('status', 'new')->count(),
            'contacted'    => SalesProspect::where('company_id', $companyId)->where('status', 'contacted')->count(),
            'qualified'    => SalesProspect::where('company_id', $companyId)->where('status', 'qualified')->count(),
            'overdue'      => SalesProspect::where('company_id', $companyId)
                ->whereNotNull('next_follow_up')
                ->where('next_follow_up', '<', now())
                ->whereNotIn('status', ['converted', 'disqualified'])
                ->count(),
        ];

        $users = User::where('company_id', $companyId)->orderBy('name')->get();

        return view('livewire.sales.crm-prospect-index', compact('prospects', 'summary', 'users'));
    }
}

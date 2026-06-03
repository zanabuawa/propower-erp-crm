<?php

namespace App\Livewire\Sales;

use App\Models\CrmCampaign;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class CrmCampaignIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = '';
    public string $type   = '';

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedStatus(): void { $this->resetPage(); }
    public function updatedType():   void { $this->resetPage(); }

    public function render()
    {
        $cid = auth()->user()->company_id;

        $campaigns = CrmCampaign::where('company_id', $cid)
            ->with('createdBy')
            ->when($this->search, fn($q) => $q->where(fn($q) =>
                $q->where('folio', 'like', "%{$this->search}%")
                  ->orWhere('name',  'like', "%{$this->search}%")
            ))
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->type,   fn($q) => $q->where('type',   $this->type))
            ->orderByRaw("FIELD(status, 'active','paused','draft','completed','cancelled')")
            ->orderByDesc('created_at')
            ->paginate(20);

        $stats = [
            'active'    => CrmCampaign::where('company_id', $cid)->where('status', 'active')->count(),
            'leads'     => CrmCampaign::where('company_id', $cid)->sum('leads_generated'),
            'budget'    => CrmCampaign::where('company_id', $cid)->whereIn('status', ['active','completed'])->sum('budget'),
            'revenue'   => CrmCampaign::where('company_id', $cid)->sum('revenue_generated'),
        ];

        return view('livewire.sales.crm-campaign-index', compact('campaigns', 'stats'));
    }
}

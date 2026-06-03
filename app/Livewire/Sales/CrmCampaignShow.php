<?php

namespace App\Livewire\Sales;

use App\Models\CrmCampaign;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class CrmCampaignShow extends Component
{
    public CrmCampaign $campaign;

    public string $newStatus         = '';
    public string $spent             = '';
    public string $leads_generated   = '';
    public string $conversions       = '';
    public string $revenue_generated = '';

    public function mount(CrmCampaign $campaign): void
    {
        $this->campaign          = $campaign->load('createdBy');
        $this->newStatus         = $campaign->status;
        $this->spent             = $campaign->spent !== null ? (string) $campaign->spent : '';
        $this->leads_generated   = (string) $campaign->leads_generated;
        $this->conversions       = (string) $campaign->conversions;
        $this->revenue_generated = $campaign->revenue_generated !== null ? (string) $campaign->revenue_generated : '';
    }

    public function updateStatus(): void
    {
        $this->validate(['newStatus' => 'required|in:draft,active,paused,completed,cancelled']);
        $this->campaign->update(['status' => $this->newStatus]);
        $this->campaign->refresh();
    }

    public function updateMetrics(): void
    {
        $this->validate([
            'spent'             => 'nullable|numeric|min:0',
            'leads_generated'   => 'nullable|integer|min:0',
            'conversions'       => 'nullable|integer|min:0',
            'revenue_generated' => 'nullable|numeric|min:0',
        ]);

        $this->campaign->update([
            'spent'             => $this->spent             ?: null,
            'leads_generated'   => (int) ($this->leads_generated   ?: 0),
            'conversions'       => (int) ($this->conversions       ?: 0),
            'revenue_generated' => $this->revenue_generated ?: null,
        ]);

        $this->campaign->refresh();
        session()->flash('success', 'Métricas actualizadas.');
    }

    public function render()
    {
        return view('livewire.sales.crm-campaign-show');
    }
}

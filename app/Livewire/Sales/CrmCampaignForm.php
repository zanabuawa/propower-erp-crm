<?php

namespace App\Livewire\Sales;

use App\Models\CrmCampaign;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class CrmCampaignForm extends Component
{
    public ?CrmCampaign $campaign = null;

    public string $name            = '';
    public string $description     = '';
    public string $type            = 'email';
    public string $status          = 'draft';
    public string $target_audience = '';
    public string $budget          = '';
    public string $start_at        = '';
    public string $end_at          = '';

    public function mount(?CrmCampaign $campaign = null): void
    {
        if ($campaign && $campaign->exists) {
            $this->campaign        = $campaign;
            $this->name            = $campaign->name;
            $this->description     = $campaign->description ?? '';
            $this->type            = $campaign->type;
            $this->status          = $campaign->status;
            $this->target_audience = $campaign->target_audience ?? '';
            $this->budget          = $campaign->budget !== null ? (string) $campaign->budget : '';
            $this->start_at        = $campaign->start_at?->format('Y-m-d') ?? '';
            $this->end_at          = $campaign->end_at?->format('Y-m-d') ?? '';
        }
    }

    public function rules(): array
    {
        return [
            'name'            => 'required|string|max:255',
            'description'     => 'nullable|string',
            'type'            => 'required|in:email,whatsapp,sms,social_media,event,phone,other',
            'status'          => 'required|in:draft,active,paused,completed,cancelled',
            'target_audience' => 'nullable|string',
            'budget'          => 'nullable|numeric|min:0',
            'start_at'        => 'nullable|date',
            'end_at'          => 'nullable|date|after_or_equal:start_at',
        ];
    }

    public function save(): void
    {
        $data = $this->validate();
        $cid  = auth()->user()->company_id;

        $data['budget']   = $data['budget']   ?: null;
        $data['start_at'] = $data['start_at'] ?: null;
        $data['end_at']   = $data['end_at']   ?: null;

        if ($this->campaign?->exists) {
            $this->campaign->update($data);
            session()->flash('success', 'Campaña actualizada.');
        } else {
            $folio = 'CAM-' . str_pad(
                CrmCampaign::where('company_id', $cid)->count() + 1,
                5, '0', STR_PAD_LEFT
            );
            CrmCampaign::create(array_merge($data, [
                'company_id' => $cid,
                'created_by' => auth()->id(),
                'folio'      => $folio,
            ]));
            session()->flash('success', 'Campaña creada correctamente.');
        }

        $this->redirect(route('sales.crm.campaigns.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.sales.crm-campaign-form');
    }
}

<?php

namespace App\Livewire\Sales;

use App\Models\CrmActivity;
use App\Models\Customer;
use App\Models\SaleQuotation;
use App\Models\SalesOpportunity;
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class CrmOpportunityShow extends Component
{
    public SalesOpportunity $opportunity;

    // Activity form
    public bool   $showActivityForm    = false;
    public string $activityType        = 'call';
    public string $activityTitle       = '';
    public string $activityDesc        = '';
    public string $activityScheduled   = '';
    public string $activityAssignedTo  = '';

    // Lost modal
    public bool   $showLostModal       = false;
    public string $lostReason          = '';

    // Convert to quotation modal
    public bool   $showConvertModal    = false;

    public function mount(SalesOpportunity $opportunity): void
    {
        $this->opportunity = $opportunity->load([
            'activities.user',
            'activities.assignedTo',
            'customer',
            'assignedTo',
        ]);
        $this->activityAssignedTo = (string) auth()->id();
    }

    public function moveStage(string $stage): void
    {
        if ($stage === 'lost') {
            $this->showLostModal = true;
            return;
        }

        $updates = [
            'stage'       => $stage,
            'probability' => SalesOpportunity::STAGE_PROBABILITY[$stage],
        ];

        if ($stage === 'won') {
            $updates['won_at']  = now();
            $updates['lost_at'] = null;
        } else {
            $updates['won_at']  = null;
            $updates['lost_at'] = null;
        }

        $this->opportunity->update($updates);
        $this->opportunity->refresh();
    }

    public function confirmLost(): void
    {
        $this->validate(['lostReason' => 'nullable|string|max:100']);

        $this->opportunity->update([
            'stage'       => 'lost',
            'probability' => 0,
            'lost_at'     => now(),
            'won_at'      => null,
            'lost_reason' => $this->lostReason ?: null,
        ]);

        $this->showLostModal = false;
        $this->opportunity->refresh();
    }

    public function saveActivity(): void
    {
        $this->validate([
            'activityType'       => 'required|in:call,email,meeting,visit,whatsapp,task,note',
            'activityTitle'      => 'required|string|max:255',
            'activityDesc'       => 'nullable|string',
            'activityScheduled'  => 'nullable|date',
            'activityAssignedTo' => 'nullable|exists:users,id',
        ]);

        CrmActivity::create([
            'company_id'     => auth()->user()->company_id,
            'user_id'        => auth()->id(),
            'assigned_to'    => $this->activityAssignedTo ?: null,
            'opportunity_id' => $this->opportunity->id,
            'type'           => $this->activityType,
            'title'          => $this->activityTitle,
            'description'    => $this->activityDesc ?: null,
            'scheduled_at'   => $this->activityScheduled ?: null,
            'status'         => 'pending',
        ]);

        $this->reset(['activityTitle', 'activityDesc', 'activityScheduled', 'showActivityForm']);
        $this->opportunity->load('activities.user');
    }

    public function completeActivity(int $id): void
    {
        CrmActivity::where('opportunity_id', $this->opportunity->id)->findOrFail($id)->update([
            'status'       => 'completed',
            'completed_at' => now(),
        ]);
        $this->opportunity->load('activities.user');
    }

    public function deleteActivity(int $id): void
    {
        CrmActivity::where('opportunity_id', $this->opportunity->id)->findOrFail($id)->delete();
        $this->opportunity->load('activities.user');
    }

    public function convertToQuotation(): void
    {
        $customerId = $this->opportunity->customer_id;

        // Si está vinculada a un prospecto convertido, usar el cliente
        $params = [];
        if ($customerId) {
            // Redirigir a form de cotización con customer pre-seleccionado
            // No se puede pasar customer_id directamente pero podemos guardar en session
            session()->flash('opportunity_customer_id', $customerId);
            session()->flash('opportunity_title', $this->opportunity->title);
        }

        // Marcar la oportunidad como en negociación si está antes
        if (in_array($this->opportunity->stage, ['qualification', 'proposal'])) {
            $this->opportunity->update([
                'stage'       => 'negotiation',
                'probability' => SalesOpportunity::STAGE_PROBABILITY['negotiation'],
            ]);
        }

        $this->redirect(route('sales.index'), navigate: true);
    }

    public function render()
    {
        $users = User::where('company_id', auth()->user()->company_id)->orderBy('name')->get();

        return view('livewire.sales.crm-opportunity-show', compact('users'));
    }
}

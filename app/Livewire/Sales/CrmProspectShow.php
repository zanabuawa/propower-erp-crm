<?php

namespace App\Livewire\Sales;

use App\Models\CrmActivity;
use App\Models\Customer;
use App\Models\SalesOpportunity;
use App\Models\SalesProspect;
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class CrmProspectShow extends Component
{
    public SalesProspect $prospect;
    public string $activeTab = 'activities';

    // Activity form
    public bool   $showActivityForm   = false;
    public string $activityType       = 'call';
    public string $activityTitle      = '';
    public string $activityDesc       = '';
    public string $activityScheduled  = '';
    public string $activityReminder   = '';
    public string $activityAssignedTo = '';

    // Convert to customer modal
    public bool   $showConvertModal   = false;
    public string $convertAction      = 'new'; // new | existing
    public string $existingCustomerId = '';

    // Opportunity modal
    public bool   $showOpportunityForm  = false;
    public string $oppTitle             = '';
    public string $oppStage             = 'qualification';
    public string $oppValue             = '';
    public string $oppCloseDate         = '';
    public string $oppAssignedTo        = '';

    public function mount(SalesProspect $prospect): void
    {
        $this->prospect = $prospect->load([
            'activities.user', 'activities.assignedTo',
            'opportunities.assignedTo',
            'assignedTo',
        ]);
        $this->activityAssignedTo = (string) auth()->id();
        $this->oppAssignedTo      = (string) auth()->id();
    }

    public function saveActivity(): void
    {
        $this->validate([
            'activityType'       => 'required|in:call,email,meeting,visit,whatsapp,task,note',
            'activityTitle'      => 'required|string|max:255',
            'activityDesc'       => 'nullable|string',
            'activityScheduled'  => 'nullable|date',
            'activityReminder'   => 'nullable|date',
            'activityAssignedTo' => 'nullable|exists:users,id',
        ]);

        CrmActivity::create([
            'company_id'   => auth()->user()->company_id,
            'user_id'      => auth()->id(),
            'assigned_to'  => $this->activityAssignedTo ?: null,
            'prospect_id'  => $this->prospect->id,
            'type'         => $this->activityType,
            'title'        => $this->activityTitle,
            'description'  => $this->activityDesc ?: null,
            'scheduled_at' => $this->activityScheduled ?: null,
            'reminder_at'  => $this->activityReminder ?: null,
            'status'       => 'pending',
        ]);

        $this->reset(['activityTitle', 'activityDesc', 'activityScheduled', 'activityReminder', 'showActivityForm']);
        $this->prospect->load('activities.user');
    }

    public function completeActivity(int $id): void
    {
        CrmActivity::findOrFail($id)->update([
            'status'       => 'completed',
            'completed_at' => now(),
        ]);
        $this->prospect->load('activities.user');
    }

    public function deleteActivity(int $id): void
    {
        CrmActivity::findOrFail($id)->delete();
        $this->prospect->load('activities.user');
    }

    public function saveOpportunity(): void
    {
        $this->validate([
            'oppTitle'       => 'required|string|max:255',
            'oppStage'       => 'required|in:qualification,proposal,negotiation,won,lost',
            'oppValue'       => 'nullable|numeric|min:0',
            'oppCloseDate'   => 'nullable|date',
            'oppAssignedTo'  => 'nullable|exists:users,id',
        ]);

        SalesOpportunity::create([
            'company_id'          => auth()->user()->company_id,
            'prospect_id'         => $this->prospect->id,
            'assigned_to'         => $this->oppAssignedTo ?: null,
            'title'               => $this->oppTitle,
            'stage'               => $this->oppStage,
            'probability'         => SalesOpportunity::STAGE_PROBABILITY[$this->oppStage],
            'estimated_value'     => $this->oppValue ?: 0,
            'expected_close_date' => $this->oppCloseDate ?: null,
        ]);

        $this->reset(['oppTitle', 'oppStage', 'oppValue', 'oppCloseDate', 'showOpportunityForm']);
        $this->prospect->load('opportunities.assignedTo');
        session()->flash('success', 'Oportunidad creada.');
    }

    public function convertToCustomer(): void
    {
        if ($this->convertAction === 'existing') {
            $this->validate(['existingCustomerId' => 'required|exists:customers,id']);
            $customerId = (int) $this->existingCustomerId;
        } else {
            $customer   = Customer::create([
                'company_id' => auth()->user()->company_id,
                'name'       => $this->prospect->name,
                'status'     => 'active',
                'city'       => $this->prospect->city,
                'state'      => $this->prospect->state,
                'assigned_to'=> $this->prospect->assigned_to,
            ]);
            $customerId = $customer->id;
        }

        $this->prospect->update([
            'status'                   => 'converted',
            'converted_at'             => now(),
            'converted_to_customer_id' => $customerId,
        ]);

        $this->showConvertModal = false;
        session()->flash('success', 'Prospecto convertido a cliente.');
        $this->redirectRoute('customers.show', $customerId, navigate: true);
    }

    public function updateStatus(string $status): void
    {
        $this->prospect->update(['status' => $status]);
        $this->prospect->refresh();
    }

    public function updateFollowUp(string $date): void
    {
        $this->prospect->update(['next_follow_up' => $date ?: null]);
        $this->prospect->refresh();
    }

    public function render()
    {
        $users     = User::where('company_id', auth()->user()->company_id)->orderBy('name')->get();
        $customers = Customer::where('company_id', auth()->user()->company_id)
            ->where('status', 'active')->orderBy('name')->get();
        return view('livewire.sales.crm-prospect-show', compact('users', 'customers'));
    }
}

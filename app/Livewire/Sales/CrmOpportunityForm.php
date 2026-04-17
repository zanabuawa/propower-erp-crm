<?php

namespace App\Livewire\Sales;

use App\Models\Customer;
use App\Models\SalesOpportunity;
use App\Models\SalesProspect;
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class CrmOpportunityForm extends Component
{
    public ?SalesOpportunity $opportunity = null;

    public string $title              = '';
    public string $stage              = 'qualification';
    public string $probability        = '10';
    public string $estimated_value    = '';
    public string $expected_close_date= '';
    public string $description        = '';
    public string $assigned_to        = '';
    public string $linked_type        = 'prospect'; // prospect | customer
    public string $prospect_id        = '';
    public string $customer_id        = '';
    public string $lost_reason        = '';

    public function mount(?SalesOpportunity $opportunity = null): void
    {
        if ($opportunity && $opportunity->exists) {
            $this->opportunity          = $opportunity;
            $this->title                = $opportunity->title;
            $this->stage                = $opportunity->stage;
            $this->probability          = (string) $opportunity->probability;
            $this->estimated_value      = $opportunity->estimated_value > 0 ? (string) $opportunity->estimated_value : '';
            $this->expected_close_date  = $opportunity->expected_close_date?->format('Y-m-d') ?? '';
            $this->description          = $opportunity->description ?? '';
            $this->assigned_to          = (string) ($opportunity->assigned_to ?? '');
            $this->lost_reason          = $opportunity->lost_reason ?? '';
            if ($opportunity->customer_id) {
                $this->linked_type  = 'customer';
                $this->customer_id  = (string) $opportunity->customer_id;
            } else {
                $this->linked_type  = 'prospect';
                $this->prospect_id  = (string) ($opportunity->prospect_id ?? '');
            }
        } else {
            $this->assigned_to = (string) auth()->id();
            // Pre-fill from URL params
            if (request('prospect_id')) {
                $this->prospect_id = request('prospect_id');
                $this->linked_type = 'prospect';
            }
            if (request('customer_id')) {
                $this->customer_id = request('customer_id');
                $this->linked_type = 'customer';
            }
        }
    }

    public function updatedStage(string $value): void
    {
        $this->probability = (string) (SalesOpportunity::STAGE_PROBABILITY[$value] ?? 10);
    }

    public function rules(): array
    {
        return [
            'title'               => 'required|string|max:255',
            'stage'               => 'required|in:qualification,proposal,negotiation,won,lost',
            'probability'         => 'required|integer|min:0|max:100',
            'estimated_value'     => 'nullable|numeric|min:0',
            'expected_close_date' => 'nullable|date',
            'description'         => 'nullable|string',
            'assigned_to'         => 'nullable|exists:users,id',
            'prospect_id'         => 'nullable|exists:sales_prospects,id',
            'customer_id'         => 'nullable|exists:customers,id',
            'lost_reason'         => 'nullable|string|max:100',
        ];
    }

    public function save(): void
    {
        $data = $this->validate();
        $data['company_id']      = auth()->user()->company_id;
        $data['estimated_value'] = $data['estimated_value'] ?? 0;
        $data['assigned_to']     = $data['assigned_to'] ?: null;

        if ($this->linked_type === 'customer') {
            $data['prospect_id'] = null;
        } else {
            $data['customer_id'] = null;
        }

        if ($data['stage'] === 'won' && !($this->opportunity?->won_at)) {
            $data['won_at'] = now();
        }
        if ($data['stage'] === 'lost' && !($this->opportunity?->lost_at)) {
            $data['lost_at'] = now();
        }

        if ($this->opportunity?->exists) {
            $this->opportunity->update($data);
            session()->flash('success', 'Oportunidad actualizada.');
        } else {
            SalesOpportunity::create($data);
            session()->flash('success', 'Oportunidad creada.');
        }

        $this->redirectRoute('sales.crm.pipeline', navigate: true);
    }

    public function render()
    {
        $companyId = auth()->user()->company_id;
        $users     = User::where('company_id', $companyId)->orderBy('name')->get();
        $prospects = SalesProspect::where('company_id', $companyId)
            ->whereIn('status', ['new', 'contacted', 'qualified'])->orderBy('name')->get();
        $customers = Customer::where('company_id', $companyId)
            ->where('status', 'active')->orderBy('name')->get();

        return view('livewire.sales.crm-opportunity-form', compact('users', 'prospects', 'customers'));
    }
}

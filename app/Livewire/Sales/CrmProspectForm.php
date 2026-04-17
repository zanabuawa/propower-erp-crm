<?php

namespace App\Livewire\Sales;

use App\Models\SalesProspect;
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class CrmProspectForm extends Component
{
    public ?SalesProspect $prospect = null;

    public string  $name             = '';
    public string  $contact_name     = '';
    public string  $contact_email    = '';
    public string  $contact_phone    = '';
    public string  $contact_position = '';
    public string  $source           = '';
    public string  $status           = 'new';
    public string  $estimated_value  = '';
    public string  $city             = '';
    public string  $state            = '';
    public string  $description      = '';
    public string  $next_follow_up   = '';
    public string  $assigned_to      = '';

    public function mount(?SalesProspect $prospect = null): void
    {
        if ($prospect && $prospect->exists) {
            $this->prospect        = $prospect;
            $this->name            = $prospect->name;
            $this->contact_name    = $prospect->contact_name ?? '';
            $this->contact_email   = $prospect->contact_email ?? '';
            $this->contact_phone   = $prospect->contact_phone ?? '';
            $this->contact_position= $prospect->contact_position ?? '';
            $this->source          = $prospect->source ?? '';
            $this->status          = $prospect->status;
            $this->estimated_value = $prospect->estimated_value > 0 ? (string) $prospect->estimated_value : '';
            $this->city            = $prospect->city ?? '';
            $this->state           = $prospect->state ?? '';
            $this->description     = $prospect->description ?? '';
            $this->next_follow_up  = $prospect->next_follow_up?->format('Y-m-d') ?? '';
            $this->assigned_to     = (string) ($prospect->assigned_to ?? '');
        } else {
            $this->assigned_to = (string) auth()->id();
        }
    }

    public function rules(): array
    {
        return [
            'name'             => 'required|string|max:255',
            'contact_name'     => 'nullable|string|max:255',
            'contact_email'    => 'nullable|email|max:255',
            'contact_phone'    => 'nullable|string|max:30',
            'contact_position' => 'nullable|string|max:100',
            'source'           => 'nullable|string|max:50',
            'status'           => 'required|in:new,contacted,qualified,disqualified,converted',
            'estimated_value'  => 'nullable|numeric|min:0',
            'city'             => 'nullable|string|max:100',
            'state'            => 'nullable|string|max:100',
            'description'      => 'nullable|string',
            'next_follow_up'   => 'nullable|date',
            'assigned_to'      => 'nullable|exists:users,id',
        ];
    }

    public function save(): void
    {
        $data = $this->validate();
        $data['company_id']      = auth()->user()->company_id;
        $data['estimated_value'] = $data['estimated_value'] ?? 0;
        $data['assigned_to']     = $data['assigned_to'] ?: null;
        $data['next_follow_up']  = $data['next_follow_up'] ?: null;

        if ($this->prospect?->exists) {
            $this->prospect->update($data);
            session()->flash('success', 'Prospecto actualizado.');
        } else {
            SalesProspect::create($data);
            session()->flash('success', 'Prospecto creado.');
        }

        $this->redirectRoute('sales.crm.prospects.index', navigate: true);
    }

    public function render()
    {
        $users = User::where('company_id', auth()->user()->company_id)->orderBy('name')->get();
        return view('livewire.sales.crm-prospect-form', compact('users'));
    }
}

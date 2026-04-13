<?php

namespace App\Livewire\Projects;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Project;
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class ProjectForm extends Component
{
    public ?Project $project = null;

    public string $code = '';
    public string $name = '';
    public string $description = '';
    public string $type = 'externo';
    public string $status = 'borrador';
    public ?int $customer_id = null;
    public ?int $branch_id = null;
    public ?int $responsible_user_id = null;
    public string $start_date = '';
    public string $end_date = '';
    public string $budget = '';
    public string $currency = 'MXN';
    public string $notes = '';

    public function mount(?Project $project = null): void
    {
        if ($project && $project->exists) {
            $this->project              = $project;
            $this->code                 = $project->code;
            $this->name                 = $project->name;
            $this->description          = $project->description ?? '';
            $this->type                 = $project->type;
            $this->status               = $project->status;
            $this->customer_id          = $project->customer_id;
            $this->branch_id            = $project->branch_id;
            $this->responsible_user_id  = $project->responsible_user_id;
            $this->start_date           = $project->start_date?->format('Y-m-d') ?? '';
            $this->end_date             = $project->end_date?->format('Y-m-d') ?? '';
            $this->budget               = $project->budget ?? '';
            $this->currency             = $project->currency;
            $this->notes                = $project->notes ?? '';
        } else {
            $this->code = 'PROJ-' . str_pad(Project::count() + 1, 4, '0', STR_PAD_LEFT);
        }
    }

    public function rules(): array
    {
        return [
            'code'                => 'required|string|max:50|unique:projects,code,' . ($this->project?->id ?? 'NULL'),
            'name'                => 'required|string|max:255',
            'description'         => 'nullable|string',
            'type'                => 'required|in:interno,externo,licitacion',
            'status'              => 'required|in:borrador,activo,pausado,completado,cancelado',
            'customer_id'         => 'nullable|exists:customers,id',
            'branch_id'           => 'nullable|exists:branches,id',
            'responsible_user_id' => 'nullable|exists:users,id',
            'start_date'          => 'nullable|date',
            'end_date'            => 'nullable|date|after_or_equal:start_date',
            'budget'              => 'nullable|numeric|min:0',
            'currency'            => 'required|string|size:3',
            'notes'               => 'nullable|string|max:1000',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'code'                => $this->code,
            'name'                => $this->name,
            'description'         => $this->description ?: null,
            'type'                => $this->type,
            'status'              => $this->status,
            'customer_id'         => $this->customer_id,
            'branch_id'           => $this->branch_id,
            'responsible_user_id' => $this->responsible_user_id,
            'start_date'          => $this->start_date ?: null,
            'end_date'            => $this->end_date ?: null,
            'budget'              => $this->budget ?: 0,
            'currency'            => $this->currency,
            'notes'               => $this->notes ?: null,
        ];

        if ($this->project && $this->project->exists) {
            $this->project->update($data);
            session()->flash('success', 'Proyecto actualizado correctamente.');
        } else {
            Project::create($data);
            session()->flash('success', 'Proyecto creado correctamente.');
        }

        $this->redirect(route('projects.index'), navigate: true);
    }

    public function render()
    {
        $branches  = Branch::where('company_id', auth()->user()->company_id)->orderBy('name')->get();
        $customers = Customer::where('company_id', auth()->user()->company_id)->orderBy('name')->get();
        $users     = User::where('company_id', auth()->user()->company_id)->orderBy('name')->get();

        return view('livewire.projects.project-form', compact('branches', 'customers', 'users'));
    }
}

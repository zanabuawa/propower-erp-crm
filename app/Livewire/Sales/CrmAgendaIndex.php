<?php

namespace App\Livewire\Sales;

use App\Models\CrmActivity;
use App\Models\Customer;
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class CrmAgendaIndex extends Component
{
    public string $filterStatus  = 'pending';
    public string $filterType    = '';
    public string $filterUser    = '';
    public string $dateFrom      = '';
    public string $dateTo        = '';
    public string $search        = '';
    public string $viewMode      = 'list'; // list | today

    // Form (create + edit)
    public bool   $showForm         = false;
    public int    $activityId       = 0;   // 0 = nuevo, >0 = editar
    public string $activityType     = 'call';
    public string $activityTitle    = '';
    public string $activityDesc     = '';
    public string $activityScheduled= '';
    public string $activityReminder = '';
    public string $activityAssigned = '';
    public string $linkedType       = 'customer';
    public string $linkedId         = '';

    // Complete/outcome modal
    public bool   $showOutcomeModal = false;
    public int    $completingId     = 0;
    public string $outcome          = '';

    public function mount(): void
    {
        $this->activityAssigned = (string) auth()->id();
        $this->dateFrom = now()->format('Y-m-d');
        $this->dateTo   = now()->addDays(14)->format('Y-m-d');
    }

    public function showToday(): void
    {
        $this->viewMode     = 'today';
        $this->dateFrom     = now()->format('Y-m-d');
        $this->dateTo       = now()->format('Y-m-d');
        $this->filterStatus = 'pending';
    }

    public function showAll(): void
    {
        $this->viewMode     = 'list';
        $this->filterStatus = '';
        $this->dateFrom     = now()->format('Y-m-d');
        $this->dateTo       = now()->addDays(14)->format('Y-m-d');
    }

    public function editActivity(int $id): void
    {
        $activity = CrmActivity::where('company_id', auth()->user()->company_id)
            ->findOrFail($id);

        $this->activityId        = $id;
        $this->activityType      = $activity->type;
        $this->activityTitle     = $activity->title;
        $this->activityDesc      = $activity->description ?? '';
        $this->activityScheduled = $activity->scheduled_at->format('Y-m-d\TH:i');
        $this->activityReminder  = $activity->reminder_at?->format('Y-m-d\TH:i') ?? '';
        $this->activityAssigned  = (string) ($activity->assigned_to ?? '');
        $this->linkedType        = 'customer';
        $this->linkedId          = (string) ($activity->customer_id ?? '');
        $this->showForm          = true;
    }

    public function saveActivity(): void
    {
        $this->validate([
            'activityType'      => 'required|in:call,email,meeting,visit,whatsapp,task,note',
            'activityTitle'     => 'required|string|max:255',
            'activityDesc'      => 'nullable|string',
            'activityScheduled' => 'required|date',
            'activityReminder'  => 'nullable|date',
            'activityAssigned'  => 'nullable|exists:users,id',
            'linkedId'          => 'nullable|integer',
        ]);

        $data = [
            'assigned_to'  => $this->activityAssigned ?: null,
            'customer_id'  => $this->linkedId ?: null,
            'type'         => $this->activityType,
            'title'        => $this->activityTitle,
            'description'  => $this->activityDesc ?: null,
            'scheduled_at' => $this->activityScheduled,
            'reminder_at'  => $this->activityReminder ?: null,
        ];

        if ($this->activityId > 0) {
            CrmActivity::where('company_id', auth()->user()->company_id)
                ->findOrFail($this->activityId)
                ->update($data);
            session()->flash('success', 'Actividad actualizada.');
        } else {
            CrmActivity::create(array_merge($data, [
                'company_id' => auth()->user()->company_id,
                'user_id'    => auth()->id(),
                'status'     => 'pending',
            ]));
            session()->flash('success', 'Actividad agendada.');
        }

        $this->reset(['activityId', 'activityTitle', 'activityDesc', 'activityScheduled',
                       'activityReminder', 'linkedId', 'showForm']);
        $this->activityType     = 'call';
        $this->activityAssigned = (string) auth()->id();
        $this->linkedType       = 'customer';
    }

    public function openOutcomeModal(int $id): void
    {
        $this->completingId     = $id;
        $this->outcome          = '';
        $this->showOutcomeModal = true;
    }

    public function completeActivity(): void
    {
        CrmActivity::where('company_id', auth()->user()->company_id)
            ->findOrFail($this->completingId)
            ->update([
                'status'       => 'completed',
                'completed_at' => now(),
                'outcome'      => $this->outcome ?: null,
            ]);

        $this->reset(['completingId', 'outcome', 'showOutcomeModal']);
    }

    public function cancelActivity(int $id): void
    {
        CrmActivity::where('company_id', auth()->user()->company_id)
            ->findOrFail($id)
            ->update(['status' => 'cancelled']);
    }

    public function render()
    {
        $companyId = auth()->user()->company_id;

        $activities = CrmActivity::query()
            ->where('company_id', $companyId)
            ->when($this->search,       fn($q) => $q->where('title', 'like', '%'.$this->search.'%'))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterType,   fn($q) => $q->where('type', $this->filterType))
            ->when($this->filterUser,   fn($q) => $q->where('assigned_to', $this->filterUser))
            ->when($this->dateFrom,     fn($q) => $q->whereDate('scheduled_at', '>=', $this->dateFrom))
            ->when($this->dateTo,       fn($q) => $q->whereDate('scheduled_at', '<=', $this->dateTo))
            ->with(['user', 'assignedTo', 'customer', 'opportunity'])
            ->orderBy('scheduled_at')
            ->get();

        $summary = [
            'today_pending'    => CrmActivity::where('company_id', $companyId)
                ->where('status', 'pending')
                ->whereDate('scheduled_at', today())
                ->count(),
            'overdue'          => CrmActivity::where('company_id', $companyId)
                ->where('status', 'pending')
                ->where('scheduled_at', '<', now()->startOfDay())
                ->count(),
            'this_week'        => CrmActivity::where('company_id', $companyId)
                ->where('status', 'pending')
                ->whereBetween('scheduled_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count(),
            'completed_today'  => CrmActivity::where('company_id', $companyId)
                ->where('status', 'completed')
                ->whereDate('completed_at', today())
                ->count(),
        ];

        $users     = User::where('company_id', $companyId)->orderBy('name')->get();
        $customers = Customer::where('company_id', $companyId)->where('status', 'active')->orderBy('name')->get();

        return view('livewire.sales.crm-agenda-index', compact(
            'activities', 'summary', 'users', 'customers'
        ));
    }
}

<?php

namespace App\Livewire\HR;

use App\Models\HrEvaluationProcess;
use App\Models\HrTestAttempt;
use App\Models\HrProspect;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Panel de Evaluación')]
class EvaluationDashboard extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatus = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'filterStatus' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $stats = [
            'active_processes' => HrEvaluationProcess::where('status', 'active')->count(),
            'pending_grades' => HrTestAttempt::whereIn('status', ['submitted', 'partially_graded', 'pending_review'])->count(),
            'completed_today' => HrEvaluationProcess::where('status', 'completed')
                ->whereDate('updated_at', now())
                ->count(),
            'total_prospects' => HrProspect::count(),
        ];

        $processes = HrEvaluationProcess::with(['prospect.position', 'employee.position', 'stages'])
            ->where(function($q) {
                if ($this->search) {
                    $q->whereHas('prospect', function($qp) {
                        $qp->where('first_name', 'like', "%{$this->search}%")
                           ->orWhere('last_name', 'like', "%{$this->search}%");
                    })->orWhereHas('employee', function($qe) {
                        $qe->where('first_name', 'like', "%{$this->search}%")
                           ->orWhere('last_name', 'like', "%{$this->search}%");
                    });
                }
            })
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->latest()
            ->paginate(10);

        $recentAttempts = HrTestAttempt::with([
                'prospectTest.testTemplate', 
                'prospectTest.stage.evaluationProcess.prospect', 
                'prospectTest.stage.evaluationProcess.employee'
            ])
            ->latest()
            ->take(5)
            ->get();

        return view('livewire.hr.evaluation-dashboard', compact('stats', 'recentAttempts', 'processes'));
    }
}

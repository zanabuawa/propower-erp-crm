<?php

namespace App\Livewire\HR;

use App\Models\HrJobOpening;
use App\Models\HrPosition;
use App\Models\HrProspect;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Reclutamiento - Prospectos')]
class ProspectIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatus = '';
    public string $filterPosition = '';
    public string $filterDateStart = '';
    public string $filterDateEnd = '';
    public ?int $filterRecruiter = null;
    public ?int $filterJobOpening = null;

    // Propiedades para evaluación
    public bool $showEvalModal = false;
    public ?int $evaluatingId = null;
    public array $criteria_scores = [];
    public string $eval_comments = '';
    public string $eval_result = 'aprobado';

    // Propiedades para decisión
    public bool $showRejectModal = false;
    public ?int $rejectingId = null;
    public string $reject_reason = '';

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilterStatus(): void { $this->resetPage(); }
    public function updatingFilterPosition(): void { $this->resetPage(); }
    public function updatingFilterDateStart(): void { $this->resetPage(); }
    public function updatingFilterDateEnd(): void { $this->resetPage(); }
    public function updatingFilterRecruiter(): void { $this->resetPage(); }
    public function updatingFilterJobOpening(): void { $this->resetPage(); }

    public function mount(): void
    {
        // Permite llegar desde "Ver candidatos" en JobOpeningIndex
        if ($jobOpeningId = request()->query('job_opening_id')) {
            $this->filterJobOpening = (int) $jobOpeningId;
        }
    }

    public function delete(int $id): void
    {
        $prospect = HrProspect::findOrFail($id);
        $prospect->delete();
        session()->flash('success', 'Prospecto eliminado correctamente.');
    }

    public function openEvaluation(int $id): void
    {
        $this->evaluatingId = $id;
        $this->criteria_scores = array_fill_keys(array_keys(\App\Models\HrProspectEvaluation::CRITERIA), 0);
        $this->eval_comments = '';
        $this->eval_result = 'aprobado';
        $this->showEvalModal = true;
    }

    public function saveEvaluation(): void
    {
        $this->validate([
            'criteria_scores.*' => 'required|numeric|min:0|max:100',
            'eval_comments'    => 'required|string|min:5',
            'eval_result'      => 'required|in:aprobado,rechazado',
        ]);

        $prospect = HrProspect::findOrFail($this->evaluatingId);
        
        $avgScore = collect($this->criteria_scores)->avg();

        $prospect->evaluations()->create([
            'evaluator_id'    => auth()->id(),
            'score'           => $avgScore,
            'criteria_scores' => $this->criteria_scores,
            'comments'        => $this->eval_comments,
            'result'          => $this->eval_result,
        ]);

        // Regla: entrevista_agendada -> entrevistado -> en_revision
        if ($prospect->status === 'entrevista_agendada') {
            $prospect->changeStatus('entrevistado', 'Entrevista realizada');
        }

        if ($this->eval_result === 'rechazado') {
            $prospect->changeStatus('rechazado', 'Rechazado tras evaluación: ' . $this->eval_comments);
        } else {
            $prospect->changeStatus('en_revision', 'En revisión tras evaluación');
        }

        $this->showEvalModal = false;
        session()->flash('success', 'Evaluación registrada correctamente.');
    }

    public function approve(int $id): void
    {
        $prospect = HrProspect::findOrFail($id);
        $prospect->changeStatus('aprobado', 'Candidato aprobado tras revisión');
        session()->flash('success', 'Prospecto aprobado.');
    }

    public function openReject(int $id): void
    {
        $this->rejectingId = $id;
        $this->reject_reason = '';
        $this->showRejectModal = true;
    }

    public function reject(): void
    {
        $this->validate(['reject_reason' => 'required|string|min:5']);
        $prospect = HrProspect::findOrFail($this->rejectingId);
        $prospect->changeStatus('rechazado', $this->reject_reason);
        $this->showRejectModal = false;
        session()->flash('success', 'Prospecto rechazado.');
    }

    public function hire(int $id): void
    {
        $this->redirect(route('hr.employees.create', ['prospect_id' => $id]), navigate: true);
    }

    public function render()
    {
        $prospects = HrProspect::query()
            ->with(['position', 'interviewer'])
            ->when($this->search, fn($q) => $q->where(fn($q2) =>
                $q2->where('first_name', 'like', "%{$this->search}%")
                   ->orWhere('last_name', 'like', "%{$this->search}%")
                   ->orWhere('second_last_name', 'like', "%{$this->search}%")
                   ->orWhere('email', 'like', "%{$this->search}%")
                   ->orWhere('phone', 'like', "%{$this->search}%")
            ))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterPosition, fn($q) => $q->where('position_id', $this->filterPosition))
            ->when($this->filterRecruiter, fn($q) => $q->where('interviewer_id', $this->filterRecruiter))
            ->when($this->filterJobOpening, fn($q) => $q->where('job_opening_id', $this->filterJobOpening))
            ->when($this->filterDateStart, fn($q) => $q->whereDate('interview_date', '>=', $this->filterDateStart))
            ->when($this->filterDateEnd, fn($q) => $q->whereDate('interview_date', '<=', $this->filterDateEnd))
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $positions = HrPosition::where('is_active', true)->orderBy('name')->get();
        $recruiters = User::where('is_active', true)->orderBy('name')->get();

        $stats = [
            'total' => HrProspect::count(),
            'new' => HrProspect::where('status', 'nuevo')->count(),
            'interviewing' => HrProspect::whereIn('status', ['evaluando', 'entrevista_agendada', 'entrevistado'])->count(),
            'hired' => HrProspect::where('status', 'contratado')->count(),
        ];

        $jobOpenings = HrJobOpening::where('company_id', auth()->user()->company_id)
            ->whereIn('status', ['open', 'paused'])
            ->orderBy('title')->get(['id', 'title']);

        return view('livewire.hr.prospect-index', compact('prospects', 'positions', 'recruiters', 'stats', 'jobOpenings'));
    }
}

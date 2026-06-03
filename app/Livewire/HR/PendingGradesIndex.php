<?php

namespace App\Livewire\HR;

use App\Models\HrTestAttempt;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Calificaciones Pendientes')]
class PendingGradesIndex extends Component
{
    use WithPagination;

    public function render()
    {
        $pendingAttempts = HrTestAttempt::with(['prospectTest.testTemplate', 'prospectTest.stage.process.prospect', 'prospectTest.stage.process.employee'])
            ->withCount([
                'answers as open_answers_count' => fn ($query) => $query->whereHas('question', fn ($question) => $question->where('type', 'open_ended')),
            ])
            ->whereIn('status', ['submitted', 'partially_graded'])
            ->latest()
            ->paginate(15);

        return view('livewire.hr.pending-grades-index', compact('pendingAttempts'));
    }
}

<?php

namespace App\Livewire\HR;

use App\Models\HrProspect;
use App\Models\HrProspectNote;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Detalles del Prospecto')]
class ProspectShow extends Component
{
    public HrProspect $prospect;
    public string $newNote = '';

    public function mount(HrProspect $prospect)
    {
        $this->prospect = $prospect->load(['position', 'interviewer', 'statusLogs.user', 'evaluations.evaluator', 'interviews.interviewer', 'notes.user']);
    }

    public function addNote()
    {
        $this->validate(['newNote' => 'required|string|min:3']);

        $this->prospect->notes()->create([
            'user_id' => auth()->id(),
            'content' => $this->newNote,
        ]);

        $this->newNote = '';
        $this->prospect->load('notes.user');
        session()->flash('note_success', 'Nota añadida correctamente.');
    }

    public function render()
    {
        // Combinar eventos para el timeline
        $timeline = collect();

        // Logs de estado
        foreach ($this->prospect->statusLogs as $log) {
            $timeline->push([
                'type' => 'status',
                'date' => $log->created_at,
                'title' => "Cambio de estado: " . HrProspect::STATUSES[$log->to_status],
                'content' => $log->reason,
                'user' => $log->user?->name,
                'icon' => 'status'
            ]);
        }

        // Entrevistas
        foreach ($this->prospect->interviews as $interview) {
            $timeline->push([
                'type' => 'interview',
                'date' => $interview->created_at,
                'title' => "Entrevista agendada ({$interview->interview_type})",
                'content' => "Para el " . $interview->interview_date->format('d/m/Y H:i'),
                'user' => $interview->interviewer?->name,
                'icon' => 'interview'
            ]);
        }

        // Notas
        foreach ($this->prospect->notes as $note) {
            $timeline->push([
                'type' => 'note',
                'date' => $note->created_at,
                'title' => "Nota interna",
                'content' => $note->content,
                'user' => $note->user?->name,
                'icon' => 'note'
            ]);
        }

        // Evaluaciones
        foreach ($this->prospect->evaluations as $eval) {
            $timeline->push([
                'type' => 'evaluation',
                'date' => $eval->created_at,
                'title' => "Evaluación registrada (Puntaje: {$eval->score})",
                'content' => $eval->comments,
                'user' => $eval->evaluator?->name,
                'icon' => 'evaluation'
            ]);
        }

        $timeline = $timeline->sortByDesc('date');

        return view('livewire.hr.prospect-show', compact('timeline'));
    }
}

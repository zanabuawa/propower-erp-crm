<?php

namespace App\Livewire\HR;

use App\Models\HrProspect;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Agenda de Reclutamiento')]
class ProspectAgenda extends Component
{
    public $currentDate;
    public $view = 'month'; // month, week
    public $selectedProspectId;
    public $interview_date;
    public $interview_time;
    public $interview_type = 'virtual';
    public $interviewer_id;
    public $showModal = false;

    public function mount()
    {
        $this->currentDate = Carbon::now();
        $this->interviewer_id = auth()->id();
    }

    public function prevMonth()
    {
        $this->currentDate = Carbon::parse($this->currentDate)->subMonth();
    }

    public function nextMonth()
    {
        $this->currentDate = Carbon::parse($this->currentDate)->addMonth();
    }

    public function selectProspect($id)
    {
        $this->selectedProspectId = $id;
        $this->showModal = true;
    }

    public function scheduleInterview()
    {
        $this->validate([
            'selectedProspectId' => 'required|exists:hr_prospects,id',
            'interview_date'     => 'required|date',
            'interview_time'     => 'required',
            'interview_type'     => 'required|in:presencial,virtual',
            'interviewer_id'     => 'required|exists:users,id',
        ]);

        $prospect = HrProspect::find($this->selectedProspectId);
        
        $dateTime = Carbon::parse($this->interview_date . ' ' . $this->interview_time);

        $prospect->update([
            'interview_date' => $dateTime,
            'interview_type' => $this->interview_type,
            'interviewer_id' => $this->interviewer_id,
            'scheduled_by_id' => auth()->id(),
        ]);

        // Crear registro en el historial de entrevistas
        $prospect->interviews()->create([
            'interviewer_id' => $this->interviewer_id,
            'interview_date' => $dateTime,
            'interview_type' => $this->interview_type,
            'status'         => 'agendada',
        ]);

        $prospect->changeStatus('entrevista_agendada', "Entrevista agendada para el " . $dateTime->format('d/m/Y H:i'));

        $this->reset(['selectedProspectId', 'interview_date', 'interview_time', 'showModal']);
        session()->flash('success', 'Entrevista agendada correctamente.');
    }

    public function render()
    {
        $startOfMonth = Carbon::parse($this->currentDate)->startOfMonth()->startOfWeek();
        $endOfMonth = Carbon::parse($this->currentDate)->endOfMonth()->endOfWeek();
        
        $days = [];
        $date = $startOfMonth->copy();
        while ($date->lte($endOfMonth)) {
            $days[] = [
                'date' => $date->copy(),
                'isCurrentMonth' => $date->month === Carbon::parse($this->currentDate)->month,
                'isToday' => $date->isToday(),
                'interviews' => HrProspect::whereDate('interview_date', $date)
                    ->with(['position', 'interviewer'])
                    ->get()
            ];
            $date->addDay();
        }

        $unscheduledProspects = HrProspect::whereNull('interview_date')
            ->whereNotIn('status', ['rechazado', 'contratado'])
            ->with('position')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('livewire.hr.prospect-agenda', [
            'days' => $days,
            'unscheduledProspects' => $unscheduledProspects,
            'users' => User::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}

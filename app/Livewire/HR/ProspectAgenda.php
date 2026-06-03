<?php

namespace App\Livewire\HR;

use App\Models\HrAgendaEvent;
use App\Models\HrAgendaEventType;
use App\Models\HrProspect;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Agenda')]
class ProspectAgenda extends Component
{
    public $currentDate;
    public $view = 'month'; // month, week
    public $selectedProspectId;
    public $interview_date;
    public $interview_time;
    public $interview_type = 'virtual';
    public $calendar_color = '#7c3aed';
    public $interviewer_id;
    public $showModal = false;
    public $showEventModal = false;
    public $editingEventId = null;
    public $event_title = '';
    public $event_type = 'general';
    public $show_event_type_creator = false;
    public $show_event_type_editor = false;
    public $show_event_type_deleter = false;
    public $editing_event_type_id = null;
    public $editing_event_type_slug = null;
    public $event_custom_type = '';
    public $event_type_feedback = '';
    public $event_date = '';
    public $event_time = '';
    public $event_color = '#2563eb';
    public $event_description = '';

    private const DEFAULT_CALENDAR_COLORS = [
        'virtual' => '#7c3aed',
        'presencial' => '#059669',
    ];

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
        $prospect = HrProspect::findOrFail($id);

        $this->selectedProspectId = $id;
        $this->interview_date = $prospect->interview_date?->format('Y-m-d') ?? $this->interview_date;
        $this->interview_time = $prospect->interview_date?->format('H:i') ?? $this->interview_time;
        $this->interview_type = $prospect->interview_type ?: $this->interview_type;
        $this->interviewer_id = $prospect->interviewer_id ?: auth()->id();
        $this->calendar_color = $prospect->calendar_color
            ?: self::DEFAULT_CALENDAR_COLORS[$this->interview_type] ?? '#7c3aed';
        $this->showModal = true;
    }

    public function moveInterviewDate($id, string $date): void
    {
        $prospect = HrProspect::findOrFail($id);

        if (! $prospect->interview_date) {
            $this->selectProspect($id);
            $this->interview_date = $date;
            return;
        }

        $newDateTime = Carbon::parse($date . ' ' . $prospect->interview_date->format('H:i'));
        $previousDateTime = $prospect->interview_date->copy();

        if ($newDateTime->isSameDay($previousDateTime)) {
            return;
        }

        $prospect->update([
            'interview_date' => $newDateTime,
            'scheduled_by_id' => auth()->id(),
        ]);

        $prospect->interviews()->create([
            'interviewer_id' => $prospect->interviewer_id,
            'interview_date' => $newDateTime,
            'interview_type' => $prospect->interview_type ?: 'virtual',
            'status' => 'agendada',
            'notes' => 'Reprogramada desde calendario. Fecha anterior: ' . $previousDateTime->format('d/m/Y H:i'),
        ]);

        session()->flash('success', 'Entrevista reprogramada correctamente.');
    }

    public function openEventModal(?string $date = null): void
    {
        $this->resetEventForm();
        $this->event_date = $date ?: '';
        $this->event_time = $date ? '09:00' : '';
        $this->showEventModal = true;
    }

    public function selectEvent($id): void
    {
        $event = HrAgendaEvent::where('company_id', auth()->user()->company_id)->findOrFail($id);

        $this->editingEventId = $event->id;
        $this->event_title = $event->title;
        $this->event_type = array_key_exists($event->type, HrAgendaEvent::TYPES) ? $event->type : 'custom';
        $this->event_custom_type = $this->event_type === 'custom' ? $event->type : '';
        $this->event_date = $event->starts_at?->format('Y-m-d') ?? '';
        $this->event_time = $event->starts_at?->format('H:i') ?? '';
        $this->event_color = $event->color;
        $this->event_description = $event->description ?? '';
        $this->showEventModal = true;
    }

    public function saveEvent(): void
    {
        $this->validate([
            'event_title' => 'required|string|max:191',
            'event_type' => 'required|string|max:100',
            'event_custom_type' => 'required_if:event_type,custom|nullable|string|max:80',
            'event_date' => 'nullable|required_with:event_time|date',
            'event_time' => 'nullable|required_with:event_date',
            'event_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'event_description' => 'nullable|string|max:1000',
        ]);

        $eventType = $this->event_type === 'custom'
            ? trim(strtolower(str_replace(' ', '_', $this->event_custom_type)))
            : $this->event_type;

        $data = [
            'company_id' => auth()->user()->company_id,
            'created_by_id' => auth()->id(),
            'title' => $this->event_title,
            'type' => $eventType,
            'starts_at' => $this->event_date ? Carbon::parse($this->event_date . ' ' . $this->event_time) : null,
            'color' => $this->event_color,
            'description' => $this->event_description ?: null,
        ];

        if ($this->editingEventId) {
            HrAgendaEvent::where('company_id', auth()->user()->company_id)
                ->findOrFail($this->editingEventId)
                ->update($data);
        } else {
            HrAgendaEvent::create($data);
        }

        $this->resetEventForm();
        session()->flash('success', 'Evento guardado correctamente.');
    }

    public function saveEventType(): void
    {
        $this->validate([
            'event_custom_type' => 'required|string|max:80',
        ]);

        $name = trim($this->event_custom_type);
        $slug = Str::slug($name, '_');

        $isEditingType = $this->editing_event_type_id || $this->editing_event_type_slug;

        if ($isEditingType) {
            $oldSlug = $this->editing_event_type_slug;

            if ($this->editing_event_type_id) {
                $type = HrAgendaEventType::where('company_id', auth()->user()->company_id)
                    ->findOrFail($this->editing_event_type_id);
                $oldSlug = $type->slug;
            } else {
                $type = null;
            }

            $type = HrAgendaEventType::where('company_id', auth()->user()->company_id)
                ->firstOrNew([
                    'company_id' => auth()->user()->company_id,
                    'slug' => $oldSlug,
                ]);

            $exists = HrAgendaEventType::where('company_id', auth()->user()->company_id)
                ->where('slug', $slug)
                ->when($type->exists, fn ($query) => $query->where('id', '!=', $type->id))
                ->exists();

            if ($exists || (array_key_exists($slug, HrAgendaEvent::TYPES) && $slug !== $oldSlug)) {
                $this->addError('event_custom_type', 'Ya existe una categoria con ese nombre.');
                return;
            }

            $type->fill([
                'company_id' => auth()->user()->company_id,
                'name' => $name,
                'slug' => $slug,
            ])->save();

            HrAgendaEvent::where('company_id', auth()->user()->company_id)
                ->where('type', $oldSlug)
                ->update(['type' => $slug]);
        } else {
            if (array_key_exists($slug, HrAgendaEvent::TYPES)) {
                $this->addError('event_custom_type', 'Ya existe una categoria con ese nombre.');
                return;
            }

            $type = HrAgendaEventType::firstOrCreate(
                [
                    'company_id' => auth()->user()->company_id,
                    'slug' => $slug,
                ],
                [
                    'name' => $name,
                ]
            );
        }

        $this->event_type = $type->slug;
        $this->event_custom_type = '';
        $this->editing_event_type_id = null;
        $this->editing_event_type_slug = null;
        $this->show_event_type_creator = false;
        $this->show_event_type_editor = false;
        $this->show_event_type_deleter = false;
        $this->event_type_feedback = $isEditingType
            ? 'Categoria actualizada correctamente.'
            : 'Categoria creada correctamente.';
    }

    public function openEventTypeCreator(): void
    {
        $this->editing_event_type_id = null;
        $this->event_custom_type = '';
        $this->event_type_feedback = '';
        $this->show_event_type_creator = true;
        $this->show_event_type_editor = false;
        $this->show_event_type_deleter = false;
    }

    public function openEventTypeEditor(): void
    {
        if ($this->event_type && $this->event_type !== 'custom') {
            $type = HrAgendaEventType::where('company_id', auth()->user()->company_id)
                ->where('slug', $this->event_type)
                ->first();

            $this->editing_event_type_id = $type?->id;
            $this->editing_event_type_slug = $type?->slug ?? $this->event_type;
            $this->event_custom_type = $type?->name
                ?? (HrAgendaEvent::TYPES[$this->event_type] ?? ucwords(str_replace(['_', '-'], ' ', $this->event_type)));
            $this->show_event_type_creator = true;
            $this->show_event_type_editor = false;
            $this->show_event_type_deleter = false;
            return;
        }

        $this->event_custom_type = '';
        $this->editing_event_type_id = null;
        $this->editing_event_type_slug = null;
        $this->event_type_feedback = '';
        $this->show_event_type_creator = false;
        $this->show_event_type_editor = true;
        $this->show_event_type_deleter = false;
    }

    public function openEventTypeDeleter(): void
    {
        if ($this->event_type && $this->event_type !== 'custom') {
            $this->deleteEventTypeBySlug($this->event_type);
            return;
        }

        $this->event_custom_type = '';
        $this->editing_event_type_id = null;
        $this->editing_event_type_slug = null;
        $this->event_type_feedback = '';
        $this->show_event_type_creator = false;
        $this->show_event_type_editor = false;
        $this->show_event_type_deleter = true;
    }

    public function editEventType($id): void
    {
        $type = HrAgendaEventType::where('company_id', auth()->user()->company_id)->findOrFail($id);

        $this->editing_event_type_id = $type->id;
        $this->editing_event_type_slug = $type->slug;
        $this->event_custom_type = $type->name;
        $this->event_type_feedback = '';
        $this->show_event_type_creator = true;
        $this->show_event_type_editor = false;
        $this->show_event_type_deleter = false;
    }

    public function deleteEventType($id): void
    {
        $type = HrAgendaEventType::where('company_id', auth()->user()->company_id)->findOrFail($id);

        $this->deleteEventTypeBySlug($type->slug, $type);
    }

    private function deleteEventTypeBySlug(string $slug, ?HrAgendaEventType $type = null): void
    {
        $type ??= HrAgendaEventType::where('company_id', auth()->user()->company_id)
            ->where('slug', $slug)
            ->first();

        if (! $type && array_key_exists($slug, HrAgendaEvent::TYPES)) {
            $this->event_type_feedback = 'Esta categoria base no se puede eliminar.';
            $this->show_event_type_deleter = false;
            return;
        }

        HrAgendaEvent::where('company_id', auth()->user()->company_id)
            ->where('type', $slug)
            ->update(['type' => 'general']);

        if ($this->event_type === $slug) {
            $this->event_type = 'general';
        }

        $type?->delete();
        $this->show_event_type_deleter = false;
        $this->show_event_type_creator = false;
        $this->show_event_type_editor = false;
        $this->editing_event_type_id = null;
        $this->editing_event_type_slug = null;
        $this->event_type_feedback = 'Categoria eliminada correctamente.';
    }

    public function moveEventDate($id, string $date): void
    {
        $event = HrAgendaEvent::where('company_id', auth()->user()->company_id)->findOrFail($id);

        $event->update([
            'starts_at' => Carbon::parse($date . ' ' . ($event->starts_at?->format('H:i') ?? '09:00')),
        ]);

        session()->flash('success', 'Evento reprogramado correctamente.');
    }

    public function deleteEvent(): void
    {
        if (! $this->editingEventId) {
            return;
        }

        HrAgendaEvent::where('company_id', auth()->user()->company_id)
            ->findOrFail($this->editingEventId)
            ->delete();

        $this->resetEventForm();
        session()->flash('success', 'Evento eliminado correctamente.');
    }

    private function resetEventForm(): void
    {
        $this->reset([
            'showEventModal',
            'editingEventId',
            'event_title',
            'show_event_type_creator',
            'show_event_type_editor',
            'show_event_type_deleter',
            'editing_event_type_id',
            'editing_event_type_slug',
            'event_custom_type',
            'event_type_feedback',
            'event_description',
        ]);

        $this->event_type = 'general';
        $this->event_date = '';
        $this->event_time = '';
        $this->event_color = '#2563eb';
    }

    public function scheduleInterview()
    {
        $this->validate([
            'selectedProspectId' => 'required|exists:hr_prospects,id',
            'interview_date'     => 'required|date',
            'interview_time'     => 'required',
            'interview_type'     => 'required|in:presencial,virtual',
            'calendar_color'     => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'interviewer_id'     => 'required|exists:users,id',
        ]);

        $prospect = HrProspect::find($this->selectedProspectId);
        $wasScheduled = $prospect->interview_date !== null;
        
        $dateTime = Carbon::parse($this->interview_date . ' ' . $this->interview_time);

        $prospect->update([
            'interview_date' => $dateTime,
            'interview_type' => $this->interview_type,
            'calendar_color' => $this->calendar_color,
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

        $prospect->changeStatus('entrevista_agendada', ($wasScheduled ? 'Entrevista actualizada para el ' : 'Entrevista agendada para el ') . $dateTime->format('d/m/Y H:i'));

        $this->reset(['selectedProspectId', 'interview_date', 'interview_time', 'showModal']);
        $this->calendar_color = self::DEFAULT_CALENDAR_COLORS[$this->interview_type] ?? '#7c3aed';
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
                    ->where('company_id', auth()->user()->company_id)
                    ->with(['position', 'interviewer'])
                    ->get(),
                'events' => HrAgendaEvent::whereDate('starts_at', $date)
                    ->where('company_id', auth()->user()->company_id)
                    ->whereNotNull('starts_at')
                    ->orderBy('starts_at')
                    ->get(),
            ];
            $date->addDay();
        }

        $unscheduledProspects = HrProspect::whereNull('interview_date')
            ->where('company_id', auth()->user()->company_id)
            ->whereNotIn('status', ['rechazado', 'contratado'])
            ->with('position')
            ->orderBy('created_at', 'desc')
            ->get();

        $unscheduledEvents = HrAgendaEvent::whereNull('starts_at')
            ->where('company_id', auth()->user()->company_id)
            ->orderBy('created_at', 'desc')
            ->get();

        $eventTypes = HrAgendaEvent::where('company_id', auth()->user()->company_id)
            ->whereNotIn('type', array_keys(HrAgendaEvent::TYPES))
            ->distinct()
            ->orderBy('type')
            ->pluck('type')
            ->mapWithKeys(fn ($type) => [$type => ucwords(str_replace(['_', '-'], ' ', $type))])
            ->all();

        $customEventTypes = HrAgendaEventType::where('company_id', auth()->user()->company_id)
            ->orderBy('name')
            ->get();

        $catalogEventTypes = $customEventTypes
            ->pluck('name', 'slug')
            ->all();

        return view('livewire.hr.prospect-agenda', [
            'days' => $days,
            'unscheduledProspects' => $unscheduledProspects,
            'unscheduledEvents' => $unscheduledEvents,
            'users' => User::where('is_active', true)->orderBy('name')->get(),
            'eventTypes' => HrAgendaEvent::TYPES + $catalogEventTypes + $eventTypes,
            'customEventTypes' => $customEventTypes,
        ]);
    }
}

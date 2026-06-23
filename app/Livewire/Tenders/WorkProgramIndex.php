<?php

namespace App\Livewire\Tenders;

use App\Models\Project;
use App\Models\WorkProgram;
use App\Models\WorkProgramActivity;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Programa de Obra')]
class WorkProgramIndex extends Component
{
    public Project $project;
    public ?WorkProgram $program = null;
    public bool $embedded = false;
    public bool $showActivityModal = false;
    public ?int $editingActivityId = null;
    public ?int $parentActivityId = null;

    public string $actName = '';
    public string $actUnit = '';
    public string $actQuantity = '';
    public string $actStartDate = '';
    public string $actEndDate = '';
    public string $actActualStartDate = '';
    public string $actActualEndDate = '';
    public string $actActualNotes = '';
    public int $actProgress = 0;
    public string $ganttStartDate = '';
    public string $ganttEndDate = '';
    public string $ganttScale = 'auto';

    public function mount(Project $project, bool $embedded = false): void
    {
        $project->load('branch');
        abort_if($project->branch && $project->branch->company_id !== auth()->user()->company_id, 404);

        $this->embedded = $embedded;
        $this->project = $project;
        $this->program = WorkProgram::where('project_id', $project->id)
            ->where('status', 'vigente')
            ->with('allActivities')
            ->first();
    }

    public function createProgram(): void
    {
        // Mark previous as historical
        WorkProgram::where('project_id', $this->project->id)->where('status', 'vigente')->update(['status' => 'historico']);
        $version = WorkProgram::where('project_id', $this->project->id)->max('version') + 1;
        $this->program = WorkProgram::create([
            'project_id' => $this->project->id,
            'name'       => 'Programa v' . $version,
            'version'    => $version,
            'status'     => 'vigente',
            'created_by' => auth()->id(),
        ]);
        session()->flash('success', 'Programa de obra creado.');
    }

    public function openActivityModal(?int $id = null, ?int $parentId = null): void
    {
        $this->resetActivityForm();
        $this->parentActivityId = $parentId;
        if ($id) {
            $a = WorkProgramActivity::whereHas('program', fn ($query) => $query->where('project_id', $this->project->id))
                ->findOrFail($id);
            $this->editingActivityId = $id;
            $this->actName      = $a->name;
            $this->actUnit      = $a->unit ?? '';
            $this->actQuantity  = (string) ($a->quantity ?? '');
            $this->actStartDate = $a->start_date?->format('Y-m-d') ?? '';
            $this->actEndDate   = $a->end_date?->format('Y-m-d') ?? '';
            $this->actActualStartDate = $a->actual_start_date?->format('Y-m-d') ?? '';
            $this->actActualEndDate = $a->actual_end_date?->format('Y-m-d') ?? '';
            $this->actActualNotes = $a->actual_notes ?? '';
            $this->actProgress  = $a->progress_pct;
        }
        $this->showActivityModal = true;
    }

    public function saveActivity(): void
    {
        $this->validate([
            'actName'      => 'required|string|max:200',
            'actStartDate' => 'nullable|date',
            'actEndDate'   => 'nullable|date|after_or_equal:actStartDate',
            'actActualStartDate' => 'nullable|date',
            'actActualEndDate' => 'nullable|date|after_or_equal:actActualStartDate',
            'actActualNotes' => 'nullable|string',
            'actProgress'  => 'integer|min:0|max:100',
        ]);

        $data = [
            'program_id'   => $this->program->id,
            'parent_id'    => $this->parentActivityId,
            'name'         => $this->actName,
            'unit'         => $this->actUnit ?: null,
            'quantity'     => $this->actQuantity ?: null,
            'start_date'   => $this->actStartDate ?: null,
            'end_date'     => $this->actEndDate ?: null,
            'actual_start_date' => $this->actActualStartDate ?: null,
            'actual_end_date' => $this->actActualEndDate ?: null,
            'actual_notes' => $this->actActualNotes ?: null,
            'progress_pct' => $this->actProgress,
        ];

        if ($this->editingActivityId) {
            WorkProgramActivity::whereHas('program', fn ($query) => $query->where('project_id', $this->project->id))
                ->findOrFail($this->editingActivityId)
                ->update($data);
        } else {
            $lastOrder = WorkProgramActivity::where('program_id', $this->program->id)->max('sort_order') ?? 0;
            $data['sort_order'] = $lastOrder + 1;
            WorkProgramActivity::create($data);
        }

        $this->showActivityModal = false;
        $this->resetActivityForm();
        $this->program->load('allActivities');
        session()->flash('success', 'Actividad guardada.');
    }

    public function deleteActivity(int $id): void
    {
        WorkProgramActivity::whereHas('program', fn ($query) => $query->where('project_id', $this->project->id))
            ->findOrFail($id)
            ->delete();
        $this->program->load('allActivities');
        session()->flash('success', 'Actividad eliminada.');
    }

    public function updateProgress(int $id, int $pct): void
    {
        WorkProgramActivity::whereHas('program', fn ($query) => $query->where('project_id', $this->project->id))
            ->findOrFail($id)
            ->update(['progress_pct' => max(0, min(100, $pct))]);
        $this->program->load('allActivities');
    }

    public function clearGanttDates(): void
    {
        $this->ganttStartDate = '';
        $this->ganttEndDate = '';
    }

    private function resetActivityForm(): void
    {
        $this->editingActivityId = null; $this->parentActivityId = null;
        $this->actName = ''; $this->actUnit = ''; $this->actQuantity = '';
        $this->actStartDate = ''; $this->actEndDate = ''; $this->actProgress = 0;
        $this->actActualStartDate = ''; $this->actActualEndDate = ''; $this->actActualNotes = '';
    }

    private function ganttData(): array
    {
        $activities = $this->visibleProgramActivities();
        $dates = collect();

        foreach ($activities as $activity) {
            foreach (['start_date', 'end_date', 'actual_start_date', 'actual_end_date'] as $field) {
                if ($activity->{$field}) {
                    $dates->push($activity->{$field}->copy());
                }
            }
        }

        $start = $dates->isNotEmpty()
            ? $dates->sortBy(fn ($date) => $date->timestamp)->first()->copy()->startOfDay()
            : now()->startOfDay();
        $end = $dates->isNotEmpty()
            ? $dates->sortByDesc(fn ($date) => $date->timestamp)->first()->copy()->startOfDay()
            : now()->copy()->addDays(14)->startOfDay();

        if ($start->diffInDays($end) < 6) {
            $end = $start->copy()->addDays(6);
        }

        if ($this->ganttStartDate !== '') {
            $start = Carbon::parse($this->ganttStartDate)->startOfDay();
        }

        if ($this->ganttEndDate !== '') {
            $end = Carbon::parse($this->ganttEndDate)->startOfDay();
        }

        if ($end->lt($start)) {
            $end = $start->copy()->addDays(6);
        }

        $totalDays = max(1, $start->diffInDays($end) + 1);
        $scale = $this->resolveGanttScale($this->ganttScale, $totalDays);
        $ticks = $this->ganttTicks($start, $end, $totalDays, $scale);

        $bar = function ($from, $to) use ($start, $totalDays) {
            if (! $from || ! $to) {
                return null;
            }

            $from = Carbon::parse($from)->startOfDay();
            $to = Carbon::parse($to)->startOfDay();

            if ($to->lt($from)) {
                $to = $from->copy();
            }

            $visibleFrom = $from->lt($start) ? $start->copy() : $from;
            $visibleEnd = $start->copy()->addDays($totalDays - 1);
            $visibleTo = $to->gt($visibleEnd) ? $visibleEnd : $to;

            if ($visibleTo->lt($visibleFrom)) {
                return null;
            }

            return [
                'left' => max(0, ($start->diffInDays($visibleFrom, false) / $totalDays) * 100),
                'width' => max(1.5, (($visibleFrom->diffInDays($visibleTo) + 1) / $totalDays) * 100),
            ];
        };

        return [
            'start' => $start,
            'end' => $end,
            'totalDays' => $totalDays,
            'scale' => $scale,
            'scale_label' => [
                'day' => 'Dias',
                'week' => 'Semanas',
                'month' => 'Meses',
                'quarter' => 'Trimestres',
            ][$scale] ?? 'Auto',
            'ticks' => $ticks,
            'rows' => $activities->map(function ($activity) use ($bar) {
                $planned = $bar($activity->start_date, $activity->end_date);
                $progress = max(0, min(100, (int) $activity->progress_pct));

                return [
                    'id' => $activity->id,
                    'name' => $activity->name,
                    'is_child' => (bool) $activity->parent_id,
                    'progress' => $progress,
                    'planned' => $planned,
                    'progress_bar' => $planned && $progress > 0
                        ? [
                            'left' => $planned['left'],
                            'width' => max(1.5, $planned['width'] * ($progress / 100)),
                        ]
                        : null,
                    'actual' => $bar($activity->actual_start_date, $activity->actual_end_date),
                    'planned_label' => $activity->start_date && $activity->end_date
                        ? $activity->start_date->format('d/m') . ' - ' . $activity->end_date->format('d/m')
                        : 'Sin programar',
                    'actual_label' => $activity->actual_start_date && $activity->actual_end_date
                        ? $activity->actual_start_date->format('d/m') . ' - ' . $activity->actual_end_date->format('d/m')
                        : 'Sin real',
                    'is_late' => $activity->actual_end_date && $activity->end_date && $activity->actual_end_date->gt($activity->end_date),
                ];
            })->values(),
        ];
    }

    private function visibleProgramActivities()
    {
        $activities = $this->program?->allActivities ?? collect();

        if ($this->ganttStartDate === '' && $this->ganttEndDate === '') {
            return $activities;
        }

        $rangeStart = $this->ganttStartDate !== ''
            ? Carbon::parse($this->ganttStartDate)->startOfDay()
            : null;
        $rangeEnd = $this->ganttEndDate !== ''
            ? Carbon::parse($this->ganttEndDate)->startOfDay()
            : null;

        if ($rangeStart && $rangeEnd && $rangeEnd->lt($rangeStart)) {
            $rangeEnd = $rangeStart->copy()->addDays(6);
        }

        return $activities->filter(function ($activity) use ($rangeStart, $rangeEnd) {
            foreach ([['start_date', 'end_date'], ['actual_start_date', 'actual_end_date']] as [$fromField, $toField]) {
                if (! $activity->{$fromField} || ! $activity->{$toField}) {
                    continue;
                }

                $from = Carbon::parse($activity->{$fromField})->startOfDay();
                $to = Carbon::parse($activity->{$toField})->startOfDay();

                if ($to->lt($from)) {
                    $to = $from->copy();
                }

                if ($rangeStart && $to->lt($rangeStart)) {
                    continue;
                }

                if ($rangeEnd && $from->gt($rangeEnd)) {
                    continue;
                }

                return true;
            }

            return false;
        });
    }

    public function render()
    {
        return view('livewire.tenders.work-program-index', [
            'project' => $this->project,
            'program' => $this->program,
            'gantt' => $this->ganttData(),
            'visibleProgramActivities' => $this->visibleProgramActivities(),
        ]);
    }

    private function resolveGanttScale(string $scale, int $totalDays): string
    {
        if (in_array($scale, ['day', 'week', 'month', 'quarter'], true)) {
            return $scale;
        }

        if ($totalDays <= 45) {
            return 'day';
        }

        if ($totalDays <= 180) {
            return 'week';
        }

        return $totalDays <= 730 ? 'month' : 'quarter';
    }

    private function ganttTicks(Carbon $start, Carbon $end, int $totalDays, string $scale): array
    {
        $ticks = [[
            'label' => match ($scale) {
                'day' => $start->format('d/m'),
                'week' => 'Sem ' . $start->isoWeek(),
                'month' => $start->format('M Y'),
                'quarter' => 'T' . $start->quarter . ' ' . $start->format('Y'),
                default => $start->format('d/m'),
            },
            'left' => 0,
        ]];

        $cursor = match ($scale) {
            'month' => $start->copy()->startOfMonth(),
            'quarter' => $start->copy()->firstOfQuarter(),
            default => $start->copy(),
        };

        if ($cursor->lt($start)) {
            $cursor = match ($scale) {
                'month' => $cursor->addMonth()->startOfMonth(),
                'quarter' => $cursor->addQuarter()->firstOfQuarter(),
                default => $start->copy(),
            };
        }

        while ($cursor->lte($end)) {
            if ($cursor->isSameDay($start)) {
                match ($scale) {
                    'day' => $cursor->addDays($totalDays > 21 ? 3 : 1),
                    'week' => $cursor->addWeek(),
                    'month' => $cursor->addMonth(),
                    'quarter' => $cursor->addQuarter(),
                    default => $cursor->addWeek(),
                };

                continue;
            }

            $ticks[] = [
                'label' => match ($scale) {
                    'day' => $cursor->format('d/m'),
                    'week' => 'Sem ' . $cursor->isoWeek(),
                    'month' => $cursor->format('M Y'),
                    'quarter' => 'T' . $cursor->quarter . ' ' . $cursor->format('Y'),
                    default => $cursor->format('d/m'),
                },
                'left' => ($start->diffInDays($cursor) / $totalDays) * 100,
            ];

            match ($scale) {
                'day' => $cursor->addDays($totalDays > 21 ? 3 : 1),
                'week' => $cursor->addWeek(),
                'month' => $cursor->addMonth(),
                'quarter' => $cursor->addQuarter(),
                default => $cursor->addWeek(),
            };
        }

        return $ticks;
    }
}

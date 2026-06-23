<?php

namespace App\Http\Controllers\Tenders;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\WorkIncidentReport;
use App\Models\WorkPhotoReport;
use App\Models\WorkProgram;
use App\Models\WorkReport;
use Carbon\Carbon;
use Illuminate\Http\Request;

class WorkDocumentPrintController extends Controller
{
    public function weekly(WorkReport $report)
    {
        $report->load(['project.customer', 'project.branch.company', 'createdBy']);
        $this->authorizeProject($report->project);

        return view('print.work-weekly-report', [
            'company' => $report->project->branch?->company,
            'customer' => $report->project->customer,
            'project' => $report->project,
            'report' => $report,
        ]);
    }

    public function photo(WorkPhotoReport $report)
    {
        $report->load(['project.customer', 'project.branch.company', 'createdBy']);
        $this->authorizeProject($report->project);

        return view('print.work-photo-report', [
            'company' => $report->project->branch?->company,
            'customer' => $report->project->customer,
            'project' => $report->project,
            'report' => $report,
        ]);
    }

    public function incident(WorkIncidentReport $report)
    {
        $report->load(['project.customer', 'project.branch.company', 'createdBy']);
        $this->authorizeProject($report->project);

        return view('print.work-incident-report', [
            'company' => $report->project->branch?->company,
            'customer' => $report->project->customer,
            'project' => $report->project,
            'report' => $report,
        ]);
    }

    public function gantt(Project $project, Request $request)
    {
        $project->load(['customer', 'branch.company']);
        $this->authorizeProject($project);

        $program = WorkProgram::where('project_id', $project->id)
            ->where('status', 'vigente')
            ->with('allActivities')
            ->first();

        $options = [
            'scale' => $request->input('scale', 'auto'),
            'detail' => $request->input('detail', 'all'),
            'start_date' => $request->filled('start_date') ? $request->date('start_date') : null,
            'end_date' => $request->filled('end_date') ? $request->date('end_date') : null,
        ];

        return view('print.work-program-gantt', [
            'company' => $project->branch?->company,
            'customer' => $project->customer,
            'project' => $project,
            'program' => $program,
            'printOptions' => $options,
            'gantt' => $this->ganttData($program, $options),
        ]);
    }

    public function logbook(Project $project)
    {
        $project->load(['customer', 'branch.company']);
        $this->authorizeProject($project);

        $reports = WorkReport::where('project_id', $project->id)
            ->with('createdBy')
            ->orderBy('week_start')
            ->get();

        return view('print.work-logbook', [
            'company' => $project->branch?->company,
            'customer' => $project->customer,
            'project' => $project,
            'reports' => $reports,
        ]);
    }

    private function authorizeProject(Project $project): void
    {
        abort_if(
            ! $project->branch || $project->branch->company_id !== auth()->user()->company_id,
            404
        );
    }

    private function ganttData(?WorkProgram $program, array $options = []): array
    {
        $activities = $program?->allActivities ?? collect();

        if (($options['detail'] ?? 'all') === 'parents') {
            $activities = $activities->whereNull('parent_id');
        }

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

        if ($options['start_date'] ?? null) {
            $start = Carbon::parse($options['start_date'])->startOfDay();
        }

        if ($options['end_date'] ?? null) {
            $end = Carbon::parse($options['end_date'])->startOfDay();
        }

        if ($end->lt($start)) {
            $end = $start->copy()->addDays(6);
        }

        $totalDays = max(1, $start->diffInDays($end) + 1);
        $scale = $this->resolveGanttScale($options['scale'] ?? 'auto', $totalDays);
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

        if (($options['start_date'] ?? null) || ($options['end_date'] ?? null)) {
            $visibleEnd = $end->copy();
            $activities = $activities->filter(function ($activity) use ($start, $visibleEnd) {
                foreach ([['start_date', 'end_date'], ['actual_start_date', 'actual_end_date']] as [$fromField, $toField]) {
                    if (! $activity->{$fromField} || ! $activity->{$toField}) {
                        continue;
                    }

                    $from = Carbon::parse($activity->{$fromField})->startOfDay();
                    $to = Carbon::parse($activity->{$toField})->startOfDay();

                    if ($to->lt($from)) {
                        $to = $from->copy();
                    }

                    if ($from->lte($visibleEnd) && $to->gte($start)) {
                        return true;
                    }
                }

                return false;
            });
        }

        return [
            'start' => $start,
            'end' => $end,
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

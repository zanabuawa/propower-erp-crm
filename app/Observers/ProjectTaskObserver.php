<?php

namespace App\Observers;

use App\Models\ProjectTask;

class ProjectTaskObserver
{
    public function saved(ProjectTask $task): void
    {
        $this->recalculateProgress($task);
    }

    public function deleted(ProjectTask $task): void
    {
        $this->recalculateProgress($task);
    }

    private function recalculateProgress(ProjectTask $task): void
    {
        $project = $task->project;
        if (!$project) {
            return;
        }

        $total     = $project->tasks()->count();
        $completed = $project->tasks()->where('status', 'completada')->count();
        $progress  = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

        // Evitar loop: solo actualizar si cambió
        if ((int) $project->progress !== $progress) {
            $project->updateQuietly(['progress' => $progress]);
        }
    }
}

<?php

namespace App\Livewire\HR;

use App\Models\HrTestAttempt;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Calificar Prueba')]
class HrTestGrading extends Component
{
    public HrTestAttempt $attempt;
    public array $grades = [];
    public float $manualPoints = 0;
    public float $automaticPoints = 0;
    public float $totalPoints = 0;
    public int $previewScore = 0;

    public function mount(HrTestAttempt $attempt): void
    {
        $this->attempt = $attempt->load([
            'answers.question.options',
            'answers.option',
            'prospectTest.template',
            'prospectTest.stage.process.prospect',
            'prospectTest.stage.process.employee',
        ]);

        foreach ($this->attempt->answers as $answer) {
            if ($answer->question->type === 'open_ended') {
                $this->grades[$answer->id] = $answer->points_earned;
            }
        }

        $this->recalculatePreview();
    }

    public function updatedGrades(): void
    {
        $this->recalculatePreview();
    }

    public function saveGrades(): void
    {
        $this->validateGrades();

        DB::transaction(function () {
            $earnedPoints = 0;
            $totalPoints = 0;

            foreach ($this->attempt->answers as $answer) {
                $totalPoints += $answer->question->points;

                if ($answer->question->type === 'open_ended') {
                    $points = (float) ($this->grades[$answer->id] ?? 0);

                    $answer->update([
                        'points_earned' => $points,
                        'is_correct' => $points > 0,
                    ]);
                    $earnedPoints += $points;
                } else {
                    $earnedPoints += $answer->points_earned;
                }
            }

            $score = $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100) : 0;

            $this->attempt->update([
                'score' => $score,
                'status' => 'graded'
            ]);

            $passingScore = $this->attempt->prospectTest->template->passing_score ?? 60;
            $isPassed = $score >= $passingScore;
            
            $testStatus = $isPassed ? 'completed' : 'failed';

            $this->attempt->prospectTest->update([
                'score' => $score,
                'status' => $testStatus,
                'graded_by_id' => auth()->id(),
            ]);

            // Check if stage can progress
            $this->checkStageProgress();
        });

        session()->flash('success', 'Calificación guardada correctamente.');
        $this->redirect(route('hr.evaluations.pending-grades'), navigate: true);
    }

    private function validateGrades(): void
    {
        $errors = [];

        foreach ($this->attempt->answers as $answer) {
            if ($answer->question->type !== 'open_ended') {
                continue;
            }

            $value = $this->grades[$answer->id] ?? null;

            if ($value === null || $value === '') {
                $errors["grades.{$answer->id}"] = 'Asigna un puntaje para esta respuesta.';
                continue;
            }

            if (! is_numeric($value)) {
                $errors["grades.{$answer->id}"] = 'El puntaje debe ser numerico.';
                continue;
            }

            if ((float) $value < 0 || (float) $value > (float) $answer->question->points) {
                $errors["grades.{$answer->id}"] = "El puntaje debe estar entre 0 y {$answer->question->points}.";
            }
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }
    }

    public function recalculatePreview(): void
    {
        $manualPoints = 0;
        $automaticPoints = 0;
        $totalPoints = 0;

        foreach ($this->attempt->answers as $answer) {
            $totalPoints += (float) $answer->question->points;

            if ($answer->question->type === 'open_ended') {
                $points = is_numeric($this->grades[$answer->id] ?? null)
                    ? (float) $this->grades[$answer->id]
                    : 0;

                $manualPoints += min(max($points, 0), (float) $answer->question->points);
            } else {
                $automaticPoints += (float) $answer->points_earned;
            }
        }

        $this->manualPoints = $manualPoints;
        $this->automaticPoints = $automaticPoints;
        $this->totalPoints = $totalPoints;
        $this->previewScore = $totalPoints > 0 ? (int) round((($manualPoints + $automaticPoints) / $totalPoints) * 100) : 0;
    }

    protected function checkStageProgress(): void
    {
        $stage = $this->attempt->prospectTest->stage->fresh('prospectTests');
        $process = $stage->process;
        
        // A test is finished if passed, or failed with no more attempts
        $allTestsFinishedInStage = $stage->prospectTests->every(function ($test) {
            if (in_array($test->status, ['completed', 'graded', 'pending_review', 'partially_graded'])) return true;
            if ($test->status === 'failed' && $test->attempts_count >= $test->max_attempts) return true;
            return false;
        });

        if ($allTestsFinishedInStage) {
            // Check if ALL tests in this stage are passed (or pending review)
            $allPassedOrPending = $stage->prospectTests->every(function ($test) {
                return in_array($test->status, ['completed', 'graded', 'pending_review', 'partially_graded']);
            });

            if ($allPassedOrPending) {
                $stage->update(['status' => 'completed']);

                // Only advance index or complete process if this was the "current" stage
                if ($process->current_stage_index == $stage->order) {
                    if ($process->current_stage_index < $process->total_stages - 1) {
                        $process->increment('current_stage_index');
                    } else {
                        // Check if ALL stages are actually completed before closing process
                        $allStagesCompleted = $process->stages()->where('status', '!=', 'completed')->count() === 0;
                        if ($allStagesCompleted) {
                            $process->update(['status' => 'completed']);
                        }
                    }
                }
            } else {
                // At least one test failed and has no more attempts
                $stage->update(['status' => 'failed']);
            }
        }
    }

    public function render()
    {
        $attempts = $this->attempt->prospectTest
            ->attempts()
            ->withCount('answers')
            ->orderBy('attempt_number')
            ->get();

        return view('livewire.hr.hr-test-grading', compact('attempts'));
    }
}

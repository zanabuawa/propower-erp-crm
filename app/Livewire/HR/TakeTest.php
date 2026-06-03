<?php

namespace App\Livewire\HR;

use App\Models\HrProspectTest;
use App\Models\HrTestAttempt;
use App\Models\HrProspectTestAnswer;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.guest')]
#[Title('Realizando Prueba')]
class TakeTest extends Component
{
    public HrProspectTest $prospectTest;
    public array $answers = [];
    public bool $isCompleted = false;
    public ?int $timeLeft = null;
    public ?string $startedAt = null;
    public array $testResult = [];

    public function mount(HrProspectTest $prospectTest): void
    {
        $this->prospectTest = $prospectTest->load([
            'stage.process.prospect',
            'stage.process.employee.user',
            'template.questions.options',
        ]);

        $process = $this->prospectTest->stage->process;

        if ($process->employee && auth()->id() !== $process->employee->user_id) {
            abort(403);
        }

        if ($this->prospectTest->stage->order !== $process->current_stage_index || ! $this->prospectTest->stage->isAvailable()) {
            session()->flash('error', 'Esta etapa aun no esta disponible.');
            $this->redirect($this->getPortalUrl());
            return;
        }

        // Check attempts
        if ($this->prospectTest->attempts_count >= $this->prospectTest->max_attempts) {
            session()->flash('error', 'Has agotado el número máximo de intentos para esta prueba.');
            $this->redirect($this->getPortalUrl());
            return;
        }

        // Allow retries if failed but attempts left
        if (in_array($this->prospectTest->status, ['completed', 'graded', 'pending_review', 'partially_graded'], true)) {
            session()->flash('info', 'Ya has completado esta prueba.');
            $this->redirect($this->getPortalUrl());
            return;
        }

        // Initialize answers
        foreach ($this->prospectTest->template->questions as $question) {
            $this->answers[$question->id] = null;
        }

        // Record start time
        $this->startedAt = now()->toDateTimeString();

        // Time Limit Logic
        if ($this->prospectTest->template->duration_minutes) {
            $this->timeLeft = $this->prospectTest->template->duration_minutes * 60;
        }
    }

    public function submit(): void
    {
        // If time is up, we skip validation to allow partial submission
        if ($this->timeLeft !== null && $this->timeLeft <= 0) {
            // Just continue to save what they have
        } else {
            $this->validate([
                'answers.*' => 'required',
            ], [
                'answers.*.required' => 'Por favor, responde todas las preguntas.',
            ]);
        }

        DB::transaction(function () {
            // Create attempt
            $attempt = HrTestAttempt::create([
                'hr_prospect_test_id' => $this->prospectTest->id,
                'attempt_number' => $this->prospectTest->attempts_count + 1,
                'score' => 0,
                'status' => 'pending',
                'started_at' => $this->startedAt,
                'submitted_at' => now(),
                'completed_at' => now(),
            ]);

            $totalPoints = 0;
            $earnedPoints = 0;
            $hasOpenEnded = false;

            foreach ($this->prospectTest->template->questions as $question) {
                $totalPoints += $question->points;
                $answerValue = $this->answers[$question->id];

                $answerRecord = [
                    'hr_test_attempt_id' => $attempt->id,
                    'hr_test_question_id' => $question->id,
                    'answer_text' => $question->type === 'open_ended' ? $answerValue : null,
                    'hr_test_option_id' => $question->type === 'multiple_choice' ? $answerValue : null,
                    'is_correct' => false,
                    'points_earned' => 0,
                ];

                if ($question->type === 'multiple_choice') {
                    $correctOption = $question->options->where('is_correct', true)->first();
                    if ($correctOption && $answerValue == $correctOption->id) {
                        $answerRecord['is_correct'] = true;
                        $answerRecord['points_earned'] = $question->points;
                        $earnedPoints += $question->points;
                    }
                } else {
                    $hasOpenEnded = true;
                }

                HrProspectTestAnswer::create($answerRecord);
            }

            $score = $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100) : 0;
            $status = $hasOpenEnded ? 'partially_graded' : 'graded';

            $attempt->update([
                'score' => $score,
                'status' => $status
            ]);

            $passingScore = $this->prospectTest->template->passing_score ?? 60;
            $isPassed = $score >= $passingScore;
            
            $newAttemptsCount = $this->prospectTest->attempts_count + 1;
            
            // Determine test status
            $testStatus = 'completed'; // Default for graded and passed
            if ($hasOpenEnded) {
                $testStatus = 'pending_review';
            } elseif (!$isPassed) {
                $testStatus = 'failed';
            }

            $this->prospectTest->update([
                'attempts_count' => $newAttemptsCount,
                'score' => $score,
                'status' => $testStatus
            ]);

            $this->testResult = [
                'score' => (int) $score,
                'status' => $testStatus,
                'isPassed' => $isPassed,
                'passingScore' => (int) $passingScore,
                'hasOpenEnded' => $hasOpenEnded,
                'attemptsLeft' => (int) ($this->prospectTest->max_attempts - $newAttemptsCount)
            ];

            // Update stage progress if everything is graded
            $this->checkStageProgress();
        });

        $this->isCompleted = true;
    }

    public function selectAnswer(int $questionId, int $optionId): void
    {
        $this->answers[$questionId] = $optionId;
    }

    protected function checkStageProgress(): void
    {
        $stage = $this->prospectTest->stage->fresh('prospectTests');
        $process = $stage->process;
        
        // A test is finished if passed, or failed with no more attempts
        $allTestsFinished = $stage->prospectTests->every(function ($test) {
            if (in_array($test->status, ['completed', 'graded', 'pending_review', 'partially_graded'])) return true;
            if ($test->status === 'failed' && $test->attempts_count >= $test->max_attempts) return true;
            return false;
        });

        if ($allTestsFinished) {
            // Check if ALL tests are passed (or pending review)
            $allPassedOrPending = $stage->prospectTests->every(function ($test) {
                return in_array($test->status, ['completed', 'graded', 'pending_review', 'partially_graded']);
            });

            if ($allPassedOrPending) {
                $stage->update(['status' => 'completed']);

                if ($process->current_stage_index == $stage->order) {
                    if ($process->current_stage_index < $process->total_stages - 1) {
                        $process->increment('current_stage_index');
                    } else {
                        // Check if all stages are done
                        $allStagesCompleted = $process->stages()->where('status', '!=', 'completed')->count() === 0;
                        if ($allStagesCompleted) {
                            $process->update(['status' => 'completed']);
                        }
                    }
                }
            } else {
                $stage->update(['status' => 'failed']);
            }
        }
    }

    public function getPortalUrl(): string
    {
        if ($this->prospectTest->stage->process->employee) {
            return route('hr.portal');
        }

        $prospect = $this->prospectTest->stage->process->prospect;
        return \Illuminate\Support\Facades\URL::signedRoute('hr.candidate.portal', ['prospect' => $prospect->id]);
    }

    public function render()
    {
        return view('livewire.hr.take-test');
    }
}

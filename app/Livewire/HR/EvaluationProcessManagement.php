<?php

namespace App\Livewire\HR;

use App\Models\HrProspect;
use App\Models\HrEmployee;
use App\Models\HrEvaluationProcess;
use App\Models\HrEvaluationStage;
use App\Models\HrTestTemplate;
use App\Models\HrProspectTest;
use App\Models\HrProspectTestAnswer;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
#[Title('Gestión de Evaluación')]
class EvaluationProcessManagement extends Component
{
    use WithFileUploads;

    public ?HrProspect $prospect = null;
    public ?HrEmployee $employee = null;
    public HrEvaluationProcess $process;

    public array $stages = [];
    public $newGuides = [];

    public function mount(HrEvaluationProcess $process, ?HrProspect $prospect = null): void
    {
        if ($prospect && $prospect->exists) {
            $this->process = HrEvaluationProcess::firstOrCreate(
                ['hr_prospect_id' => $prospect->id],
                [
                    'current_stage_index' => 0,
                    'total_stages' => 0,
                    'status' => 'active'
                ]
            );
        } else {
            $this->process = $process;
        }

        $this->process->load(['prospect', 'employee']);
        $this->prospect = $this->process->prospect;
        $this->employee = $this->process->employee;

        $this->loadProcess();
    }

    public function initDefaultStages(): void
    {
        $defaultNames = ['Filtro inicial', 'Entrevista', 'Prueba psicometrica', 'Propuesta'];
        $this->stages = [];
        foreach ($defaultNames as $index => $name) {
            $this->stages[] = [
                'name' => $name,
                'order' => $index,
                'scheduled_at' => null,
                'guide_path' => null,
                'guide_paths' => [],
                'video_links' => [],
                'test_templates' => [],
            ];
        }
    }

    private function shouldUseInterviewDefaultStages(): bool
    {
        return $this->prospect
            && in_array($this->prospect->status, ['entrevista_agendada', 'entrevistado'], true);
    }

    public function loadProcess(): void
    {
        $this->stages = $this->process->stages()->with(['prospectTests.attempts' => fn ($query) => $query->orderBy('attempt_number')])->get()->map(function ($stage) {
            return [
                'id' => $stage->id,
                'name' => $stage->name,
                'order' => $stage->order,
                'scheduled_at' => $stage->scheduled_at?->format('Y-m-d\TH:i'),
                'guide_path' => $stage->guide_path,
                'guide_paths' => $this->normalizeGuidePaths($stage),
                'video_links' => $stage->video_links ?? [],
                'test_templates' => $stage->prospectTests->mapWithKeys(function ($pt) {
                    return [$pt->hr_test_template_id => $pt->max_attempts ?? 1];
                })->toArray(),
                'attempts' => $stage->prospectTests->mapWithKeys(function ($pt) {
                    return [$pt->hr_test_template_id => $pt->attempts->map(fn ($attempt) => [
                        'id' => $attempt->id,
                        'attempt_number' => $attempt->attempt_number,
                        'score' => $attempt->score,
                        'status' => $attempt->status,
                    ])->toArray()];
                })->toArray(),
            ];
        })->toArray();
        
        if (empty($this->stages) && $this->shouldUseInterviewDefaultStages()) {
            $this->initDefaultStages();
        }
    }

    public function addStage(): void
    {
        $this->stages[] = [
            'name' => 'Nueva Etapa',
            'order' => count($this->stages),
            'scheduled_at' => null,
            'guide_path' => null,
            'guide_paths' => [],
            'video_links' => [],
            'test_templates' => [],
        ];
    }

    private function normalizeGuidePaths(HrEvaluationStage $stage): array
    {
        return collect($stage->guide_paths ?? [])
            ->when($stage->guide_path, fn ($paths) => $paths->prepend($stage->guide_path))
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }

    public function removeGuide(int $stageIndex, int $guideIndex): void
    {
        unset($this->stages[$stageIndex]['guide_paths'][$guideIndex]);
        $this->stages[$stageIndex]['guide_paths'] = array_values($this->stages[$stageIndex]['guide_paths'] ?? []);
    }

    public function addVideoLink(int $stageIndex): void
    {
        $this->stages[$stageIndex]['video_links'][] = '';
    }

    public function removeVideoLink(int $stageIndex, int $linkIndex): void
    {
        unset($this->stages[$stageIndex]['video_links'][$linkIndex]);
        $this->stages[$stageIndex]['video_links'] = array_values($this->stages[$stageIndex]['video_links']);
    }

    public function removeStage(int $index): void
    {
        unset($this->stages[$index]);
        $this->stages = array_values($this->stages);
    }

    public function save(): void
    {
        $this->validate([
            'newGuides.*.*' => 'file|mimes:pdf|max:40960',
        ]);

        DB::transaction(function () {
            $oldStageCount = $this->process->stages()->count();
            $newStageCount = count($this->stages);
            
            $this->process->update(['total_stages' => $newStageCount]);

            // Sync stages
            foreach ($this->stages as $index => $sData) {
                $guidePaths = array_values(array_filter($sData['guide_paths'] ?? []));
                
                foreach ($this->uploadedGuidesForStage($index) as $guide) {
                    $guidePaths[] = $guide->store('evaluation_guides', 'public');
                }

                $guidePaths = array_values(array_unique(array_filter($guidePaths)));

                $stage = HrEvaluationStage::updateOrCreate(
                    ['hr_evaluation_process_id' => $this->process->id, 'order' => $index],
                    [
                        'name' => $sData['name'], 
                        'scheduled_at' => $sData['scheduled_at'] ?: null,
                        'guide_path' => $guidePaths[0] ?? null,
                        'guide_paths' => $guidePaths,
                        'video_links' => array_filter($sData['video_links'] ?? [])
                    ]
                );

                // Sync tests for this stage
                $newTestsData = array_filter(
                    $sData['test_templates'] ?? [],
                    fn ($maxAttempts) => $maxAttempts !== null && $maxAttempts !== '' && (int) $maxAttempts > 0
                ); // [id => max_attempts]
                $newTestIds = array_keys($newTestsData);

                // Remove tests not in new list
                $stage->prospectTests()->whereNotIn('hr_test_template_id', $newTestIds)->delete();

                // Add or update tests
                foreach ($newTestsData as $templateId => $maxAttempts) {
                    $stage->prospectTests()->updateOrCreate(
                        ['hr_test_template_id' => $templateId],
                        [
                            'max_attempts' => $maxAttempts,
                            'status' => 'pending'
                        ]
                    );
                }
            }

            $this->process->stages()
                ->where('order', '>=', count($this->stages))
                ->delete();
            
            // REACTIVATION LOGIC:
            // If the process was completed but now we have more stages or we've modified it, 
            // check if there are any incomplete stages.
            $hasIncompleteStages = $this->process->stages()->where('status', '!=', 'completed')->exists();
            
            if ($hasIncompleteStages) {
                if ($this->process->status === 'completed') {
                    $this->process->update(['status' => 'active']);
                }
                
                // Ensure current_stage_index points to the first incomplete stage
                $firstIncomplete = $this->process->stages()
                    ->where('status', '!=', 'completed')
                    ->orderBy('order')
                    ->first();
                    
                if ($firstIncomplete && $this->process->current_stage_index >= $firstIncomplete->order) {
                    $this->process->update(['current_stage_index' => $firstIncomplete->order]);
                }
            }
            
            // Update prospect status if needed
            if ($this->prospect && $this->prospect->status === 'nuevo') {
                $this->prospect->changeStatus('evaluando');
            }
        });

        session()->flash('success', 'Proceso de evaluación guardado correctamente.');
        $this->newGuides = [];
        $this->loadProcess();
    }

    private function uploadedGuidesForStage(int $stageIndex): array
    {
        return array_filter(Arr::wrap($this->newGuides[$stageIndex] ?? []));
    }

    public function autoGrade(): void
    {
        if (!$this->process) return;

        $tests = HrProspectTest::whereIn('hr_evaluation_stage_id', $this->process->stages()->pluck('id'))->get();

        foreach ($tests as $test) {
            // Get the latest attempt
            $latestAttempt = $test->attempts()->orderBy('attempt_number', 'desc')->first();
            if (!$latestAttempt) continue;

            $answers = $latestAttempt->answers()->with('question.options')->get();
            $totalPoints = 0;
            $earnedPoints = 0;
            $hasOpenEnded = false;

            foreach ($answers as $answer) {
                $question = $answer->question;
                $totalPoints += $question->points;

                if ($question->type === 'multiple_choice') {
                    $correctOption = $question->options->where('is_correct', true)->first();
                    if ($correctOption && $answer->hr_test_option_id == $correctOption->id) {
                        $answer->update([
                            'is_correct' => true,
                            'points_earned' => $question->points
                        ]);
                        $earnedPoints += $question->points;
                    } else {
                        $answer->update([
                            'is_correct' => false,
                            'points_earned' => 0
                        ]);
                    }
                } else {
                    $hasOpenEnded = true;
                }
            }

            $score = $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100) : 0;
            $status = $hasOpenEnded ? 'partially_graded' : 'graded';

            $latestAttempt->update([
                'score' => $score,
                'status' => $status
            ]);

            $passingScore = $test->template->passing_score ?? 60;
            $isPassed = $score >= $passingScore;
            
            $testStatus = 'completed';
            if ($hasOpenEnded) {
                $testStatus = 'pending_review';
            } elseif (!$isPassed) {
                $testStatus = 'failed';
            }

            $test->update([
                'score' => $score,
                'status' => $testStatus
            ]);
            
            $this->checkStageProgress($test->stage);
        }

        session()->flash('success', 'Calificación automática completada.');
        $this->loadProcess();
    }

    protected function checkStageProgress(HrEvaluationStage $stage): void
    {
        $stage->load('prospectTests');
        
        $allTestsFinished = $stage->prospectTests->every(function ($test) {
            if (in_array($test->status, ['completed', 'graded', 'pending_review', 'partially_graded'])) return true;
            if ($test->status === 'failed' && $test->attempts_count >= $test->max_attempts) return true;
            return false;
        });

        if ($allTestsFinished) {
            $allPassedOrPending = $stage->prospectTests->every(function ($test) {
                return in_array($test->status, ['completed', 'graded', 'pending_review', 'partially_graded']);
            });

            if ($allPassedOrPending) {
                $stage->update(['status' => 'completed']);

                $process = $stage->process;
                if ($process->current_stage_index < $process->total_stages - 1) {
                    $process->increment('current_stage_index');
                } else {
                    $process->update(['status' => 'completed']);
                }
            } else {
                $stage->update(['status' => 'failed']);
            }
        }
    }

    public function render()
    {
        $testType = $this->prospect?->test_type ?: null;
        // If employee, we could add a test_type to process or employee
        // For now, if it's an employee, maybe we show all active tests or we need another way to filter.

        return view('livewire.hr.evaluation-process-management', [
            'testTemplates' => HrTestTemplate::where('is_active', true)
                ->when($testType, function($q) use ($testType) {
                    return $q->where(function($sq) use ($testType) {
                        $sq->where('role_target', $testType)
                           ->orWhereNull('role_target')
                           ->orWhere('role_target', '');
                    });
                })
                ->get()
        ]);
    }
}

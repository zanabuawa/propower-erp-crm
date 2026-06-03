<?php

namespace App\Livewire\HR;

use App\Models\HrTestTemplate;
use App\Models\HrTestQuestion;
use App\Models\HrTestOption;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Formulario de Plantilla')]
class TestTemplateForm extends Component
{
    public ?HrTestTemplate $template = null;
    public bool $isEdit = false;

    public string $name = '';
    public string $description = '';
    public string $role_target = '';
    public int $passing_score = 60;
    public ?int $duration_minutes = null;
    public bool $is_active = true;

    public array $questions = [];

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'role_target' => 'nullable|string|max:255',
        'passing_score' => 'required|integer|min:0|max:100',
        'duration_minutes' => 'nullable|integer|min:1|max:480',
        'is_active' => 'boolean',
        'questions.*.question_text' => 'required|string',
        'questions.*.type' => 'required|in:multiple_choice,open_ended',
        'questions.*.points' => 'required|integer|min:0',
        'questions.*.options.*.option_text' => 'required_if:questions.*.type,multiple_choice|string',
        'questions.*.options.*.is_correct' => 'boolean',
    ];

    public function mount(?HrTestTemplate $template = null): void
    {
        if ($template && $template->exists) {
            $this->template = $template;
            $this->isEdit = true;
            $this->name = $template->name;
            $this->description = $template->description ?? '';
            $this->role_target = $template->role_target ?? '';
            $this->passing_score = $template->passing_score;
            $this->duration_minutes = $template->duration_minutes;
            $this->is_active = $template->is_active;

            $this->questions = $template->questions()->with('options')->get()->map(function ($q) {
                return [
                    'id' => $q->id,
                    'question_text' => $q->question_text,
                    'type' => $q->type,
                    'points' => $q->points,
                    'options' => $q->options->map(function ($o) {
                        return [
                            'id' => $o->id,
                            'option_text' => $o->option_text,
                            'is_correct' => (bool)$o->is_correct,
                        ];
                    })->toArray(),
                ];
            })->toArray();
        } else {
            $this->addQuestion();
        }
    }

    public function addQuestion(): void
    {
        $this->questions[] = [
            'question_text' => '',
            'type' => 'multiple_choice',
            'points' => 10,
            'options' => [
                ['option_text' => '', 'is_correct' => true],
                ['option_text' => '', 'is_correct' => false],
            ],
        ];
    }

    public function removeQuestion(int $index): void
    {
        unset($this->questions[$index]);
        $this->questions = array_values($this->questions);
    }

    public function addOption(int $questionIndex): void
    {
        $this->questions[$questionIndex]['options'][] = [
            'option_text' => '',
            'is_correct' => false,
        ];
    }

    public function removeOption(int $questionIndex, int $optionIndex): void
    {
        $wasCorrect = $this->questions[$questionIndex]['options'][$optionIndex]['is_correct'] ?? false;
        
        unset($this->questions[$questionIndex]['options'][$optionIndex]);
        $this->questions[$questionIndex]['options'] = array_values($this->questions[$questionIndex]['options']);

        // If we removed the correct one, make the first one correct
        if ($wasCorrect && count($this->questions[$questionIndex]['options']) > 0) {
            $this->questions[$questionIndex]['options'][0]['is_correct'] = true;
        }
    }

    public function setCorrectOption(int $questionIndex, int $optionIndex): void
    {
        $questions = $this->questions;
        foreach ($questions[$questionIndex]['options'] as $i => $option) {
            $questions[$questionIndex]['options'][$i]['is_correct'] = ($i === $optionIndex);
        }
        $this->questions = $questions;
    }

    public function save(): void
    {
        $this->validate();

        \Illuminate\Support\Facades\Log::info('Saving Template. Questions array state:', $this->questions);

        // Custom validation for multiple choice questions
        foreach ($this->questions as $index => $question) {
            if ($question['type'] === 'multiple_choice') {
                $hasCorrect = collect($question['options'] ?? [])->contains('is_correct', true);
                if (!$hasCorrect) {
                    $this->addError("questions.$index.options", "Debes seleccionar al menos una opción correcta para la pregunta #" . ($index + 1));
                    return;
                }
            }
        }

        DB::transaction(function () {
            $totalPoints = collect($this->questions)->sum('points');

            $templateData = [
                'name' => $this->name,
                'description' => $this->description,
                'role_target' => $this->role_target,
                'total_points' => $totalPoints,
                'passing_score' => $this->passing_score,
                'duration_minutes' => $this->duration_minutes,
                'is_active' => $this->is_active,
                'company_id' => auth()->user()->company_id ?? 1,
            ];

            if ($this->isEdit) {
                $this->template->update($templateData);
            } else {
                $this->template = HrTestTemplate::create($templateData);
            }

            $existingQuestionIds = $this->template->questions()->pluck('id')->toArray();
            $currentQuestionIds = [];

            foreach ($this->questions as $index => $qData) {
                $question = $this->template->questions()->updateOrCreate(
                    ['id' => $qData['id'] ?? null],
                    [
                        'question_text' => $qData['question_text'],
                        'type' => $qData['type'],
                        'points' => $qData['points'],
                        'order' => $index,
                    ]
                );

                $currentQuestionIds[] = $question->id;

                if ($qData['type'] === 'multiple_choice' && isset($qData['options'])) {
                    $existingOptionIds = $question->options()->pluck('id')->toArray();
                    $currentOptionIds = [];

                    foreach ($qData['options'] as $oData) {
                        $option = $question->options()->updateOrCreate(
                            ['id' => $oData['id'] ?? null],
                            [
                                'option_text' => $oData['option_text'],
                                'is_correct' => (bool)($oData['is_correct'] ?? false),
                            ]
                        );
                        $currentOptionIds[] = $option->id;
                    }

                    // Delete options not in the current set
                    $question->options()->whereIn('id', array_diff($existingOptionIds, $currentOptionIds))->delete();
                } else {
                    // If changed to open ended, delete all options
                    $question->options()->delete();
                }
            }

            // Delete questions not in the current set
            $this->template->questions()->whereIn('id', array_diff($existingQuestionIds, $currentQuestionIds))->delete();
        });

        session()->flash('success', 'Plantilla guardada correctamente.');
        $this->redirect(route('hr.test-templates.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.hr.test-template-form');
    }
}

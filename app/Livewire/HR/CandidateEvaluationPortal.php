<?php

namespace App\Livewire\HR;

use App\Models\HrProspect;
use App\Models\HrEvaluationProcess;
use App\Models\HrEvaluationStage;
use App\Models\HrProspectTest;
use App\Models\HrTestAttempt;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.guest')]
#[Title('Portal de Evaluación del Candidato')]
class CandidateEvaluationPortal extends Component
{
    public HrProspect $prospect;
    public ?HrEvaluationProcess $process = null;
    public ?HrEvaluationStage $currentStage = null;

    public function mount(HrProspect $prospect): void
    {
        $this->prospect = $prospect;
        $this->process = HrEvaluationProcess::where('hr_prospect_id', $prospect->id)->first();

        if ($this->process) {
            $this->currentStage = $this->process->stages()
                ->where('order', $this->process->current_stage_index)
                ->first();
        }
    }

    public function getTestsProperty()
    {
        if (!$this->currentStage) {
            return collect();
        }

        return $this->currentStage->prospectTests()->with('template')->get();
    }

    public function getCurrentStageAvailableProperty(): bool
    {
        return $this->currentStage?->isAvailable() ?? false;
    }

    public function getProgressPercentageProperty(): int
    {
        if (!$this->process || $this->process->total_stages === 0) {
            return 0;
        }

        return (int) (($this->process->current_stage_index / $this->process->total_stages) * 100);
    }

    public function embedVideoUrl(string $url): ?string
    {
        $host = parse_url($url, PHP_URL_HOST) ?: '';
        $path = trim(parse_url($url, PHP_URL_PATH) ?: '', '/');
        $query = [];
        parse_str(parse_url($url, PHP_URL_QUERY) ?: '', $query);
        $videoId = null;

        if (str_contains($host, 'youtu.be')) {
            $videoId = explode('/', $path)[0] ?? null;
        }

        if (! $videoId && str_contains($host, 'youtube.com')) {
            $videoId = $query['v'] ?? null;

            if (! $videoId && preg_match('~(?:embed|shorts|live)/([^/?&]+)~', $path, $matches)) {
                $videoId = $matches[1];
            }
        }

        if ($videoId) {
            $videoId = preg_replace('/[^A-Za-z0-9_-]/', '', $videoId);

            return $videoId ? "https://www.youtube-nocookie.com/embed/{$videoId}?rel=0&modestbranding=1" : null;
        }

        if (str_contains($host, 'vimeo.com') && preg_match('/(\d+)/', $path, $matches)) {
            return "https://player.vimeo.com/video/{$matches[1]}";
        }

        return null;
    }

    public function render()
    {
        return view('livewire.hr.candidate-evaluation-portal', [
            'tests' => $this->tests,
            'progress' => $this->progress_percentage,
            'currentStageAvailable' => $this->current_stage_available,
        ]);
    }
}

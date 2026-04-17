<?php

namespace App\Livewire\Sales;

use App\Models\SalesOpportunity;
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class CrmPipelineIndex extends Component
{
    public string $filterUser  = '';
    public string $viewMode    = 'kanban'; // kanban | list
    public bool   $showWonLost = false;

    public function moveStage(int $oppId, string $newStage): void
    {
        $opp = SalesOpportunity::where('company_id', auth()->user()->company_id)
            ->findOrFail($oppId);

        $updates = [
            'stage'       => $newStage,
            'probability' => SalesOpportunity::STAGE_PROBABILITY[$newStage],
        ];

        if ($newStage === 'won') {
            $updates['won_at']  = now();
            $updates['lost_at'] = null;
        } elseif ($newStage === 'lost') {
            $updates['lost_at'] = now();
            $updates['won_at']  = null;
        }

        $opp->update($updates);
    }

    public function render()
    {
        $companyId = auth()->user()->company_id;

        $stages = array_keys(SalesOpportunity::STAGES);
        if (!$this->showWonLost) {
            $stages = array_diff($stages, ['won', 'lost']);
        }

        $opportunities = SalesOpportunity::query()
            ->where('company_id', $companyId)
            ->when(!$this->showWonLost, fn($q) => $q->whereNotIn('stage', ['won', 'lost']))
            ->when($this->filterUser, fn($q) => $q->where('assigned_to', $this->filterUser))
            ->with(['prospect', 'customer', 'assignedTo'])
            ->withCount('activities')
            ->orderBy('expected_close_date')
            ->get();

        // Agrupar por etapa para el kanban
        $byStage = collect($stages)->mapWithKeys(fn($stage) => [
            $stage => $opportunities->where('stage', $stage)->values(),
        ]);

        // Totales
        $summary = [
            'count'          => $opportunities->count(),
            'total_value'    => $opportunities->sum('estimated_value'),
            'weighted_value' => $opportunities->sum(fn($o) => $o->weightedValue()),
            'won_this_month' => SalesOpportunity::where('company_id', $companyId)
                ->where('stage', 'won')
                ->whereMonth('won_at', now()->month)
                ->sum('estimated_value'),
        ];

        $users = User::where('company_id', $companyId)->orderBy('name')->get();

        return view('livewire.sales.crm-pipeline-index', compact('byStage', 'summary', 'users', 'stages'));
    }
}

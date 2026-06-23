<?php

namespace App\Livewire\Tenders;

use App\Models\WorkReport;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Formato Libre de Reporte')]
class WorkReportDocumentEditor extends Component
{
    public WorkReport $report;
    public string $custom_body = '';

    public function mount(WorkReport $report): void
    {
        $report->load(['project.customer', 'project.branch.company', 'createdBy']);

        abort_if(
            ! $report->project->branch || $report->project->branch->company_id !== auth()->user()->company_id,
            404
        );

        $this->report = $report;
        $this->custom_body = $report->custom_body ?: $this->defaultBody();
    }

    public function save(): void
    {
        $this->validate([
            'custom_body' => 'required|string',
        ]);

        $this->report->update([
            'custom_body' => $this->sanitizeHtml($this->custom_body),
        ]);

        session()->flash('success', 'Formato libre guardado.');
    }

    public function resetToDefault(): void
    {
        $this->custom_body = $this->defaultBody();
        $this->dispatch('free-report-reset', body: $this->custom_body);
    }

    private function defaultBody(): string
    {
        return '
            <h2>Actividades realizadas</h2>
            <p>' . e($this->report->activities ?: 'Sin actividades capturadas.') . '</p>
        ';
    }

    private function sanitizeHtml(string $html): string
    {
        return strip_tags($html, '<p><br><strong><b><em><i><u><ul><ol><li><h1><h2><h3><blockquote><table><thead><tbody><tr><th><td>');
    }

    public function render()
    {
        return view('livewire.tenders.work-report-document-editor', [
            'company' => $this->report->project->branch?->company,
            'customer' => $this->report->project->customer,
            'project' => $this->report->project,
        ]);
    }
}

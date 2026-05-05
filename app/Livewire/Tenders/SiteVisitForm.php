<?php

namespace App\Livewire\Tenders;

use App\Models\Project;
use App\Models\SiteVisit;
use App\Models\Tender;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
#[Title('Visita de Campo')]
class SiteVisitForm extends Component
{
    use WithFileUploads;

    public ?SiteVisit $siteVisit = null;

    public ?int $project_id = null;
    public ?int $tender_id = null;
    public string $visit_date = '';
    public string $visit_type = 'supervision';
    public string $purpose = '';
    public string $address = '';
    public string $location_notes = '';
    public string $attendeesText = '';
    public string $report = '';
    public string $status = 'programada';
    public array $newPhotos = [];

    public function mount(?SiteVisit $siteVisit = null): void
    {
        $this->visit_date = now()->format('Y-m-d');
        if ($siteVisit && $siteVisit->exists) {
            $this->siteVisit      = $siteVisit;
            $this->project_id     = $siteVisit->project_id;
            $this->tender_id      = $siteVisit->tender_id;
            $this->visit_date     = $siteVisit->visit_date->format('Y-m-d');
            $this->visit_type     = $siteVisit->visit_type;
            $this->purpose        = $siteVisit->purpose;
            $this->address        = $siteVisit->address ?? '';
            $this->location_notes = $siteVisit->location_notes ?? '';
            $this->report         = $siteVisit->report ?? '';
            $this->status         = $siteVisit->status;
            $this->attendeesText  = implode("\n", $siteVisit->attendees ?? []);
        }
    }

    public function save(): void
    {
        $this->validate([
            'visit_date' => 'required|date',
            'visit_type' => 'required|in:' . implode(',', array_keys(SiteVisit::TYPES)),
            'purpose'    => 'required|string|max:300',
            'status'     => 'required|in:' . implode(',', array_keys(SiteVisit::STATUSES)),
            'newPhotos'  => 'nullable|array',
            'newPhotos.*'=> 'image|max:5120',
        ]);

        $attendees = array_values(array_filter(array_map('trim', explode("\n", $this->attendeesText))));

        $existingPhotos = $this->siteVisit?->photos ?? [];
        $photoPaths = $existingPhotos;
        foreach ($this->newPhotos as $photo) {
            $photoPaths[] = $photo->store('visits/photos', 'public');
        }

        $data = [
            'company_id'     => auth()->user()->company_id,
            'project_id'     => $this->project_id,
            'tender_id'      => $this->tender_id ?: null,
            'visit_date'     => $this->visit_date,
            'visit_type'     => $this->visit_type,
            'purpose'        => $this->purpose,
            'address'        => $this->address ?: null,
            'location_notes' => $this->location_notes ?: null,
            'attendees'      => $attendees,
            'report'         => $this->report ?: null,
            'photos'         => $photoPaths,
            'status'         => $this->status,
            'created_by'     => auth()->id(),
        ];

        if ($this->siteVisit && $this->siteVisit->exists) {
            $this->siteVisit->update($data);
            session()->flash('success', 'Visita actualizada.');
        } else {
            SiteVisit::create($data);
            session()->flash('success', 'Visita registrada.');
        }

        $this->redirect(route('tenders.visits.index'), navigate: true);
    }

    public function render()
    {
        $companyId = auth()->user()->company_id;
        return view('livewire.tenders.site-visit-form', [
            'projects' => Project::whereHas('branch', fn($q) => $q->where('company_id', $companyId))->orderBy('name')->get(),
            'tenders'  => Tender::where('company_id', $companyId)->orderBy('name')->get(),
            'types'    => SiteVisit::TYPES,
            'statuses' => SiteVisit::STATUSES,
        ]);
    }
}

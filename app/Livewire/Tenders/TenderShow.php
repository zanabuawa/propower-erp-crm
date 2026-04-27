<?php

namespace App\Livewire\Tenders;

use App\Models\Tender;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Detalle Licitación')]
class TenderShow extends Component
{
    public Tender $tender;
    public string $activeTab = 'partidas';

    public function mount(Tender $tender): void
    {
        $this->tender = $tender;
    }

    public function render()
    {
        $this->tender->load(['items', 'quotations.issuingCompany', 'workPermits', 'workReports', 'workLibranzas', 'siteVisits', 'customer', 'project', 'responsible']);

        return view('livewire.tenders.tender-show', [
            'tender'   => $this->tender,
            'statuses' => Tender::STATUSES,
            'types'    => Tender::TYPES,
        ]);
    }
}

<?php

namespace App\Livewire\Tenders;

use App\Models\FinanceCashflow;
use App\Models\FinanceTransaction;
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

        $financeTransactions = FinanceTransaction::where('tender_id', $this->tender->id)
            ->with('account:id,name')
            ->orderByDesc('transaction_date')
            ->get();

        $financeCashflows = FinanceCashflow::where('tender_id', $this->tender->id)
            ->orderBy('expected_date')
            ->get();

        return view('livewire.tenders.tender-show', [
            'tender'              => $this->tender,
            'statuses'            => Tender::STATUSES,
            'types'               => Tender::TYPES,
            'financeTransactions' => $financeTransactions,
            'financeCashflows'    => $financeCashflows,
        ]);
    }
}

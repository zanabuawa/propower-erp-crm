<?php

namespace App\Livewire\Tenders;

use App\Models\Tender;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Licitaciones')]
class TenderIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatus = '';
    public string $filterType = '';

    public function updatedSearch(): void   { $this->resetPage(); }
    public function updatedFilterStatus(): void { $this->resetPage(); }
    public function updatedFilterType(): void   { $this->resetPage(); }

    public function delete(int $id): void
    {
        Tender::findOrFail($id)->delete();
        session()->flash('success', 'Licitación eliminada.');
    }

    public function render()
    {
        $tenders = Tender::where('company_id', auth()->user()->company_id)
            ->when($this->search, fn($q) => $q->where(fn($q2) =>
                $q2->where('name', 'like', '%' . $this->search . '%')
                   ->orWhere('folio', 'like', '%' . $this->search . '%')
            ))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterType,   fn($q) => $q->where('type', $this->filterType))
            ->with(['customer', 'responsible'])
            ->latest()
            ->paginate(15);

        return view('livewire.tenders.tender-index', [
            'tenders'  => $tenders,
            'statuses' => Tender::STATUSES,
            'types'    => Tender::TYPES,
        ]);
    }
}

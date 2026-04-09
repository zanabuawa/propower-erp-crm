<?php

namespace App\Livewire\Suppliers;

use App\Models\Supplier;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class SupplierIndex extends Component
{
    use WithPagination;

    public string $search           = '';
    public string $filterType       = '';
    public string $filterStatus     = '';
    public string $filterCategory   = '';
    public string $filterServiceType = '';
    public string $filterCity       = '';
    public string $filterState      = '';
    public string $filterCountry    = '';
    public bool   $confirmingDelete = false;
    public ?int   $deleteId         = null;

    public function updatingSearch(): void          { $this->resetPage(); }
    public function updatingFilterType(): void      { $this->resetPage(); }
    public function updatingFilterStatus(): void    { $this->resetPage(); }
    public function updatingFilterCategory(): void  { $this->resetPage(); }
    public function updatingFilterServiceType(): void { $this->resetPage(); }
    public function updatingFilterCity(): void      { $this->resetPage(); }

    public function clearFilters(): void
    {
        $this->reset([
            'search', 'filterType', 'filterStatus', 'filterCategory',
            'filterServiceType', 'filterCity', 'filterState', 'filterCountry',
        ]);
        $this->resetPage();
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
        $this->confirmingDelete = true;
    }

    public function cancelDelete(): void
    {
        $this->deleteId = null;
        $this->confirmingDelete = false;
    }

    public function delete(): void
    {
        Supplier::findOrFail($this->deleteId)->delete();
        $this->confirmingDelete = false;
        $this->deleteId = null;
        session()->flash('success', 'Proveedor eliminado correctamente.');
    }

    public function updatingFilterCountry(): void
    {
        // Al cambiar país, limpiar estado y ciudad
        $this->filterState = '';
        $this->filterCity  = '';
        $this->resetPage();
    }

    public function updatingFilterState(): void
    {
        // Al cambiar estado, limpiar ciudad
        $this->filterCity = '';
        $this->resetPage();
    }

    public function render()
    {
        $companyId = auth()->user()->company_id;

        $baseQuery = Supplier::query()->where('company_id', $companyId);

        // Opciones de ubicación filtradas en cascada
        $countries = (clone $baseQuery)
            ->whereNotNull('country')->where('country', '!=', '')
            ->distinct()->orderBy('country')->pluck('country');

        $states = (clone $baseQuery)
            ->whereNotNull('state')->where('state', '!=', '')
            ->when($this->filterCountry, fn($q) => $q->where('country', $this->filterCountry))
            ->distinct()->orderBy('state')->pluck('state');

        $cities = (clone $baseQuery)
            ->whereNotNull('city')->where('city', '!=', '')
            ->when($this->filterCountry, fn($q) => $q->where('country', $this->filterCountry))
            ->when($this->filterState,   fn($q) => $q->where('state',   $this->filterState))
            ->distinct()->orderBy('city')->pluck('city');

        return view('livewire.suppliers.supplier-index', [
            'suppliers' => (clone $baseQuery)
                ->when($this->search, fn($q) => $q
                    ->where(fn($q) => $q
                        ->where('name', 'like', "%{$this->search}%")
                        ->orWhere('rfc',  'like', "%{$this->search}%")))
                ->when($this->filterType,        fn($q) => $q->where('type',              $this->filterType))
                ->when($this->filterStatus,      fn($q) => $q->where('status',            $this->filterStatus))
                ->when($this->filterCategory,    fn($q) => $q->where('supplier_category', $this->filterCategory))
                ->when($this->filterServiceType, fn($q) => $q->where('service_type',      $this->filterServiceType))
                ->when($this->filterCountry,     fn($q) => $q->where('country',           $this->filterCountry))
                ->when($this->filterState,       fn($q) => $q->where('state',             $this->filterState))
                ->when($this->filterCity,        fn($q) => $q->where('city',              $this->filterCity))
                ->with(['phones', 'emails', 'assignedTo'])
                ->withCount('contacts', 'notes')
                ->latest()
                ->paginate(15),
            'countries' => $countries,
            'states'    => $states,
            'cities'    => $cities,
            'activeFilters' => array_filter([
                $this->filterType, $this->filterStatus, $this->filterCategory,
                $this->filterServiceType, $this->filterCity, $this->filterState, $this->filterCountry,
            ]),
        ]);
    }
}

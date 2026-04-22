<?php

namespace App\Livewire\HR;

use App\Models\Branch;
use App\Models\HrJobOpening;
use App\Models\HrPosition;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Vacantes')]
class JobOpeningIndex extends Component
{
    use WithPagination;

    public string $search       = '';
    public string $filterStatus = '';
    public string $filterType   = '';

    // Modal
    public bool    $showModal  = false;
    public ?int    $editingId  = null;

    public string  $title          = '';
    public ?int    $position_id    = null;
    public ?int    $branch_id      = null;
    public string  $type           = 'external';
    public int     $quantity       = 1;
    public string  $salary_range   = '';
    public string  $description    = '';
    public string  $requirements   = '';
    public string  $published_at   = '';
    public string  $closing_date   = '';
    public string  $status         = 'open';

    public array   $positionOptions = [];
    public array   $branchOptions   = [];

    public function mount(): void
    {
        $companyId = auth()->user()->company_id;

        $this->positionOptions = HrPosition::where('company_id', $companyId)
            ->orderBy('name')->get(['id', 'name'])->toArray();

        $this->branchOptions = Branch::where('company_id', $companyId)
            ->orderBy('name')->get(['id', 'name'])->toArray();
    }

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilterStatus(): void { $this->resetPage(); }
    public function updatingFilterType(): void { $this->resetPage(); }

    public function openCreate(): void
    {
        $this->reset(['editingId','title','position_id','branch_id','salary_range',
                      'description','requirements','closing_date']);
        $this->type         = 'external';
        $this->quantity     = 1;
        $this->status       = 'open';
        $this->published_at = now()->format('Y-m-d');
        $this->showModal    = true;
    }

    public function openEdit(int $id): void
    {
        $o = HrJobOpening::findOrFail($id);
        $this->editingId    = $id;
        $this->title        = $o->title;
        $this->position_id  = $o->position_id;
        $this->branch_id    = $o->branch_id;
        $this->type         = $o->type;
        $this->quantity     = $o->quantity;
        $this->salary_range = $o->salary_range ?? '';
        $this->description  = $o->description ?? '';
        $this->requirements = $o->requirements ?? '';
        $this->published_at = $o->published_at?->format('Y-m-d') ?? '';
        $this->closing_date = $o->closing_date?->format('Y-m-d') ?? '';
        $this->status       = $o->status;
        $this->showModal    = true;
    }

    public function save(): void
    {
        $this->validate([
            'title'       => 'required|string|max:150',
            'position_id' => 'required|exists:hr_positions,id',
            'type'        => 'required|in:' . implode(',', array_keys(HrJobOpening::TYPES)),
            'quantity'    => 'required|integer|min:1',
            'status'      => 'required|in:' . implode(',', array_keys(HrJobOpening::STATUSES)),
            'published_at'=> 'nullable|date',
            'closing_date'=> 'nullable|date|after_or_equal:published_at',
        ], [
            'title.required'    => 'El título de la vacante es obligatorio.',
            'position_id.required' => 'Selecciona un puesto.',
        ]);

        $data = [
            'title'        => $this->title,
            'position_id'  => $this->position_id,
            'branch_id'    => $this->branch_id ?: null,
            'type'         => $this->type,
            'quantity'     => $this->quantity,
            'salary_range' => $this->salary_range ?: null,
            'description'  => $this->description ?: null,
            'requirements' => $this->requirements ?: null,
            'published_at' => $this->published_at ?: null,
            'closing_date' => $this->closing_date ?: null,
            'status'       => $this->status,
        ];

        if ($this->editingId) {
            HrJobOpening::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Vacante actualizada.');
        } else {
            HrJobOpening::create(array_merge($data, [
                'company_id' => auth()->user()->company_id,
                'created_by' => auth()->id(),
            ]));
            session()->flash('success', 'Vacante publicada.');
        }

        $this->showModal = false;
    }

    public function toggleStatus(int $id): void
    {
        $o = HrJobOpening::findOrFail($id);
        $o->update(['status' => $o->status === 'open' ? 'paused' : 'open']);
    }

    public function close(int $id): void
    {
        HrJobOpening::findOrFail($id)->update(['status' => 'closed']);
    }

    public function delete(int $id): void
    {
        HrJobOpening::findOrFail($id)->delete();
        session()->flash('success', 'Vacante eliminada.');
    }

    public function render()
    {
        $openings = HrJobOpening::with(['position', 'branch'])
            ->withCount('prospects')
            ->where('company_id', auth()->user()->company_id)
            ->when($this->search, fn($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterType,   fn($q) => $q->where('type',   $this->filterType))
            ->latest()
            ->paginate(12);

        $stats = HrJobOpening::where('company_id', auth()->user()->company_id)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('livewire.hr.job-opening-index', compact('openings', 'stats'));
    }
}

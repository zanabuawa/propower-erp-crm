<?php

namespace App\Livewire\HR;

use App\Models\Branch;
use App\Models\HrJobOpening;
use App\Models\HrPosition;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Formulario de Vacante')]
class JobOpeningForm extends Component
{
    public ?HrJobOpening $jobOpening = null;

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

    public function mount(?HrJobOpening $jobOpening = null): void
    {
        $this->published_at = now()->format('Y-m-d');

        if ($jobOpening && $jobOpening->exists) {
            $this->jobOpening   = $jobOpening;
            $this->title        = $jobOpening->title;
            $this->position_id  = $jobOpening->position_id;
            $this->branch_id    = $jobOpening->branch_id;
            $this->type         = $jobOpening->type;
            $this->quantity     = $jobOpening->quantity;
            $this->salary_range = $jobOpening->salary_range ?? '';
            $this->description  = $jobOpening->description ?? '';
            $this->requirements = $jobOpening->requirements ?? '';
            $this->published_at = $jobOpening->published_at?->format('Y-m-d') ?? '';
            $this->closing_date = $jobOpening->closing_date?->format('Y-m-d') ?? '';
            $this->status       = $jobOpening->status;
        }
    }

    public function rules(): array
    {
        return [
            'title'       => 'required|string|max:150',
            'position_id' => 'required|exists:hr_positions,id',
            'branch_id'   => 'nullable|exists:branches,id',
            'type'        => 'required|in:' . implode(',', array_keys(HrJobOpening::TYPES)),
            'quantity'    => 'required|integer|min:1',
            'status'      => 'required|in:' . implode(',', array_keys(HrJobOpening::STATUSES)),
            'published_at'=> 'nullable|date',
            'closing_date'=> 'nullable|date|after_or_equal:published_at',
            'salary_range'=> 'nullable|string|max:100',
            'description' => 'nullable|string',
            'requirements'=> 'nullable|string',
        ];
    }

    public function save(): void
    {
        $this->validate();

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

        if ($this->jobOpening && $this->jobOpening->exists) {
            $this->jobOpening->update($data);
            session()->flash('success', 'Vacante actualizada correctamente.');
        } else {
            HrJobOpening::create(array_merge($data, [
                'company_id' => auth()->user()->company_id,
                'created_by' => auth()->id(),
            ]));
            session()->flash('success', 'Vacante publicada correctamente.');
        }

        $this->redirect(route('hr.job-openings.index'), navigate: true);
    }

    public function render()
    {
        $companyId = auth()->user()->company_id;
        $positions = HrPosition::where('company_id', $companyId)->orderBy('name')->get(['id', 'name']);
        $branches  = Branch::where('company_id', $companyId)->orderBy('name')->get(['id', 'name']);

        return view('livewire.hr.job-opening-form', compact('positions', 'branches'));
    }
}

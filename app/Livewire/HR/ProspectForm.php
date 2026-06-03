<?php

namespace App\Livewire\HR;

use App\Models\HrJobOpening;
use App\Models\HrPosition;
use App\Models\HrProspect;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
#[Title('Prospecto')]
class ProspectForm extends Component
{
    use WithFileUploads;

    public ?HrProspect $prospect = null;

    public ?string $first_name = '';
    public ?string $last_name = '';
    public ?string $second_last_name = '';
    public ?string $email = '';
    public ?string $phone = '';
    public ?string $test_type = null;
    public ?int $position_id = null;
    public ?int $job_opening_id = null;
    public ?string $source = '';
    public ?string $initial_notes = '';
    public ?string $interview_date = '';
    public string $calendar_color = '#7c3aed';
    public string $status = 'nuevo';
    public ?string $status_reason = '';
    public $cv_upload = null;

    public function mount(?HrProspect $prospect = null): void
    {
        if ($prospect && $prospect->exists) {
            $this->prospect = $prospect;
            $this->first_name = $prospect->first_name;
            $this->last_name = $prospect->last_name;
            $this->second_last_name = $prospect->second_last_name ?? '';
            $this->email = $prospect->email ?? '';
            $this->phone = $prospect->phone ?? '';
            $this->test_type = $prospect->test_type;
            $this->position_id    = $prospect->position_id;
            $this->job_opening_id = $prospect->job_opening_id;
            $this->source = $prospect->source ?? '';
            $this->initial_notes = $prospect->initial_notes ?? '';
            $this->status = $prospect->status;
            $this->interview_date = $prospect->interview_date?->format('Y-m-d\TH:i') ?? '';
            $this->calendar_color = $prospect->calendar_color ?: '#7c3aed';
        } elseif ($jobOpeningId = request()->query('job_opening_id')) {
            $jobOpening = HrJobOpening::where('company_id', auth()->user()->company_id)
                ->findOrFail($jobOpeningId);

            $this->job_opening_id = $jobOpening->id;
            $this->position_id = $jobOpening->position_id;
        }
    }

    public function updatedJobOpeningId($value): void
    {
        if (! $value) {
            return;
        }

        $jobOpening = HrJobOpening::where('company_id', auth()->user()->company_id)
            ->find($value);

        if ($jobOpening) {
            $this->position_id = $jobOpening->position_id;
        }
    }

    public function save(): void
    {
        $rules = [
            'first_name'     => 'required|string|max:191',
            'last_name'      => 'required|string|max:191',
            'email'          => 'nullable|email|max:191',
            'phone'          => 'nullable|string|max:20',
            'test_type'      => 'nullable|string|in:segurista,supervisor,otro',
            'position_id'    => 'nullable|exists:hr_positions,id',
            'job_opening_id' => 'nullable|exists:hr_job_openings,id',
            'source'         => 'nullable|string|in:' . implode(',', array_keys(HrProspect::SOURCES)),
            'status'         => 'required|string|in:' . implode(',', array_keys(HrProspect::STATUSES)),
            'interview_date' => 'nullable|date',
            'calendar_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'cv_upload'      => 'nullable|file|mimes:pdf,doc,docx|max:5120',
        ];

        // Regla: Si el estado es rechazado, motivo obligatorio
        if ($this->status === 'rechazado') {
            $rules['status_reason'] = 'required|string|min:5';
        }

        $this->validate($rules);

        $data = [
            'company_id'       => auth()->user()->company_id,
            'first_name'       => $this->first_name,
            'last_name'        => $this->last_name,
            'second_last_name' => $this->second_last_name ?: null,
            'email'            => $this->email ?: null,
            'phone'            => $this->phone ?: null,
            'test_type'        => $this->test_type ?: null,
            'position_id'      => $this->position_id,
            'job_opening_id'   => $this->job_opening_id ?: null,
            'source'           => $this->source ?: null,
            'initial_notes'    => $this->initial_notes ?: null,
            'interview_date'   => $this->interview_date ?: null,
            'calendar_color'   => $this->calendar_color,
        ];

        if ($this->cv_upload) {
            $data['cv_path'] = $this->cv_upload->store('hr/cvs', 'public');
        }

        if ($this->prospect && $this->prospect->exists) {
            $oldStatus = $this->prospect->status;
            $this->prospect->update($data);
            
            if ($oldStatus !== $this->status) {
                $this->prospect->changeStatus($this->status, $this->status_reason);
            }
            
            session()->flash('success', 'Prospecto actualizado correctamente.');
        } else {
            $data['status'] = $this->status;
            $prospect = HrProspect::create($data);
            
            // Log inicial
            $prospect->statusLogs()->create([
                'user_id'   => auth()->id(),
                'to_status' => $this->status,
                'reason'    => 'Registro inicial' . ($this->status === 'rechazado' ? ': ' . $this->status_reason : ''),
            ]);
            
            session()->flash('success', 'Prospecto registrado correctamente.');
        }

        $this->redirect(route('hr.prospects.index'), navigate: true);
    }

    public function render()
    {
        $positions   = HrPosition::where('is_active', true)->orderBy('name')->get();
        $jobOpenings = HrJobOpening::where('company_id', auth()->user()->company_id)
            ->whereNotIn('status', ['cancelled'])
            ->orderBy('title')->get(['id', 'title', 'status']);
        return view('livewire.hr.prospect-form', compact('positions', 'jobOpenings'));
    }
}

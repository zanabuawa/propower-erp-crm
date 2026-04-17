<?php

namespace App\Livewire\HR;

use App\Models\HrCourse;
use App\Models\HrEmployee;
use App\Models\HrEmployeeDocument;
use App\Models\HrEmployeeEducation;
use App\Models\HrEmployeeTraining;
use Livewire\Component;
use Livewire\WithFileUploads;

class EmployeeExpedient extends Component
{
    use WithFileUploads;

    public HrEmployee $employee;
    public string $type = ''; // document, education, training

    // Form Document
    public string $doc_type = '';
    public $doc_file;
    public string $doc_expiry = '';
    public string $doc_notes = '';

    // Form Education
    public string $edu_institution = '';
    public string $edu_degree = '';
    public string $edu_field = '';
    public string $edu_start = '';
    public string $edu_end = '';
    public bool $edu_completed = false;

    // Form Training
    public ?int $training_course_id = null;
    public string $training_date = '';
    public string $training_expiry = '';
    public string $training_status = 'completed';
    public $training_file;

    protected $listeners = ['openExpedientModal' => 'openModal'];

    public function openModal(string $type): void
    {
        $this->type = $type;
        $this->resetForm();
        $this->dispatch('show-expedient-modal');
    }

    public function resetForm(): void
    {
        $this->reset(['doc_type', 'doc_file', 'doc_expiry', 'doc_notes', 'edu_institution', 'edu_degree', 'edu_field', 'edu_start', 'edu_end', 'edu_completed', 'training_course_id', 'training_date', 'training_expiry', 'training_status', 'training_file']);
    }

    public function saveDocument(): void
    {
        $this->validate([
            'doc_type' => 'required|string',
            'doc_file' => 'required|file|max:5120',
            'doc_expiry' => 'nullable|date',
        ]);

        $path = $this->doc_file->store('hr/documents/' . $this->employee->id, 'public');

        HrEmployeeDocument::create([
            'employee_id' => $this->employee->id,
            'document_type' => $this->doc_type,
            'file_path' => $path,
            'expiry_date' => $this->doc_expiry ?: null,
            'notes' => $this->doc_notes,
        ]);

        $this->closeAndRefresh('Documento guardado correctamente.');
    }

    public function saveEducation(): void
    {
        $this->validate([
            'edu_institution' => 'required|string',
            'edu_degree' => 'required|string',
            'edu_start' => 'nullable|date',
        ]);

        HrEmployeeEducation::create([
            'employee_id' => $this->employee->id,
            'institution' => $this->edu_institution,
            'degree' => $this->edu_degree,
            'field_of_study' => $this->edu_field,
            'start_date' => $this->edu_start ?: null,
            'end_date' => $this->edu_end ?: null,
            'is_completed' => $this->edu_completed,
        ]);

        $this->closeAndRefresh('Historial académico registrado.');
    }

    public function saveTraining(): void
    {
        $this->validate([
            'training_course_id' => 'required|exists:hr_courses,id',
            'training_date' => 'nullable|date',
        ]);

        $path = $this->training_file ? $this->training_file->store('hr/trainings/' . $this->employee->id, 'public') : null;

        HrEmployeeTraining::create([
            'employee_id' => $this->employee->id,
            'course_id' => $this->training_course_id,
            'completion_date' => $this->training_date ?: null,
            'expiry_date' => $this->training_expiry ?: null,
            'status' => $this->training_status,
            'certificate_path' => $path,
        ]);

        $this->closeAndRefresh('Capacitación registrada.');
    }

    private function closeAndRefresh(string $message): void
    {
        $this->dispatch('hide-expedient-modal');
        session()->flash('success', $message);
        $this->dispatch('refreshEmployeeShow');
    }

    public function render()
    {
        $courses = HrCourse::where('company_id', auth()->user()->company_id)->orderBy('name')->get();
        return view('livewire.hr.employee-expedient', compact('courses'));
    }
}

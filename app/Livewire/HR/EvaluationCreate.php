<?php

namespace App\Livewire\HR;

use App\Models\HrEmployee;
use App\Models\HrProspect;
use App\Models\HrEvaluationProcess;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Iniciar Evaluación de Permiso')]
class EvaluationCreate extends Component
{
    public $type = 'prospect'; // 'prospect' or 'employee'
    public $search = '';
    public $selected_id = null;
    public $test_type = '';

    public function selectItem($id)
    {
        $this->selected_id = $id;
    }

    public function startProcess()
    {
        $this->validate([
            'selected_id' => 'required',
            'test_type' => 'required|in:segurista,supervisor,otro',
        ]);

        $data = [
            'status' => 'active',
            'current_stage_index' => 0,
            'total_stages' => 0,
        ];

        if ($this->type === 'prospect') {
            $data['hr_prospect_id'] = $this->selected_id;
            $prospect = HrProspect::find($this->selected_id);
            $prospect->update(['test_type' => $this->test_type]);
        } else {
            $data['hr_employee_id'] = $this->selected_id;
            // Employees don't have test_type column yet, maybe add it or just use it for the process
        }

        $process = HrEvaluationProcess::create($data);

        session()->flash('success', 'Proceso de evaluación iniciado correctamente.');

        if ($this->type === 'prospect') {
            return redirect()->route('hr.prospects.evaluation', $this->selected_id);
        } else {
            // We need a route for employee evaluation management
            return redirect()->route('hr.evaluations.dashboard');
        }
    }

    public function render()
    {
        $items = [];
        if ($this->type === 'prospect') {
            $items = HrProspect::where('status', '!=', 'contratado')
                ->where(function($q) {
                    $q->where('first_name', 'like', '%' . $this->search . '%')
                      ->orWhere('last_name', 'like', '%' . $this->search . '%');
                })
                ->latest()
                ->take(10)
                ->get();
        } else {
            $items = HrEmployee::where('status', 'active')
                ->where(function($q) {
                    $q->where('first_name', 'like', '%' . $this->search . '%')
                      ->orWhere('last_name', 'like', '%' . $this->search . '%');
                })
                ->latest()
                ->take(10)
                ->get();
        }

        return view('livewire.hr.evaluation-create', [
            'items' => $items
        ]);
    }
}

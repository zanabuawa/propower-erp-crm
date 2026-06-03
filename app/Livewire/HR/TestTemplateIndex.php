<?php

namespace App\Livewire\HR;

use App\Models\HrTestTemplate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Plantillas de Evaluaciones')]
class TestTemplateIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public function delete(int $id): void
    {
        $template = HrTestTemplate::findOrFail($id);
        $template->delete();
        session()->flash('success', 'Plantilla eliminada correctamente.');
    }

    public function render()
    {
        $templates = HrTestTemplate::withCount('questions')
            ->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
            ->latest()
            ->paginate(12);

        return view('livewire.hr.test-template-index', compact('templates'));
    }
}

<?php

namespace App\Livewire\Users;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class UserIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $confirmingDelete = false;
    public ?int $deleteId = null;

    public function updatingSearch(): void
    {
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
        $user = User::findOrFail($this->deleteId);

        if ($user->id === auth()->id()) {
            session()->flash('error', 'No puedes eliminar tu propio usuario.');
            $this->confirmingDelete = false;
            return;
        }

        $user->delete();
        $this->confirmingDelete = false;
        $this->deleteId = null;
        session()->flash('success', 'Usuario eliminado correctamente.');
    }

    public function render()
    {
        return view('livewire.users.user-index', [
            'users' => User::query()
                ->when($this->search, fn($q) => $q
                    ->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%"))
                ->with('company', 'branch')
                ->latest()
                ->paginate(15),
        ]);
    }
}
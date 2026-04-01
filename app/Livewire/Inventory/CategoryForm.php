<?php

namespace App\Livewire\Inventory;

use App\Models\Category;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Str;

#[Layout('layouts.app')]
class CategoryForm extends Component
{
    public ?Category $category = null;
    public string $name = '';
    public ?int $parent_id = null;
    public string $color = '#6366f1';
    public bool $is_active = true;

    public function mount($category = null): void
    {
        if ($category) {
            $this->category  = $category instanceof Category ? $category : Category::findOrFail($category);
            $this->name      = $this->category->name;
            $this->parent_id = $this->category->parent_id;
            $this->color     = $this->category->color;
            $this->is_active = $this->category->is_active;
        }
    }

    public function rules(): array
    {
        return [
            'name'      => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'color'     => 'required|string|max:7',
            'is_active' => 'boolean',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'company_id' => auth()->user()->company_id,
            'name'       => $this->name,
            'slug'       => Str::slug($this->name),
            'parent_id'  => $this->parent_id,
            'color'      => $this->color,
            'is_active'  => $this->is_active,
        ];

        if ($this->category?->exists) {
            $this->category->update($data);
            session()->flash('success', 'Categoría actualizada correctamente.');
        } else {
            Category::create($data);
            session()->flash('success', 'Categoría creada correctamente.');
        }

        $this->redirect(route('inventory.categories.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.inventory.category-form', [
            'parents' => Category::where('is_active', true)
                ->whereNull('parent_id')
                ->when($this->category?->exists, fn($q) => $q->where('id', '!=', $this->category->id))
                ->orderBy('name')
                ->get(),
        ]);
    }
}
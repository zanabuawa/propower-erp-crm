<?php

namespace App\Livewire\Tenders;

use App\Models\TenderCatalogCategory;
use App\Models\TenderCatalogItem;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Catálogo APU')]
class CatalogIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public ?int $category_id = null;
    public bool $showCategoryModal = false;
    public bool $editingCategory = false;
    public ?int $editingCategoryId = null;
    public string $catCode = '';
    public string $catName = '';
    public ?int $catParentId = null;

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedCategoryId(): void { $this->resetPage(); }

    public function openCategoryModal(bool $editing = false, ?int $id = null): void
    {
        $this->resetCategoryForm();
        $this->editingCategory = $editing;
        $this->editingCategoryId = $id;
        if ($editing && $id) {
            $cat = TenderCatalogCategory::findOrFail($id);
            $this->catCode     = $cat->code ?? '';
            $this->catName     = $cat->name;
            $this->catParentId = $cat->parent_id;
        }
        $this->showCategoryModal = true;
    }

    public function saveCategory(): void
    {
        $this->validate([
            'catName' => 'required|string|max:150',
            'catCode' => 'nullable|string|max:20',
        ]);

        $data = [
            'company_id' => auth()->user()->company_id,
            'code'       => $this->catCode ?: null,
            'name'       => $this->catName,
            'parent_id'  => $this->catParentId ?: null,
        ];

        if ($this->editingCategory && $this->editingCategoryId) {
            TenderCatalogCategory::findOrFail($this->editingCategoryId)->update($data);
        } else {
            TenderCatalogCategory::create($data);
        }

        $this->showCategoryModal = false;
        $this->resetCategoryForm();
        session()->flash('success', 'Categoría guardada.');
    }

    public function deleteCategory(int $id): void
    {
        $cat = TenderCatalogCategory::findOrFail($id);
        if ($cat->children()->exists() || $cat->items()->exists()) {
            session()->flash('error', 'No se puede eliminar: tiene sub-categorías o conceptos.');
            return;
        }
        $cat->delete();
        session()->flash('success', 'Categoría eliminada.');
    }

    public function deleteItem(int $id): void
    {
        TenderCatalogItem::findOrFail($id)->delete();
        session()->flash('success', 'Concepto eliminado.');
    }

    private function resetCategoryForm(): void
    {
        $this->catCode = '';
        $this->catName = '';
        $this->catParentId = null;
        $this->editingCategoryId = null;
    }

    public function render()
    {
        $companyId  = auth()->user()->company_id;
        $categories = TenderCatalogCategory::where('company_id', $companyId)
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $items = TenderCatalogItem::where('company_id', $companyId)
            ->when($this->search, fn($q) => $q->where(fn($q2) =>
                $q2->where('name', 'like', '%' . $this->search . '%')
                   ->orWhere('code', 'like', '%' . $this->search . '%')
            ))
            ->when($this->category_id, fn($q) => $q->where('category_id', $this->category_id))
            ->with('category')
            ->orderBy('code')
            ->orderBy('name')
            ->paginate(20);

        $allCategories = TenderCatalogCategory::where('company_id', $companyId)
            ->orderBy('name')->get();

        return view('livewire.tenders.catalog-index', compact('categories', 'items', 'allCategories'));
    }
}

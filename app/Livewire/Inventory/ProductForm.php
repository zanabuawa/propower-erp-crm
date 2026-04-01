<?php

namespace App\Livewire\Inventory;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Supplier;
use App\Models\UnitOfMeasure;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class ProductForm extends Component
{
    use WithFileUploads;

    public ?Product $product = null;

    // ── Campos principales ───────────────────────────────────────────────────
    public string  $type               = 'product';
    public string  $name               = '';
    public ?int    $category_id        = null;
    public ?int    $subcategory_id     = null;
    public ?int    $unit_of_measure_id = null;
    public ?int    $supplier_id        = null;
    public string  $sku                = '';
    public string  $barcode            = '';
    public string  $description        = '';
    public string  $brand              = '';
    public string  $model              = '';
    public string  $color              = '';
    public string  $purchase_price     = '0';
    public string  $profit_margin      = '0';
    public string  $min_stock          = '0';
    public string  $max_stock          = '0';
    public bool    $is_active          = true;
    public $images = [];
    public array   $existingImages     = [];

    // ── Modal: nueva categoría ───────────────────────────────────────────────
    public bool   $showCategoryModal  = false;
    public string $newCategoryName    = '';
    public string $newCategoryColor   = '#6366f1';

    // ── Modal: nueva subcategoría ────────────────────────────────────────────
    public bool   $showSubcategoryModal  = false;
    public string $newSubcategoryName    = '';

    // ── Modal: nueva unidad ──────────────────────────────────────────────────
    public bool   $showUnitModal   = false;
    public string $newUnitName     = '';
    public string $newUnitAbbr     = '';

    // ── Modal: nuevo proveedor ───────────────────────────────────────────────
    public bool   $showSupplierModal = false;
    public string $newSupplierName   = '';
    public string $newSupplierPhone  = '';
    public string $newSupplierEmail  = '';

    // ── Computed ─────────────────────────────────────────────────────────────
    public function getNormalSalePriceProperty(): float
    {
        $p = (float) $this->purchase_price;
        $m = (float) $this->profit_margin;
        return round($p * (1 + $m / 100), 2);
    }

    // ── Mount ────────────────────────────────────────────────────────────────
    public function mount($product = null): void
    {
        $companyId = auth()->user()->company_id;

        if ($product) {
            $this->product            = $product instanceof Product ? $product : Product::findOrFail($product);
            $this->type               = $this->product->type ?? 'product';
            $this->name               = $this->product->name;
            $this->category_id        = $this->product->category_id;
            $this->subcategory_id     = $this->product->subcategory_id;
            $this->unit_of_measure_id = $this->product->unit_of_measure_id;
            $this->supplier_id        = $this->product->supplier_id;
            $this->sku                = $this->product->sku ?? '';
            $this->barcode            = $this->product->barcode ?? '';
            $this->description        = $this->product->description ?? '';
            $this->brand              = $this->product->brand ?? '';
            $this->model              = $this->product->model ?? '';
            $this->color              = $this->product->color ?? '';
            $this->purchase_price     = $this->product->purchase_price;
            $this->profit_margin      = $this->product->profit_margin ?? '0';
            $this->min_stock          = $this->product->min_stock;
            $this->max_stock          = $this->product->max_stock;
            $this->is_active          = $this->product->is_active;
            $this->existingImages     = $this->product->images->toArray();
        } else {
            if ($companyId) {
                $this->barcode = Product::generateBarcode($companyId);
            }
        }
    }

    // ── Watchers ─────────────────────────────────────────────────────────────
    public function updatedCategoryId(): void
    {
        $this->subcategory_id = null;
    }

    public function updatedName(): void
    {
        $companyId = auth()->user()->company_id;
        if (! $this->product?->exists && $companyId && strlen($this->name) >= 3) {
            $this->sku = Product::generateSku($this->name, $companyId);
        }
    }

    // ── Regeneradores ────────────────────────────────────────────────────────
    public function regenerateSku(): void
    {
        $companyId = auth()->user()->company_id;
        if ($companyId && strlen($this->name) >= 1) {
            $this->sku = Product::generateSku($this->name, $companyId);
        }
    }

    public function regenerateBarcode(): void
    {
        $companyId = auth()->user()->company_id;
        if ($companyId) {
            $this->barcode = Product::generateBarcode($companyId);
        }
    }

    // ── Imágenes ─────────────────────────────────────────────────────────────
    public function removeExistingImage(int $imageId): void
    {
        ProductImage::findOrFail($imageId)->delete();
        $this->existingImages = array_filter(
            $this->existingImages,
            fn($img) => $img['id'] !== $imageId
        );
    }

    public function setPrimaryImage(int $imageId): void
    {
        ProductImage::where('product_id', $this->product->id)->update(['is_primary' => false]);
        ProductImage::findOrFail($imageId)->update(['is_primary' => true]);
        $this->existingImages = Product::findOrFail($this->product->id)->images->toArray();
    }

    // ── Modales: crear al vuelo ───────────────────────────────────────────────

    public function saveCategory(): void
    {
        $this->validate(['newCategoryName' => 'required|string|max:255']);

        $cat = Category::create([
            'company_id' => auth()->user()->company_id,
            'name'       => $this->newCategoryName,
            'slug'       => Str::slug($this->newCategoryName),
            'color'      => $this->newCategoryColor,
            'is_active'  => true,
        ]);

        $this->category_id       = $cat->id;
        $this->subcategory_id    = null;
        $this->showCategoryModal = false;
        $this->reset(['newCategoryName', 'newCategoryColor']);
        $this->newCategoryColor = '#6366f1';
    }

    public function saveSubcategory(): void
    {
        $this->validate([
            'newSubcategoryName' => 'required|string|max:255',
            'category_id'        => 'required|exists:categories,id',
        ]);

        $sub = Category::create([
            'company_id' => auth()->user()->company_id,
            'name'       => $this->newSubcategoryName,
            'slug'       => Str::slug($this->newSubcategoryName),
            'parent_id'  => $this->category_id,
            'color'      => '#6366f1',
            'is_active'  => true,
        ]);

        $this->subcategory_id       = $sub->id;
        $this->showSubcategoryModal = false;
        $this->reset('newSubcategoryName');
    }

    public function saveUnit(): void
    {
        $this->validate([
            'newUnitName' => 'required|string|max:255',
            'newUnitAbbr' => 'required|string|max:10',
        ]);

        $unit = UnitOfMeasure::create([
            'company_id'   => auth()->user()->company_id,
            'name'         => $this->newUnitName,
            'abbreviation' => $this->newUnitAbbr,
            'is_active'    => true,
        ]);

        $this->unit_of_measure_id = $unit->id;
        $this->showUnitModal      = false;
        $this->reset(['newUnitName', 'newUnitAbbr']);
    }

    public function saveSupplier(): void
    {
        $this->validate(['newSupplierName' => 'required|string|max:255']);

        $supplier = Supplier::create([
            'company_id' => auth()->user()->company_id,
            'name'       => $this->newSupplierName,
            'type'       => 'company',
            'status'     => 'active',
            'credit_limit'   => 0,
            'payment_terms'  => 0,
        ]);

        if ($this->newSupplierPhone) {
            $supplier->phones()->create(['number' => $this->newSupplierPhone]);
        }
        if ($this->newSupplierEmail) {
            $supplier->emails()->create(['email' => $this->newSupplierEmail]);
        }

        $this->supplier_id        = $supplier->id;
        $this->showSupplierModal  = false;
        $this->reset(['newSupplierName', 'newSupplierPhone', 'newSupplierEmail']);
    }

    // ── Validación y guardado ────────────────────────────────────────────────
    public function rules(): array
    {
        return [
            'type'               => 'required|in:product,service',
            'name'               => 'required|string|max:255',
            'category_id'        => 'nullable|exists:categories,id',
            'subcategory_id'     => 'nullable|exists:categories,id',
            'unit_of_measure_id' => 'nullable|exists:unit_of_measures,id',
            'supplier_id'        => 'nullable|exists:suppliers,id',
            'sku'                => 'nullable|string|max:100',
            'barcode'            => 'nullable|string|max:100',
            'description'        => 'nullable|string',
            'brand'              => 'nullable|string|max:100',
            'model'              => 'nullable|string|max:100',
            'color'              => 'nullable|string|max:60',
            'purchase_price'     => 'required|numeric|min:0',
            'profit_margin'      => 'required|numeric|min:0|max:999',
            'min_stock'          => 'required|numeric|min:0',
            'max_stock'          => 'required|numeric|min:0',
            'is_active'          => 'boolean',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $purchasePrice = (float) $this->purchase_price;
        $profitMargin  = (float) $this->profit_margin;
        $salePrice     = round($purchasePrice * (1 + $profitMargin / 100), 2);

        $data = [
            'company_id'         => auth()->user()->company_id,
            'type'               => $this->type,
            'name'               => $this->name,
            'category_id'        => $this->category_id,
            'subcategory_id'     => $this->subcategory_id,
            'unit_of_measure_id' => $this->unit_of_measure_id,
            'supplier_id'        => $this->supplier_id,
            'sku'                => $this->sku ?: null,
            'barcode'            => $this->type === 'product' ? ($this->barcode ?: null) : null,
            'description'        => $this->description,
            'brand'              => $this->brand ?: null,
            'model'              => $this->model ?: null,
            'color'              => $this->color ?: null,
            'purchase_price'     => $this->purchase_price,
            'profit_margin'      => $this->profit_margin,
            'sale_price'         => $salePrice,
            'min_stock'          => $this->type === 'product' ? $this->min_stock : 0,
            'max_stock'          => $this->type === 'product' ? $this->max_stock : 0,
            'is_active'          => $this->is_active,
        ];

        if ($this->product?->exists) {
            $this->product->update($data);
            $product = $this->product;
            session()->flash('success', 'Guardado correctamente.');
        } else {
            $product = Product::create($data);
            session()->flash('success', ucfirst($this->type === 'service' ? 'Servicio' : 'Producto') . ' creado correctamente.');
        }

        if ($this->type === 'product' && is_array($this->images) && count($this->images) > 0) {
            $isFirst = $product->images()->count() === 0;
            foreach ($this->images as $index => $image) {
                if (is_object($image)) {
                    $path = $image->store('products', 'public');
                    $product->images()->create([
                        'path'       => $path,
                        'is_primary' => $isFirst && $index === 0,
                        'sort_order' => $product->images()->count(),
                    ]);
                }
            }
        }

        $this->redirect(route('inventory.index'));
    }

    public function render()
    {
        return view('livewire.inventory.product-form', [
            'categories'    => Category::where('is_active', true)
                ->whereNull('parent_id')
                ->orderBy('name')->get(),
            'subcategories' => $this->category_id
                ? Category::where('is_active', true)
                    ->where('parent_id', $this->category_id)
                    ->orderBy('name')->get()
                : collect(),
            'units'      => UnitOfMeasure::where('is_active', true)->orderBy('name')->get(),
            'suppliers'  => Supplier::where('company_id', auth()->user()->company_id)
                ->where('status', 'active')->orderBy('name')->get(),
        ]);
    }
}

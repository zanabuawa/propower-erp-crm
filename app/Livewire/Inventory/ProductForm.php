<?php

namespace App\Livewire\Inventory;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Supplier;
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
    public ?int    $supplier_id        = null;
    public string  $sku                = '';
    public string  $sat_product_code   = '';
    public string  $sat_unit_code      = '';
    public string  $barcode            = '';
    public string  $description        = '';
    public string  $brand              = '';
    public string  $model              = '';
    public string  $color              = '';
    public string  $purchase_price             = '0';
    public bool    $purchase_price_includes_iva = false;
    public string  $profit_margin               = '30';
    public string  $operational_costs           = '10';
    public string  $min_stock          = '0';
    public string  $max_stock          = '0';
    public bool    $is_active          = true;
    public $images        = [];   // productos: múltiples archivos
    public $serviceImage  = null; // servicios: un solo archivo
    public array $existingImages = [];

    // ── Modal: nueva categoría ───────────────────────────────────────────────
    public bool   $showCategoryModal  = false;
    public string $newCategoryName    = '';
    public string $newCategoryColor   = '#6366f1';

    // ── Modal: nueva subcategoría ────────────────────────────────────────────
    public bool   $showSubcategoryModal  = false;
    public string $newSubcategoryName    = '';

    // ── Modal: nuevo proveedor ───────────────────────────────────────────────
    public bool   $showSupplierModal = false;
    public string $newSupplierName   = '';
    public string $newSupplierPhone  = '';
    public string $newSupplierEmail  = '';

    // ── Hooks ────────────────────────────────────────────────────────────────
    public function updatedProfitMargin(): void
    {
        $margin = (float) $this->profit_margin;
        $opCost = (float) $this->operational_costs;
        if ($margin < 10) {
            $this->profit_margin = '10';
        } elseif ($margin <= $opCost) {
            $this->profit_margin = (string) ($opCost + 1);
        }
    }

    public function updatedOperationalCosts(): void
    {
        $opCost = (float) $this->operational_costs;
        $margin = (float) $this->profit_margin;
        if ($opCost < 0) {
            $this->operational_costs = '0';
        } elseif ($opCost >= $margin) {
            $this->operational_costs = (string) max(0, $margin - 1);
        }
    }

    // ── Computed ─────────────────────────────────────────────────────────────
    public function getNormalSalePriceProperty(): float
    {
        $p       = (float) $this->purchase_price;
        $m       = (float) $this->profit_margin;
        $divisor = 1 - $m / 100;
        return $divisor > 0 ? round($p / $divisor, 2) : 0.0;
    }

    public function getMinSalePriceProperty(): float
    {
        $p       = (float) $this->purchase_price;
        $op      = (float) $this->operational_costs;
        $divisor = 1 - $op / 100;
        return $divisor > 0 ? round($p / $divisor, 2) : 0.0;
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
            $this->supplier_id        = $this->product->supplier_id;
            $this->sku                = $this->product->sku ?? '';
            $this->sat_product_code   = $this->product->sat_product_code ?? '';
            $this->sat_unit_code      = $this->product->sat_unit_code ?? '';
            $this->barcode            = $this->product->barcode ?? '';
            $this->description        = $this->product->description ?? '';
            $this->brand              = $this->product->brand ?? '';
            $this->model              = $this->product->model ?? '';
            $this->color              = $this->product->color ?? '';
            $this->purchase_price             = $this->product->purchase_price;
            $this->purchase_price_includes_iva = (bool) $this->product->purchase_price_includes_iva;
            $this->profit_margin               = max(10, (float) ($this->product->profit_margin ?? 30));
            $this->operational_costs           = max(0, (float) ($this->product->operational_costs ?? 10));
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
            'supplier_id'        => 'nullable|exists:suppliers,id',
            'sku'                => 'nullable|string|max:100',
            'sat_product_code'   => 'nullable|string|max:20',
            'sat_unit_code'      => 'nullable|string|max:10',
            'barcode'            => 'nullable|string|max:100',
            'description'        => 'nullable|string',
            'brand'              => 'nullable|string|max:100',
            'model'              => 'nullable|string|max:100',
            'color'              => 'nullable|string|max:60',
            'purchase_price'             => 'required|numeric|min:0',
            'purchase_price_includes_iva' => 'boolean',
            'profit_margin'      => 'required|numeric|min:10|max:999|gt:operational_costs',
            'operational_costs'  => 'required|numeric|min:0|max:99',
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
        $marginDiv     = 1 - $profitMargin / 100;
        $salePrice     = $marginDiv > 0 ? round($purchasePrice / $marginDiv, 2) : 0;

        $data = [
            'company_id'         => auth()->user()->company_id,
            'type'               => $this->type,
            'name'               => $this->name,
            'category_id'        => $this->category_id,
            'subcategory_id'     => $this->subcategory_id,
            'supplier_id'        => $this->supplier_id,
            'sku'                => $this->sku ?: null,
            'sat_product_code'   => $this->sat_product_code ?: null,
            'sat_unit_code'      => $this->sat_unit_code ?: null,
            'barcode'            => $this->type === 'product' ? ($this->barcode ?: null) : null,
            'description'        => $this->description,
            'brand'              => $this->brand ?: null,
            'model'              => $this->model ?: null,
            'color'              => $this->color ?: null,
            'purchase_price'             => $this->purchase_price,
            'purchase_price_includes_iva' => $this->purchase_price_includes_iva,
            'profit_margin'              => $this->profit_margin,
            'operational_costs'          => $this->operational_costs,
            'sale_price'                 => $salePrice,
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

        if ($this->type === 'service' && is_object($this->serviceImage)) {
            // Reemplazar imagen única del servicio
            $product->images()->delete();
            $product->images()->create([
                'path'       => $this->serviceImage->store('services', 'public'),
                'is_primary' => true,
                'sort_order' => 0,
            ]);
        } elseif ($this->type === 'product' && is_array($this->images) && count($this->images) > 0) {
            $isFirst = $product->images()->count() === 0;
            foreach ($this->images as $index => $image) {
                if (is_object($image)) {
                    $product->images()->create([
                        'path'       => $image->store('products', 'public'),
                        'is_primary' => $isFirst && $index === 0,
                        'sort_order' => $product->images()->count(),
                    ]);
                }
            }
        }

        $this->redirect(route('inventory.index'), navigate: true);
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
            'suppliers'  => Supplier::where('company_id', auth()->user()->company_id)
                ->where('status', 'active')->orderBy('name')->get(),
        ]);
    }
}

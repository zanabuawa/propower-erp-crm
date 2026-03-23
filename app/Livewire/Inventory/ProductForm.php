<?php

namespace App\Livewire\Inventory;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\UnitOfMeasure;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class ProductForm extends Component
{
    use WithFileUploads;

    public ?Product $product = null;
    public string $name = '';
    public ?int $category_id = null;
    public ?int $unit_of_measure_id = null;
    public string $sku = '';
    public string $barcode = '';
    public string $description = '';
    public string $purchase_price = '0';
    public string $sale_price = '0';
    public string $min_stock = '0';
    public string $max_stock = '0';
    public bool $is_active = true;
    public $images = [];
    public array $existingImages = [];

    public function mount($product = null): void
    {
        if ($product) {
            $this->product            = $product instanceof Product ? $product : Product::findOrFail($product);
            $this->name               = $this->product->name;
            $this->category_id        = $this->product->category_id;
            $this->unit_of_measure_id = $this->product->unit_of_measure_id;
            $this->sku                = $this->product->sku ?? '';
            $this->barcode            = $this->product->barcode ?? '';
            $this->description        = $this->product->description ?? '';
            $this->purchase_price     = $this->product->purchase_price;
            $this->sale_price         = $this->product->sale_price;
            $this->min_stock          = $this->product->min_stock;
            $this->max_stock          = $this->product->max_stock;
            $this->is_active          = $this->product->is_active;
            $this->existingImages     = $this->product->images->toArray();
        }
    }

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

    public function rules(): array
    {
        return [
            'name'               => 'required|string|max:255',
            'category_id'        => 'nullable|exists:categories,id',
            'unit_of_measure_id' => 'nullable|exists:unit_of_measures,id',
            'sku'                => 'nullable|string|max:100',
            'barcode'            => 'nullable|string|max:100',
            'description'        => 'nullable|string',
            'purchase_price'     => 'required|numeric|min:0',
            'sale_price'         => 'required|numeric|min:0',
            'min_stock'          => 'required|numeric|min:0',
            'max_stock'          => 'required|numeric|min:0',
            'is_active'          => 'boolean',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'company_id'         => auth()->user()->company_id,
            'name'               => $this->name,
            'category_id'        => $this->category_id,
            'unit_of_measure_id' => $this->unit_of_measure_id,
            'sku'                => $this->sku ?: null,
            'barcode'            => $this->barcode ?: null,
            'description'        => $this->description,
            'purchase_price'     => $this->purchase_price,
            'sale_price'         => $this->sale_price,
            'min_stock'          => $this->min_stock,
            'max_stock'          => $this->max_stock,
            'is_active'          => $this->is_active,
        ];

        if ($this->product?->exists) {
            $this->product->update($data);
            $product = $this->product;
            session()->flash('success', 'Producto actualizado correctamente.');
        } else {
            $product = Product::create($data);
            session()->flash('success', 'Producto creado correctamente.');
        }

        if (is_array($this->images) && count($this->images) > 0) {
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
            'categories' => Category::where('is_active', true)->orderBy('name')->get(),
            'units'      => UnitOfMeasure::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
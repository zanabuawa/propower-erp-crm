<?php

namespace App\Livewire\Tenders;

use App\Models\TenderCatalogCategory;
use App\Models\TenderCatalogItem;
use App\Models\TenderCatalogResource;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Concepto APU')]
class CatalogItemForm extends Component
{
    public ?TenderCatalogItem $item = null;

    public ?int $category_id = null;
    public string $code = '';
    public string $name = '';
    public string $unit = '';
    public string $description = '';
    public float $indirect_pct = 0;
    public float $overhead_pct = 0;
    public float $utility_pct = 0;

    public array $resources = [];

    public function mount(?TenderCatalogItem $item = null): void
    {
        if ($item && $item->exists) {
            $this->item         = $item;
            $this->category_id  = $item->category_id;
            $this->code         = $item->code ?? '';
            $this->name         = $item->name;
            $this->unit         = $item->unit ?? '';
            $this->description  = $item->description ?? '';
            $this->indirect_pct = $item->indirect_pct;
            $this->overhead_pct = $item->overhead_pct;
            $this->utility_pct  = $item->utility_pct;
            $this->resources    = $item->resources->map(fn($r) => [
                'id'          => $r->id,
                'type'        => $r->type,
                'description' => $r->description,
                'unit'        => $r->unit ?? '',
                'quantity'    => $r->quantity,
                'unit_cost'   => $r->unit_cost,
            ])->toArray();
        }
        if (empty($this->resources)) {
            $this->addResource();
        }
    }

    public function addResource(): void
    {
        $this->resources[] = [
            'id'          => null,
            'type'        => 'material',
            'description' => '',
            'unit'        => '',
            'quantity'    => 1,
            'unit_cost'   => 0,
        ];
    }

    public function removeResource(int $index): void
    {
        array_splice($this->resources, $index, 1);
    }

    public function getDirectCostProperty(): float
    {
        return array_sum(array_map(fn($r) => (float)$r['quantity'] * (float)$r['unit_cost'], $this->resources));
    }

    public function getUnitPriceProperty(): float
    {
        $factor = 1 + ($this->indirect_pct + $this->overhead_pct + $this->utility_pct) / 100;
        return round($this->directCost * $factor, 2);
    }

    public function save(): void
    {
        $this->validate([
            'category_id'  => 'required|exists:tender_catalog_categories,id',
            'name'         => 'required|string|max:200',
            'unit'         => 'nullable|string|max:20',
            'indirect_pct' => 'numeric|min:0|max:100',
            'overhead_pct' => 'numeric|min:0|max:100',
            'utility_pct'  => 'numeric|min:0|max:100',
            'resources'            => 'array|min:1',
            'resources.*.type'        => 'required|in:material,labor,equipment',
            'resources.*.description' => 'required|string|max:200',
            'resources.*.quantity'    => 'required|numeric|min:0',
            'resources.*.unit_cost'   => 'required|numeric|min:0',
        ]);

        $data = [
            'company_id'   => auth()->user()->company_id,
            'category_id'  => $this->category_id,
            'code'         => $this->code ?: null,
            'name'         => $this->name,
            'unit'         => $this->unit ?: null,
            'description'  => $this->description ?: null,
            'indirect_pct' => $this->indirect_pct,
            'overhead_pct' => $this->overhead_pct,
            'utility_pct'  => $this->utility_pct,
        ];

        if ($this->item && $this->item->exists) {
            $this->item->update($data);
            $item = $this->item;
        } else {
            $item = TenderCatalogItem::create($data);
        }

        // Sync resources
        $item->resources()->delete();
        foreach ($this->resources as $r) {
            if (empty($r['description'])) continue;
            TenderCatalogResource::create([
                'item_id'     => $item->id,
                'type'        => $r['type'],
                'description' => $r['description'],
                'unit'        => $r['unit'] ?: null,
                'quantity'    => $r['quantity'],
                'unit_cost'   => $r['unit_cost'],
            ]);
        }

        session()->flash('success', 'Concepto guardado correctamente.');
        $this->redirect(route('tenders.catalog.index'), navigate: true);
    }

    public function render()
    {
        $categories = TenderCatalogCategory::where('company_id', auth()->user()->company_id)
            ->orderBy('name')->get();

        return view('livewire.tenders.catalog-item-form', compact('categories'));
    }
}

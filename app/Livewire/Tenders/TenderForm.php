<?php

namespace App\Livewire\Tenders;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Project;
use App\Models\Tender;
use App\Models\TenderItem;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Licitación')]
class TenderForm extends Component
{
    public ?Tender $tender = null;

    public string $name = '';
    public string $description = '';
    public string $type = 'obra_privada';
    public string $status = 'borrador';
    public ?int $customer_id = null;
    public ?int $project_id = null;
    public ?int $branch_id = null;
    public ?int $responsible_user_id = null;
    public string $submission_date = '';
    public string $opening_date = '';
    public string $award_date = '';
    public string $estimated_budget = '';
    public string $awarded_amount = '';
    public string $feedback = '';
    public string $notes = '';

    public array $items = [];

    public function mount(?Tender $tender = null): void
    {
        if ($tender && $tender->exists) {
            $this->tender               = $tender;
            $this->name                 = $tender->name;
            $this->description          = $tender->description ?? '';
            $this->type                 = $tender->type;
            $this->status               = $tender->status;
            $this->customer_id          = $tender->customer_id;
            $this->project_id           = $tender->project_id;
            $this->branch_id            = $tender->branch_id;
            $this->responsible_user_id  = $tender->responsible_user_id;
            $this->submission_date      = $tender->submission_date?->format('Y-m-d') ?? '';
            $this->opening_date         = $tender->opening_date?->format('Y-m-d') ?? '';
            $this->award_date           = $tender->award_date?->format('Y-m-d') ?? '';
            $this->estimated_budget     = (string) ($tender->estimated_budget ?? '');
            $this->awarded_amount       = (string) ($tender->awarded_amount ?? '');
            $this->feedback             = $tender->feedback ?? '';
            $this->notes                = $tender->notes ?? '';
            $this->items = $tender->items->map(fn($i) => [
                'id'          => $i->id,
                'product_id'  => $i->product_id,
                'code'        => $i->code ?? '',
                'category'    => $i->category ?? '',
                'description' => $i->description,
                'unit'        => $i->unit ?? '',
                'quantity'    => $i->quantity,
                'unit_price'  => $i->unit_price,
                'total'       => $i->total,
            ])->toArray();
        }
        if (empty($this->items)) {
            $this->addItem();
        }
    }

    public function addItem(): void
    {
        $this->items[] = [
            'id'          => null,
            'product_id'  => null,
            'code'        => '',
            'category'    => '',
            'description' => '',
            'unit'        => '',
            'quantity'    => 1,
            'unit_price'  => 0,
            'total'       => 0,
        ];
    }

    public function removeItem(int $index): void
    {
        array_splice($this->items, $index, 1);
    }

    public function loadProduct(int $index, ?int $productId): void
    {
        if (!$productId) {
            $this->items[$index]['product_id'] = null;
            return;
        }
        $p = Product::with('unitOfMeasure', 'category')->find($productId);
        if (!$p) return;

        $this->items[$index]['product_id']  = $p->id;
        $this->items[$index]['code']        = $p->sku ?? '';
        $this->items[$index]['category']    = $p->category?->name ?? '';
        $this->items[$index]['description'] = $p->name;
        $this->items[$index]['unit']        = $p->unitOfMeasure?->abbreviation ?? '';
        $this->items[$index]['unit_price']  = (float) $p->sale_price;
        $this->recalcItem($index);
    }

    public function recalcItem(int $index): void
    {
        $qty   = (float) ($this->items[$index]['quantity'] ?? 0);
        $price = (float) ($this->items[$index]['unit_price'] ?? 0);
        $this->items[$index]['total'] = round($qty * $price, 2);
    }

    public function getTotalProperty(): float
    {
        return array_sum(array_column($this->items, 'total'));
    }

    public function save(): void
    {
        $this->validate([
            'name'             => 'required|string|max:200',
            'type'             => 'required|in:' . implode(',', array_keys(Tender::TYPES)),
            'status'           => 'required|in:' . implode(',', array_keys(Tender::STATUSES)),
            'submission_date'  => 'nullable|date',
            'opening_date'     => 'nullable|date',
            'award_date'       => 'nullable|date',
            'estimated_budget' => 'nullable|numeric|min:0',
            'awarded_amount'   => 'nullable|numeric|min:0',
            'items'            => 'array',
            'items.*.description' => 'required|string',
            'items.*.quantity'    => 'required|numeric|min:0',
            'items.*.unit_price'  => 'required|numeric|min:0',
        ]);

        $data = [
            'company_id'          => auth()->user()->company_id,
            'name'                => $this->name,
            'description'         => $this->description ?: null,
            'type'                => $this->type,
            'status'              => $this->status,
            'customer_id'         => $this->customer_id,
            'project_id'          => $this->project_id,
            'branch_id'           => $this->branch_id,
            'responsible_user_id' => $this->responsible_user_id,
            'submission_date'     => $this->submission_date ?: null,
            'opening_date'        => $this->opening_date ?: null,
            'award_date'          => $this->award_date ?: null,
            'estimated_budget'    => $this->estimated_budget ?: null,
            'awarded_amount'      => $this->awarded_amount ?: null,
            'feedback'            => $this->feedback ?: null,
            'notes'               => $this->notes ?: null,
        ];

        if ($this->tender && $this->tender->exists) {
            $this->tender->update($data);
            $tender = $this->tender;
        } else {
            $tender = Tender::create($data);
        }

        // Sync items
        $tender->items()->delete();
        foreach ($this->items as $i => $row) {
            if (empty($row['description'])) continue;
            TenderItem::create([
                'tender_id'   => $tender->id,
                'product_id'  => $row['product_id'] ?: null,
                'code'        => $row['code'] ?: null,
                'category'    => $row['category'] ?: null,
                'description' => $row['description'],
                'unit'        => $row['unit'] ?: null,
                'quantity'    => $row['quantity'],
                'unit_price'  => $row['unit_price'],
                'total'       => $row['total'],
                'sort_order'  => $i,
            ]);
        }

        session()->flash('success', 'Licitación guardada correctamente.');
        $this->redirect(route('tenders.show', $tender), navigate: true);
    }

    public function render()
    {
        $companyId = auth()->user()->company_id;

        return view('livewire.tenders.tender-form', [
            'customers' => Customer::where('company_id', $companyId)->orderBy('name')->get(),
            'projects'  => Project::whereHas('branch', fn($q) => $q->where('company_id', $companyId))->orderBy('name')->get(),
            'branches'  => Branch::where('company_id', $companyId)->orderBy('name')->get(),
            'users'     => User::where('company_id', $companyId)->orderBy('name')->get(),
            'products'  => Product::where('company_id', $companyId)->where('is_active', true)
                            ->with('unitOfMeasure:id,abbreviation', 'category:id,name')
                            ->orderBy('name')->get(['id','name','sku','sale_price','unit_of_measure_id','category_id','type']),
            'types'     => Tender::TYPES,
            'statuses'  => Tender::STATUSES,
        ]);
    }
}

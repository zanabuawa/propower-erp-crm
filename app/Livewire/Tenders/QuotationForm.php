<?php

namespace App\Livewire\Tenders;

use App\Models\Company;
use App\Models\Tender;
use App\Models\TenderQuotation;
use App\Models\TenderQuotationItem;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Cotización')]
class QuotationForm extends Component
{
    public Tender $tender;
    public ?TenderQuotation $quotation = null;

    public ?int $issuing_company_id = null;
    public string $status = 'borrador';
    public string $valid_until = '';
    public string $notes = '';
    public array $items = [];

    public function mount(Tender $tender, ?TenderQuotation $quotation = null): void
    {
        $this->tender = $tender;

        if ($quotation && $quotation->exists) {
            $this->quotation          = $quotation;
            $this->issuing_company_id = $quotation->issuing_company_id;
            $this->status             = $quotation->status;
            $this->valid_until        = $quotation->valid_until?->format('Y-m-d') ?? '';
            $this->notes              = $quotation->notes ?? '';
            $this->items = $quotation->items->map(fn($i) => [
                'tender_item_id' => $i->tender_item_id,
                'description'    => $i->description,
                'unit'           => $i->unit ?? '',
                'quantity'       => $i->quantity,
                'unit_price'     => $i->unit_price,
                'total'          => $i->total,
            ])->toArray();
        } else {
            // Pre-populate from tender items
            $this->items = $tender->items->map(fn($i) => [
                'tender_item_id' => $i->id,
                'description'    => $i->description,
                'unit'           => $i->unit ?? '',
                'quantity'       => $i->quantity,
                'unit_price'     => $i->unit_price,
                'total'          => $i->total,
            ])->toArray();
        }

        if (empty($this->items)) {
            $this->addItem();
        }
    }

    public function addItem(): void
    {
        $this->items[] = [
            'tender_item_id' => null,
            'description'    => '',
            'unit'           => '',
            'quantity'       => 1,
            'unit_price'     => 0,
            'total'          => 0,
        ];
    }

    public function removeItem(int $index): void
    {
        array_splice($this->items, $index, 1);
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
            'issuing_company_id'      => 'required|exists:companies,id',
            'valid_until'             => 'nullable|date',
            'items'                   => 'array|min:1',
            'items.*.description'     => 'required|string',
            'items.*.quantity'        => 'required|numeric|min:0',
            'items.*.unit_price'      => 'required|numeric|min:0',
        ]);

        $data = [
            'tender_id'          => $this->tender->id,
            'issuing_company_id' => $this->issuing_company_id,
            'status'             => $this->status,
            'valid_until'        => $this->valid_until ?: null,
            'notes'              => $this->notes ?: null,
            'created_by'         => auth()->id(),
        ];

        if ($this->quotation && $this->quotation->exists) {
            $this->quotation->update($data);
            $quotation = $this->quotation;
        } else {
            $quotation = TenderQuotation::create($data);
        }

        $quotation->items()->delete();
        foreach ($this->items as $i => $row) {
            if (empty($row['description'])) continue;
            TenderQuotationItem::create([
                'quotation_id'   => $quotation->id,
                'tender_item_id' => $row['tender_item_id'] ?: null,
                'description'    => $row['description'],
                'unit'           => $row['unit'] ?: null,
                'quantity'       => $row['quantity'],
                'unit_price'     => $row['unit_price'],
                'total'          => $row['total'],
                'sort_order'     => $i,
            ]);
        }

        session()->flash('success', 'Cotización guardada.');
        $this->redirect(route('tenders.show', $this->tender), navigate: true);
    }

    public function render()
    {
        return view('livewire.tenders.quotation-form', [
            'companies' => Company::where('is_active', true)->orderBy('name')->get(),
            'statuses'  => TenderQuotation::STATUSES,
        ]);
    }
}

<?php

namespace App\Livewire\Finance;

use App\Models\Branch;
use App\Models\FinanceAccount;
use App\Models\HrEmployee;
use App\Models\Project;
use App\Models\TravelExpense;
use App\Models\TravelExpenseItem;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class TravelExpenseForm extends Component
{
    public ?TravelExpense $travel = null;

    public ?int    $employee_id     = null;
    public ?int    $project_id      = null;
    public ?int    $branch_id       = null;
    public string  $destination     = '';
    public string  $purpose         = '';
    public string  $departure_date  = '';
    public string  $return_date     = '';
    public string  $amount_approved = '';
    public string  $currency        = 'MXN';
    public string  $notes           = '';

    public array $items = [];

    public function mount(?TravelExpense $travel = null): void
    {
        $this->departure_date = now()->format('Y-m-d');
        $this->return_date    = now()->format('Y-m-d');

        if ($travel && $travel->exists) {
            $this->travel           = $travel;
            $this->employee_id      = $travel->employee_id;
            $this->project_id       = $travel->project_id;
            $this->branch_id        = $travel->branch_id;
            $this->destination      = $travel->destination;
            $this->purpose          = $travel->purpose;
            $this->departure_date   = $travel->departure_date->format('Y-m-d');
            $this->return_date      = $travel->return_date->format('Y-m-d');
            $this->amount_approved  = (string) $travel->amount_approved;
            $this->currency         = $travel->currency;
            $this->notes            = $travel->notes ?? '';

            $this->items = $travel->items->map(fn($i) => [
                'id'             => $i->id,
                'category'       => $i->category,
                'concept'        => $i->concept,
                'amount'         => (string) $i->amount,
                'receipt_number' => $i->receipt_number ?? '',
                'notes'          => $i->notes ?? '',
            ])->toArray();
        }

        if (empty($this->items)) {
            $this->items[] = $this->blankItem();
        }
    }

    private function blankItem(): array
    {
        return [
            'id'             => null,
            'category'       => 'transporte',
            'concept'        => '',
            'amount'         => '',
            'receipt_number' => '',
            'notes'          => '',
        ];
    }

    public function addItem(): void
    {
        $this->items[] = $this->blankItem();
    }

    public function removeItem(int $index): void
    {
        if (count($this->items) > 1) {
            array_splice($this->items, $index, 1);
        }
    }

    public function getTotalItemsProperty(): float
    {
        return collect($this->items)->sum(fn($i) => (float) ($i['amount'] ?? 0));
    }

    public function save(): void
    {
        $this->validate([
            'employee_id'      => 'required|exists:hr_employees,id',
            'destination'      => 'required|string|max:255',
            'purpose'          => 'required|string|max:255',
            'departure_date'   => 'required|date',
            'return_date'      => 'required|date|after_or_equal:departure_date',
            'amount_approved'  => 'required|numeric|min:0',
            'currency'         => 'required|in:MXN,USD,EUR',
            'project_id'       => 'nullable|exists:projects,id',
            'branch_id'        => 'nullable|exists:branches,id',
            'notes'            => 'nullable|string|max:1000',
            'items'            => 'required|array|min:1',
            'items.*.category' => 'required|string',
            'items.*.concept'  => 'required|string|max:255',
            'items.*.amount'   => 'required|numeric|min:0',
        ], [
            'employee_id.required'    => 'Selecciona el empleado.',
            'destination.required'    => 'Indica el destino.',
            'purpose.required'        => 'Indica el propósito del viaje.',
            'departure_date.required' => 'Fecha de salida requerida.',
            'return_date.required'    => 'Fecha de retorno requerida.',
            'return_date.after_or_equal' => 'El retorno debe ser igual o posterior a la salida.',
            'amount_approved.required'   => 'Ingresa el monto aprobado.',
        ]);

        $companyId = auth()->user()->company_id;

        $data = [
            'company_id'      => $companyId,
            'branch_id'       => $this->branch_id ?: null,
            'employee_id'     => $this->employee_id,
            'assigned_by'     => auth()->id(),
            'project_id'      => $this->project_id ?: null,
            'destination'     => $this->destination,
            'purpose'         => $this->purpose,
            'departure_date'  => $this->departure_date,
            'return_date'     => $this->return_date,
            'amount_approved' => $this->amount_approved,
            'currency'        => $this->currency,
            'notes'           => $this->notes ?: null,
        ];

        if ($this->travel && $this->travel->exists) {
            $this->travel->update($data);
            $travel = $this->travel;

            // Sync items
            $keepIds = collect($this->items)->pluck('id')->filter()->toArray();
            $travel->items()->whereNotIn('id', $keepIds)->delete();
        } else {
            $folio = 'VIA-' . str_pad(
                TravelExpense::where('company_id', $companyId)->count() + 1,
                5, '0', STR_PAD_LEFT
            );
            $data['folio']  = $folio;
            $data['status'] = 'borrador';
            $travel = TravelExpense::create($data);
        }

        foreach ($this->items as $item) {
            $itemData = [
                'category'       => $item['category'],
                'concept'        => $item['concept'],
                'amount'         => $item['amount'],
                'receipt_number' => $item['receipt_number'] ?: null,
                'notes'          => $item['notes'] ?: null,
            ];

            if (!empty($item['id'])) {
                TravelExpenseItem::where('id', $item['id'])
                    ->where('travel_expense_id', $travel->id)
                    ->update($itemData);
            } else {
                $travel->items()->create($itemData);
            }
        }

        session()->flash('success', 'Viático guardado correctamente.');
        $this->redirect(route('finance.travel-expenses.index'), navigate: true);
    }

    public function render()
    {
        $companyId = auth()->user()->company_id;

        $employees = HrEmployee::where('company_id', $companyId)
            ->where('status', 'active')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'second_last_name', 'employee_number']);

        $projects = Project::where('company_id', $companyId)
            ->whereIn('status', ['activo', 'en_progreso'])
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        $branches = Branch::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $itemCategories = TravelExpense::ITEM_CATEGORIES;

        return view('livewire.finance.travel-expense-form', compact(
            'employees', 'projects', 'branches', 'itemCategories'
        ));
    }
}

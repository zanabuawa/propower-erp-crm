<?php

namespace App\Livewire\Sales;

use App\Models\FinanceAccount;
use App\Models\FinanceTransaction;
use App\Models\SaleInvoice;
use App\Models\SalePayment;
use App\Services\FacturapiService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class InvoiceShow extends Component
{
    public SaleInvoice $invoice;
    public string $activeTab = 'items';
    public bool $showPaymentForm = false;
    public string $paymentAmount = '';
    public string $paymentMethod = 'cash';
    public string $paymentReference = '';
    public string $paymentNotes = '';
    public ?int $paymentAccountId = null;
    public array $financeAccounts = [];

    public bool $paymentError = false;
    public string $paymentErrorMessage = '';

    public bool $stampError = false;
    public string $stampErrorMessage = '';

    public bool $showCancelModal = false;
    public string $cancelMotive = '02';
    public string $cancelUuidReplacement = '';
    public bool $cancelError = false;
    public string $cancelErrorMessage = '';

    const CANCEL_MOTIVES = [
        '01' => '01 - Comprobante emitido con errores con relación',
        '02' => '02 - Comprobante emitido con errores sin relación',
        '03' => '03 - No se llevó a cabo la operación',
        '04' => '04 - Operación nominativa relacionada en una factura global',
    ];

    // Mapeo de formas de pago SAT
    const PAYMENT_FORM_SAT_MAP = [
        'cash'     => '01', // Efectivo
        'transfer' => '03', // Transferencia
        'card'     => '04', // Tarjeta de crédito
        'check'    => '02', // Cheque nominativo
        'credit'   => '99', // Por definir
    ];

    public function mount($invoice): void
    {
        $this->invoice = $invoice instanceof SaleInvoice
            ? $invoice
            : SaleInvoice::with(['items.product', 'customer', 'createdBy', 'payments', 'order'])->findOrFail($invoice);

        $this->paymentAmount = $this->invoice->balance;

        $this->financeAccounts = FinanceAccount::where('company_id', auth()->user()->company_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'type', 'currency', 'current_balance'])
            ->toArray();
    }

    public function stamp(): void
    {
        abort_unless(auth()->user()->can('stamp invoices'), 403);

        $this->stampError        = false;
        $this->stampErrorMessage = '';

        $invoice  = $this->invoice;
        $customer = $invoice->customer;
        $company  = auth()->user()->company;

        // Validaciones previas
        $errors = [];
        if (! $company->rfc)               $errors[] = 'Falta el RFC de la empresa.';
        if (! $company->fiscal_regime)     $errors[] = 'Falta el régimen fiscal de la empresa.';
        if (! $company->fiscal_postal_code) $errors[] = 'Falta el código postal fiscal de la empresa.';
        if (! $customer->rfc)              $errors[] = 'Falta el RFC del cliente.';
        if (! $customer->tax_regime)       $errors[] = 'Falta el régimen fiscal del cliente.';

        foreach ($invoice->items as $item) {
            if ($item->product && ! $item->product->sat_product_code) {
                $errors[] = "El producto \"{$item->product->name}\" no tiene clave SAT.";
            }
        }

        if (count($errors)) {
            $this->stampError        = true;
            $this->stampErrorMessage = implode(' ', $errors);
            return;
        }

        // Construir payload CFDI para FacturAPI v2
        $items = $invoice->items->map(function ($item) {
            $unitPrice      = round((float) $item->unit_price, 6);
            $discountAmount = round($unitPrice * ((float) $item->discount_pct / 100), 6);
            $taxRate        = (float) $item->tax_rate / 100; // FacturAPI espera 0.16, no 16

            $product = [
                'description' => $item->description,
                'product_key' => $item->product?->sat_product_code ?? '01010101',
                'unit_key'    => $item->product?->sat_unit_code ?? 'H87',
                'unit_name'   => $item->unit ?: 'Unidad',
                'price'       => $unitPrice,
                'tax_included' => false,
            ];

            if ($item->product?->sku) {
                $product['sku'] = $item->product->sku;
            }

            $taxes = [];
            $iepsRate = (float) $item->ieps_rate;
            if ($iepsRate > 0) {
                $taxes[] = [
                    'type'      => 'IEPS',
                    'rate'      => round($iepsRate / 100, 6),
                    'factor'    => 'Tasa',
                ];
            }
            if ($item->tax_rate > 0) {
                $taxes[] = [
                    'type'   => 'IVA',
                    'rate'   => round($taxRate, 6),
                    'factor' => 'Tasa',
                ];
            }
            $product['taxes'] = $taxes;

            $entry = [
                'quantity' => (float) $item->quantity,
                'product'  => $product,
            ];

            if ($discountAmount > 0) {
                $entry['discount'] = $discountAmount;
            }

            return $entry;
        })->values()->toArray();

        // Folio numérico (FacturAPI espera integer)
        $folioNumber = (int) ltrim(preg_replace('/[^0-9]/', '', $invoice->folio), '0') ?: 1;

        $cfdiPayload = [
            'type'           => 'I',
            'use'            => $customer->cfdi_use ?: 'G01', // uso CFDI: nivel raíz en FacturAPI v2
            'payment_form'   => self::PAYMENT_FORM_SAT_MAP[$invoice->payment_method] ?? '99',
            'payment_method' => 'PUE',
            'currency'       => $invoice->currency,
            'folio_number'   => $folioNumber,
            'customer'       => [
                'legal_name' => $customer->name,
                'tax_id'     => $customer->rfc,
                'tax_system' => $customer->tax_regime,
                'address'    => [
                    'zip' => $customer->zip_code ?: $company->fiscal_postal_code,
                ],
            ],
            'items' => $items,
        ];

        Log::debug('FacturAPI CFDI payload', $cfdiPayload);

        try {
            $facturapi = app(FacturapiService::class);
            $result    = $facturapi->createInvoice($cfdiPayload);

            Log::debug('FacturAPI CFDI response', (array) $result);

            $facturApiId = $result->id ?? null;
            $uuid        = $result->uuid ?? null;

            if (! $facturApiId || ! $uuid) {
                throw new \RuntimeException('FacturAPI no devolvió ID o UUID. Respuesta: ' . json_encode($result));
            }

            $invoice->update([
                'status'       => 'stamped',
                'cfdi_uuid'    => $uuid,
                'facturapi_id' => $facturApiId,
            ]);

            $this->invoice->refresh()->load(['items.product', 'customer', 'createdBy', 'payments', 'order']);
            session()->flash('success', 'Factura timbrada correctamente. UUID: ' . $uuid);

        } catch (\Throwable $e) {
            Log::error('FacturAPI stamp error', [
                'invoice' => $invoice->id,
                'error'   => $e->getMessage(),
                'payload' => $cfdiPayload,
            ]);
            $this->stampError        = true;
            $this->stampErrorMessage = 'Error al timbrar: ' . $e->getMessage();
        }
    }

    public function cancelCfdi(): void
    {
        abort_unless(auth()->user()->can('cancel invoices'), 403);

        $this->cancelError        = false;
        $this->cancelErrorMessage = '';

        $this->validate([
            'cancelMotive'          => 'required|in:01,02,03,04',
            'cancelUuidReplacement' => $this->cancelMotive === '01'
                ? 'required|uuid'
                : 'nullable',
        ], [
            'cancelMotive.required'          => 'Selecciona un motivo de cancelación.',
            'cancelMotive.in'                => 'Motivo de cancelación inválido.',
            'cancelUuidReplacement.required' => 'El motivo 01 requiere el UUID del comprobante sustituto.',
            'cancelUuidReplacement.uuid'     => 'El UUID de sustitución no tiene el formato correcto.',
        ]);

        $invoice = $this->invoice;

        if (! $invoice->cfdi_uuid || ! $invoice->facturapi_id) {
            $this->cancelError        = true;
            $this->cancelErrorMessage = 'Esta factura no tiene un CFDI timbrado o falta el ID de FacturAPI.';
            return;
        }

        Log::debug('FacturAPI cancel request', [
            'invoice'      => $invoice->id,
            'facturapi_id' => $invoice->facturapi_id,
            'uuid'         => $invoice->cfdi_uuid,
            'motive'       => $this->cancelMotive,
        ]);

        try {
            $facturapi = app(FacturapiService::class);
            $result    = $facturapi->cancelInvoice(
                $invoice->facturapi_id,
                $this->cancelMotive,
                $this->cancelMotive === '01' ? ($this->cancelUuidReplacement ?: null) : null
            );

            Log::debug('FacturAPI cancel response', (array) $result);

            $invoice->update(['status' => 'cancelled']);
            $this->invoice->refresh()->load(['items.product', 'customer', 'createdBy', 'payments', 'order']);

            $this->showCancelModal       = false;
            $this->cancelMotive          = '02';
            $this->cancelUuidReplacement = '';

            session()->flash('success', 'Factura cancelada correctamente ante el SAT.');

        } catch (\Throwable $e) {
            Log::error('FacturAPI cancel error', [
                'invoice' => $invoice->id,
                'error'   => $e->getMessage(),
            ]);
            $this->cancelError        = true;
            $this->cancelErrorMessage = 'Error al cancelar: ' . $e->getMessage();
        }
    }

    public function openPaymentForm(): void
    {
        $this->activeTab       = 'payments';
        $this->showPaymentForm = true;
        $this->paymentError    = false;
        $this->paymentAmount   = $this->invoice->balance;
    }

    public function savePayment(): void
    {
        $this->paymentError        = false;
        $this->paymentErrorMessage = '';

        $this->validate([
            'paymentAmount'    => 'required|numeric|min:0.01',
            'paymentMethod'    => 'required|in:cash,transfer,card,check,credit',
            'paymentAccountId' => 'required|integer|exists:finance_accounts,id',
        ], [
            'paymentAmount.required'    => 'El monto es obligatorio.',
            'paymentAmount.numeric'     => 'El monto debe ser un número.',
            'paymentAmount.min'         => 'El monto debe ser mayor a cero.',
            'paymentAccountId.required' => 'Selecciona la cuenta que recibió el pago.',
            'paymentAccountId.exists'   => 'La cuenta seleccionada no es válida.',
        ]);

        try {
            DB::transaction(function () {
                $companyId = auth()->user()->company_id;

                $folio = 'PAG-' . str_pad(
                    SalePayment::where('company_id', $companyId)->count() + 1,
                    6, '0', STR_PAD_LEFT
                );

                $payment = SalePayment::create([
                    'company_id'         => $companyId,
                    'sale_invoice_id'    => $this->invoice->id,
                    'customer_id'        => $this->invoice->customer_id,
                    'created_by'         => auth()->id(),
                    'finance_account_id' => $this->paymentAccountId,
                    'folio'              => $folio,
                    'currency'           => $this->invoice->currency,
                    'payment_method'     => $this->paymentMethod,
                    'status'             => 'applied',
                    'amount'             => $this->paymentAmount,
                    'reference'          => $this->paymentReference ?: null,
                    'notes'              => $this->paymentNotes ?: null,
                    'paid_at'            => now(),
                ]);

                FinanceTransaction::create([
                    'account_id'       => $this->paymentAccountId,
                    'registered_by'    => auth()->id(),
                    'folio'            => 'TXN-' . $folio,
                    'type'             => 'ingreso',
                    'concept'          => 'Cobro: ' . $this->invoice->folio . ' — ' . $this->invoice->customer->name,
                    'category'         => 'venta',
                    'amount'           => $this->paymentAmount,
                    'currency'         => $this->invoice->currency,
                    'exchange_rate'    => 1,
                    'transaction_date' => now()->toDateString(),
                    'reference'        => $payment->folio,
                    'status'           => 'confirmado',
                    'notes'            => $this->paymentNotes ?: null,
                ]);

                $newPaidAmount = (float) $this->invoice->paid_amount + (float) $this->paymentAmount;
                $newStatus     = $newPaidAmount >= (float) $this->invoice->total ? 'paid' : $this->invoice->status;

                $this->invoice->update([
                    'paid_amount' => $newPaidAmount,
                    'status'      => $newStatus,
                ]);
            });

            $this->showPaymentForm = false;
            $this->reset(['paymentMethod', 'paymentReference', 'paymentNotes', 'paymentAccountId']);
            $this->invoice->refresh()->load(['items.product', 'customer', 'createdBy', 'payments', 'order']);
            $this->paymentAmount = $this->invoice->balance;
            session()->flash('success', 'Pago registrado correctamente.');

        } catch (\Throwable $e) {
            Log::error('savePayment error', ['invoice' => $this->invoice->id, 'error' => $e->getMessage()]);
            $this->paymentError        = true;
            $this->paymentErrorMessage = 'Error al guardar el pago: ' . $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.sales.invoice-show');
    }
}

<?php

namespace App\Livewire\Sales;

use App\Models\SaleInvoice;
use App\Models\SalePayment;
use App\Services\FacturamaService;
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

    // Catálogos Facturama
    const PAYMENT_FORM_MAP = [
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

        // Construir payload CFDI
        $items = $invoice->items->map(function ($item) {
            $base     = (float) $item->quantity * (float) $item->unit_price;
            $discount = $base * ((float) $item->discount_pct / 100);
            $subtotal = $base - $discount;
            $taxTotal = $subtotal * ((float) $item->tax_rate / 100);

            $hasTax = $item->tax_rate > 0;

            $payload = [
                'ProductCode'          => $item->product?->sat_product_code ?? '01010101',
                'IdentificationNumber' => $item->product?->sku ?? '',
                'Description'          => $item->description,
                'Unit'                 => $item->unit ?: 'NO APLICA',
                'UnitCode'             => $item->product?->sat_unit_code ?? 'H87',
                'UnitPrice'            => round((float) $item->unit_price, 6),
                'Quantity'             => (float) $item->quantity,
                'Subtotal'             => round($subtotal, 6),
                'TaxObject'            => $hasTax ? '02' : '01', // 02=Sí objeto, 01=No objeto
                'Total'                => round($subtotal + $taxTotal, 6),
            ];

            if ($item->discount_pct > 0) {
                $payload['Discount'] = round($discount, 6);
            }

            if ($hasTax) {
                $payload['Taxes'] = [[
                    'Total'       => round($taxTotal, 6),
                    'Name'        => 'IVA',
                    'Base'        => round($subtotal, 6),
                    'Rate'        => round((float) $item->tax_rate / 100, 6),
                    'IsRetention' => false,
                ]];
            }

            return $payload;
        })->values()->toArray();

        $cfdiPayload = [
            'CfdiType'        => 'I',
            'PaymentForm'     => self::PAYMENT_FORM_MAP[$invoice->payment_method] ?? '99',
            'PaymentMethod'   => 'PUE',
            'ExpeditionPlace' => $company->fiscal_postal_code,
            'Currency'        => $invoice->currency,
            'Folio'           => preg_replace('/^FAC-/', '', $invoice->folio),
            'Issuer'          => [
                'FiscalRegime' => $company->fiscal_regime,
                'Rfc'          => $company->rfc,
                'Name'         => $company->legal_name ?: $company->name,
            ],
            'Receiver'        => [
                'Rfc'          => $customer->rfc,
                'Name'         => $customer->name,
                'CfdiUse'      => $customer->cfdi_use ?: 'S01',
                'FiscalRegime' => $customer->tax_regime,
                'TaxZipCode'   => $customer->zip_code ?: '',
            ],
            'Items' => $items,
        ];

        Log::debug('Facturama CFDI payload', $cfdiPayload);

        try {
            $facturama = app(FacturamaService::class);
            $result    = $facturama->client()->post('3/cfdis', $cfdiPayload);

            Log::debug('Facturama CFDI response', (array) $result);

            $uuid = $result->Id ?? ($result['Id'] ?? null);

            if (! $uuid) {
                throw new \RuntimeException('Facturama no devolvió un UUID.');
            }

            $invoice->update([
                'status'    => 'stamped',
                'cfdi_uuid' => $uuid,
            ]);

            $this->invoice->refresh()->load(['items.product', 'customer', 'createdBy', 'payments', 'order']);
            session()->flash('success', 'Factura timbrada correctamente. UUID: ' . $uuid);

        } catch (\Facturama\Exception\RequestException $e) {
            $detail = $e->getPrevious()?->getMessage() ?: $e->getMessage();
            Log::error('Facturama stamp error', [
                'invoice' => $invoice->id,
                'error'   => $e->getMessage(),
                'detail'  => $detail,
                'payload' => $cfdiPayload,
            ]);
            $this->stampError        = true;
            $this->stampErrorMessage = $e->getMessage()
                . ($detail && $detail !== $e->getMessage() ? ' — ' . $detail : '');
        } catch (\Throwable $e) {
            Log::error('Facturama stamp error', [
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

        if (! $invoice->cfdi_uuid) {
            $this->cancelError        = true;
            $this->cancelErrorMessage = 'Esta factura no tiene un CFDI timbrado.';
            return;
        }

        $params = [
            'type'   => 'issued',
            'motive' => $this->cancelMotive,
        ];
        if ($this->cancelMotive === '01' && $this->cancelUuidReplacement) {
            $params['uuidReplacement'] = $this->cancelUuidReplacement;
        }

        Log::debug('Facturama cancel request', [
            'invoice' => $invoice->id,
            'uuid'    => $invoice->cfdi_uuid,
            'params'  => $params,
        ]);

        try {
            $facturama = app(FacturamaService::class);
            $result    = $facturama->client()->delete("Cfdi/{$invoice->cfdi_uuid}", $params);

            Log::debug('Facturama cancel response', (array) $result);

            $invoice->update(['status' => 'cancelled']);
            $this->invoice->refresh()->load(['items.product', 'customer', 'createdBy', 'payments', 'order']);

            $this->showCancelModal      = false;
            $this->cancelMotive         = '02';
            $this->cancelUuidReplacement = '';

            session()->flash('success', 'Factura cancelada correctamente ante el SAT.');

        } catch (\Facturama\Exception\RequestException $e) {
            $detail = $e->getPrevious()?->getMessage() ?: $e->getMessage();
            Log::error('Facturama cancel error', [
                'invoice' => $invoice->id,
                'error'   => $e->getMessage(),
                'detail'  => $detail,
            ]);
            $this->cancelError        = true;
            $this->cancelErrorMessage = $e->getMessage()
                . ($detail && $detail !== $e->getMessage() ? ' — ' . $detail : '');
        } catch (\Throwable $e) {
            Log::error('Facturama cancel error', [
                'invoice' => $invoice->id,
                'error'   => $e->getMessage(),
            ]);
            $this->cancelError        = true;
            $this->cancelErrorMessage = 'Error al cancelar: ' . $e->getMessage();
        }
    }

    public function savePayment(): void
    {
        $this->validate([
            'paymentAmount' => 'required|numeric|min:0.01',
            'paymentMethod' => 'required|in:cash,transfer,card,check,credit',
        ]);

        $lastNumber = (int) SalePayment::where('company_id', auth()->user()->company_id)
            ->selectRaw("MAX(CAST(SUBSTRING(folio, 5) AS UNSIGNED)) as max_num")
            ->value('max_num');
        $folio = 'PAG-' . str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);

        SalePayment::create([
            'company_id'      => auth()->user()->company_id,
            'sale_invoice_id' => $this->invoice->id,
            'customer_id'     => $this->invoice->customer_id,
            'created_by'      => auth()->id(),
            'folio'           => $folio,
            'currency'        => $this->invoice->currency,
            'payment_method'  => $this->paymentMethod,
            'status'          => 'applied',
            'amount'          => $this->paymentAmount,
            'reference'       => $this->paymentReference ?: null,
            'notes'           => $this->paymentNotes ?: null,
            'paid_at'         => now(),
        ]);

        $newPaidAmount = $this->invoice->paid_amount + $this->paymentAmount;
        $newStatus     = $newPaidAmount >= $this->invoice->total ? 'paid' : $this->invoice->status;

        $this->invoice->update([
            'paid_amount' => $newPaidAmount,
            'status'      => $newStatus,
        ]);

        $this->reset(['paymentAmount', 'paymentMethod', 'paymentReference', 'paymentNotes', 'showPaymentForm']);
        $this->invoice->refresh()->load(['items.product', 'customer', 'createdBy', 'payments', 'order']);
        session()->flash('success', 'Pago registrado correctamente.');
    }

    public function render()
    {
        return view('livewire.sales.invoice-show');
    }
}

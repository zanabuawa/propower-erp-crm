<?php

namespace App\Livewire\Purchases;

use App\Models\PurchaseInvoice;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class PurchaseInvoiceForm extends Component
{
    use WithFileUploads;

    public ?int    $orderId               = null;
    public ?int    $supplierId            = null;
    public string  $supplierInvoiceNumber = '';
    public string  $currency             = 'MXN';
    public string  $issuedAt             = '';
    public string  $subtotalInput        = '0';
    public string  $taxInput             = '0';
    public string  $totalInput           = '0';
    public string  $notes                = '';

    public $xmlFile = null;
    public $pdfFile = null;

    public array $orderOptions    = [];
    public array $supplierOptions = [];
    public ?array $loadedOrder    = null;

    public function mount(): void
    {
        $companyId = auth()->user()->company_id;
        $this->issuedAt = now()->toDateString();

        $this->supplierOptions = Supplier::where('company_id', $companyId)
            ->where('status', 'active')->orderBy('name')
            ->get(['id', 'name'])->toArray();

        // OCs en estado recibido o pagado (el pago ya ocurrió antes de la factura)
        $this->orderOptions = PurchaseOrder::where('company_id', $companyId)
            ->whereIn('status', ['received', 'partial_received', 'paid'])
            ->with('supplier:id,name')
            ->latest()->get(['id', 'folio', 'supplier_id', 'currency', 'total'])
            ->toArray();

        $orderId = request()->query('order');
        if ($orderId) {
            $this->orderId = (int) $orderId;
            $this->loadOrder();
        }
    }

    public function updatedOrderId(): void
    {
        $this->loadOrder();
    }

    private function loadOrder(): void
    {
        if (! $this->orderId) {
            $this->loadedOrder = null;
            return;
        }

        $order = PurchaseOrder::with('supplier')->find((int) $this->orderId);
        if (! $order) return;

        $this->supplierId   = $order->supplier_id;
        $this->currency     = $order->currency;
        $this->totalInput   = (string) (float) $order->total;
        $this->loadedOrder  = [
            'id'    => $order->id,
            'folio' => $order->folio,
            'total' => $order->total,
        ];
    }

    public function save(): void
    {
        $this->validate([
            'supplierId'            => 'required|exists:suppliers,id',
            'supplierInvoiceNumber' => 'required|string|max:100',
            'issuedAt'              => 'required|date',
            'totalInput'            => 'required|numeric|min:0.01',
            'xmlFile'               => 'nullable|file|mimes:xml|max:5120',
            'pdfFile'               => 'nullable|file|mimes:pdf|max:10240',
        ], [
            'supplierId.required'            => 'Selecciona el proveedor.',
            'supplierInvoiceNumber.required' => 'El número de factura del proveedor es requerido.',
            'issuedAt.required'              => 'La fecha de emisión es requerida.',
            'totalInput.min'                 => 'El total debe ser mayor a cero.',
            'xmlFile.mimes'                  => 'El archivo XML debe ser formato XML.',
            'pdfFile.mimes'                  => 'El archivo PDF debe ser formato PDF.',
        ]);

        try {
            DB::transaction(function () {
                $companyId = auth()->user()->company_id;

                $folio = 'FP-' . str_pad(
                    PurchaseInvoice::where('company_id', $companyId)->count() + 1,
                    6, '0', STR_PAD_LEFT
                );

                $subtotal = (float) $this->subtotalInput;
                $tax      = (float) $this->taxInput;
                $total    = (float) $this->totalInput;

                $xmlPath = $this->xmlFile
                    ? $this->xmlFile->storeAs("cfdi/{$companyId}", "{$folio}.xml", 'local')
                    : null;

                $pdfPath = $this->pdfFile
                    ? $this->pdfFile->storeAs("cfdi/{$companyId}", "{$folio}.pdf", 'local')
                    : null;

                // Determinar si ya está pagada (OC pagada)
                $status = 'pending';
                if ($this->orderId) {
                    $order = PurchaseOrder::find($this->orderId);
                    if ($order && $order->status === 'paid') {
                        $status      = 'paid';
                    }
                }

                $invoice = PurchaseInvoice::create([
                    'company_id'              => $companyId,
                    'purchase_order_id'       => $this->orderId ?: null,
                    'supplier_id'             => $this->supplierId,
                    'created_by'              => auth()->id(),
                    'folio'                   => $folio,
                    'supplier_invoice_number' => $this->supplierInvoiceNumber,
                    'currency'                => $this->currency,
                    'subtotal'                => $subtotal ?: ($total / 1.16),
                    'tax'                     => $tax ?: ($total - $total / 1.16),
                    'total'                   => $total,
                    'paid_amount'             => $status === 'paid' ? $total : 0,
                    'status'                  => $status,
                    'match_status'            => 'pending',
                    'notes'                   => $this->notes ?: null,
                    'issued_at'               => $this->issuedAt,
                    'xml_path'                => $xmlPath,
                    'pdf_path'                => $pdfPath,
                ]);

                if ($this->orderId) {
                    PurchaseOrder::where('id', $this->orderId)
                        ->whereNotIn('status', ['cancelled'])
                        ->update(['status' => 'invoiced']);
                }

                session()->flash('success', "Factura {$folio} registrada correctamente.");
                $this->redirectRoute('purchases.invoices.show', $invoice, navigate: true);
            });

        } catch (\Throwable $e) {
            Log::error('PurchaseInvoiceForm save', ['error' => $e->getMessage()]);
            session()->flash('error', 'Error al guardar: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.purchases.purchase-invoice-form');
    }
}

<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseInvoice extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id', 'purchase_order_id', 'supplier_id', 'created_by',
        'folio', 'supplier_invoice_number', 'currency',
        'subtotal', 'discount_amount', 'tax', 'total', 'paid_amount',
        'status', 'match_status', 'notes',
        'issued_at', 'received_at', 'due_at', 'paid_at',
        'xml_path', 'pdf_path',
    ];

    protected $casts = [
        'subtotal'        => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax'             => 'decimal:2',
        'total'           => 'decimal:2',
        'paid_amount'     => 'decimal:2',
        'issued_at'       => 'datetime',
        'received_at'     => 'datetime',
        'due_at'          => 'datetime',
        'paid_at'         => 'datetime',
    ];

    public const STATUS = [
        'pending'   => 'Pendiente',
        'approved'  => 'Aprobada',
        'partial'   => 'Pago parcial',
        'paid'      => 'Pagada',
        'cancelled' => 'Cancelada',
    ];

    public const STATUS_COLORS = [
        'pending'   => 'bg-yellow-50 text-yellow-700',
        'approved'  => 'bg-blue-50 text-blue-700',
        'partial'   => 'bg-indigo-50 text-indigo-700',
        'paid'      => 'bg-green-50 text-green-700',
        'cancelled' => 'bg-red-50 text-red-600',
    ];

    public const MATCH_STATUS = [
        'pending'     => 'Sin validar',
        'matched'     => 'Cotejo OK',
        'discrepancy' => 'Con discrepancias',
        'approved'    => 'Aprobada manualmente',
    ];

    public const MATCH_COLORS = [
        'pending'     => 'bg-gray-100 text-gray-500',
        'matched'     => 'bg-green-100 text-green-700',
        'discrepancy' => 'bg-red-100 text-red-600',
        'approved'    => 'bg-blue-100 text-blue-700',
    ];

    // ── Relations ─────────────────────────────────────────────────────────

    public function order(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseInvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SupplierPayment::class);
    }

    public function creditNotes(): HasMany
    {
        return $this->hasMany(SupplierCreditNote::class);
    }

    // ── Computed ──────────────────────────────────────────────────────────

    public function getBalanceAttribute(): float
    {
        return max(0, (float) $this->total - (float) $this->paid_amount);
    }

    // ── 3-Way Match ───────────────────────────────────────────────────────

    /**
     * Ejecuta el cotejo a tres vías línea por línea.
     * Considera rechazos de recepción: net_accepted = qty_received - qty_rejected.
     * Tolerancia de precio: ±$0.01
     */
    public function runThreeWayMatch(): void
    {
        // Build a map: purchase_order_item_id → [qty_received, qty_rejected]
        // by summing across all receipts of this order
        $receiptMap = [];
        if ($this->purchase_order_id) {
            $receiptItems = \App\Models\PurchaseReceiptItem::whereHas(
                'receipt',
                fn ($q) => $q->where('purchase_order_id', $this->purchase_order_id)
            )->get(['purchase_order_item_id', 'quantity_received', 'quantity_rejected']);

            foreach ($receiptItems as $ri) {
                $key = $ri->purchase_order_item_id;
                if (! isset($receiptMap[$key])) {
                    $receiptMap[$key] = ['received' => 0, 'rejected' => 0];
                }
                $receiptMap[$key]['received'] += (float) $ri->quantity_received;
                $receiptMap[$key]['rejected'] += (float) $ri->quantity_rejected;
            }
        }

        $allMatched = true;

        foreach ($this->items as $item) {
            if (! $item->purchase_order_item_id) {
                $item->update(['match_status' => 'unmatched']);
                $allMatched = false;
                continue;
            }

            $poiKey       = $item->purchase_order_item_id;
            $qtyReceived  = $receiptMap[$poiKey]['received'] ?? (float) ($item->qty_received ?? 0);
            $qtyRejected  = $receiptMap[$poiKey]['rejected'] ?? 0;
            $netAccepted  = max(0, $qtyReceived - $qtyRejected);
            $qtyInvoiced  = (float) $item->quantity;
            $priceOrdered = (float) ($item->price_ordered ?? 0);
            $priceInvoiced = (float) $item->unit_price;

            // Persist fresh receipt totals on the invoice item
            $item->qty_received = $qtyReceived;
            $item->qty_rejected = $qtyRejected;

            // Sin recepción
            if ($qtyReceived <= 0) {
                $item->match_status   = 'no_receipt';
                $item->variance_notes = 'No hay recepción registrada para esta partida.';
                $item->save();
                $allMatched = false;
                continue;
            }

            // Rechazos: facturado > aceptado neto
            if ($qtyRejected > 0 && $qtyInvoiced > $netAccepted + 0.001) {
                $diff = round($qtyInvoiced - $netAccepted, 4);
                $item->match_status   = 'rejection_variance';
                $item->variance_notes = "Aceptado neto: {$netAccepted} (recibido: {$qtyReceived}, rechazado: {$qtyRejected}). Facturado: {$qtyInvoiced}. Exceso sobre aceptado: {$diff}.";
                $item->save();
                $allMatched = false;
                continue;
            }

            // Cantidad facturada mayor a la recibida (sin rechazos)
            if ($qtyInvoiced > $netAccepted + 0.001) {
                $diff = round($qtyInvoiced - $netAccepted, 4);
                $item->match_status   = 'over_invoiced';
                $item->variance_notes = "Facturado {$qtyInvoiced}, aceptado {$netAccepted} (exceso: {$diff}).";
                $item->save();
                $allMatched = false;
                continue;
            }

            // Varianza de precio
            if (abs($priceInvoiced - $priceOrdered) > 0.01 && $priceOrdered > 0) {
                $diff = round($priceInvoiced - $priceOrdered, 4);
                $sign = $diff > 0 ? '+' : '';
                $item->match_status   = 'price_variance';
                $item->variance_notes = "Precio OC: \${$priceOrdered}, facturado: \${$priceInvoiced} (diferencia: {$sign}{$diff}).";
                $item->save();
                $allMatched = false;
                continue;
            }

            // Varianza de cantidad (menor = recepción parcial, solo advierte)
            if ($qtyInvoiced < $netAccepted - 0.001) {
                $item->match_status   = 'qty_variance';
                $item->variance_notes = "Facturado {$qtyInvoiced} de {$netAccepted} aceptados.";
                $item->save();
                continue;
            }

            // Todo OK
            $item->match_status   = 'matched';
            $item->variance_notes = null;
            $item->save();
        }

        $blockingStatuses  = ['no_receipt', 'over_invoiced', 'price_variance', 'rejection_variance'];
        $hasDiscrepancy    = $this->items()->whereIn('match_status', $blockingStatuses)->exists();

        $this->update([
            'match_status' => $hasDiscrepancy ? 'discrepancy' : 'matched',
        ]);
    }
}

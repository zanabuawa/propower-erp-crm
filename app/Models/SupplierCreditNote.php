<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierCreditNote extends Model
{
    use BelongsToCompany, SoftDeletes;

    protected $fillable = [
        'company_id', 'purchase_invoice_id', 'supplier_id', 'created_by',
        'folio', 'supplier_credit_note_number', 'currency',
        'subtotal', 'tax', 'total', 'applied_amount',
        'status', 'reason', 'notes',
        'issued_at', 'applied_at',
    ];

    protected $casts = [
        'subtotal'        => 'decimal:2',
        'tax'             => 'decimal:2',
        'total'           => 'decimal:2',
        'applied_amount'  => 'decimal:2',
        'issued_at'       => 'date',
        'applied_at'      => 'datetime',
    ];

    public const STATUS = [
        'draft'     => 'Borrador',
        'partial'   => 'Aplicada parcial',
        'applied'   => 'Aplicada',
        'cancelled' => 'Cancelada',
    ];

    public const STATUS_COLORS = [
        'draft'     => 'bg-gray-100 text-gray-600',
        'partial'   => 'bg-amber-50 text-amber-700',
        'applied'   => 'bg-green-50 text-green-700',
        'cancelled' => 'bg-red-50 text-red-700',
    ];

    public const REASONS = [
        'return'           => 'Devolución de mercancía',
        'price_adjustment' => 'Ajuste de precio',
        'duplicate'        => 'Factura duplicada',
        'error'            => 'Error en facturación',
        'other'            => 'Otro',
    ];

    public function getBalanceAttribute(): float
    {
        return max(0, (float) $this->total - (float) $this->applied_amount);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(PurchaseInvoice::class, 'purchase_invoice_id');
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
        return $this->hasMany(SupplierCreditNoteItem::class);
    }
}

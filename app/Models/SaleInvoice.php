<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaleInvoice extends Model
{
    use BelongsToCompany, SoftDeletes;

    protected $fillable = [
        'company_id', 'sale_order_id', 'customer_id', 'created_by',
        'folio', 'cfdi_uuid', 'cfdi_xml', 'cfdi_pdf', 'type', 'currency',
        'status', 'payment_method', 'subtotal', 'discount_amount', 'tax',
        'total', 'paid_amount', 'notes', 'issued_at', 'due_at',
    ];

    protected $casts = [
        'subtotal'        => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax'             => 'decimal:2',
        'total'           => 'decimal:2',
        'paid_amount'     => 'decimal:2',
        'issued_at'       => 'datetime',
        'due_at'          => 'datetime',
    ];

    const STATUS = [
        'draft'     => 'Borrador',
        'stamped'   => 'Timbrada',
        'paid'      => 'Pagada',
        'cancelled' => 'Cancelada',
    ];

    const STATUS_COLORS = [
        'draft'     => 'bg-gray-100 text-gray-600',
        'stamped'   => 'bg-blue-50 text-blue-700',
        'paid'      => 'bg-green-50 text-green-700',
        'cancelled' => 'bg-red-50 text-red-700',
    ];

    const PAYMENT_METHODS = [
        'cash'     => 'Efectivo',
        'transfer' => 'Transferencia',
        'card'     => 'Tarjeta',
        'check'    => 'Cheque',
        'credit'   => 'Crédito',
    ];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function order(): BelongsTo { return $this->belongsTo(SaleOrder::class, 'sale_order_id'); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function items(): HasMany { return $this->hasMany(SaleInvoiceItem::class); }
    public function payments(): HasMany { return $this->hasMany(SalePayment::class); }
    public function creditNotes(): HasMany { return $this->hasMany(SaleCreditNote::class); }

    public function getBalanceAttribute(): float
    {
        return $this->total - $this->paid_amount;
    }
}
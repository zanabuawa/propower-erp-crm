<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaleOrder extends Model
{
    use BelongsToCompany, SoftDeletes;

    protected $fillable = [
        'company_id', 'branch_id', 'customer_id', 'sale_quotation_id', 'price_list_id',
        'created_by', 'folio', 'currency', 'status', 'payment_method', 'payment_terms',
        'subtotal', 'discount_amount', 'tax', 'total', 'notes', 'required_at',
    ];

    protected $casts = [
        'subtotal'        => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax'             => 'decimal:2',
        'total'           => 'decimal:2',
        'required_at'     => 'datetime',
    ];

    const STATUS = [
        'draft'             => 'Borrador',
        'confirmed'         => 'Confirmada',
        'partial_delivered' => 'Entrega parcial',
        'delivered'         => 'Entregada',
        'invoiced'          => 'Facturada',
        'cancelled'         => 'Cancelada',
    ];

    const STATUS_COLORS = [
        'draft'             => 'bg-gray-100 text-gray-600',
        'confirmed'         => 'bg-blue-50 text-blue-700',
        'partial_delivered' => 'bg-amber-50 text-amber-700',
        'delivered'         => 'bg-teal-50 text-teal-700',
        'invoiced'          => 'bg-green-50 text-green-700',
        'cancelled'         => 'bg-red-50 text-red-700',
    ];

    const PAYMENT_METHODS = [
        'cash'     => 'Efectivo',
        'transfer' => 'Transferencia',
        'card'     => 'Tarjeta',
        'check'    => 'Cheque',
        'credit'   => 'Crédito',
    ];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function quotation(): BelongsTo { return $this->belongsTo(SaleQuotation::class, 'sale_quotation_id'); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function items(): HasMany { return $this->hasMany(SaleOrderItem::class); }
    public function deliveries(): HasMany { return $this->hasMany(SaleDelivery::class); }
    public function invoice(): HasOne { return $this->hasOne(SaleInvoice::class); }
}
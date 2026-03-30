<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaleQuotation extends Model
{
    use BelongsToCompany, SoftDeletes;

    protected $fillable = [
        'company_id', 'branch_id', 'customer_id', 'price_list_id', 'created_by',
        'folio', 'currency', 'status', 'subtotal', 'discount_amount', 'tax', 'total',
        'valid_days', 'valid_until', 'notes', 'terms',
    ];

    protected $casts = [
        'subtotal'        => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax'             => 'decimal:2',
        'total'           => 'decimal:2',
        'valid_until'     => 'date',
    ];

    const STATUS = [
        'draft'    => 'Borrador',
        'sent'     => 'Enviada',
        'accepted' => 'Aceptada',
        'rejected' => 'Rechazada',
        'expired'  => 'Vencida',
    ];

    const STATUS_COLORS = [
        'draft'    => 'bg-gray-100 text-gray-600',
        'sent'     => 'bg-blue-50 text-blue-700',
        'accepted' => 'bg-green-50 text-green-700',
        'rejected' => 'bg-red-50 text-red-700',
        'expired'  => 'bg-amber-50 text-amber-700',
    ];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function priceList(): BelongsTo { return $this->belongsTo(PriceList::class); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function items(): HasMany { return $this->hasMany(SaleQuotationItem::class); }
    public function order(): HasOne { return $this->hasOne(SaleOrder::class, 'sale_quotation_id'); }
}
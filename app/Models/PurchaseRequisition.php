<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseRequisition extends Model
{
    use BelongsToCompany, SoftDeletes;

    protected $fillable = [
        'company_id', 'branch_id', 'requested_by', 'reviewed_by',
        'folio', 'currency', 'status', 'justification', 'quote_response',
        'quoted_amount', 'needed_by', 'quoted_at',
    ];

    protected $casts = [
        'quoted_amount' => 'decimal:2',
        'needed_by'     => 'datetime',
        'quoted_at'     => 'datetime',
    ];

    const STATUS = [
        'draft'            => 'Borrador',
        'pending_quote'    => 'Pendiente cotización',
        'quoted'           => 'Cotizado',
        'pending_approval' => 'Pendiente aprobación',
        'approved'         => 'Aprobado',
        'rejected'         => 'Rechazado',
        'cancelled'        => 'Cancelado',
    ];

    const STATUS_COLORS = [
        'draft'            => 'bg-gray-100 text-gray-600',
        'pending_quote'    => 'bg-amber-50 text-amber-700',
        'quoted'           => 'bg-blue-50 text-blue-700',
        'pending_approval' => 'bg-purple-50 text-purple-700',
        'approved'         => 'bg-green-50 text-green-700',
        'rejected'         => 'bg-red-50 text-red-700',
        'cancelled'        => 'bg-gray-100 text-gray-500',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseRequisitionItem::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(PurchaseApproval::class);
    }

    public function order(): HasOne
    {
        return $this->hasOne(PurchaseOrder::class);
    }

    public function getTotalAttribute(): float
    {
        return $this->items->sum(fn($i) => $i->quantity * $i->unit_price);
    }
}
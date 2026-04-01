<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseQuotation extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id', 'purchase_requisition_id', 'type', 'status',
        'subtotal', 'tax', 'total', 'notes', 'requester_notes',
        'created_by', 'responded_at',
    ];

    protected $casts = [
        'subtotal'      => 'decimal:2',
        'tax'           => 'decimal:2',
        'total'         => 'decimal:2',
        'responded_at'  => 'datetime',
    ];

    // Nivel de autorización según monto total
    const THRESHOLD_ADMIN   = 2000;
    const THRESHOLD_GERENTE = 10000;

    public function requisition(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequisition::class, 'purchase_requisition_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseQuotationItem::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(PurchaseQuotationApproval::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Roles que deben autorizar esta cotización según el monto.
     */
    public function getRequiredRoles(): array
    {
        $roles = ['comprador'];
        if ($this->total >= self::THRESHOLD_ADMIN)   $roles[] = 'admin';
        if ($this->total >= self::THRESHOLD_GERENTE) $roles[] = 'gerente';
        return $roles;
    }

    /**
     * Nivel de autorización según monto (1, 2 o 3).
     */
    public function getAuthLevelAttribute(): int
    {
        if ($this->total >= self::THRESHOLD_GERENTE) return 3;
        if ($this->total >= self::THRESHOLD_ADMIN)   return 2;
        return 1;
    }
}

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
        'company_id', 'branch_id', 'requested_by', 'reviewed_by', 'rejected_by',
        'folio', 'currency', 'requisition_type', 'priority', 'expense_type', 'project_name',
        'status', 'justification',
        'quote_response', 'quoted_amount', 'needed_by', 'quoted_at',
        'requester_notes', 'reject_reason',
        'submitted_at', 'confirmed_at', 'rejected_at',
    ];

    protected $casts = [
        'quoted_amount'  => 'decimal:2',
        'needed_by'      => 'datetime',
        'quoted_at'      => 'datetime',
        'submitted_at'   => 'datetime',
        'confirmed_at'   => 'datetime',
        'rejected_at'    => 'datetime',
    ];

    // ── Tipos de requisición ────────────────────────────────────────────────
    public const REQUISITION_TYPES = [
        'material' => 'Material / Consumible',
        'service'  => 'Servicio',
        'tool'     => 'Herramienta / Equipo',
        'asset'    => 'Activo fijo',
        'mixed'    => 'Mixta',
    ];

    public const REQUISITION_TYPE_ICONS = [
        'material' => '📦',
        'service'  => '🛠️',
        'tool'     => '🔧',
        'asset'    => '🏢',
        'mixed'    => '📋',
    ];

    public const REQUISITION_TYPE_COLORS = [
        'material' => 'bg-blue-50 text-blue-700',
        'service'  => 'bg-purple-50 text-purple-700',
        'tool'     => 'bg-amber-50 text-amber-700',
        'asset'    => 'bg-emerald-50 text-emerald-700',
        'mixed'    => 'bg-gray-100 text-gray-600',
    ];

    // ── Prioridades ─────────────────────────────────────────────────────────
    public const PRIORITY = [
        'low'    => 'Baja',
        'normal' => 'Normal',
        'high'   => 'Alta',
        'urgent' => 'Urgente',
    ];

    public const PRIORITY_COLORS = [
        'low'    => 'bg-gray-100 text-gray-500',
        'normal' => 'bg-blue-50 text-blue-600',
        'high'   => 'bg-amber-50 text-amber-700',
        'urgent' => 'bg-red-100 text-red-700',
    ];

    // ── Tipos de gasto ──────────────────────────────────────────────────────
    public const EXPENSE_TYPES = [
        'operational'  => 'Operación',
        'capital'      => 'Capital / Inversión',
        'maintenance'  => 'Mantenimiento',
        'project'      => 'Proyecto específico',
        'other'        => 'Otro',
    ];

    // ── Estados del flujo nuevo ─────────────────────────────────────────────
    public const STATUS = [
        'draft'                   => 'Borrador',
        'submitted'               => 'Enviada a compras',
        'preliminary_quoted'      => 'Cotización preliminar enviada',
        'requester_returned'      => 'Devuelta por solicitante',
        'requester_confirmed'     => 'Confirmada por solicitante',
        'final_quoted'            => 'Cotización final en proceso',
        'pending_auth'            => 'Pendiente de autorización',
        'authorized'              => 'Autorizada',
        'ordered'                 => 'Orden de compra generada',
        'rejected'                => 'Rechazada',
        'cancelled'               => 'Cancelada',
        // compatibilidad con registros anteriores
        'pending_quote'           => 'Pendiente cotización',
        'quoted'                  => 'Cotizado',
        'pending_approval'        => 'Pendiente aprobación',
        'approved'                => 'Aprobado',
    ];

    public const STATUS_COLORS = [
        'draft'               => 'bg-gray-100 text-gray-600',
        'submitted'           => 'bg-amber-50 text-amber-700',
        'preliminary_quoted'  => 'bg-blue-50 text-blue-700',
        'requester_returned'  => 'bg-orange-50 text-orange-700',
        'requester_confirmed' => 'bg-cyan-50 text-cyan-700',
        'final_quoted'        => 'bg-purple-50 text-purple-700',
        'pending_auth'        => 'bg-purple-50 text-purple-700',
        'authorized'          => 'bg-green-50 text-green-700',
        'ordered'             => 'bg-green-100 text-green-800',
        'rejected'            => 'bg-red-50 text-red-700',
        'cancelled'           => 'bg-gray-100 text-gray-500',
        'pending_quote'       => 'bg-amber-50 text-amber-700',
        'quoted'              => 'bg-blue-50 text-blue-700',
        'pending_approval'    => 'bg-purple-50 text-purple-700',
        'approved'            => 'bg-green-50 text-green-700',
    ];

    // ── Relationships ───────────────────────────────────────────────────────

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

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
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

    public function quotations(): HasMany
    {
        return $this->hasMany(PurchaseQuotation::class);
    }

    public function preliminaryQuotation(): HasOne
    {
        return $this->hasOne(PurchaseQuotation::class)->where('type', 'preliminary')->latestOfMany();
    }

    public function finalQuotation(): HasOne
    {
        return $this->hasOne(PurchaseQuotation::class)->where('type', 'final')->latestOfMany();
    }

    // ── Computed ────────────────────────────────────────────────────────────

    public function getTotalAttribute(): float
    {
        return $this->items->sum(fn($i) => $i->quantity * $i->unit_price);
    }
}

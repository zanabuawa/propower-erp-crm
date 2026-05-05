<?php

namespace App\Models;

use App\Notifications\DiscountApprovalNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DiscountApproval extends Model
{
    protected $fillable = [
        'company_id', 'requester_id', 'approver_id',
        'model_type', 'model_id',
        'status', 'requested_discount_pct', 'max_allowed_pct',
        'requester_notes', 'approver_notes', 'responded_at',
    ];

    protected $casts = [
        'requested_discount_pct' => 'decimal:2',
        'max_allowed_pct'        => 'decimal:2',
        'responded_at'           => 'datetime',
    ];

    public const STATUSES = [
        'pending'  => 'Pendiente',
        'approved' => 'Aprobada',
        'rejected' => 'Rechazada',
    ];

    public const STATUS_COLORS = [
        'pending'  => 'bg-amber-50 text-amber-700 border-amber-100',
        'approved' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
        'rejected' => 'bg-red-50 text-red-600 border-red-100',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function approvable(): MorphTo
    {
        return $this->morphTo('model');
    }

    public function approve(int $approverId, ?string $notes = null): void
    {
        $this->update([
            'status'         => 'approved',
            'approver_id'    => $approverId,
            'approver_notes' => $notes,
            'responded_at'   => now(),
        ]);

        $this->approvable?->update(['approval_status' => 'approved']);

        $this->requester?->notify(new DiscountApprovalNotification(
            type:          'approved',
            folio:         $this->approvable?->folio ?? "#{$this->model_id}",
            requestedPct:  (float) $this->requested_discount_pct,
            maxAllowedPct: (float) $this->max_allowed_pct,
            notes:         $notes,
            approvalId:    $this->id,
        ));
    }

    public function reject(int $approverId, ?string $notes = null): void
    {
        $this->update([
            'status'         => 'rejected',
            'approver_id'    => $approverId,
            'approver_notes' => $notes,
            'responded_at'   => now(),
        ]);

        $this->approvable?->update(['approval_status' => 'rejected']);

        $this->requester?->notify(new DiscountApprovalNotification(
            type:          'rejected',
            folio:         $this->approvable?->folio ?? "#{$this->model_id}",
            requestedPct:  (float) $this->requested_discount_pct,
            maxAllowedPct: (float) $this->max_allowed_pct,
            notes:         $notes,
            approvalId:    $this->id,
        ));
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrmActivity extends Model
{
    protected $fillable = [
        'company_id', 'user_id', 'assigned_to',
        'prospect_id', 'customer_id', 'opportunity_id',
        'type', 'title', 'description',
        'scheduled_at', 'reminder_at', 'completed_at',
        'status', 'outcome',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'reminder_at'  => 'datetime',
        'completed_at' => 'datetime',
    ];

    const TYPES = [
        'call'     => 'Llamada',
        'email'    => 'Correo',
        'meeting'  => 'Reunión',
        'visit'    => 'Visita',
        'whatsapp' => 'WhatsApp',
        'task'     => 'Tarea',
        'note'     => 'Nota',
    ];

    const TYPE_ICONS = [
        'call'     => '📞',
        'email'    => '✉️',
        'meeting'  => '🤝',
        'visit'    => '🏢',
        'whatsapp' => '💬',
        'task'     => '✅',
        'note'     => '📝',
    ];

    const STATUSES = [
        'pending'   => 'Pendiente',
        'completed' => 'Completado',
        'cancelled' => 'Cancelado',
    ];

    const STATUS_COLORS = [
        'pending'   => 'bg-amber-50 text-amber-700 border-amber-100',
        'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
        'cancelled' => 'bg-red-50 text-red-600 border-red-100',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function prospect(): BelongsTo
    {
        return $this->belongsTo(SalesProspect::class, 'prospect_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(SalesOpportunity::class, 'opportunity_id');
    }

    public function isOverdue(): bool
    {
        return $this->status === 'pending'
            && $this->scheduled_at
            && $this->scheduled_at->isPast();
    }

    public function getRelatedNameAttribute(): string
    {
        return $this->customer?->name ?? $this->prospect?->name ?? '—';
    }
}

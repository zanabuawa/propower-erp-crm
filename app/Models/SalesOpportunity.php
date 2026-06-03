<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesOpportunity extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id', 'assigned_to', 'prospect_id', 'customer_id',
        'title', 'stage', 'probability', 'estimated_value',
        'expected_close_date', 'description', 'lost_reason', 'won_at', 'lost_at',
    ];

    protected $casts = [
        'expected_close_date' => 'date',
        'won_at'              => 'datetime',
        'lost_at'             => 'datetime',
        'estimated_value'     => 'decimal:2',
        'probability'         => 'integer',
    ];

    const STAGES = [
        'qualification' => 'Calificación',
        'proposal'      => 'Propuesta',
        'negotiation'   => 'Negociación',
        'won'           => 'Ganada',
        'lost'          => 'Perdida',
    ];

    const STAGE_COLORS = [
        'qualification' => 'bg-gray-100 text-gray-600 border-gray-200',
        'proposal'      => 'bg-blue-50 text-blue-700 border-blue-100',
        'negotiation'   => 'bg-amber-50 text-amber-700 border-amber-100',
        'won'           => 'bg-emerald-50 text-emerald-700 border-emerald-100',
        'lost'          => 'bg-red-50 text-red-600 border-red-100',
    ];

    // Probabilidad por defecto al cambiar etapa
    const STAGE_PROBABILITY = [
        'qualification' => 10,
        'proposal'      => 30,
        'negotiation'   => 60,
        'won'           => 100,
        'lost'          => 0,
    ];

    const LOST_REASONS = [
        'price'       => 'Precio no competitivo',
        'competitor'  => 'Ganó la competencia',
        'no_budget'   => 'Sin presupuesto',
        'no_need'     => 'Sin necesidad real',
        'no_decision' => 'Sin decisión de compra',
        'timing'      => 'Mal momento',
        'other'       => 'Otro motivo',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(CrmActivity::class, 'opportunity_id')->latest('scheduled_at');
    }

    public function getLinkedNameAttribute(): string
    {
        return $this->customer?->name ?? '—';
    }

    public function isActive(): bool
    {
        return !in_array($this->stage, ['won', 'lost']);
    }

    public function weightedValue(): float
    {
        return round((float) $this->estimated_value * $this->probability / 100, 2);
    }
}

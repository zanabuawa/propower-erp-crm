<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetMaintenance extends Model
{
    protected $fillable = [
        'company_id', 'fixed_asset_id', 'created_by', 'technician_user_id',
        'folio', 'type', 'status', 'scheduled_date', 'completed_date',
        'technician_name', 'provider', 'cost', 'next_scheduled_date',
        'interval_months', 'work_performed', 'parts_replaced', 'observations',
    ];

    protected $casts = [
        'scheduled_date'      => 'date',
        'completed_date'      => 'date',
        'next_scheduled_date' => 'date',
        'cost'                => 'decimal:2',
    ];

    const TYPES = [
        'preventive'  => 'Mantenimiento preventivo',
        'corrective'  => 'Mantenimiento correctivo',
        'calibration' => 'Calibración',
        'inspection'  => 'Inspección',
    ];

    const STATUSES = [
        'scheduled'   => 'Programado',
        'in_progress' => 'En proceso',
        'completed'   => 'Completado',
        'cancelled'   => 'Cancelado',
    ];

    const STATUS_COLORS = [
        'scheduled'   => 'blue',
        'in_progress' => 'yellow',
        'completed'   => 'green',
        'cancelled'   => 'red',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(FixedAsset::class, 'fixed_asset_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_user_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function getTechnicianLabelAttribute(): string
    {
        return $this->technician?->name ?? $this->technician_name ?? '—';
    }

    public function isOverdue(): bool
    {
        return in_array($this->status, ['scheduled', 'in_progress'])
            && $this->scheduled_date->isPast();
    }

    public static function generateFolio(int $companyId): string
    {
        $count = self::where('company_id', $companyId)->count() + 1;
        return 'MNT-' . str_pad($count, 5, '0', STR_PAD_LEFT);
    }
}

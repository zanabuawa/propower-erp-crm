<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmCampaign extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id', 'created_by',
        'folio', 'name', 'description',
        'type', 'status', 'target_audience',
        'budget', 'spent', 'start_at', 'end_at',
        'leads_generated', 'conversions', 'revenue_generated',
    ];

    protected $casts = [
        'start_at'          => 'date',
        'end_at'            => 'date',
        'budget'            => 'float',
        'spent'             => 'float',
        'revenue_generated' => 'float',
    ];

    const TYPES = [
        'email'        => 'Email',
        'whatsapp'     => 'WhatsApp',
        'sms'          => 'SMS',
        'social_media' => 'Redes sociales',
        'event'        => 'Evento',
        'phone'        => 'Llamada telefónica',
        'other'        => 'Otro',
    ];

    const TYPE_COLORS = [
        'email'        => 'bg-blue-50 text-blue-700 border-blue-100',
        'whatsapp'     => 'bg-emerald-50 text-emerald-700 border-emerald-100',
        'sms'          => 'bg-teal-50 text-teal-700 border-teal-100',
        'social_media' => 'bg-indigo-50 text-indigo-700 border-indigo-100',
        'event'        => 'bg-purple-50 text-purple-700 border-purple-100',
        'phone'        => 'bg-amber-50 text-amber-700 border-amber-100',
        'other'        => 'bg-gray-50 text-gray-600 border-gray-200',
    ];

    const STATUSES = [
        'draft'     => 'Borrador',
        'active'    => 'Activa',
        'paused'    => 'Pausada',
        'completed' => 'Completada',
        'cancelled' => 'Cancelada',
    ];

    const STATUS_COLORS = [
        'draft'     => 'bg-gray-100 text-gray-500 border-gray-200',
        'active'    => 'bg-emerald-50 text-emerald-700 border-emerald-100',
        'paused'    => 'bg-amber-50 text-amber-700 border-amber-100',
        'completed' => 'bg-blue-50 text-blue-700 border-blue-100',
        'cancelled' => 'bg-red-50 text-red-600 border-red-100',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function roi(): ?float
    {
        if (!$this->budget || $this->budget == 0) return null;
        return (($this->revenue_generated ?? 0) - ($this->spent ?? $this->budget)) / $this->budget * 100;
    }

    public function conversionRate(): float
    {
        if (!$this->leads_generated) return 0;
        return $this->conversions / $this->leads_generated * 100;
    }

    public function costPerLead(): ?float
    {
        if (!$this->leads_generated) return null;
        return ($this->spent ?? 0) / $this->leads_generated;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}

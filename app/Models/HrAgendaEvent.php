<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class HrAgendaEvent extends Model
{
    use BelongsToCompany, SoftDeletes;

    protected $fillable = [
        'company_id',
        'created_by_id',
        'title',
        'type',
        'starts_at',
        'color',
        'description',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
    ];

    public const TYPES = [
        'general' => 'General',
        'capacitacion' => 'Capacitacion',
        'reunion' => 'Reunion',
        'recordatorio' => 'Recordatorio',
        'evento' => 'Evento',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucwords(str_replace(['_', '-'], ' ', $this->type));
    }
}

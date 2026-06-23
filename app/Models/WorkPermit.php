<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class WorkPermit extends Model
{
    use SoftDeletes;

    const TYPES = [
        'altura'     => 'Trabajo en Altura',
        'excavacion' => 'Excavación',
        'electrico'  => 'Trabajo Eléctrico',
        'confinado'  => 'Espacio Confinado',
        'caliente'   => 'Trabajo en Caliente',
        'general'    => 'General',
    ];

    const STATUSES = [
        'activo'    => 'Activo',
        'vencido'   => 'Vencido',
        'cancelado' => 'Cancelado',
    ];

    const STATUS_COLORS = [
        'activo'    => 'green',
        'vencido'   => 'yellow',
        'cancelado' => 'red',
    ];

    protected $fillable = [
        'project_id', 'tender_id', 'type', 'description',
        'issued_by', 'valid_from', 'valid_until', 'status', 'notes',
        'document_path', 'document_original_name',
    ];

    protected $casts = [
        'valid_from'  => 'date',
        'valid_until' => 'date',
    ];

    public function project(): BelongsTo   { return $this->belongsTo(Project::class); }
    public function tender(): BelongsTo    { return $this->belongsTo(Tender::class); }
    public function issuedBy(): BelongsTo  { return $this->belongsTo(User::class, 'issued_by'); }

    public function getIsExpiredAttribute(): bool
    {
        return $this->status === 'activo' && $this->valid_until->isPast();
    }

    public function getDocumentUrlAttribute(): ?string
    {
        return $this->document_path ? Storage::url($this->document_path) : null;
    }
}

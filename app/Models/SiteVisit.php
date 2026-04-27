<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class SiteVisit extends Model
{
    use BelongsToCompany, SoftDeletes;

    const TYPES = [
        'reconocimiento' => 'Reconocimiento de sitio',
        'supervision'    => 'Supervisión',
        'entrega'        => 'Entrega de obra',
        'cliente'        => 'Visita con cliente',
        'interna'        => 'Interna',
    ];

    const STATUSES = [
        'programada' => 'Programada',
        'realizada'  => 'Realizada',
        'cancelada'  => 'Cancelada',
    ];

    const STATUS_COLORS = [
        'programada' => 'blue',
        'realizada'  => 'green',
        'cancelada'  => 'red',
    ];

    protected $fillable = [
        'company_id', 'project_id', 'tender_id',
        'visit_date', 'visit_type', 'purpose',
        'address', 'location_notes', 'attendees',
        'report', 'photos', 'status', 'created_by',
    ];

    protected $casts = [
        'visit_date' => 'date',
        'attendees'  => 'array',
        'photos'     => 'array',
    ];

    public function company(): BelongsTo    { return $this->belongsTo(Company::class); }
    public function project(): BelongsTo    { return $this->belongsTo(Project::class); }
    public function tender(): BelongsTo     { return $this->belongsTo(Tender::class); }
    public function createdBy(): BelongsTo  { return $this->belongsTo(User::class, 'created_by'); }

    public function getPhotoUrlsAttribute(): array
    {
        return collect($this->photos ?? [])->map(fn($p) => Storage::url($p))->toArray();
    }
}

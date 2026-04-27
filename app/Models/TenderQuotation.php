<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TenderQuotation extends Model
{
    use SoftDeletes;

    const STATUSES = [
        'borrador'  => 'Borrador',
        'enviada'   => 'Enviada',
        'aceptada'  => 'Aceptada',
        'rechazada' => 'Rechazada',
    ];

    const STATUS_COLORS = [
        'borrador'  => 'gray',
        'enviada'   => 'blue',
        'aceptada'  => 'green',
        'rechazada' => 'red',
    ];

    protected $fillable = [
        'tender_id', 'issuing_company_id', 'folio',
        'status', 'valid_until', 'notes', 'created_by',
    ];

    protected $casts = [
        'valid_until' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $q) {
            if (empty($q->folio)) {
                $year  = now()->format('Y');
                $count = self::whereYear('created_at', $year)->count() + 1;
                $q->folio = 'COT-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function tender(): BelongsTo       { return $this->belongsTo(Tender::class); }
    public function issuingCompany(): BelongsTo { return $this->belongsTo(Company::class, 'issuing_company_id'); }
    public function createdBy(): BelongsTo    { return $this->belongsTo(User::class, 'created_by'); }
    public function items(): HasMany          { return $this->hasMany(TenderQuotationItem::class, 'quotation_id')->orderBy('sort_order'); }

    public function getTotalAttribute(): float
    {
        return (float) $this->items->sum('total');
    }
}

<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tender extends Model
{
    use BelongsToCompany, SoftDeletes;

    const TYPES = [
        'contrato_servicios' => 'Contrato de Servicios',
        'obra_publica'       => 'Obra Pública',
        'obra_privada'       => 'Obra Privada',
        'suministro'         => 'Suministro',
        'mixto'              => 'Mixto',
    ];

    const STATUSES = [
        'borrador'       => 'Borrador',
        'publicada'      => 'Publicada',
        'en_evaluacion'  => 'En Evaluación',
        'adjudicada'     => 'Adjudicada',
        'desierta'       => 'Desierta',
        'cancelada'      => 'Cancelada',
    ];

    const STATUS_COLORS = [
        'borrador'      => 'gray',
        'publicada'     => 'blue',
        'en_evaluacion' => 'yellow',
        'adjudicada'    => 'green',
        'desierta'      => 'orange',
        'cancelada'     => 'red',
    ];

    protected $fillable = [
        'company_id', 'branch_id', 'folio', 'name', 'description',
        'type', 'status', 'customer_id', 'project_id', 'responsible_user_id',
        'submission_date', 'opening_date', 'award_date',
        'estimated_budget', 'awarded_amount', 'feedback', 'notes',
    ];

    protected $casts = [
        'submission_date'  => 'date',
        'opening_date'     => 'date',
        'award_date'       => 'date',
        'estimated_budget' => 'float',
        'awarded_amount'   => 'float',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $tender) {
            if (empty($tender->folio)) {
                $year  = now()->format('Y');
                $count = self::whereYear('created_at', $year)->count() + 1;
                $tender->folio = 'LIC-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function company(): BelongsTo       { return $this->belongsTo(Company::class); }
    public function branch(): BelongsTo        { return $this->belongsTo(Branch::class); }
    public function customer(): BelongsTo      { return $this->belongsTo(Customer::class); }
    public function project(): BelongsTo       { return $this->belongsTo(Project::class); }
    public function responsible(): BelongsTo   { return $this->belongsTo(User::class, 'responsible_user_id'); }
    public function items(): HasMany           { return $this->hasMany(TenderItem::class)->orderBy('sort_order'); }
    public function quotations(): HasMany      { return $this->hasMany(TenderQuotation::class); }
    public function workPermits(): HasMany     { return $this->hasMany(WorkPermit::class); }
    public function workReports(): HasMany     { return $this->hasMany(WorkReport::class); }
    public function workLibranzas(): HasMany      { return $this->hasMany(WorkLibranza::class); }
    public function siteVisits(): HasMany         { return $this->hasMany(SiteVisit::class); }
    public function financeTransactions(): HasMany { return $this->hasMany(FinanceTransaction::class); }
    public function financeCashflows(): HasMany    { return $this->hasMany(FinanceCashflow::class); }

    public function getTotalAttribute(): float
    {
        return (float) $this->items->sum('total');
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'gray';
    }
}

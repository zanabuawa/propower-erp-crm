<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkLibranza extends Model
{
    use SoftDeletes;

    const STATUSES = [
        'borrador' => 'Borrador',
        'enviada'  => 'Enviada',
        'aprobada' => 'Aprobada',
        'pagada'   => 'Pagada',
    ];

    const STATUS_COLORS = [
        'borrador' => 'gray',
        'enviada'  => 'blue',
        'aprobada' => 'green',
        'pagada'   => 'emerald',
    ];

    protected $fillable = [
        'project_id', 'tender_id', 'number', 'concept',
        'period_start', 'period_end', 'amount', 'advance_pct',
        'status', 'approved_by', 'approved_at', 'notes',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end'   => 'date',
        'approved_at'  => 'datetime',
        'amount'       => 'float',
        'advance_pct'  => 'float',
    ];

    public function project(): BelongsTo       { return $this->belongsTo(Project::class); }
    public function tender(): BelongsTo        { return $this->belongsTo(Tender::class); }
    public function approvedBy(): BelongsTo    { return $this->belongsTo(User::class, 'approved_by'); }
    public function financeCashflows(): HasMany { return $this->hasMany(FinanceCashflow::class, 'libranza_id'); }
    public function financeTransactions(): HasMany { return $this->hasMany(FinanceTransaction::class, 'libranza_id'); }
}

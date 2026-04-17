<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierEvaluation extends Model
{
    protected $fillable = [
        'company_id', 'supplier_id', 'purchase_order_id', 'evaluated_by',
        'score_price', 'score_quality', 'score_delivery', 'score_compliance',
        'score_overall', 'notes', 'evaluated_at',
    ];

    protected $casts = [
        'score_price'      => 'integer',
        'score_quality'    => 'integer',
        'score_delivery'   => 'integer',
        'score_compliance' => 'integer',
        'score_overall'    => 'decimal:2',
        'evaluated_at'     => 'date',
    ];

    const SCORE_LABELS = [
        1 => 'Muy malo',
        2 => 'Malo',
        3 => 'Regular',
        4 => 'Bueno',
        5 => 'Excelente',
    ];

    const SCORE_COLORS = [
        1 => 'text-red-600',
        2 => 'text-orange-500',
        3 => 'text-amber-500',
        4 => 'text-teal-600',
        5 => 'text-emerald-600',
    ];

    const DIMENSIONS = [
        'score_price'      => 'Precio',
        'score_quality'    => 'Calidad',
        'score_delivery'   => 'Entrega',
        'score_compliance' => 'Cumplimiento',
    ];

    public function supplier(): BelongsTo  { return $this->belongsTo(Supplier::class); }
    public function purchaseOrder(): BelongsTo { return $this->belongsTo(PurchaseOrder::class); }
    public function evaluatedBy(): BelongsTo { return $this->belongsTo(User::class, 'evaluated_by'); }

    protected static function booted(): void
    {
        static::saving(function (self $eval) {
            $eval->score_overall = round(
                ($eval->score_price + $eval->score_quality + $eval->score_delivery + $eval->score_compliance) / 4,
                2
            );
        });
    }
}

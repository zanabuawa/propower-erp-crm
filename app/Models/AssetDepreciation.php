<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetDepreciation extends Model
{
    protected $fillable = [
        'company_id', 'fixed_asset_id', 'year', 'month', 'method',
        'book_value_start', 'depreciation_amount', 'accumulated_depreciation',
        'book_value_end', 'is_fiscal', 'notes',
    ];

    protected $casts = [
        'book_value_start'       => 'decimal:2',
        'depreciation_amount'    => 'decimal:2',
        'accumulated_depreciation' => 'decimal:2',
        'book_value_end'         => 'decimal:2',
        'is_fiscal'              => 'boolean',
    ];

    const METHODS = [
        'linea_recta'  => 'Línea recta',
        'doble_saldo'  => 'Doble saldo decreciente',
        'suma_digitos' => 'Suma de dígitos',
        'fiscal'       => 'Fiscal SAT',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(FixedAsset::class, 'fixed_asset_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function getPeriodLabelAttribute(): string
    {
        $months = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
        return ($months[$this->month - 1] ?? '') . ' ' . $this->year;
    }
}

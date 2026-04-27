<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TenderCatalogItem extends Model
{
    use BelongsToCompany, SoftDeletes;

    protected $fillable = [
        'company_id', 'category_id', 'code', 'name', 'unit',
        'description', 'indirect_pct', 'overhead_pct', 'utility_pct',
    ];

    protected $casts = [
        'indirect_pct' => 'float',
        'overhead_pct' => 'float',
        'utility_pct'  => 'float',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TenderCatalogCategory::class, 'category_id');
    }

    public function resources(): HasMany
    {
        return $this->hasMany(TenderCatalogResource::class, 'item_id');
    }

    /** Costo directo = suma de todos los insumos */
    public function getDirectCostAttribute(): float
    {
        return $this->resources->sum(fn($r) => $r->quantity * $r->unit_cost);
    }

    /** Precio unitario final aplicando indirectos, overhead y utilidad */
    public function getUnitPriceAttribute(): float
    {
        $direct = $this->directCost;
        $factor = 1 + ($this->indirect_pct + $this->overhead_pct + $this->utility_pct) / 100;
        return round($direct * $factor, 2);
    }
}

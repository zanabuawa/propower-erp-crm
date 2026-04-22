<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectBudgetLine extends Model
{
    protected $fillable = [
        'project_id', 'version_id', 'category', 'concept', 'description',
        'unit', 'quantity', 'unit_cost', 'budgeted_amount', 'notes', 'sort_order',
    ];

    protected $casts = [
        'quantity'         => 'decimal:4',
        'unit_cost'        => 'decimal:4',
        'budgeted_amount'  => 'decimal:2',
        'sort_order'       => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(ProjectBudgetVersion::class, 'version_id');
    }

    public static array $categoryLabels = [
        'material'     => 'Materiales',
        'mano_obra'    => 'Mano de obra',
        'subcontrato'  => 'Subcontratos',
        'viaticos'     => 'Viáticos',
        'indirectos'   => 'Costos indirectos',
        'otros'        => 'Otros',
    ];
}

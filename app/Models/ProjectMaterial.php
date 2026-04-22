<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectMaterial extends Model
{
    protected $fillable = [
        'project_id', 'task_id', 'product_id', 'name', 'resource_type',
        'unit', 'quantity_needed', 'quantity_used', 'unit_cost', 'status', 'notes',
    ];

    protected $casts = [
        'quantity_needed' => 'decimal:4',
        'quantity_used'   => 'decimal:4',
        'unit_cost'       => 'decimal:4',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(ProjectTask::class, 'task_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function purchaseRequisition(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequisition::class);
    }

    public function getTotalCostAttribute(): float
    {
        return (float) ($this->quantity_needed * $this->unit_cost);
    }

    public static array $typeLabels = [
        'material'    => 'Material',
        'equipo'      => 'Equipo',
        'herramienta' => 'Herramienta',
        'otro'        => 'Otro',
    ];

    public static array $statusLabels = [
        'pendiente'  => 'Pendiente',
        'solicitado' => 'Solicitado',
        'adquirido'  => 'Adquirido',
        'utilizado'  => 'Utilizado',
        'devuelto'   => 'Devuelto',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectMaterial extends Model
{
    protected $fillable = [
        'project_id', 'task_id', 'product_id', 'warehouse_id', 'name', 'resource_type',
        'unit', 'quantity_needed', 'quantity_reserved', 'quantity_used', 'unit_cost', 'status', 'notes',
        'purchase_requisition_id',
    ];

    protected $casts = [
        'quantity_needed'   => 'decimal:4',
        'quantity_reserved' => 'decimal:4',
        'quantity_used'     => 'decimal:4',
        'unit_cost'         => 'decimal:4',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Warehouse::class);
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
        'reservado'  => 'Reservado en almacén',
        'solicitado' => 'Solicitado (compra)',
        'adquirido'  => 'Adquirido',
        'utilizado'  => 'Utilizado',
        'devuelto'   => 'Devuelto',
    ];

    public static array $statusColors = [
        'pendiente'  => 'bg-gray-100 text-gray-600',
        'reservado'  => 'bg-blue-50 text-blue-700',
        'solicitado' => 'bg-amber-50 text-amber-700',
        'adquirido'  => 'bg-emerald-50 text-emerald-700',
        'utilizado'  => 'bg-indigo-50 text-indigo-700',
        'devuelto'   => 'bg-slate-100 text-slate-600',
    ];

    /** Cantidad aún no cubierta (ni reservada en stock ni en compra) */
    public function getPendingQuantityAttribute(): float
    {
        return max(0, (float) $this->quantity_needed - (float) $this->quantity_reserved);
    }
}

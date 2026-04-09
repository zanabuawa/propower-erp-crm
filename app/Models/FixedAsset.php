<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FixedAsset extends Model
{
    protected $fillable = [
        'company_id', 'branch_id', 'warehouse_id', 'assigned_to',
        'folio', 'name', 'category', 'brand', 'model', 'serial_number',
        'description', 'acquisition_date', 'acquisition_cost',
        'status', 'notes', 'is_active',
    ];

    protected $casts = [
        'acquisition_date' => 'date',
        'acquisition_cost' => 'decimal:2',
        'is_active'        => 'boolean',
    ];

    const CATEGORIES = [
        'computadora'   => 'Computadora / Electrónico',
        'vehiculo'      => 'Vehículo',
        'mobiliario'    => 'Mobiliario y equipo de oficina',
        'maquinaria'    => 'Maquinaria e industria',
        'herramienta'   => 'Herramienta',
        'comunicacion'  => 'Equipo de comunicación',
        'otro'          => 'Otro',
    ];

    const STATUSES = [
        'active'          => 'Activo',
        'in_maintenance'  => 'En mantenimiento',
        'transferred'     => 'Transferido',
        'retired'         => 'Dado de baja',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(AssetTransfer::class, 'asset_id');
    }

    public static function generateFolio(int $companyId): string
    {
        $count = self::where('company_id', $companyId)->count() + 1;
        return 'ACT-' . str_pad($count, 6, '0', STR_PAD_LEFT);
    }
}

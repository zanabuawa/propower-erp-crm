<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetTransfer extends Model
{
    protected $fillable = [
        'company_id', 'asset_id',
        'from_branch_id', 'to_branch_id',
        'from_warehouse_id', 'to_warehouse_id',
        'from_user_id', 'to_user_id',
        'requested_by', 'folio', 'status',
        'reason', 'notes', 'transferred_at',
    ];

    protected $casts = [
        'transferred_at' => 'datetime',
    ];

    const STATUSES = [
        'completed'  => 'Completada',
        'cancelled'  => 'Cancelada',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(FixedAsset::class, 'asset_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function fromBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    public function toBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    public function fromWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public static function generateFolio(int $companyId): string
    {
        $count = self::where('company_id', $companyId)->count() + 1;
        return 'TRA-AF-' . str_pad($count, 6, '0', STR_PAD_LEFT);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierPhone extends Model
{
    protected $fillable = ['supplier_id', 'number', 'type', 'is_primary'];
    protected $casts = ['is_primary' => 'boolean'];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}
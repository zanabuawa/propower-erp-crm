<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierEmail extends Model
{
    protected $fillable = ['supplier_id', 'email', 'type', 'is_primary'];
    protected $casts = ['is_primary' => 'boolean'];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}
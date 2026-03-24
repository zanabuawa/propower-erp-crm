<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerEmail extends Model
{
    protected $fillable = ['customer_id', 'email', 'type', 'is_primary'];
    protected $casts = ['is_primary' => 'boolean'];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
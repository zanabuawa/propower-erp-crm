<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerContact extends Model
{
    protected $fillable = [
        'customer_id', 'first_name', 'last_name', 'position',
        'phone', 'email', 'image', 'is_primary',
    ];

    protected $casts = ['is_primary' => 'boolean'];

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
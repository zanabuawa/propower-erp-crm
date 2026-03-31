<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerContact extends Model
{
    protected $fillable = [
        'customer_id',
        'first_name',
        'alias',
        'paternal_surname',
        'maternal_surname',
        'position',
        'phone',
        'email',
        'image',
        'is_primary',
        'description',
    ];

    protected $casts = ['is_primary' => 'boolean'];

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->paternal_surname} {$this->maternal_surname}");
    }

    public function getDisplayNameAttribute(): string
    {
        $name = $this->alias ?: $this->first_name;
        $surname = trim("{$this->paternal_surname} {$this->maternal_surname}");
        return trim("{$name} {$surname}");
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}

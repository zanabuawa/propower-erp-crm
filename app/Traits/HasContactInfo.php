<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasContactInfo
{
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function getPrimaryPhoneAttribute(): ?string
    {
        return $this->phones->firstWhere('is_primary', true)?->number
            ?? $this->phones->first()?->number;
    }

    public function getPrimaryEmailAttribute(): ?string
    {
        return $this->emails->firstWhere('is_primary', true)?->email
            ?? $this->emails->first()?->email;
    }
}
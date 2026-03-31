<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use App\Traits\HasContactInfo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use BelongsToCompany, HasContactInfo, SoftDeletes;

    protected $fillable = [
        'company_id',
        'assigned_to',
        'name',
        'rfc',
        'tax_regime',
        'anniversary_date',
        'image',
        'address',
        'city',
        'state',
        'country',
        'zip_code',
        'website',
        'credit_limit',
        'payment_terms',
        'status',
        'description',
    ];

    protected $casts = [
        'anniversary_date' => 'date',
        'credit_limit' => 'decimal:2',
    ];

    const STATUS = [
        'prospect' => 'Prospecto',
        'active' => 'Activo',
        'inactive' => 'Inactivo',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function phones(): HasMany
    {
        return $this->hasMany(CustomerPhone::class);
    }

    public function emails(): HasMany
    {
        return $this->hasMany(CustomerEmail::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(CustomerContact::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(CustomerNote::class)->latest('noted_at');
    }
    public function priceLists()
    {
        return $this->belongsToMany(PriceList::class);
    }
}
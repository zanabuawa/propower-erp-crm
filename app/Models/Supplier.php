<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use App\Traits\HasContactInfo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use BelongsToCompany, HasContactInfo, SoftDeletes;

    protected $fillable = [
        'company_id', 'assigned_to', 'type', 'name', 'internal_code', 'rfc', 'tax_regime',
        'image', 'address', 'city', 'state', 'country', 'zip_code', 'website',
        'credit_limit', 'payment_terms', 'bank_name', 'bank_account', 'bank_clabe',
        'status', 'description',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
    ];

    const STATUS = [
        'active'   => 'Activo',
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
        return $this->hasMany(SupplierPhone::class);
    }

    public function emails(): HasMany
    {
        return $this->hasMany(SupplierEmail::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(SupplierContact::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(SupplierNote::class)->latest('noted_at');
    }

    public function bankAccounts(): HasMany
{
    return $this->hasMany(SupplierBankAccount::class);
}
}
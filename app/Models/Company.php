<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    const FISCAL_REGIMES = [
        '601' => '601 - General de Ley Personas Morales',
        '603' => '603 - Personas Morales con Fines no Lucrativos',
        '606' => '606 - Arrendamiento',
        '612' => '612 - Personas Físicas con Actividades Empresariales y Profesionales',
        '616' => '616 - Sin obligaciones fiscales',
        '621' => '621 - Incorporación Fiscal',
        '625' => '625 - Actividades Empresariales con ingresos a través de Plataformas Tecnológicas',
        '626' => '626 - Régimen Simplificado de Confianza (RESICO)',
    ];

    protected $fillable = [
        'name',
        'legal_name',
        'rfc',
        'fiscal_regime',
        'fiscal_postal_code',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'country',
        'logo',
        'icon',
        'print_logo',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}

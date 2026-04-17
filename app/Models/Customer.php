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

    const CFDI_USES = [
        'G01' => 'G01 - Adquisición de mercancias',
        'G02' => 'G02 - Devoluciones, descuentos o bonificaciones',
        'G03' => 'G03 - Gastos en general',
        'I01' => 'I01 - Construcciones',
        'I04' => 'I04 - Equipo de computo y accesorios',
        'I06' => 'I06 - Comunicaciones telefónicas',
        'S01' => 'S01 - Sin efectos fiscales',
        'CP01' => 'CP01 - Pagos',
        'CN01' => 'CN01 - Nómina',
    ];

    const TAX_REGIMES = [
        '601' => '601 - General de Ley Personas Morales',
        '603' => '603 - Personas Morales con Fines no Lucrativos',
        '606' => '606 - Arrendamiento',
        '612' => '612 - Personas Físicas con Actividades Empresariales y Profesionales',
        '616' => '616 - Sin obligaciones fiscales',
        '621' => '621 - Incorporación Fiscal',
        '625' => '625 - Actividades Empresariales con ingresos a través de Plataformas Tecnológicas',
        '626' => '626 - Régimen Simplificado de Confianza (RESICO)',
    ];

    public const SEGMENTS = [
        'A' => 'A — Clave',
        'B' => 'B — Alto valor',
        'C' => 'C — Medio',
        'D' => 'D — Bajo',
    ];

    public const CATEGORIES = [
        'distribuidor'  => 'Distribuidor',
        'mayorista'     => 'Mayorista',
        'minorista'     => 'Minorista',
        'usuario_final' => 'Usuario final',
        'gobierno'      => 'Gobierno',
        'otro'          => 'Otro',
    ];

    protected $fillable = [
        'company_id',
        'assigned_to',
        'name',
        'rfc',
        'tax_regime',
        'cfdi_use',
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
        'segment',
        'zone',
        'customer_category',
        'annual_revenue',
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
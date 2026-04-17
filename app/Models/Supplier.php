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
        'company_id', 'assigned_to', 'type', 'service_type', 'supplier_category',
        'name', 'internal_code', 'rfc', 'tax_regime',
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

    const SERVICE_TYPES = [
        'product_supplier'    => 'Proveedor de materiales / productos',
        'service_contractor'  => 'Contratista de servicios',
        'both'                => 'Materiales y servicios',
    ];

    const CATEGORIES = [
        'electrical_materials'   => 'Materiales eléctricos',
        'construction_materials' => 'Materiales de construcción',
        'tools_equipment'        => 'Herramientas y equipo',
        'ppe_safety'             => 'Equipo de protección personal (EPP)',
        'electrical_installation'=> 'Instalación eléctrica',
        'civil_works'            => 'Obra civil',
        'mechanical_installation'=> 'Instalación mecánica',
        'hvac'                   => 'Climatización (HVAC)',
        'automation'             => 'Automatización e instrumentación',
        'telecommunications'     => 'Telecomunicaciones y redes',
        'lighting'               => 'Iluminación',
        'generators_ups'         => 'Generadores y UPS',
        'transformers'           => 'Transformadores y subestaciones',
        'engineering_consulting' => 'Ingeniería y consultoría',
        'transport_logistics'    => 'Transporte y logística',
        'rental_equipment'       => 'Renta de equipo y maquinaria',
        'professional_services'  => 'Servicios profesionales',
        'office_supplies'        => 'Papelería y consumibles',
        'other'                  => 'Otro',
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

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class)->latest();
    }

    public function purchaseInvoices(): HasMany
    {
        return $this->hasMany(PurchaseInvoice::class)->latest();
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(SupplierEvaluation::class)->latest('evaluated_at');
    }

    public function getAverageScoreAttribute(): ?float
    {
        $avg = $this->evaluations()->avg('score_overall');
        return $avg ? round((float) $avg, 2) : null;
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesProspect extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id', 'assigned_to', 'name', 'contact_name', 'contact_email',
        'contact_phone', 'contact_position', 'source', 'status', 'estimated_value',
        'city', 'state', 'description', 'next_follow_up',
        'converted_at', 'converted_to_customer_id',
    ];

    protected $casts = [
        'next_follow_up' => 'date',
        'converted_at'   => 'datetime',
        'estimated_value'=> 'decimal:2',
    ];

    const SOURCES = [
        'inbound'    => 'Inbound / web',
        'referral'   => 'Referido',
        'cold_call'  => 'Llamada en frío',
        'social'     => 'Redes sociales',
        'event'      => 'Evento / feria',
        'outbound'   => 'Campaña outbound',
        'other'      => 'Otro',
    ];

    const STATUSES = [
        'new'          => 'Nuevo',
        'contacted'    => 'Contactado',
        'qualified'    => 'Calificado',
        'disqualified' => 'Descalificado',
        'converted'    => 'Convertido',
    ];

    const STATUS_COLORS = [
        'new'          => 'bg-gray-100 text-gray-600 border-gray-200',
        'contacted'    => 'bg-blue-50 text-blue-700 border-blue-100',
        'qualified'    => 'bg-emerald-50 text-emerald-700 border-emerald-100',
        'disqualified' => 'bg-red-50 text-red-600 border-red-100',
        'converted'    => 'bg-indigo-50 text-indigo-700 border-indigo-100',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function convertedToCustomer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'converted_to_customer_id');
    }

    public function opportunities(): HasMany
    {
        return $this->hasMany(SalesOpportunity::class, 'prospect_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(CrmActivity::class, 'prospect_id')->latest('scheduled_at');
    }

    public function isOverdue(): bool
    {
        return $this->next_follow_up && $this->next_follow_up->isPast()
            && !in_array($this->status, ['converted', 'disqualified']);
    }
}

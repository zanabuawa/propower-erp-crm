<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TravelExpense extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id', 'branch_id', 'employee_id', 'assigned_by',
        'finance_account_id', 'finance_transaction_id', 'project_id',
        'folio', 'destination', 'purpose',
        'departure_date', 'return_date',
        'status', 'amount_approved', 'amount_spent', 'currency',
        'trip_confirmed', 'trip_confirmed_at', 'trip_confirmed_by',
        'notes', 'rejection_reason',
    ];

    protected $casts = [
        'departure_date'   => 'date',
        'return_date'      => 'date',
        'amount_approved'  => 'decimal:2',
        'amount_spent'     => 'decimal:2',
        'trip_confirmed'   => 'boolean',
        'trip_confirmed_at'=> 'datetime',
    ];

    public const STATUSES = [
        'borrador'   => 'Borrador',
        'aprobado'   => 'Aprobado',
        'pagado'     => 'Pagado',
        'comprobado' => 'Comprobado',
        'rechazado'  => 'Rechazado',
    ];

    public const STATUS_COLORS = [
        'borrador'   => 'bg-slate-100 text-slate-600 border-slate-200',
        'aprobado'   => 'bg-blue-50 text-blue-700 border-blue-100',
        'pagado'     => 'bg-emerald-50 text-emerald-700 border-emerald-100',
        'comprobado' => 'bg-violet-50 text-violet-700 border-violet-100',
        'rechazado'  => 'bg-red-50 text-red-600 border-red-100',
    ];

    public const ITEM_CATEGORIES = [
        'hospedaje'       => 'Hospedaje',
        'transporte'      => 'Transporte',
        'alimentacion'    => 'Alimentación',
        'viaticos_diarios'=> 'Viáticos diarios',
        'combustible'     => 'Combustible',
        'peajes'          => 'Peajes / Casetas',
        'otro'            => 'Otro',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(HrEmployee::class, 'employee_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function financeAccount(): BelongsTo
    {
        return $this->belongsTo(FinanceAccount::class, 'finance_account_id');
    }

    public function financeTransaction(): BelongsTo
    {
        return $this->belongsTo(FinanceTransaction::class, 'finance_transaction_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function tripConfirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'trip_confirmed_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(TravelExpenseItem::class);
    }

    public function getTotalItemsProperty(): float
    {
        return (float) $this->items()->sum('amount');
    }

    public function getDifferenceProperty(): float
    {
        return (float) $this->amount_approved - (float) ($this->amount_spent ?? 0);
    }

    public function getDaysProperty(): int
    {
        return (int) $this->departure_date->diffInDays($this->return_date) + 1;
    }
}

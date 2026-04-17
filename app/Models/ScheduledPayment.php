<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScheduledPayment extends Model
{
    use BelongsToCompany, SoftDeletes;

    protected $fillable = [
        'company_id', 'finance_account_id', 'supplier_id', 'purchase_invoice_id',
        'created_by', 'executed_by', 'finance_transaction_id',
        'folio', 'concept', 'category', 'frequency', 'status',
        'amount', 'currency', 'exchange_rate',
        'scheduled_date', 'end_date', 'paid_at',
        'reference', 'notes',
    ];

    protected $casts = [
        'amount'         => 'decimal:2',
        'exchange_rate'  => 'decimal:6',
        'scheduled_date' => 'date',
        'end_date'       => 'date',
        'paid_at'        => 'datetime',
    ];

    public const STATUS = [
        'pending'   => 'Pendiente',
        'overdue'   => 'Vencido',
        'paid'      => 'Pagado',
        'cancelled' => 'Cancelado',
    ];

    public const STATUS_COLORS = [
        'pending'   => 'bg-blue-50 text-blue-700',
        'overdue'   => 'bg-red-100 text-red-700',
        'paid'      => 'bg-green-50 text-green-700',
        'cancelled' => 'bg-gray-100 text-gray-500',
    ];

    public const CATEGORIES = [
        'proveedor'  => 'Proveedor',
        'nomina'     => 'Nómina',
        'impuesto'   => 'Impuesto',
        'servicio'   => 'Servicio',
        'prestamo'   => 'Préstamo',
        'inversion'  => 'Inversión',
        'otro'       => 'Otro',
    ];

    public const FREQUENCIES = [
        'once'      => 'Una sola vez',
        'weekly'    => 'Semanal',
        'biweekly'  => 'Quincenal',
        'monthly'   => 'Mensual',
        'quarterly' => 'Trimestral',
        'annual'    => 'Anual',
    ];

    // Calcula la siguiente fecha según la frecuencia
    public function nextOccurrence(): ?Carbon
    {
        if ($this->frequency === 'once' || ! $this->scheduled_date) return null;

        $next = $this->scheduled_date->copy();

        return match($this->frequency) {
            'weekly'    => $next->addWeek(),
            'biweekly'  => $next->addWeeks(2),
            'monthly'   => $next->addMonth(),
            'quarterly' => $next->addMonths(3),
            'annual'    => $next->addYear(),
            default     => null,
        };
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'pending'
            && $this->scheduled_date
            && $this->scheduled_date->isPast();
    }

    // ── Relaciones ──────────────────────────────────────────────────────

    public function financeAccount(): BelongsTo
    {
        return $this->belongsTo(FinanceAccount::class, 'finance_account_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseInvoice(): BelongsTo
    {
        return $this->belongsTo(PurchaseInvoice::class, 'purchase_invoice_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function executedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'executed_by');
    }

    public function financeTransaction(): BelongsTo
    {
        return $this->belongsTo(FinanceTransaction::class, 'finance_transaction_id');
    }
}

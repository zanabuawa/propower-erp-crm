<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancePeriodClose extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id', 'closed_by', 'reopened_by',
        'year', 'month', 'period_label', 'status',
        'total_income', 'total_expense', 'net_result',
        'opening_cash', 'closing_cash',
        'checklist', 'notes', 'closed_at', 'reopened_at',
    ];

    protected $casts = [
        'checklist'    => 'array',
        'closed_at'    => 'datetime',
        'reopened_at'  => 'datetime',
        'total_income' => 'decimal:2',
        'total_expense'=> 'decimal:2',
        'net_result'   => 'decimal:2',
        'opening_cash' => 'decimal:2',
        'closing_cash' => 'decimal:2',
    ];

    public const STATUS = [
        'open'      => 'Abierto',
        'reviewing' => 'En revisión',
        'closed'    => 'Cerrado',
    ];

    public const STATUS_COLORS = [
        'open'      => 'bg-blue-50 text-blue-700',
        'reviewing' => 'bg-amber-50 text-amber-700',
        'closed'    => 'bg-green-50 text-green-700',
    ];

    // Checklist estándar de cierre mensual
    public static function defaultChecklist(): array
    {
        return [
            ['key' => 'invoices_reconciled',   'label' => 'Facturas de clientes conciliadas',         'done' => false, 'done_at' => null, 'done_by' => null],
            ['key' => 'supplier_inv_reconciled','label' => 'Facturas de proveedores conciliadas',      'done' => false, 'done_at' => null, 'done_by' => null],
            ['key' => 'payments_reconciled',   'label' => 'Cobros y pagos conciliados con banco',      'done' => false, 'done_at' => null, 'done_by' => null],
            ['key' => 'pending_invoices',      'label' => 'Sin facturas pendientes de cobro vencidas', 'done' => false, 'done_at' => null, 'done_by' => null],
            ['key' => 'pending_payables',      'label' => 'Sin cuentas por pagar vencidas críticas',   'done' => false, 'done_at' => null, 'done_by' => null],
            ['key' => 'cashflow_reviewed',     'label' => 'Flujo de caja revisado y actualizado',      'done' => false, 'done_at' => null, 'done_by' => null],
            ['key' => 'budget_variance',       'label' => 'Varianza vs presupuesto analizada',         'done' => false, 'done_at' => null, 'done_by' => null],
            ['key' => 'balances_verified',     'label' => 'Saldos bancarios verificados',              'done' => false, 'done_at' => null, 'done_by' => null],
        ];
    }

    public function getProgressAttribute(): int
    {
        $checklist = $this->checklist ?? [];
        if (empty($checklist)) return 0;
        $done = collect($checklist)->where('done', true)->count();
        return (int) round($done / count($checklist) * 100);
    }

    public function getCanCloseAttribute(): bool
    {
        return $this->status !== 'closed'
            && collect($this->checklist ?? [])->every(fn($item) => $item['done']);
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function reopenedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reopened_by');
    }
}

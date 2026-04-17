<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierPayment extends Model
{
    protected $fillable = [
        'company_id', 'purchase_invoice_id', 'supplier_id', 'created_by',
        'finance_account_id', 'folio', 'currency', 'payment_method',
        'status', 'amount', 'reference', 'notes', 'paid_at',
        'reconciled_at', 'reconciled_by', 'reconciliation_note',
    ];

    protected $casts = [
        'amount'        => 'decimal:2',
        'paid_at'       => 'datetime',
        'reconciled_at' => 'datetime',
    ];

    public const STATUS = [
        'pending'   => 'Pendiente',
        'applied'   => 'Aplicado',
        'cancelled' => 'Cancelado',
    ];

    public const PAYMENT_METHODS = [
        'transfer'    => 'Transferencia',
        'check'       => 'Cheque',
        'cash'        => 'Efectivo',
        'credit_card' => 'Tarjeta de crédito',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(PurchaseInvoice::class, 'purchase_invoice_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function financeAccount(): BelongsTo
    {
        return $this->belongsTo(FinanceAccount::class, 'finance_account_id');
    }

    public function reconciledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reconciled_by');
    }

    public function getIsReconciledAttribute(): bool
    {
        return $this->reconciled_at !== null;
    }
}

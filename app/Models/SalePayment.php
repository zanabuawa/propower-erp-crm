<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalePayment extends Model
{
    protected $fillable = [
        'company_id', 'sale_invoice_id', 'customer_id', 'created_by',
        'finance_account_id',
        'folio', 'currency', 'payment_method', 'status',
        'amount', 'reference', 'notes', 'paid_at',
    ];

    protected $casts = [
        'amount'  => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    const STATUS = [
        'pending'   => 'Pendiente',
        'applied'   => 'Aplicado',
        'cancelled' => 'Cancelado',
    ];

    const PAYMENT_METHODS = [
        'cash'     => 'Efectivo',
        'transfer' => 'Transferencia',
        'card'     => 'Tarjeta',
        'check'    => 'Cheque',
        'credit'   => 'Crédito',
    ];

    public function invoice(): BelongsTo        { return $this->belongsTo(SaleInvoice::class, 'sale_invoice_id'); }
    public function customer(): BelongsTo       { return $this->belongsTo(Customer::class); }
    public function createdBy(): BelongsTo      { return $this->belongsTo(User::class, 'created_by'); }
    public function financeAccount(): BelongsTo { return $this->belongsTo(FinanceAccount::class, 'finance_account_id'); }
}
<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use BelongsToCompany, SoftDeletes;

    protected $fillable = [
        'company_id', 'branch_id', 'supplier_id', 'purchase_requisition_id', 'project_id',
        'created_by', 'folio', 'currency', 'status', 'subtotal', 'tax',
        'total', 'paid_amount', 'payment_terms', 'supplier_bank_account_id', 'notes',
        'expected_at', 'required_at',
        'shipping_address', 'billing_address', 'print_language',
    ];

    protected $casts = [
        'subtotal'    => 'decimal:2',
        'tax'         => 'decimal:2',
        'total'       => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'expected_at' => 'datetime',
        'required_at' => 'date',
    ];

    // Orden lógico del flujo (para el timeline)
    const STATUS_FLOW = [
        'draft', 'sent', 'waiting_delivery',
        'partial_received', 'received', 'invoiced', 'paid',
    ];

    const STATUS = [
        'draft'            => 'Borrador',
        'sent'             => 'Enviada al proveedor',
        'waiting_delivery' => 'Esperando mercancía',
        'partial_received' => 'Recepción parcial',
        'received'         => 'Mercancía recibida',
        'invoiced'         => 'Facturada',
        'paid'             => 'Pagada',
        'cancelled'        => 'Cancelada',
    ];

    const STATUS_COLORS = [
        'draft'            => 'bg-gray-100 text-gray-600',
        'sent'             => 'bg-blue-50 text-blue-700',
        'waiting_delivery' => 'bg-violet-50 text-violet-700',
        'partial_received' => 'bg-amber-50 text-amber-700',
        'received'         => 'bg-teal-50 text-teal-700',
        'invoiced'         => 'bg-indigo-50 text-indigo-700',
        'paid'             => 'bg-emerald-50 text-emerald-700',
        'cancelled'        => 'bg-red-50 text-red-700',
    ];

    public function getBalanceAttribute(): float
    {
        return max(0, (float) $this->total - (float) $this->paid_amount);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function requisition(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequisition::class, 'purchase_requisition_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function supplierBankAccount(): BelongsTo
    {
        return $this->belongsTo(SupplierBankAccount::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(PurchaseReceipt::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(PurchaseInvoice::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(PurchaseInvoice::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(PurchaseReturn::class);
    }
}
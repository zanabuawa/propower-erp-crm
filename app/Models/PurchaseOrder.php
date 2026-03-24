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
        'company_id', 'branch_id', 'supplier_id', 'purchase_requisition_id',
        'created_by', 'folio', 'currency', 'status', 'subtotal', 'tax',
        'total', 'payment_terms', 'supplier_bank_account_id', 'notes', 'expected_at',
    ];

    protected $casts = [
        'subtotal'    => 'decimal:2',
        'tax'         => 'decimal:2',
        'total'       => 'decimal:2',
        'expected_at' => 'datetime',
    ];

    const STATUS = [
        'draft'            => 'Borrador',
        'sent'             => 'Enviada',
        'partial_received' => 'Recepción parcial',
        'received'         => 'Recibida',
        'invoiced'         => 'Facturada',
        'cancelled'        => 'Cancelada',
    ];

    const STATUS_COLORS = [
        'draft'            => 'bg-gray-100 text-gray-600',
        'sent'             => 'bg-blue-50 text-blue-700',
        'partial_received' => 'bg-amber-50 text-amber-700',
        'received'         => 'bg-teal-50 text-teal-700',
        'invoiced'         => 'bg-green-50 text-green-700',
        'cancelled'        => 'bg-red-50 text-red-700',
    ];

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

    public function returns(): HasMany
    {
        return $this->hasMany(PurchaseReturn::class);
    }
}
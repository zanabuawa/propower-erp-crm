<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmTicket extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id', 'customer_id', 'assigned_to', 'created_by',
        'sale_order_id', 'sale_invoice_id',
        'folio', 'subject', 'description',
        'type', 'priority', 'status',
        'resolved_at', 'closed_at', 'due_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'closed_at'   => 'datetime',
        'due_at'      => 'datetime',
    ];

    const TYPES = [
        'internal'  => 'Interno',
        'support'   => 'Soporte técnico',
        'warranty'  => 'Garantía',
        'complaint' => 'Queja',
        'inquiry'   => 'Consulta',
        'return'    => 'Devolución',
    ];

    const TYPE_COLORS = [
        'internal'  => 'bg-violet-50 text-violet-700 border-violet-100',
        'support'   => 'bg-blue-50 text-blue-700 border-blue-100',
        'warranty'  => 'bg-amber-50 text-amber-700 border-amber-100',
        'complaint' => 'bg-red-50 text-red-700 border-red-100',
        'inquiry'   => 'bg-gray-50 text-gray-600 border-gray-200',
        'return'    => 'bg-orange-50 text-orange-700 border-orange-100',
    ];

    const PRIORITIES = [
        'low'    => 'Baja',
        'medium' => 'Media',
        'high'   => 'Alta',
        'urgent' => 'Urgente',
    ];

    const PRIORITY_COLORS = [
        'low'    => 'bg-gray-100 text-gray-500',
        'medium' => 'bg-blue-100 text-blue-700',
        'high'   => 'bg-amber-100 text-amber-700',
        'urgent' => 'bg-red-100 text-red-700',
    ];

    const STATUSES = [
        'open'        => 'Abierto',
        'in_progress' => 'En progreso',
        'waiting'     => 'En espera',
        'resolved'    => 'Resuelto',
        'closed'      => 'Cerrado',
    ];

    const STATUS_COLORS = [
        'open'        => 'bg-blue-50 text-blue-700 border-blue-100',
        'in_progress' => 'bg-indigo-50 text-indigo-700 border-indigo-100',
        'waiting'     => 'bg-amber-50 text-amber-700 border-amber-100',
        'resolved'    => 'bg-emerald-50 text-emerald-700 border-emerald-100',
        'closed'      => 'bg-gray-100 text-gray-500 border-gray-200',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function saleOrder(): BelongsTo
    {
        return $this->belongsTo(SaleOrder::class);
    }

    public function saleInvoice(): BelongsTo
    {
        return $this->belongsTo(SaleInvoice::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(CrmTicketMessage::class, 'ticket_id')->orderBy('created_at');
    }

    public function isOpen(): bool
    {
        return in_array($this->status, ['open', 'in_progress', 'waiting']);
    }

    public function isOverdue(): bool
    {
        return $this->due_at && $this->due_at->isPast() && $this->isOpen();
    }
}

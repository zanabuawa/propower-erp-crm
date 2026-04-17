<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
// FinanceAccount and FinanceTransaction resolved at runtime via Eloquent

class StockMovement extends Model
{
    protected $fillable = [
        'company_id', 'warehouse_id', 'warehouse_destination_id',
        'user_id', 'type', 'folio', 'status', 'reference', 'notes', 'moved_at',
        'dispatch_notes', 'dispatched_by', 'dispatch_is_final',
        'adjustment_reason', 'approved_by', 'approved_at',
        'finance_account_id', 'finance_transaction_id',
    ];

    protected $casts = [
        'moved_at'         => 'datetime',
        'approved_at'      => 'datetime',
        'dispatch_is_final' => 'boolean',
    ];

    const ADJUSTMENT_REASONS = [
        'merma'          => 'Merma / pérdida natural',
        'rotura'         => 'Rotura / daño físico',
        'conteo_fisico'  => 'Conteo físico / inventario',
        'vencimiento'    => 'Producto vencido',
        'robo'           => 'Robo o extravío',
        'devolucion'     => 'Devolución de cliente',
        'correccion'     => 'Corrección de error',
        'donacion'       => 'Donación / muestra',
        'otro'           => 'Otro motivo',
    ];

    // Ajustes que requieren aprobación (montos o diferencias significativas)
    const REQUIRES_APPROVAL_REASONS = ['robo', 'merma', 'vencimiento'];

    public const TYPES = [
        'entry'        => 'Entrada',
        'exit'         => 'Salida',
        'adjustment'   => 'Ajuste',
        'transfer'     => 'Transferencia',
        'return'       => 'Devolución',
    ];

    public const STATUS = [
        'draft'               => 'Borrador',
        'confirmed'           => 'Confirmado',
        'cancelled'           => 'Cancelado',
        // Transfer-specific statuses
        'requested'           => 'Solicitada',
        'accepted'            => 'Aceptada',
        'in_transit'          => 'Enviada',
        'partially_received'  => 'Recepción parcial',
        'completed'           => 'Completada',
    ];

    public const TRANSFER_STATUSES = [
        'requested'          => 'Solicitada',
        'accepted'           => 'Aceptada',
        'in_transit'         => 'Enviada',
        'partially_received' => 'Recepción parcial',
        'completed'          => 'Completada',
        'cancelled'          => 'Cancelada',
        'rejected'           => 'Rechazada',
    ];

    public function isTransfer(): bool
    {
        return $this->type === 'transfer';
    }

    public function isEditable(): bool
    {
        return in_array($this->status, ['requested', 'accepted', 'in_transit', 'partially_received']);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function warehouseDestination(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_destination_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function dispatchedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispatched_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockMovementItem::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(StockMovementEvent::class)->orderBy('created_at');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function financeAccount(): BelongsTo
    {
        return $this->belongsTo(FinanceAccount::class, 'finance_account_id');
    }

    public function financeTransaction(): BelongsTo
    {
        return $this->belongsTo(FinanceTransaction::class, 'finance_transaction_id');
    }

    public function isPendingApproval(): bool
    {
        return $this->status === 'pending_approval';
    }

    public function requiresApproval(): bool
    {
        return $this->type === 'adjustment'
            && in_array($this->adjustment_reason, self::REQUIRES_APPROVAL_REASONS, true);
    }
}
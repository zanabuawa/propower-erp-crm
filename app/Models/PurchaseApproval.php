<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseApproval extends Model
{
    protected $fillable = [
        'purchase_requisition_id', 'user_id', 'role',
        'status', 'comments', 'responded_at',
    ];

    protected $casts = ['responded_at' => 'datetime'];

    const ROLES = [
        'compras'        => 'Compras',
        'administracion' => 'Administración',
        'gerencia'       => 'Gerencia',
    ];

    public function requisition(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequisition::class, 'purchase_requisition_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
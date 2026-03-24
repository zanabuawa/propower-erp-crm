<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierNote extends Model
{
    protected $fillable = ['supplier_id', 'user_id', 'type', 'title', 'body', 'noted_at'];
    protected $casts = ['noted_at' => 'datetime'];

    const TYPES = [
        'note'    => 'Nota',
        'call'    => 'Llamada',
        'email'   => 'Correo',
        'meeting' => 'Reunión',
        'task'    => 'Tarea',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
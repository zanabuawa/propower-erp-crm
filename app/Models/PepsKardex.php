<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PepsKardex extends Model
{
    protected $table = 'peps_kardex';

    protected $fillable = [
        'company_id', 'product_id', 'lot_id', 'warehouse_id',
        'movement_type', 'direction',
        'quantity',
        'unit_cost', 'total_cost',
        'unit_price', 'total_revenue',
        'profit', 'profit_pct',
        'balance_quantity', 'balance_value',
        'reference', 'lot_number', 'moved_at', 'notes',
    ];

    protected $casts = [
        'quantity'         => 'decimal:4',
        'unit_cost'        => 'decimal:4',
        'total_cost'       => 'decimal:2',
        'unit_price'       => 'decimal:4',
        'total_revenue'    => 'decimal:2',
        'profit'           => 'decimal:2',
        'profit_pct'       => 'decimal:4',
        'balance_quantity' => 'decimal:4',
        'balance_value'    => 'decimal:2',
        'moved_at'         => 'datetime',
    ];

    // ── Tipos de movimiento ──────────────────────────────────────────────────

    const MOVEMENT_TYPES = [
        'purchase'        => ['label' => 'Compra / Entrada',          'direction' => 'in',  'color' => 'green'],
        'return_purchase' => ['label' => 'Devolución a proveedor',     'direction' => 'out', 'color' => 'orange'],
        'sale'            => ['label' => 'Venta',                      'direction' => 'out', 'color' => 'red'],
        'return_sale'     => ['label' => 'Devolución de cliente',       'direction' => 'in',  'color' => 'teal'],
        'adjustment_in'   => ['label' => 'Ajuste de entrada',          'direction' => 'in',  'color' => 'blue'],
        'adjustment_out'  => ['label' => 'Ajuste de salida',           'direction' => 'out', 'color' => 'blue'],
        'transfer_in'     => ['label' => 'Transferencia (entrada)',     'direction' => 'in',  'color' => 'violet'],
        'transfer_out'    => ['label' => 'Transferencia (salida)',      'direction' => 'out', 'color' => 'violet'],
        'internal_use'    => ['label' => 'Uso interno',                'direction' => 'out', 'color' => 'gray'],
        'scrap'           => ['label' => 'Merma / Desperdicio',         'direction' => 'out', 'color' => 'gray'],
        'other'           => ['label' => 'Otro',                       'direction' => 'out', 'color' => 'gray'],
    ];

    // ── Relaciones ───────────────────────────────────────────────────────────

    public function company(): BelongsTo   { return $this->belongsTo(Company::class); }
    public function product(): BelongsTo   { return $this->belongsTo(Product::class); }
    public function lot(): BelongsTo       { return $this->belongsTo(ProductLot::class, 'lot_id'); }
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
}

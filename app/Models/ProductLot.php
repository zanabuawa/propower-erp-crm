<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductLot extends Model
{
    protected $fillable = [
        'company_id', 'product_id', 'warehouse_id',
        'lot_number', 'barcode',
        'initial_quantity', 'quantity', 'unit_cost',
        'entry_date', 'expiry_date',
        'reference', 'status', 'notes',
    ];

    protected $casts = [
        'initial_quantity' => 'decimal:4',
        'quantity'         => 'decimal:4',
        'unit_cost'        => 'decimal:4',
        'entry_date'       => 'date',
        'expiry_date'      => 'date',
    ];

    const STATUSES = [
        'active'   => 'Activo',
        'depleted' => 'Agotado',
        'expired'  => 'Vencido',
    ];

    // ── Relaciones ───────────────────────────────────────────────────────────

    public function company(): BelongsTo   { return $this->belongsTo(Company::class); }
    public function product(): BelongsTo   { return $this->belongsTo(Product::class); }
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }

    public function movementItems(): HasMany
    {
        return $this->hasMany(StockMovementItem::class, 'lot_id');
    }

    public function deliveryItems(): HasMany
    {
        return $this->hasMany(SaleDeliveryItem::class, 'lot_id');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Genera número de lote único: LOT-{YYYYMMDD}-{SEQ:04d}
     */
    public static function generateLotNumber(int $companyId, int $productId): string
    {
        $date  = now()->format('Ymd');
        $count = static::where('company_id', $companyId)
            ->where('product_id', $productId)
            ->whereDate('entry_date', today())
            ->count();

        return 'LOT-' . $date . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Genera código de barras escaneable único (Code-128 compatible):
     * LOTE + company(4) + product(5) + seq(6)
     */
    public static function generateBarcode(int $companyId, int $productId): string
    {
        $seq = static::where('company_id', $companyId)->count() + 1;
        return 'LOTE'
            . str_pad($companyId, 4, '0', STR_PAD_LEFT)
            . str_pad($productId, 5, '0', STR_PAD_LEFT)
            . str_pad($seq, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Marca el lote como agotado si quantity <= 0
     */
    public function checkAndMarkDepleted(): void
    {
        if ((float) $this->quantity <= 0) {
            $this->update(['status' => 'depleted', 'quantity' => 0]);
        }
    }
}

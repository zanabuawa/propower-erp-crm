<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use BelongsToCompany;

    // Códigos SAT más comunes para productos y servicios eléctricos/tecnológicos
    const SAT_PRODUCT_CODES = [
        '43231500' => '43231500 - Equipo de cómputo y accesorios',
        '43211500' => '43211500 - Computadoras personales',
        '39121500' => '39121500 - Cables eléctricos y accesorios',
        '39122000' => '39122000 - Componentes eléctricos',
        '84111506' => '84111506 - Servicios de instalación eléctrica',
        '81112000' => '81112000 - Servicios de tecnología de la información',
        '01010101' => '01010101 - No existe en el catálogo',
    ];

    const SAT_UNIT_CODES = [
        'H87' => 'H87 - Pieza',
        'EA'  => 'EA  - Elemento',
        'MTR' => 'MTR - Metro',
        'KGM' => 'KGM - Kilogramo',
        'LTR' => 'LTR - Litro',
        'E48' => 'E48 - Unidad de servicio',
        'ACT' => 'ACT - Actividad',
        'MTS' => 'MTS - Metro cuadrado',
    ];

    protected $fillable = [
        'company_id', 'type', 'category_id', 'subcategory_id', 'unit_of_measure_id', 'supplier_id',
        'name', 'sku', 'sat_product_code', 'sat_unit_code', 'barcode', 'description',
        'brand', 'model', 'color',
        'purchase_price', 'profit_margin', 'operational_costs', 'sale_price',
        'min_stock', 'max_stock', 'is_active',
    ];

    protected $casts = [
        'is_active'          => 'boolean',
        'purchase_price'     => 'decimal:2',
        'profit_margin'      => 'decimal:4',
        'operational_costs'  => 'decimal:4',
        'sale_price'         => 'decimal:2',
        'min_stock'          => 'decimal:2',
        'max_stock'          => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'subcategory_id');
    }

    public function unitOfMeasure(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasure::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function getTotalStockAttribute(): float
    {
        return $this->stocks->sum('quantity');
    }

    /**
     * Precio normal de venta = precio_obtencion * (1 + margen% / 100)
     */
    public function getNormalSalePriceAttribute(): float
    {
        $purchase = (float) $this->purchase_price;
        $margin   = (float) $this->profit_margin;
        return round($purchase * (1 + $margin / 100), 2);
    }

    /**
     * Precio minimo de venta = precio_obtencion * (1 + gastos_operacion% / 100)
     * El precio con descuento nunca puede bajar de aqui.
     */
    public function getMinSalePriceAttribute(): float
    {
        $purchase = (float) $this->purchase_price;
        $opCosts  = (float) $this->operational_costs;
        return round($purchase * (1 + $opCosts / 100), 2);
    }

    /**
     * Descuento maximo que se puede aplicar sin perder capital.
     * max_discount = precio_normal - precio_minimo
     */
    public function getMaxDiscountAttribute(): float
    {
        return max(0, round($this->normal_sale_price - $this->min_sale_price, 2));
    }

    /**
     * Genera y asigna el sale_price calculado automaticamente.
     */
    public function computeSalePrice(): void
    {
        $this->sale_price = $this->normal_sale_price;
    }

    /**
     * Genera un SKU automatico basado en el nombre y un secuencial.
     */
    public static function generateSku(string $name, int $companyId): string
    {
        $prefix = strtoupper(
            preg_replace('/[^A-Z0-9]/', '', strtoupper(substr($name, 0, 3)))
        );
        $prefix = str_pad($prefix, 3, 'X');

        $last = static::where('company_id', $companyId)
            ->where('sku', 'like', $prefix . '-%')
            ->orderByDesc('id')
            ->value('sku');

        $seq = 1;
        if ($last && preg_match('/-(\d+)$/', $last, $m)) {
            $seq = (int) $m[1] + 1;
        }

        return $prefix . '-' . str_pad($seq, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Genera un codigo de barras EAN-13 unico.
     */
    public static function generateBarcode(int $companyId): string
    {
        do {
            // Prefijo 200 (uso interno) + company_id (3 dig) + random (6 dig)
            $base = '200'
                . str_pad($companyId % 1000, 3, '0', STR_PAD_LEFT)
                . str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);

            // Calculo del digito verificador EAN-13
            $sum = 0;
            for ($i = 0; $i < 12; $i++) {
                $sum += (int) $base[$i] * ($i % 2 === 0 ? 1 : 3);
            }
            $check = (10 - ($sum % 10)) % 10;
            $barcode = $base . $check;
        } while (static::where('company_id', $companyId)->where('barcode', $barcode)->exists());

        return $barcode;
    }
}

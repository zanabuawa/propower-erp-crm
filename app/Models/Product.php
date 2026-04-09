<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use BelongsToCompany;

    // -------------------------------------------------------------------------
    // Códigos SAT (ClaveProdServ) — Catálogo CFDI 4.0
    // Cubiertos para electroconstructora / contratista eléctrico
    // -------------------------------------------------------------------------
    const SAT_PRODUCT_CODES = [

        // -- CABLES Y ALAMBRADO -----------------------------------------------
        '39121500' => '39121500 - Cables, arneses y accesorios de cables',
        '39121501' => '39121501 - Cable eléctrico THW / THHW (cobre)',
        '39121502' => '39121502 - Cable concéntrico / acometida',
        '39121503' => '39121503 - Cable de alta tensión',
        '39121504' => '39121504 - Cable de control',
        '39121505' => '39121505 - Alambre de cobre para instalaciones',
        '39121506' => '39121506 - Cable coaxial',
        '39121507' => '39121507 - Cable de red UTP / FTP',
        '39121508' => '39121508 - Cable de fibra óptica',

        // -- TABLEROS Y EQUIPO DE DISTRIBUCIÓN --------------------------------
        '39101500' => '39101500 - Equipo de distribución de energía eléctrica',
        '39101501' => '39101501 - Tablero de distribución eléctrica',
        '39101502' => '39101502 - Centro de carga eléctrico',
        '39101503' => '39101503 - Subestación eléctrica',
        '39101504' => '39101504 - Transformador de distribución',
        '39101600' => '39101600 - Equipo de control de distribución de energía',
        '39101601' => '39101601 - Interruptor automático (breaker)',
        '39101602' => '39101602 - Contactor eléctrico',
        '39101603' => '39101603 - Guardamotor',
        '39101604' => '39101604 - Arrancador de motor eléctrico',
        '39101605' => '39101605 - Variador de velocidad / frecuencia',
        '39101606' => '39101606 - Relevador / relé de protección',

        // -- MOTORES, GENERADORES Y UPS ---------------------------------------
        '39112100' => '39112100 - Generadores de energía eléctrica',
        '39112101' => '39112101 - Generador eléctrico / planta de emergencia',
        '39112200' => '39112200 - Motores eléctricos',
        '39112201' => '39112201 - Motor eléctrico trifásico',
        '39112202' => '39112202 - Motor eléctrico monofásico',
        '39113200' => '39113200 - No break / UPS (alimentación ininterrumpida)',

        // -- COMPONENTES DE INSTALACIÓN ---------------------------------------
        '39122000' => '39122000 - Componentes y accesorios eléctricos',
        '39122001' => '39122001 - Interruptor termomagnético / fusible',
        '39122002' => '39122002 - Contacto / tomacorriente',
        '39122003' => '39122003 - Apagador / interruptor de pared',
        '39122004' => '39122004 - Cinta aislante y autofundente',
        '39122005' => '39122005 - Conectores y terminales eléctricas',
        '39122006' => '39122006 - Bornera / regleta de conexión',

        // -- TUBERÍA CONDUIT Y CANALIZACIÓN -----------------------------------
        '39122100' => '39122100 - Tubería conduit y accesorios',
        '39122101' => '39122101 - Conduit EMT (tubo de pared delgada)',
        '39122102' => '39122102 - Conduit rígido metálico / PVC',
        '39122103' => '39122103 - Conduit flexible metálico / PVC',
        '39122104' => '39122104 - Canaleta y trunking',
        '39122105' => '39122105 - Charola portacables',

        // -- CAJAS ELÉCTRICAS Y TAPAS -----------------------------------------
        '39122200' => '39122200 - Cajas eléctricas y tapas',
        '39122201' => '39122201 - Caja de paso / registro eléctrico',
        '39122202' => '39122202 - Caja condulet',
        '39122203' => '39122203 - Tapa ciega y accesorios de caja',

        // -- INSTRUMENTOS DE MEDICIÓN ELÉCTRICA -------------------------------
        '39111500' => '39111500 - Medidores de electricidad',
        '39111501' => '39111501 - Medidor de energía eléctrica (kWh)',
        '39111600' => '39111600 - Instrumentos de prueba y medición eléctrica',
        '39111601' => '39111601 - Multímetro / voltímetro / amperímetro',
        '39111602' => '39111602 - Pinza amperimétrica (clamp meter)',
        '39111603' => '39111603 - Megóhmetro / probador de aislamiento',
        '39111604' => '39111604 - Telurómetro (medidor de tierra física)',
        '39111605' => '39111605 - Analizador de redes eléctricas',

        // -- ILUMINACIÓN ------------------------------------------------------
        '39131500' => '39131500 - Accesorios para iluminación',
        '39131501' => '39131501 - Luminaria LED de interior',
        '39131502' => '39131502 - Panel LED',
        '39131503' => '39131503 - Luminaria industrial (campana LED)',
        '39131504' => '39131504 - Luminaria de emergencia',
        '39131505' => '39131505 - Reflector LED',
        '39131506' => '39131506 - Poste y soporte de iluminación',
        '39131600' => '39131600 - Luminarios y accesorios de soporte',
        '39131601' => '39131601 - Luminaria exterior / vialidad',
        '39131700' => '39131700 - Lámparas y bombillas',
        '39131701' => '39131701 - Foco / bombilla LED',
        '39131702' => '39131702 - Lámpara fluorescente / HID',
        '39131800' => '39131800 - Balastras y drivers LED',
        '39131801' => '39131801 - Driver LED',
        '39131802' => '39131802 - Balasto electrónico',

        // -- HERRAMIENTAS -----------------------------------------------------
        '27112700' => '27112700 - Herramientas manuales para electricistas',
        '27112701' => '27112701 - Juego de herramientas de electricista',
        '27113100' => '27113100 - Herramientas eléctricas / neumáticas portátiles',
        '27113101' => '27113101 - Taladro y rotomartillo',

        // -- EQUIPO DE PROTECCIÓN PERSONAL (EPP) ------------------------------
        '46181500' => '46181500 - Equipo de protección personal (EPP)',
        '46181501' => '46181501 - Casco de seguridad',
        '46181502' => '46181502 - Guantes dieléctricos',
        '46181503' => '46181503 - Calzado de seguridad',
        '46181504' => '46181504 - Arnés de seguridad contra caídas',
        '46181505' => '46181505 - Protección visual (lentes) y auditiva',

        // -- FIJACIONES Y MATERIALES ------------------------------------------
        '31151700' => '31151700 - Tornillería y elementos de fijación',
        '31201600' => '31201600 - Anclas, taquetes y fijaciones',

        // -- SERVICIOS DE INSTALACIÓN ELÉCTRICA -------------------------------
        '72151500' => '72151500 - Servicios de instalación eléctrica',
        '72151501' => '72151501 - Instalación eléctrica industrial',
        '72151502' => '72151502 - Instalación eléctrica comercial',
        '72151503' => '72151503 - Instalación eléctrica residencial',
        '72151504' => '72151504 - Instalación de subestación eléctrica',
        '72151505' => '72151505 - Instalación de tableros eléctricos',
        '72151506' => '72151506 - Instalación de sistema de iluminación',

        // -- SERVICIOS DE MANTENIMIENTO ELÉCTRICO -----------------------------
        '72151600' => '72151600 - Servicios de mantenimiento eléctrico',
        '72151601' => '72151601 - Mantenimiento preventivo eléctrico',
        '72151602' => '72151602 - Mantenimiento correctivo eléctrico',
        '72151603' => '72151603 - Termografía eléctrica / mantenimiento predictivo',

        // -- SERVICIOS DE SISTEMAS DE VOZ, DATOS Y VIDEO ----------------------
        '72152100' => '72152100 - Instalación de sistemas de voz, datos y video',
        '72152101' => '72152101 - Cableado estructurado (red de datos)',
        '72152102' => '72152102 - Instalación de sistema CCTV / videovigilancia',
        '72152103' => '72152103 - Instalación de sistema de alarmas y detección',
        '72152104' => '72152104 - Instalación de control de acceso',

        // -- SERVICIOS DE INGENIERÍA ------------------------------------------
        '81101500' => '81101500 - Servicios de ingeniería y consultoría eléctrica',
        '81101600' => '81101600 - Diseño de proyecto eléctrico',
        '81101700' => '81101700 - Levantamiento, dictamen y peritaje eléctrico',

        // -- CONSTRUCCIÓN EN GENERAL ------------------------------------------
        '82101500' => '82101500 - Servicios de construcción en general',

        // -- OTROS ------------------------------------------------------------
        '43231500' => '43231500 - Equipo de cómputo y accesorios',
        '01010101' => '01010101 - No existe en el catálogo',
    ];

    // -------------------------------------------------------------------------
    // Claves de unidad SAT (ClaveUnidad) — UN/CEFACT
    // -------------------------------------------------------------------------
    const SAT_UNIT_CODES = [
        // Conteo
        'H87' => 'H87 - Pieza',
        'EA'  => 'EA  - Elemento',
        'C62' => 'C62 - Unidad',
        'DZN' => 'DZN - Docena',
        'PR'  => 'PR  - Par',
        // Longitud / Área / Volumen
        'MTR' => 'MTR - Metro lineal',
        'MTK' => 'MTK - Metro cuadrado',
        'MTQ' => 'MTQ - Metro cúbico',
        // Masa
        'KGM' => 'KGM - Kilogramo',
        'GRM' => 'GRM - Gramo',
        'TON' => 'TON - Tonelada métrica',
        // Volumen líquido
        'LTR' => 'LTR - Litro',
        // Empaque / Conjunto
        'SET' => 'SET - Juego / Conjunto',
        'KT'  => 'KT  - Kit',
        'BX'  => 'BX  - Caja',
        'ROL' => 'ROL - Rollo',
        // Tiempo
        'HUR' => 'HUR - Hora',
        'DAY' => 'DAY - Día',
        'WEE' => 'WEE - Semana',
        'MON' => 'MON - Mes',
        // Servicios / Trabajo
        'E48' => 'E48 - Unidad de servicio',
        'ACT' => 'ACT - Actividad',
        'E51' => 'E51 - Trabajo / Obra',
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

    public function lots(): HasMany
    {
        return $this->hasMany(ProductLot::class)->orderBy('entry_date');
    }

    public function activeLots(): HasMany
    {
        return $this->hasMany(ProductLot::class)
            ->where('status', 'active')
            ->where('quantity', '>', 0)
            ->orderBy('entry_date');
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

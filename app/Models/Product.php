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
    // Catálogo ampliado con las claves más utilizadas en México
    // -------------------------------------------------------------------------
    const SAT_PRODUCT_CODES = [

        // ═══════════════════════════════════════════════════════════════
        // ELÉCTRICO / ELECTRÓNICO
        // ═══════════════════════════════════════════════════════════════

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

        // ═══════════════════════════════════════════════════════════════
        // TECNOLOGÍA E INFORMÁTICA
        // ═══════════════════════════════════════════════════════════════

        '43211500' => '43211500 - Computadoras personales y de escritorio',
        '43211501' => '43211501 - Computadora de escritorio (PC)',
        '43211502' => '43211502 - Laptop / computadora portátil',
        '43211503' => '43211503 - Tableta / iPad',
        '43211504' => '43211504 - Servidor de cómputo',
        '43211600' => '43211600 - Dispositivos de entrada',
        '43211601' => '43211601 - Teclado y ratón',
        '43211700' => '43211700 - Monitores y pantallas',
        '43211701' => '43211701 - Monitor / pantalla de computadora',
        '43212100' => '43212100 - Impresoras y periféricos',
        '43212101' => '43212101 - Impresora láser',
        '43212102' => '43212102 - Impresora de inyección de tinta',
        '43212103' => '43212103 - Impresora térmica / de etiquetas',
        '43212104' => '43212104 - Escáner de documentos',
        '43221500' => '43221500 - Equipo de red y comunicaciones',
        '43221501' => '43221501 - Switch de red',
        '43221502' => '43221502 - Router / enrutador',
        '43221503' => '43221503 - Access point Wi-Fi',
        '43221504' => '43221504 - Firewall / UTM',
        '43221505' => '43221505 - Patch panel y accesorios de red',
        '43231500' => '43231500 - Equipo de cómputo y accesorios',
        '43231501' => '43231501 - Disco duro externo / SSD',
        '43231502' => '43231502 - Memoria USB / flash drive',
        '43231503' => '43231503 - Tarjeta de memoria',
        '43232100' => '43232100 - Proyectores y equipo audiovisual',
        '43232101' => '43232101 - Proyector multimedia',
        '43232102' => '43232102 - Pantalla de proyección',
        '43232103' => '43232103 - Pantalla interactiva / pizarrón digital',

        // ═══════════════════════════════════════════════════════════════
        // CONSTRUCCIÓN Y MATERIALES
        // ═══════════════════════════════════════════════════════════════

        '30102500' => '30102500 - Materiales de construcción en general',
        '30102501' => '30102501 - Cemento Portland',
        '30102502' => '30102502 - Varilla de acero para construcción',
        '30102503' => '30102503 - Block de concreto / tabique',
        '30102504' => '30102504 - Grava y arena para construcción',
        '30102505' => '30102505 - Malla electrosoldada',
        '30102506' => '30102506 - Lámina galvanizada / acanalada',
        '30102507' => '30102507 - Perfil de acero estructural',
        '30102508' => '30102508 - Vigueta y bovedilla',
        '30121700' => '30121700 - Tubería y accesorios para construcción',
        '30121701' => '30121701 - Tubo de PVC hidráulico',
        '30121702' => '30121702 - Tubo de PVC sanitario',
        '30121703' => '30121703 - Tubo de cobre para instalaciones',
        '30121704' => '30121704 - Tubo de CPVC para agua caliente',
        '30121705' => '30121705 - Tubería hidráulica de polietileno (PE)',
        '30121706' => '30121706 - Codos, tees y accesorios PVC/cobre',
        '30131600' => '30131600 - Pinturas, recubrimientos y selladores',
        '30131601' => '30131601 - Pintura vinílica / látex',
        '30131602' => '30131602 - Pintura esmalte',
        '30131603' => '30131603 - Pintura anticorrosiva / primario',
        '30131604' => '30131604 - Impermeabilizante',
        '30131605' => '30131605 - Sellador y masilla',
        '30131606' => '30131606 - Thinner y solventes',
        '30141600' => '30141600 - Pisos, azulejos y revestimientos',
        '30141601' => '30141601 - Cerámica / azulejo',
        '30141602' => '30141602 - Porcelanato',
        '30141603' => '30141603 - Piso vinílico / laminado',
        '30141604' => '30141604 - Mosaico y cantera',
        '30141605' => '30141605 - Loseta de concreto',
        '30151500' => '30151500 - Madera y materiales derivados',
        '30151501' => '30151501 - Madera aserrada (tabla, poste, viga)',
        '30151502' => '30151502 - Triplay / plywood',
        '30151503' => '30151503 - MDF / aglomerado',
        '30151504' => '30151504 - Duela para piso',

        // ═══════════════════════════════════════════════════════════════
        // HERRAMIENTAS Y FERRETERÍA
        // ═══════════════════════════════════════════════════════════════

        '27111500' => '27111500 - Herramientas de mano',
        '27111501' => '27111501 - Desarmadores',
        '27111502' => '27111502 - Llaves de tubo y herramienta hidráulica',
        '27111503' => '27111503 - Pinzas y alicates',
        '27111504' => '27111504 - Martillo y mazo',
        '27111505' => '27111505 - Cinta métrica y nivel',
        '27111506' => '27111506 - Segueta y serrucho',
        '27112700' => '27112700 - Herramientas manuales para electricistas',
        '27112701' => '27112701 - Juego de herramientas de electricista',
        '27113100' => '27113100 - Herramientas eléctricas / neumáticas portátiles',
        '27113101' => '27113101 - Taladro y rotomartillo',
        '27113102' => '27113102 - Esmeriladora / amoladora angular',
        '27113103' => '27113103 - Sierra circular / caladora',
        '27113104' => '27113104 - Pistola de calor / sopladora',
        '27113105' => '27113105 - Compresor de aire portátil',
        '27113200' => '27113200 - Equipos de corte y soldadura',
        '27113201' => '27113201 - Soldadora eléctrica / inversora',
        '27113202' => '27113202 - Equipo de soldadura oxiacetilénica',
        '27113203' => '27113203 - Cortadora de plasma',
        '31151700' => '31151700 - Tornillería y elementos de fijación',
        '31151701' => '31151701 - Tornillos, tuercas y rondanas',
        '31201600' => '31201600 - Anclas, taquetes y fijaciones',
        '31201601' => '31201601 - Taquete plástico / fisher',
        '31201602' => '31201602 - Ancla química',
        '31201603' => '31201603 - Abrazadera y clamp',

        // ═══════════════════════════════════════════════════════════════
        // EQUIPO DE PROTECCIÓN PERSONAL (EPP)
        // ═══════════════════════════════════════════════════════════════

        '46181500' => '46181500 - Equipo de protección personal (EPP)',
        '46181501' => '46181501 - Casco de seguridad',
        '46181502' => '46181502 - Guantes dieléctricos',
        '46181503' => '46181503 - Calzado de seguridad',
        '46181504' => '46181504 - Arnés de seguridad contra caídas',
        '46181505' => '46181505 - Protección visual (lentes) y auditiva',
        '46181506' => '46181506 - Chaleco de seguridad / alta visibilidad',
        '46181507' => '46181507 - Overol y ropa de trabajo',
        '46181508' => '46181508 - Mascarilla y respirador',
        '46181509' => '46181509 - Careta facial de protección',
        '46181510' => '46181510 - Señalización y cono de seguridad',

        // ═══════════════════════════════════════════════════════════════
        // PLOMERÍA E HIDRÁULICA
        // ═══════════════════════════════════════════════════════════════

        '40141600' => '40141600 - Bombas hidráulicas y accesorios',
        '40141601' => '40141601 - Bomba centrífuga',
        '40141602' => '40141602 - Bomba sumergible',
        '40141603' => '40141603 - Bomba periférica / de superficie',
        '40141700' => '40141700 - Válvulas y llaves de paso',
        '40141701' => '40141701 - Válvula de compuerta / globo / bola',
        '40141702' => '40141702 - Válvula check / antirretorno',
        '40141703' => '40141703 - Válvula de flotador / boya',
        '40141704' => '40141704 - Llave de paso de ángulo',
        '40141800' => '40141800 - Cisternas, tinacos y almacenamiento de agua',
        '40141801' => '40141801 - Tinaco de polietileno',
        '40141802' => '40141802 - Cisterna prefabricada',
        '40142000' => '40142000 - Calentadores de agua',
        '40142001' => '40142001 - Calentador de gas paso',
        '40142002' => '40142002 - Calentador eléctrico (boiler)',
        '40142003' => '40142003 - Calentador solar',
        '40161500' => '40161500 - Sanitarios, lavabos y muebles de baño',
        '40161501' => '40161501 - Inodoro / WC',
        '40161502' => '40161502 - Lavabo',
        '40161503' => '40161503 - Regadera y accesorios de baño',
        '40161504' => '40161504 - Mingitorio',

        // ═══════════════════════════════════════════════════════════════
        // CLIMATIZACIÓN Y HVAC
        // ═══════════════════════════════════════════════════════════════

        '40101500' => '40101500 - Equipos de climatización y HVAC',
        '40101501' => '40101501 - Aire acondicionado tipo mini-split',
        '40101502' => '40101502 - Aire acondicionado de ventana',
        '40101503' => '40101503 - Unidad paquete de HVAC',
        '40101504' => '40101504 - Chiller / enfriador de agua',
        '40101600' => '40101600 - Ventilación y extracción',
        '40101601' => '40101601 - Ventilador industrial / de techo',
        '40101602' => '40101602 - Extractor de aire',
        '40101603' => '40101603 - Ducto de HVAC',

        // ═══════════════════════════════════════════════════════════════
        // VEHÍCULOS Y TRANSPORTE
        // ═══════════════════════════════════════════════════════════════

        '25101500' => '25101500 - Automóviles y camionetas',
        '25101501' => '25101501 - Automóvil particular',
        '25101502' => '25101502 - Camioneta pick-up',
        '25101503' => '25101503 - Camioneta de pasajeros (van)',
        '25101600' => '25101600 - Camiones y vehículos de carga',
        '25101601' => '25101601 - Camión de carga',
        '25101602' => '25101602 - Tractocamión',
        '25101700' => '25101700 - Motocicletas y bicicletas',
        '25101701' => '25101701 - Motocicleta',
        '25101702' => '25101702 - Bicicleta',
        '25171500' => '25171500 - Refacciones y accesorios automotrices',
        '25171501' => '25171501 - Batería automotriz',
        '25171502' => '25171502 - Llantas y rines',
        '25171503' => '25171503 - Aceite de motor y lubricantes',
        '25171504' => '25171504 - Filtros (aceite, aire, combustible)',
        '25171505' => '25171505 - Frenos y accesorios',
        '25171506' => '25171506 - Piezas de motor y transmisión',

        // ═══════════════════════════════════════════════════════════════
        // MUEBLES Y EQUIPO DE OFICINA
        // ═══════════════════════════════════════════════════════════════

        '56101500' => '56101500 - Escritorios y mesas de trabajo',
        '56101501' => '56101501 - Escritorio de oficina',
        '56101502' => '56101502 - Mesa de reuniones',
        '56101503' => '56101503 - Mesa de trabajo',
        '56101600' => '56101600 - Sillas y sillones',
        '56101601' => '56101601 - Silla ejecutiva de oficina',
        '56101602' => '56101602 - Silla de visita',
        '56101603' => '56101603 - Sillón / sofá',
        '56101700' => '56101700 - Archiveros, libreros y estantes',
        '56101701' => '56101701 - Archivero metálico',
        '56101702' => '56101702 - Librero y estante de oficina',
        '56101703' => '56101703 - Locker y casillero',
        '44121600' => '44121600 - Artículos de papelería y oficina',
        '44121601' => '44121601 - Papel bond y papelería',
        '44121602' => '44121602 - Bolígrafos, marcadores y lápices',
        '44121603' => '44121603 - Carpetas, archivadores y folders',
        '44121604' => '44121604 - Cintas adhesivas y pegamento',
        '44121605' => '44121605 - Tijeras, cutters y perforadoras',
        '44121606' => '44121606 - Engrapadora y grapas',

        // ═══════════════════════════════════════════════════════════════
        // PRODUCTOS DE LIMPIEZA
        // ═══════════════════════════════════════════════════════════════

        '47131500' => '47131500 - Productos de limpieza e higiene',
        '47131501' => '47131501 - Detergente y jabón industrial',
        '47131502' => '47131502 - Desinfectante y cloro',
        '47131503' => '47131503 - Desengrasante industrial',
        '47131504' => '47131504 - Aromatizante y limpiador de pisos',
        '47131505' => '47131505 - Papel higiénico y servilletas',
        '47131506' => '47131506 - Bolsas para basura',
        '47131600' => '47131600 - Equipo de limpieza',
        '47131601' => '47131601 - Escoba, trapeador y jalador',
        '47131602' => '47131602 - Cubeta y mopa',
        '47131603' => '47131603 - Aspiradora industrial',

        // ═══════════════════════════════════════════════════════════════
        // ALIMENTOS Y BEBIDAS
        // ═══════════════════════════════════════════════════════════════

        '50101500' => '50101500 - Frutas y verduras',
        '50101501' => '50101501 - Frutas frescas',
        '50101502' => '50101502 - Verduras y hortalizas',
        '50111500' => '50111500 - Carnes y embutidos',
        '50111501' => '50111501 - Carne de res',
        '50111502' => '50111502 - Carne de cerdo',
        '50111503' => '50111503 - Carne de pollo',
        '50111504' => '50111504 - Embutidos y charcutería',
        '50121500' => '50121500 - Lácteos',
        '50121501' => '50121501 - Leche',
        '50121502' => '50121502 - Queso y derivados',
        '50131500' => '50131500 - Abarrotes y alimentos empacados',
        '50131501' => '50131501 - Cereales, arroz y pasta',
        '50131502' => '50131502 - Aceites y grasas comestibles',
        '50131503' => '50131503 - Conservas y enlatados',
        '50131504' => '50131504 - Condimentos, salsas y especias',
        '50201500' => '50201500 - Bebidas no alcohólicas',
        '50201501' => '50201501 - Agua embotellada',
        '50201502' => '50201502 - Refrescos y bebidas carbonatadas',
        '50201503' => '50201503 - Jugos y néctares',
        '50201504' => '50201504 - Café y té',
        '50211500' => '50211500 - Bebidas alcohólicas',
        '50211501' => '50211501 - Cerveza',
        '50211502' => '50211502 - Vinos',
        '50211503' => '50211503 - Licores y destilados',

        // ═══════════════════════════════════════════════════════════════
        // SALUD Y FARMACIA
        // ═══════════════════════════════════════════════════════════════

        '51100000' => '51100000 - Medicamentos y productos farmacéuticos',
        '51101500' => '51101500 - Medicamentos de uso general',
        '51101501' => '51101501 - Analgésicos y antiinflamatorios',
        '51101502' => '51101502 - Antibióticos',
        '51101503' => '51101503 - Vitaminas y suplementos',
        '51101504' => '51101504 - Medicamentos de prescripción',
        '42141500' => '42141500 - Instrumental y equipo médico',
        '42141501' => '42141501 - Tensiómetro / baumanómetro',
        '42141502' => '42141502 - Termómetro',
        '42141503' => '42141503 - Botiquín de primeros auxilios',
        '42141504' => '42141504 - Material de curación',

        // ═══════════════════════════════════════════════════════════════
        // TEXTILES Y ROPA
        // ═══════════════════════════════════════════════════════════════

        '53101500' => '53101500 - Uniformes y ropa de trabajo',
        '53101501' => '53101501 - Playera / polo de uniforme',
        '53101502' => '53101502 - Pantalón de trabajo',
        '53101503' => '53101503 - Chamarra y sudadera',
        '53101504' => '53101504 - Gorra y accesorios textiles',
        '60121500' => '60121500 - Telas y materias textiles',
        '60121501' => '60121501 - Tela de algodón',
        '60121502' => '60121502 - Tela sintética / poliéster',

        // ═══════════════════════════════════════════════════════════════
        // QUÍMICOS E INSUMOS INDUSTRIALES
        // ═══════════════════════════════════════════════════════════════

        '12142200' => '12142200 - Gases industriales',
        '12142201' => '12142201 - Oxígeno industrial',
        '12142202' => '12142202 - Nitrógeno industrial',
        '12142203' => '12142203 - Acetileno',
        '12142204' => '12142204 - Argón',
        '12141500' => '12141500 - Lubricantes y aceites industriales',
        '12141501' => '12141501 - Aceite hidráulico',
        '12141502' => '12141502 - Grasa industrial',
        '12141503' => '12141503 - Aceite de corte',
        '12352000' => '12352000 - Adhesivos y selladores industriales',
        '12352001' => '12352001 - Resistol / adhesivo de contacto',
        '12352002' => '12352002 - Silicón sellador',
        '12352003' => '12352003 - Pegamento epóxico',

        // ═══════════════════════════════════════════════════════════════
        // EMPAQUES Y LOGÍSTICA
        // ═══════════════════════════════════════════════════════════════

        '24111500' => '24111500 - Cajas y empaques',
        '24111501' => '24111501 - Caja de cartón',
        '24111502' => '24111502 - Caja de madera / tarima',
        '24111503' => '24111503 - Bolsa de plástico y polipropileno',
        '24111504' => '24111504 - Cinta de empaque',
        '24111505' => '24111505 - Burbuja y foam para embalaje',
        '24111506' => '24111506 - Etiquetas y códigos de barras',

        // ═══════════════════════════════════════════════════════════════
        // SERVICIOS — INSTALACIÓN Y CONSTRUCCIÓN
        // ═══════════════════════════════════════════════════════════════

        '72101500' => '72101500 - Servicios generales de construcción',
        '72101501' => '72101501 - Construcción de obra civil',
        '72101502' => '72101502 - Remodelación y acabados',
        '72101503' => '72101503 - Obra de urbanización',
        '72101504' => '72101504 - Demolición',
        '72121500' => '72121500 - Servicios de plomería e hidráulica',
        '72121501' => '72121501 - Instalación hidráulica y sanitaria',
        '72121502' => '72121502 - Reparación de fugas y tuberías',
        '72141500' => '72141500 - Servicios de climatización y HVAC',
        '72141501' => '72141501 - Instalación de aire acondicionado',
        '72141502' => '72141502 - Mantenimiento de equipos HVAC',
        '72151500' => '72151500 - Servicios de instalación eléctrica',
        '72151501' => '72151501 - Instalación eléctrica industrial',
        '72151502' => '72151502 - Instalación eléctrica comercial',
        '72151503' => '72151503 - Instalación eléctrica residencial',
        '72151504' => '72151504 - Instalación de subestación eléctrica',
        '72151505' => '72151505 - Instalación de tableros eléctricos',
        '72151506' => '72151506 - Instalación de sistema de iluminación',
        '72151600' => '72151600 - Servicios de mantenimiento eléctrico',
        '72151601' => '72151601 - Mantenimiento preventivo eléctrico',
        '72151602' => '72151602 - Mantenimiento correctivo eléctrico',
        '72151603' => '72151603 - Termografía eléctrica / mantenimiento predictivo',
        '72152100' => '72152100 - Instalación de sistemas de voz, datos y video',
        '72152101' => '72152101 - Cableado estructurado (red de datos)',
        '72152102' => '72152102 - Instalación de sistema CCTV / videovigilancia',
        '72152103' => '72152103 - Instalación de sistema de alarmas y detección',
        '72152104' => '72152104 - Instalación de control de acceso',

        // ═══════════════════════════════════════════════════════════════
        // SERVICIOS — MANTENIMIENTO GENERAL
        // ═══════════════════════════════════════════════════════════════

        '76111500' => '76111500 - Servicios de mantenimiento de instalaciones',
        '76111501' => '76111501 - Mantenimiento preventivo de instalaciones',
        '76111502' => '76111502 - Mantenimiento correctivo general',
        '76111503' => '76111503 - Servicio de fumigación y control de plagas',
        '76111504' => '76111504 - Limpieza industrial y de instalaciones',
        '76121500' => '76121500 - Servicios de jardinería y limpieza exterior',

        // ═══════════════════════════════════════════════════════════════
        // SERVICIOS — INGENIERÍA Y TÉCNICOS
        // ═══════════════════════════════════════════════════════════════

        '81101500' => '81101500 - Servicios de ingeniería y consultoría eléctrica',
        '81101600' => '81101600 - Diseño de proyecto eléctrico',
        '81101700' => '81101700 - Levantamiento, dictamen y peritaje eléctrico',
        '81111500' => '81111500 - Servicios de ingeniería civil y arquitectura',
        '81111501' => '81111501 - Diseño arquitectónico',
        '81111502' => '81111502 - Cálculo estructural',
        '81111503' => '81111503 - Supervisión de obra',
        '81111504' => '81111504 - Topografía y levantamiento',
        '81141600' => '81141600 - Servicios de tecnología de la información',
        '81141601' => '81141601 - Desarrollo de software a la medida',
        '81141602' => '81141602 - Soporte técnico y mantenimiento de equipo',
        '81141603' => '81141603 - Consultoría en TI',
        '81141604' => '81141604 - Hospedaje web y servicios en la nube',

        // ═══════════════════════════════════════════════════════════════
        // SERVICIOS — PROFESIONALES Y ADMINISTRATIVOS
        // ═══════════════════════════════════════════════════════════════

        '80141500' => '80141500 - Servicios contables y financieros',
        '80141501' => '80141501 - Servicios de contabilidad',
        '80141502' => '80141502 - Auditoría y revisión fiscal',
        '80141503' => '80141503 - Consultoría fiscal y legal',
        '80141600' => '80141600 - Servicios jurídicos / legales',
        '80141601' => '80141601 - Asesoría legal y notarial',
        '80141602' => '80141602 - Trámites y gestión legal',
        '80101500' => '80101500 - Servicios de gestión empresarial',
        '80101501' => '80101501 - Consultoría en gestión y administración',
        '80101502' => '80101502 - Capacitación y adiestramiento',
        '80141700' => '80141700 - Servicios de recursos humanos',
        '80141701' => '80141701 - Reclutamiento y selección de personal',
        '80141702' => '80141702 - Nómina y administración de personal',

        // ═══════════════════════════════════════════════════════════════
        // SERVICIOS — TRANSPORTE Y LOGÍSTICA
        // ═══════════════════════════════════════════════════════════════

        '78101500' => '78101500 - Servicios de flete y transporte de carga',
        '78101501' => '78101501 - Flete local',
        '78101502' => '78101502 - Flete foráneo / transporte de larga distancia',
        '78101503' => '78101503 - Servicio de mensajería y paquetería',
        '78101504' => '78101504 - Almacenaje y logística',
        '78111500' => '78111500 - Transporte de pasajeros',
        '78111501' => '78111501 - Servicio de taxi / traslado ejecutivo',
        '78111502' => '78111502 - Renta de vehículos',

        // ═══════════════════════════════════════════════════════════════
        // SERVICIOS — PUBLICIDAD Y COMUNICACIÓN
        // ═══════════════════════════════════════════════════════════════

        '82141600' => '82141600 - Servicios de publicidad y marketing',
        '82141601' => '82141601 - Publicidad en medios digitales',
        '82141602' => '82141602 - Diseño gráfico e impresión',
        '82141603' => '82141603 - Fotografía y video profesional',
        '82141604' => '82141604 - Relaciones públicas y comunicación',
        '82111500' => '82111500 - Servicios de impresión',
        '82111501' => '82111501 - Impresión de documentos y papelería',
        '82111502' => '82111502 - Impresión de gran formato / lonas y vinilos',

        // ═══════════════════════════════════════════════════════════════
        // SERVICIOS — HOSPITALIDAD Y ALIMENTOS
        // ═══════════════════════════════════════════════════════════════

        '90111500' => '90111500 - Servicios de hospedaje / hotel',
        '90111501' => '90111501 - Hospedaje en hotel',
        '90111502' => '90111502 - Renta de salones y eventos',
        '90111600' => '90111600 - Servicios de alimentos y bebidas',
        '90111601' => '90111601 - Servicio de comedor / catering',
        '90111602' => '90111602 - Consumo en restaurante',

        // ═══════════════════════════════════════════════════════════════
        // SERVICIOS — SEGURIDAD
        // ═══════════════════════════════════════════════════════════════

        '92121500' => '92121500 - Servicios de seguridad privada',
        '92121501' => '92121501 - Vigilancia y resguardo',
        '92121502' => '92121502 - Monitoreo de alarmas',
        '92121503' => '92121503 - Instalación de sistemas de seguridad',

        // ═══════════════════════════════════════════════════════════════
        // ARRENDAMIENTO Y RENTA
        // ═══════════════════════════════════════════════════════════════

        '80131500' => '80131500 - Arrendamiento de inmuebles',
        '80131501' => '80131501 - Renta de oficina / local comercial',
        '80131502' => '80131502 - Renta de bodega / nave industrial',
        '80131503' => '80131503 - Renta de terreno',
        '80141800' => '80141800 - Arrendamiento de equipo y maquinaria',
        '80141801' => '80141801 - Renta de maquinaria para construcción',
        '80141802' => '80141802 - Renta de equipo de cómputo',
        '80141803' => '80141803 - Renta de vehículos / flotilla',

        // ═══════════════════════════════════════════════════════════════
        // CONSTRUCCIÓN EN GENERAL
        // ═══════════════════════════════════════════════════════════════

        '82101500' => '82101500 - Servicios de construcción en general',

        // ═══════════════════════════════════════════════════════════════
        // OTROS / ESPECIALES
        // ═══════════════════════════════════════════════════════════════

        '84111500' => '84111500 - Seguros de bienes y responsabilidad civil',
        '84111501' => '84111501 - Seguro de vehículos',
        '84111502' => '84111502 - Seguro de vida',
        '84111503' => '84111503 - Seguro de gastos médicos mayores',
        '84111504' => '84111504 - Seguro de daños / empresarial',
        '84121500' => '84121500 - Servicios bancarios y financieros',
        '84121501' => '84121501 - Comisiones bancarias',
        '84121502' => '84121502 - Intereses sobre préstamos',
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
        'purchase_price', 'purchase_price_includes_iva', 'profit_margin', 'operational_costs', 'sale_price',
        'min_stock', 'max_stock', 'is_active',
    ];

    protected $casts = [
        'is_active'                  => 'boolean',
        'purchase_price_includes_iva' => 'boolean',
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

    public function priceListItems(): HasMany
    {
        return $this->hasMany(PriceListItem::class);
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

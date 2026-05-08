<?php

namespace Database\Seeders;

use App\Models\LandingSetting;
use Illuminate\Database\Seeder;

class LandingSettingSeeder extends Seeder
{
    public function run(): void
    {
        // ── Hero ─────────────────────────────────────────────────────────────
        LandingSetting::setSection('hero', [
            'eyebrow'  => 'ProPower Electroconstrucciones · desde 2018',
            'title'    => "Soluciones,\ncalidad y\ngarantía.",
            'cta_text' => 'Contáctanos →',
            'cta_sub'  => '↓ Conoce nuestro proceso',
            'images'   => [
                '/assets/img/hero/hero-background.webp',
                '/assets/img/Carrousel/pexels-1920-1.jpg',
                '/assets/img/Carrousel/pexels-1920-2.jpg',
                '/assets/img/Carrousel/pexels-1920-4.jpg',
                '/assets/img/Carrousel/pexels-cottonbro-5089126.jpg',
                '/assets/img/Carrousel/pexels-mateusz-dach-99805-5956083.jpg',
            ],
            'stats' => [
                ['value' => '8+',   'label' => 'Años operando'],
                ['value' => '200+', 'label' => 'Proyectos entregados'],
                ['value' => '2',    'label' => 'Sucursales en CHIH'],
                ['value' => '100%', 'label' => 'Capital mexicano'],
            ],
        ]);

        // ── Oferta ────────────────────────────────────────────────────────────
        LandingSetting::setSection('oferta', [
            'eyebrow' => 'Nuestra oferta',
            'title'   => "Tres sectores.\nUna sola exigencia.",
            'sectors' => [
                [
                    'image' => '/assets/img/Inicio/pexels-sergey-sergeev-2153675005-32845692.jpg',
                    'title' => 'Industria',
                    'desc'  => 'Impulsa tu industria hoy con nuestros servicios electromecánicos.',
                    'tags'  => ['Subestaciones', 'Tableros', 'Automatización'],
                ],
                [
                    'image' => '/assets/img/Inicio/pexels-hannu-iso-oja-3301403-4946889.jpg',
                    'title' => 'Minería',
                    'desc'  => 'Explora nuestra oferta diseñada especialmente para la minería.',
                    'tags'  => ['Subestaciones móviles', 'Bombeo', 'Cable G/GGC'],
                ],
                [
                    'image' => '/assets/img/Inicio/pexels-freek-wolsink-508219-34207359.jpg',
                    'title' => 'Ingeniería',
                    'desc'  => 'Descubre nuestras soluciones de ingeniería personalizadas.',
                    'tags'  => ['Proyecto llave', 'Supervisión'],
                ],
            ],
        ]);

        // ── Nosotros ─────────────────────────────────────────────────────────
        LandingSetting::setSection('nosotros', [
            'eyebrow'  => '¿Quiénes somos?',
            'title'    => "100%\nmexicana.\nSiempre a la vanguardia.",
            'since'    => 'Desde 2018 · Chihuahua · México',
            'body1'    => 'ProPower Electroconstrucciones es una empresa 100% mexicana especializada en servicios electromecánicos industriales y comerciales.',
            'body2'    => 'Nuestro equipo, ambicioso y con un fuerte espíritu de trabajo, se mantiene siempre a la vanguardia, con el objetivo de ofrecer a nuestros clientes seguridad y calidad en cada proyecto.',
            'mision'   => 'Entregar soluciones electromecánicas con garantía y seguridad, superando las expectativas de cada cliente.',
            'vision'   => 'Ser el contratista de referencia en el norte de México en electroconstrucciones industriales.',
            'valores'  => 'Compromiso, responsabilidad y honestidad en cada obra y en cada relación.',
        ]);

        // ── Servicios ─────────────────────────────────────────────────────────
        LandingSetting::setSection('servicios', [
            'eyebrow' => 'Servicios',
            'title'   => "30 servicios.\nTres especialidades.",
            'body'    => 'Desde la planeación eléctrica hasta la puesta en marcha, cubrimos cada etapa de tu obra con personal certificado.',
            'industria' => [
                ['img' => '/assets/img/Servicios/instalaciones-electricas.webp', 't' => 'Instalaciones eléctricas en baja y media tensión'],
                ['img' => '/assets/img/Servicios/b.webp', 't' => 'Implementación y ejecución de programas de mantenimientos'],
                ['img' => '/assets/img/Servicios/c.webp', 't' => 'Cálculo e instalación de sistemas de iluminación'],
                ['img' => '/assets/img/Servicios/d.webp', 't' => 'Instalación de sistemas mecánicos'],
                ['img' => '/assets/img/Servicios/e.webp', 't' => 'Estructuras, soldadura y pintura industrial'],
                ['img' => '/assets/img/Servicios/f.webp', 't' => 'Pruebas de resistencia de aislamiento'],
                ['img' => '/assets/img/Servicios/g.webp', 't' => 'Control y automatización de procesos'],
                ['img' => '/assets/img/Servicios/h.webp', 't' => 'Venta y montaje de transformadores'],
                ['img' => '/assets/img/Servicios/i.webp', 't' => 'Maniobras de izaje, montaje y colocación'],
                ['img' => '/assets/img/Servicios/j.webp', 't' => 'Venta e instalación de bancos de capacitores'],
                ['img' => '/assets/img/Servicios/k.webp', 't' => 'Mantenimiento a subestaciones eléctricas'],
                ['img' => '/assets/img/Servicios/l.webp', 't' => 'Memorias de cálculo eléctrico (NOM-001-SEDE-2012)'],
                ['img' => '/assets/img/Servicios/m.webp', 't' => 'Reparación equipo y motores eléctricos de cualquier capacidad'],
                ['img' => '/assets/img/Servicios/n.webp', 't' => 'Instalación de centros de control de motores (CCM)'],
                ['img' => '/assets/img/Servicios/ñ.webp', 't' => 'Venta de equipo y material eléctrico'],
                ['img' => '/assets/img/Servicios/o.webp', 't' => 'Pruebas de termografía y ultrasonido'],
                ['img' => '/assets/img/Servicios/p.webp', 't' => 'Dictámenes eléctricos (NOM-001-SEDE-2012)'],
            ],
            'mineria' => [
                ['img' => '/assets/img/Servicios/q.webp', 't' => 'Líneas de baja y media tensión'],
                ['img' => '/assets/img/Servicios/r.webp', 't' => 'Venta e instalación de transformadores'],
                ['img' => '/assets/img/Servicios/s.webp', 't' => 'Subestaciones móviles para interior mina 4.16 y 13.8 KV'],
                ['img' => '/assets/img/Servicios/t.webp', 't' => 'Venta, instalación y mantenimiento de bombas sumergibles'],
                ['img' => '/assets/img/Servicios/u.webp', 't' => 'Reparación y mantenimiento a motores de cualquier capacidad'],
                ['img' => '/assets/img/Servicios/v.webp', 't' => 'Proyectos de iluminación'],
                ['img' => '/assets/img/Servicios/w.webp', 't' => 'Venta de tablero tipo centinela avanzado'],
                ['img' => '/assets/img/Servicios/x.webp', 't' => 'Venta de cable tipo G y GGC'],
                ['img' => '/assets/img/Servicios/y.webp', 't' => 'Venta de arrancadores suaves, variadores de frecuencia'],
                ['img' => '/assets/img/Servicios/z.webp', 't' => 'Venta y reparación de ventiladores tipo Zitron'],
            ],
            'ingenieria' => [
                ['img' => '/assets/img/Servicios/aa.webp', 't' => 'Diseño y planos', 'items' => ['Diseño CAD', 'Diagramas eléctricos', 'Layout', 'Planos de control', 'Programación']],
                ['img' => '/assets/img/Servicios/bb.webp', 't' => 'Análisis y mediciones', 'items' => ['Calidad de la energía', 'Resistencia de puesta a tierra', 'Resistividad del terreno']],
                ['img' => '/assets/img/Servicios/cc.webp', 't' => 'Cálculo y dictámenes', 'items' => ['Memorias de cálculo', 'Cálculo de iluminación', 'Tierra y pararrayos', 'Dictámenes eléctricos']],
            ],
        ]);

        // ── Galería ───────────────────────────────────────────────────────────
        LandingSetting::setSection('galeria', [
            'eyebrow' => 'Galería',
            'title'   => "Obras que\nhablan por sí solas.",
            'projects' => [
                ['img' => '/assets/img/Galeria/Media-Tension/25.webp',    't' => 'Subestación industrial',   'loc' => 'Chihuahua, CHIH', 'year' => '2024', 'cat' => 'Media tensión',  'sector' => 'Industria'],
                ['img' => '/assets/img/Galeria/Baja-Tension/30.webp',     't' => 'Nave de manufactura',      'loc' => 'Delicias, CHIH',  'year' => '2024', 'cat' => 'Baja tensión',   'sector' => 'Industria'],
                ['img' => '/assets/img/Galeria/Mejora de tableros electricos existentes/2.webp', 't' => 'Tableros eléctricos', 'loc' => 'Sierra, CHIH', 'year' => '2023', 'cat' => 'Tableros', 'sector' => 'Industria'],
                ['img' => '/assets/img/Galeria/Actualizaciones de control para planta energetica/1.webp', 't' => 'Control de planta', 'loc' => 'Chihuahua, CHIH', 'year' => '2023', 'cat' => 'Control', 'sector' => 'Ingeniería'],
                ['img' => '/assets/img/Galeria/Pruebas Electricas/3.webp', 't' => 'Pruebas eléctricas',     'loc' => 'Parque Industrial', 'year' => '2023', 'cat' => 'Pruebas',      'sector' => 'Ingeniería'],
                ['img' => '/assets/img/Galeria/Remplazo de laminas translucidas y actualizacion de iluminacion led/11.webp', 't' => 'Iluminación LED industrial', 'loc' => 'Nave 12, CHIH', 'year' => '2022', 'cat' => 'Iluminación', 'sector' => 'Minería'],
                ['img' => '/assets/img/Galeria/Laminado de estructura de molino/1.webp', 't' => 'Estructura de molino', 'loc' => 'Proyecto industrial', 'year' => '2022', 'cat' => 'Estructural', 'sector' => 'Minería'],
            ],
        ]);

        // ── Contacto ──────────────────────────────────────────────────────────
        LandingSetting::setSection('contacto', [
            'eyebrow' => 'Contacto',
            'title'   => "Cuéntanos tu\nproyecto.",
            'body'    => 'Escríbenos y un asesor técnico se pondrá en contacto contigo en menos de 24 horas hábiles.',
            'phone'   => '614 166 6340',
            'email'   => 'contacto@propower.mx',
            'hours'   => 'Lun–Vie · 9:00–18:00',
            'sucursales' => [
                [
                    'title' => 'Sucursal Chihuahua',
                    'embed' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d6537.871959268932!2d-106.12901740537757!3d28.70382956590884!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x86ea438974075dc5%3A0xb8c2426f69011cbb!2sProPower%20Electroconstrucciones!5e0!3m2!1ses-419!2smx!4v1763958506976!5m2!1ses-419!2smx',
                ],
                [
                    'title' => 'Sucursal Delicias',
                    'embed' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d8259.094005874922!2d-105.45656117616318!3d28.183644407838823!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x86eb159b6256c213%3A0x3aa93cc16e2a0b9!2sPropower%20Electroconstrucciones!5e0!3m2!1ses-419!2smx!4v1763958405258!5m2!1ses-419!2smx',
                ],
            ],
        ]);

        // ── Footer ────────────────────────────────────────────────────────────
        LandingSetting::setSection('footer', [
            'description' => 'Empresa 100% mexicana especializada en servicios electromecánicos industriales y comerciales desde 2018.',
            'copyright'   => '© 2026 ProPower Electroconstrucciones — Todos los derechos reservados.',
            'whatsapp'    => 'https://wa.me/526141666340',
            'facebook'    => 'https://www.facebook.com/ProPowerMX',
            'phone'       => '614 166 6340',
            'email'       => 'contacto@propower.mx',
        ]);
    }
}

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Aviso de Privacidad | ProPower</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@700;800;900&family=Figtree:wght@400;500;600;700&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --pp-red: #c81e1e;
            --pp-black: #0a0a0a;
            --pp-stone: #a8a29e;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            background: var(--pp-black);
            color: #fff;
            font-family: Figtree, Arial, sans-serif;
        }

        a { color: inherit; }

        .privacy-shell {
            min-height: 100vh;
            background:
                radial-gradient(circle at 82% 12%, rgba(200,30,30,0.12), transparent 30%),
                linear-gradient(90deg, rgba(10,10,10,0.97) 0%, rgba(10,10,10,0.9) 50%, rgba(10,10,10,0.98) 100%),
                url('/assets/img/hero/hero-background.webp') center/cover fixed;
        }

        .privacy-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            padding: 20px clamp(20px, 5vw, 56px);
            border-bottom: 1px solid rgba(255,255,255,0.08);
            background: rgba(10,10,10,0.82);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
        }

        .privacy-logo {
            display: block;
            height: clamp(48px, 4.2vw, 76px);
            width: auto;
        }

        .privacy-back {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 40px;
            padding: 0 16px;
            border: 1px solid rgba(255,255,255,0.24);
            border-bottom-color: rgba(200,30,30,0.82);
            background: rgba(255,255,255,0.03);
            color: rgba(255,255,255,0.84);
            font-family: "JetBrains Mono", Consolas, monospace;
            font-size: 11px;
            letter-spacing: 0.12em;
            text-decoration: none;
            text-transform: uppercase;
            transition: border-color 0.2s ease, background 0.2s ease, color 0.2s ease;
        }

        .privacy-back:hover {
            border-color: rgba(200,30,30,0.72);
            background: rgba(200,30,30,0.08);
            color: #fff;
        }

        .privacy-main {
            width: min(100%, 1120px);
            margin: 0 auto;
            padding: clamp(48px, 7vw, 96px) clamp(20px, 5vw, 56px) 80px;
        }

        .privacy-kicker {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: var(--pp-red);
            font-family: "JetBrains Mono", Consolas, monospace;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            margin-bottom: 22px;
        }

        .privacy-kicker::before {
            content: "";
            width: 28px;
            height: 1px;
            background: var(--pp-red);
        }

        h1 {
            max-width: 760px;
            margin: 0;
            font-family: Archivo, Figtree, Arial, sans-serif;
            font-size: clamp(42px, 6.4vw, 88px);
            font-weight: 800;
            line-height: 0.96;
            letter-spacing: -0.025em;
            text-transform: uppercase;
        }

        .privacy-meta {
            margin-top: 24px;
            color: rgba(255,255,255,0.56);
            font-family: "JetBrains Mono", Consolas, monospace;
            font-size: 11px;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .privacy-content {
            margin-top: clamp(36px, 5vw, 64px);
            border-top: 1px solid rgba(255,255,255,0.16);
            border-bottom: 1px solid rgba(255,255,255,0.08);
            display: grid;
            grid-template-columns: minmax(180px, 280px) minmax(0, 1fr);
            gap: clamp(28px, 5vw, 72px);
            padding: clamp(28px, 4vw, 48px) 0 clamp(32px, 4vw, 52px);
        }

        .privacy-side {
            color: rgba(255,255,255,0.52);
            border-left: 1px solid rgba(200,30,30,0.6);
            padding-left: 18px;
            font-family: "JetBrains Mono", Consolas, monospace;
            font-size: 11px;
            letter-spacing: 0.14em;
            line-height: 1.8;
            text-transform: uppercase;
        }

        .privacy-copy {
            color: rgba(255,255,255,0.78);
            font-size: 16px;
            line-height: 1.75;
        }

        .privacy-copy h2 {
            margin: 42px 0 14px;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 12px;
            font-family: Archivo, Arial, sans-serif;
            font-size: 22px;
            letter-spacing: 0;
            text-transform: uppercase;
        }

        .privacy-copy h2::before {
            content: "";
            width: 22px;
            height: 1px;
            background: var(--pp-red);
            flex: 0 0 auto;
        }

        .privacy-copy h2:first-child { margin-top: 0; }

        .privacy-copy p { margin: 0 0 18px; }
        .privacy-copy ul, .privacy-copy ol { margin: 0 0 22px; padding-left: 22px; }
        .privacy-copy li { margin-bottom: 10px; }
        .privacy-copy strong { color: #fff; }

        .privacy-actions {
            margin-top: 44px;
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
        }

        .privacy-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 46px;
            padding: 0 22px;
            border: 1px solid var(--pp-red);
            background: var(--pp-red);
            color: #fff;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-decoration: none;
            text-transform: uppercase;
            transition: transform 0.2s ease, border-color 0.2s ease, background 0.2s ease;
        }

        .privacy-button:hover {
            transform: translateY(-1px);
            background: #b91c1c;
        }

        .privacy-button.secondary {
            background: transparent;
            border-color: rgba(255,255,255,0.24);
            color: rgba(255,255,255,0.82);
        }

        .privacy-button.secondary:hover {
            border-color: rgba(200,30,30,0.7);
            background: rgba(200,30,30,0.08);
        }

        @media (max-width: 720px) {
            .privacy-nav { align-items: flex-start; flex-direction: column; }
            .privacy-content { grid-template-columns: 1fr; }
            .privacy-side { border-left: 0; border-bottom: 1px solid rgba(255,255,255,0.12); padding: 0 0 18px; }
        }
    </style>
</head>
<body>
    <div class="privacy-shell">
        <nav class="privacy-nav">
            <a href="/">
                <img class="privacy-logo" src="/assets/img/LOGO ELECTROCONSTRUCCIONES/PNG/propower_Mesa de trabajo 1h red.png" alt="ProPower">
            </a>
            <a class="privacy-back" href="/">Volver al inicio</a>
        </nav>

        <main class="privacy-main">
            <div class="privacy-kicker">Proteccion de datos personales</div>
            <h1>Aviso de <span style="color: var(--pp-red)">Privacidad.</span></h1>
            <div class="privacy-meta">Ultima actualizacion: 28 de mayo de 2026</div>

            <section class="privacy-content">
                <aside class="privacy-side">
                    ProPower Electroconstrucciones<br>
                    Chihuahua, Mexico<br>
                    contacto@propower.mx
                </aside>

                <div class="privacy-copy">
                    <p><strong>PROPOWER ELECTROCONSTRUCCIONES</strong>, con domicilio en 5HM3+73 Delicias, Chihuahua, es responsable de recabar sus datos personales, del uso que se les de a los mismos y de su proteccion.</p>
                    <p>De conformidad con lo previsto en la Ley Federal de Proteccion de Datos Personales en Posesion de los Particulares y su Reglamento, solicitamos leer cuidadosamente este Aviso de Privacidad, ya que contiene los terminos y condiciones aplicables a los datos personales recabados por PROPOWER ELECTROCONSTRUCCIONES.</p>
                    <p>Su informacion personal sera utilizada para proveer los servicios y productos que ha solicitado, informarle sobre cambios en los mismos y evaluar la calidad del servicio que le brindamos.</p>

                    <h2>Datos personales recabados</h2>
                    <p>Como parte normal de sus actividades, PROPOWER ELECTROCONSTRUCCIONES puede recabar y almacenar informacion considerada como datos personales en terminos de la Ley.</p>
                    <p>En caso de ingresar comentarios a traves del formulario de contacto, se recabaran y almacenaran los siguientes datos:</p>
                    <ul>
                        <li>Nombre completo que usted proporcione.</li>
                        <li>Correo electronico.</li>
                        <li>Cualquier otro dato que usted ingrese en los comentarios.</li>
                    </ul>
                    <p>Al ingresar al sitio tambien podran recabarse datos mediante cookies y web beacons, incluyendo direccion IP, tipo de navegador, sistema operativo, paginas visitadas, habitos y patrones de navegacion, vinculos seguidos y el sitio visitado antes de entrar al nuestro.</p>

                    <h2>Datos sensibles</h2>
                    <p>PROPOWER ELECTROCONSTRUCCIONES no recaba datos personales sensibles.</p>

                    <h2>Finalidades primarias</h2>
                    <p>En caso de llenar el formulario de contacto, sus datos seran utilizados para contactarle, resolver sus dudas y plantear una propuesta o posible relacion comercial, con base en sus necesidades o las de la empresa que usted representa.</p>

                    <h2>Finalidades secundarias</h2>
                    <ul>
                        <li>Identificarle, ubicarle, comunicarle, contactarle, enviarle informacion y realizar uso estadistico o cientifico mediante analisis de metricas.</li>
                        <li>Desarrollar estudios sobre intereses, comportamientos y demografia de los titulares para comprender mejor sus necesidades e intereses.</li>
                        <li>Mejorar nuestras iniciativas y estrategias comerciales.</li>
                        <li>Analizar paginas de internet visitadas y busquedas efectuadas para mejorar nuestra oferta de contenido, articulos, presentacion, programacion y servicios.</li>
                        <li>Enviar informacion por correo electronico respecto de noticias o eventos relevantes.</li>
                    </ul>

                    <h2>Transferencias y conservacion</h2>
                    <p>PROPOWER ELECTROCONSTRUCCIONES no transferira sus datos personales sin su consentimiento, aunque podra utilizarlos para finalidades que dependan de terceros, como estadisticas y envio de boletines, sin que exista transferencia de dichos datos.</p>
                    <p>La temporalidad del manejo de los datos personales sera indefinida a partir de la fecha en que usted los proporcione al responsable. Usted podra oponerse en cualquier momento para efectos de bloqueo y cancelacion.</p>
                    <p>Sus datos personales seran tratados de manera licita y conforme a los principios de Licitud, Consentimiento, Informacion, Calidad, Finalidad, Lealtad, Proporcionalidad y Responsabilidad.</p>

                    <h2>Contacto y derechos ARCO</h2>
                    <p>Cualquier duda sobre este Aviso, sus datos personales y su tratamiento, o sobre como ejercer sus derechos de acceso, rectificacion, cancelacion, oposicion o revocacion del consentimiento, podra atenderse en los telefonos 614 166-6340 y 639 268-2359, de lunes a viernes de 8:00 a.m. a 5:30 p.m. y sabado de 8:00 a.m. a 1:30 p.m. en dias habiles.</p>
                    <p>Tambien puede dirigir su solicitud a PROPOWER ELECTROCONSTRUCCIONES o al correo <strong>contacto@propower.mx</strong>.</p>

                    <h2>Requisitos de solicitud</h2>
                    <p>A su solicitud debera acompanar:</p>
                    <ol>
                        <li>Fotografia o escaneo de identificacion oficial con fotografia y firma autografa. Si actua en representacion de alguien, adjuntar copia del poder notarizado y/o registrado ante el Registro Publico correspondiente y, en su caso, el acta constitutiva.</li>
                        <li>Escaneo o fotografia de un comprobante de domicilio.</li>
                    </ol>
                    <p>En el escrito debera senalar su nombre o razon social, domicilio fisico para recibir respuesta, los datos personales sobre los que desea ejercer derechos, el proposito para el cual los aporto si lo conoce, y establecer de manera clara, respetuosa y concisa su peticion.</p>

                    <h2>Plazos</h2>
                    <p>El responsable contara con un plazo de veinte dias, contados a partir de recibida la solicitud, para resolverla o requerir mayor informacion. Si la peticion resulta procedente, en un lapso no mayor a quince dias se procedera a su ejecucion.</p>
                    <p>Si el responsable requiere usar sus datos personales para fines distintos a los senalados en este Aviso de Privacidad, contactara con usted por medios escritos, telefonicos, electronicos, opticos, sonoros, visuales u otros permitidos por la tecnologia, y le explicara los nuevos usos para recabar su consentimiento.</p>

                    <div class="privacy-actions">
                        <a class="privacy-button" href="/">Volver al inicio</a>
                        <a class="privacy-button secondary" href="mailto:contacto@propower.mx">contacto@propower.mx</a>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>

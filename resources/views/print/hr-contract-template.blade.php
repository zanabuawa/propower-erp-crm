<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Contrato {{ $contract->contract_number ?? 'FOL-'.$contract->id }}</title>
    <style>
        @page { size: A4; margin: 11.3mm 11mm 7mm 11mm; }
        * { box-sizing: border-box; }
        body {
            color: #111;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10pt;
            line-height: 1.15;
            margin: 0;
        }
        .toolbar {
            align-items: center;
            background: #fff;
            border-bottom: 1px solid #ddd;
            display: flex;
            font-family: Arial, Helvetica, sans-serif;
            justify-content: space-between;
            margin: -11.3mm -11mm 12px -11mm;
            padding: 10px 11mm;
            position: sticky;
            top: 0;
            z-index: 5;
        }
        .toolbar strong { font-size: 12px; }
        .toolbar button {
            background: #111827;
            border: 0;
            border-radius: 6px;
            color: #fff;
            cursor: pointer;
            font-size: 11px;
            font-weight: 700;
            padding: 7px 12px;
        }
        h1 {
            font-size: 11pt;
            line-height: 1.15;
            margin: 4pt 0 11pt;
            text-align: center;
        }
        p {
            font-size: 10pt;
            line-height: 1.15;
            margin: 0;
            text-align: justify;
            text-indent: 0;
        }
        p + p { margin-top: 0.5pt; }
        p.clause {
            margin-top: 0.5pt;
            text-indent: 12.5mm;
        }
        h1 + p,
        p.section-gap,
        p.clause.major-gap {
            margin-top: 11.5pt;
        }
        p.roman-item {
            line-height: 1.15;
            margin: 2.5pt 0 0;
            padding-left: 9mm;
            text-indent: -7mm;
        }
        p.letter-item {
            margin-top: 0.5pt;
            text-align: justify;
        }
        strong { font-weight: 700; }
        .clause-title { font-weight: 700; }
        .page-number {
            bottom: 0;
            font-size: 10pt;
            line-height: 1.15;
            left: 0;
            margin: 0;
            position: absolute;
            right: 0;
            text-align: center;
            text-indent: 0;
        }
        .print-page {
            height: 276mm;
            padding-bottom: 13mm;
            position: relative;
        }
        .fill-page {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .editable-print-page {
            display: flex;
            flex-direction: column;
        }
        .editable-print-page .editable-page-content {
            flex: 1;
        }
        .editable-print-page.fill-page .editable-page-content {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .editable-print-page .page-number {
            margin-top: auto;
            position: static;
        }
        .signature-page {
            display: flex;
            flex-direction: column;
            height: 276mm;
        }
        .signature-page .closing-text {
            margin-top: 18pt;
        }
        .signature-page .page-number {
            margin-top: auto;
            position: static;
        }
        .page-break { break-after: page; page-break-after: always; }
        .signatures-grid {
            align-items: start;
            display: grid;
            gap: 26mm;
            grid-template-columns: 1fr 1fr;
            margin-top: 86mm;
            page-break-inside: avoid;
        }
        .sign-line {
            border-top: 1px solid #111;
            margin: 0 auto 5px;
            padding-top: 5px;
            text-align: center;
            width: 100%;
        }
        .signature { text-align: center; }
        .signature p {
            margin: 0 0 3px;
            text-align: center;
            text-indent: 0;
        }
        @media print {
            .toolbar { display: none; }
        }
    </style>
</head>
<body>
@php
    $benefits = $contract->benefits ?? [];
    $companyName = mb_strtoupper($company?->legal_name ?? $company?->name ?? 'PROPOWER ELECTROCONSTRUCCIONES');
    $companyRfc = mb_strtoupper($company?->rfc ?? 'PEL250620N87');
    $employerRep = 'JAIRO ORLANDO NAJERA BARRON';
    $employerRepRfc = 'NABJ950101G66';
    $employerRepCurp = 'NABJ950101HCHJRR08';
    $employerRepAge = 30;
    $employerRepGender = 'MASCULINO';
    $employerAddress = mb_strtoupper($company?->address ?: 'CALLE AMADA ARMENDARIZ #1289, COL. REVOLUCION, CHIHUAHUA, CHIHUAHUA');
    $employeeName = mb_strtoupper($employee->full_name);
    $employeeRfc = mb_strtoupper($employee->rfc ?? 'PENDIENTE');
    $employeeCurp = mb_strtoupper($employee->curp ?? 'PENDIENTE');
    $employeeGender = mb_strtoupper(\App\Models\HrEmployee::GENDERS[$employee->gender] ?? $employee->gender ?? 'NO ESPECIFICADO');
    $employeeAge = $employee->birth_date ? floor($employee->birth_date->diffInYears(now())) : '___';
    $position = mb_strtoupper($employee->position?->name ?? 'PUESTO PENDIENTE');
    $workplace = mb_strtoupper($company?->address ?: 'CALLE AMADA ARMENDARIZ #1289, COL. REVOLUCION, CHIHUAHUA CHIHUAHUA');
    $startDate = $contract->start_date;
    $endDate = $contract->end_date;
    $dailySalary = match ($contract->salary_period) {
        'daily' => (float) $contract->salary,
        'weekly' => (float) $contract->salary / 7,
        'biweekly' => (float) $contract->salary / 15,
        default => (float) $contract->salary / 30,
    };
    $vacationDays = $benefits['vacation_days'] ?? 12;
    $aguinaldoDays = $benefits['aguinaldo_days'] ?? 15;
    $vacationPremium = $benefits['vacation_premium_pct'] ?? 25;
    $months = [1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril', 5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto', 9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'];
    $dateLong = fn ($date) => $date ? $date->format('d').' de '.$months[(int) $date->format('n')].' de '.$date->format('Y') : 'fecha pendiente';
    $city = $company?->city ?: 'Delicias';
    $state = $company?->state ?: 'Chihuahua';
    $contractTypeText = $endDate ? 'tiempo determinado' : 'tiempo indeterminado';
    $editablePrintPages = array_values(array_filter($contract->print_pages ?? [], fn ($page) => trim((string) $page) !== ''));
    $editablePrintPages = $editablePrintPages ? array_pad(array_slice($editablePrintPages, 0, 5), 5, '') : [];
    $tokenValues = [
        '{{city}}' => '<strong>' . e($city) . '</strong>',
        '{{state}}' => '<strong>' . e($state) . '</strong>',
        '{{start_date_long}}' => '<strong>' . e($dateLong($startDate)) . '</strong>',
        '{{end_date_long}}' => '<strong>' . e($dateLong($endDate)) . '</strong>',
        '{{contract_term_clause}}' => $endDate
            ? 'con una vigencia del <strong>'.$dateLong($startDate).'</strong> al <strong>'.$dateLong($endDate).'</strong> como fecha de vencimiento, contrato que se celebra por tiempo determinado en virtud de las necesidades operativas de la empresa'
            : 'iniciando su vigencia el <strong>'.$dateLong($startDate).'</strong>, sin fecha de vencimiento pactada',
        '{{end_date_clause}}' => $endDate ? ', concluyendo el dia <strong>'.$dateLong($endDate).'</strong>' : ', sin fecha de vencimiento pactada',
        '{{company_name}}' => '<strong>' . e($companyName) . '</strong>',
        '{{company_rfc}}' => '<strong>' . e($companyRfc) . '</strong>',
        '{{employer_rep}}' => '<strong>' . e($employerRep) . '</strong>',
        '{{employer_rep_rfc}}' => '<strong>' . e($employerRepRfc) . '</strong>',
        '{{employer_rep_curp}}' => '<strong>' . e($employerRepCurp) . '</strong>',
        '{{employer_rep_age}}' => '<strong>' . e($employerRepAge) . '</strong>',
        '{{employer_rep_gender}}' => '<strong>' . e($employerRepGender) . '</strong>',
        '{{employer_address}}' => '<strong>' . e($employerAddress) . '</strong>',
        '{{employee_name}}' => '<strong>' . e($employeeName) . '</strong>',
        '{{employee_rfc}}' => '<strong>' . e($employeeRfc) . '</strong>',
        '{{employee_curp}}' => '<strong>' . e($employeeCurp) . '</strong>',
        '{{employee_gender}}' => '<strong>' . e($employeeGender) . '</strong>',
        '{{employee_age}}' => '<strong>' . e($employeeAge) . '</strong>',
        '{{position}}' => '<strong>' . e($position) . '</strong>',
        '{{workplace}}' => '<strong>' . e($workplace) . '</strong>',
        '{{contract_type_text}}' => '<strong>' . e($contractTypeText) . '</strong>',
        '{{daily_salary}}' => '<strong>' . number_format($dailySalary, 2) . '</strong>',
        '{{work_hours_per_week}}' => '<strong>' . e($contract->work_hours_per_week) . '</strong>',
        '{{entry_time}}' => '<strong>' . e(substr($contract->entry_time ?? '00:00', 0, 5)) . '</strong>',
        '{{exit_time}}' => '<strong>' . e(substr($contract->exit_time ?? '00:00', 0, 5)) . '</strong>',
        '{{work_days_label}}' => '<strong>' . e($contract->work_days_label) . '</strong>',
        '{{aguinaldo_days}}' => '<strong>' . e($aguinaldoDays) . '</strong>',
        '{{vacation_days}}' => '<strong>' . e($vacationDays) . '</strong>',
        '{{vacation_premium}}' => '<strong>' . e($vacationPremium) . '</strong>',
    ];
    $renderEditableText = function (string $text, int $pageIndex = 0) use ($tokenValues, $employeeName, $employerRep) {
        if ($pageIndex === 4 && str_contains($text, '________________________________')) {
            $closing = trim(strstr($text, '________________________________', true) ?: $text);
            $closingBlocks = preg_split("/\R{2,}/", $closing) ?: [];
            $closingHtml = collect($closingBlocks)
                ->map(fn ($block) => trim($block) !== '' ? '<p class="closing-text">'.strtr(nl2br(e(trim($block))), $tokenValues).'</p>' : '')
                ->filter()
                ->implode("\n");

            return $closingHtml.'
                <div class="signatures-grid">
                    <div class="signature">
                        <div class="sign-line"></div>
                        <p>C. '.e($employeeName).'</p>
                        <p><strong>"EL TRABAJADOR"</strong>.</p>
                    </div>

                    <div class="signature">
                        <div class="sign-line"></div>
                        <p>ING. '.e($employerRep).'</p>
                        <p><strong>"EL EMPLEADOR"</strong>.</p>
                    </div>
                </div>';
        }

        $blocks = preg_split("/\R{2,}/", trim($text)) ?: [];

        return collect($blocks)->map(function ($block, $index) use ($tokenValues) {
            $block = trim($block);
            if ($block === '') {
                return '';
            }

            $upperBlock = mb_strtoupper($block);

            if ($index === 0 && str_starts_with($upperBlock, 'CONTRATO ')) {
                return '<h1>'.strtr(nl2br(e($block)), $tokenValues).'</h1>';
            }

            if (trim($upperBlock, ". \t\n\r\0\x0B") === 'CLAUSULAS') {
                return '<p class="section-gap"><strong>'.nl2br(e($block)).'</strong></p>';
            }

            $isClause = preg_match('/^(PRIMERA|SEGUNDA|TERCERA|CUARTA|QUINTA|SEXTA|SEPTIMA|OCTAVA|NOVENA|DECIMA|VIGESIMA|VIGECIMA|CLAUSULA)\b/u', $upperBlock);
            $isRoman = preg_match('/^(I|II|III|IV|V|VI|VII|VIII|IX|X|XI|XII|XIII|XIV|XV)\./', trim($block));
            $isLetterItem = preg_match('/^[a-d]\)\.\s*-/i', trim($block));
            $needsMajorGap = preg_match('/^(CUARTA|DECIMA SEGUNDA|DECIMA OCTAVA|VIGECIMA OCTAVA)\b/u', $upperBlock);
            $class = $isRoman
                ? ' class="roman-item"'
                : ($isClause ? ' class="clause'.($needsMajorGap ? ' major-gap' : '').'"' : ($isLetterItem ? ' class="letter-item"' : ''));
            
            $escaped = nl2br(e($block));

            if ($isClause) {
                $escaped = preg_replace(
                    '/^([A-ZÁÉÍÓÚÑ ]+?\.)/u',
                    '<span class="clause-title">$1</span>',
                    $escaped,
                    1
                );
            }

            return '<p'.$class.'>'.strtr($escaped, $tokenValues).'</p>';
        })->filter()->implode("\n");
    };
@endphp

<div class="toolbar">
    <strong>{{ $contract->contract_number ?? 'FOL-'.$contract->id }} · {{ $employeeName }}</strong>
    <button type="button" onclick="window.print()">Imprimir contrato</button>
</div>

<script>
    function emphasizeContractTerms() {
        const root = document.getElementById('contract-body');
        if (!root || root.dataset.emphasized === '1') return;

        const matchPattern = /(PROPOWER ELECTROCONSTRUCCIONES|"EL TRABAJADOR"|"EL PATRON"|EL TRABAJADOR|EL PATRON)/;
        const splitPattern = /(PROPOWER ELECTROCONSTRUCCIONES|"EL TRABAJADOR"|"EL PATRON"|EL TRABAJADOR|EL PATRON)/g;
        const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, {
            acceptNode(node) {
                const parent = node.parentElement;
                if (!parent || ['SCRIPT', 'STYLE', 'STRONG'].includes(parent.tagName)) {
                    return NodeFilter.FILTER_REJECT;
                }

                return matchPattern.test(node.nodeValue)
                    ? NodeFilter.FILTER_ACCEPT
                    : NodeFilter.FILTER_REJECT;
            }
        });

        const nodes = [];
        while (walker.nextNode()) nodes.push(walker.currentNode);

        nodes.forEach(node => {
            const fragment = document.createDocumentFragment();
            const parts = node.nodeValue.split(splitPattern);

            parts.forEach(part => {
                if (!part) return;
                if (matchPattern.test(part)) {
                    const strong = document.createElement('strong');
                    strong.textContent = part;
                    fragment.appendChild(strong);
                    return;
                }
                fragment.appendChild(document.createTextNode(part));
            });

            node.parentNode.replaceChild(fragment, node);
        });

        root.dataset.emphasized = '1';
    }

    window.addEventListener('DOMContentLoaded', emphasizeContractTerms);
</script>

@if(request()->boolean('print'))
    <script>
        window.addEventListener('load', function () {
            emphasizeContractTerms();
            window.setTimeout(function () {
                window.print();
            }, 250);
        });
    </script>
@endif

<main id="contract-body">
@if($editablePrintPages)
    @foreach($editablePrintPages as $index => $page)
        <section class="print-page editable-print-page {{ $index < 4 ? 'fill-page' : 'signature-page' }}">
            <div class="editable-page-content">
                {!! $renderEditableText($page, $index) !!}
                @if($index === 2 && $contract->print_custom_clauses && ! str_contains(mb_strtoupper($page), 'CLAUSULA ESPECIAL'))
                    <p class="clause">
                        <span class="clause-title">CLAUSULA ESPECIAL.</span>
                        {!! nl2br(e($contract->print_custom_clauses)) !!}
                    </p>
                @endif
            </div>
            <p class="page-number">Pagina {{ $index + 1 }} de {{ count($editablePrintPages) }}</p>
        </section>
        @if($index < count($editablePrintPages) - 1)
            <div class="page-break"></div>
        @endif
    @endforeach
@else
<section class="print-page fill-page">
<h1>CONTRATO INDIVIDUAL DE TRABAJO</h1>

<p>
    En la ciudad de {{ $city }}, estado de {{ $state }}, siendo el dia {{ $dateLong($startDate) }}, los que suscriben el presente, a saber,
    C. {{ $companyName }} con RFC: {{ $companyRfc }}, representada por C. {{ $employerRep }} con RFC: {{ $employerRepRfc }}
    CURP: {{ $employerRepCurp }}, nacionalidad: MEXICANA edad: {{ $employerRepAge }} AÑOS; Sexo: {{ $employerRepGender }}, domicilio: {{ $employerAddress }},
    quien en el curso del presente contrato se denominara "EL PATRON", y, por la otra C. {{ $employeeName }}, con RFC:
    {{ $employeeRfc }}, CURP: {{ $employeeCurp }}, nacionalidad: MEXICANA, edad: {{ $employeeAge }} AÑOS; sexo:
    {{ $employeeGender }}, quien en el curso del presente contrato se denominara "EL TRABAJADOR", hacemos constar que
    hemos convenido celebrar un CONTRATO INDIVIDUAL DE TRABAJO, al tenor de las siguientes:
</p>

<p class="section-gap"><strong>CLAUSULAS.</strong></p>

<p class="clause">
    <span class="clause-title">PRIMERA.</span> Para los efectos del articulo 25 de la Ley Federal de Trabajo, manifiesta
    "EL PATRON" llamarse como ha quedado asentado en el proemio, y que esta se dedica al ramo de
    ELECTROCONSTRUCCIONES, entre otras del objeto social; Asi mismo "EL TRABAJADOR" declara llamarse como quedo
    escrito en el proemio, por su propio derecho. Reconociendo el trabajador el citado domicilio como unica fuente de trabajo
    para todos los efectos legales a que haya lugar y con el conocimiento de que es contratado para que preste sus servicios
    personales subordinados juridicamente a la patronal unica y exclusivamente este como unico patron, mediante el pago de
    un salario por virtud del presente contrato de trabajo.
</p>

<p class="clause">
    <span class="clause-title">SEGUNDA.</span> "EL TRABAJADOR" se obliga a prestar sus servicios personales para
    "EL PATRON" subordinado juridicamente a este, con el puesto de {{ $position }} consistiendo sus funciones en: las
    inherentes a dicho puesto, asi como los mandados fuera de la negociacion y todas las inherentes al puesto que
    desempeñara actividades que se señalan unicamente en forma enunciativa y no limitativa. Este trabajo debera ejecutarlo
    con esmero y eficiencia. Queda expresamente convenido que acatara en el desempeño de su trabajo, todas las
    disposiciones que dicte el patron, asi como las contenidas en el reglamento interior del trabajo y todos los ordenamientos
    legales que le sean aplicables.
</p>

<p class="clause">
    <span class="clause-title">TERCERA.</span> "EL TRABAJADOR" debera ejecutar su trabajo en el local y/o oficinas del
    patron mismas que se encuentran ubicadas en {{ $workplace }}, o bien en cualquier lugar que el patron le ordene, en
    cualquier parte de la republica donde opere la sociedad patronal, otorgando desde este momento el trabajador su
    consentimiento expreso para ello, por requerir el personal en la consecucion del objeto social del patron y que es el unico
    patron del trabajador contratado por el presente instrumento.
</p>

<p class="clause major-gap">
    <span class="clause-title">CUARTA.</span> Este contrato se celebra por {{ $contractTypeText }},
    @if($endDate)
        con una vigencia del {{ $dateLong($startDate) }} al {{ $dateLong($endDate) }} como fecha de vencimiento,
        contrato que se celebra por tiempo determinado en virtud de las necesidades operativas de la empresa.
    @else
        iniciando su vigencia el {{ $dateLong($startDate) }}, sin fecha de vencimiento pactada.
    @endif
</p>

<p class="clause">
    <span class="clause-title">QUINTA.</span> "EL TRABAJADOR" percibira, por la prestacion de los servicios a que se
    refiere este contrato, un salario diario de ${{ number_format($dailySalary, 2) }} pesos mexicanos.
</p>
<p>
    "EL TRABAJADOR" conviene, acepta y otorga su consentimiento expreso, para que "EL PATRON" le deposite el pago
    de su salario y demas prestaciones mediante deposito en cuenta de banco a nombre de "EL TRABAJADOR", pudiendo
    este retirar las cantidades depositadas directamente de ventanilla en el banco, o de cajero automatico mediante tarjeta
    de debito, o bien, haciendose el retiro por cualquier otro procedimiento que permita la disponibilidad del dinero en
    efectivo. El deposito en la cuenta bancaria se podra hacer por la totalidad del salario y demas prestaciones que le
    correspondan o bien, se podra hacer el deposito en la cuenta bancaria de solo una parte del salario o las prestaciones
    correspondientes y el resto pagarsele en dinero en efectivo.
</p>

<p class="clause">
    <span class="clause-title">SEXTA.</span> Queda expresamente convenido que el trabajador no podra dedicarse (ni aun a
    titulo gratuito) a la venta de ningun otro producto o bien ya sea mueble o inmueble de ninguna otra empresa dentro de su
    jornada de trabajo, y que, fuera de su jornada de trabajo, le estara estrictamente prohibido dedicarse a cualquier labor que
    signifique en cualquier forma una competencia para las actividades del patron, en caso de inobservancia de la presente
    clausula se considerara causal de rescision de la relacion laboral sin responsabilidad para el patron por causas imputables
    al trabajador.
</p>

<p class="clause">
    <span class="clause-title">SEPTIMA.</span> La duracion de la jornada de trabajo sera de {{ $contract->work_hours_per_week }}
    horas a la semana, conforme al horario de {{ substr($contract->entry_time ?? '00:00', 0, 5) }} a
    {{ substr($contract->exit_time ?? '00:00', 0, 5) }} horas, en los dias {{ $contract->work_days_label }}, para lo cual el
    trabajador concede al
</p>

<p class="page-number">Pagina 1 de 5</p>
</section>
<div class="page-break"></div>

<section class="print-page fill-page">
<p>
    patron la facultad de repartir o combinar los horarios de trabajo de acuerdo a las necesidades de la empresa sin que por
    ello se entienda como una modificacion unilateral de las condiciones de trabajo, toda vez que el trabajador desde este
    momento otorga consentimiento para ello, de conformidad con el articulo 59 de la Ley Federal del Trabajo.
</p>
<p class="clause">
    <span class="clause-title">OCTAVA.</span> Cuando por circunstancias extraordinarias se aumente la jornada de trabajo,
    los servicios prestados durante el tiempo excedente se consideraran como extraordinarios.
</p>
<p class="clause">
    <span class="clause-title">NOVENA.</span> "EL TRABAJADOR" esta obligado a checar su tarjeta o a firmar las listas de
    asistencia, a la hora de entrada y a la hora de salida de sus labores, su periodo para descansar y tomar sus alimentos
    sera designado conforme al horario que determine el patron.
</p>
<p class="clause">
    <span class="clause-title">DECIMA.</span> Por cada seis dias de trabajo, tendra el trabajador un descanso semanal de
    un dia, con pago de salario integro, conviniendose en que dicho descanso lo disfrutara el dia que la empresa considere
    prudente debido al giro de la misma. Tambien disfrutara del pago señalado segun la ley, los dias señalados en el articulo
    74 de la Ley Federal del Trabajo, a saber: el 1 de enero, el primer lunes de febrero, el tercer lunes de marzo, el 1 de mayo,
    el 16 de septiembre, el tercer lunes de noviembre, el 25 de diciembre y el 1 de diciembre de cada seis años cuando
    corresponda a la transmision del Poder Ejecutivo Federal.
</p>
<p class="clause">
    <span class="clause-title">DECIMA PRIMERA.</span> "EL TRABAJADOR", despues de un año de servicios continuos
    disfrutara de un periodo anual de vacaciones pagadas, de {{ $vacationDays }} dias laborales, que aumentara en dos dias
    laborales, hasta llegar a veinte, por cada año subsecuente de servicios. Despues del sexto año, el periodo de vacaciones
    aumentara en dos dias por cada cinco años de servicios. Los salarios correspondientes a las vacaciones se cubriran con
    una prima del {{ $vacationPremium }} por ciento sobre los mismos.
</p>
<p>
    En caso de faltas injustificadas de asistencia al trabajo, se podran deducir dichas faltas del periodo de prestacion de
    servicios computable para fijar las vacaciones, reduciendose estas proporcionalmente.
</p>
<p>
    Las vacaciones no podran compensarse con una remuneracion economica. Si la relacion de trabajo termina antes de que
    se cumpla el año de servicios, el trabajador tendra derecho a una remuneracion proporcionada al tiempo de servicios prestados.
</p>
<p class="clause major-gap">
    <span class="clause-title">DECIMA SEGUNDA.</span> "EL TRABAJADOR" percibira un aguinaldo anual, que debera
    pagarsele antes del dia veinte de diciembre, equivalente a {{ $aguinaldoDays }} dias de salario.
</p>
<p>
    Cuando no haya cumplido el año de servicios, tendra derecho a que se le pague en proporcion al tiempo trabajado.
</p>
<p class="clause">
    <span class="clause-title">DECIMA TERCERA.</span> "EL TRABAJADOR" conviene en someterse a los reconocimientos
    medicos que periodicamente ordene el patron en los terminos de la fraccion X del articulo 134 de la Ley Federal del
    Trabajo; en la inteligencia de que el medico que los practique sera designado y retribuido por el patron.
</p>
<p class="clause">
    <span class="clause-title">DECIMA CUARTA.</span> "EL TRABAJADOR" sera capacitado o adiestrado en los terminos de
    los planes y programas establecidos, o que se establezcan, por el patron, conforme a lo dispuesto en el Capitulo II Bis,
    Titulo Cuarto de la Ley Federal del Trabajo.
</p>
<p class="clause">
    <span class="clause-title">DECIMA QUINTA.</span> Para los efectos de su antiguedad, queda establecido que el trabajador
    ingreso a prestar sus servicios personales subordinados mediante pago de un salario para el patron en el dia
    {{ $dateLong($startDate) }}.
</p>
<p class="clause">
    <span class="clause-title">DECIMA SEXTA.</span> Las partes convienen que todo lo no previsto en el presente contrato se
    regira por lo dispuesto en el Reglamento Interior del Trabajo, la Ley Federal del Trabajo, y en que, para todo lo que se
    refiera a interpretacion, ejecucion y cumplimiento del mismo, se someten expresamente a la jurisdiccion y competencia de
    la autoridad laboral correspondiente.
</p>
<p class="clause">
    <span class="clause-title">DECIMA SEPTIMA.</span> Queda expresamente convenido, que debido a la naturaleza de las
    labores que debe prestar "EL TRABAJADOR", este podra ser cambiado indistintamente del lugar que preste sus servicios,
    a cualquier lugar que el patron lo llegara a necesitar por motivo de su empleo y/o capacitacion, y tambien se le podra
    modificar el horario de labores, siempre dentro de la jornada legal, lo anterior de acuerdo a las necesidades del servicio.
</p>

<p class="page-number">Pagina 2 de 5</p>
</section>
<div class="page-break"></div>

<section class="print-page fill-page">
<p class="clause major-gap">
    <span class="clause-title">DECIMA OCTAVA.</span> Se establece con fundamento en el Articulo 349 de la Ley Federal del
    Trabajo, como causal especial de la rescision de la relacion de trabajo por causas imputables al trabajador, el hecho que
    este no atienda con cortesia y esmero a la clientela del establecimiento, sin que esta clausula sea contraria a la Ley Laboral.
</p>
<p class="clause">
    <span class="clause-title">VIGESIMA.</span> "EL TRABAJADOR" se obliga a no revelar a terceras personas los nombres
    de los proveedores, informacion confidencial de las ventas que se efectuan en la fuente de trabajo, estudios, tecnicas que
    se utilicen en la fuente de trabajo, cartera de clientes, etc. o cualquier otro asunto relacionado con la empresa y/o de
    cualquier otra actividad de los cuales tenga conocimiento por razon del trabajo que va a realizar, asi como los asuntos
    administrativos cuya divulgacion pueda perjudicar a su patron mientras este vigente el presente contrato, siendo causal
    de rescisión de la relacion de trabajo sin responsabilidad para su patron lo anterior.
</p>
<p class="clause">
    <span class="clause-title">VIGESIMA PRIMERA.</span> "EL TRABAJADOR" se obliga a presentarse a laborar con el uniforme
    correspondiente, el cual debera de portarlo con limpieza y pulcritud.
</p>
<p>
    Asi mismo "EL TRABAJADOR" faculta desde este momento a "EL PATRON" para que, en caso de no acatar las
    disposiciones anteriormente señaladas, este podra imponerle a "EL TRABAJADOR" las medidas disciplinarias
    correspondientes que podran ser:
</p>
<p>a). - Una amonestacion verbal.</p>
<p>b). - Una amonestacion por escrito.</p>
<p>c). - Una suspension de uno a tres dias sin goce de sueldo.</p>
<p>d). - La rescisión del contrato y la relacion de trabajo por causas imputables a "EL TRABAJADOR".</p>
<p class="clause">
    <span class="clause-title">VIGESIMA SEGUNDA.</span> "EL TRABAJADOR" faculta y autoriza expresamente a "EL PATRON"
    a efectuar descuentos en su salario por concepto de pago de uniforme que utiliza para el desempeño de sus labores el
    cual es de su exclusiva propiedad, descuentos que deberan apegarse a lo establecido por el articulo 110 y demas relativos
    de la Ley Federal del Trabajo.
</p>
<p class="clause">
    <span class="clause-title">VIGESIMA TERCERA.</span> "EL TRABAJADOR" reconoce que hasta la fecha se ha desempeñado
    en una jornada legal de labores, asi como que se le ha cubierto hasta el dia de hoy de manera integra todas y cada una
    de las prestaciones de ley entre las que se encuentra el tiempo extraordinario cuando lo llego a laborar entre otras, con
    excepcion del aguinaldo proporcional a este ultimo año.
</p>
<p class="clause">
    <span class="clause-title">VIGESIMA CUARTA.</span> "EL TRABAJADOR" reconoce que dada la naturaleza del empleo
    realiza, y con anterioridad ha realizado, trabajos de investigacion y perfeccionamiento de los procedimientos utilizados por
    el patron y los clientes de este, y que los realiza por instrucciones, bajo la supervision y con la asesoria del patron, asi
    como por cuenta de este, por lo que reconoce que la propiedad de cualesquier invencion y el derecho a la explotacion de
    la patente respectiva corresponderan al patron.
</p>
<p class="clause">
    <span class="clause-title">VIGESIMA QUINTA.</span> "EL TRABAJADOR" se obliga a cumplir con el articulo 21 de la Ley
    Federal De Proteccion De Datos Personales En Posesion De Los Particulares. El responsable o terceros que intervengan
    en cualquier fase del tratamiento de datos personales deberan guardar confidencialidad respecto a estos, obligacion que
    subsistira aun despues de finalizar sus relaciones con el titular o, en su caso, con el responsable.
</p>
<p class="clause">
    <span class="clause-title">VIGESIMA SEXTA.</span> "EL PATRON", se encuentra obligado a solicitar al beneficiario unico
    en caso de muerte del trabajador, se apersone ante la autoridad laboral o junta de conciliacion y arbitraje competente, a
    fin de garantizar que cuente con el caracter de beneficiario unico vigente, antes de realizar el pago correspondiente.
</p>
<p class="clause">
    <span class="clause-title">VIGESIMA SEPTIMA.</span> "EL PATRON" podra permitir el uso a "EL TRABAJADOR", de un
    automovil propiedad de "EL PATRON", unica y exclusivamente por motivo de las actividades propias de su trabajo, por lo
    que queda prohibido a "EL TRABAJADOR" hacer uso de dicho automovil para uso personal.
</p>

@if($contract->print_custom_clauses)
    <p class="clause">
    <span class="clause-title">CLAUSULA ESPECIAL.</span>
        {!! nl2br(e($contract->print_custom_clauses)) !!}
    </p>
@endif

<p class="page-number">Pagina 3 de 5</p>
</section>
<div class="page-break"></div>

<section class="print-page fill-page">
<p>
    Asi mismo, "EL TRABAJADOR", tiene la obligacion de devolver y guardar el vehiculo diariamente, despues de la jornada
    de trabajo, al lugar en donde le indique "EL PATRON".
</p>
<p class="clause major-gap">
    <span class="clause-title">VIGECIMA OCTAVA.</span> Con fundamento en el articulo 47 de la Ley Federal del Trabajo,
    son causas de rescision de la relacion de trabajo, sin responsabilidad para el patron:
</p>
<p class="roman-item">I. Engañarlo el trabajador o en su caso, el sindicato que lo hubiese propuesto o recomendado con certificados falsos o referencias.</p>
<p class="roman-item">II. Incurrir el trabajador, durante sus labores, en faltas de probidad u honradez, actos de violencia, amagos, injurias o malos tratamientos.</p>
<p class="roman-item">III. Cometer el trabajador contra alguno de sus compañeros cualquiera de los actos enumerados en la fraccion anterior.</p>
<p class="roman-item">IV. Cometer el trabajador, fuera del servicio, contra el patron, sus familiares o personal directivo administrativo, actos graves.</p>
<p class="roman-item">V. Ocasionar el trabajador, intencionalmente, perjuicios materiales durante el desempeño de las labores.</p>
<p class="roman-item">VI. Ocasionar el trabajador perjuicios graves, sin dolo, pero con negligencia tal que sea la causa unica del perjuicio.</p>
<p class="roman-item">VII. Comprometer el trabajador, por su imprudencia o descuido inexcusable, la seguridad del establecimiento o de las personas.</p>
<p class="roman-item">VIII. Cometer el trabajador actos inmorales en el establecimiento o lugar de trabajo.</p>
<p class="roman-item">IX. Revelar el trabajador los secretos de fabricacion o dar a conocer asuntos de caracter reservado, con perjuicio de la empresa.</p>
<p class="roman-item">X. Tener el trabajador mas de tres faltas de asistencia en un periodo de treinta dias, sin permiso del patron o sin causa justificada.</p>
<p class="roman-item">XI. Desobedecer el trabajador al patron o a sus representantes, sin causa justificada, siempre que se trate del trabajo contratado.</p>
<p class="roman-item">XII. Negarse el trabajador a adoptar las medidas preventivas o a seguir los procedimientos indicados para evitar accidentes o enfermedades.</p>
<p class="roman-item">XIII. Concurrir el trabajador a sus labores en estado de embriaguez o bajo la influencia de algun narcotico o droga enervante.</p>
<p class="roman-item">XIV. La sentencia ejecutoriada que imponga al trabajador una pena de prision, que le impida el cumplimiento de la relacion de trabajo.</p>
<p class="roman-item">XV. Las analogas a las establecidas en las fracciones anteriores, de igual manera graves y de consecuencias semejantes.</p>

<p class="page-number">Pagina 4 de 5</p>
</section>
<div class="page-break"></div>

<section class="print-page signature-page">
<p class="closing-text">
    Leido que fue el presente contrato por las partes, e impuestas de su contenido y fuerza legal, lo firmaron, quedando un
    tanto en poder de cada una de las mismas.
</p>

<div class="signatures-grid">
    <div class="signature">
        <div class="sign-line"></div>
        <p>C. {{ $employeeName }}</p>
        <p><strong>"EL TRABAJADOR"</strong>.</p>
    </div>

    <div class="signature">
        <div class="sign-line"></div>
        <p>ING. JAIRO ORLANDO NAJERA BARRON</p>
        <p><strong>"EL EMPLEADOR"</strong>.</p>
    </div>
</div>

<p class="page-number">Pagina 5 de 5</p>
</section>
@endif
</main>
</body>
</html>

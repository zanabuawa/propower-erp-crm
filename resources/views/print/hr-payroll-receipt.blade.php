<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo complemento de nomina - {{ $employee->full_name }}</title>
    <style>
        @page { size: letter; margin: 18mm; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: #f8fafc;
            color: #2f2f35;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
        }
        .no-print {
            padding: 12px 20px;
            text-align: right;
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
        }
        .no-print button {
            border: 0;
            border-radius: 8px;
            background: #b6374a;
            color: #fff;
            cursor: pointer;
            font-size: 12px;
            font-weight: 700;
            padding: 9px 18px;
        }
        .page {
            width: 216mm;
            min-height: 279mm;
            margin: 18px auto;
            background: white;
            padding: 22mm 18mm;
            box-shadow: 0 10px 40px rgba(15, 23, 42, .08);
        }
        h1 {
            margin: 0 0 22px;
            font-size: 15px;
            letter-spacing: .02em;
            font-weight: 800;
        }
        .band {
            display: inline-block;
            min-width: 210px;
            margin-bottom: 7px;
            padding: 5px 8px;
            background: #b6374a;
            color: #fff;
            font-size: 14px;
            font-weight: 800;
            letter-spacing: .03em;
            text-transform: uppercase;
        }
        .muted { color: #4b5563; }
        .section { margin-top: 26px; }
        .line { margin: 4px 0; }
        .row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 26px;
            align-items: start;
        }
        .field-row {
            display: grid;
            grid-template-columns: 120px 1fr;
            align-items: center;
            gap: 8px;
            margin-bottom: 11px;
        }
        .box {
            min-height: 22px;
            border: 2px solid #3f3f46;
            padding: 2px 7px;
            font-weight: 700;
        }
        .checks {
            display: grid;
            gap: 3px;
            margin-top: 7px;
        }
        .check-line {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .check {
            width: 18px;
            height: 18px;
            border: 2px solid #52525b;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 900;
            line-height: 1;
        }
        .totals .field-row {
            grid-template-columns: 150px 120px;
            margin-bottom: 0;
        }
        .totals .box {
            min-height: 22px;
            font-size: 13px;
            padding-left: 14px;
        }
        .bank .field-row {
            grid-template-columns: 118px 210px;
        }
        .declaration {
            max-width: 540px;
            margin-top: 88px;
            font-size: 14px;
            line-height: 1.38;
        }
        .signatures {
            display: grid;
            grid-template-columns: 250px 250px;
            gap: 85px;
            margin-top: 125px;
        }
        .signature {
            text-align: left;
        }
        .signature-box {
            height: 54px;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            padding-bottom: 5px;
        }
        .signature-box img {
            max-width: 210px;
            max-height: 48px;
            object-fit: contain;
        }
        .signature-line {
            border-top: 1px solid #3f3f46;
            padding-top: 8px;
            font-size: 13px;
        }
        .example-watermark {
            margin-top: 26px;
            color: #b45309;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .12em;
            text-transform: uppercase;
        }
        @media print {
            body { background: white; }
            .no-print { display: none !important; }
            .page {
                width: auto;
                min-height: auto;
                margin: 0;
                padding: 0;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()">Imprimir / Guardar PDF</button>
    </div>

    @php
        $companyName = $company?->legal_name ?: $company?->name ?: 'Empresa';
        $companyAddress = collect([$company?->address, $company?->city, $company?->state, $company?->country])->filter()->join(', ');
        $paymentMethod = $employee->payment_method ?: 'transferencia';
        $lastFour = $employee->clabe
            ? substr(preg_replace('/\D+/', '', $employee->clabe), -4)
            : ($employee->bank_account ? substr(preg_replace('/\D+/', '', $employee->bank_account), -4) : '');
    @endphp

    <main class="page">
        <h1>RECIBO&nbsp; COMPLEMENTO DE NOMINA</h1>

        <section>
            <div class="band">{{ $companyName }}</div>
            <p class="line">RFC: {{ $company?->rfc ?? 'N/A' }}</p>
            <p class="line">Domicilio Fiscal: {{ $companyAddress ?: 'N/A' }}</p>
        </section>

        <section class="section">
            <div class="band">
                {{ $employee->employee_number ? $employee->employee_number.'- ' : '' }}{{ $employee->full_name }}
            </div>
            <p class="line">RFC: {{ $employee->rfc ?: 'N/A' }} &nbsp;&nbsp;&nbsp; CURP: {{ $employee->curp ?: 'N/A' }}</p>
            <p class="line">
                {{ $employee->position?->name ?? 'Puesto no asignado' }}
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                {{ $employee->department?->name ?? 'Departamento no asignado' }}
            </p>
        </section>

        <section class="section row">
            <div>
                <div class="field-row">
                    <span>Periodo:</span>
                    <span class="box">{{ $periodStart->format('d/m/Y') }} - {{ $periodEnd->format('d/m/Y') }}</span>
                </div>

                <div class="checks">
                    <div class="check-line"><span class="check">{{ $paymentMethod === 'transferencia' ? 'X' : '' }}</span>Transferencia bancaria</div>
                    <div class="check-line"><span class="check">{{ $paymentMethod === 'efectivo' ? 'X' : '' }}</span>Efectivo</div>
                    <div class="check-line"><span class="check">{{ $paymentMethod === 'cheque' ? 'X' : '' }}</span>Cheque</div>
                </div>

                <div class="bank section" style="margin-top: 19px;">
                    <div class="field-row">
                        <span>Banco</span>
                        <span class="box">{{ $employee->bank ?: '' }}</span>
                    </div>
                    <div class="field-row">
                        <span>Cuenta/CLABE<br><small>(Ultimos 4 digitos)</small></span>
                        <span class="box">{{ $lastFour }}</span>
                    </div>
                </div>
            </div>

            <div>
                <div class="field-row">
                    <span>Fecha de pago:</span>
                    <span class="box">{{ $paymentDate->format('d/m/Y') }}</span>
                </div>

                <div class="totals" style="margin-top: 42px;">
                    <div class="field-row">
                        <span>Total Percepciones</span>
                        <span class="box">$ {{ number_format((float) $item['gross_salary'], 2) }}</span>
                    </div>
                    <div class="field-row">
                        <span>Total Deducciones</span>
                        <span class="box">$ {{ number_format((float) $item['total_deductions'], 2) }}</span>
                    </div>
                    <div class="field-row" style="font-weight: 800;">
                        <span>Neto a pagar:</span>
                        <span class="box">$ {{ number_format((float) $item['net_salary'], 2) }}</span>
                    </div>
                </div>
            </div>
        </section>

        <p class="declaration">
            Declaro que recibi el pago correspondiente a este periodo, asi como
            el CFDI de nomina estando conforme con la informacion contenida.
        </p>

        @if($isExample)
            <p class="example-watermark">Ejemplo generado desde sistema - no sustituye el CFDI oficial</p>
        @endif

        <section class="signatures">
            <div class="signature">
                <div class="signature-box">
                    @if($employee->user?->signature)
                        <img src="{{ $employee->user->signature }}" alt="Firma empleado">
                    @endif
                </div>
                <div class="signature-line">C. {{ $employee->full_name }}</div>
            </div>
            <div class="signature">
                <div class="signature-box">
                    @if($responsible?->signature)
                        <img src="{{ $responsible->signature }}" alt="Firma responsable">
                    @endif
                </div>
                <div class="signature-line">Responsable de nomina/ RH.</div>
            </div>
        </section>
    </main>
</body>
</html>

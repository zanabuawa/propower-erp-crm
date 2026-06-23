<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de incidencias - {{ $project->name }}</title>
    <style>
        @page { margin: 16mm; }
        body { font-family: Arial, sans-serif; color: #111827; font-size: 12px; }
        .print-btn { position: fixed; top: 16px; right: 16px; background: #4f46e5; color: #fff; border: 0; padding: 8px 18px; border-radius: 8px; font-weight: 700; cursor: pointer; }
        @media print { .print-btn { display: none; } }
        .header { display: grid; grid-template-columns: 210px 1fr 210px; align-items: center; gap: 18px; border-bottom: 2px solid #e5e7eb; padding-bottom: 14px; margin-bottom: 18px; }
        .logo-wrap { text-align: center; }
        .logo { height: 118px; display: flex; align-items: center; justify-content: center; }
        .logo img { max-height: 112px; max-width: 200px; object-fit: contain; }
        .placeholder { width: 104px; height: 104px; border-radius: 10px; background: #eef2ff; color: #4f46e5; display: flex; align-items: center; justify-content: center; font-size: 36px; font-weight: 800; }
        .role-badge { display: inline-block; margin-top: 6px; padding: 4px 10px; border-radius: 999px; background: #eef2ff; color: #4338ca; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: .12em; }
        .title { text-align: center; }
        .title h1 { margin: 0 0 8px; font-size: 20px; text-transform: uppercase; letter-spacing: .08em; }
        .title p { margin: 3px 0; color: #4b5563; }
        .section { margin-top: 14px; break-inside: avoid; }
        .section h2 { font-size: 12px; text-transform: uppercase; letter-spacing: .08em; color: #374151; border-bottom: 1px solid #e5e7eb; padding-bottom: 6px; }
        .meta { margin: 0 0 10px; color: #4b5563; font-size: 11px; }
        .incident-card { margin-bottom: 18px; padding: 14px 16px; border: 1px solid #e5e7eb; border-radius: 10px; break-inside: avoid; }
        .incident-card h2 { margin: 0 0 6px; font-size: 14px; color: #111827; }
        .text { white-space: pre-line; line-height: 1.5; color: #1f2937; }
        .custom-body { white-space: normal; line-height: 1.55; color: #1f2937; }
        .custom-body h1 { margin: 18px 0 10px; font-size: 18px; font-weight: 800; text-transform: uppercase; letter-spacing: .08em; color: #111827; }
        .custom-body h2 { margin: 16px 0 9px; padding-bottom: 6px; border-bottom: 1px solid #e5e7eb; font-size: 14px; font-weight: 800; text-transform: uppercase; letter-spacing: .1em; color: #1f2937; }
        .custom-body h3 { margin: 12px 0 7px; font-size: 13px; font-weight: 700; color: #1f2937; }
        .custom-body p { margin: 0 0 10px; }
        .custom-body ul { margin: 0 0 12px; padding-left: 24px; list-style: disc; }
        .custom-body ol { margin: 0 0 12px; padding-left: 24px; list-style: decimal; }
        .custom-body li { margin-bottom: 5px; display: list-item; }
        .custom-body blockquote { margin: 12px 0; padding-left: 14px; border-left: 4px solid #c7d2fe; color: #4b5563; font-style: italic; }
    </style>
</head>
<body onload="window.print()">
    <button class="print-btn" onclick="window.print()">Imprimir / Guardar PDF</button>

    @php
        $companyLogo = $company?->print_logo ?? $company?->logo;
        $customerLogo = $customer?->image;
    @endphp

    <div class="header">
        <div class="logo-wrap">
            <div class="logo">
                @if($companyLogo)
                    <img src="{{ asset('storage/' . $companyLogo) }}" alt="{{ $company?->name }}">
                @else
                    <div class="placeholder">{{ mb_strtoupper(mb_substr($company?->name ?? 'E', 0, 1)) }}</div>
                @endif
            </div>
            <span class="role-badge">Prestador</span>
        </div>
        <div class="title">
            <h1>Reporte de incidencias de obra</h1>
            <p><strong>Proyecto:</strong> {{ $project->name }}</p>
            <p><strong>Fecha:</strong> {{ now()->format('d/m/Y') }}</p>
        </div>
        <div class="logo-wrap">
            <div class="logo">
                @if($customerLogo)
                    <img src="{{ asset('storage/' . $customerLogo) }}" alt="{{ $customer?->name }}">
                @else
                    <div class="placeholder">{{ mb_strtoupper(mb_substr($customer?->name ?? 'C', 0, 1)) }}</div>
                @endif
            </div>
            <span class="role-badge">Cliente</span>
        </div>
    </div>

    <div class="section">
        <h2>Incidencias registradas</h2>

        @forelse($reports as $report)
            <div class="incident-card">
                <h2>Semana {{ $report->week_start?->format('d/m/Y') }} - {{ $report->week_end?->format('d/m/Y') }}</h2>
                <p class="meta">
                    <strong>Capturo:</strong> {{ $report->createdBy?->name ?? 'Sin responsable' }}
                </p>

                @if($report->issues)
                    <div class="text">{{ $report->issues }}</div>
                @elseif($report->custom_body)
                    <div class="custom-body">{!! $report->custom_body !!}</div>
                @elseif($report->activities)
                    <div class="text">{{ $report->activities }}</div>
                @else
                    <div class="text">Sin incidencias capturadas.</div>
                @endif
            </div>
        @empty
            <p>Sin reportes de incidencias registrados.</p>
        @endforelse
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de incidencia - {{ $project->name }}</title>
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
        .meta-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin: 18px 0; }
        .meta-item { border: 1px solid #e5e7eb; border-radius: 8px; padding: 10px 12px; }
        .meta-item span { display: block; margin-bottom: 4px; color: #6b7280; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: .08em; }
        .meta-item strong { font-size: 12px; color: #111827; }
        .section { margin-top: 16px; break-inside: avoid; }
        .section h2 { font-size: 12px; text-transform: uppercase; letter-spacing: .08em; color: #374151; border-bottom: 1px solid #e5e7eb; padding-bottom: 6px; }
        .text { white-space: pre-line; line-height: 1.55; color: #1f2937; }
    </style>
</head>
<body onload="window.print()">
    <button class="print-btn" onclick="window.print()">Imprimir / Guardar PDF</button>

    @php
        $companyLogo = $company?->print_logo ?? $company?->logo;
        $customerLogo = $customer?->image;
        $statusLabels = [
            'abierta' => 'Abierta',
            'en_revision' => 'En revision',
            'cerrada' => 'Cerrada',
        ];
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
            <h1>Reporte de incidencia</h1>
            <p><strong>Proyecto:</strong> {{ $project->name }}</p>
            <p><strong>Fecha de incidencia:</strong> {{ $report->incident_date?->format('d/m/Y') }}</p>
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

    <div class="meta-grid">
        <div class="meta-item">
            <span>Concepto</span>
            <strong>{{ $report->title }}</strong>
        </div>
        <div class="meta-item">
            <span>Estado</span>
            <strong>{{ $statusLabels[$report->status] ?? ucfirst($report->status) }}</strong>
        </div>
        <div class="meta-item">
            <span>Ubicacion / area</span>
            <strong>{{ $report->location ?: 'No especificada' }}</strong>
        </div>
        <div class="meta-item">
            <span>Responsable</span>
            <strong>{{ $report->responsible_name ?: $report->createdBy?->name ?? 'No especificado' }}</strong>
        </div>
    </div>

    <div class="section">
        <h2>Descripcion de la incidencia</h2>
        <div class="text">{{ $report->description }}</div>
    </div>

    <div class="section">
        <h2>Acciones tomadas / seguimiento</h2>
        <div class="text">{{ $report->actions_taken ?: 'Sin acciones capturadas.' }}</div>
    </div>
</body>
</html>

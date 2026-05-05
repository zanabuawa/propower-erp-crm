<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Proyectos – {{ $company->name }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 11px; color: #1a1a2e; background: #fff; padding: 24px 32px; }

        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #ef4444; padding-bottom: 12px; margin-bottom: 16px; }
        .header-logo img { max-height: 70px; max-width: 200px; object-fit: contain; display: block; }
        .header-logo .logo-placeholder { width: 70px; height: 70px; background: #fee2e2; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 26px; font-weight: 700; color: #ef4444; }
        .header-left h1 { font-size: 18px; font-weight: 700; color: #ef4444; }
        .header-left p { font-size: 12px; color: #6b7280; margin-top: 2px; }
        .header-right { text-align: right; font-size: 10px; color: #6b7280; }
        .header-right strong { display: block; font-size: 11px; color: #374151; }

        .filters { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px; font-size: 10px; }
        .filter-chip { background: #f3f4f6; border: 1px solid #e5e7eb; border-radius: 999px; padding: 2px 10px; color: #374151; }
        .filter-chip span { font-weight: 600; color: #ef4444; }

        .kpis { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 16px; }
        .kpi { border: 1px solid #e5e7eb; border-radius: 8px; padding: 10px 12px; }
        .kpi-label { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #9ca3af; margin-bottom: 4px; }
        .kpi-value { font-size: 16px; font-weight: 800; color: #111827; }
        .kpi-value.green  { color: #059669; }
        .kpi-value.amber  { color: #d97706; }
        .kpi-value.accent { color: #ef4444; }

        table { width: 100%; border-collapse: collapse; font-size: 10.5px; }
        thead tr { background: #f9fafb; border-bottom: 1px solid #e5e7eb; }
        thead th { padding: 7px 10px; text-align: left; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.07em; color: #6b7280; }
        thead th.right { text-align: right; }
        thead th.center { text-align: center; }
        tbody tr { border-bottom: 1px solid #f3f4f6; }
        tbody tr:nth-child(even) { background: #fafafa; }
        tbody td { padding: 6px 10px; vertical-align: middle; color: #374151; }
        tbody td.mono { font-family: 'Courier New', monospace; font-size: 10px; color: #6b7280; }
        tbody td.bold { font-weight: 700; color: #111827; }
        tbody td.right { text-align: right; }
        tbody td.center { text-align: center; }
        tbody td.amount { font-weight: 700; text-align: right; }

        .badge { display: inline-block; padding: 2px 7px; border-radius: 999px; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; }
        .badge-planning    { background: #f3f4f6; color: #374151; }
        .badge-active      { background: #d1fae5; color: #065f46; }
        .badge-on_hold     { background: #fef3c7; color: #92400e; }
        .badge-completed   { background: #dbeafe; color: #1d4ed8; }
        .badge-cancelled   { background: #fee2e2; color: #991b1b; }

        .progress-bar { background: #e5e7eb; border-radius: 999px; height: 6px; width: 60px; display: inline-block; vertical-align: middle; }
        .progress-fill { background: #ef4444; border-radius: 999px; height: 6px; }

        tfoot tr { background: #fff1f2; border-top: 2px solid #ef4444; }
        tfoot td { padding: 8px 10px; font-weight: 700; color: #1a1a2e; }
        tfoot td.right { text-align: right; }

        .footer { margin-top: 20px; padding-top: 10px; border-top: 1px solid #e5e7eb; display: flex; justify-content: space-between; font-size: 9px; color: #9ca3af; }

        @media print {
            body { padding: 12px 16px; }
            @page { margin: 10mm 12mm; size: landscape; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="text-align:right; margin-bottom:12px;">
        <button onclick="window.print()" style="background:#ef4444;color:#fff;border:none;padding:8px 20px;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;">Imprimir / Guardar PDF</button>
        <button onclick="window.close()" style="background:#f3f4f6;color:#374151;border:1px solid #e5e7eb;padding:8px 20px;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;margin-left:8px;">Cerrar</button>
    </div>

    <div class="header">
        <div style="display:flex; align-items:center; gap:14px;">
            <div class="header-logo">
                @php $logoPath = $company->print_logo ?? $company->logo ?? null; @endphp
                @if($logoPath)
                    <img src="{{ asset('storage/' . $logoPath) }}" alt="{{ $company->name }}">
                @else
                    <div class="logo-placeholder">{{ mb_strtoupper(mb_substr($company->name ?? 'E', 0, 1)) }}</div>
                @endif
            </div>
            <div class="header-left">
                <h1>Reporte de Proyectos</h1>
                <p>{{ $company->name }}</p>
            </div>
        </div>
        <div class="header-right">
            <strong>Fecha de impresión</strong>
            {{ now()->format('d/m/Y H:i') }}<br>
            Generado por {{ auth()->user()->name }}
        </div>
    </div>

    <div class="filters">
        <div class="filter-chip">Estado: <span>{{ $statusLabel }}</span></div>
        <div class="filter-chip">Tipo: <span>{{ $typeLabel }}</span></div>
        <div class="filter-chip">Proyectos: <span>{{ $projects->count() }}</span></div>
    </div>

    <div class="kpis">
        <div class="kpi">
            <div class="kpi-label">En progreso</div>
            <div class="kpi-value accent">{{ $activeCount }}</div>
        </div>
        <div class="kpi">
            <div class="kpi-label">Completados</div>
            <div class="kpi-value green">{{ $completedCount }}</div>
        </div>
        <div class="kpi">
            <div class="kpi-label">Presupuesto total</div>
            <div class="kpi-value">${{ number_format($totalBudget, 2) }}</div>
        </div>
        <div class="kpi">
            <div class="kpi-label">Costo real</div>
            <div class="kpi-value amber">${{ number_format($totalCost, 2) }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Código</th>
                <th>Proyecto</th>
                <th>Tipo</th>
                <th>Cliente</th>
                <th>Responsable</th>
                <th class="center">Avance</th>
                <th>Inicio</th>
                <th>Fin</th>
                <th class="right">Presupuesto</th>
                <th class="center">Estado</th>
            </tr>
        </thead>
        <tbody>
            @php $row = 0; @endphp
            @forelse($projects as $project)
                @php $row++; @endphp
                <tr>
                    <td class="mono">{{ $row }}</td>
                    <td class="mono bold">{{ $project->code ?? '—' }}</td>
                    <td class="bold">{{ $project->name }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $project->type ?? '—')) }}</td>
                    <td>{{ $project->customer?->name ?? '—' }}</td>
                    <td>{{ $project->manager?->name ?? $project->responsible?->name ?? '—' }}</td>
                    <td class="center">
                        @php $pct = $project->progress ?? 0; @endphp
                        <span class="progress-bar"><span class="progress-fill" style="width:{{ $pct }}%"></span></span>
                        <span style="margin-left:4px;font-size:9px;color:#6b7280;">{{ $pct }}%</span>
                    </td>
                    <td>{{ $project->start_date?->format('d/m/Y') ?? '—' }}</td>
                    <td>{{ $project->end_date?->format('d/m/Y') ?? '—' }}</td>
                    <td class="amount">${{ number_format($project->budget ?? 0, 2) }}</td>
                    <td class="center">
                        <span class="badge badge-{{ $project->status }}">
                            {{ \App\Models\Project::STATUSES[$project->status] ?? $project->status }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" style="text-align:center;padding:20px;color:#9ca3af;">No hay proyectos con los filtros seleccionados.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="9" style="font-size:11px;">TOTALES</td>
                <td class="right" style="font-size:13px;color:#ef4444;">${{ number_format($totalBudget, 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <span>ProPower ERP • Módulo Proyectos</span>
        <span>{{ $company->name }} • {{ now()->format('d/m/Y H:i') }}</span>
    </div>

</body>
</html>

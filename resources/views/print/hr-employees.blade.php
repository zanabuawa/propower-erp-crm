<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Directorio de Empleados – {{ $company->name }}</title>
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
        .kpi-value { font-size: 18px; font-weight: 800; color: #111827; }
        .kpi-value.red   { color: #dc2626; }
        .kpi-value.amber { color: #d97706; }
        .kpi-value.green { color: #059669; }
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

        .badge { display: inline-block; padding: 2px 7px; border-radius: 999px; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; }
        .badge-active    { background: #d1fae5; color: #065f46; }
        .badge-on_leave  { background: #fef3c7; color: #92400e; }
        .badge-inactive  { background: #f3f4f6; color: #374151; }
        .badge-suspended { background: #fee2e2; color: #991b1b; }
        .badge-terminated{ background: #fce7f3; color: #9d174d; }

        tfoot tr { background: #fff1f2; border-top: 2px solid #ef4444; }
        tfoot td { padding: 8px 10px; font-weight: 700; color: #1a1a2e; }

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
                <h1>Directorio de Empleados</h1>
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
        @if($department)
            <div class="filter-chip">Departamento: <span>{{ $department->name }}</span></div>
        @endif
        <div class="filter-chip">Total empleados: <span>{{ $employees->count() }}</span></div>
    </div>

    <div class="kpis">
        <div class="kpi">
            <div class="kpi-label">Total</div>
            <div class="kpi-value accent">{{ $employees->count() }}</div>
        </div>
        <div class="kpi">
            <div class="kpi-label">Activos</div>
            <div class="kpi-value green">{{ $activeCount }}</div>
        </div>
        <div class="kpi">
            <div class="kpi-label">Con permiso</div>
            <div class="kpi-value amber">{{ $onLeaveCount }}</div>
        </div>
        <div class="kpi">
            <div class="kpi-label">Inactivos</div>
            <div class="kpi-value red">{{ $inactiveCount }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>No. Empleado</th>
                <th>Nombre completo</th>
                <th>Departamento</th>
                <th>Puesto</th>
                <th>Contrato</th>
                <th>Fecha ingreso</th>
                @can('view employee sensitive data')
                <th>RFC</th>
                @endcan
                <th class="center">Estado</th>
            </tr>
        </thead>
        <tbody>
            @php $row = 0; @endphp
            @forelse($employees as $emp)
                @php
                    $row++;
                    $badgeClass = 'badge-' . $emp->status;
                    $statusLabel = \App\Models\HrEmployee::STATUSES[$emp->status] ?? $emp->status;
                @endphp
                <tr>
                    <td class="mono">{{ $row }}</td>
                    <td class="mono">{{ $emp->employee_number ?? '—' }}</td>
                    <td class="bold">{{ $emp->last_name }} {{ $emp->second_last_name }} {{ $emp->first_name }}</td>
                    <td>{{ $emp->department?->name ?? '—' }}</td>
                    <td>{{ $emp->position?->name ?? '—' }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $emp->contract_type ?? '—')) }}</td>
                    <td>{{ $emp->hire_date?->format('d/m/Y') ?? '—' }}</td>
                    @can('view employee sensitive data')
                    <td class="mono">{{ $emp->rfc ?? '—' }}</td>
                    @endcan
                    <td class="center">
                        <span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align:center;padding:20px;color:#9ca3af;">No hay empleados con los filtros seleccionados.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" style="font-size:11px;">TOTAL</td>
                <td colspan="7" style="font-size:13px;color:#ef4444;">{{ $employees->count() }} empleados</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <span>ProPower ERP • Módulo RRHH</span>
        <span>{{ $company->name }} • {{ now()->format('d/m/Y H:i') }}</span>
    </div>

</body>
</html>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transacciones – {{ $company->name }}</title>
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
        .kpi-value.red    { color: #dc2626; }
        .kpi-value.blue   { color: #3b82f6; }
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
        .badge-income   { background: #d1fae5; color: #065f46; }
        .badge-expense  { background: #fee2e2; color: #991b1b; }
        .badge-transfer { background: #dbeafe; color: #1d4ed8; }
        .badge-pending  { background: #fef3c7; color: #92400e; }
        .badge-posted   { background: #d1fae5; color: #065f46; }
        .badge-voided   { background: #f3f4f6; color: #374151; }

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
                <h1>Transacciones Financieras</h1>
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
        <div class="filter-chip">Tipo: <span>{{ $typeLabel }}</span></div>
        @if($filterAccount)
            <div class="filter-chip">Cuenta: <span>{{ $filterAccount->name }}</span></div>
        @endif
        <div class="filter-chip">Registros: <span>{{ $transactions->count() }}</span></div>
    </div>

    <div class="kpis">
        <div class="kpi">
            <div class="kpi-label">Ingresos</div>
            <div class="kpi-value green">${{ number_format($totalIngresos, 2) }}</div>
        </div>
        <div class="kpi">
            <div class="kpi-label">Egresos</div>
            <div class="kpi-value red">${{ number_format($totalEgresos, 2) }}</div>
        </div>
        <div class="kpi">
            <div class="kpi-label">Transferencias</div>
            <div class="kpi-value blue">${{ number_format($totalTransferencias, 2) }}</div>
        </div>
        <div class="kpi">
            <div class="kpi-label">Saldo neto</div>
            <div class="kpi-value {{ $saldoNeto >= 0 ? 'green' : 'red' }}">${{ number_format($saldoNeto, 2) }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Folio</th>
                <th>Fecha</th>
                <th>Concepto</th>
                <th>Cuenta</th>
                <th class="center">Tipo</th>
                <th class="right">Monto</th>
                <th class="center">Estado</th>
            </tr>
        </thead>
        <tbody>
            @php $row = 0; @endphp
            @forelse($transactions as $tx)
                @php $row++; @endphp
                <tr>
                    <td class="mono">{{ $row }}</td>
                    <td class="mono bold">{{ $tx->folio ?? '—' }}</td>
                    <td>{{ $tx->date?->format('d/m/Y') ?? '—' }}</td>
                    <td class="bold">{{ $tx->concept ?? $tx->description ?? '—' }}</td>
                    <td>{{ $tx->account?->name ?? '—' }}</td>
                    <td class="center">
                        <span class="badge badge-{{ $tx->type }}">
                            {{ \App\Models\FinanceTransaction::TYPES[$tx->type] ?? $tx->type }}
                        </span>
                    </td>
                    <td class="amount" style="color:{{ $tx->type === 'income' ? '#059669' : ($tx->type === 'expense' ? '#dc2626' : '#3b82f6') }}">
                        ${{ number_format($tx->amount, 2) }}
                    </td>
                    <td class="center">
                        <span class="badge badge-{{ $tx->status }}">
                            {{ \App\Models\FinanceTransaction::STATUSES[$tx->status] ?? $tx->status }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align:center;padding:20px;color:#9ca3af;">No hay transacciones con los filtros seleccionados.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" style="font-size:11px;">TOTALES</td>
                <td class="right" style="font-size:13px;color:#ef4444;">${{ number_format($totalIngresos - $totalEgresos, 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <span>ProPower ERP • Módulo Finanzas</span>
        <span>{{ $company->name }} • {{ now()->format('d/m/Y H:i') }}</span>
    </div>

</body>
</html>

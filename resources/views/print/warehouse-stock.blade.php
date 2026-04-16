<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Existencias – {{ $warehouse->name }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 11px;
            color: #1a1a2e;
            background: #fff;
            padding: 24px 32px;
        }

        /* ── ENCABEZADO ─────────────────────────────────────────── */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .header-left h1 {
            font-size: 18px;
            font-weight: 700;
            color: #4f46e5;
        }
        .header-left p {
            font-size: 12px;
            color: #6b7280;
            margin-top: 2px;
        }
        .header-right {
            text-align: right;
            font-size: 10px;
            color: #6b7280;
        }
        .header-right strong {
            display: block;
            font-size: 11px;
            color: #374151;
        }

        /* ── FILTROS ACTIVOS ────────────────────────────────────── */
        .filters {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 14px;
            font-size: 10px;
        }
        .filter-chip {
            background: #f3f4f6;
            border: 1px solid #e5e7eb;
            border-radius: 999px;
            padding: 2px 10px;
            color: #374151;
        }
        .filter-chip span { font-weight: 600; color: #4f46e5; }

        /* ── KPIs ───────────────────────────────────────────────── */
        .kpis {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 16px;
        }
        .kpi {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 10px 12px;
        }
        .kpi-label {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #9ca3af;
            margin-bottom: 4px;
        }
        .kpi-value {
            font-size: 18px;
            font-weight: 800;
            color: #111827;
        }
        .kpi-value.indigo { color: #4f46e5; }
        .kpi-value.red    { color: #dc2626; }
        .kpi-value.amber  { color: #d97706; }

        /* ── TABLA ──────────────────────────────────────────────── */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10.5px;
        }
        thead tr {
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
        }
        thead th {
            padding: 7px 10px;
            text-align: left;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: #6b7280;
        }
        thead th.right { text-align: right; }
        thead th.center { text-align: center; }

        tbody tr {
            border-bottom: 1px solid #f3f4f6;
        }
        tbody tr:nth-child(even) { background: #fafafa; }
        tbody td {
            padding: 6px 10px;
            vertical-align: middle;
            color: #374151;
        }
        tbody td.right  { text-align: right; }
        tbody td.center { text-align: center; }
        tbody td.mono   { font-family: 'Courier New', monospace; font-size: 10px; color: #6b7280; }
        tbody td.bold   { font-weight: 700; color: #111827; }
        tbody td.num    { font-weight: 800; font-size: 12px; }
        tbody td.num.ok     { color: #059669; }
        tbody td.num.low    { color: #d97706; }
        tbody td.num.out    { color: #dc2626; }

        .badge {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 999px;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .badge.ok    { background: #d1fae5; color: #065f46; }
        .badge.low   { background: #fef3c7; color: #92400e; }
        .badge.out   { background: #fee2e2; color: #991b1b; }

        /* ── SUBTOTAL POR CATEGORÍA ─────────────────────────────── */
        .category-row td {
            background: #eff6ff;
            font-weight: 700;
            color: #1d4ed8;
            font-size: 10px;
            padding: 5px 10px;
            border-top: 1px solid #bfdbfe;
            border-bottom: 1px solid #bfdbfe;
        }

        /* ── TOTALES ────────────────────────────────────────────── */
        tfoot tr {
            background: #f0f0ff;
            border-top: 2px solid #4f46e5;
        }
        tfoot td {
            padding: 8px 10px;
            font-weight: 700;
            color: #1a1a2e;
        }
        tfoot td.right { text-align: right; }

        /* ── PIE DE PÁGINA ──────────────────────────────────────── */
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            font-size: 9px;
            color: #9ca3af;
        }

        /* ── PRINT ──────────────────────────────────────────────── */
        @media print {
            body { padding: 12px 16px; }
            @page { margin: 10mm 12mm; size: landscape; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

    {{-- ── Botón imprimir (solo pantalla) ─────────────────────── --}}
    <div class="no-print" style="text-align:right; margin-bottom:12px;">
        <button onclick="window.print()"
            style="background:#4f46e5;color:#fff;border:none;padding:8px 20px;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;">
            Imprimir / Guardar PDF
        </button>
        <button onclick="window.close()"
            style="background:#f3f4f6;color:#374151;border:1px solid #e5e7eb;padding:8px 20px;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;margin-left:8px;">
            Cerrar
        </button>
    </div>

    {{-- ── Encabezado ─────────────────────────────────────────── --}}
    <div class="header">
        <div class="header-left">
            <h1>Existencias por almacén</h1>
            <p>{{ $warehouse->name }}{{ $warehouse->branch ? ' — ' . $warehouse->branch->name : '' }}{{ $warehouse->location ? ' · ' . $warehouse->location : '' }}</p>
        </div>
        <div class="header-right">
            <strong>Fecha de impresión</strong>
            {{ now()->format('d/m/Y H:i') }}
            <br>
            Generado por {{ auth()->user()->name }}
        </div>
    </div>

    {{-- ── Filtros activos ─────────────────────────────────────── --}}
    <div class="filters">
        <div class="filter-chip">Estado: <span>{{ $stockFilterLabel }}</span></div>
        @if($category)
            <div class="filter-chip">Categoría: <span>{{ $category->name }}</span></div>
        @endif
        <div class="filter-chip">Total de referencias: <span>{{ $stocks->count() }}</span></div>
    </div>

    {{-- ── KPIs ─────────────────────────────────────────────────── --}}
    <div class="kpis">
        <div class="kpi">
            <div class="kpi-label">Referencias</div>
            <div class="kpi-value">{{ number_format($stocks->count()) }}</div>
        </div>
        <div class="kpi">
            <div class="kpi-label">Sin existencias</div>
            <div class="kpi-value red">{{ $outCount }}</div>
        </div>
        <div class="kpi">
            <div class="kpi-label">Stock bajo</div>
            <div class="kpi-value amber">{{ $lowCount }}</div>
        </div>
        <div class="kpi">
            <div class="kpi-label">Valor total (costo)</div>
            <div class="kpi-value indigo">${{ number_format($totalValue, 2) }}</div>
        </div>
    </div>

    {{-- ── Tabla ───────────────────────────────────────────────── --}}
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Producto</th>
                <th>SKU</th>
                <th>Categoría</th>
                <th class="right">Existencias</th>
                <th class="right">Mínimo</th>
                <th class="right">Costo unitario</th>
                <th class="right">Precio venta</th>
                <th class="right">Valor total</th>
                <th class="center">Estado</th>
            </tr>
        </thead>
        <tbody>
            @php $row = 0; @endphp
            @forelse($stocks as $stock)
                @php
                    $p     = $stock->product;
                    $qty   = $stock->quantity;
                    $isOut = $qty <= 0;
                    $isLow = $qty > 0 && $qty <= $p->min_stock;
                    $row++;
                @endphp
                <tr>
                    <td class="mono">{{ $row }}</td>
                    <td class="bold">{{ $p->name }}</td>
                    <td class="mono">{{ $p->sku ?? '—' }}</td>
                    <td>{{ $p->category?->name ?? 'Sin categoría' }}</td>
                    <td class="right num {{ $isOut ? 'out' : ($isLow ? 'low' : 'ok') }}">
                        {{ number_format($qty, 2) }}
                    </td>
                    <td class="right" style="color:#9ca3af;">{{ number_format($p->min_stock, 2) }}</td>
                    <td class="right">${{ number_format($p->purchase_price, 2) }}</td>
                    <td class="right">${{ number_format($p->sale_price, 2) }}</td>
                    <td class="right bold">${{ number_format($qty * (float)$p->purchase_price, 2) }}</td>
                    <td class="center">
                        @if($isOut)
                            <span class="badge out">Agotado</span>
                        @elseif($isLow)
                            <span class="badge low">Stock bajo</span>
                        @else
                            <span class="badge ok">Óptimo</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align:center;padding:20px;color:#9ca3af;">
                        No hay productos con los filtros seleccionados.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" style="font-size:11px;">TOTAL</td>
                <td class="right" style="font-size:13px;color:#4f46e5;">
                    {{ number_format($stocks->sum('quantity'), 2) }}
                </td>
                <td colspan="3"></td>
                <td class="right" style="font-size:13px;color:#4f46e5;">
                    ${{ number_format($totalValue, 2) }}
                </td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    {{-- ── Pie de página ───────────────────────────────────────── --}}
    <div class="footer">
        <span>ProPower ERP • Módulo de Inventario</span>
        <span>{{ $warehouse->name }} • {{ now()->format('d/m/Y H:i') }}</span>
    </div>

</body>
</html>

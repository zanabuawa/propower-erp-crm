<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recepción {{ $receipt->folio }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 11px; color: #1a1a2e; background: #fff; }
        .page { max-width: 800px; margin: 0 auto; padding: 32px; }

        /* ── Header ── */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #ef4444;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .header-logo img { max-height: 70px; max-width: 200px; object-fit: contain; display: block; }
        .header-logo .logo-placeholder { width: 70px; height: 70px; background: #fee2e2; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 26px; font-weight: 700; color: #ef4444; }
        .header-left h1 { font-size: 18px; font-weight: 700; color: #ef4444; }
        .header-left .folio { font-size: 14px; font-weight: 600; color: #374151; margin-top: 4px; }
        .header-left .date  { font-size: 11px; color: #6b7280; margin-top: 2px; }
        .header-right { text-align: right; font-size: 10px; color: #6b7280; }
        .header-right strong { display: block; font-size: 11px; color: #374151; }

        /* ── Badge ── */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            background: #fee2e2;
            color: #991b1b;
        }

        /* ── Meta grid ── */
        .meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px; }
        .meta-box { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px 14px; }
        .meta-box h3 { font-size: 9px; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 8px; }
        .meta-row { display: flex; justify-content: space-between; font-size: 10.5px; margin-bottom: 4px; }
        .meta-row span:first-child { color: #6b7280; }
        .meta-row span:last-child { font-weight: 500; color: #111827; text-align: right; }

        /* ── Table ── */
        .section-title { font-size: 9px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; font-size: 10.5px; }
        thead tr { background: #f9fafb; border-bottom: 1px solid #e5e7eb; }
        thead th { text-align: left; padding: 7px 10px; font-size: 9px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 0.07em; }
        thead th.right { text-align: right; }
        tbody tr { border-bottom: 1px solid #f3f4f6; }
        tbody tr:nth-child(even) { background: #fafafa; }
        tbody td { padding: 6px 10px; vertical-align: top; color: #374151; }
        tbody td.right { text-align: right; font-weight: 600; }
        tbody td.notes { color: #6b7280; font-style: italic; font-size: 10px; }

        /* ── Notes box ── */
        .notes-box { background: #fff7f7; border: 1px solid #fecaca; border-radius: 8px; padding: 10px 14px; margin-bottom: 16px; font-size: 10.5px; color: #7f1d1d; }

        /* ── Signature area ── */
        .signatures { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 40px; padding-top: 20px; border-top: 1px solid #e5e7eb; }
        .sig-box { text-align: center; }
        .sig-line { border-top: 1px solid #9ca3af; margin-bottom: 6px; }
        .sig-name { font-size: 11px; font-weight: 600; color: #374151; }
        .sig-title { font-size: 10px; color: #9ca3af; margin-top: 2px; }

        /* ── Footer ── */
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            font-size: 9px;
            color: #9ca3af;
        }

        @media print {
            body { padding: 0; }
            .page { padding: 20px; }
            .no-print { display: none !important; }
            @page { size: A4; margin: 12mm 14mm; }
        }
    </style>
</head>
<body>

    {{-- ── Botones (solo pantalla) ─────────────────────────────────── --}}
    <div class="no-print" style="text-align:right; padding:12px 20px; background:#fff; border-bottom:1px solid #e5e7eb;">
        <button onclick="window.print()"
            style="background:#ef4444;color:#fff;border:none;padding:8px 20px;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;">
            Imprimir / Guardar PDF
        </button>
        <button onclick="window.close()"
            style="background:#f3f4f6;color:#374151;border:1px solid #e5e7eb;padding:8px 20px;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;margin-left:8px;">
            Cerrar
        </button>
    </div>

    <div class="page">

        {{-- ── Header ── --}}
        <div class="header">
            <div style="display:flex; align-items:center; gap:14px;">
                <div class="header-logo">
                    @php $logoPath = $receipt->company?->print_logo ?? $receipt->company?->logo ?? null; @endphp
                    @if($logoPath)
                        <img src="{{ asset('storage/' . $logoPath) }}" alt="{{ $receipt->company->name }}">
                    @else
                        <div class="logo-placeholder">{{ mb_strtoupper(mb_substr($receipt->company?->name ?? 'E', 0, 1)) }}</div>
                    @endif
                </div>
                <div class="header-left">
                    <h1>Recepción de mercancías</h1>
                    <div class="folio">{{ $receipt->folio }}</div>
                    <div class="date">{{ $receipt->received_at->format('d/m/Y H:i') }}</div>
                    <div style="margin-top:5px;">
                        <span class="badge">{{ \App\Models\PurchaseReceipt::RECEPTION_TYPES[$receipt->reception_type] ?? $receipt->reception_type }}</span>
                    </div>
                </div>
            </div>
            <div class="header-right">
                <strong>Fecha de impresión</strong>
                {{ now()->format('d/m/Y H:i') }}<br>
                Generado por {{ auth()->user()->name }}
            </div>
        </div>

        {{-- ── Meta info ── --}}
        <div class="meta-grid">
            <div class="meta-box">
                <h3>Orden de compra</h3>
                @if($receipt->order)
                    <div class="meta-row">
                        <span>Folio</span>
                        <span>{{ $receipt->order->folio }}</span>
                    </div>
                    <div class="meta-row">
                        <span>Proveedor</span>
                        <span>{{ $receipt->order->supplier->name }}</span>
                    </div>
                    @if($receipt->order->branch)
                        <div class="meta-row">
                            <span>Sucursal</span>
                            <span>{{ $receipt->order->branch->name }}</span>
                        </div>
                    @endif
                @else
                    <p style="font-size:10.5px;color:#6b7280;">Entrada directa (sin orden)</p>
                @endif
            </div>
            <div class="meta-box">
                <h3>Datos de recepción</h3>
                <div class="meta-row">
                    <span>Recibido por</span>
                    <span>{{ $receipt->receivedBy->name }}</span>
                </div>
                <div class="meta-row">
                    <span>Fecha</span>
                    <span>{{ $receipt->received_at->format('d/m/Y H:i') }}</span>
                </div>
                @if($receipt->operating_expenses > 0)
                    <div class="meta-row">
                        <span>Gastos de operación</span>
                        <span>${{ number_format($receipt->operating_expenses, 2) }}</span>
                    </div>
                @endif
            </div>
        </div>

        @if($receipt->notes)
            <div class="notes-box">
                <strong>Notas:</strong> {{ $receipt->notes }}
            </div>
        @endif

        {{-- ── Items table ── --}}
        <p class="section-title">Productos recibidos</p>
        <table>
            <thead>
                <tr>
                    <th style="width:32px">#</th>
                    <th>Producto / Descripción</th>
                    <th>Almacén destino</th>
                    <th class="right">Cantidad recibida</th>
                    <th>Notas / Observaciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($receipt->items as $i => $item)
                    <tr>
                        <td style="color:#9ca3af;font-family:'Courier New',monospace;font-size:10px;">{{ $i + 1 }}</td>
                        <td>
                            <strong>{{ $item->product?->name ?? '—' }}</strong>
                            @if($item->product?->sku)
                                <br><span style="font-size:10px;color:#9ca3af;font-family:'Courier New',monospace;">{{ $item->product->sku }}</span>
                            @endif
                        </td>
                        <td>{{ $item->warehouse?->name ?? '—' }}</td>
                        <td class="right">{{ number_format($item->quantity_received, 2) }}</td>
                        <td class="notes">{{ $item->notes ?: '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- ── Comparison with order ── --}}
        @if($receipt->order && $receipt->order->items->isNotEmpty())
            <p class="section-title" style="margin-top:16px">Comparativo vs. orden de compra</p>
            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th class="right">Ordenado</th>
                        <th class="right">Recibido total</th>
                        <th class="right">Pendiente</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($receipt->order->items as $oi)
                        @php $pending = max(0, (float)$oi->quantity - (float)$oi->quantity_received); @endphp
                        <tr>
                            <td>{{ $oi->description }}</td>
                            <td class="right">{{ number_format($oi->quantity, 2) }}</td>
                            <td class="right" style="color:{{ $oi->quantity_received >= $oi->quantity ? '#059669' : '#d97706' }}; font-weight:600">
                                {{ number_format($oi->quantity_received, 2) }}
                            </td>
                            <td class="right" style="color:{{ $pending > 0 ? '#dc2626' : '#9ca3af' }}">
                                {{ $pending > 0 ? number_format($pending, 2) : '✓' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        {{-- ── Signatures ── --}}
        <div class="signatures">
            <div class="sig-box">
                <div style="height:40px"></div>
                <div class="sig-line"></div>
                <div class="sig-name">{{ $receipt->receivedBy->name }}</div>
                <div class="sig-title">Responsable de recepción</div>
            </div>
            <div class="sig-box">
                <div style="height:40px"></div>
                <div class="sig-line"></div>
                <div class="sig-name">_________________________</div>
                <div class="sig-title">Vo. Bo. / Almacén</div>
            </div>
        </div>

        {{-- ── Footer ── --}}
        <div class="footer">
            <span>ProPower ERP • Módulo de Compras</span>
            <span>{{ $receipt->company?->name ?? config('app.name') }}</span>
            <span>Documento interno — no es comprobante fiscal · {{ now()->format('d/m/Y H:i') }}</span>
        </div>

    </div>
</body>
</html>

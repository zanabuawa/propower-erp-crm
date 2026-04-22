<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recepción {{ $receipt->folio }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #1f2937; background: #fff; }
        .page { max-width: 800px; margin: 0 auto; padding: 32px; }

        /* Header */
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 28px; padding-bottom: 20px; border-bottom: 2px solid #e5e7eb; }
        .company-info h2 { font-size: 18px; font-weight: 700; color: #111827; }
        .company-info p { font-size: 11px; color: #6b7280; margin-top: 2px; }
        .doc-info { text-align: right; }
        .doc-title { font-size: 20px; font-weight: 700; color: #0f766e; letter-spacing: 0.5px; }
        .doc-folio { font-size: 14px; font-weight: 600; color: #374151; margin-top: 4px; }
        .doc-date { font-size: 11px; color: #6b7280; margin-top: 2px; }

        /* Meta grid */
        .meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px; }
        .meta-box { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px 14px; }
        .meta-box h3 { font-size: 10px; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 8px; }
        .meta-row { display: flex; justify-content: space-between; font-size: 11px; margin-bottom: 4px; }
        .meta-row span:first-child { color: #6b7280; }
        .meta-row span:last-child { font-weight: 500; color: #111827; text-align: right; }

        /* Badge */
        .badge { display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: 10px; font-weight: 600; background: #ccfbf1; color: #0f766e; }

        /* Table */
        .section-title { font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        thead tr { background: #f3f4f6; }
        th { text-align: left; padding: 8px 10px; font-size: 10px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #e5e7eb; }
        th.right { text-align: right; }
        td { padding: 9px 10px; font-size: 11px; border-bottom: 1px solid #f3f4f6; vertical-align: top; }
        td.right { text-align: right; font-weight: 600; }
        td.notes { color: #6b7280; font-style: italic; font-size: 10px; }
        tbody tr:last-child td { border-bottom: none; }

        /* Notes box */
        .notes-box { background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 10px 14px; margin-bottom: 20px; font-size: 11px; color: #92400e; }

        /* Signature area */
        .signatures { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 40px; padding-top: 20px; border-top: 1px solid #e5e7eb; }
        .sig-box { text-align: center; }
        .sig-line { border-top: 1px solid #9ca3af; margin-bottom: 6px; }
        .sig-name { font-size: 11px; font-weight: 600; color: #374151; }
        .sig-title { font-size: 10px; color: #9ca3af; margin-top: 2px; }

        /* Footer */
        .footer { margin-top: 28px; padding-top: 16px; border-top: 1px solid #e5e7eb; text-align: center; font-size: 10px; color: #9ca3af; }

        /* Print button */
        .print-btn { position: fixed; top: 20px; right: 20px; background: #0f766e; color: white; border: none; padding: 8px 16px; border-radius: 8px; font-size: 12px; cursor: pointer; font-weight: 600; }
        @media print { .print-btn { display: none; } body { font-size: 11px; } .page { padding: 20px; } }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">Imprimir / PDF</button>

    <div class="page">

        {{-- Header --}}
        <div class="header">
            <div class="company-info">
                @if($receipt->company?->logo && file_exists(public_path('storage/' . $receipt->company->logo)))
                    <img src="{{ asset('storage/' . ($receipt->company->print_logo ?? $receipt->company->logo)) }}" alt="Logo" style="height:70px; margin-bottom:6px; object-fit:contain;">
                @endif
                <h2>{{ $receipt->company?->name ?? config('app.name') }}</h2>
                @if($receipt->company?->rfc)
                    <p>RFC: {{ $receipt->company->rfc }}</p>
                @endif
                @if($receipt->company?->address)
                    <p>{{ $receipt->company->address }}</p>
                @endif
            </div>
            <div class="doc-info">
                <div class="doc-title">RECEPCIÓN DE MERCANCÍAS</div>
                <div class="doc-folio">{{ $receipt->folio }}</div>
                <div class="doc-date">{{ $receipt->received_at->format('d/m/Y H:i') }}</div>
                <div style="margin-top:6px;">
                    <span class="badge">{{ \App\Models\PurchaseReceipt::RECEPTION_TYPES[$receipt->reception_type] ?? $receipt->reception_type }}</span>
                </div>
            </div>
        </div>

        {{-- Meta info --}}
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
                    <p style="font-size:11px;color:#6b7280;">Entrada directa (sin orden)</p>
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

        {{-- Items table --}}
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
                        <td style="color:#9ca3af">{{ $i + 1 }}</td>
                        <td>
                            <strong>{{ $item->product?->name ?? '—' }}</strong>
                            @if($item->product?->sku)
                                <br><span style="font-size:10px;color:#9ca3af;font-family:monospace">{{ $item->product->sku }}</span>
                            @endif
                        </td>
                        <td>{{ $item->warehouse?->name ?? '—' }}</td>
                        <td class="right">{{ number_format($item->quantity_received, 2) }}</td>
                        <td class="notes">{{ $item->notes ?: '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Comparison with order (if linked) --}}
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
                            <td class="right" style="color:{{ $oi->quantity_received >= $oi->quantity ? '#0f766e' : '#d97706' }}; font-weight:600">
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

        {{-- Signatures --}}
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

        <div class="footer">
            Documento interno — no es comprobante fiscal · Generado el {{ now()->format('d/m/Y H:i') }}
        </div>

    </div>
</body>
</html>

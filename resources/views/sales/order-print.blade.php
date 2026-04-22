<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido {{ $order->folio }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; font-size: 11px; color: #1a1a1a; background: #fff; }

        .page { width: 210mm; min-height: 297mm; margin: 0 auto; padding: 14mm 16mm; }

        /* Header */
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #7c3aed; padding-bottom: 10px; margin-bottom: 14px; }
        .header-logo img { max-height: 70px; max-width: 200px; object-fit: contain; }
        .company-name { font-size: 16px; font-weight: 700; color: #4c1d95; }
        .header-right { text-align: right; }
        .doc-title { font-size: 18px; font-weight: 700; color: #4c1d95; text-transform: uppercase; letter-spacing: 1px; }
        .folio { font-size: 22px; font-weight: 800; color: #7c3aed; }
        .status-badge { display: inline-block; margin-top: 4px; padding: 2px 10px; border-radius: 12px; font-size: 10px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase; }
        .status-confirmed        { background: #dbeafe; color: #1e40af; border: 1px solid #93c5fd; }
        .status-delivered        { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
        .status-partial          { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
        .status-invoiced         { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
        .status-cancelled        { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .status-draft            { background: #f3f4f6; color: #374151; border: 1px solid #d1d5db; }

        /* Two-column header info */
        .cols-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px; }
        .info-box { border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px 12px; }
        .info-box-title { font-size: 9px; font-weight: 700; color: #7c3aed; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; border-bottom: 1px solid #ede9fe; padding-bottom: 4px; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 3px; }
        .info-label { font-size: 9px; color: #64748b; text-transform: uppercase; }
        .info-value { font-size: 10.5px; color: #1e293b; font-weight: 500; text-align: right; max-width: 60%; }
        .info-value.large { font-size: 12px; font-weight: 700; }
        .info-value.red { color: #dc2626; font-weight: 700; }

        /* Section title */
        .section-title { font-size: 10px; font-weight: 700; color: #7c3aed; text-transform: uppercase; letter-spacing: 0.8px; border-bottom: 1px solid #ddd6fe; padding-bottom: 4px; margin-bottom: 8px; }

        /* Table */
        table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        thead tr { background: #7c3aed; color: #fff; }
        thead th { padding: 6px 8px; text-align: left; font-size: 9.5px; font-weight: 700; }
        thead th.right { text-align: right; }
        tbody tr:nth-child(even) { background: #faf5ff; }
        tbody td { padding: 5px 8px; font-size: 10.5px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
        tbody td.right { text-align: right; }

        /* Totals */
        .totals-block { display: flex; justify-content: flex-end; margin-bottom: 18px; }
        .totals-table { width: 250px; }
        .totals-table tr td:first-child { color: #475569; font-size: 10px; padding: 3px 0; }
        .totals-table tr td:last-child { text-align: right; font-size: 11px; font-weight: 600; padding: 3px 0; }
        .totals-table .total-row td { font-size: 14px; font-weight: 700; color: #7c3aed; border-top: 2px solid #7c3aed; padding-top: 6px; }

        /* Text box */
        .text-box { border: 1px solid #e2e8f0; border-radius: 5px; padding: 8px 10px; font-size: 10.5px; color: #334155; margin-bottom: 12px; min-height: 30px; line-height: 1.5; }
        .text-box-label { font-size: 9px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 4px; }

        /* Quotation ref */
        .ref-strip { background: #faf5ff; border: 1px solid #ddd6fe; border-radius: 6px; padding: 6px 14px; margin-bottom: 14px; font-size: 10px; color: #4c1d95; }

        /* Signature lines */
        .sig-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 28px; }
        .sig-box { border-top: 1px solid #475569; padding-top: 6px; text-align: center; }
        .sig-box-name { font-size: 10px; color: #334155; }
        .sig-box-label { font-size: 9px; color: #64748b; }

        /* Footer */
        .footer { border-top: 1px solid #e2e8f0; padding-top: 8px; margin-top: 20px; display: flex; justify-content: space-between; font-size: 8.5px; color: #94a3b8; }

        @media print {
            body { padding: 0; }
            .page { padding: 10mm 14mm; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="no-print" style="background:#7c3aed;color:#fff;padding:8px 20px;display:flex;justify-content:space-between;align-items:center;font-family:Arial,sans-serif;font-size:12px;">
    <span>Pedido de venta — {{ $order->folio }}</span>
    <div style="display:flex;gap:10px;">
        <button onclick="window.print()" style="background:#fff;color:#7c3aed;border:none;padding:6px 16px;border-radius:6px;font-weight:700;cursor:pointer;font-size:12px;">🖨 Imprimir / PDF</button>
        <a href="{{ url()->previous() }}" style="color:#ddd6fe;text-decoration:none;padding:6px 12px;">← Regresar</a>
    </div>
</div>

<div class="page">

    {{-- HEADER --}}
    @php $company = $order->company; @endphp
    <div class="header">
        <div class="header-logo">
            @if($company->print_logo ?? $company->logo ?? null)
                <img src="{{ Storage::url($company->print_logo ?? $company->logo) }}" alt="{{ $company->name }}">
            @else
                <div class="company-name">{{ $company->name }}</div>
            @endif
        </div>
        <div class="header-right">
            <div class="doc-title">Pedido de venta</div>
            <div class="folio">{{ $order->folio }}</div>
            @php
                $statusClass = match($order->status) {
                    'confirmed'         => 'status-confirmed',
                    'delivered'         => 'status-delivered',
                    'partial_delivered' => 'status-partial',
                    'invoiced'          => 'status-invoiced',
                    'cancelled'         => 'status-cancelled',
                    default             => 'status-draft',
                };
                $statusLabel = \App\Models\SaleOrder::STATUS[$order->status] ?? $order->status;
            @endphp
            <span class="status-badge {{ $statusClass }}">{{ $statusLabel }}</span>
        </div>
    </div>

    {{-- DOS COLUMNAS: Cliente / Detalles --}}
    <div class="cols-2">
        {{-- Cliente --}}
        <div class="info-box">
            <div class="info-box-title">Cliente</div>
            <div class="info-row">
                <span class="info-label">Nombre</span>
                <span class="info-value large">{{ $order->customer?->name ?? '—' }}</span>
            </div>
            @if($order->customer?->rfc)
                <div class="info-row">
                    <span class="info-label">RFC</span>
                    <span class="info-value">{{ $order->customer->rfc }}</span>
                </div>
            @endif
            @php $phone = $order->customer?->phones?->firstWhere('is_primary', true)?->number ?? $order->customer?->phones?->first()?->number; @endphp
            @if($phone)
                <div class="info-row">
                    <span class="info-label">Teléfono</span>
                    <span class="info-value">{{ $phone }}</span>
                </div>
            @endif
            @php $email = $order->customer?->emails?->firstWhere('is_primary', true)?->email ?? $order->customer?->emails?->first()?->email; @endphp
            @if($email)
                <div class="info-row">
                    <span class="info-label">Correo</span>
                    <span class="info-value">{{ $email }}</span>
                </div>
            @endif
            @if($order->customer?->address)
                <div class="info-row" style="margin-top:4px;">
                    <span class="info-label">Dirección</span>
                    <span class="info-value">{{ implode(', ', array_filter([$order->customer->address, $order->customer->city, $order->customer->state])) }}</span>
                </div>
            @endif
        </div>

        {{-- Detalles del pedido --}}
        <div class="info-box">
            <div class="info-box-title">Detalles del pedido</div>
            <div class="info-row">
                <span class="info-label">Fecha</span>
                <span class="info-value">{{ $order->created_at?->format('d/m/Y') }}</span>
            </div>
            @if($order->required_at)
                <div class="info-row">
                    <span class="info-label">Fecha requerida</span>
                    <span class="info-value red">{{ $order->required_at->format('d/m/Y') }}</span>
                </div>
            @endif
            <div class="info-row">
                <span class="info-label">Forma de pago</span>
                <span class="info-value">{{ \App\Models\SaleOrder::PAYMENT_METHODS[$order->payment_method] ?? $order->payment_method }}</span>
            </div>
            @if((int)$order->payment_terms > 0)
                <div class="info-row">
                    <span class="info-label">Días de crédito</span>
                    <span class="info-value">{{ $order->payment_terms }} días</span>
                </div>
            @endif
            <div class="info-row">
                <span class="info-label">Moneda</span>
                <span class="info-value">{{ strtoupper($order->currency ?? 'MXN') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Elaboró</span>
                <span class="info-value">{{ $order->createdBy?->name ?? '—' }}</span>
            </div>
            @if($order->branch)
                <div class="info-row">
                    <span class="info-label">Sucursal</span>
                    <span class="info-value">{{ $order->branch->name }}</span>
                </div>
            @endif
        </div>
    </div>

    {{-- Referencia cotización --}}
    @if($order->quotation)
        <div class="ref-strip">
            Originado de la cotización <strong>{{ $order->quotation->folio }}</strong>
            del {{ $order->quotation->created_at?->format('d/m/Y') }}
        </div>
    @endif

    {{-- PRODUCTOS --}}
    <div class="section-title">Partidas del pedido</div>
    <table>
        <thead>
            <tr>
                <th style="width:30px">#</th>
                <th>Descripción</th>
                <th style="width:55px" class="right">Cant.</th>
                <th style="width:55px">Unidad</th>
                <th style="width:85px" class="right">P. unitario</th>
                <th style="width:55px" class="right">Desc%</th>
                <th style="width:55px" class="right">IVA%</th>
                <th style="width:90px" class="right">Importe</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $i => $item)
                @php
                    $importe = ($item->quantity * $item->unit_price) * (1 - ($item->discount_pct ?? 0) / 100);
                @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>
                        <strong>{{ $item->description }}</strong>
                        @if($item->product?->sku)
                            <br><span style="font-size:9px;color:#94a3b8;font-family:monospace;">{{ $item->product->sku }}</span>
                        @endif
                    </td>
                    <td class="right">{{ number_format($item->quantity, 2) }}</td>
                    <td>{{ $item->unit ?? '—' }}</td>
                    <td class="right">${{ number_format($item->unit_price, 2) }}</td>
                    <td class="right">{{ $item->discount_pct > 0 ? number_format($item->discount_pct, 1).'%' : '—' }}</td>
                    <td class="right">{{ $item->tax_rate }}%</td>
                    <td class="right">${{ number_format($importe, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- TOTALES --}}
    <div class="totals-block">
        <table class="totals-table">
            <tr>
                <td>Subtotal</td>
                <td>${{ number_format($order->subtotal, 2) }}</td>
            </tr>
            @if($order->discount_amount > 0)
                <tr>
                    <td>Descuento</td>
                    <td style="color:#dc2626;">- ${{ number_format($order->discount_amount, 2) }}</td>
                </tr>
            @endif
            <tr>
                <td>IVA</td>
                <td>${{ number_format($order->tax, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td>TOTAL</td>
                <td>${{ number_format($order->total, 2) }} {{ strtoupper($order->currency ?? 'MXN') }}</td>
            </tr>
        </table>
    </div>

    {{-- NOTAS --}}
    @if($order->notes)
        <div class="text-box-label">Notas</div>
        <div class="text-box">{{ $order->notes }}</div>
    @endif

    {{-- FIRMAS --}}
    <div class="sig-grid">
        <div class="sig-box" style="height:50px;">
            <div class="sig-box-name">{{ $order->createdBy?->name ?? '_________________________' }}</div>
            <div class="sig-box-label">Elaboró · {{ $company->name }}</div>
        </div>
        <div class="sig-box" style="height:50px;">
            <div class="sig-box-name">{{ $order->customer?->name ?? '_________________________' }}</div>
            <div class="sig-box-label">Confirma · Cliente</div>
        </div>
    </div>

    {{-- FOOTER --}}
    <div class="footer">
        <span>{{ $company->name }}@if($company->address) · {{ $company->address }}@endif</span>
        <span>{{ $order->folio }} · Generado: {{ now()->format('d/m/Y H:i') }}</span>
    </div>

</div>
</body>
</html>

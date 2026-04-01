<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cotización {{ $quotation->folio }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; font-size: 11px; color: #1a1a1a; background: #fff; }

        .page { width: 210mm; min-height: 297mm; margin: 0 auto; padding: 14mm 16mm; }

        /* Header */
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #059669; padding-bottom: 10px; margin-bottom: 14px; }
        .header-logo img { max-height: 52px; max-width: 160px; object-fit: contain; }
        .company-name { font-size: 16px; font-weight: 700; color: #065f46; }
        .header-right { text-align: right; }
        .doc-title { font-size: 18px; font-weight: 700; color: #065f46; text-transform: uppercase; letter-spacing: 1px; }
        .folio { font-size: 22px; font-weight: 800; color: #059669; }
        .status-badge { display: inline-block; margin-top: 4px; padding: 2px 10px; border-radius: 12px; font-size: 10px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase; }
        .status-accepted { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
        .status-rejected { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .status-sent     { background: #dbeafe; color: #1e40af; border: 1px solid #93c5fd; }
        .status-draft    { background: #f3f4f6; color: #374151; border: 1px solid #d1d5db; }
        .status-expired  { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }

        /* Two-column header info */
        .cols-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px; }
        .info-box { border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px 12px; }
        .info-box-title { font-size: 9px; font-weight: 700; color: #059669; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; border-bottom: 1px solid #d1fae5; padding-bottom: 4px; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 3px; }
        .info-label { font-size: 9px; color: #64748b; text-transform: uppercase; }
        .info-value { font-size: 10.5px; color: #1e293b; font-weight: 500; text-align: right; max-width: 60%; }
        .info-value.large { font-size: 12px; font-weight: 700; }
        .info-value.red { color: #dc2626; }

        /* Section title */
        .section-title { font-size: 10px; font-weight: 700; color: #059669; text-transform: uppercase; letter-spacing: 0.8px; border-bottom: 1px solid #a7f3d0; padding-bottom: 4px; margin-bottom: 8px; }

        /* Table */
        table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        thead tr { background: #059669; color: #fff; }
        thead th { padding: 6px 8px; text-align: left; font-size: 9.5px; font-weight: 700; }
        thead th.right { text-align: right; }
        tbody tr:nth-child(even) { background: #f0fdf4; }
        tbody td { padding: 5px 8px; font-size: 10.5px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
        tbody td.right { text-align: right; }
        tfoot td { padding: 5px 8px; }

        /* Totals */
        .totals-block { display: flex; justify-content: flex-end; margin-bottom: 18px; }
        .totals-table { width: 250px; }
        .totals-table tr td:first-child { color: #475569; font-size: 10px; padding: 3px 0; }
        .totals-table tr td:last-child { text-align: right; font-size: 11px; font-weight: 600; padding: 3px 0; }
        .totals-table .total-row td { font-size: 14px; font-weight: 700; color: #059669; border-top: 2px solid #059669; padding-top: 6px; }

        /* Notes / Terms */
        .text-box { border: 1px solid #e2e8f0; border-radius: 5px; padding: 8px 10px; font-size: 10.5px; color: #334155; margin-bottom: 12px; min-height: 30px; line-height: 1.5; }
        .text-box-label { font-size: 9px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 4px; }

        /* Validity strip */
        .validity-strip { background: #f0fdf4; border: 1px solid #a7f3d0; border-radius: 6px; padding: 8px 14px; margin-bottom: 14px; display: flex; justify-content: space-between; align-items: center; }
        .validity-strip .valid-label { font-size: 10px; color: #065f46; font-weight: 600; }
        .validity-strip .valid-date { font-size: 12px; font-weight: 700; color: #059669; }

        /* Signature lines */
        .sig-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 20px; }
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

<div class="no-print" style="background:#059669;color:#fff;padding:8px 20px;display:flex;justify-content:space-between;align-items:center;font-family:Arial,sans-serif;font-size:12px;">
    <span>Cotización — {{ $quotation->folio }}</span>
    <div style="display:flex;gap:10px;">
        <button onclick="window.print()" style="background:#fff;color:#059669;border:none;padding:6px 16px;border-radius:6px;font-weight:700;cursor:pointer;font-size:12px;">🖨 Imprimir / PDF</button>
        <a href="{{ url()->previous() }}" style="color:#a7f3d0;text-decoration:none;padding:6px 12px;">← Regresar</a>
    </div>
</div>

<div class="page">

    {{-- HEADER --}}
    @php $company = $quotation->company; @endphp
    <div class="header">
        <div class="header-logo">
            @if($company->print_logo ?? $company->logo ?? null)
                <img src="{{ Storage::url($company->print_logo ?? $company->logo) }}" alt="{{ $company->name }}">
            @else
                <div class="company-name">{{ $company->name }}</div>
            @endif
        </div>
        <div class="header-right">
            <div class="doc-title">Cotización</div>
            <div class="folio">{{ $quotation->folio }}</div>
            @php
                $statusClass = match($quotation->status) {
                    'accepted' => 'status-accepted',
                    'rejected' => 'status-rejected',
                    'sent'     => 'status-sent',
                    'expired'  => 'status-expired',
                    default    => 'status-draft',
                };
                $statusLabel = \App\Models\SaleQuotation::STATUS[$quotation->status] ?? $quotation->status;
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
                <span class="info-value large">{{ $quotation->customer?->name ?? '—' }}</span>
            </div>
            @if($quotation->customer?->rfc)
                <div class="info-row">
                    <span class="info-label">RFC</span>
                    <span class="info-value">{{ $quotation->customer->rfc }}</span>
                </div>
            @endif
            @php $phone = $quotation->customer?->phones?->firstWhere('is_primary', true)?->number ?? $quotation->customer?->phones?->first()?->number; @endphp
            @if($phone)
                <div class="info-row">
                    <span class="info-label">Teléfono</span>
                    <span class="info-value">{{ $phone }}</span>
                </div>
            @endif
            @php $email = $quotation->customer?->emails?->firstWhere('is_primary', true)?->email ?? $quotation->customer?->emails?->first()?->email; @endphp
            @if($email)
                <div class="info-row">
                    <span class="info-label">Correo</span>
                    <span class="info-value">{{ $email }}</span>
                </div>
            @endif
            @if($quotation->customer?->address)
                <div class="info-row" style="margin-top:4px;">
                    <span class="info-label">Dirección</span>
                    <span class="info-value">{{ implode(', ', array_filter([$quotation->customer->address, $quotation->customer->city, $quotation->customer->state])) }}</span>
                </div>
            @endif
        </div>

        {{-- Detalles del documento --}}
        <div class="info-box">
            <div class="info-box-title">Detalles</div>
            <div class="info-row">
                <span class="info-label">Fecha</span>
                <span class="info-value">{{ $quotation->created_at?->format('d/m/Y') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Vigencia</span>
                <span class="info-value {{ $quotation->valid_until?->isPast() ? 'red' : '' }}">
                    {{ $quotation->valid_until?->format('d/m/Y') ?? '—' }}
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Moneda</span>
                <span class="info-value">{{ strtoupper($quotation->currency ?? 'MXN') }}</span>
            </div>
            @if($quotation->priceList)
                <div class="info-row">
                    <span class="info-label">Lista de precios</span>
                    <span class="info-value">{{ $quotation->priceList->name }}</span>
                </div>
            @endif
            <div class="info-row">
                <span class="info-label">Elaboró</span>
                <span class="info-value">{{ $quotation->createdBy?->name ?? '—' }}</span>
            </div>
            @if($quotation->branch)
                <div class="info-row">
                    <span class="info-label">Sucursal</span>
                    <span class="info-value">{{ $quotation->branch->name }}</span>
                </div>
            @endif
        </div>
    </div>

    {{-- PRODUCTOS --}}
    <div class="section-title">Partidas</div>
    <table>
        <thead>
            <tr>
                <th style="width:30px">#</th>
                <th>Descripción</th>
                <th style="width:55px" class="right">Cant.</th>
                <th style="width:55px">Unidad</th>
                <th style="width:85px" class="right">P. unitario</th>
                <th style="width:55px" class="right">Desc%</th>
                <th style="width:90px" class="right">Importe</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quotation->items as $i => $item)
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
                        @if($item->notes)
                            <br><span style="font-size:9px;color:#64748b;">{{ $item->notes }}</span>
                        @endif
                    </td>
                    <td class="right">{{ number_format($item->quantity, 2) }}</td>
                    <td>{{ $item->unit ?? '—' }}</td>
                    <td class="right">${{ number_format($item->unit_price, 2) }}</td>
                    <td class="right">{{ $item->discount_pct > 0 ? number_format($item->discount_pct, 1).'%' : '—' }}</td>
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
                <td>${{ number_format($quotation->subtotal, 2) }}</td>
            </tr>
            @if($quotation->discount_amount > 0)
                <tr>
                    <td>Descuento</td>
                    <td style="color:#dc2626;">- ${{ number_format($quotation->discount_amount, 2) }}</td>
                </tr>
            @endif
            <tr>
                <td>IVA (16%)</td>
                <td>${{ number_format($quotation->tax, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td>TOTAL</td>
                <td>${{ number_format($quotation->total, 2) }} {{ strtoupper($quotation->currency ?? 'MXN') }}</td>
            </tr>
        </table>
    </div>

    {{-- VIGENCIA --}}
    @if($quotation->valid_until)
        <div class="validity-strip">
            <span class="valid-label">Esta cotización es válida hasta:</span>
            <span class="valid-date">{{ $quotation->valid_until->format('d \d\e F \d\e Y') }}</span>
        </div>
    @endif

    {{-- NOTAS --}}
    @if($quotation->notes)
        <div class="text-box-label">Notas</div>
        <div class="text-box">{{ $quotation->notes }}</div>
    @endif

    {{-- TÉRMINOS --}}
    @if($quotation->terms)
        <div class="text-box-label">Términos y condiciones</div>
        <div class="text-box">{{ $quotation->terms }}</div>
    @endif

    {{-- FIRMAS --}}
    <div class="sig-grid" style="margin-top: 30px;">
        <div class="sig-box" style="height:50px;">
            <div class="sig-box-name">{{ $quotation->createdBy?->name ?? '_________________________' }}</div>
            <div class="sig-box-label">Elaboró · {{ $company->name }}</div>
        </div>
        <div class="sig-box" style="height:50px;">
            <div class="sig-box-name">{{ $quotation->customer?->name ?? '_________________________' }}</div>
            <div class="sig-box-label">Acepta · Cliente</div>
        </div>
    </div>

    {{-- FOOTER --}}
    <div class="footer">
        <span>{{ $company->name }}@if($company->address) · {{ $company->address }}@endif</span>
        <span>{{ $quotation->folio }} · Generado: {{ now()->format('d/m/Y H:i') }}</span>
    </div>

</div>
</body>
</html>

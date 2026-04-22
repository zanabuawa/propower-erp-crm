<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Requisición {{ $requisition->folio }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #1a1a1a;
            background: #fff;
            padding: 0;
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 14mm 16mm 14mm 16mm;
        }

        /* ── Header ── */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #1e40af;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }
        .header-logo img { max-height: 70px; max-width: 200px; object-fit: contain; }
        .header-logo .company-name { font-size: 16px; font-weight: 700; color: #1e3a8a; }
        .header-right { text-align: right; }
        .header-right .doc-title { font-size: 18px; font-weight: 700; color: #1e3a8a; text-transform: uppercase; letter-spacing: 1px; }
        .header-right .folio { font-size: 22px; font-weight: 800; color: #1e40af; }
        .header-right .status-badge {
            display: inline-block;
            margin-top: 4px;
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .status-authorized { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
        .status-rejected   { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .status-pending    { background: #fef9c3; color: #854d0e; border: 1px solid #fde047; }

        /* ── Info grid ── */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px 20px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 12px;
            margin-bottom: 14px;
        }
        .info-item { display: flex; flex-direction: column; }
        .info-label { font-size: 9px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 1px; }
        .info-value { font-size: 11px; color: #1e293b; }
        .info-item.full { grid-column: 1 / -1; }

        /* ── Section title ── */
        .section-title {
            font-size: 10px;
            font-weight: 700;
            color: #1e40af;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            border-bottom: 1px solid #bfdbfe;
            padding-bottom: 4px;
            margin-bottom: 8px;
        }

        /* ── Table ── */
        table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        thead tr { background: #1e40af; color: #fff; }
        thead th { padding: 6px 8px; text-align: left; font-size: 9.5px; font-weight: 700; letter-spacing: 0.3px; }
        thead th.right { text-align: right; }
        tbody tr:nth-child(even) { background: #f8fafc; }
        tbody td { padding: 5px 8px; font-size: 10.5px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
        tbody td.right { text-align: right; }
        tbody td.num { font-variant-numeric: tabular-nums; }
        tfoot td { padding: 5px 8px; font-size: 11px; }

        /* ── Totals ── */
        .totals-block { display: flex; justify-content: flex-end; margin-bottom: 18px; }
        .totals-table { width: 240px; }
        .totals-table tr td:first-child { color: #475569; font-size: 10px; }
        .totals-table tr td:last-child { text-align: right; font-size: 11px; font-weight: 600; }
        .totals-table .total-row td { font-size: 13px; font-weight: 700; color: #1e40af; border-top: 2px solid #1e40af; padding-top: 5px; }

        /* ── Justification ── */
        .justification-box {
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            padding: 8px 10px;
            font-size: 10.5px;
            color: #334155;
            margin-bottom: 18px;
            min-height: 36px;
            line-height: 1.5;
        }

        /* ── Signatures ── */
        .signatures-section { margin-top: 18px; }
        .signatures-grid { display: grid; gap: 14px; }
        .signatures-grid.cols-1 { grid-template-columns: 1fr; max-width: 280px; }
        .signatures-grid.cols-2 { grid-template-columns: 1fr 1fr; }
        .signatures-grid.cols-3 { grid-template-columns: 1fr 1fr 1fr; }

        .sig-card {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            overflow: hidden;
        }
        .sig-card-header {
            background: #f1f5f9;
            padding: 5px 10px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .sig-role { font-size: 9.5px; font-weight: 700; color: #334155; text-transform: uppercase; letter-spacing: 0.5px; }
        .sig-status { font-size: 9px; font-weight: 700; padding: 2px 7px; border-radius: 8px; }
        .sig-status.approved { background: #dcfce7; color: #166534; }
        .sig-status.rejected { background: #fee2e2; color: #991b1b; }
        .sig-status.pending  { background: #fef9c3; color: #854d0e; }

        .sig-body { padding: 8px 10px; }
        .sig-canvas-area {
            height: 64px;
            border: 1px dashed #cbd5e1;
            border-radius: 4px;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: #fafafa;
        }
        .sig-canvas-area img { max-height: 62px; max-width: 100%; object-fit: contain; }
        .sig-canvas-area .no-sig { font-size: 9px; color: #94a3b8; font-style: italic; }

        .sig-name { font-size: 10px; font-weight: 700; color: #1e293b; }
        .sig-date { font-size: 9px; color: #64748b; }
        .sig-comment { font-size: 9.5px; color: #475569; margin-top: 3px; font-style: italic; border-top: 1px solid #f1f5f9; padding-top: 3px; }

        /* ── Requester signature box ── */
        .requester-sig {
            display: flex;
            gap: 16px;
            margin-bottom: 18px;
        }
        .req-sig-box {
            flex: 1;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px 12px;
        }
        .req-sig-label { font-size: 9px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 4px; }
        .req-sig-line { border-bottom: 1px solid #475569; height: 38px; }
        .req-sig-name { font-size: 10px; color: #334155; margin-top: 4px; text-align: center; }

        /* ── Footer ── */
        .footer {
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
            font-size: 8.5px;
            color: #94a3b8;
        }

        @media print {
            body { padding: 0; }
            .page { padding: 10mm 14mm; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

{{-- Print bar --}}
<div class="no-print" style="background:#1e40af;color:#fff;padding:8px 20px;display:flex;justify-content:space-between;align-items:center;font-size:12px;font-family:Arial,sans-serif;">
    <span>Requisición de compra — {{ $requisition->folio }}</span>
    <div style="display:flex;gap:10px;">
        <button onclick="window.print()"
            style="background:#fff;color:#1e40af;border:none;padding:6px 16px;border-radius:6px;font-weight:700;cursor:pointer;font-size:12px;">
            🖨 Imprimir / Guardar PDF
        </button>
        <a href="{{ url()->previous() }}"
            style="color:#bfdbfe;text-decoration:none;padding:6px 12px;">← Regresar</a>
    </div>
</div>

<div class="page">

    {{-- ── HEADER ── --}}
    <div class="header">
        <div class="header-logo">
            @php $company = $requisition->company; @endphp
            @if($company->print_logo ?? $company->logo ?? null)
                <img src="{{ Storage::url($company->print_logo ?? $company->logo) }}" alt="{{ $company->name }}">
            @else
                <div class="company-name">{{ $company->name }}</div>
            @endif
            @if($company->print_logo && $company->logo)
                <div style="font-size:10px;color:#475569;margin-top:4px;">{{ $company->name }}</div>
            @endif
        </div>
        <div class="header-right">
            <div class="doc-title">Requisición de compra</div>
            <div class="folio">{{ $requisition->folio }}</div>
            @php
                $statusLabel = \App\Models\PurchaseRequisition::STATUS[$requisition->status] ?? $requisition->status;
                $statusClass = match(true) {
                    $requisition->status === 'authorized' || $requisition->status === 'ordered' => 'status-authorized',
                    $requisition->status === 'rejected'  || $requisition->status === 'cancelled' => 'status-rejected',
                    default => 'status-pending',
                };
            @endphp
            <span class="status-badge {{ $statusClass }}">{{ $statusLabel }}</span>
        </div>
    </div>

    {{-- ── INFO GENERAL ── --}}
    <div class="info-grid">
        <div class="info-item">
            <span class="info-label">Solicitante</span>
            <span class="info-value">{{ $requisition->requestedBy?->name ?? '—' }}</span>
        </div>
        <div class="info-item">
            <span class="info-label">Sucursal / Área</span>
            <span class="info-value">{{ $requisition->branch?->name ?? '—' }}</span>
        </div>
        <div class="info-item">
            <span class="info-label">Fecha de solicitud</span>
            <span class="info-value">{{ $requisition->created_at?->format('d/m/Y') ?? '—' }}</span>
        </div>
        <div class="info-item">
            <span class="info-label">Se requiere para</span>
            <span class="info-value" style="{{ $requisition->needed_by ? 'font-weight:700;color:#dc2626;' : '' }}">
                {{ $requisition->needed_by?->format('d/m/Y') ?? '—' }}
            </span>
        </div>
        <div class="info-item">
            <span class="info-label">Moneda</span>
            <span class="info-value">{{ strtoupper($requisition->currency ?? 'MXN') }}</span>
        </div>
        @if($requisition->finalQuotation)
            <div class="info-item">
                <span class="info-label">Monto autorizado</span>
                <span class="info-value" style="font-weight:700;">
                    ${{ number_format($requisition->finalQuotation->total, 2) }} {{ strtoupper($requisition->currency ?? 'MXN') }}
                </span>
            </div>
        @endif
        @if($requisition->justification)
            <div class="info-item full">
                <span class="info-label">Justificación</span>
                <span class="info-value">{{ $requisition->justification }}</span>
            </div>
        @endif
    </div>

    {{-- ── PARTIDAS SOLICITADAS ── --}}
    <div class="section-title">Partidas solicitadas</div>
    <table>
        <thead>
            <tr>
                <th style="width:30px">#</th>
                <th>Descripción</th>
                <th style="width:55px" class="right">Cantidad</th>
                <th style="width:55px">Unidad</th>
                <th style="width:80px" class="right">P. unitario</th>
                <th style="width:90px" class="right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($requisition->items as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>
                        <strong>{{ $item->product?->name ?? $item->description }}</strong>
                        @if($item->notes)
                            <br><span style="font-size:9.5px;color:#64748b;">{{ $item->notes }}</span>
                        @endif
                    </td>
                    <td class="right num">{{ number_format($item->quantity, 2) }}</td>
                    <td>{{ $item->unit ?? $item->product?->unitOfMeasure?->abbreviation ?? '—' }}</td>
                    <td class="right num">${{ number_format($item->unit_price, 2) }}</td>
                    <td class="right num">${{ number_format($item->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ── TOTALES COTIZACIÓN FINAL ── --}}
    @if($quotation = $requisition->finalQuotation)
        @if($quotation->items->count() > 0)
            <div class="section-title">Cotización final aprobada</div>
            <table>
                <thead>
                    <tr>
                        <th style="width:30px">#</th>
                        <th>Descripción</th>
                        <th style="width:55px" class="right">Cantidad</th>
                        <th style="width:80px" class="right">P. unitario</th>
                        <th style="width:55px" class="right">IVA%</th>
                        <th style="width:90px" class="right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($quotation->items as $i => $qItem)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $qItem->description }}</td>
                            <td class="right num">{{ number_format($qItem->quantity, 2) }}</td>
                            <td class="right num">${{ number_format($qItem->unit_price, 2) }}</td>
                            <td class="right num">{{ $qItem->tax_rate ?? 16 }}%</td>
                            <td class="right num">${{ number_format($qItem->quantity * $qItem->unit_price, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <div class="totals-block">
            <table class="totals-table">
                <tr>
                    <td>Subtotal</td>
                    <td>${{ number_format($quotation->subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td>IVA</td>
                    <td>${{ number_format($quotation->tax, 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td>TOTAL</td>
                    <td>${{ number_format($quotation->total, 2) }} {{ strtoupper($requisition->currency ?? 'MXN') }}</td>
                </tr>
            </table>
        </div>
    @else
        {{-- Solo total de la requisición --}}
        <div class="totals-block">
            <table class="totals-table">
                <tr class="total-row">
                    <td>TOTAL ESTIMADO</td>
                    <td>${{ number_format($requisition->total, 2) }} {{ strtoupper($requisition->currency ?? 'MXN') }}</td>
                </tr>
            </table>
        </div>
    @endif

    {{-- ── FIRMAS DE AUTORIZACIÓN ── --}}
    @if($quotation?->approvals?->count())
        @php
            $approvals = $quotation->approvals->sortBy('level');
            $cols = match($approvals->count()) { 1 => 'cols-1', 2 => 'cols-2', default => 'cols-3' };
            $roleLabels = [
                'comprador'      => 'Compras',
                'admin'          => 'Administración',
                'gerente'        => 'Gerencia',
            ];
        @endphp

        <div class="signatures-section">
            <div class="section-title">Autorizaciones</div>
            <div class="signatures-grid {{ $cols }}">
                @foreach($approvals as $approval)
                    <div class="sig-card">
                        <div class="sig-card-header">
                            <span class="sig-role">{{ $roleLabels[$approval->role] ?? ucfirst($approval->role) }}</span>
                            @php
                                $sClass = match($approval->status) { 'approved' => 'approved', 'rejected' => 'rejected', default => 'pending' };
                                $sLabel = match($approval->status) { 'approved' => 'Autorizado', 'rejected' => 'Rechazado', default => 'Pendiente' };
                            @endphp
                            <span class="sig-status {{ $sClass }}">{{ $sLabel }}</span>
                        </div>
                        <div class="sig-body">
                            <div class="sig-canvas-area">
                                @if($approval->signature)
                                    <img src="{{ $approval->signature }}" alt="Firma">
                                @else
                                    <span class="no-sig">Sin firma</span>
                                @endif
                            </div>
                            <div class="sig-name">{{ $approval->user?->name ?? '—' }}</div>
                            <div class="sig-date">
                                {{ $approval->decided_at?->format('d/m/Y H:i') ?? ($approval->status === 'pending' ? 'Pendiente' : '—') }}
                            </div>
                            @if($approval->comments)
                                <div class="sig-comment">"{{ $approval->comments }}"</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    @else
        {{-- Espacio para firma física si no hay firmas digitales --}}
        <div class="signatures-section">
            <div class="section-title">Autorizaciones</div>
            <div class="requester-sig">
                <div class="req-sig-box">
                    <div class="req-sig-label">Solicitante</div>
                    <div class="req-sig-line"></div>
                    <div class="req-sig-name">{{ $requisition->requestedBy?->name ?? '' }}</div>
                </div>
                <div class="req-sig-box">
                    <div class="req-sig-label">Compras</div>
                    <div class="req-sig-line"></div>
                    <div class="req-sig-name">&nbsp;</div>
                </div>
                <div class="req-sig-box">
                    <div class="req-sig-label">Autorización</div>
                    <div class="req-sig-line"></div>
                    <div class="req-sig-name">&nbsp;</div>
                </div>
            </div>
        </div>
    @endif

    {{-- ── FOOTER ── --}}
    <div class="footer">
        <span>{{ $company->name }} — Documento interno</span>
        <span>Folio: {{ $requisition->folio }} &nbsp;|&nbsp; Generado: {{ now()->format('d/m/Y H:i') }}</span>
    </div>

</div>
</body>
</html>

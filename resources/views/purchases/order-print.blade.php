@php
    $en = $order->print_language === 'en';
    $company    = $order->company;
    $supplier   = $order->supplier;
    $branch     = $order->branch;
    $destBranch = $order->requisition?->branch;
    $approvals  = $order->requisition?->finalQuotation?->approvals?->sortBy('level') ?? collect();

    $printLogoPath = $company->print_logo ?? $company->logo ?? null;

    $labels = $en ? [
        'doc_title'      => 'PURCHASE ORDER',
        'origin'         => 'Origin',
        'destination'    => 'Destination',
        'order_details'  => 'Order Details',
        'folio'          => 'Folio',
        'requisition'    => 'Requisition',
        'responsible'    => 'Responsible',
        'order_date'     => 'Order Date',
        'delivery_date'  => 'Expected Delivery',
        'required_date'  => 'Required By',
        'currency'       => 'Currency',
        'supplier_title' => 'Supplier',
        'vendor_code'    => 'Vendor Code',
        'contact'        => 'Contact',
        'phone'          => 'Phone',
        'email'          => 'Email',
        'city'           => 'City',
        'items_title'    => 'Line Items',
        'col_desc'       => 'Description',
        'col_sku'        => 'SKU',
        'col_unit'       => 'Unit',
        'col_qty'        => 'Qty',
        'col_price'      => 'Unit Price',
        'col_tax'        => 'Tax',
        'col_subtotal'   => 'Subtotal',
        'subtotal'       => 'Subtotal',
        'tax'            => 'Tax',
        'total'          => 'Total',
        'payment_title'  => 'Payment Terms',
        'credit_days'    => 'Credit Days',
        'bank'           => 'Bank',
        'account'        => 'Account',
        'clabe'          => 'CLABE',
        'ship_to'        => 'Ship To',
        'bill_to'        => 'Bill To',
        'notes_title'    => 'Notes',
        'auth_title'     => 'Authorizations',
        'auth_approved'  => 'Approved',
        'auth_pending'   => 'Pending',
        'auth_rejected'  => 'Rejected',
        'immediate'      => 'Immediate',
        'footer_doc'     => 'Internal document — not a fiscal invoice',
        'generated'      => 'Generated',
        'print_btn'      => 'Print / Save PDF',
        'close_btn'      => 'Close',
        'status'         => \App\Models\PurchaseOrder::STATUS,
    ] : [
        'doc_title'      => 'ORDEN DE COMPRA',
        'origin'         => 'Origen',
        'destination'    => 'Destino',
        'order_details'  => 'Datos de la compra',
        'folio'          => 'Folio',
        'requisition'    => 'Requisición',
        'responsible'    => 'Responsable',
        'order_date'     => 'Fecha de compra',
        'delivery_date'  => 'Entrega tentativa',
        'required_date'  => 'Fecha requerida',
        'currency'       => 'Moneda',
        'supplier_title' => 'Proveedor',
        'vendor_code'    => 'Código interno',
        'contact'        => 'Contacto',
        'phone'          => 'Teléfono',
        'email'          => 'Email',
        'city'           => 'Ciudad',
        'items_title'    => 'Partidas de la orden',
        'col_desc'       => 'Descripción',
        'col_sku'        => 'SKU',
        'col_unit'       => 'Unidad',
        'col_qty'        => 'Cant.',
        'col_price'      => 'Precio unit.',
        'col_tax'        => 'IVA',
        'col_subtotal'   => 'Subtotal',
        'subtotal'       => 'Subtotal',
        'tax'            => 'IVA',
        'total'          => 'Total',
        'payment_title'  => 'Condiciones de pago',
        'credit_days'    => 'Días de crédito',
        'bank'           => 'Banco',
        'account'        => 'Cuenta',
        'clabe'          => 'CLABE',
        'ship_to'        => 'Dirección de envío',
        'bill_to'        => 'Dirección de facturación',
        'notes_title'    => 'Notas',
        'auth_title'     => 'Autorizaciones',
        'auth_approved'  => 'Autorizado',
        'auth_pending'   => 'Pendiente',
        'auth_rejected'  => 'Rechazado',
        'immediate'      => 'Pago inmediato',
        'footer_doc'     => 'Documento interno — no tiene validez fiscal',
        'generated'      => 'Generado el',
        'print_btn'      => 'Imprimir / Guardar PDF',
        'close_btn'      => 'Cerrar',
        'status'         => \App\Models\PurchaseOrder::STATUS,
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ $en ? 'en' : 'es' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $labels['doc_title'] }} {{ $order->folio }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 11px; color: #1e293b; background: #fff; }

        @media print {
            body { margin: 0; }
            .no-print { display: none !important; }
            @page { size: A4; margin: 15mm 12mm; }
        }

        .page { max-width: 210mm; margin: 0 auto; padding: 20px; background: #fff; }

        /* Header */
        .header {
            display: flex; align-items: flex-start; justify-content: space-between;
            border-bottom: 2px solid #ef4444; padding-bottom: 14px; margin-bottom: 14px;
        }
        .company-logo { height: 70px; width: auto; object-fit: contain; }
        .company-info { flex: 1; padding-left: 16px; }
        .company-name { font-size: 15px; font-weight: 700; color: #1e293b; }
        .company-sub  { font-size: 10px; color: #64748b; margin-top: 2px; }
        .doc-title-block { text-align: right; }
        .doc-title  { font-size: 20px; font-weight: 700; color: #ef4444; letter-spacing: 1px; }
        .doc-folio  { font-size: 13px; font-weight: 600; color: #374151; margin-top: 4px; }
        .doc-status { display: inline-block; margin-top: 4px; padding: 2px 10px; border-radius: 999px; font-size: 10px; font-weight: 600; background: #fee2e2; color: #991b1b; }

        /* Info grid */
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px; }
        .info-box { border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px 12px; }
        .info-box-title { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #94a3b8; margin-bottom: 6px; }
        .info-row { display: flex; gap: 4px; margin-bottom: 2px; }
        .info-label { color: #64748b; min-width: 100px; }
        .info-value { font-weight: 600; color: #1e293b; }

        /* Branches bar */
        .branches-bar {
            display: flex; align-items: center; gap: 8px;
            background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px;
            padding: 8px 12px; margin-bottom: 12px; font-size: 10px;
        }
        .branch-label { color: #64748b; }
        .branch-name  { font-weight: 600; color: #1e293b; }
        .branch-arrow { color: #94a3b8; font-size: 14px; }

        /* Address bar */
        .address-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px; }
        .address-box { border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 12px; font-size: 10px; }
        .address-title { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #94a3b8; margin-bottom: 4px; }
        .address-text { color: #1e293b; line-height: 1.5; }

        /* Items table */
        .items-section-title { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #6b7280; margin-bottom: 6px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        thead th { background: #ef4444; color: #fff; font-size: 10px; font-weight: 600; padding: 6px 8px; text-align: left; }
        thead th.right  { text-align: right; }
        thead th.center { text-align: center; }
        tbody tr:nth-child(even) { background: #fafafa; }
        tbody td { padding: 5px 8px; border-bottom: 1px solid #f1f5f9; font-size: 10.5px; vertical-align: top; }
        tbody td.right  { text-align: right; }
        tbody td.center { text-align: center; }

        /* Totals */
        .totals-wrap { display: flex; justify-content: flex-end; margin-bottom: 14px; }
        .totals-table { width: 240px; border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden; }
        .totals-row { display: flex; justify-content: space-between; padding: 5px 12px; font-size: 10.5px; }
        .totals-row:not(:last-child) { border-bottom: 1px solid #f1f5f9; }
        .totals-row.grand { background: #ef4444; color: #fff; font-size: 12px; font-weight: 700; }
        .totals-value { font-weight: 600; font-variant-numeric: tabular-nums; }

        /* Notes */
        .notes-box { border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 12px; margin-bottom: 14px; font-size: 10px; color: #475569; }
        .notes-box-title { font-weight: 700; color: #94a3b8; font-size: 9px; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 4px; }

        /* Authorizations */
        .auth-section-title { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #6b7280; margin-bottom: 8px; padding-bottom: 4px; border-bottom: 1px solid #e2e8f0; }
        .auth-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 10px; margin-bottom: 14px; }
        .auth-card { border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 10px; text-align: center; }
        .auth-sig-img   { max-width: 100%; max-height: 60px; object-fit: contain; display: block; margin: 0 auto 6px; }
        .auth-sig-blank { height: 60px; border-bottom: 1px solid #94a3b8; margin-bottom: 6px; }
        .auth-name  { font-weight: 600; font-size: 10px; color: #1e293b; }
        .auth-role  { font-size: 9px; color: #64748b; margin-top: 1px; }
        .auth-date  { font-size: 9px; color: #94a3b8; margin-top: 1px; }
        .auth-badge { display: inline-block; margin-top: 3px; padding: 1px 8px; border-radius: 999px; font-size: 9px; font-weight: 600; }
        .badge-approved { background: #dcfce7; color: #15803d; }
        .badge-pending  { background: #fef9c3; color: #854d0e; }
        .badge-rejected { background: #fee2e2; color: #991b1b; }

        /* Footer */
        .footer { border-top: 1px solid #e2e8f0; padding-top: 8px; display: flex; justify-content: space-between; font-size: 9px; color: #94a3b8; }
    </style>
</head>
<body>

{{-- ── Botones (solo pantalla) ─────────────────────────────────── --}}
<div class="no-print" style="text-align:right; padding:12px 20px;">
    <button onclick="window.print()"
        style="background:#ef4444;color:#fff;border:none;padding:8px 20px;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;">
        {{ $labels['print_btn'] }}
    </button>
    <button onclick="window.close()"
        style="background:#f3f4f6;color:#374151;border:1px solid #e5e7eb;padding:8px 20px;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;margin-left:8px;">
        {{ $labels['close_btn'] }}
    </button>
</div>

<div class="page">

    {{-- ── HEADER ──────────────────────────────────────────────────────── --}}
    <div class="header">
        <div style="display:flex; align-items:center;">
            @if($printLogoPath)
                <img src="{{ asset('storage/' . $printLogoPath) }}" alt="{{ $company->name }}" class="company-logo">
            @else
                <div style="width:52px;height:52px;background:#fee2e2;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:700;color:#ef4444;">
                    {{ mb_strtoupper(mb_substr($company->name, 0, 1)) }}
                </div>
            @endif
            <div class="company-info">
                <div class="company-name">{{ $company->name }}</div>
                @if($company->rfc)      <div class="company-sub">RFC: {{ $company->rfc }}</div> @endif
                @if($company->address)  <div class="company-sub">{{ $company->address }}{{ $company->city ? ', ' . $company->city : '' }}</div> @endif
            </div>
        </div>
        <div class="doc-title-block">
            <div class="doc-title">{{ $labels['doc_title'] }}</div>
            <div class="doc-folio">{{ $order->folio }}</div>
            <div class="doc-status">{{ $labels['status'][$order->status] ?? $order->status }}</div>
            @if($en)
                <div style="font-size:9px;color:#94a3b8;margin-top:3px;">Language: English</div>
            @endif
        </div>
    </div>

    {{-- ── ORIGIN / DESTINATION ─────────────────────────────────────────── --}}
    @if($branch || $destBranch)
    <div class="branches-bar">
        <span class="branch-label">{{ $labels['origin'] }}:</span>
        <span class="branch-name">{{ $branch?->name ?? $company->name }}</span>
        <span class="branch-arrow">→</span>
        <span class="branch-label">{{ $labels['destination'] }}:</span>
        <span class="branch-name">{{ $destBranch?->name ?? $branch?->name ?? '—' }}</span>
    </div>
    @endif

    {{-- ── ADDRESSES ────────────────────────────────────────────────────── --}}
    @if($order->shipping_address || $order->billing_address)
    <div class="address-grid">
        @if($order->shipping_address)
        <div class="address-box">
            <div class="address-title">{{ $labels['ship_to'] }}</div>
            <div class="address-text">{{ $order->shipping_address }}</div>
        </div>
        @endif
        @if($order->billing_address)
        <div class="address-box">
            <div class="address-title">{{ $labels['bill_to'] }}</div>
            <div class="address-text">{{ $order->billing_address }}</div>
        </div>
        @elseif($order->shipping_address)
        <div class="address-box">
            <div class="address-title">{{ $labels['bill_to'] }}</div>
            <div class="address-text" style="color:#94a3b8;">{{ $en ? 'Same as shipping address' : 'Igual a dirección de envío' }}</div>
        </div>
        @endif
    </div>
    @endif

    {{-- ── INFO GRID ─────────────────────────────────────────────────────── --}}
    <div class="info-grid">
        {{-- Order details --}}
        <div class="info-box">
            <div class="info-box-title">{{ $labels['order_details'] }}</div>
            <div class="info-row"><span class="info-label">{{ $labels['folio'] }}:</span><span class="info-value">{{ $order->folio }}</span></div>
            @if($order->requisition)
            <div class="info-row"><span class="info-label">{{ $labels['requisition'] }}:</span><span class="info-value">{{ $order->requisition->folio }}</span></div>
            @endif
            <div class="info-row"><span class="info-label">{{ $labels['responsible'] }}:</span><span class="info-value">{{ $order->createdBy?->name ?? '—' }}</span></div>
            <div class="info-row"><span class="info-label">{{ $labels['order_date'] }}:</span><span class="info-value">{{ $order->created_at->format('d/m/Y') }}</span></div>
            <div class="info-row"><span class="info-label">{{ $labels['delivery_date'] }}:</span><span class="info-value">{{ $order->expected_at ? $order->expected_at->format('d/m/Y') : '—' }}</span></div>
            @if($order->required_at)
            <div class="info-row"><span class="info-label">{{ $labels['required_date'] }}:</span><span class="info-value" style="color:#dc2626;font-weight:700;">{{ $order->required_at->format('d/m/Y') }}</span></div>
            @endif
            <div class="info-row"><span class="info-label">{{ $labels['currency'] }}:</span><span class="info-value">{{ $order->currency }}</span></div>
        </div>

        {{-- Supplier --}}
        @if($supplier)
        <div class="info-box">
            <div class="info-box-title">{{ $labels['supplier_title'] }}</div>
            <div class="info-row"><span class="info-label">{{ $en ? 'Company' : 'Empresa' }}:</span><span class="info-value">{{ $supplier->name }}</span></div>
            @if($supplier->internal_code)
            <div class="info-row"><span class="info-label">{{ $labels['vendor_code'] }}:</span><span class="info-value">{{ $supplier->internal_code }}</span></div>
            @endif
            @if($supplier->rfc)
            <div class="info-row"><span class="info-label">RFC:</span><span class="info-value">{{ $supplier->rfc }}</span></div>
            @endif
            @if($supplier->contacts->first())
            <div class="info-row"><span class="info-label">{{ $labels['contact'] }}:</span><span class="info-value">{{ $supplier->contacts->first()->name }}</span></div>
            @endif
            @if($supplier->phones->first())
            <div class="info-row"><span class="info-label">{{ $labels['phone'] }}:</span><span class="info-value">{{ $supplier->phones->first()->number }}</span></div>
            @endif
            @if($supplier->emails->first())
            <div class="info-row"><span class="info-label">{{ $labels['email'] }}:</span><span class="info-value">{{ $supplier->emails->first()->email }}</span></div>
            @endif
            @if($supplier->city)
            <div class="info-row"><span class="info-label">{{ $labels['city'] }}:</span><span class="info-value">{{ $supplier->city }}{{ $supplier->state ? ', ' . $supplier->state : '' }}</span></div>
            @endif
        </div>
        @else
        <div class="info-box">
            <div class="info-box-title">{{ $labels['supplier_title'] }}</div>
            <p style="font-size:10px;color:#64748b;">{{ $en ? 'Multiple suppliers — see line items.' : 'Múltiples proveedores — ver partidas.' }}</p>
        </div>
        @endif
    </div>

    {{-- ── ITEMS TABLE ──────────────────────────────────────────────────── --}}
    <div class="items-section-title">{{ $labels['items_title'] }}</div>
    <table>
        <thead>
            <tr>
                <th style="width:28px;" class="center">#</th>
                <th>{{ $labels['col_desc'] }}</th>
                <th style="width:90px;">{{ $labels['supplier_title'] }}</th>
                <th style="width:55px;" class="center">{{ $labels['col_sku'] }}</th>
                <th style="width:45px;" class="center">{{ $labels['col_unit'] }}</th>
                <th style="width:50px;" class="center">{{ $labels['col_qty'] }}</th>
                <th style="width:78px;" class="right">{{ $labels['col_price'] }}</th>
                <th style="width:40px;" class="center">{{ $labels['col_tax'] }}</th>
                <th style="width:85px;" class="right">{{ $labels['col_subtotal'] }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $i => $item)
            <tr>
                <td class="center">{{ $i + 1 }}</td>
                <td>{{ $item->description }}</td>
                <td style="color:#475569;font-size:9.5px;">{{ $item->supplier?->name ?? '—' }}</td>
                <td class="center" style="color:#64748b;font-size:9px;">{{ $item->product?->sku ?? '—' }}</td>
                <td class="center">{{ $item->unit ?: '—' }}</td>
                <td class="center">{{ number_format((float)$item->quantity, 2) }}</td>
                <td class="right">${{ number_format((float)$item->unit_price, 2) }}</td>
                <td class="center">{{ $item->tax_rate }}%</td>
                <td class="right">${{ number_format((float)$item->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ── TOTALS ───────────────────────────────────────────────────────── --}}
    <div class="totals-wrap">
        <div class="totals-table">
            <div class="totals-row">
                <span>{{ $labels['subtotal'] }}</span>
                <span class="totals-value">${{ number_format((float)$order->subtotal, 2) }}</span>
            </div>
            <div class="totals-row">
                <span>{{ $labels['tax'] }}</span>
                <span class="totals-value">${{ number_format((float)$order->tax, 2) }}</span>
            </div>
            <div class="totals-row grand">
                <span>{{ $labels['total'] }} {{ $order->currency }}</span>
                <span class="totals-value">${{ number_format((float)$order->total, 2) }}</span>
            </div>
        </div>
    </div>

    {{-- ── PAYMENT / BANK ───────────────────────────────────────────────── --}}
    @if($order->payment_terms !== null || $order->supplierBankAccount)
    <div class="notes-box">
        <div class="notes-box-title">{{ $labels['payment_title'] }}</div>
        <div style="display:flex;gap:24px;flex-wrap:wrap;">
            @if($order->payment_terms !== null)
            <div class="info-row">
                <span class="info-label">{{ $labels['credit_days'] }}:</span>
                <span class="info-value">{{ $order->payment_terms == 0 ? $labels['immediate'] : $order->payment_terms . ($en ? ' days' : ' días') }}</span>
            </div>
            @endif
            @if($order->supplierBankAccount)
            <div class="info-row"><span class="info-label">{{ $labels['bank'] }}:</span><span class="info-value">{{ $order->supplierBankAccount->bank_name }}</span></div>
            <div class="info-row"><span class="info-label">{{ $labels['account'] }}:</span><span class="info-value">{{ $order->supplierBankAccount->account_number }}</span></div>
            @if($order->supplierBankAccount->clabe)
            <div class="info-row"><span class="info-label">{{ $labels['clabe'] }}:</span><span class="info-value">{{ $order->supplierBankAccount->clabe }}</span></div>
            @endif
            @endif
        </div>
    </div>
    @endif

    {{-- ── NOTES ────────────────────────────────────────────────────────── --}}
    @if($order->notes)
    <div class="notes-box">
        <div class="notes-box-title">{{ $labels['notes_title'] }}</div>
        {{ $order->notes }}
    </div>
    @endif

    {{-- ── AUTHORIZATIONS ──────────────────────────────────────────────── --}}
    @if($approvals->count() > 0)
    <div class="auth-section-title">{{ $labels['auth_title'] }}</div>
    <div class="auth-grid">
        @foreach($approvals as $approval)
        @php
            $badge = match($approval->status) {
                'approved' => ['class' => 'badge-approved', 'label' => $labels['auth_approved']],
                'rejected' => ['class' => 'badge-rejected', 'label' => $labels['auth_rejected']],
                default    => ['class' => 'badge-pending',  'label' => $labels['auth_pending']],
            };
        @endphp
        <div class="auth-card">
            @if($approval->signature)
                <img src="{{ $approval->signature }}" alt="{{ $en ? 'Signature' : 'Firma' }}" class="auth-sig-img">
            @else
                <div class="auth-sig-blank"></div>
            @endif
            <div class="auth-name">{{ $approval->user?->name ?? ($en ? 'Pending' : 'Pendiente') }}</div>
            <div class="auth-role">{{ ucfirst($approval->role) }}</div>
            @if($approval->decided_at)
                <div class="auth-date">{{ $approval->decided_at->format('d/m/Y H:i') }}</div>
            @endif
            <div class="auth-badge {{ $badge['class'] }}">{{ $badge['label'] }}</div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- ── FOOTER ───────────────────────────────────────────────────────── --}}
    <div class="footer">
        <span>{{ $labels['generated'] }} {{ now()->format('d/m/Y H:i') }}</span>
        <span>{{ $company->name }} · {{ $order->folio }}</span>
        <span>{{ $labels['footer_doc'] }}</span>
    </div>

</div>
</body>
</html>

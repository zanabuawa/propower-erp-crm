<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Gantt - {{ $project->name }}</title>
    <style>
        @page { size: letter landscape; margin: 10mm; }
        * { box-sizing: border-box; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        body { font-family: Arial, sans-serif; color: #111827; font-size: 11px; margin: 0; }
        .print-btn { position: fixed; top: 12px; right: 12px; background: #4f46e5; color: #fff; border: 0; padding: 8px 16px; border-radius: 8px; font-weight: 700; cursor: pointer; z-index: 10; }
        @media print {
            .print-btn { display: none; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
        .header { display: grid; grid-template-columns: 150px 1fr 150px; align-items: center; gap: 14px; border-bottom: 2px solid #e5e7eb; padding-bottom: 10px; margin-bottom: 12px; }
        .logo { height: 70px; display: flex; align-items: center; justify-content: center; }
        .logo img { max-height: 68px; max-width: 145px; object-fit: contain; }
        .placeholder { width: 62px; height: 62px; border-radius: 10px; background: #eef2ff; color: #4f46e5; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 800; }
        .role { display: block; text-align: center; margin-top: 3px; font-size: 8px; font-weight: 800; color: #4338ca; text-transform: uppercase; letter-spacing: .12em; }
        .title { text-align: center; }
        .title h1 { margin: 0 0 6px; font-size: 18px; text-transform: uppercase; letter-spacing: .08em; }
        .title p { margin: 2px 0; color: #4b5563; }
        .meta { display: flex; justify-content: space-between; gap: 12px; margin-bottom: 10px; color: #4b5563; font-size: 10px; }
        .legend { display: flex; gap: 12px; margin-bottom: 10px; font-size: 9px; font-weight: 700; color: #4b5563; }
        .legend span { display: inline-flex; align-items: center; gap: 5px; }
        .dot { width: 14px; height: 8px; border-radius: 999px; display: inline-block; }
        .planned { background: #6366f1; color: #4f46e5; }
        .actual { background: #10b981; color: #059669; }
        .late { background: #ef4444; color: #dc2626; }
        .progress-line { background: #111827; color: #111827; }
        .gantt { border: 1px solid #e5e7eb; border-radius: 10px; overflow: hidden; }
        .row { display: grid; grid-template-columns: 190px 1fr; border-bottom: 1px solid #f1f5f9; break-inside: avoid; }
        .row:last-child { border-bottom: 0; }
        .head { background: #f8fafc; font-size: 9px; font-weight: 800; text-transform: uppercase; color: #64748b; }
        .cell { padding: 8px; min-height: 36px; }
        .activity.child { padding-left: 22px; }
        .activity strong { display: block; font-size: 10px; color: #111827; }
        .activity small { color: #64748b; }
        .timeline { position: relative; min-height: 42px; background: #f8fafc; overflow: hidden; }
        .tick { position: absolute; top: 0; bottom: 0; width: 1px; background: #dbe3ef; border-left: 1px solid #cbd5e1; }
        .tick-label { position: absolute; top: 8px; transform: translateX(-50%); color: #64748b; font-size: 8px; font-weight: 700; white-space: nowrap; }
        .bar {
            position: absolute;
            height: 8px;
            border-radius: 999px;
            border: 1.5px solid currentColor;
            box-shadow: inset 0 0 0 999px currentColor;
        }
        .bar.planned { top: 16px; }
        .bar.actual, .bar.late { bottom: 8px; }
        .bar.progress-line {
            top: 25px;
            height: 3px;
            min-width: 8px;
            border-width: 1px;
        }
        .progress { position: absolute; right: 6px; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,.9); border-radius: 999px; padding: 2px 6px; font-size: 8px; font-weight: 800; color: #475569; }
        .empty { padding: 28px; text-align: center; color: #94a3b8; }
    </style>
</head>
<body onload="window.print()">
    <button class="print-btn" onclick="window.print()">Imprimir / Guardar PDF</button>

    @php
        $companyLogo = $company?->print_logo ?? $company?->logo;
        $customerLogo = $customer?->image;
    @endphp

    <div class="header">
        <div>
            <div class="logo">
                @if($companyLogo)
                    <img src="{{ asset('storage/' . $companyLogo) }}" alt="{{ $company?->name }}">
                @else
                    <div class="placeholder">{{ mb_strtoupper(mb_substr($company?->name ?? 'E', 0, 1)) }}</div>
                @endif
            </div>
            <span class="role">Prestador</span>
        </div>

        <div class="title">
            <h1>Diagrama de Gantt</h1>
            <p><strong>Proyecto:</strong> {{ $project->name }}</p>
            <p><strong>Programa:</strong> {{ $program?->name ?? 'Sin programa vigente' }}</p>
        </div>

        <div>
            <div class="logo">
                @if($customerLogo)
                    <img src="{{ asset('storage/' . $customerLogo) }}" alt="{{ $customer?->name }}">
                @else
                    <div class="placeholder">{{ mb_strtoupper(mb_substr($customer?->name ?? 'C', 0, 1)) }}</div>
                @endif
            </div>
            <span class="role">Cliente</span>
        </div>
    </div>

    <div class="meta">
        <span><strong>Periodo:</strong> {{ $gantt['start']->format('d/m/Y') }} - {{ $gantt['end']->format('d/m/Y') }}</span>
        <span><strong>Escala:</strong> {{ $gantt['scale_label'] }}</span>
        <span><strong>Detalle:</strong> {{ ($printOptions['detail'] ?? 'all') === 'parents' ? 'Actividades principales' : 'Todas las actividades' }}</span>
        <span><strong>Exportado:</strong> {{ now()->format('d/m/Y H:i') }}</span>
    </div>

    <div class="legend">
        <span><i class="dot planned"></i> Programado</span>
        <span><i class="dot actual"></i> Real</span>
        <span><i class="dot late"></i> Real fuera de tiempo</span>
        <span><i class="dot progress-line"></i> Avance %</span>
    </div>

    <div class="gantt">
        <div class="row head">
            <div class="cell">Actividad</div>
            <div class="cell timeline">
                @foreach($gantt['ticks'] as $tick)
                    <span class="tick" style="left: {{ $tick['left'] }}%;"></span>
                    <span class="tick-label" style="left: {{ $tick['left'] }}%;">{{ $tick['label'] }}</span>
                @endforeach
            </div>
        </div>

        @forelse($gantt['rows'] as $row)
            <div class="row">
                <div class="cell activity {{ $row['is_child'] ? 'child' : '' }}">
                    <strong>{{ $row['name'] }}</strong>
                    <small>Prog. {{ $row['planned_label'] }} | Real {{ $row['actual_label'] }}</small>
                </div>
                <div class="cell timeline">
                    @foreach($gantt['ticks'] as $tick)
                        <span class="tick" style="left: {{ $tick['left'] }}%;"></span>
                    @endforeach
                    @if($row['planned'])
                        <span class="bar planned" style="left: {{ $row['planned']['left'] }}%; width: {{ $row['planned']['width'] }}%;"></span>
                    @endif
                    @if($row['progress_bar'])
                        <span class="bar progress-line" style="left: {{ $row['progress_bar']['left'] }}%; width: {{ $row['progress_bar']['width'] }}%;"></span>
                    @endif
                    @if($row['actual'])
                        <span class="bar {{ $row['is_late'] ? 'late' : 'actual' }}" style="left: {{ $row['actual']['left'] }}%; width: {{ $row['actual']['width'] }}%;"></span>
                    @endif
                    <span class="progress">{{ $row['progress'] }}%</span>
                </div>
            </div>
        @empty
            <div class="empty">No hay actividades para exportar.</div>
        @endforelse
    </div>
</body>
</html>

<div class="max-w-7xl mx-auto space-y-6">

    {{-- ── Header + filtros de período ──────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 px-1">
        <div>
            <h1 class="text-xl font-medium text-gray-900">Dashboard de Ventas</h1>
            <p class="text-sm text-gray-500 mt-0.5">Métricas y análisis del rendimiento comercial</p>
        </div>
        <div class="flex items-center gap-2">
            <div class="flex bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm text-sm">
                @foreach(['month' => 'Mes', 'quarter' => 'Trimestre', 'year' => 'Año', 'custom' => 'Personalizado'] as $val => $label)
                    <button type="button" wire:click="$set('period', '{{ $val }}')"
                        class="px-3 py-1.5 transition-colors {{ $period === $val ? 'bg-indigo-600 text-white font-medium' : 'text-gray-500 hover:bg-gray-50' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
            @if($period === 'custom')
                <input wire:model.live="dateFrom" type="date"
                    class="border border-gray-200 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-indigo-300">
                <span class="text-gray-400 text-sm">—</span>
                <input wire:model.live="dateTo" type="date"
                    class="border border-gray-200 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-indigo-300">
            @else
                <span class="text-xs text-gray-400">
                    {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} – {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}
                </span>
            @endif
        </div>
    </div>

    {{-- ── KPI Cards ──────────────────────────────────────────────────────── --}}
    @php $k = $this->kpis; @endphp
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Facturado --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Facturado</p>
                <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900">${{ number_format($k['invoiced_total'], 0) }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $k['invoiced_qty'] }} facturas</p>
            @if($k['growth_pct'] !== null)
                <div class="mt-2 flex items-center gap-1">
                    <span class="text-xs font-medium {{ $k['growth_pct'] >= 0 ? 'text-emerald-600' : 'text-red-500' }}">
                        {{ $k['growth_pct'] >= 0 ? '+' : '' }}{{ $k['growth_pct'] }}%
                    </span>
                    <span class="text-xs text-gray-400">vs período anterior</span>
                </div>
            @endif
        </div>

        {{-- Por cobrar --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Por cobrar</p>
                <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900">${{ number_format($k['pending_amount'], 0) }}</p>
            <p class="text-xs text-gray-400 mt-1">Pendiente de pago</p>
        </div>

        {{-- Ticket promedio --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Ticket promedio</p>
                <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900">${{ number_format($k['avg_ticket'], 0) }}</p>
            <p class="text-xs text-gray-400 mt-1">Por factura</p>
        </div>

        {{-- Conversión --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Conversión</p>
                <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900">{{ $k['conv_rate'] }}%</p>
            <p class="text-xs text-gray-400 mt-1">Cotización → Orden ({{ $k['quotations_qty'] }} cot. / {{ $k['orders_qty'] }} órd.)</p>
        </div>
    </div>

    {{-- ── Tendencia mensual + Embudo ─────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- Tendencia 12 meses --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <h3 class="text-sm font-medium text-gray-700 mb-4">Facturación mensual — últimos 12 meses</h3>
            @php
                $trend = $this->monthlyTrend;
                $maxVal = collect($trend)->max('total') ?: 1;
            @endphp
            @if(count($trend) > 0)
                <div class="flex items-end gap-1.5 h-40">
                    @foreach($trend as $t)
                        @php $pct = round($t['total'] / $maxVal * 100); @endphp
                        <div class="flex-1 flex flex-col items-center gap-1 group">
                            <div class="relative w-full">
                                <div class="w-full bg-indigo-500 rounded-t-sm transition-all"
                                    style="height: {{ max(2, round($pct * 1.52)) }}px"
                                    title="${{ number_format($t['total'], 0) }}">
                                </div>
                                <div class="absolute bottom-full mb-1 left-1/2 -translate-x-1/2 hidden group-hover:block
                                    bg-gray-900 text-white text-[10px] rounded px-1.5 py-0.5 whitespace-nowrap z-10">
                                    ${{ number_format($t['total'], 0) }}<br>{{ $t['qty'] }} fact.
                                </div>
                            </div>
                            <p class="text-[9px] text-gray-400 rotate-0 leading-none">{{ substr($t['label'], 0, 3) }}</p>
                        </div>
                    @endforeach
                </div>
                <div class="mt-3 flex justify-between text-xs text-gray-400">
                    <span>{{ $trend[0]['label'] ?? '' }}</span>
                    <span>{{ end($trend)['label'] ?? '' }}</span>
                </div>
            @else
                <div class="flex items-center justify-center h-40 text-gray-400 italic text-sm">Sin datos para mostrar</div>
            @endif
        </div>

        {{-- Embudo --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <h3 class="text-sm font-medium text-gray-700 mb-4">Embudo de ventas</h3>
            @php
                $funnel = $this->funnel;
                $maxF = collect($funnel)->max('value') ?: 1;
            @endphp
            <div class="space-y-3">
                @foreach($funnel as $step)
                    @php $w = max(20, round($step['value'] / $maxF * 100)); @endphp
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs text-gray-600">{{ $step['label'] }}</span>
                            <span class="text-sm font-bold text-gray-900">{{ $step['value'] }}</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-3">
                            <div class="{{ $step['color'] }} rounded-full h-3 transition-all" style="width: {{ $w }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
            @php $f = $this->funnel; @endphp
            @if($f[0]['value'] > 0)
                <div class="mt-4 pt-4 border-t border-gray-100 grid grid-cols-2 gap-2 text-center">
                    <div>
                        <p class="text-xs text-gray-400">Cot→Ord</p>
                        <p class="text-sm font-bold text-indigo-600">
                            {{ $f[0]['value'] > 0 ? round($f[1]['value']/$f[0]['value']*100,1) : 0 }}%
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Ord→Fact</p>
                        <p class="text-sm font-bold text-emerald-600">
                            {{ $f[1]['value'] > 0 ? round($f[2]['value']/$f[1]['value']*100,1) : 0 }}%
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- ── Top Clientes + Top Productos ───────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        {{-- Top Clientes --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-gray-700">Top clientes</h3>
                <a wire:navigate href="{{ route('sales.report') }}" class="text-xs text-indigo-600 hover:underline">Ver reporte →</a>
            </div>
            @php
                $customers = $this->topCustomers;
                $maxC = collect($customers)->max('total') ?: 1;
            @endphp
            @forelse($customers as $c)
                @php $pct = round($c['total'] / $maxC * 100); @endphp
                <div class="mb-3">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm text-gray-800 truncate max-w-[60%]">{{ $c['name'] }}</span>
                        <span class="text-sm font-semibold text-gray-900">${{ number_format($c['total'], 0) }}</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-1.5">
                        <div class="bg-indigo-500 rounded-full h-1.5" style="width: {{ $pct }}%"></div>
                    </div>
                    <p class="text-[10px] text-gray-400 mt-0.5">{{ $c['qty'] }} facturas</p>
                </div>
            @empty
                <p class="text-sm text-gray-400 italic text-center py-6">Sin datos en este período</p>
            @endforelse
        </div>

        {{-- Top Productos --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-gray-700">Top productos / servicios</h3>
                <a wire:navigate href="{{ route('sales.report') }}?reportType=products" class="text-xs text-indigo-600 hover:underline">Ver detalle →</a>
            </div>
            @php
                $prods = $this->topProducts;
                $maxP = collect($prods)->max('total_revenue') ?: 1;
            @endphp
            @forelse($prods as $p)
                @php $pct = round($p['total_revenue'] / $maxP * 100); @endphp
                <div class="mb-3">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm text-gray-800 truncate max-w-[60%]">{{ $p['description'] }}</span>
                        <span class="text-sm font-semibold text-gray-900">${{ number_format($p['total_revenue'], 0) }}</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-1.5">
                        <div class="bg-emerald-500 rounded-full h-1.5" style="width: {{ $pct }}%"></div>
                    </div>
                    <p class="text-[10px] text-gray-400 mt-0.5">{{ number_format($p['total_qty'], 0) }} uds.</p>
                </div>
            @empty
                <p class="text-sm text-gray-400 italic text-center py-6">Sin datos en este período</p>
            @endforelse
        </div>
    </div>

    {{-- ── Por vendedor ────────────────────────────────────────────────────── --}}
    @php $vendors = $this->byVendor; @endphp
    @if(count($vendors) > 1)
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
        <h3 class="text-sm font-medium text-gray-700 mb-4">Facturación por vendedor</h3>
        @php $maxV = collect($vendors)->max('total') ?: 1; @endphp
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($vendors as $v)
                @php $pct = round($v['total'] / $maxV * 100); @endphp
                <div class="bg-gray-50 rounded-lg p-3">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-800 truncate">{{ $v['name'] }}</span>
                        <span class="text-xs font-bold text-indigo-700">${{ number_format($v['total'], 0) }}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-1.5 mb-1">
                        <div class="bg-indigo-500 rounded-full h-1.5" style="width: {{ $pct }}%"></div>
                    </div>
                    <p class="text-[10px] text-gray-400">{{ $v['qty'] }} facturas</p>
                </div>
            @endforeach
        </div>
    </div>
    @endif

</div>

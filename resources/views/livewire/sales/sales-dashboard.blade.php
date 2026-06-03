<div class="space-y-6">

    {{-- ── Header + filtros de período ──────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 px-1">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Dashboard de Ventas</h1>
            <p class="text-sm text-slate-500 mt-0.5">
                {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} – {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}
            </p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <div class="flex bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm text-sm">
                @foreach(['month' => 'Mes', 'quarter' => 'Trimestre', 'year' => 'Año', 'custom' => 'Custom'] as $val => $label)
                    <button type="button" wire:click="$set('period', '{{ $val }}')"
                        class="px-3 py-1.5 transition-colors cursor-pointer
                            {{ $period === $val ? 'bg-indigo-600 text-white font-bold' : 'text-slate-500 hover:bg-slate-50' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
            @if($period === 'custom')
                <input wire:model.live="dateFrom" type="date"
                    class="border border-slate-200 rounded-xl px-3 py-1.5 text-sm focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400">
                <span class="text-slate-400 text-sm">—</span>
                <input wire:model.live="dateTo" type="date"
                    class="border border-slate-200 rounded-xl px-3 py-1.5 text-sm focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400">
            @endif
            <a wire:navigate href="{{ route('sales.report') }}"
                class="flex items-center gap-1.5 px-3 py-1.5 text-sm font-semibold text-indigo-600 border border-indigo-200 rounded-xl hover:bg-indigo-50 transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Reporte
            </a>
        </div>
    </div>

    {{-- Indicador de carga global --}}
    <div wire:loading wire:target="period,dateFrom,dateTo"
        class="flex items-center gap-2 text-xs text-indigo-600 font-semibold px-1">
        <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
        </svg>
        Actualizando métricas...
    </div>

    {{-- ── KPI Cards ──────────────────────────────────────────────────────── --}}
    @php $k = $this->kpis; @endphp
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4"
        wire:loading.class="opacity-60" wire:target="period,dateFrom,dateTo">

        {{-- Facturado --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Facturado</p>
                <div class="w-8 h-8 rounded-xl bg-emerald-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-black text-slate-900">${{ number_format($k['invoiced_total'], 0) }}</p>
            <p class="text-[10px] text-slate-400 mt-1 font-medium">{{ $k['invoiced_qty'] }} facturas</p>
            @if($k['growth_pct'] !== null)
                <div class="mt-2 flex items-center gap-1">
                    <span class="text-xs font-bold {{ $k['growth_pct'] >= 0 ? 'text-emerald-600' : 'text-rose-500' }}">
                        {{ $k['growth_pct'] >= 0 ? '+' : '' }}{{ $k['growth_pct'] }}%
                    </span>
                    <span class="text-[10px] text-slate-400">vs período anterior</span>
                </div>
            @endif
        </div>

        {{-- Por cobrar --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Por cobrar</p>
                <div class="w-8 h-8 rounded-xl bg-amber-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-black {{ $k['pending_amount'] > 0 ? 'text-amber-600' : 'text-slate-900' }}">${{ number_format($k['pending_amount'], 0) }}</p>
            <p class="text-[10px] text-slate-400 mt-1 font-medium">Pendiente de pago</p>
        </div>

        {{-- Ticket promedio --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Ticket promedio</p>
                <div class="w-8 h-8 rounded-xl bg-blue-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-black text-slate-900">${{ number_format($k['avg_ticket'], 0) }}</p>
            <p class="text-[10px] text-slate-400 mt-1 font-medium">Por factura</p>
        </div>

        {{-- Conversión --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Conversión</p>
                <div class="w-8 h-8 rounded-xl bg-indigo-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-black text-slate-900">{{ $k['conv_rate'] }}%</p>
            <p class="text-[10px] text-slate-400 mt-1 font-medium">{{ $k['quotations_qty'] }} cot. → {{ $k['orders_qty'] }} órd.</p>
        </div>
    </div>

    {{-- ── Segunda fila KPIs: IVA + Órdenes total ────────────────────────── --}}
    @if($k['invoiced_total'] > 0)
    <div class="grid grid-cols-2 gap-4"
        wire:loading.class="opacity-60" wire:target="period,dateFrom,dateTo">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm px-5 py-4 flex items-center gap-4">
            <div class="w-8 h-8 rounded-xl bg-blue-50 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">IVA trasladado</p>
                <p class="text-lg font-black text-slate-800">${{ number_format($k['invoiced_tax'], 0) }}</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm px-5 py-4 flex items-center gap-4">
            <div class="w-8 h-8 rounded-xl bg-teal-50 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Órdenes de venta</p>
                <p class="text-lg font-black text-slate-800">${{ number_format($k['orders_total'], 0) }} <span class="text-xs font-medium text-slate-400">({{ $k['orders_qty'] }})</span></p>
            </div>
        </div>
    </div>
    @endif

    {{-- ── Tendencia mensual + Embudo ─────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- Tendencia 12 meses --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-slate-700">Facturación mensual — últimos 12 meses</h3>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tendencia</span>
            </div>
            @php
                $trend  = $this->monthlyTrend;
                $maxVal = collect($trend)->max('total') ?: 1;
            @endphp
            @if(count($trend) > 0)
                <div class="flex items-end gap-1.5 h-36">
                    @foreach($trend as $t)
                        @php $pct = round($t['total'] / $maxVal * 100); @endphp
                        <div class="flex-1 flex flex-col items-center gap-1 group cursor-default">
                            <div class="relative w-full">
                                <div class="w-full bg-indigo-500 rounded-t transition-all group-hover:bg-indigo-400"
                                    style="height: {{ max(3, (int)round($pct * 1.32)) }}px">
                                </div>
                                <div class="absolute bottom-full mb-1.5 left-1/2 -translate-x-1/2 hidden group-hover:block
                                    bg-slate-900 text-white text-[10px] rounded-lg px-2 py-1 whitespace-nowrap z-10 shadow-lg">
                                    <span class="font-bold">${{ number_format($t['total'], 0) }}</span>
                                    <span class="text-slate-400 ml-1">{{ $t['qty'] }} fact.</span>
                                </div>
                            </div>
                            <p class="text-[9px] text-slate-400 leading-none">{{ substr($t['label'], 0, 3) }}</p>
                        </div>
                    @endforeach
                </div>
                <div class="mt-2 flex justify-between text-[10px] text-slate-400">
                    <span>{{ $trend[0]['label'] ?? '' }}</span>
                    <span>{{ end($trend)['label'] ?? '' }}</span>
                </div>
            @else
                <div class="flex flex-col items-center justify-center h-36 text-slate-400">
                    <svg class="w-8 h-8 mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    <p class="text-sm italic">Sin datos disponibles</p>
                </div>
            @endif
        </div>

        {{-- Embudo --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <h3 class="text-sm font-bold text-slate-700 mb-4">Embudo de ventas</h3>
            @php
                $funnel = $this->funnel;
                $maxF   = collect($funnel)->max('value') ?: 1;
            @endphp
            <div class="space-y-4">
                @foreach($funnel as $step)
                    @php $w = max(15, round($step['value'] / $maxF * 100)); @endphp
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-xs font-semibold text-slate-600">{{ $step['label'] }}</span>
                            <span class="text-sm font-black text-slate-900">{{ number_format($step['value']) }}</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2.5">
                            <div class="{{ $step['color'] }} rounded-full h-2.5 transition-all" style="width: {{ $w }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
            @if($funnel[0]['value'] > 0)
                <div class="mt-5 pt-4 border-t border-slate-100 grid grid-cols-2 gap-2 text-center">
                    <div class="bg-indigo-50 rounded-xl py-2">
                        <p class="text-[10px] font-bold text-indigo-400 uppercase tracking-widest">Cot→Ord</p>
                        <p class="text-base font-black text-indigo-700">
                            {{ round($funnel[1]['value'] / $funnel[0]['value'] * 100, 1) }}%
                        </p>
                    </div>
                    <div class="bg-emerald-50 rounded-xl py-2">
                        <p class="text-[10px] font-bold text-emerald-400 uppercase tracking-widest">Ord→Fact</p>
                        <p class="text-base font-black text-emerald-700">
                            {{ $funnel[1]['value'] > 0 ? round($funnel[2]['value'] / $funnel[1]['value'] * 100, 1) : 0 }}%
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- ── Top Clientes + Top Productos ───────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        {{-- Top Clientes --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-slate-700">Top clientes del período</h3>
                <a wire:navigate href="{{ route('sales.report') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition">Ver reporte →</a>
            </div>
            @php
                $topCustomers = $this->topCustomers;
                $maxC = collect($topCustomers)->max('total') ?: 1;
            @endphp
            @forelse($topCustomers as $c)
                @php $pct = round($c['total'] / $maxC * 100); @endphp
                <div class="mb-4">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm font-semibold text-slate-800 truncate max-w-[65%]">{{ $c['name'] }}</span>
                        <span class="text-sm font-black text-slate-900">${{ number_format($c['total'], 0) }}</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-1.5">
                        <div class="bg-indigo-500 rounded-full h-1.5 transition-all" style="width: {{ $pct }}%"></div>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-0.5">{{ $c['qty'] }} facturas</p>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center py-8 text-slate-400">
                    <svg class="w-8 h-8 mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <p class="text-sm italic">Sin datos en este período</p>
                </div>
            @endforelse
        </div>

        {{-- Top Productos --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-slate-700">Top productos / servicios</h3>
                <a wire:navigate href="{{ route('sales.report') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition">Ver detalle →</a>
            </div>
            @php
                $prods = $this->topProducts;
                $maxP  = collect($prods)->max('total_revenue') ?: 1;
            @endphp
            @forelse($prods as $p)
                @php $pct = round($p['total_revenue'] / $maxP * 100); @endphp
                <div class="mb-4">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm font-semibold text-slate-800 truncate max-w-[65%]">{{ $p['description'] }}</span>
                        <span class="text-sm font-black text-slate-900">${{ number_format($p['total_revenue'], 0) }}</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-1.5">
                        <div class="bg-emerald-500 rounded-full h-1.5 transition-all" style="width: {{ $pct }}%"></div>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-0.5">{{ number_format($p['total_qty'], 0) }} uds.</p>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center py-8 text-slate-400">
                    <svg class="w-8 h-8 mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    <p class="text-sm italic">Sin datos en este período</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- ── Por vendedor ────────────────────────────────────────────────────── --}}
    @php $vendors = $this->byVendor; @endphp
    @if(count($vendors) > 0)
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-slate-700">Facturación por vendedor</h3>
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ count($vendors) }} vendedor{{ count($vendors) > 1 ? 'es' : '' }}</span>
        </div>
        @php $maxV = collect($vendors)->max('total') ?: 1; @endphp
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($vendors as $v)
                @php $pct = round($v['total'] / $maxV * 100); @endphp
                <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-bold text-slate-800 truncate max-w-[65%]">{{ $v['name'] }}</span>
                        <span class="text-xs font-black text-indigo-700">${{ number_format($v['total'], 0) }}</span>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-1.5 mb-1.5">
                        <div class="bg-indigo-500 rounded-full h-1.5 transition-all" style="width: {{ $pct }}%"></div>
                    </div>
                    <p class="text-[10px] text-slate-400 font-medium">{{ $v['qty'] }} facturas</p>
                </div>
            @endforeach
        </div>
    </div>
    @endif

</div>

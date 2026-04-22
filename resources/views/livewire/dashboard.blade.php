<div class="space-y-6">

    {{-- ══ HEADER ═══════════════════════════════════════════════════════════ --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-lg font-semibold text-slate-800 leading-tight">
                Bienvenido, {{ auth()->user()->name }}
            </h1>
            <p class="text-sm text-slate-400 mt-0.5 capitalize">
                {{ now()->translatedFormat('l, d \d\e F \d\e Y') }}
            </p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            @if($branches->count() > 1)
            <select wire:model.live="branchId"
                    class="text-sm text-slate-600 bg-white border border-slate-200 rounded-lg px-3 py-1.5
                           shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer">
                <option value="">Todas las sucursales</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                @endforeach
            </select>
            @endif

            @can('create sales')
            <a wire:navigate href="{{ route('sales.quotations.create') }}"
               class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700
                      text-white text-sm font-medium px-3 py-1.5 rounded-lg
                      transition-colors duration-150 shadow-sm cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nueva cotización
            </a>
            @endcan

            @can('create purchases')
            <a wire:navigate href="{{ route('purchases.requisitions.create') }}"
               class="inline-flex items-center gap-1.5 bg-white hover:bg-slate-50
                      text-slate-700 border border-slate-200 text-sm font-medium px-3 py-1.5
                      rounded-lg transition-colors duration-150 shadow-sm cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nueva requisición
            </a>
            @endcan
        </div>
    </div>

    {{-- ══ KPI ROW — VENTAS ══════════════════════════════════════════════════ --}}
    @can('view sales summary')
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- Ventas del mes --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5
                    hover:shadow-md transition-shadow duration-200">
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0 flex-1">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                        Ventas del mes
                    </p>
                    <p class="mt-2 text-2xl font-bold text-slate-900 tabular-nums leading-none">
                        ${{ number_format($salesThisMonth, 0) }}
                    </p>
                </div>
                <div class="w-9 h-9 rounded-xl bg-indigo-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-[18px] h-[18px] text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                              d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                @if($salesGrowth !== null)
                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded-full
                                 {{ $salesGrowth >= 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600' }}">
                        @if($salesGrowth >= 0)
                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                            </svg>
                        @else
                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                            </svg>
                        @endif
                        {{ abs($salesGrowth) }}% vs mes anterior
                    </span>
                @else
                    <span class="text-xs text-slate-400">Sin datos previos</span>
                @endif
            </div>
        </div>

        {{-- Órdenes activas --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5
                    hover:shadow-md transition-shadow duration-200">
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0 flex-1">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                        Órdenes activas
                    </p>
                    <p class="mt-2 text-2xl font-bold text-slate-900 tabular-nums leading-none">
                        {{ $activeOrders }}
                    </p>
                </div>
                <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-[18px] h-[18px] text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                              d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <a wire:navigate href="{{ route('sales.orders.index') }}"
                   class="text-[11px] font-medium text-blue-600 hover:text-blue-700 cursor-pointer">
                    Ver órdenes →
                </a>
            </div>
        </div>

        {{-- Cotizaciones enviadas --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5
                    hover:shadow-md transition-shadow duration-200">
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0 flex-1">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                        Cotizaciones enviadas
                    </p>
                    <p class="mt-2 text-2xl font-bold text-slate-900 tabular-nums leading-none">
                        {{ $pendingQuotations }}
                    </p>
                </div>
                <div class="w-9 h-9 rounded-xl bg-amber-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-[18px] h-[18px] text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <a wire:navigate href="{{ route('sales.index') }}"
                   class="text-[11px] font-medium text-amber-600 hover:text-amber-700 cursor-pointer">
                    Ver cotizaciones →
                </a>
            </div>
        </div>

        {{-- Clientes nuevos --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5
                    hover:shadow-md transition-shadow duration-200">
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0 flex-1">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                        Clientes nuevos
                    </p>
                    <p class="mt-2 text-2xl font-bold text-slate-900 tabular-nums leading-none">
                        {{ $newCustomersThisMonth }}
                    </p>
                </div>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-[18px] h-[18px] text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-xs text-slate-400">Este mes</span>
            </div>
        </div>

    </div>
    @endcan

    {{-- ══ KPI ROW — OPERACIONES ════════════════════════════════════════════ --}}
    @if(auth()->user()->canAny(['view purchases summary', 'view inventory summary', 'view finance summary']))
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

        @can('view purchases summary')
        {{-- Requisiciones en proceso --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5
                    hover:shadow-md transition-shadow duration-200">
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0 flex-1">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                        Requisiciones en proceso
                    </p>
                    <p class="mt-2 text-2xl font-bold text-slate-900 tabular-nums leading-none">
                        {{ $pendingRequisitions }}
                    </p>
                </div>
                <div class="w-9 h-9 rounded-xl bg-violet-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-[18px] h-[18px] text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <a wire:navigate href="{{ route('purchases.index') }}"
                   class="text-[11px] font-medium text-violet-600 hover:text-violet-700 cursor-pointer">
                    Ver requisiciones →
                </a>
            </div>
        </div>

        {{-- OC abiertas --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5
                    hover:shadow-md transition-shadow duration-200">
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0 flex-1">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                        OC abiertas
                    </p>
                    <p class="mt-2 text-2xl font-bold text-slate-900 tabular-nums leading-none">
                        {{ $openPurchaseOrders }}
                    </p>
                </div>
                <div class="w-9 h-9 rounded-xl bg-teal-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-[18px] h-[18px] text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                              d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <a wire:navigate href="{{ route('purchases.orders.index') }}"
                   class="text-[11px] font-medium text-teal-600 hover:text-teal-700 cursor-pointer">
                    Ver órdenes →
                </a>
            </div>
        </div>
        @endcan

        @can('view inventory summary')
        {{-- Bajo mínimo (alert state) --}}
        <div class="bg-white rounded-2xl shadow-sm border p-5 transition-shadow duration-200
                    hover:shadow-md
                    {{ $lowStockProducts > 0 ? 'border-red-200 ring-1 ring-red-100' : 'border-slate-100' }}">
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0 flex-1">
                    <p class="text-[11px] font-semibold uppercase tracking-wider
                              {{ $lowStockProducts > 0 ? 'text-red-400' : 'text-slate-400' }}">
                        Productos bajo mínimo
                    </p>
                    <p class="mt-2 text-2xl font-bold tabular-nums leading-none
                              {{ $lowStockProducts > 0 ? 'text-red-700' : 'text-slate-900' }}">
                        {{ $lowStockProducts }}
                    </p>
                </div>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0
                            {{ $lowStockProducts > 0 ? 'bg-red-50' : 'bg-slate-50' }}">
                    <svg class="w-[18px] h-[18px] {{ $lowStockProducts > 0 ? 'text-red-500' : 'text-slate-300' }}"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                @if($lowStockProducts > 0)
                    <a wire:navigate href="{{ route('inventory.general') }}"
                       class="text-[11px] font-medium text-red-600 hover:text-red-700 cursor-pointer">
                        Ver existencias →
                    </a>
                @else
                    <span class="text-xs text-slate-400">Todo en orden</span>
                @endif
            </div>
        </div>
        @endcan

        @can('view finance summary')
        {{-- Facturas vencidas (alert state) --}}
        <div class="bg-white rounded-2xl shadow-sm border p-5 transition-shadow duration-200
                    hover:shadow-md
                    {{ $overdueInvoices > 0 ? 'border-orange-200 ring-1 ring-orange-100' : 'border-slate-100' }}">
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0 flex-1">
                    <p class="text-[11px] font-semibold uppercase tracking-wider
                              {{ $overdueInvoices > 0 ? 'text-orange-400' : 'text-slate-400' }}">
                        Facturas vencidas
                    </p>
                    <p class="mt-2 text-2xl font-bold tabular-nums leading-none
                              {{ $overdueInvoices > 0 ? 'text-orange-700' : 'text-slate-900' }}">
                        {{ $overdueInvoices }}
                    </p>
                </div>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0
                            {{ $overdueInvoices > 0 ? 'bg-orange-50' : 'bg-slate-50' }}">
                    <svg class="w-[18px] h-[18px] {{ $overdueInvoices > 0 ? 'text-orange-500' : 'text-slate-300' }}"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                @if($overdueInvoices > 0)
                    <span class="text-[11px] font-semibold text-orange-600">
                        ${{ number_format($overdueTotal, 0) }} por cobrar
                    </span>
                @else
                    <span class="text-xs text-slate-400">Sin vencidas</span>
                @endif
            </div>
        </div>
        @endcan

    </div>
    @endif

    {{-- ══ CHART + ÚLTIMAS ÓRDENES ══════════════════════════════════════════ --}}
    @can('view sales summary')
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">

        {{-- Bar chart —  ventas 6 meses --}}
        <div class="lg:col-span-3 bg-white rounded-2xl shadow-sm border border-slate-100 p-5"
             x-data="{
                chart: null,
                init() {
                    this.$nextTick(() => {
                        const labels = {{ Js::from($salesChart->pluck('label')) }};
                        const data   = {{ Js::from($salesChart->pluck('value')) }};
                        const ctx    = this.$refs.canvas.getContext('2d');
                        if (this.chart) this.chart.destroy();
                        this.chart   = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels,
                                datasets: [{
                                    label: 'Ventas',
                                    data,
                                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                                    borderColor: '#4f46e5',
                                    borderWidth: 2,
                                    borderRadius: 6,
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: { legend: { display: false } },
                                scales: {
                                    y: { beginAtZero: true, border: { display: false }, ticks: { color: '#94a3b8', font: { size: 10 } } },
                                    x: { border: { display: false }, grid: { display: false }, ticks: { color: '#94a3b8', font: { size: 10 } } }
                                }
                            }
                        });
                    });
                }
             }">
            <div class="flex items-start justify-between mb-5">
                <div>
                    <p class="text-sm font-semibold text-slate-800">Ventas últimos 6 meses</p>
                    <p class="text-xs text-slate-400 mt-0.5">Órdenes de venta confirmadas</p>
                </div>
            </div>
            <div class="h-52">
                <canvas x-ref="canvas"></canvas>
            </div>
        </div>

        {{-- Recent orders list --}}
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-100 p-5 flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <p class="text-sm font-semibold text-slate-800">Últimas órdenes</p>
                <a wire:navigate href="{{ route('sales.orders.index') }}"
                   class="text-xs font-medium text-indigo-600 hover:text-indigo-700 cursor-pointer">
                    Ver todas →
                </a>
            </div>

            <div class="flex-1 space-y-0.5">
                @forelse($recentOrders as $order)
                <a wire:navigate href="{{ route('sales.orders.show', $order) }}"
                   class="flex items-center gap-3 py-2.5 px-2 -mx-2 rounded-lg
                          hover:bg-slate-50 transition-colors duration-150 cursor-pointer group">
                    {{-- Avatar initials --}}
                    <div class="w-7 h-7 rounded-full bg-indigo-50 flex items-center justify-center
                                flex-shrink-0 text-[10px] font-bold text-indigo-500">
                        {{ strtoupper(substr($order->customer?->name ?? '?', 0, 2)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-slate-700 truncate leading-tight">
                            {{ $order->customer?->name ?? '—' }}
                        </p>
                        <p class="text-[10px] text-slate-400 leading-tight mt-0.5">
                            {{ $order->folio }} · {{ $order->created_at->diffForHumans() }}
                        </p>
                    </div>
                    <div class="flex flex-col items-end gap-1 flex-shrink-0">
                        <span class="text-xs font-semibold text-slate-800 tabular-nums">
                            ${{ number_format($order->total, 0) }}
                        </span>
                        @php $sc = \App\Models\SaleOrder::STATUS_COLORS[$order->status] ?? 'bg-gray-100 text-gray-600'; @endphp
                        <span class="text-[10px] px-1.5 py-0.5 rounded-full font-medium {{ $sc }}">
                            {{ \App\Models\SaleOrder::STATUS[$order->status] ?? $order->status }}
                        </span>
                    </div>
                </a>
                @empty
                <div class="flex flex-col items-center justify-center py-10 text-center">
                    <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center mb-2">
                        <svg class="w-5 h-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                    </div>
                    <p class="text-xs text-slate-400">Sin órdenes aún</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ── Top 5 productos ──────────────────────────────────────────────── --}}
    @if($topProducts->isNotEmpty())
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
        <div class="flex items-center justify-between mb-5">
            <div>
                <p class="text-sm font-semibold text-slate-800">Top productos del mes</p>
                <p class="text-xs text-slate-400 mt-0.5">Por unidades vendidas</p>
            </div>
        </div>
        <div class="space-y-4">
            @foreach($topProducts as $i => $item)
            @php $maxQty = $topProducts->max('total_qty'); $pct = $maxQty > 0 ? ($item->total_qty / $maxQty) * 100 : 0; @endphp
            <div class="flex items-center gap-3">
                <span class="w-5 h-5 rounded-full bg-indigo-50 text-indigo-500 text-[10px] font-bold
                             flex items-center justify-center flex-shrink-0">
                    {{ $i + 1 }}
                </span>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-4 mb-1.5">
                        <p class="text-xs font-medium text-slate-700 truncate">
                            {{ $item->product?->name ?? '—' }}
                        </p>
                        <p class="text-xs text-slate-500 flex-shrink-0 tabular-nums">
                            <span class="font-semibold text-slate-700">{{ number_format($item->total_qty, 0) }}</span> uds
                            <span class="text-slate-300 mx-1">·</span>
                            ${{ number_format($item->total_revenue, 0) }}
                        </p>
                    </div>
                    <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-indigo-400 rounded-full transition-all duration-500"
                             style="width: {{ $pct }}%"></div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
    @endcan

    {{-- ══ TABLES ROW ═══════════════════════════════════════════════════════ --}}
    @php
        $hasInvoices = isset($pendingInvoicesList) && $pendingInvoicesList->isNotEmpty();
        $hasReqs     = isset($pendingReqList) && $pendingReqList->isNotEmpty();
    @endphp

    @if($hasInvoices || $hasReqs)
    <div class="grid grid-cols-1 {{ $hasInvoices && $hasReqs ? 'xl:grid-cols-2' : '' }} gap-4">

        {{-- Facturas por cobrar --}}
        @can('view finance summary')
        @if($hasInvoices)
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <p class="text-sm font-semibold text-slate-800">Facturas por cobrar</p>
                    @if($overdueInvoices > 0)
                    <p class="text-xs text-orange-500 mt-0.5">
                        {{ $overdueInvoices }} vencida{{ $overdueInvoices !== 1 ? 's' : '' }}
                    </p>
                    @endif
                </div>
                <a wire:navigate href="{{ route('sales.invoices.index') }}"
                   class="text-xs font-medium text-indigo-600 hover:text-indigo-700 cursor-pointer">
                    Ver todas →
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 text-left">
                            <th class="pb-2.5 pr-4 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Folio</th>
                            <th class="pb-2.5 pr-4 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Cliente</th>
                            <th class="pb-2.5 pr-4 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Vence</th>
                            <th class="pb-2.5 pr-4 text-[11px] font-semibold uppercase tracking-wider text-slate-400 text-right">Saldo</th>
                            <th class="pb-2.5 w-8"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($pendingInvoicesList as $inv)
                        @php
                            $balance   = $inv->total - $inv->paid_amount;
                            $isOverdue = $inv->due_at && $inv->due_at->isPast();
                        @endphp
                        <tr class="hover:bg-slate-50 transition-colors duration-150 group">
                            <td class="py-2.5 pr-4 font-mono font-semibold text-slate-700">
                                {{ $inv->folio }}
                            </td>
                            <td class="py-2.5 pr-4 text-slate-600 max-w-[10rem] truncate">
                                {{ $inv->customer?->name ?? '—' }}
                            </td>
                            <td class="py-2.5 pr-4">
                                @if($isOverdue)
                                    <span class="inline-flex items-center gap-1.5 text-red-600 font-semibold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-400 flex-shrink-0"></span>
                                        {{ $inv->due_at?->format('d/m/Y') }}
                                    </span>
                                @else
                                    <span class="text-slate-400">{{ $inv->due_at?->format('d/m/Y') ?? '—' }}</span>
                                @endif
                            </td>
                            <td class="py-2.5 pr-4 text-right font-semibold text-slate-800 tabular-nums">
                                ${{ number_format($balance, 0) }}
                            </td>
                            <td class="py-2.5 text-right">
                                <a wire:navigate href="{{ route('sales.invoices.show', $inv) }}"
                                   class="font-medium text-indigo-600 hover:text-indigo-700 cursor-pointer
                                          opacity-0 group-hover:opacity-100 transition-opacity duration-150">
                                    Ver →
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
        @endcan

        {{-- Requisiciones pendientes --}}
        @can('view purchases summary')
        @if($hasReqs)
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <p class="text-sm font-semibold text-slate-800">Requisiciones pendientes</p>
                    <p class="text-xs text-slate-400 mt-0.5">Requieren atención</p>
                </div>
                <a wire:navigate href="{{ route('purchases.index') }}"
                   class="text-xs font-medium text-indigo-600 hover:text-indigo-700 cursor-pointer">
                    Ver todas →
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 text-left">
                            <th class="pb-2.5 pr-4 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Folio</th>
                            <th class="pb-2.5 pr-4 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Solicitante</th>
                            <th class="pb-2.5 pr-4 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Actualizado</th>
                            <th class="pb-2.5 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Estado</th>
                            <th class="pb-2.5 w-8"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($pendingReqList as $req)
                        <tr class="hover:bg-slate-50 transition-colors duration-150 group">
                            <td class="py-2.5 pr-4 font-mono font-semibold text-slate-700">
                                {{ $req->folio }}
                            </td>
                            <td class="py-2.5 pr-4 text-slate-600 max-w-[8rem] truncate">
                                {{ $req->requestedBy?->name ?? '—' }}
                            </td>
                            <td class="py-2.5 pr-4 text-slate-400">
                                {{ $req->updated_at->diffForHumans() }}
                            </td>
                            <td class="py-2.5">
                                @php $rc = \App\Models\PurchaseRequisition::STATUS_COLORS[$req->status] ?? 'bg-gray-100 text-gray-600'; @endphp
                                <span class="inline-block px-2 py-0.5 rounded-full font-medium {{ $rc }}">
                                    {{ \App\Models\PurchaseRequisition::STATUS[$req->status] ?? $req->status }}
                                </span>
                            </td>
                            <td class="py-2.5 text-right">
                                <a wire:navigate href="{{ route('purchases.requisitions.show', $req) }}"
                                   class="font-medium text-indigo-600 hover:text-indigo-700 cursor-pointer
                                          opacity-0 group-hover:opacity-100 transition-opacity duration-150">
                                    Ver →
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
        @endcan

    </div>
    @endif

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
@endpush

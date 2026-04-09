<div class="space-y-6">

    {{-- Bienvenida --}}
    <div class="bg-gradient-to-r from-indigo-600 to-indigo-800 rounded-2xl p-6 text-white shadow-lg">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold">¡Bienvenido, {{ auth()->user()->name }}!</h1>
                <p class="text-indigo-200 text-sm mt-0.5">{{ now()->translatedFormat('l, d \d\e F \d\e Y') }}</p>
            </div>
            <div class="flex gap-2 flex-wrap items-center">
                @if($branches->count() > 1)
                <select wire:model.live="branchId"
                        class="text-sm bg-white/15 border border-white/20 text-white rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-white/40 [&>option]:text-gray-800">
                    <option value="">Todas las sucursales</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
                @endif
                @can('create sales')
                <a wire:navigate href="{{ route('sales.quotations.create') }}"
                   class="inline-flex items-center gap-1.5 bg-white/15 hover:bg-white/25 text-white text-sm font-medium px-3 py-1.5 rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Nueva cotización
                </a>
                @endcan
                @can('create purchases')
                <a wire:navigate href="{{ route('purchases.requisitions.create') }}"
                   class="inline-flex items-center gap-1.5 bg-white/15 hover:bg-white/25 text-white text-sm font-medium px-3 py-1.5 rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Nueva requisición
                </a>
                @endcan
            </div>
        </div>
    </div>

    {{-- KPIs fila 1: Ventas --}}
    @can('view sales summary')
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- Ventas del mes --}}
        <div class="bg-white rounded-xl border border-gray-200 p-4 flex flex-col gap-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Ventas del mes</span>
                <div class="w-8 h-8 bg-indigo-50 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900">${{ number_format($salesThisMonth, 0) }}</p>
            @if($salesGrowth !== null)
                <span class="text-xs {{ $salesGrowth >= 0 ? 'text-emerald-600 bg-emerald-50' : 'text-red-600 bg-red-50' }} px-2 py-0.5 rounded-full self-start font-medium">
                    {{ $salesGrowth >= 0 ? '+' : '' }}{{ $salesGrowth }}% vs mes anterior
                </span>
            @else
                <span class="text-xs text-gray-400">Sin datos previos</span>
            @endif
        </div>

        {{-- Órdenes activas --}}
        <div class="bg-white rounded-xl border border-gray-200 p-4 flex flex-col gap-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Órdenes activas</span>
                <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900">{{ $activeOrders }}</p>
            <a wire:navigate href="{{ route('sales.orders.index') }}" class="text-xs text-blue-600 hover:underline self-start">Ver órdenes →</a>
        </div>

        {{-- Cotizaciones enviadas --}}
        <div class="bg-white rounded-xl border border-gray-200 p-4 flex flex-col gap-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Cotizaciones enviadas</span>
                <div class="w-8 h-8 bg-amber-50 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900">{{ $pendingQuotations }}</p>
            <a wire:navigate href="{{ route('sales.index') }}" class="text-xs text-amber-600 hover:underline self-start">Ver cotizaciones →</a>
        </div>

        {{-- Clientes nuevos --}}
        <div class="bg-white rounded-xl border border-gray-200 p-4 flex flex-col gap-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Clientes nuevos</span>
                <div class="w-8 h-8 bg-emerald-50 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900">{{ $newCustomersThisMonth }}</p>
            <span class="text-xs text-gray-400">Este mes</span>
        </div>
    </div>
    @endcan

    {{-- KPIs fila 2: Compras + Inventario + Cobranza --}}
    @if(auth()->user()->canAny(['view purchases summary', 'view inventory summary', 'view finance summary']))
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

        @can('view purchases summary')
        {{-- Requisiciones en proceso --}}
        <div class="bg-white rounded-xl border border-gray-200 p-4 flex flex-col gap-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Requisiciones en proceso</span>
                <div class="w-8 h-8 bg-violet-50 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900">{{ $pendingRequisitions }}</p>
            <a wire:navigate href="{{ route('purchases.index') }}" class="text-xs text-violet-600 hover:underline self-start">Ver requisiciones →</a>
        </div>

        {{-- Órdenes de compra abiertas --}}
        <div class="bg-white rounded-xl border border-gray-200 p-4 flex flex-col gap-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Órdenes compra abiertas</span>
                <div class="w-8 h-8 bg-teal-50 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900">{{ $openPurchaseOrders }}</p>
            <a wire:navigate href="{{ route('purchases.orders.index') }}" class="text-xs text-teal-600 hover:underline self-start">Ver órdenes →</a>
        </div>
        @endcan

        @can('view inventory summary')
        {{-- Stock bajo mínimo --}}
        <div class="bg-white rounded-xl border {{ $lowStockProducts > 0 ? 'border-red-200 bg-red-50/30' : 'border-gray-200' }} p-4 flex flex-col gap-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium {{ $lowStockProducts > 0 ? 'text-red-500' : 'text-gray-500' }} uppercase tracking-wide">Productos bajo mínimo</span>
                <div class="w-8 h-8 {{ $lowStockProducts > 0 ? 'bg-red-100' : 'bg-gray-100' }} rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 {{ $lowStockProducts > 0 ? 'text-red-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold {{ $lowStockProducts > 0 ? 'text-red-700' : 'text-gray-900' }}">{{ $lowStockProducts }}</p>
            @if($lowStockProducts > 0)
                <a wire:navigate href="{{ route('inventory.general') }}" class="text-xs text-red-600 hover:underline self-start">Ver existencias →</a>
            @else
                <span class="text-xs text-gray-400">Todo en orden</span>
            @endif
        </div>
        @endcan

        @can('view finance summary')
        {{-- Facturas vencidas --}}
        <div class="bg-white rounded-xl border {{ $overdueInvoices > 0 ? 'border-orange-200 bg-orange-50/20' : 'border-gray-200' }} p-4 flex flex-col gap-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium {{ $overdueInvoices > 0 ? 'text-orange-500' : 'text-gray-500' }} uppercase tracking-wide">Facturas vencidas</span>
                <div class="w-8 h-8 {{ $overdueInvoices > 0 ? 'bg-orange-100' : 'bg-gray-100' }} rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 {{ $overdueInvoices > 0 ? 'text-orange-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold {{ $overdueInvoices > 0 ? 'text-orange-700' : 'text-gray-900' }}">{{ $overdueInvoices }}</p>
            @if($overdueInvoices > 0)
                <span class="text-xs text-orange-600 font-medium">${{ number_format($overdueTotal, 0) }} por cobrar</span>
            @else
                <span class="text-xs text-gray-400">Sin vencidas</span>
            @endif
        </div>
        @endcan
    </div>
    @endif

    {{-- Gráfico ventas + Últimas órdenes --}}
    @can('view sales summary')
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">

        {{-- Gráfico ventas 6 meses --}}
        <div class="lg:col-span-3 bg-white rounded-xl border border-gray-200 p-5"
             x-data="{
                chart: null,
                init() {
                    const labels = {{ Js::from($salesChart->pluck('label')) }};
                    const data   = {{ Js::from($salesChart->pluck('value')) }};
                    const ctx = this.$refs.canvas.getContext('2d');
                    this.chart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels,
                            datasets: [{
                                label: 'Ventas',
                                data,
                                backgroundColor: 'rgba(99,102,241,0.15)',
                                borderColor: '#6366f1',
                                borderWidth: 2,
                                borderRadius: 6,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: v => '$' + Intl.NumberFormat('es-MX', { notation: 'compact' }).format(v)
                                    },
                                    grid: { color: 'rgba(0,0,0,0.04)' }
                                },
                                x: { grid: { display: false } }
                            }
                        }
                    });
                }
             }">
            <h3 class="text-sm font-semibold text-gray-800 mb-4">Ventas últimos 6 meses</h3>
            <div class="h-48">
                <canvas x-ref="canvas"></canvas>
            </div>
        </div>

        {{-- Últimas órdenes de venta --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-5 flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-800">Últimas órdenes de venta</h3>
                <a wire:navigate href="{{ route('sales.orders.index') }}" class="text-xs text-indigo-600 hover:underline">Ver todas</a>
            </div>
            <div class="flex-1 space-y-2">
                @forelse($recentOrders as $order)
                    <a wire:navigate href="{{ route('sales.orders.show', $order) }}"
                       class="flex items-center justify-between gap-3 p-2.5 rounded-lg hover:bg-gray-50 transition group">
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-gray-800 truncate">{{ $order->customer?->name ?? '—' }}</p>
                            <p class="text-[10px] text-gray-400">{{ $order->folio }} · {{ $order->created_at->diffForHumans() }}</p>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <span class="text-xs font-semibold text-gray-700">${{ number_format($order->total, 0) }}</span>
                            @php $colors = \App\Models\SaleOrder::STATUS_COLORS[$order->status] ?? 'bg-gray-100 text-gray-600'; @endphp
                            <span class="text-[10px] px-1.5 py-0.5 rounded-full font-medium {{ $colors }}">
                                {{ \App\Models\SaleOrder::STATUS[$order->status] ?? $order->status }}
                            </span>
                        </div>
                    </a>
                @empty
                    <p class="text-sm text-gray-400 text-center py-6">Sin órdenes aún</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Top 5 productos del mes --}}
    @if($topProducts->isNotEmpty())
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="text-sm font-semibold text-gray-800 mb-4">Top 5 productos más vendidos este mes</h3>
        <div class="space-y-3">
            @foreach($topProducts as $i => $item)
            @php
                $maxQty = $topProducts->max('total_qty');
                $pct    = $maxQty > 0 ? ($item->total_qty / $maxQty) * 100 : 0;
            @endphp
            <div class="flex items-center gap-3">
                <span class="w-5 h-5 rounded-full bg-indigo-50 text-indigo-600 text-[10px] font-bold flex items-center justify-center flex-shrink-0">
                    {{ $i + 1 }}
                </span>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-2 mb-1">
                        <p class="text-xs font-medium text-gray-800 truncate">{{ $item->product?->name ?? '—' }}</p>
                        <span class="text-xs text-gray-500 flex-shrink-0">{{ number_format($item->total_qty, 0) }} uds · ${{ number_format($item->total_revenue, 0) }}</span>
                    </div>
                    <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full bg-indigo-400 rounded-full transition-all" style="width: {{ $pct }}%"></div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
    @endcan

    {{-- Facturas pendientes de cobro --}}
    @can('view finance summary')
    @if($pendingInvoicesList->isNotEmpty())
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-gray-800">Facturas pendientes de cobro</h3>
            <a wire:navigate href="{{ route('sales.invoices.index') }}" class="text-xs text-indigo-600 hover:underline">Ver todas</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="text-left text-gray-400 border-b border-gray-100">
                        <th class="pb-2 font-medium">Folio</th>
                        <th class="pb-2 font-medium">Cliente</th>
                        <th class="pb-2 font-medium">Vencimiento</th>
                        <th class="pb-2 font-medium text-right">Saldo</th>
                        <th class="pb-2 font-medium">Estado</th>
                        <th class="pb-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($pendingInvoicesList as $inv)
                    @php
                        $balance   = $inv->total - $inv->paid_amount;
                        $isOverdue = $inv->due_at && $inv->due_at->isPast();
                    @endphp
                    <tr class="hover:bg-gray-50 transition">
                        <td class="py-2.5 font-mono font-medium text-gray-700">{{ $inv->folio }}</td>
                        <td class="py-2.5 text-gray-600">{{ $inv->customer?->name ?? '—' }}</td>
                        <td class="py-2.5 {{ $isOverdue ? 'text-red-600 font-medium' : 'text-gray-400' }}">
                            {{ $inv->due_at ? $inv->due_at->format('d/m/Y') : '—' }}
                            @if($isOverdue)
                                <span class="ml-1 text-[10px] bg-red-100 text-red-600 px-1 rounded">vencida</span>
                            @endif
                        </td>
                        <td class="py-2.5 text-right font-semibold text-gray-700">${{ number_format($balance, 0) }}</td>
                        <td class="py-2.5">
                            @php $invColors = \App\Models\SaleInvoice::STATUS_COLORS[$inv->status] ?? 'bg-gray-100 text-gray-600'; @endphp
                            <span class="px-2 py-0.5 rounded-full font-medium {{ $invColors }}">
                                {{ \App\Models\SaleInvoice::STATUS[$inv->status] ?? $inv->status }}
                            </span>
                        </td>
                        <td class="py-2.5 text-right">
                            <a wire:navigate href="{{ route('sales.invoices.show', $inv) }}" class="text-indigo-600 hover:underline">Ver →</a>
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
    @if($pendingReqList->isNotEmpty())
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-gray-800">Requisiciones que requieren atención</h3>
            <a wire:navigate href="{{ route('purchases.index') }}" class="text-xs text-indigo-600 hover:underline">Ver todas</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="text-left text-gray-400 border-b border-gray-100">
                        <th class="pb-2 font-medium">Folio</th>
                        <th class="pb-2 font-medium">Solicitante</th>
                        <th class="pb-2 font-medium">Actualizado</th>
                        <th class="pb-2 font-medium">Estado</th>
                        <th class="pb-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($pendingReqList as $req)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="py-2.5 font-mono font-medium text-gray-700">{{ $req->folio }}</td>
                        <td class="py-2.5 text-gray-600">{{ $req->requestedBy?->name ?? '—' }}</td>
                        <td class="py-2.5 text-gray-400">{{ $req->updated_at->diffForHumans() }}</td>
                        <td class="py-2.5">
                            @php $reqColors = \App\Models\PurchaseRequisition::STATUS_COLORS[$req->status] ?? 'bg-gray-100 text-gray-600'; @endphp
                            <span class="px-2 py-0.5 rounded-full font-medium {{ $reqColors }}">
                                {{ \App\Models\PurchaseRequisition::STATUS[$req->status] ?? $req->status }}
                            </span>
                        </td>
                        <td class="py-2.5 text-right">
                            <a wire:navigate href="{{ route('purchases.requisitions.show', $req) }}" class="text-indigo-600 hover:underline">Ver →</a>
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
@endpush

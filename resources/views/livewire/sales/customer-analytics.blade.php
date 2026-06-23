<div>
    <x-page-header title="Analytics de Clientes" description="Rentabilidad, segmentación y valor de vida del cliente">
        <x-slot:actions>
            <a wire:navigate href="{{ route('sales.report') }}"
               class="px-3 py-2 text-sm border border-slate-200 text-slate-600 rounded-lg hover:bg-slate-50 transition">
                ← Ventas
            </a>
        </x-slot:actions>
    </x-page-header>

    <x-alert />

    {{-- Filtros ─────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-slate-200 p-4 mb-5 flex flex-wrap gap-3">
        <div>
            <label class="block text-[10px] text-slate-400 mb-0.5 uppercase">Desde</label>
            <input wire:model.live="filterFrom" type="date"
                   class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
        </div>
        <div>
            <label class="block text-[10px] text-slate-400 mb-0.5 uppercase">Hasta</label>
            <input wire:model.live="filterTo" type="date"
                   class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
        </div>
        <div>
            <label class="block text-[10px] text-slate-400 mb-0.5 uppercase">Buscar cliente</label>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Nombre..."
                   class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
        </div>
    </div>

    {{-- KPIs ─────────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-6">
        <div class="bg-white rounded-xl border border-slate-200 p-4 text-center">
            <p class="text-2xl font-bold text-slate-800">{{ $totalCustomers }}</p>
            <p class="text-[10px] text-slate-400 mt-0.5 uppercase">Clientes activos</p>
        </div>
        <div class="bg-white rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-center sm:col-span-2">
            <p class="text-2xl font-bold text-emerald-700">${{ number_format($totalRevenue, 0) }}</p>
            <p class="text-[10px] text-slate-400 mt-0.5 uppercase">Ingresos totales</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 text-center">
            <p class="text-2xl font-bold text-indigo-600">${{ number_format($avgTicket, 0) }}</p>
            <p class="text-[10px] text-slate-400 mt-0.5 uppercase">Ticket promedio</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 text-center">
            @if($paretoPct !== null)
            <p class="text-2xl font-bold text-amber-600">{{ $paretoPct }}%</p>
            <p class="text-[10px] text-slate-400 mt-0.5 uppercase">Top {{ $top20pct }} = X% ingresos</p>
            @else
            <p class="text-2xl font-bold text-slate-300">—</p>
            <p class="text-[10px] text-slate-400 mt-0.5 uppercase">Pareto</p>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

        {{-- Segmentación por recencia ───────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Segmentación de clientes</h3>
                <p class="text-[10px] text-slate-400 mt-0.5">Por recencia de última compra</p>
            </div>
            <div class="p-5 space-y-4">
                @foreach($bySegment as $seg)
                <div>
                    <div class="flex justify-between text-xs mb-1">
                        <div class="flex items-center gap-1.5">
                            <div class="w-2 h-2 rounded-full bg-{{ $seg['color'] }}-400 flex-shrink-0"></div>
                            <span class="text-slate-700 font-medium">{{ $seg['label'] }}</span>
                        </div>
                        <div class="flex gap-2">
                            <span class="text-slate-500">{{ $seg['count'] }} clientes</span>
                            <span class="font-bold text-slate-800">${{ number_format($seg['revenue'], 0) }}</span>
                        </div>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2">
                        <div class="bg-{{ $seg['color'] }}-400 h-2 rounded-full"
                             style="width: {{ $totalCustomers > 0 ? round($seg['count']/$totalCustomers*100) : 0 }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Nuevos clientes tendencia ──────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Nuevos clientes por mes</h3>
            </div>
            <div class="p-5">
                <div class="flex items-end gap-1 h-28">
                    @foreach($newCustomersTrend as $m)
                    <div class="flex-1 flex flex-col items-center gap-1">
                        <div class="w-full bg-indigo-400 rounded-t"
                             style="height: {{ $maxNewCustomers > 0 ? max(2, round($m['count']/$maxNewCustomers*100)) : 2 }}%"></div>
                        <span class="text-[8px] text-slate-400">{{ $m['label'] }}</span>
                    </div>
                    @endforeach
                </div>
                <p class="mt-3 text-xs text-slate-500">Total últimos 12 meses: <strong>{{ $newCustomersTrend->sum('count') }}</strong> nuevos clientes</p>
            </div>
        </div>

        {{-- Frecuencia de compra ───────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Frecuencia y LTV</h3>
            </div>
            <div class="p-5 space-y-3 text-sm">
                <div class="flex justify-between py-2 border-b border-slate-100">
                    <span class="text-slate-500">Facturas promedio/cliente</span>
                    <span class="font-bold text-slate-800">{{ number_format($avgFrequency, 1) }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-slate-100">
                    <span class="text-slate-500">Ticket promedio</span>
                    <span class="font-bold text-slate-800">${{ number_format($avgTicket, 0) }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-slate-100">
                    <span class="text-slate-500">LTV anual prom.</span>
                    <span class="font-bold text-indigo-700">${{ number_format($segments->avg('ltv_annual') ?? 0, 0) }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-slate-100">
                    <span class="text-slate-500">LTV max (top cliente)</span>
                    <span class="font-bold text-emerald-700">${{ number_format($segments->max('ltv_annual') ?? 0, 0) }}</span>
                </div>
                <div class="pt-1 text-[10px] text-slate-400">
                    LTV = ingresos en el período / meses activos × 12
                </div>
            </div>
        </div>

    </div>

    {{-- Tabla detalle de clientes ──────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
            <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Detalle de clientes — Rentabilidad y comportamiento</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="text-left px-5 py-2.5 text-xs font-semibold text-slate-400 uppercase">#</th>
                        <th class="text-left px-4 py-2.5 text-xs font-semibold text-slate-400 uppercase">Cliente</th>
                        <th class="text-right px-4 py-2.5 text-xs font-semibold text-slate-400 uppercase">Ingresos</th>
                        <th class="text-center px-4 py-2.5 text-xs font-semibold text-slate-400 uppercase hidden sm:table-cell">Facturas</th>
                        <th class="text-right px-4 py-2.5 text-xs font-semibold text-slate-400 uppercase hidden md:table-cell">Ticket prom.</th>
                        <th class="text-right px-4 py-2.5 text-xs font-semibold text-slate-400 uppercase hidden lg:table-cell">LTV anual</th>
                        <th class="text-center px-4 py-2.5 text-xs font-semibold text-slate-400 uppercase">% total</th>
                        <th class="text-center px-4 py-2.5 text-xs font-semibold text-slate-400 uppercase">Segmento</th>
                        <th class="text-center px-4 py-2.5 text-xs font-semibold text-slate-400 uppercase hidden xl:table-cell">Últ. compra</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($segments as $i => $c)
                    @php
                        $segColors = ['activo' => 'emerald', 'en_riesgo' => 'amber', 'inactivo' => 'orange', 'perdido' => 'red'];
                        $sc = $segColors[$c->segment] ?? 'slate';
                        $segLabel = ['activo' => 'Activo', 'en_riesgo' => 'En riesgo', 'inactivo' => 'Inactivo', 'perdido' => 'Perdido'][$c->segment] ?? $c->segment;
                    @endphp
                    <tr class="hover:bg-slate-50/50">
                        <td class="px-5 py-3 text-slate-400 text-xs">{{ $i + 1 }}</td>
                        <td class="px-4 py-3">
                            <span class="font-medium text-slate-800">{{ $c->name }}</span>
                        </td>
                        <td class="px-4 py-3 text-right font-bold text-slate-800">${{ number_format($c->revenue, 0) }}</td>
                        <td class="px-4 py-3 text-center text-slate-600 hidden sm:table-cell">{{ $c->invoices }}</td>
                        <td class="px-4 py-3 text-right text-slate-500 hidden md:table-cell">${{ number_format($c->avg_ticket, 0) }}</td>
                        <td class="px-4 py-3 text-right font-bold text-indigo-700 hidden lg:table-cell">${{ number_format($c->ltv_annual, 0) }}</td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex flex-col items-center gap-1">
                                <span class="text-xs font-bold text-slate-700">{{ number_format($c->revenue_pct, 1) }}%</span>
                                <div class="w-16 bg-slate-100 rounded-full h-1">
                                    <div class="bg-emerald-400 h-1 rounded-full" style="width: {{ min($c->revenue_pct * 2, 100) }}%"></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-[10px] px-2 py-0.5 rounded-full font-medium bg-{{ $sc }}-100 text-{{ $sc }}-700">
                                {{ $segLabel }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-xs text-slate-400 hidden xl:table-cell">
                            {{ \Carbon\Carbon::parse($c->last_invoice)->format('d/m/Y') }}
                            <br><span class="{{ $c->days_since > 90 ? 'text-red-400' : '' }}">{{ $c->days_since }}d atrás</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-5 py-12 text-center text-slate-400">Sin clientes con facturas en el período.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

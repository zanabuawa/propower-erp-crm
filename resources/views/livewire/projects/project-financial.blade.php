<div class="min-h-screen bg-slate-50/50 -m-4 lg:-m-6">
    {{-- ── STICKY HEADER ────────────────────────────────────────────────── --}}
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 max-w-full mx-auto">
            <div class="flex items-center gap-3 min-w-0">
                <a wire:navigate href="{{ route('projects.show', $project) }}" 
                   class="group flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-100 hover:shadow-sm transition-all duration-200">
                    <svg class="w-5 h-5 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl font-bold text-slate-800 truncate">Salud Financiera — {{ $project->name }}</h1>
                    <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">{{ $project->code }} &middot; Consolidado de Costos Reales</p>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-full mx-auto p-4 sm:p-6 lg:p-8 space-y-8">
        <x-alert />
        @cannot('view project financials')
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 flex items-start gap-4">
            <svg class="w-6 h-6 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            <div>
                <p class="text-sm font-bold text-amber-800">Acceso restringido</p>
                <p class="text-sm text-amber-700 mt-0.5">No tienes permiso para ver la información financiera de proyectos (ingresos, costos y márgenes).</p>
            </div>
        </div>
        @endcannot
        @can('view project financials')

        {{-- ── KPIs DE RENTABILIDAD ────────────────────────────────────────── --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white rounded-3xl border border-slate-200/60 p-6 shadow-sm">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Ingreso del Proyecto</p>
                @if($editingRevenue)
                    <form wire:submit.prevent="saveRevenue" class="flex gap-2 mt-1">
                        <input wire:model="revenueInput" type="number" step="0.01" min="0" 
                               class="w-full bg-slate-50 border-none rounded-xl px-3 py-2 text-sm font-black text-indigo-700 focus:ring-4 focus:ring-indigo-500/10 transition-all">
                        <button type="submit" class="px-3 py-2 bg-indigo-600 text-white rounded-xl text-xs font-black shadow-lg">✓</button>
                    </form>
                @else
                    <div class="flex items-end justify-between">
                        <div class="flex items-baseline gap-1.5">
                            <span class="text-xs font-bold text-slate-400">$</span>
                            <span class="text-3xl font-black text-emerald-600 tracking-tighter">{{ number_format($revenue, 2) }}</span>
                        </div>
                        <button wire:click="$set('editingRevenue', true)" class="p-2 text-slate-300 hover:text-indigo-600 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 11l6-6 3 3-6 6H9v-3z"/></svg>
                        </button>
                    </div>
                @endif
            </div>

            <div class="bg-white rounded-3xl border border-slate-200/60 p-6 shadow-sm">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Costo Total Real</p>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-xs font-bold text-slate-400">$</span>
                    <span class="text-3xl font-black text-rose-600 tracking-tighter">{{ number_format($totalCost, 2) }}</span>
                </div>
            </div>

            <div class="bg-white rounded-3xl border {{ $profit >= 0 ? 'border-emerald-100' : 'border-rose-100' }} p-6 shadow-sm">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Utilidad Bruta</p>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-xs font-bold text-slate-400">$</span>
                    <span class="text-3xl font-black {{ $profit >= 0 ? 'text-emerald-700' : 'text-rose-700' }} tracking-tighter">
                        {{ $profit >= 0 ? '+' : '' }}{{ number_format($profit, 2) }}
                    </span>
                </div>
            </div>

            <div class="bg-slate-900 rounded-3xl p-6 shadow-xl shadow-slate-200 relative overflow-hidden group">
                <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-white/10 rounded-full blur-2xl transition-transform group-hover:scale-150"></div>
                <div class="relative z-10">
                    <p class="text-[10px] font-black text-indigo-300 uppercase tracking-widest mb-1">Margen Comercial</p>
                    <div class="flex items-center gap-3">
                        <span class="text-3xl font-black text-white tracking-tighter">
                            {{ $marginPct !== null ? number_format($marginPct, 1).'%' : '—' }}
                        </span>
                        @if($marginPct !== null)
                            <div class="flex-1 bg-white/10 rounded-full h-1.5 overflow-hidden">
                                <div class="h-1.5 rounded-full {{ $marginPct >= 20 ? 'bg-emerald-400 shadow-[0_0_8px_rgba(52,211,153,0.5)]' : ($marginPct >= 10 ? 'bg-amber-400' : 'bg-rose-400') }}"
                                     style="width: {{ min(max(0, $marginPct), 100) }}%"></div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ── SECCIÓN: AVANCES Y EFICIENCIA ──────────────────────────────── --}}
        <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm p-6 lg:p-10">
            <div class="flex items-center gap-3 mb-8">
                <div class="w-1.5 h-6 rounded-full bg-indigo-500"></div>
                <h3 class="text-xs font-black text-slate-700 uppercase tracking-[0.2em]">Desempeño Operativo vs. Económico</h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-12">
                {{-- Físico --}}
                <div class="space-y-4">
                    <div class="flex justify-between items-end">
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Avance Físico</p>
                            <p class="text-2xl font-black text-slate-800">{{ number_format($physPct, 0) }}%</p>
                        </div>
                        <span class="text-[9px] font-bold text-slate-300 uppercase">Ejecución Tareas</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-3 shadow-inner">
                        <div class="bg-indigo-600 h-3 rounded-full transition-all duration-1000 shadow-[0_0_12px_rgba(79,70,229,0.3)]" style="width: {{ min($physPct, 100) }}%"></div>
                    </div>
                </div>

                {{-- Financiero --}}
                <div class="space-y-4">
                    <div class="flex justify-between items-end">
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Avance Financiero</p>
                            <p class="text-2xl font-black {{ $financPct > 100 ? 'text-rose-600' : 'text-slate-800' }}">{{ $financPct !== null ? number_format($financPct, 0).'%' : '—' }}</p>
                        </div>
                        <span class="text-[9px] font-bold text-slate-300 uppercase">Consumo Presupuesto</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-3 shadow-inner">
                        @if($financPct !== null)
                            <div class="h-3 rounded-full transition-all duration-1000 {{ $financPct > 100 ? 'bg-rose-500' : ($financPct > 90 ? 'bg-amber-400' : 'bg-slate-400') }}"
                                 style="width: {{ min($financPct, 100) }}%"></div>
                        @endif
                    </div>
                </div>

                {{-- Eficiencia --}}
                <div class="flex flex-col justify-center">
                    @if($progressGap !== null)
                        <div class="p-6 rounded-[2rem] {{ $progressGap >= 0 ? 'bg-emerald-50 border-emerald-100 text-emerald-700' : 'bg-rose-50 border-rose-100 text-rose-700' }} border flex items-center gap-6 shadow-sm group">
                            <div class="text-4xl transition-transform group-hover:scale-125">{{ $progressGap >= 0 ? '▲' : '▼' }}</div>
                            <div>
                                <p class="text-lg font-black tracking-tighter">{{ $progressGap >= 0 ? '+' : '' }}{{ number_format($progressGap, 1) }} pts</p>
                                <p class="text-[9px] font-black uppercase tracking-widest opacity-80">{{ $progressGap >= 0 ? 'Eficiencia Financiera' : 'Riesgo de Sobre-costo' }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            {{-- ── DESGLOSE DE COSTOS REALES ──────────────────────────────────── --}}
            <div class="xl:col-span-2 space-y-8">
                {{-- Mano de Obra --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/30 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center text-indigo-600 border border-indigo-50">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            </div>
                            <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest">Inversión en Capital Humano</h3>
                        </div>
                        <span class="text-lg font-black text-indigo-700">${{ number_format($realLaborCost, 2) }}</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left border-b border-slate-100">
                                    <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Colaborador / Especialidad</th>
                                    <th class="px-4 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Horas Reales</th>
                                    <th class="px-4 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right hidden sm:table-cell">Costo / Hora</th>
                                    <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Inversión</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @if($totalLaborReal > 0)
                                    @foreach($attendanceByEmployee as $row)
                                        <tr class="hover:bg-slate-50/50 transition-colors">
                                            <td class="px-8 py-4 font-bold text-slate-800">{{ $row['name'] }}</td>
                                            <td class="px-4 py-4 text-right">
                                                <span class="text-xs font-black text-slate-600">{{ number_format($row['hours'], 1) }}h</span>
                                            </td>
                                            <td class="px-4 py-4 text-right hidden sm:table-cell">
                                                <span class="text-[10px] font-bold text-slate-400">${{ number_format($row['rate'], 2) }}</span>
                                            </td>
                                            <td class="px-8 py-4 text-right font-black text-indigo-700">${{ number_format($row['cost'], 2) }}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    @foreach($laborRows as $row)
                                        <tr class="hover:bg-slate-50/50 transition-colors">
                                            <td class="px-8 py-4 font-bold text-slate-800">{{ $row['name'] }} <span class="text-[9px] font-black text-slate-300 ml-1 uppercase">({{ $row['role'] }})</span></td>
                                            <td class="px-4 py-4 text-right">
                                                <span class="text-xs font-black text-slate-500 italic">{{ number_format($row['hours'], 1) }}h (est)</span>
                                            </td>
                                            <td class="px-4 py-4 text-right hidden sm:table-cell">
                                                <span class="text-[10px] font-bold text-slate-400">${{ number_format($row['rate'], 2) }}</span>
                                            </td>
                                            <td class="px-8 py-4 text-right font-black text-slate-400">${{ number_format($row['cost'], 2) }}</td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Materiales --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/30 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center text-amber-600 border border-amber-50">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            </div>
                            <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest">Suministros & Materiales</h3>
                        </div>
                        <span class="text-lg font-black text-amber-700">${{ number_format($totalMaterials, 2) }}</span>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @forelse($materialRows as $row)
                            <div class="px-8 py-4 flex items-center justify-between hover:bg-slate-50/50 transition-all">
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-bold text-slate-800 truncate">{{ $row['name'] }}</p>
                                    <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest">
                                        {{ number_format($row['qty'], 2) }} {{ $row['unit'] }} &middot; ${{ number_format($row['unit_cost'], 2) }}/u
                                        <span class="ml-2 inline-flex px-1.5 py-0.5 rounded bg-slate-100 text-slate-500 font-black">{{ $row['status'] }}</span>
                                    </p>
                                </div>
                                <p class="text-sm font-black text-slate-700 ml-4">${{ number_format($row['cost'], 2) }}</p>
                            </div>
                        @empty
                            <p class="px-8 py-10 text-center text-[10px] font-bold text-slate-400 uppercase tracking-widest italic">Sin materiales asignados</p>
                        @endforelse
                    </div>
                </div>

                {{-- Gastos --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/30 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center text-rose-600 border border-rose-50">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            </div>
                            <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest">Gastos Operativos & Viáticos</h3>
                        </div>
                        <span class="text-lg font-black text-rose-700">${{ number_format($totalExpenses, 2) }}</span>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @forelse($expenseRows as $row)
                            <div class="px-8 py-4 flex items-center justify-between hover:bg-slate-50/50 transition-all">
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-bold text-slate-800 uppercase tracking-wider">{{ $row['category'] }}</p>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $row['count'] }} Transacciones registradas</p>
                                </div>
                                <p class="text-sm font-black text-slate-700 ml-4">${{ number_format($row['cost'], 2) }}</p>
                            </div>
                        @empty
                            <p class="px-8 py-10 text-center text-[10px] font-bold text-slate-400 uppercase tracking-widest italic">Sin gastos adicionales</p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- ── PANEL LATERAL DE ANALÍTICA ──────────────────────────────────── --}}
            <div class="space-y-8">
                {{-- Composición de Costos --}}
                <div class="bg-slate-900 rounded-[2.5rem] p-8 text-white shadow-xl shadow-slate-200">
                    <h3 class="text-xs font-black uppercase tracking-[0.2em] text-indigo-400 mb-6">Composición del Costo</h3>
                    <div class="space-y-6">
                        @php
                            $mix = [
                                ['label' => 'Personal', 'val' => $realLaborCost, 'col' => 'bg-indigo-500'],
                                ['label' => 'Suministros', 'val' => $totalMaterials, 'col' => 'bg-amber-400'],
                                ['label' => 'Operación', 'val' => $totalExpenses, 'col' => 'bg-rose-500'],
                            ];
                        @endphp
                        @foreach($mix as $m)
                            @php $pct = $totalCost > 0 ? ($m['val'] / $totalCost * 100) : 0; @endphp
                            <div class="space-y-2">
                                <div class="flex justify-between items-center text-[10px] font-black uppercase tracking-widest">
                                    <span class="text-slate-400">{{ $m['label'] }}</span>
                                    <span>{{ number_format($pct, 0) }}%</span>
                                </div>
                                <div class="w-full bg-white/10 rounded-full h-1.5 overflow-hidden">
                                    <div class="{{ $m['col'] }} h-1.5 rounded-full transition-all duration-1000" style="width: {{ $pct }}%"></div>
                                </div>
                                <p class="text-right text-xs font-black text-white/90">${{ number_format($m['val'], 0) }}</p>
                            </div>
                        @endforeach
                        <div class="pt-4 border-t border-white/10 flex justify-between items-center">
                            <span class="text-[10px] font-black uppercase tracking-widest text-indigo-400">Total Devengado</span>
                            <span class="text-xl font-black">${{ number_format($totalCost, 0) }}</span>
                        </div>
                    </div>
                </div>

                {{-- Desviación Presupuestal --}}
                @if($activeVersion)
                    <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                        <div class="p-6 lg:p-8 space-y-6">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                                <h3 class="text-[10px] font-black text-slate-800 uppercase tracking-[0.2em]">Presupuesto vs. Real</h3>
                                <span class="text-[9px] font-bold text-slate-400 uppercase">v{{ $activeVersion->version }}</span>
                            </div>
                            <div class="space-y-4">
                                @foreach($comparisonRows as $row)
                                    @if($row['budgeted'] > 0 || $row['executed'] > 0)
                                        <div class="space-y-1.5">
                                            <div class="flex justify-between items-center">
                                                <span class="text-[10px] font-bold text-slate-500 uppercase">{{ $row['label'] }}</span>
                                                @if($row['pct'] !== null)
                                                    <span class="text-[10px] font-black {{ $row['pct'] > 100 ? 'text-rose-600' : 'text-emerald-600' }}">{{ $row['pct'] }}%</span>
                                                @endif
                                            </div>
                                            <div class="w-full bg-slate-100 rounded-full h-1 overflow-hidden">
                                                <div class="h-1 rounded-full transition-all duration-1000 {{ $row['pct'] > 100 ? 'bg-rose-500' : 'bg-indigo-500' }}"
                                                     style="width: {{ min($row['pct'] ?? 0, 100) }}%"></div>
                                            </div>
                                            <div class="flex justify-between text-[9px] font-black uppercase tracking-tighter">
                                                <span class="text-slate-400">Est: ${{ number_format($row['budgeted'], 0) }}</span>
                                                <span class="{{ $row['variance'] < 0 ? 'text-rose-500' : 'text-slate-600' }}">Real: ${{ number_format($row['executed'], 0) }}</span>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
        @endcan
</div>


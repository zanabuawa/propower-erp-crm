<div>
    <x-page-header title="Analytics de Proyectos" description="Rentabilidad, avance y eficiencia del portafolio">
        <x-slot:actions>
            <a wire:navigate href="{{ route('projects.index') }}"
               class="px-3 py-2 text-sm border border-slate-200 text-slate-600 rounded-lg hover:bg-slate-50 transition">
                ← Proyectos
            </a>
        </x-slot:actions>
    </x-page-header>

    <x-alert />

    {{-- Filtros ──────────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-slate-200 p-4 mb-5 flex flex-wrap gap-3">
        <div>
            <label class="block text-[10px] text-slate-400 mb-0.5 uppercase">Estado</label>
            <select wire:model.live="filterStatus"
                    class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30 bg-white">
                <option value="">Todos</option>
                <option value="activo">Activo</option>
                <option value="pausado">Pausado</option>
                <option value="completado">Completado</option>
            </select>
        </div>
        <div>
            <label class="block text-[10px] text-slate-400 mb-0.5 uppercase">Tipo</label>
            <select wire:model.live="filterType"
                    class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30 bg-white">
                <option value="">Todos</option>
                <option value="obra">Obra</option>
                <option value="mantenimiento">Mantenimiento</option>
                <option value="instalacion">Instalación</option>
                <option value="servicio">Servicio</option>
                <option value="consultoria">Consultoría</option>
                <option value="otro">Otro</option>
            </select>
        </div>
        <div>
            <label class="block text-[10px] text-slate-400 mb-0.5 uppercase">Inicio desde</label>
            <input wire:model.live="filterFrom" type="date"
                   class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
        </div>
        <div>
            <label class="block text-[10px] text-slate-400 mb-0.5 uppercase">Inicio hasta</label>
            <input wire:model.live="filterTo" type="date"
                   class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
        </div>
        <div class="ml-auto flex items-end">
            <span class="text-xs text-slate-400">{{ $kpis['total'] }} proyecto(s) en el análisis</span>
        </div>
    </div>

    {{-- KPIs globales ────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3 mb-6">
        <div class="bg-white rounded-xl border border-slate-200 p-4 text-center">
            <p class="text-2xl font-bold text-slate-800">{{ $kpis['total'] }}</p>
            <p class="text-[10px] text-slate-400 mt-0.5 uppercase">Proyectos</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 text-center">
            <p class="text-2xl font-bold text-indigo-600">{{ $kpis['with_revenue'] }}</p>
            <p class="text-[10px] text-slate-400 mt-0.5 uppercase">Con ingreso</p>
        </div>
        <div class="bg-white rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-center">
            <p class="text-2xl font-bold text-emerald-700">{{ $kpis['profitable'] }}</p>
            <p class="text-[10px] text-slate-400 mt-0.5 uppercase">Rentables</p>
        </div>
        <div class="bg-white rounded-xl border {{ $kpis['over_budget'] > 0 ? 'border-red-200 bg-red-50' : 'border-slate-200' }} p-4 text-center">
            <p class="text-2xl font-bold {{ $kpis['over_budget'] > 0 ? 'text-red-600' : 'text-slate-300' }}">{{ $kpis['over_budget'] }}</p>
            <p class="text-[10px] text-slate-400 mt-0.5 uppercase">Sobre presup.</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 text-center sm:col-span-2">
            <p class="text-xl font-bold text-emerald-600">${{ number_format($kpis['total_revenue'], 0) }}</p>
            <p class="text-[10px] text-slate-400 mt-0.5 uppercase">Ingresos totales</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 text-center sm:col-span-2">
            <p class="text-xl font-bold {{ $kpis['total_profit'] >= 0 ? 'text-emerald-700' : 'text-red-600' }}">
                {{ $kpis['total_profit'] >= 0 ? '+' : '' }}${{ number_format($kpis['total_profit'], 0) }}
            </p>
            <p class="text-[10px] text-slate-400 mt-0.5 uppercase">Utilidad total ·
                @if($kpis['avg_margin'] !== null)
                <span class="{{ $kpis['avg_margin'] >= 15 ? 'text-emerald-600' : ($kpis['avg_margin'] >= 5 ? 'text-amber-600' : 'text-red-500') }} font-bold">
                    {{ number_format($kpis['avg_margin'], 1) }}% prom.
                </span>
                @endif
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

        {{-- Rentabilidad por cliente ────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Por cliente</h3>
            </div>
            @php $maxClientRevenue = $byClient->max('revenue') ?: 1; @endphp
            <div class="divide-y divide-slate-100">
                @forelse($byClient as $row)
                <div class="px-5 py-3">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm font-medium text-slate-800 truncate pr-2">{{ $row['customer'] }}</span>
                        <div class="text-right flex-shrink-0">
                            @if($row['margin'] !== null)
                            <span class="text-xs font-bold {{ $row['margin'] >= 15 ? 'text-emerald-600' : ($row['margin'] >= 5 ? 'text-amber-600' : 'text-red-500') }}">
                                {{ number_format($row['margin'], 1) }}%
                            </span>
                            @else
                            <span class="text-xs text-slate-300">—</span>
                            @endif
                        </div>
                    </div>
                    <div class="flex gap-1 text-[10px] text-slate-400 mb-1.5">
                        <span>{{ $row['projects'] }} proyecto(s)</span>
                        <span>·</span>
                        <span class="text-emerald-600">${{ number_format($row['revenue'], 0) }}</span>
                        <span>·</span>
                        <span class="text-slate-500">costo ${{ number_format($row['cost'], 0) }}</span>
                    </div>
                    @if($row['revenue'] > 0)
                    <div class="w-full bg-slate-100 rounded-full h-1.5">
                        <div class="h-1.5 rounded-full {{ $row['margin'] >= 15 ? 'bg-emerald-400' : ($row['margin'] >= 5 ? 'bg-amber-400' : 'bg-red-400') }}"
                             style="width: {{ round($row['revenue'] / $maxClientRevenue * 100) }}%"></div>
                    </div>
                    @endif
                </div>
                @empty
                <p class="px-5 py-8 text-sm text-slate-400 text-center">Sin datos de clientes</p>
                @endforelse
            </div>
        </div>

        {{-- Rentabilidad por tipo ───────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Por tipo de servicio</h3>
            </div>
            @php $maxTypeRevenue = $byType->max('revenue') ?: 1; @endphp
            <div class="divide-y divide-slate-100">
                @forelse($byType as $row)
                <div class="px-5 py-3">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm font-medium text-slate-800">{{ $row['type'] }}</span>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] text-slate-400">{{ $row['projects'] }} proy.</span>
                            @if($row['margin'] !== null)
                            <span class="text-xs font-bold {{ $row['margin'] >= 15 ? 'text-emerald-600' : ($row['margin'] >= 5 ? 'text-amber-600' : 'text-red-500') }}">
                                {{ number_format($row['margin'], 1) }}%
                            </span>
                            @else
                            <span class="text-xs text-slate-300">—</span>
                            @endif
                        </div>
                    </div>
                    <div class="flex gap-3 text-[10px] mb-1.5">
                        <span class="text-emerald-600">Ing ${{ number_format($row['revenue'], 0) }}</span>
                        <span class="text-rose-500">Cto ${{ number_format($row['cost'], 0) }}</span>
                        <span class="{{ $row['profit'] >= 0 ? 'text-emerald-700' : 'text-red-600' }} font-bold">
                            {{ $row['profit'] >= 0 ? '+' : '' }}${{ number_format($row['profit'], 0) }}
                        </span>
                    </div>
                    @if($row['revenue'] > 0)
                    <div class="w-full bg-slate-100 rounded-full h-1.5">
                        <div class="h-1.5 rounded-full {{ $row['margin'] >= 15 ? 'bg-emerald-400' : ($row['margin'] >= 5 ? 'bg-amber-400' : 'bg-red-400') }}"
                             style="width: {{ round($row['revenue'] / $maxTypeRevenue * 100) }}%"></div>
                    </div>
                    @endif
                </div>
                @empty
                <p class="px-5 py-8 text-sm text-slate-400 text-center">Sin datos</p>
                @endforelse
            </div>
        </div>

        {{-- Top rentables + peores ─────────────────────────────────────── --}}
        <div class="space-y-4">
            <div class="bg-white rounded-xl border border-emerald-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-emerald-100 bg-emerald-50/50">
                    <h3 class="text-sm font-bold text-emerald-700 uppercase tracking-wider">Top rentables</h3>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse($topBest as $p)
                    <div class="px-5 py-2.5 flex items-center justify-between">
                        <div class="min-w-0 pr-2">
                            <a wire:navigate href="{{ route('projects.financial', $p['id']) }}"
                               class="text-sm font-medium text-slate-800 hover:text-indigo-600 truncate block">{{ $p['name'] }}</a>
                            <p class="text-[10px] text-slate-400 truncate">{{ $p['customer'] }}</p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <p class="text-sm font-bold text-emerald-600">{{ number_format($p['margin'], 1) }}%</p>
                            <p class="text-[10px] text-emerald-700">+${{ number_format($p['profit'], 0) }}</p>
                        </div>
                    </div>
                    @empty
                    <p class="px-5 py-6 text-sm text-slate-400 text-center">Sin proyectos con ingreso</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-white rounded-xl border border-red-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-red-100 bg-red-50/50">
                    <h3 class="text-sm font-bold text-red-600 uppercase tracking-wider">Menor margen</h3>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse($topWorst as $p)
                    <div class="px-5 py-2.5 flex items-center justify-between">
                        <div class="min-w-0 pr-2">
                            <a wire:navigate href="{{ route('projects.financial', $p['id']) }}"
                               class="text-sm font-medium text-slate-800 hover:text-indigo-600 truncate block">{{ $p['name'] }}</a>
                            <p class="text-[10px] text-slate-400 truncate">{{ $p['customer'] }}</p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <p class="text-sm font-bold {{ $p['margin'] < 0 ? 'text-red-600' : 'text-amber-600' }}">{{ number_format($p['margin'], 1) }}%</p>
                            <p class="text-[10px] {{ $p['profit'] < 0 ? 'text-red-500' : 'text-slate-400' }}">{{ $p['profit'] >= 0 ? '+' : '' }}${{ number_format($p['profit'], 0) }}</p>
                        </div>
                    </div>
                    @empty
                    <p class="px-5 py-6 text-sm text-slate-400 text-center">Sin proyectos con ingreso</p>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

    {{-- Tabla detalle de proyectos ───────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
            <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Detalle por proyecto — Avance físico vs financiero · Indicadores</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/30">
                        <th class="text-left px-5 py-2.5 text-xs font-semibold text-slate-400 uppercase whitespace-nowrap">Proyecto</th>
                        <th class="text-left px-4 py-2.5 text-xs font-semibold text-slate-400 uppercase whitespace-nowrap hidden md:table-cell">Cliente</th>
                        <th class="text-center px-4 py-2.5 text-xs font-semibold text-slate-400 uppercase whitespace-nowrap">Físico %</th>
                        <th class="text-center px-4 py-2.5 text-xs font-semibold text-slate-400 uppercase whitespace-nowrap">Financiero %</th>
                        <th class="text-center px-4 py-2.5 text-xs font-semibold text-slate-400 uppercase whitespace-nowrap hidden lg:table-cell">Brecha</th>
                        <th class="text-right px-4 py-2.5 text-xs font-semibold text-slate-400 uppercase whitespace-nowrap">Costo real</th>
                        <th class="text-right px-4 py-2.5 text-xs font-semibold text-slate-400 uppercase whitespace-nowrap hidden lg:table-cell">Ingreso</th>
                        <th class="text-center px-4 py-2.5 text-xs font-semibold text-slate-400 uppercase whitespace-nowrap">Margen</th>
                        <th class="text-center px-4 py-2.5 text-xs font-semibold text-slate-400 uppercase whitespace-nowrap hidden xl:table-cell">Efic. RRHH</th>
                        <th class="text-center px-4 py-2.5 text-xs font-semibold text-slate-400 uppercase whitespace-nowrap">Desviación</th>
                        <th class="px-4 py-2.5 hidden sm:table-cell"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($projects as $p)
                    @php
                        $budgetDev = $p['budget_pct'] !== null ? $p['budget_pct'] - 100 : null;
                        $gapColor  = $p['gap'] === null ? '' : ($p['gap'] >= 0 ? 'text-emerald-600' : 'text-red-500');
                        $marginColor = $p['margin'] === null ? 'text-slate-300' : ($p['margin'] >= 15 ? 'text-emerald-600' : ($p['margin'] >= 5 ? 'text-amber-600' : 'text-red-500'));
                        $devColor  = $budgetDev === null ? 'text-slate-300' : ($budgetDev <= 0 ? 'text-emerald-600' : ($budgetDev <= 10 ? 'text-amber-600' : 'text-red-500'));
                        $effColor  = $p['efficiency'] === null ? 'text-slate-300' : ($p['efficiency'] <= 100 ? 'text-emerald-600' : ($p['efficiency'] <= 115 ? 'text-amber-600' : 'text-red-500'));
                    @endphp
                    <tr class="hover:bg-slate-50/50">
                        <td class="px-5 py-3">
                            <a wire:navigate href="{{ route('projects.show', $p['id']) }}"
                               class="font-medium text-slate-800 hover:text-indigo-600 block">{{ $p['name'] }}</a>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <span class="text-[10px] text-slate-400">{{ $p['code'] }}</span>
                                <span class="text-[9px] px-1 py-0.5 rounded font-medium
                                    {{ $p['status'] === 'activo' ? 'bg-emerald-100 text-emerald-700' : ($p['status'] === 'completado' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-500') }}">
                                    {{ $p['status'] }}
                                </span>
                                <span class="text-[9px] px-1 py-0.5 rounded bg-slate-100 text-slate-500 font-medium">{{ $p['type'] }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-slate-600 hidden md:table-cell text-xs">{{ $p['customer'] }}</td>

                        {{-- Avance físico --}}
                        <td class="px-4 py-3 text-center">
                            <div class="flex flex-col items-center gap-1">
                                <span class="text-sm font-bold text-indigo-600">{{ number_format($p['phys_pct'], 0) }}%</span>
                                <div class="w-16 bg-slate-100 rounded-full h-1.5">
                                    <div class="bg-indigo-400 h-1.5 rounded-full" style="width: {{ min($p['phys_pct'], 100) }}%"></div>
                                </div>
                            </div>
                        </td>

                        {{-- Avance financiero --}}
                        <td class="px-4 py-3 text-center">
                            @if($p['budget_pct'] !== null)
                            <div class="flex flex-col items-center gap-1">
                                <span class="text-sm font-bold {{ $p['budget_pct'] > 100 ? 'text-red-600' : ($p['budget_pct'] > 90 ? 'text-amber-600' : 'text-slate-700') }}">
                                    {{ number_format($p['budget_pct'], 0) }}%
                                </span>
                                <div class="w-16 bg-slate-100 rounded-full h-1.5">
                                    <div class="h-1.5 rounded-full {{ $p['budget_pct'] > 100 ? 'bg-red-400' : ($p['budget_pct'] > 90 ? 'bg-amber-400' : 'bg-slate-400') }}"
                                         style="width: {{ min($p['budget_pct'], 100) }}%"></div>
                                </div>
                            </div>
                            @else
                            <span class="text-xs text-slate-300">Sin presup.</span>
                            @endif
                        </td>

                        {{-- Brecha físico - financiero --}}
                        <td class="px-4 py-3 text-center hidden lg:table-cell">
                            @if($p['gap'] !== null)
                            <div class="inline-flex items-center gap-0.5 text-xs font-bold {{ $gapColor }}">
                                {{ $p['gap'] >= 0 ? '▲' : '▼' }}{{ number_format(abs($p['gap']), 1) }}pp
                            </div>
                            <p class="text-[10px] text-slate-400 mt-0.5">{{ $p['gap'] >= 0 ? 'adelante' : 'atrás' }}</p>
                            @else
                            <span class="text-xs text-slate-300">—</span>
                            @endif
                        </td>

                        {{-- Costo real --}}
                        <td class="px-4 py-3 text-right font-bold text-slate-800">${{ number_format($p['total_cost'], 0) }}</td>

                        {{-- Ingreso --}}
                        <td class="px-4 py-3 text-right hidden lg:table-cell text-sm text-emerald-700 font-medium">
                            {{ $p['revenue'] > 0 ? '$'.number_format($p['revenue'], 0) : '—' }}
                        </td>

                        {{-- Margen --}}
                        <td class="px-4 py-3 text-center">
                            <span class="text-sm font-bold {{ $marginColor }}">
                                {{ $p['margin'] !== null ? number_format($p['margin'], 1).'%' : '—' }}
                            </span>
                        </td>

                        {{-- Eficiencia RRHH --}}
                        <td class="px-4 py-3 text-center hidden xl:table-cell">
                            @if($p['efficiency'] !== null)
                            <span class="text-xs font-bold {{ $effColor }}">{{ number_format($p['efficiency'], 0) }}%</span>
                            <p class="text-[10px] text-slate-400">{{ number_format($p['h_real'], 1) }}/{{ number_format($p['h_assigned'], 1) }}h</p>
                            @else
                            <span class="text-xs text-slate-300">—</span>
                            @endif
                        </td>

                        {{-- Desviación presupuesto --}}
                        <td class="px-4 py-3 text-center">
                            @if($budgetDev !== null)
                            <span class="text-xs font-bold {{ $devColor }}">
                                {{ $budgetDev >= 0 ? '+' : '' }}{{ number_format($budgetDev, 1) }}%
                            </span>
                            @else
                            <span class="text-xs text-slate-300">—</span>
                            @endif
                        </td>

                        <td class="px-4 py-3 hidden sm:table-cell">
                            <a wire:navigate href="{{ route('projects.financial', $p['id']) }}"
                               class="text-[10px] text-indigo-500 hover:underline whitespace-nowrap">Financiero →</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="px-5 py-12 text-center text-slate-400 text-sm">
                            No hay proyectos en el período seleccionado.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($projects->count() > 0)
                <tfoot>
                    <tr class="bg-slate-50 border-t-2 border-slate-200 font-bold text-sm">
                        <td class="px-5 py-3 text-slate-700" colspan="2">TOTALES ({{ $projects->count() }} proyectos)</td>
                        <td colspan="2" class="px-4 py-3 text-center text-slate-500 text-xs">
                            Prom. físico {{ number_format($projects->avg('phys_pct'), 0) }}%
                        </td>
                        <td class="hidden lg:table-cell"></td>
                        <td class="px-4 py-3 text-right text-slate-800">${{ number_format($kpis['total_cost'], 0) }}</td>
                        <td class="px-4 py-3 text-right text-emerald-700 hidden lg:table-cell">${{ number_format($kpis['total_revenue'], 0) }}</td>
                        <td class="px-4 py-3 text-center {{ $kpis['avg_margin'] >= 15 ? 'text-emerald-600' : ($kpis['avg_margin'] >= 5 ? 'text-amber-600' : 'text-red-500') }}">
                            {{ $kpis['avg_margin'] !== null ? number_format($kpis['avg_margin'], 1).'%' : '—' }}
                        </td>
                        <td class="hidden xl:table-cell"></td>
                        <td colspan="2" class="px-4 py-3 text-center">
                            <span class="{{ $kpis['total_profit'] >= 0 ? 'text-emerald-700' : 'text-red-600' }}">
                                {{ $kpis['total_profit'] >= 0 ? '+' : '' }}${{ number_format($kpis['total_profit'], 0) }}
                            </span>
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- Leyenda de indicadores ───────────────────────────────────────────── --}}
    <div class="mt-5 bg-slate-50 rounded-xl border border-slate-200 px-5 py-4">
        <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">Guía de indicadores</h4>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs text-slate-500">
            <div>
                <span class="font-bold text-slate-700">Brecha físico/financiero</span><br>
                ▲ adelante = avance físico supera el gasto → eficiente.<br>
                ▼ atrás = se gasta más de lo que se avanza → riesgo.
            </div>
            <div>
                <span class="font-bold text-slate-700">Margen</span><br>
                Verde ≥ 15% · Ámbar 5–15% · Rojo &lt; 5%.<br>
                Requiere ingreso registrado en Financiero.
            </div>
            <div>
                <span class="font-bold text-slate-700">Eficiencia RRHH</span><br>
                (Horas reales asistencia / Horas planificadas) × 100.<br>
                Verde ≤ 100% · Ámbar 100–115% · Rojo &gt; 115%.
            </div>
            <div>
                <span class="font-bold text-slate-700">Desviación presupuesto</span><br>
                (Costo real / Presupuesto – 1) × 100.<br>
                Verde = dentro · Ámbar ≤ 10% excedido · Rojo &gt; 10%.
            </div>
        </div>
    </div>
</div>

<div>
    <x-page-header title="Indicadores de Capital Humano" description="Análisis detallado de plantilla, costos y desempeño">
        <x-slot:actions>
            <div class="flex items-center gap-3">
                <select wire:model.live="branchId" class="text-sm border-slate-200 rounded-lg focus:ring-indigo-500/30">
                    <option value="">Todas las sucursales</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                    @endforeach
                </select>
                <select wire:model.live="year" class="text-sm border-slate-200 rounded-lg focus:ring-indigo-500/30">
                    @foreach(range(now()->year, now()->year - 3) as $y)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endforeach
                </select>
            </div>
        </x-slot:actions>
    </x-page-header>

    {{-- Resumen Principal --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <p class="text-xs font-bold text-slate-500 uppercase mb-1">Plantilla Activa</p>
            <h3 class="text-3xl font-black text-indigo-600">{{ $activeCount }}</h3>
            <p class="text-xs text-slate-400 mt-1">{{ $inactiveCount }} inactivos</p>
        </div>
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <p class="text-xs font-bold text-slate-500 uppercase mb-1">Rotación Anual ({{ $year }})</p>
            <h3 class="text-3xl font-black {{ $turnoverRate > 15 ? 'text-red-600' : ($turnoverRate > 8 ? 'text-amber-500' : 'text-emerald-600') }}">
                {{ number_format($turnoverRate, 1) }}%
            </h3>
            <p class="text-xs text-slate-400 mt-1">{{ $terminatedThisYear }} bajas · ref &lt;8%</p>
        </div>
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <p class="text-xs font-bold text-slate-500 uppercase mb-1">Antigüedad Promedio</p>
            <h3 class="text-3xl font-black text-emerald-600">{{ number_format($avgSeniority, 1) }} <span class="text-sm font-normal text-slate-500">años</span></h3>
            <p class="text-xs text-slate-400 mt-1">Estabilidad laboral</p>
        </div>
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <p class="text-xs font-bold text-slate-500 uppercase mb-1">Promedio Desempeño</p>
            <h3 class="text-3xl font-black text-amber-500">{{ number_format($avgPerformance, 1) }}%</h3>
            <p class="text-xs text-slate-400 mt-1">Evaluaciones {{ $year }}</p>
        </div>
    </div>

    {{-- Puntualidad y Ausentismo --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm text-center">
            <p class="text-xs font-bold text-slate-400 uppercase mb-1">Puntualidad</p>
            @if($punctualityRate !== null)
            <p class="text-2xl font-black {{ $punctualityRate >= 90 ? 'text-green-600' : ($punctualityRate >= 75 ? 'text-amber-500' : 'text-red-600') }}">
                {{ $punctualityRate }}%
            </p>
            @else <p class="text-2xl font-black text-slate-300">—</p> @endif
            <p class="text-[10px] text-slate-400 mt-0.5">Últimos 30 días · ref ≥90%</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm text-center">
            <p class="text-xs font-bold text-slate-400 uppercase mb-1">Ausentismo</p>
            @if($absenteeismRate !== null)
            <p class="text-2xl font-black {{ $absenteeismRate <= 2 ? 'text-green-600' : ($absenteeismRate <= 5 ? 'text-amber-500' : 'text-red-600') }}">
                {{ $absenteeismRate }}%
            </p>
            @else <p class="text-2xl font-black text-slate-300">—</p> @endif
            <p class="text-[10px] text-slate-400 mt-0.5">Últimos 30 días · ref &lt;2%</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm text-center">
            <p class="text-xs font-bold text-slate-400 uppercase mb-1">Retardos</p>
            <p class="text-2xl font-black text-yellow-500">{{ $attendanceStats['late'] ?? 0 }}</p>
            <p class="text-[10px] text-slate-400 mt-0.5">Registros últimos 30 días</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm text-center">
            <p class="text-xs font-bold text-slate-400 uppercase mb-1">Costo Mensual</p>
            <p class="text-xl font-black text-slate-700">${{ number_format($monthlyCost/1000, 0) }}k</p>
            <p class="text-[10px] text-slate-400 mt-0.5">Salarios activos</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Costos por Departamento --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Costo Mensual Salarial por Departamento</h3>
                <span class="text-lg font-bold text-slate-800">$ {{ number_format($monthlyCost, 2) }}</span>
            </div>
            <div class="p-5">
                <div class="space-y-4">
                    @foreach($costsByDept as $dept)
                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="font-medium text-slate-600">{{ $dept['label'] }}</span>
                            <span class="font-bold text-slate-800">$ {{ number_format($dept['value'], 2) }}</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2">
                            <div class="bg-indigo-500 h-2 rounded-full" style="width: {{ $monthlyCost > 0 ? ($dept['value'] / $monthlyCost) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Asistencia --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Asistencia (Últimos 30 días)</h3>
            </div>
            <div class="p-5">
                <div class="space-y-4">
                    @php
                        $totalAtt = collect($attendanceStats)->sum();
                    @endphp
                    @foreach(['present' => ['Puntualidad', 'bg-green-500'], 'late' => ['Retardos', 'bg-yellow-500'], 'absent' => ['Faltas', 'bg-red-500']] as $key => $conf)
                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="font-medium text-slate-600">{{ $conf[0] }}</span>
                            <span class="font-bold text-slate-800">{{ $attendanceStats[$key] ?? 0 }}</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2">
                            <div class="{{ $conf[1] }} h-2 rounded-full" style="width: {{ $totalAtt > 0 ? (($attendanceStats[$key] ?? 0) / $totalAtt) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                <div class="mt-8 pt-6 border-t border-slate-100">
                    <h4 class="text-xs font-bold text-slate-500 uppercase mb-4">Distribución por Género</h4>
                    <div class="flex items-center gap-4">
                        @foreach(['masculino' => 'Hombres', 'femenino' => 'Mujeres', 'otro' => 'Otros'] as $g => $l)
                        <div class="flex-1 text-center">
                            <p class="text-lg font-bold text-slate-700">{{ $genderDist[$g] ?? 0 }}</p>
                            <p class="text-[10px] text-slate-400 uppercase">{{ $l }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tendencia de Rotación Mensual --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden mt-6">
        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
            <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Entradas vs Salidas — Últimos 12 meses</h3>
        </div>
        <div class="p-5">
            @php
                $maxVal = max(1, max(array_merge($turnoverTrend['hires'], $turnoverTrend['terms'])));
            @endphp
            <div class="overflow-x-auto">
                <div class="flex items-end gap-2 min-w-max" style="height:140px">
                    @foreach($turnoverTrend['labels'] as $i => $label)
                    <div class="flex flex-col items-center gap-1" style="width:56px">
                        <div class="flex items-end gap-0.5 w-full" style="height:120px">
                            <div class="flex-1 bg-green-400 rounded-t transition-all"
                                 style="height:{{ $turnoverTrend['hires'][$i] > 0 ? max(4, round(($turnoverTrend['hires'][$i]/$maxVal)*100)) : 2 }}%"
                                 title="Ingresos: {{ $turnoverTrend['hires'][$i] }}"></div>
                            <div class="flex-1 bg-red-400 rounded-t transition-all"
                                 style="height:{{ $turnoverTrend['terms'][$i] > 0 ? max(4, round(($turnoverTrend['terms'][$i]/$maxVal)*100)) : 2 }}%"
                                 title="Bajas: {{ $turnoverTrend['terms'][$i] }}"></div>
                        </div>
                        <p class="text-[9px] text-slate-400 text-center leading-tight">{{ $label }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="flex gap-4 mt-3 text-xs text-slate-500">
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm bg-green-400 inline-block"></span>Ingresos</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm bg-red-400 inline-block"></span>Bajas</span>
            </div>
        </div>
    </div>

    {{-- Top Empleados por Costo --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden mt-6">
        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
            <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Top 10 Empleados por Costo Salarial</h3>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50/40">
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase">#</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Empleado</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase hidden md:table-cell">Área</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase hidden sm:table-cell">Puesto</th>
                    <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Salario</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase hidden sm:table-cell">Período</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($topByCost as $i => $emp)
                <tr class="hover:bg-slate-50/50">
                    <td class="px-4 py-3 text-slate-400 text-xs">{{ $i + 1 }}</td>
                    <td class="px-4 py-3 font-medium text-slate-800">{{ $emp->first_name }} {{ $emp->last_name }}</td>
                    <td class="px-4 py-3 text-slate-500 text-xs hidden md:table-cell">{{ $emp->department?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-500 text-xs hidden sm:table-cell">{{ $emp->position?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-right font-bold text-slate-800">$ {{ number_format($emp->salary, 2) }}</td>
                    <td class="px-4 py-3 text-xs text-slate-400 hidden sm:table-cell capitalize">{{ $emp->salary_period }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-10 text-center text-slate-400 text-sm">Sin empleados activos.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

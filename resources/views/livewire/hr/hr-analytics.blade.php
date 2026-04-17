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
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <p class="text-xs font-bold text-slate-500 uppercase mb-1">Plantilla Activa</p>
            <h3 class="text-3xl font-black text-indigo-600">{{ $activeCount }}</h3>
            <p class="text-xs text-slate-400 mt-1">{{ $inactiveCount }} inactivos</p>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <p class="text-xs font-bold text-slate-500 uppercase mb-1">Rotación Anual ({{ $year }})</p>
            <h3 class="text-3xl font-black text-red-600">{{ number_format($turnoverRate, 1) }}%</h3>
            <p class="text-xs text-slate-400 mt-1">{{ $terminatedThisYear }} bajas este año</p>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <p class="text-xs font-bold text-slate-500 uppercase mb-1">Antigüedad Promedio</p>
            <h3 class="text-3xl font-black text-emerald-600">{{ number_format($avgSeniority, 1) }} <span class="text-sm font-normal text-slate-500">años</span></h3>
            <p class="text-xs text-slate-400 mt-1">Estabilidad laboral</p>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <p class="text-xs font-bold text-slate-500 uppercase mb-1">Promedio Desempeño</p>
            <h3 class="text-3xl font-black text-amber-500">{{ number_format($avgPerformance, 1) }}%</h3>
            <p class="text-xs text-slate-400 mt-1">Nivel de cumplimiento</p>
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
</div>

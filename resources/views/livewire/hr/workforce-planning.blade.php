<div>
    <x-page-header title="Planeación Organizacional" description="Control de plantilla, rotación y ausentismo por área">
        <x-slot:actions>
            <a href="{{ route('hr.org-chart') }}" wire:navigate
               class="inline-flex items-center gap-2 px-3 py-2 text-sm text-slate-600 border border-slate-200 rounded-lg hover:bg-slate-50 transition">
                Ver organigrama
            </a>
        </x-slot:actions>
    </x-page-header>

    {{-- Tabs --}}
    <div class="flex gap-1 mb-6 border-b border-slate-200">
        @foreach(['headcount' => 'Control de plantilla', 'turnover' => 'Rotación de personal', 'absenteeism' => 'Ausentismo'] as $tab => $label)
        <button wire:click="$set('activeTab','{{ $tab }}')"
                class="px-4 py-2.5 text-sm font-medium border-b-2 transition -mb-px
                    {{ $activeTab === $tab
                        ? 'border-indigo-600 text-indigo-600'
                        : 'border-transparent text-slate-500 hover:text-slate-700' }}">
            {{ $label }}
        </button>
        @endforeach
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         TAB 1: Control de plantilla
         ═══════════════════════════════════════════════════════════ --}}
    @if($activeTab === 'headcount')
    @php $hc = $headcountData; @endphp

    {{-- KPIs --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
        <div class="bg-white rounded-xl border border-slate-200 p-4 text-center">
            <p class="text-2xl font-bold text-slate-800">{{ $hc['total_fill'] }}</p>
            <p class="text-xs text-slate-500 mt-0.5">Ocupados</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 text-center">
            <p class="text-2xl font-bold text-slate-500">{{ $hc['total_auth'] }}</p>
            <p class="text-xs text-slate-500 mt-0.5">Autorizados</p>
        </div>
        <div class="bg-white rounded-xl border border-amber-200 bg-amber-50 p-4 text-center">
            <p class="text-2xl font-bold text-amber-600">{{ $hc['under'] }}</p>
            <p class="text-xs text-amber-700 mt-0.5">Puestos con vacante</p>
        </div>
        <div class="bg-white rounded-xl border border-red-200 bg-red-50 p-4 text-center">
            <p class="text-2xl font-bold text-red-600">{{ $hc['over'] }}</p>
            <p class="text-xs text-red-700 mt-0.5">Puestos que exceden plantilla</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50/60">
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Puesto</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase hidden md:table-cell">Área</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Autorizados</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Ocupados</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 uppercase hidden sm:table-cell">Vacantes activas</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Estado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($hc['rows'] as $row)
                @php
                    $badge = match($row['status']) {
                        'over'      => ['bg-red-100 text-red-700',    'Excede'],
                        'complete'  => ['bg-green-100 text-green-700','Completo'],
                        'under'     => ['bg-amber-100 text-amber-700', $row['gap'].' vacante'.($row['gap']>1?'s':'')],
                        default     => ['bg-slate-100 text-slate-500', 'Sin definir'],
                    };
                @endphp
                <tr class="hover:bg-slate-50/50">
                    <td class="px-4 py-3 font-medium text-slate-800">{{ $row['name'] }}</td>
                    <td class="px-4 py-3 hidden md:table-cell text-slate-500 text-xs">{{ $row['department'] }}</td>
                    <td class="px-4 py-3 text-center text-slate-700">{{ $row['authorized'] ?: '—' }}</td>
                    <td class="px-4 py-3 text-center font-semibold text-slate-800">{{ $row['filled'] }}</td>
                    <td class="px-4 py-3 text-center hidden sm:table-cell">
                        @if($row['open_vacs'] > 0)
                        <span class="text-indigo-600 font-medium">{{ $row['open_vacs'] }}</span>
                        @else
                        <span class="text-slate-300">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $badge[0] }}">
                            {{ $badge[1] }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-10 text-center text-slate-400 text-sm">No hay puestos activos con plantilla definida.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════
         TAB 2: Rotación
         ═══════════════════════════════════════════════════════════ --}}
    @if($activeTab === 'turnover')
    @php $tv = $turnoverData; @endphp

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6">
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <p class="text-xs text-slate-400 mb-1">Tasa de rotación {{ $year }}</p>
            <p class="text-3xl font-bold {{ $tv['rate'] > 15 ? 'text-red-600' : ($tv['rate'] > 8 ? 'text-amber-600' : 'text-green-600') }}">
                {{ $tv['rate'] }}%
            </p>
            <p class="text-xs text-slate-400 mt-1">Referencia: &lt;8% saludable</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <p class="text-xs text-slate-400 mb-1">Bajas en {{ $year }}</p>
            <p class="text-3xl font-bold text-slate-700">{{ $tv['totalTermYear'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <p class="text-xs text-slate-400 mb-1">Más rotación por área</p>
            @if($tv['byDept']->isNotEmpty())
            <p class="text-base font-bold text-slate-800">{{ $tv['byDept']->keys()->first() }}</p>
            <p class="text-sm text-red-600">{{ $tv['byDept']->first() }} bajas</p>
            @else
            <p class="text-slate-400 text-sm">Sin datos</p>
            @endif
        </div>
    </div>

    {{-- Chart: bar chart via inline SVG bars --}}
    <div class="bg-white rounded-xl border border-slate-200 p-5 mb-5">
        <p class="text-sm font-semibold text-slate-700 mb-4">Entradas vs Salidas — últimos 12 meses</p>
        @php
            $maxVal = max(1, max(array_merge($tv['termSeries'], $tv['hireSeries'])));
        @endphp
        <div class="overflow-x-auto">
            <div class="flex items-end gap-2 min-w-max" style="height:140px">
                @foreach($tv['labels'] as $i => $label)
                <div class="flex flex-col items-center gap-1" style="width:52px">
                    <div class="flex items-end gap-0.5 w-full" style="height:120px">
                        {{-- Hires --}}
                        <div class="flex-1 bg-green-400 rounded-t transition-all"
                             style="height:{{ $tv['hireSeries'][$i] > 0 ? max(4, round(($tv['hireSeries'][$i]/$maxVal)*100)) : 2 }}%"
                             title="Ingresos: {{ $tv['hireSeries'][$i] }}"></div>
                        {{-- Terms --}}
                        <div class="flex-1 bg-red-400 rounded-t transition-all"
                             style="height:{{ $tv['termSeries'][$i] > 0 ? max(4, round(($tv['termSeries'][$i]/$maxVal)*100)) : 2 }}%"
                             title="Bajas: {{ $tv['termSeries'][$i] }}"></div>
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

    {{-- By department --}}
    @if($tv['byDept']->isNotEmpty())
    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <p class="text-sm font-semibold text-slate-700 mb-4">Bajas por área — {{ $year }}</p>
        <div class="space-y-2">
            @php $maxDept = $tv['byDept']->max(); @endphp
            @foreach($tv['byDept'] as $deptName => $count)
            <div class="flex items-center gap-3">
                <p class="text-sm text-slate-600 w-40 flex-shrink-0 truncate">{{ $deptName }}</p>
                <div class="flex-1 bg-slate-100 rounded-full h-2">
                    <div class="bg-red-400 h-2 rounded-full" style="width:{{ round(($count/$maxDept)*100) }}%"></div>
                </div>
                <span class="text-sm font-semibold text-slate-700 w-6 text-right">{{ $count }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif
    @endif

    {{-- ═══════════════════════════════════════════════════════════
         TAB 3: Ausentismo
         ═══════════════════════════════════════════════════════════ --}}
    @if($activeTab === 'absenteeism')
    @php $ab = $absenteeismData; @endphp

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6">
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <p class="text-xs text-slate-400 mb-1">Tasa de ausentismo (mes actual)</p>
            <p class="text-3xl font-bold {{ $ab['globalRate'] > 5 ? 'text-red-600' : ($ab['globalRate'] > 2 ? 'text-amber-600' : 'text-green-600') }}">
                {{ $ab['globalRate'] }}%
            </p>
            <p class="text-xs text-slate-400 mt-1">Referencia: &lt;2% aceptable</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <p class="text-xs text-slate-400 mb-1">Total faltas este mes</p>
            <p class="text-3xl font-bold text-slate-700">{{ $ab['totalAbsences'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <p class="text-xs text-slate-400 mb-1">Días hábiles del mes</p>
            <p class="text-3xl font-bold text-slate-700">{{ $ab['workingDays'] }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/60">
            <p class="text-sm font-semibold text-slate-700">Ausentismo por área — {{ now()->translatedFormat('F Y') }}</p>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100">
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Área</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Empleados</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Faltas</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 uppercase hidden sm:table-cell">Tardanzas</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Tasa</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($ab['rows'] as $row)
                @php
                    $barColor = $row['rate'] > 5 ? 'bg-red-400' : ($row['rate'] > 2 ? 'bg-amber-400' : 'bg-green-400');
                @endphp
                <tr class="hover:bg-slate-50/50">
                    <td class="px-4 py-3 font-medium text-slate-800">{{ $row['name'] }}</td>
                    <td class="px-4 py-3 text-center text-slate-600">{{ $row['employees'] }}</td>
                    <td class="px-4 py-3 text-center font-semibold {{ $row['absences'] > 0 ? 'text-red-600' : 'text-slate-400' }}">
                        {{ $row['absences'] }}
                    </td>
                    <td class="px-4 py-3 text-center hidden sm:table-cell {{ $row['lates'] > 0 ? 'text-amber-600 font-medium' : 'text-slate-400' }}">
                        {{ $row['lates'] }}
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <div class="w-24 bg-slate-100 rounded-full h-1.5">
                                <div class="{{ $barColor }} h-1.5 rounded-full" style="width:{{ min(100, $row['rate'] * 10) }}%"></div>
                            </div>
                            <span class="text-xs font-semibold {{ $row['rate'] > 5 ? 'text-red-600' : ($row['rate'] > 2 ? 'text-amber-600' : 'text-slate-600') }}">
                                {{ $row['rate'] }}%
                            </span>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-10 text-center text-slate-400 text-sm">Sin datos de asistencia para el mes actual.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @endif
</div>

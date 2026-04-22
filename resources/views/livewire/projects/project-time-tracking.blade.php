<div>
    {{-- Header --}}
    <div class="flex items-center gap-3 mb-6">
        <a wire:navigate href="{{ route('projects.show', $project) }}" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div class="flex-1">
            <h1 class="text-xl font-medium text-gray-900">Control de Tiempos — {{ $project->name }}</h1>
            <p class="text-xs text-gray-400">{{ $project->code }} · Asistencias vinculadas al proyecto</p>
        </div>
        <a wire:navigate href="{{ route('hr.attendances.index') }}"
           class="px-3 py-2 text-sm border border-slate-200 text-slate-600 rounded-lg hover:bg-slate-50 transition">
            Ir a Asistencias
        </a>
    </div>

    <x-alert />

    {{-- Filtros --}}
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
            <label class="block text-[10px] text-slate-400 mb-0.5 uppercase">Empleado</label>
            <select wire:model.live="filterEmployee"
                    class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30 bg-white">
                <option value="">Todos</option>
                @foreach($employees as $emp)
                <option value="{{ $emp->id }}">{{ $emp->last_name }} {{ $emp->first_name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- KPIs --}}
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-6">
        <div class="bg-white rounded-xl border border-slate-200 p-4 text-center">
            <p class="text-2xl font-bold text-indigo-600">{{ number_format($totalHours, 1) }}</p>
            <p class="text-xs text-slate-500 mt-0.5">Horas totales</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 text-center">
            <p class="text-2xl font-bold text-amber-500">{{ number_format($totalOvertime, 1) }}</p>
            <p class="text-xs text-slate-500 mt-0.5">Horas extra</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 text-center">
            <p class="text-2xl font-bold text-slate-700">{{ $totalDays }}</p>
            <p class="text-xs text-slate-500 mt-0.5">Días trabajados</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 text-center">
            <p class="text-2xl font-bold text-slate-700">{{ $employeeCount }}</p>
            <p class="text-xs text-slate-500 mt-0.5">Personas</p>
        </div>
        <div class="bg-white rounded-xl border {{ $totalLaborCost > 0 ? 'border-indigo-200 bg-indigo-50' : 'border-slate-200' }} p-4 text-center">
            <p class="text-2xl font-bold text-indigo-700">$ {{ number_format($totalLaborCost, 0) }}</p>
            <p class="text-xs text-slate-500 mt-0.5">Costo mano de obra</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Por empleado --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Horas por empleado</h3>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="text-left px-5 py-2.5 text-xs font-semibold text-slate-500 uppercase">Empleado</th>
                        <th class="text-center px-4 py-2.5 text-xs font-semibold text-slate-500 uppercase">Días</th>
                        <th class="text-right px-4 py-2.5 text-xs font-semibold text-slate-500 uppercase">Horas</th>
                        <th class="text-right px-4 py-2.5 text-xs font-semibold text-slate-500 uppercase hidden sm:table-cell">Extra</th>
                        <th class="text-right px-4 py-2.5 text-xs font-semibold text-slate-500 uppercase hidden md:table-cell">Tarifa/h</th>
                        <th class="text-right px-5 py-2.5 text-xs font-semibold text-slate-500 uppercase">Costo MO</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($byEmployee as $row)
                    <tr class="hover:bg-slate-50/50">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0">
                                    <span class="text-xs font-bold text-indigo-600">
                                        {{ strtoupper(substr($row['employee']?->first_name ?? '?', 0, 1) . substr($row['employee']?->last_name ?? '?', 0, 1)) }}
                                    </span>
                                </div>
                                <span class="font-medium text-slate-800">{{ $row['employee']?->full_name ?? '—' }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center text-slate-600">{{ $row['days'] }}</td>
                        <td class="px-4 py-3 text-right font-bold text-slate-800">{{ number_format($row['hours'], 1) }}h</td>
                        <td class="px-4 py-3 text-right hidden sm:table-cell {{ $row['overtime'] > 0 ? 'text-amber-600 font-medium' : 'text-slate-300' }}">
                            {{ $row['overtime'] > 0 ? '+'.number_format($row['overtime'], 1).'h' : '—' }}
                        </td>
                        <td class="px-4 py-3 text-right text-slate-500 hidden md:table-cell text-xs">
                            {{ $row['rate'] > 0 ? '$'.number_format($row['rate'], 2).'/h' : '—' }}
                        </td>
                        <td class="px-5 py-3 text-right font-bold {{ $row['cost'] > 0 ? 'text-indigo-700' : 'text-slate-300' }}">
                            {{ $row['cost'] > 0 ? '$'.number_format($row['cost'], 2) : '—' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-slate-400 text-sm">
                            Sin registros de asistencia vinculados a este proyecto en el período.
                            <br><a wire:navigate href="{{ route('hr.attendances.index') }}" class="text-indigo-500 underline text-xs mt-1 inline-block">Ir a registrar asistencia con proyecto</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($byEmployee->isNotEmpty())
                <tfoot>
                    <tr class="bg-slate-50 border-t-2 border-slate-200 font-bold">
                        <td class="px-5 py-3 text-slate-700">TOTAL</td>
                        <td class="px-4 py-3 text-center text-slate-600">{{ $totalDays }}</td>
                        <td class="px-4 py-3 text-right text-slate-800">{{ number_format($totalHours, 1) }}h</td>
                        <td class="px-4 py-3 text-right text-amber-600 hidden sm:table-cell">
                            {{ $totalOvertime > 0 ? '+'.number_format($totalOvertime, 1).'h' : '—' }}
                        </td>
                        <td class="px-4 py-3 hidden md:table-cell"></td>
                        <td class="px-5 py-3 text-right text-indigo-700">
                            {{ $totalLaborCost > 0 ? '$'.number_format($totalLaborCost, 2) : '—' }}
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        {{-- Por semana + detalle reciente --}}
        <div class="space-y-5">

            {{-- Tendencia semanal --}}
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
                    <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Horas por semana</h3>
                </div>
                @php $maxWeekHours = $byWeek->max('hours') ?: 1; @endphp
                <div class="p-5 space-y-3">
                    @forelse($byWeek as $w)
                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-slate-600 truncate pr-2">{{ $w['week'] }}</span>
                            <span class="font-bold text-slate-800 flex-shrink-0">{{ $w['hours'] }}h</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2">
                            <div class="bg-indigo-400 h-2 rounded-full transition-all"
                                 style="width: {{ round(($w['hours'] / $maxWeekHours) * 100) }}%"></div>
                        </div>
                    </div>
                    @empty
                    <p class="text-sm text-slate-400 text-center py-4">Sin datos</p>
                    @endforelse
                </div>
            </div>

            {{-- Últimos 10 registros --}}
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
                    <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Últimos registros</h3>
                </div>
                <ul class="divide-y divide-slate-100">
                    @forelse($records->take(10) as $rec)
                    <li class="px-5 py-3 flex justify-between items-center">
                        <div>
                            <p class="text-sm font-medium text-slate-800">{{ $rec->employee?->full_name ?? '—' }}</p>
                            <p class="text-xs text-slate-400">{{ $rec->date->format('d/m/Y') }}
                                @if($rec->check_in) · {{ \Carbon\Carbon::parse($rec->check_in)->format('H:i') }} @endif
                                @if($rec->check_out) – {{ \Carbon\Carbon::parse($rec->check_out)->format('H:i') }} @endif
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-bold text-slate-700">{{ number_format($rec->worked_hours, 1) }}h</p>
                            @if($rec->overtime_hours > 0)
                            <p class="text-xs text-amber-600">+{{ number_format($rec->overtime_hours, 1) }}h extra</p>
                            @endif
                        </div>
                    </li>
                    @empty
                    <li class="px-5 py-8 text-center text-sm text-slate-400">Sin registros</li>
                    @endforelse
                </ul>
            </div>

        </div>
    </div>
</div>

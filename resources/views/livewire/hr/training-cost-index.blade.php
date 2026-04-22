<div>
    <x-page-header title="Costos de Capacitación" description="Inversión en formación por empleado, área y curso">
        <x-slot:actions>
            <a wire:navigate href="{{ route('hr.training.index') }}"
               class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-slate-700 px-3 py-2 rounded-lg border border-slate-200 hover:bg-slate-50 transition">
                ← Volver
            </a>
        </x-slot:actions>
    </x-page-header>

    {{-- Filtros --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 mb-5 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Año</label>
            <select wire:model.live="filterYear"
                    class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
                @foreach($years as $year)
                <option value="{{ $year }}">{{ $year }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Tipo</label>
            <select wire:model.live="filterType"
                    class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
                <option value="">Todos</option>
                <option value="internal">Interno</option>
                <option value="external">Externo</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Área</label>
            <select wire:model.live="filterDepartment"
                    class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
                <option value="">Todas las áreas</option>
                @foreach($departments as $dep)
                <option value="{{ $dep->id }}">{{ $dep->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- KPIs --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-5">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1">Inversión total</p>
            <p class="text-2xl font-bold text-indigo-600">${{ number_format($totalCost, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1">Capacitaciones</p>
            <p class="text-2xl font-bold text-slate-800">{{ $totalTrainings }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1">Horas totales</p>
            <p class="text-2xl font-bold text-slate-800">{{ number_format($totalHours) }} hrs</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1">Empleados formados</p>
            <p class="text-2xl font-bold text-slate-800">{{ $totalEmployees }}</p>
        </div>
    </div>

    {{-- Interno vs Externo --}}
    @if($costByType->isNotEmpty())
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
        @foreach(['internal' => ['label' => 'Interno', 'color' => 'text-blue-600 bg-blue-50'], 'external' => ['label' => 'Externo', 'color' => 'text-purple-600 bg-purple-50']] as $type => $config)
        @php $row = $costByType->get($type) @endphp
        @if($row)
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 flex items-center justify-between gap-4">
            <div>
                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $config['color'] }}">{{ $config['label'] }}</span>
                <p class="text-xl font-bold text-slate-800 mt-1">${{ number_format($row->total, 2) }}</p>
                <p class="text-xs text-slate-400">{{ $row->count }} capacitaciones</p>
            </div>
            @if($totalCost > 0)
            <div class="text-right">
                <p class="text-2xl font-bold text-slate-600">{{ number_format($row->total / $totalCost * 100, 1) }}%</p>
                <p class="text-xs text-slate-400">del total</p>
            </div>
            @endif
        </div>
        @endif
        @endforeach
    </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">

        {{-- Top cursos --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-100">
                <h3 class="text-sm font-semibold text-slate-700">Top cursos por inversión</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50 text-xs font-semibold text-slate-500 uppercase">
                            <th class="text-left px-4 py-2">Curso</th>
                            <th class="text-center px-3 py-2">Tipo</th>
                            <th class="text-right px-3 py-2">Part.</th>
                            <th class="text-right px-4 py-2">Inversión</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($topCourses as $course)
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-4 py-2.5">
                                <p class="font-medium text-slate-800 text-xs">{{ $course->name }}</p>
                                <p class="text-[10px] text-slate-400">{{ $course->provider }} · {{ $course->duration_hours }} hrs</p>
                            </td>
                            <td class="px-3 py-2.5 text-center">
                                <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-medium
                                    {{ $course->type === 'internal' ? 'bg-blue-50 text-blue-600' : 'bg-purple-50 text-purple-600' }}">
                                    {{ $course->type === 'internal' ? 'Int' : 'Ext' }}
                                </span>
                            </td>
                            <td class="px-3 py-2.5 text-right text-xs text-slate-600">{{ $course->participants }}</td>
                            <td class="px-4 py-2.5 text-right font-semibold text-slate-800 text-xs">
                                ${{ number_format($course->total_cost, 2) }}
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="px-4 py-8 text-center text-slate-400 text-sm">Sin registros</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Por área --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-100">
                <h3 class="text-sm font-semibold text-slate-700">Inversión por área</h3>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($byDepartment as $dep)
                <div class="px-4 py-3 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-medium text-slate-700">{{ $dep->department_name }}</p>
                        <p class="text-xs text-slate-400">{{ $dep->courses_count }} capacitaciones · {{ $dep->total_hours }} hrs</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-indigo-600">${{ number_format($dep->total_cost, 2) }}</p>
                        @if($totalCost > 0)
                        <p class="text-xs text-slate-400">{{ number_format($dep->total_cost / $totalCost * 100, 1) }}%</p>
                        @endif
                    </div>
                </div>
                @empty
                <div class="px-4 py-8 text-center text-sm text-slate-400">Sin datos</div>
                @endforelse
            </div>
        </div>

    </div>

    {{-- Por empleado --}}
    <div class="mt-5 bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-100">
            <h3 class="text-sm font-semibold text-slate-700">Inversión por empleado (top 20)</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50 text-xs font-semibold text-slate-500 uppercase">
                        <th class="text-left px-5 py-2">Empleado</th>
                        <th class="text-right px-4 py-2">Cursos</th>
                        <th class="text-right px-4 py-2">Horas</th>
                        <th class="text-right px-4 py-2">Inversión</th>
                        <th class="text-right px-5 py-2">% del total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($byEmployee as $emp)
                    <tr class="hover:bg-slate-50/50">
                        <td class="px-5 py-2.5 font-medium text-slate-800">
                            {{ trim($emp->first_name . ' ' . $emp->last_name . ' ' . $emp->second_last_name) }}
                        </td>
                        <td class="px-4 py-2.5 text-right text-slate-600">{{ $emp->courses_count }}</td>
                        <td class="px-4 py-2.5 text-right text-slate-600">{{ $emp->total_hours }} hrs</td>
                        <td class="px-4 py-2.5 text-right font-semibold text-slate-800">${{ number_format($emp->total_cost, 2) }}</td>
                        <td class="px-5 py-2.5 text-right text-slate-500">
                            @if($totalCost > 0)
                            <div class="flex items-center justify-end gap-2">
                                <div class="w-16 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-indigo-400 rounded-full"
                                         style="width: {{ min(100, $emp->total_cost / $totalCost * 100) }}%"></div>
                                </div>
                                <span class="text-xs">{{ number_format($emp->total_cost / $totalCost * 100, 1) }}%</span>
                            </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-5 py-8 text-center text-slate-400">Sin registros</td></tr>
                    @endforelse
                </tbody>
                @if($totalCost > 0)
                <tfoot class="border-t border-slate-200 bg-slate-50">
                    <tr>
                        <td colspan="3" class="px-5 py-2 text-right text-xs font-semibold text-slate-600 uppercase">Total</td>
                        <td class="px-4 py-2 text-right font-bold text-indigo-700">${{ number_format($totalCost, 2) }}</td>
                        <td class="px-5 py-2"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

</div>

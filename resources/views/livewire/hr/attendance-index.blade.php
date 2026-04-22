<div>
    <x-page-header title="Control de asistencias" description="Registro de entradas, salidas y estatus diario">
        <x-slot:actions>
            <a href="{{ route('hr.attendance.locations') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 text-sm font-medium rounded-lg transition-colors">
                Zonas GPS
            </a>
            @can('create hr')
            <button wire:click="registerAllPresent"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                Registrar todos presentes
            </button>
            <button wire:click="openCreate"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                + Registrar
            </button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    {{-- Filtros --}}
    <div class="bg-white rounded-xl border border-slate-200 p-4 mb-5">
        <div class="flex flex-wrap gap-3">
            <input wire:model.live="filterDate" type="date"
                   class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
            <select wire:model.live="filterEmployee"
                    class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                <option value="">Todos los empleados</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}">{{ $emp->last_name }} {{ $emp->first_name }}</option>
                @endforeach
            </select>
            <select wire:model.live="filterStatus"
                    class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                <option value="">Todos los estatus</option>
                @foreach(\App\Models\HrAttendance::STATUSES as $k => $v)
                    <option value="{{ $k }}">{{ $v }}</option>
                @endforeach
            </select>
            <select wire:model.live="filterProject"
                    class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                <option value="">Todos los proyectos</option>
                @foreach($projects as $proj)
                    <option value="{{ $proj->id }}">{{ $proj->code }} — {{ $proj->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Resumen del día --}}
    @if($filterDate && !empty($summary))
    <div class="grid grid-cols-3 sm:grid-cols-6 gap-3 mb-5">
        @foreach(\App\Models\HrAttendance::STATUSES as $k => $v)
        <div class="bg-white rounded-xl border border-slate-200 p-3 text-center">
            <p class="text-xl font-bold text-slate-800">{{ $summary[$k] ?? 0 }}</p>
            <p class="text-xs text-slate-500">{{ $v }}</p>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Horas por proyecto (si hay filtro de fecha) --}}
    @if($projectHours && $projectHours->isNotEmpty())
    <div class="bg-white rounded-xl border border-slate-200 p-4 mb-5">
        <p class="text-xs font-bold text-slate-500 uppercase mb-3">Horas trabajadas por proyecto — {{ $filterDate }}</p>
        <div class="flex flex-wrap gap-3">
            @foreach($projectHours as $ph)
            <div class="flex items-center gap-2 px-3 py-2 bg-indigo-50 rounded-lg border border-indigo-100">
                <div class="w-2 h-2 bg-indigo-500 rounded-full"></div>
                <span class="text-sm font-medium text-slate-700">{{ $ph->project?->name ?? '—' }}</span>
                <span class="text-sm font-bold text-indigo-700">{{ number_format($ph->total_hours, 1) }}h</span>
                <span class="text-xs text-slate-400">({{ $ph->employees }} personas)</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Tabla --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50/60">
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Empleado</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Fecha</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase hidden sm:table-cell">Proyecto</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase hidden md:table-cell">Entrada</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase hidden md:table-cell">Salida</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase hidden lg:table-cell">Horas / Extra</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Estatus</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($attendances as $att)
                @php
                    $isLate      = $att->status === 'late';
                    $hasOvertime = $att->overtime_hours && $att->overtime_hours > 0;
                @endphp
                <tr class="hover:bg-slate-50/50">
                    <td class="px-4 py-3 font-medium text-slate-800">{{ $att->employee?->full_name ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $att->date->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 hidden sm:table-cell">
                        @if($att->project)
                        <span class="inline-flex items-center gap-1 text-xs bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded-full font-medium">
                            {{ $att->project->code }}
                        </span>
                        @else
                        <span class="text-slate-300">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 hidden md:table-cell">
                        <span class="font-mono text-slate-600">{{ $att->check_in ? \Carbon\Carbon::parse($att->check_in)->format('H:i') : '—' }}</span>
                        @if($isLate)
                        <span class="ml-1 inline-flex px-1.5 py-0.5 rounded text-[10px] font-bold bg-yellow-100 text-yellow-700 uppercase">Tarde</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 hidden md:table-cell font-mono text-slate-600">
                        {{ $att->check_out ? \Carbon\Carbon::parse($att->check_out)->format('H:i') : '—' }}
                    </td>
                    <td class="px-4 py-3 hidden lg:table-cell text-slate-600">
                        <span>{{ $att->worked_hours ? number_format($att->worked_hours, 1).' h' : '—' }}</span>
                        @if($hasOvertime)
                        <span class="ml-1 inline-flex px-1.5 py-0.5 rounded text-[10px] font-bold bg-orange-100 text-orange-700">+{{ number_format($att->overtime_hours, 1) }}h extra</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $att->status_color }}">
                            {{ $att->status_label }}
                        </span>
                        @if($att->location_id && ! $att->location_valid)
                        <span class="ml-1 inline-flex px-1.5 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-600">Fuera de zona</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        @can('edit hr')
                        <button wire:click="openEdit({{ $att->id }})"
                                class="text-xs text-indigo-600 hover:text-indigo-800">Editar</button>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-4 py-10 text-center text-slate-400 text-sm">No hay registros de asistencia para los filtros seleccionados.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($attendances->hasPages())
        <div class="px-4 py-3 border-t border-slate-100">{{ $attendances->links() }}</div>
        @endif
    </div>

    {{-- Modal --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40" wire:click.self="$set('showModal', false)">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md p-6">
            <h2 class="text-base font-semibold text-slate-800 mb-4">Registrar asistencia</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Empleado <span class="text-red-500">*</span></label>
                    <select wire:model="employee_id"
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                        <option value="">Seleccionar</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->last_name }} {{ $emp->first_name }}</option>
                        @endforeach
                    </select>
                    @error('employee_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Proyecto (opcional)</label>
                    <select wire:model="project_id"
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                        <option value="">— Sin proyecto —</option>
                        @foreach($projects as $proj)
                            <option value="{{ $proj->id }}">{{ $proj->code }} — {{ $proj->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Fecha <span class="text-red-500">*</span></label>
                    <input wire:model="date" type="date"
                           class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Hora entrada</label>
                        <input wire:model="check_in" type="time"
                               class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Hora salida</label>
                        <input wire:model="check_out" type="time"
                               class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Estatus <span class="text-red-500">*</span></label>
                    <select wire:model="status"
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                        @foreach(\App\Models\HrAttendance::STATUSES as $k => $v)
                            <option value="{{ $k }}">{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Notas</label>
                    <textarea wire:model="notes" rows="2"
                              class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30 resize-none"></textarea>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button wire:click="$set('showModal', false)"
                        class="px-4 py-2 text-sm text-slate-600 border border-slate-200 rounded-lg hover:bg-slate-50">Cancelar</button>
                <button wire:click="save"
                        class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">Guardar</button>
            </div>
        </div>
    </div>
    @endif
</div>

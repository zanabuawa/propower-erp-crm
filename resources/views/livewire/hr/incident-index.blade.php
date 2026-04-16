<div>
    <x-page-header title="Registro de incidencias" description="Faltas, tardanzas, accidentes y comportamientos">
        <x-slot:actions>
            @can('create hr')
            <button wire:click="openCreate"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg">
                + Registrar incidencia
            </button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    <div class="flex flex-wrap gap-3 mb-5">
        <select wire:model.live="filterEmployee"
                class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
            <option value="">Todos los empleados</option>
            @foreach($employees as $emp)
                <option value="{{ $emp->id }}">{{ $emp->last_name }} {{ $emp->first_name }}</option>
            @endforeach
        </select>
        <select wire:model.live="filterType"
                class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
            <option value="">Todos los tipos</option>
            @foreach(\App\Models\HrIncident::TYPES as $k => $v)
                <option value="{{ $k }}">{{ $v }}</option>
            @endforeach
        </select>
        <select wire:model.live="filterSeverity"
                class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
            <option value="">Todas las severidades</option>
            @foreach(\App\Models\HrIncident::SEVERITIES as $k => $v)
                <option value="{{ $k }}">{{ $v }}</option>
            @endforeach
        </select>
        <label class="flex items-center gap-2 text-sm text-slate-600 cursor-pointer">
            <input wire:model.live="filterResolved" type="checkbox" class="rounded">
            Mostrar resueltas
        </label>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50/60">
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Empleado</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Tipo</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase hidden md:table-cell">Fecha</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Severidad</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase hidden lg:table-cell">Descripción</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase hidden sm:table-cell">Estado</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($incidents as $incident)
                <tr class="hover:bg-slate-50/50">
                    <td class="px-4 py-3 font-medium text-slate-800">{{ $incident->employee?->full_name ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $incident->type_label }}</td>
                    <td class="px-4 py-3 hidden md:table-cell text-slate-500 text-xs">{{ $incident->incident_date->format('d/m/Y') }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $incident->severity_color }}">
                            {{ \App\Models\HrIncident::SEVERITIES[$incident->severity] ?? $incident->severity }}
                        </span>
                    </td>
                    <td class="px-4 py-3 hidden lg:table-cell text-xs text-slate-500">{{ Str::limit($incident->description, 60) }}</td>
                    <td class="px-4 py-3 hidden sm:table-cell">
                        @if($incident->resolved)
                            <span class="px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-700">Resuelta</span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-xs bg-yellow-100 text-yellow-700">Pendiente</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        @can('edit hr')
                        <div class="flex justify-end gap-2">
                            @if(!$incident->resolved)
                            <button wire:click="markResolved({{ $incident->id }})"
                                    class="text-xs text-green-600 hover:text-green-800">Resolver</button>
                            @endif
                            <button wire:click="openEdit({{ $incident->id }})"
                                    class="text-xs text-slate-500 hover:text-slate-700">Editar</button>
                        </div>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-10 text-center text-slate-400 text-sm">No hay incidencias registradas.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($incidents->hasPages())
        <div class="px-4 py-3 border-t border-slate-100">{{ $incidents->links() }}</div>
        @endif
    </div>

    {{-- Modal --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40" wire:click.self="$set('showModal', false)">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg p-6">
            <h2 class="text-base font-semibold text-slate-800 mb-4">{{ $editingId ? 'Editar' : 'Registrar' }} incidencia</h2>
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
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Tipo <span class="text-red-500">*</span></label>
                        <select wire:model="type"
                                class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                            @foreach(\App\Models\HrIncident::TYPES as $k => $v)
                                <option value="{{ $k }}">{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Fecha <span class="text-red-500">*</span></label>
                        <input wire:model="incident_date" type="date"
                               class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Severidad</label>
                    <select wire:model="severity"
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                        @foreach(\App\Models\HrIncident::SEVERITIES as $k => $v)
                            <option value="{{ $k }}">{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Descripción <span class="text-red-500">*</span></label>
                    <textarea wire:model="description" rows="3"
                              class="w-full px-3 py-2 text-sm border @error('description') border-red-300 @else border-slate-200 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30 resize-none"></textarea>
                    @error('description') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Acción tomada</label>
                    <textarea wire:model="action_taken" rows="2"
                              class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30 resize-none"></textarea>
                </div>
                <div class="flex items-center gap-2">
                    <input wire:model="resolved" type="checkbox" id="resolved_check" class="rounded">
                    <label for="resolved_check" class="text-sm text-slate-600">Marcar como resuelta</label>
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

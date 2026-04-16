<div>
    <x-page-header title="Bajas temporales y permisos" description="Vacaciones, incapacidades y permisos especiales">
        <x-slot:actions>
            @can('create hr')
            <button wire:click="openCreate"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg">
                + Nueva solicitud
            </button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    <div class="flex flex-wrap gap-3 mb-5">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar empleado..."
               class="flex-1 min-w-[180px] px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
        <select wire:model.live="filterStatus"
                class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
            <option value="">Todos los estados</option>
            @foreach(\App\Models\HrLeave::STATUSES as $k => $v)
                <option value="{{ $k }}">{{ $v }}</option>
            @endforeach
        </select>
        <select wire:model.live="filterType"
                class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
            <option value="">Todos los tipos</option>
            @foreach(\App\Models\HrLeave::TYPES as $k => $v)
                <option value="{{ $k }}">{{ $v }}</option>
            @endforeach
        </select>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50/60">
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Empleado</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Tipo</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase hidden md:table-cell">Fechas</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase hidden lg:table-cell">Días</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Estado</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($leaves as $leave)
                <tr class="hover:bg-slate-50/50">
                    <td class="px-4 py-3 font-medium text-slate-800">{{ $leave->employee?->full_name ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $leave->type_label }}</td>
                    <td class="px-4 py-3 hidden md:table-cell text-xs text-slate-500">
                        {{ $leave->start_date->format('d/m/Y') }} → {{ $leave->end_date->format('d/m/Y') }}
                    </td>
                    <td class="px-4 py-3 hidden lg:table-cell text-slate-600">{{ $leave->business_days }} días hábiles</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $leave->status_color }}">
                            {{ \App\Models\HrLeave::STATUSES[$leave->status] ?? $leave->status }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex justify-end gap-2">
                            @can('edit hr')
                            @if($leave->status === 'pending')
                            <button wire:click="approve({{ $leave->id }})"
                                    class="text-xs text-green-600 hover:text-green-800 font-medium">Aprobar</button>
                            <button wire:click="reject({{ $leave->id }})"
                                    class="text-xs text-red-600 hover:text-red-800">Rechazar</button>
                            @endif
                            <button wire:click="openEdit({{ $leave->id }})"
                                    class="text-xs text-slate-500 hover:text-slate-700">Editar</button>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-10 text-center text-slate-400 text-sm">No hay solicitudes registradas.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($leaves->hasPages())
        <div class="px-4 py-3 border-t border-slate-100">{{ $leaves->links() }}</div>
        @endif
    </div>

    {{-- Modal --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40" wire:click.self="$set('showModal', false)">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg p-6">
            <h2 class="text-base font-semibold text-slate-800 mb-4">{{ $editingId ? 'Editar solicitud' : 'Nueva solicitud' }}</h2>
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
                    <label class="block text-xs font-medium text-slate-600 mb-1">Tipo <span class="text-red-500">*</span></label>
                    <select wire:model="type"
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                        @foreach(\App\Models\HrLeave::TYPES as $k => $v)
                            <option value="{{ $k }}">{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Fecha inicio <span class="text-red-500">*</span></label>
                        <input wire:model="start_date" type="date"
                               class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Fecha fin <span class="text-red-500">*</span></label>
                        <input wire:model="end_date" type="date"
                               class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Motivo</label>
                    <textarea wire:model="reason" rows="2"
                              class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30 resize-none"></textarea>
                </div>
                @if($type === 'incapacidad_imss')
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Folio incapacidad IMSS</label>
                    <input wire:model="imss_certificate_number" type="text"
                           class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                </div>
                @endif
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Notas internas</label>
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

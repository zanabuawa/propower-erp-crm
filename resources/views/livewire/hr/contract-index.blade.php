<div>
    <x-page-header title="Contratos" description="Gestión de contratos laborales">
        <x-slot:actions>
            @can('create hr')
            <button wire:click="openCreate"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg">
                + Nuevo contrato
            </button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    <div class="flex flex-wrap gap-3 mb-5">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar empleado..."
               class="flex-1 min-w-[200px] px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
        <select wire:model.live="filterStatus"
                class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
            <option value="">Todos los estados</option>
            @foreach(\App\Models\HrContract::STATUSES as $k => $v)
                <option value="{{ $k }}">{{ $v }}</option>
            @endforeach
        </select>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50/60">
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Empleado</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase hidden md:table-cell">Tipo</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase hidden md:table-cell">Vigencia</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase hidden lg:table-cell">Salario</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Estado</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($contracts as $contract)
                <tr class="hover:bg-slate-50/50">
                    <td class="px-4 py-3">
                        <p class="font-medium text-slate-800">{{ $contract->employee?->full_name ?? '—' }}</p>
                        @if($contract->contract_number) <p class="text-xs text-slate-400">#{{ $contract->contract_number }}</p> @endif
                    </td>
                    <td class="px-4 py-3 hidden md:table-cell text-slate-600">{{ $contract->type_label }}</td>
                    <td class="px-4 py-3 hidden md:table-cell text-xs text-slate-500">
                        {{ $contract->start_date->format('d/m/Y') }}<br>
                        {{ $contract->end_date ? '→ '.$contract->end_date->format('d/m/Y') : '(indefinido)' }}
                    </td>
                    <td class="px-4 py-3 hidden lg:table-cell text-slate-600">
                        ${{ number_format($contract->salary, 2) }}
                        <span class="text-xs text-slate-400">/ {{ \App\Models\HrEmployee::SALARY_PERIODS[$contract->salary_period] ?? '' }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $contract->status_color }}">{{ $contract->status_label }}</span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        @can('edit hr')
                        <button wire:click="openEdit({{ $contract->id }})"
                                class="text-xs text-indigo-600 hover:text-indigo-800">Editar</button>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-10 text-center text-slate-400 text-sm">No hay contratos registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($contracts->hasPages())
        <div class="px-4 py-3 border-t border-slate-100">{{ $contracts->links() }}</div>
        @endif
    </div>

    {{-- Modal --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40" wire:click.self="$set('showModal', false)">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl p-6 max-h-[90vh] overflow-y-auto">
            <h2 class="text-base font-semibold text-slate-800 mb-4">{{ $editingId ? 'Editar' : 'Nuevo' }} contrato</h2>
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-xs font-medium text-slate-600 mb-1">Empleado <span class="text-red-500">*</span></label>
                    <select wire:model="employee_id"
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                        <option value="">Seleccionar empleado</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->last_name }} {{ $emp->first_name }}</option>
                        @endforeach
                    </select>
                    @error('employee_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">N° Contrato</label>
                    <input wire:model="contract_number" type="text"
                           class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Tipo <span class="text-red-500">*</span></label>
                    <select wire:model="type"
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                        @foreach(\App\Models\HrContract::TYPES as $k => $v)
                            <option value="{{ $k }}">{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Fecha inicio <span class="text-red-500">*</span></label>
                    <input wire:model="start_date" type="date"
                           class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Fecha fin</label>
                    <input wire:model="end_date" type="date"
                           class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Salario <span class="text-red-500">*</span></label>
                    <input wire:model="salary" type="number" step="0.01" min="0"
                           class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Periodicidad</label>
                    <select wire:model="salary_period"
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                        @foreach(\App\Models\HrEmployee::SALARY_PERIODS as $k => $v)
                            <option value="{{ $k }}">{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Turno</label>
                    <select wire:model="work_shift"
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                        <option value="">Seleccionar</option>
                        @foreach(\App\Models\HrEmployee::WORK_SHIFTS as $k => $v)
                            <option value="{{ $k }}">{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Horas/semana</label>
                    <input wire:model="work_hours_per_week" type="number" min="1" max="96"
                           class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                </div>

                {{-- Prestaciones --}}
                <div class="col-span-2 pt-2 border-t border-slate-100">
                    <p class="text-xs font-semibold text-slate-600 mb-3">Prestaciones</p>
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Días aguinaldo</label>
                            <input wire:model="aguinaldo_days" type="number" min="15"
                                   class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Días vacaciones</label>
                            <input wire:model="vacation_days" type="number" min="6"
                                   class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Prima vacacional %</label>
                            <input wire:model="vacation_premium_pct" type="number" min="25" max="100"
                                   class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Estado</label>
                    <select wire:model="status"
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                        @foreach(\App\Models\HrContract::STATUSES as $k => $v)
                            <option value="{{ $k }}">{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-span-2">
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

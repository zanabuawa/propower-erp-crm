<div>
    <x-page-header title="Puestos laborales" description="Catálogo de puestos y rangos salariales">
        <x-slot:actions>
            @can('create hr')
            <button wire:click="openCreate"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                + Nuevo puesto
            </button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    <div class="flex flex-wrap gap-3 mb-5">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar puesto..."
               class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
        <select wire:model.live="filterDepartment"
                class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
            <option value="">Todos los departamentos</option>
            @foreach($departments as $dept)
                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50/60">
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Puesto</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase hidden md:table-cell">Departamento</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase hidden lg:table-cell">Rango salarial</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase hidden sm:table-cell">Empleados</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Estado</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($positions as $pos)
                <tr class="hover:bg-slate-50/50">
                    <td class="px-4 py-3">
                        <p class="font-medium text-slate-800">{{ $pos->name }}</p>
                        @if($pos->code) <p class="text-xs text-slate-400 font-mono">{{ $pos->code }}</p> @endif
                    </td>
                    <td class="px-4 py-3 hidden md:table-cell text-slate-600">{{ $pos->department?->name ?? '—' }}</td>
                    <td class="px-4 py-3 hidden lg:table-cell text-slate-600 text-xs">
                        @if($pos->min_salary || $pos->max_salary)
                            ${{ number_format($pos->min_salary ?? 0, 0) }} – ${{ number_format($pos->max_salary ?? 0, 0) }}
                            / {{ \App\Models\HrPosition::SALARY_TYPES[$pos->salary_type] ?? '' }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="px-4 py-3 hidden sm:table-cell text-slate-600">{{ $pos->employees_count }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs {{ $pos->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $pos->is_active ? 'Activo' : 'Inactivo' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        @can('edit hr')
                        <button wire:click="openEdit({{ $pos->id }})"
                                class="text-xs text-indigo-600 hover:text-indigo-800">Editar</button>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-10 text-center text-slate-400 text-sm">No hay puestos registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modal --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40" wire:click.self="$set('showModal', false)">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg p-6">
            <h2 class="text-base font-semibold text-slate-800 mb-4">{{ $editingId ? 'Editar' : 'Nuevo' }} puesto</h2>
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-slate-600 mb-1">Nombre <span class="text-red-500">*</span></label>
                        <input wire:model="name" type="text"
                               class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                        @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Código</label>
                        <input wire:model="code" type="text"
                               class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Departamento <span class="text-red-500">*</span></label>
                        <select wire:model="department_id"
                                class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                            <option value="">Seleccionar</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                        @error('department_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Tipo de salario</label>
                        <select wire:model="salary_type"
                                class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                            @foreach(\App\Models\HrPosition::SALARY_TYPES as $k => $v)
                                <option value="{{ $k }}">{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Salario mínimo</label>
                        <input wire:model="min_salary" type="number" step="0.01" min="0"
                               class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Salario máximo</label>
                        <input wire:model="max_salary" type="number" step="0.01" min="0"
                               class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-slate-600 mb-1">Descripción</label>
                        <textarea wire:model="description" rows="2"
                                  class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30 resize-none"></textarea>
                    </div>
                    <div class="col-span-2 flex items-center gap-2">
                        <input wire:model="is_active" type="checkbox" id="pos_active" class="rounded">
                        <label for="pos_active" class="text-sm text-slate-600">Puesto activo</label>
                    </div>
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

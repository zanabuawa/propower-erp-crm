<div>
    <x-page-header title="Conceptos de Nómina" description="Catálogo de percepciones y deducciones personalizadas">
        <x-slot:actions>
            <button wire:click="openCreate"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                + Nuevo concepto
            </button>
        </x-slot:actions>
    </x-page-header>

    <x-alert />

    {{-- Filtro --}}
    <div class="mb-4 flex gap-2">
        <button wire:click="$set('filterType', '')"
                class="px-3 py-1.5 text-sm rounded-lg transition {{ $filterType === '' ? 'bg-indigo-600 text-white' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
            Todos
        </button>
        <button wire:click="$set('filterType', 'perception')"
                class="px-3 py-1.5 text-sm rounded-lg transition {{ $filterType === 'perception' ? 'bg-green-600 text-white' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
            Percepciones
        </button>
        <button wire:click="$set('filterType', 'deduction')"
                class="px-3 py-1.5 text-sm rounded-lg transition {{ $filterType === 'deduction' ? 'bg-red-600 text-white' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
            Deducciones
        </button>
    </div>

    {{-- Tabla --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50 text-xs font-semibold text-slate-500 uppercase">
                    <th class="text-left px-5 py-3">Concepto</th>
                    <th class="text-center px-4 py-3">Código</th>
                    <th class="text-center px-4 py-3">Tipo</th>
                    <th class="text-center px-4 py-3">Gravable</th>
                    <th class="text-center px-4 py-3">Activo</th>
                    <th class="text-center px-4 py-3">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($concepts as $concept)
                <tr class="hover:bg-slate-50/50 {{ !$concept->is_active ? 'opacity-50' : '' }}">
                    <td class="px-5 py-3 font-medium text-slate-800">{{ $concept->name }}</td>
                    <td class="px-4 py-3 text-center font-mono text-xs text-slate-500">{{ $concept->code ?? '—' }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                            {{ $concept->type === 'perception' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $concept->type === 'perception' ? 'Percepción' : 'Deducción' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center text-xs text-slate-500">
                        {{ $concept->is_taxable ? 'Sí' : 'No' }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        <button wire:click="toggleActive({{ $concept->id }})"
                                class="text-xs {{ $concept->is_active ? 'text-green-600 hover:text-green-800' : 'text-slate-400 hover:text-slate-600' }}">
                            {{ $concept->is_active ? 'Activo' : 'Inactivo' }}
                        </button>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <button wire:click="openEdit({{ $concept->id }})"
                                    class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Editar</button>
                            <button wire:click="delete({{ $concept->id }})"
                                    wire:confirm="¿Eliminar este concepto?"
                                    class="text-xs text-red-500 hover:text-red-700 font-medium">Eliminar</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-5 py-10 text-center text-slate-400">Sin conceptos registrados. Crea el primero.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modal --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4">
            <h3 class="text-base font-bold text-slate-800 mb-4">
                {{ $editingId ? 'Editar concepto' : 'Nuevo concepto' }}
            </h3>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Nombre <span class="text-red-400">*</span></label>
                    <input wire:model="name" type="text"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                           placeholder="Ej. Bono de productividad">
                    @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Tipo <span class="text-red-400">*</span></label>
                        <select wire:model="type"
                                class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
                            <option value="perception">Percepción</option>
                            <option value="deduction">Deducción</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Código SAT</label>
                        <input wire:model="code" type="text"
                               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                               placeholder="Ej. 019">
                    </div>
                </div>

                <div class="flex gap-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input wire:model="is_taxable" type="checkbox"
                               class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-300">
                        <span class="text-sm text-slate-700">Gravable (ISR)</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input wire:model="is_active" type="checkbox"
                               class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-300">
                        <span class="text-sm text-slate-700">Activo</span>
                    </label>
                </div>
            </div>

            <div class="flex gap-3 mt-5">
                <button wire:click="save" wire:loading.attr="disabled"
                        class="flex-1 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                    <span wire:loading.remove wire:target="save">Guardar</span>
                    <span wire:loading wire:target="save">Guardando...</span>
                </button>
                <button wire:click="$set('showModal', false)"
                        class="flex-1 text-slate-600 text-sm font-medium px-4 py-2 rounded-lg border border-slate-200 hover:bg-slate-50 transition">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
    @endif
</div>

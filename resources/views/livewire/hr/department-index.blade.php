<div>
    <x-page-header title="Departamentos" description="Estructura organizacional de la empresa">
        <x-slot:actions>
            @can('create hr')
            <button wire:click="openCreate"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                + Nuevo departamento
            </button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    <div class="mb-4">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar departamento..."
               class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30 w-64">
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($departments as $dept)
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <h3 class="font-semibold text-slate-800">{{ $dept->name }}</h3>
                    @if($dept->code) <p class="text-xs text-slate-400 font-mono">{{ $dept->code }}</p> @endif
                </div>
                <span class="px-2 py-0.5 rounded-full text-xs {{ $dept->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                    {{ $dept->is_active ? 'Activo' : 'Inactivo' }}
                </span>
            </div>
            @if($dept->description)
            <p class="text-xs text-slate-500 mb-3">{{ Str::limit($dept->description, 80) }}</p>
            @endif
            <div class="flex items-center justify-between text-xs text-slate-500 pt-3 border-t border-slate-100">
                <span>{{ $dept->employees_count }} empleados</span>
                @if($dept->manager)
                <span>Jefe: {{ $dept->manager->full_name }}</span>
                @endif
            </div>
            @can('edit hr')
            <div class="flex gap-2 mt-3">
                <button wire:click="openEdit({{ $dept->id }})"
                        class="flex-1 text-xs py-1.5 text-indigo-600 hover:text-indigo-800 border border-indigo-200 rounded-lg hover:bg-indigo-50 transition-colors">
                    Editar
                </button>
                <button wire:click="toggleActive({{ $dept->id }})"
                        class="flex-1 text-xs py-1.5 text-slate-500 hover:text-slate-700 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
                    {{ $dept->is_active ? 'Desactivar' : 'Activar' }}
                </button>
            </div>
            @endcan
        </div>
        @empty
        <div class="col-span-3 py-10 text-center text-slate-400 text-sm">No hay departamentos registrados.</div>
        @endforelse
    </div>

    {{-- Modal --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40" wire:click.self="$set('showModal', false)">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md p-6">
            <h2 class="text-base font-semibold text-slate-800 mb-4">{{ $editingId ? 'Editar' : 'Nuevo' }} departamento</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Nombre <span class="text-red-500">*</span></label>
                    <input wire:model="name" type="text"
                           class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Código</label>
                    <input wire:model="code" type="text" placeholder="RH, VTA, ADM..."
                           class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Descripción</label>
                    <textarea wire:model="description" rows="2"
                              class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30 resize-none"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Jefe de departamento</label>
                    <select wire:model="manager_id"
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                        <option value="">Sin asignar</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->last_name }} {{ $emp->first_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <input wire:model="is_active" type="checkbox" id="dept_active" class="rounded">
                    <label for="dept_active" class="text-sm text-slate-600">Departamento activo</label>
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

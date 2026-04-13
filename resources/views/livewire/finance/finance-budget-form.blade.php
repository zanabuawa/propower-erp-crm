<div class="max-w-2xl">
    <div class="flex items-center gap-3 mb-6">
        <a wire:navigate href="{{ route('finance.budgets.index') }}" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="text-xl font-medium text-gray-900">
            {{ $budget && $budget->exists ? 'Editar presupuesto' : 'Nuevo presupuesto' }}
        </h1>
    </div>

    <form wire:submit="save" class="space-y-5">

        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">Identificación</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">Nombre *</label>
                    <input wire:model="name" type="text" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Categoría *</label>
                    <select wire:model="category" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
                        <option value="ingresos">Ingresos</option>
                        <option value="egresos">Egresos</option>
                        <option value="proyecto">Proyecto</option>
                        <option value="departamento">Departamento</option>
                        <option value="otro">Otro</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Estado</label>
                    <select wire:model="status" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
                        <option value="borrador">Borrador</option>
                        <option value="aprobado">Aprobado</option>
                        <option value="cerrado">Cerrado</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">Período</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Tipo de período *</label>
                    <select wire:model.live="period_type" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
                        <option value="mensual">Mensual</option>
                        <option value="trimestral">Trimestral</option>
                        <option value="semestral">Semestral</option>
                        <option value="anual">Anual</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Año *</label>
                    <input wire:model="year" type="number" min="2000" max="2100" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    @error('year') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                @if($period_type !== 'anual')
                <div>
                    <label class="block text-xs text-gray-500 mb-1">
                        @if($period_type === 'mensual') Mes (1-12)
                        @elseif($period_type === 'trimestral') Trimestre (1-4)
                        @else Semestre (1-2)
                        @endif
                    </label>
                    <input wire:model="period_number" type="number" min="1"
                        max="{{ $period_type === 'mensual' ? 12 : ($period_type === 'trimestral' ? 4 : 2) }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    @error('period_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">Monto y asignación</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Monto planeado *</label>
                    <input wire:model="amount_planned" type="number" step="0.01" min="0" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    @error('amount_planned') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Moneda</label>
                    <select wire:model="currency" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
                        <option value="MXN">MXN</option>
                        <option value="USD">USD</option>
                        <option value="EUR">EUR</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Sucursal</label>
                    <select wire:model="branch_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
                        <option value="">— Sin sucursal —</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Proyecto</label>
                    <select wire:model="project_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
                        <option value="">— Sin proyecto —</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}">{{ $project->code }} – {{ $project->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">Notas</label>
                    <textarea wire:model="notes" rows="2" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 resize-none"></textarea>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a wire:navigate href="{{ route('finance.budgets.index') }}"
                class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 border border-gray-200 rounded-lg transition">
                Cancelar
            </a>
            <button type="submit"
                class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition shadow-sm">
                {{ $budget && $budget->exists ? 'Guardar cambios' : 'Crear presupuesto' }}
            </button>
        </div>
    </form>
</div>

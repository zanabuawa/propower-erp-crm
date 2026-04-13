<div>
    <div class="flex items-center gap-3 mb-6">
        <a wire:navigate href="{{ route('projects.show', $project) }}" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div class="flex-1">
            <h1 class="text-xl font-medium text-gray-900">Gastos del proyecto</h1>
            <p class="text-xs text-gray-400">{{ $project->code }} — {{ $project->name }}</p>
        </div>
        @can('create projects')
        <button wire:click="$set('showForm', true)"
            class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Agregar gasto
        </button>
        @endcan
    </div>

    <x-alert />

    {{-- Resumen por estado --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
        @php
            $statusLabels = ['pendiente'=>['label'=>'Pendiente','color'=>'yellow'], 'aprobado'=>['label'=>'Aprobado','color'=>'green'], 'rechazado'=>['label'=>'Rechazado','color'=>'red'], 'pagado'=>['label'=>'Pagado','color'=>'blue']];
        @endphp
        @foreach($statusLabels as $key => $s)
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-400 mb-1">{{ $s['label'] }}</p>
            <p class="text-sm font-semibold text-gray-800">{{ number_format($totalByStatus[$key] ?? 0, 2) }}</p>
        </div>
        @endforeach
    </div>

    {{-- Formulario inline --}}
    @if($showForm)
    <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-5 mb-5">
        <h2 class="text-sm font-medium text-gray-700 mb-4">{{ $editingId ? 'Editar gasto' : 'Nuevo gasto' }}</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="sm:col-span-2">
                <label class="block text-xs text-gray-500 mb-1">Concepto *</label>
                <input wire:model="concept" type="text" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                @error('concept') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Categoría *</label>
                <select wire:model="category" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
                    <option value="material">Material</option>
                    <option value="mano_obra">Mano de obra</option>
                    <option value="subcontrato">Subcontrato</option>
                    <option value="transporte">Transporte</option>
                    <option value="viaje">Viaje</option>
                    <option value="otro">Otro</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Monto *</label>
                <input wire:model="amount" type="number" step="0.01" min="0.01" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                @error('amount') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
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
                <label class="block text-xs text-gray-500 mb-1">Fecha *</label>
                <input wire:model="expense_date" type="date" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Referencia</label>
                <input wire:model="reference" type="text" placeholder="Folio, factura..." class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Estado</label>
                <select wire:model="status" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
                    <option value="pendiente">Pendiente</option>
                    <option value="aprobado">Aprobado</option>
                    <option value="rechazado">Rechazado</option>
                    <option value="pagado">Pagado</option>
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs text-gray-500 mb-1">Notas</label>
                <input wire:model="notes" type="text" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
            </div>
        </div>
        <div class="flex gap-2 mt-4">
            <button wire:click="save" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition">
                {{ $editingId ? 'Guardar cambios' : 'Registrar gasto' }}
            </button>
            <button wire:click="resetForm" class="px-4 py-2 border border-gray-200 text-gray-600 text-sm rounded-lg hover:bg-gray-50 transition">
                Cancelar
            </button>
        </div>
    </div>
    @endif

    {{-- Filtros --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4">
        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar concepto..."
                class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
        </div>
        <select wire:model.live="filterCategory" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
            <option value="">Todas las categorías</option>
            <option value="material">Material</option>
            <option value="mano_obra">Mano de obra</option>
            <option value="subcontrato">Subcontrato</option>
            <option value="transporte">Transporte</option>
            <option value="viaje">Viaje</option>
            <option value="otro">Otro</option>
        </select>
        <select wire:model.live="filterStatus" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
            <option value="">Todos los estados</option>
            <option value="pendiente">Pendiente</option>
            <option value="aprobado">Aprobado</option>
            <option value="rechazado">Rechazado</option>
            <option value="pagado">Pagado</option>
        </select>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[640px]">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Fecha</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Concepto</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden md:table-cell">Categoría</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Monto</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Estado</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($expenses as $expense)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-5 py-3 text-gray-600">{{ $expense->expense_date->format('d/m/Y') }}</td>
                        <td class="px-5 py-3">
                            <div class="font-medium text-gray-900">{{ $expense->concept }}</div>
                            @if($expense->reference)<div class="text-xs text-gray-400">Ref: {{ $expense->reference }}</div>@endif
                        </td>
                        <td class="px-5 py-3 hidden md:table-cell capitalize text-gray-600">{{ str_replace('_', ' ', $expense->category) }}</td>
                        <td class="px-5 py-3 text-right font-semibold text-gray-800">{{ $expense->currency }} {{ number_format($expense->amount, 2) }}</td>
                        <td class="px-5 py-3">
                            @php $sc = ['pendiente'=>'yellow','aprobado'=>'green','rechazado'=>'red','pagado'=>'blue'][$expense->status] ?? 'gray'; @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $sc }}-100 text-{{ $sc }}-700 capitalize">{{ $expense->status }}</span>
                        </td>
                        <td class="px-5 py-3 text-right flex items-center justify-end gap-3">
                            @can('edit projects')
                            <button wire:click="edit({{ $expense->id }})" class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">Editar</button>
                            @endcan
                            @can('delete projects')
                            <button wire:click="delete({{ $expense->id }})" wire:confirm="¿Eliminar este gasto?" class="text-red-400 hover:text-red-600 text-xs">Eliminar</button>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-gray-400 text-sm">Sin gastos registrados.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($expenses->hasPages())
        <div class="px-5 py-3 border-t border-gray-100">{{ $expenses->links() }}</div>
        @endif
    </div>
</div>

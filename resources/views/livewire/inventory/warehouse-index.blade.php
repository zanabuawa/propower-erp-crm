<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-medium text-gray-900">Almacenes</h1>
            <p class="text-sm text-gray-500 mt-0.5">Gestiona los almacenes por sucursal</p>
        </div>
        <a href="{{ route('inventory.warehouses.create') }}"
            class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
            + Nuevo almacén
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-4">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar almacén..."
            class="w-full sm:w-80 border border-gray-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50">
                    <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Almacén</th>
                    <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Sucursal</th>
                    <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Código</th>
                    <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Ubicación</th>
                    <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Estado</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($warehouses as $warehouse)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-5 py-3 font-medium text-gray-900">{{ $warehouse->name }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ $warehouse->branch?->name ?? '—' }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ $warehouse->code ?? '—' }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ $warehouse->location ?? '—' }}</td>
                        <td class="px-5 py-3">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                                {{ $warehouse->is_active ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $warehouse->is_active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('inventory.warehouses.edit', $warehouse) }}"
                                    class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Editar</a>
                                <button wire:click="confirmDelete({{ $warehouse->id }})"
                                    class="text-xs text-red-500 hover:text-red-700 font-medium">Eliminar</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-gray-400 text-sm">No se encontraron almacenes.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($warehouses->hasPages())
            <div class="px-5 py-3 border-t border-gray-100">{{ $warehouses->links() }}</div>
        @endif
    </div>

    @if($confirmingDelete)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl border border-gray-200 p-6 w-full max-w-sm mx-4">
                <h3 class="font-medium text-gray-900 mb-1">¿Eliminar almacén?</h3>
                <p class="text-sm text-gray-500 mb-5">Esta acción eliminará el stock registrado en este almacén.</p>
                <div class="flex gap-3 justify-end">
                    <button wire:click="cancelDelete" class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50">Cancelar</button>
                    <button wire:click="delete" class="px-4 py-2 text-sm bg-red-600 hover:bg-red-700 text-white rounded-lg">Sí, eliminar</button>
                </div>
            </div>
        </div>
    @endif
</div>
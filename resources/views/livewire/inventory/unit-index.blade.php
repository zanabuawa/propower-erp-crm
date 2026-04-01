<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl font-medium text-gray-900">Unidades de medida</h1>
            <p class="text-sm text-gray-500 mt-0.5">Define las unidades para tus productos</p>
        </div>
        <a wire:navigate href="{{ route('inventory.units.create') }}"
            class="inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
            + Nueva unidad
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-4">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar unidad..."
            class="w-full sm:w-80 border border-gray-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[400px]">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50">
                        <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Nombre</th>
                        <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Abreviación</th>
                        <th class="text-left px-5 py-3 text-xs font-medium text-gray-500 hidden sm:table-cell">Productos</th>
                        <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Estado</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($units as $unit)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-5 py-3 font-medium text-gray-900">{{ $unit->name }}</td>
                            <td class="px-5 py-3">
                                <span class="bg-gray-100 text-gray-700 text-xs px-2 py-0.5 rounded font-mono">
                                    {{ $unit->abbreviation }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-gray-600 hidden sm:table-cell">{{ $unit->products_count }}</td>
                            <td class="px-5 py-3">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                                    {{ $unit->is_active ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $unit->is_active ? 'Activa' : 'Inactiva' }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <a wire:navigate href="{{ route('inventory.units.edit', $unit) }}"
                                        class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Editar</a>
                                    <button wire:click="confirmDelete({{ $unit->id }})"
                                        class="text-xs text-red-500 hover:text-red-700 font-medium">Eliminar</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-gray-400 text-sm">No se encontraron unidades.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($units->hasPages())
            <div class="px-5 py-3 border-t border-gray-100">{{ $units->links() }}</div>
        @endif
    </div>

    @if($confirmingDelete)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-xl border border-gray-200 p-6 w-full max-w-sm">
                <h3 class="font-medium text-gray-900 mb-1">¿Eliminar unidad?</h3>
                <p class="text-sm text-gray-500 mb-5">Los productos asociados quedarán sin unidad de medida.</p>
                <div class="flex gap-3 justify-end">
                    <button wire:click="cancelDelete" class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50">Cancelar</button>
                    <button wire:click="delete" class="px-4 py-2 text-sm bg-red-600 hover:bg-red-700 text-white rounded-lg">Sí, eliminar</button>
                </div>
            </div>
        </div>
    @endif
</div>

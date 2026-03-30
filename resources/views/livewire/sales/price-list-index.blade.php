<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-medium text-gray-900">Listas de precios</h1>
            <p class="text-sm text-gray-500 mt-0.5">Gestiona tus listas de precios por cliente</p>
        </div>
        <a href="{{ route('sales.price-lists.create') }}"
            class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
            + Nueva lista
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50">
                    <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Nombre</th>
                    <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Moneda</th>
                    <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Productos</th>
                    <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Vigencia</th>
                    <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Estado</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($priceLists as $list)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <span class="font-medium text-gray-900">{{ $list->name }}</span>
                                @if($list->is_default)
                                    <span class="text-xs bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded-full">Predeterminada</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-5 py-3 text-gray-600">{{ $list->currency }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ $list->items_count }}</td>
                        <td class="px-5 py-3 text-gray-600">
                            @if($list->valid_from || $list->valid_to)
                                {{ $list->valid_from?->format('d/m/Y') ?? '—' }} — {{ $list->valid_to?->format('d/m/Y') ?? '—' }}
                            @else
                                Sin vencimiento
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                                {{ $list->is_active ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $list->is_active ? 'Activa' : 'Inactiva' }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('sales.price-lists.edit', $list) }}"
                                    class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Editar</a>
                                <button wire:click="confirmDelete({{ $list->id }})"
                                    class="text-xs text-red-500 hover:text-red-700 font-medium">Eliminar</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-gray-400 text-sm">
                            No se encontraron listas de precios.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($priceLists->hasPages())
            <div class="px-5 py-3 border-t border-gray-100">{{ $priceLists->links() }}</div>
        @endif
    </div>

    @if($confirmingDelete)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl border border-gray-200 p-6 w-full max-w-sm mx-4">
                <h3 class="font-medium text-gray-900 mb-1">¿Eliminar lista?</h3>
                <p class="text-sm text-gray-500 mb-5">Esta acción no se puede deshacer.</p>
                <div class="flex gap-3 justify-end">
                    <button wire:click="cancelDelete" class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50">Cancelar</button>
                    <button wire:click="delete" class="px-4 py-2 text-sm bg-red-600 hover:bg-red-700 text-white rounded-lg">Sí, eliminar</button>
                </div>
            </div>
        </div>
    @endif
</div>
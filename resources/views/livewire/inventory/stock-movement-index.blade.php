<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-medium text-gray-900">Movimientos de stock</h1>
            <p class="text-sm text-gray-500 mt-0.5">Entradas, salidas, ajustes y transferencias</p>
        </div>
        <a href="{{ route('inventory.movements.create') }}"
            class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
            + Nuevo movimiento
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-wrap gap-3 mb-4">
        <input wire:model.live.debounce.300ms="search" type="text"
            placeholder="Buscar por folio o referencia..."
            class="flex-1 min-w-[180px] border border-gray-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
        <select wire:model.live="filterType"
            class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
            <option value="">Todos los tipos</option>
            <option value="entry">Entrada</option>
            <option value="exit">Salida</option>
            <option value="adjustment">Ajuste</option>
            <option value="transfer">Transferencia</option>
            <option value="return">Devolución</option>
        </select>
        <select wire:model.live="filterStatus"
            class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
            <option value="">Todos los estados</option>
            <option value="confirmed">Confirmado</option>
            <option value="draft">Borrador</option>
            <option value="cancelled">Cancelado</option>
        </select>
        <input wire:model.live="dateFrom" type="date"
            class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
        <input wire:model.live="dateTo" type="date"
            class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50">
                    <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Folio</th>
                    <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Tipo</th>
                    <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Almacén</th>
                    <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Productos</th>
                    <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Usuario</th>
                    <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Fecha</th>
                    <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Estado</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($movements as $movement)
                    @php
                        $typeColors = [
                            'entry'      => 'bg-green-50 text-green-700',
                            'exit'       => 'bg-red-50 text-red-700',
                            'adjustment' => 'bg-amber-50 text-amber-700',
                            'transfer'   => 'bg-blue-50 text-blue-700',
                            'return'     => 'bg-purple-50 text-purple-700',
                        ];
                        $statusColors = [
                            'confirmed' => 'bg-green-50 text-green-700',
                            'draft'     => 'bg-gray-100 text-gray-500',
                            'cancelled' => 'bg-red-50 text-red-700',
                        ];
                    @endphp
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-5 py-3">
                            <span class="font-mono text-xs font-medium text-gray-900">{{ $movement->folio }}</span>
                        </td>
                        <td class="px-5 py-3">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $typeColors[$movement->type] ?? '' }}">
                                {{ \App\Models\StockMovement::TYPES[$movement->type] ?? $movement->type }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-gray-600">
                            <p>{{ $movement->warehouse?->name ?? '—' }}</p>
                            @if($movement->warehouseDestination)
                                <p class="text-xs text-gray-400">→ {{ $movement->warehouseDestination->name }}</p>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-gray-600">{{ $movement->items_count }} producto(s)</td>
                        <td class="px-5 py-3 text-gray-600">{{ $movement->user?->name ?? '—' }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ $movement->moved_at->format('d/m/Y H:i') }}</td>
                        <td class="px-5 py-3">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$movement->status] ?? '' }}">
                                {{ \App\Models\StockMovement::STATUS[$movement->status] ?? $movement->status }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route('inventory.movements.show', $movement) }}"
                                class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Ver detalle</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-5 py-10 text-center text-gray-400 text-sm">
                            No se encontraron movimientos.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($movements->hasPages())
            <div class="px-5 py-3 border-t border-gray-100">
                {{ $movements->links() }}
            </div>
        @endif
    </div>
</div>
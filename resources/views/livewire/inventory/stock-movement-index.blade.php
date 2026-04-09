<div>
    <x-page-header title="Movimientos de stock" description="Entradas, salidas, ajustes y transferencias">
        <x-slot:actions>
            <a wire:navigate href="{{ route('inventory.movements.create') }}"
                class="inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nuevo movimiento
            </a>
        </x-slot:actions>
    </x-page-header>

    <x-alert />

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
        <div class="relative sm:col-span-2">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input wire:model.live.debounce.300ms="search" type="text"
                placeholder="Buscar por folio o referencia..."
                aria-label="Buscar movimientos"
                class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-transparent transition">
        </div>
        <select wire:model.live="filterType" aria-label="Filtrar por tipo"
            class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
            <option value="">Todos los tipos</option>
            <option value="entry">Entrada</option>
            <option value="exit">Salida</option>
            <option value="adjustment">Ajuste</option>
            <option value="transfer">Transferencia</option>
            <option value="return">Devolución</option>
        </select>
        <select wire:model.live="filterStatus" aria-label="Filtrar por estado"
            class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
            <option value="">Todos los estados</option>
            <option value="confirmed">Confirmado</option>
            <option value="draft">Borrador</option>
            <option value="cancelled">Cancelado</option>
        </select>
        <input wire:model.live="dateFrom" type="date" aria-label="Desde fecha"
            class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
        <input wire:model.live="dateTo" type="date" aria-label="Hasta fecha"
            class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[640px]">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Folio</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Tipo</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Almacén</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden sm:table-cell">Productos</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden md:table-cell">Usuario</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden md:table-cell">Fecha</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Estado</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($movements as $movement)
                        @php
                            $typeColors = [
                                'entry'      => 'bg-emerald-50 text-emerald-700',
                                'exit'       => 'bg-red-50 text-red-700',
                                'adjustment' => 'bg-amber-50 text-amber-700',
                                'transfer'   => 'bg-blue-50 text-blue-700',
                                'return'     => 'bg-purple-50 text-purple-700',
                            ];
                            $statusColors = [
                                'confirmed' => 'bg-emerald-50 text-emerald-700',
                                'draft'     => 'bg-gray-100 text-gray-500',
                                'cancelled' => 'bg-red-50 text-red-700',
                            ];
                        @endphp
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-5 py-3">
                                <span class="font-mono text-xs font-medium text-gray-900">{{ $movement->folio }}</span>
                                <p class="text-xs text-gray-400 md:hidden mt-0.5">{{ $movement->moved_at->format('d/m/Y') }}</p>
                            </td>
                            <td class="px-5 py-3">
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium {{ $typeColors[$movement->type] ?? 'bg-gray-100 text-gray-500' }}">
                                    {{ \App\Models\StockMovement::TYPES[$movement->type] ?? $movement->type }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-gray-700">
                                <p>{{ $movement->warehouse?->name ?? '—' }}</p>
                                @if($movement->warehouseDestination)
                                    <p class="text-xs text-gray-400">→ {{ $movement->warehouseDestination->name }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-gray-600 hidden sm:table-cell">{{ $movement->items_count }} producto(s)</td>
                            <td class="px-5 py-3 text-gray-600 hidden md:table-cell">{{ $movement->user?->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-gray-500 text-xs hidden md:table-cell">{{ $movement->moved_at->format('d/m/Y H:i') }}</td>
                            <td class="px-5 py-3">
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$movement->status] ?? 'bg-gray-100 text-gray-500' }}">
                                    {{ \App\Models\StockMovement::STATUS[$movement->status] ?? $movement->status }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <a wire:navigate href="{{ route('inventory.movements.show', $movement) }}"
                                    class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Ver →</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8"><x-empty-state message="No se encontraron movimientos de stock." /></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($movements->hasPages())
            <div class="px-5 py-3 border-t border-gray-100">{{ $movements->links() }}</div>
        @endif
    </div>
</div>

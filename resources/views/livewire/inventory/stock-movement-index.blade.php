<div>
    <x-page-header title="Movimientos de stock" description="Entradas, salidas, ajustes y transferencias">
        <x-slot:actions>
            <a wire:navigate href="{{ route('inventory.movements.create') }}"
                class="inline-flex items-center justify-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-medium px-5 py-2 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nuevo movimiento
            </a>
        </x-slot:actions>
    </x-page-header>

    <x-alert />

    {{-- Filtros --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-5 mb-6 shadow-sm">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="relative sm:col-span-2">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input wire:model.live.debounce.300ms="search" type="text"
                    placeholder="Buscar por folio o referencia..."
                    aria-label="Buscar movimientos"
                    class="w-full pl-9 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
            </div>
            <div class="relative">
                <select wire:model.live="filterType" aria-label="Filtrar por tipo"
                    class="w-full px-4 py-2.5 pr-10 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all hover:bg-gray-100 appearance-none">
                    <option value="">Todos los tipos</option>
                    <option value="entry">Entrada</option>
                    <option value="exit">Salida</option>
                    <option value="adjustment">Ajuste</option>
                    <option value="transfer">Transferencia</option>
                    <option value="return">Devolución</option>
                </select>
                <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
            <div class="relative">
                <select wire:model.live="filterStatus" aria-label="Filtrar por estado"
                    class="w-full px-4 py-2.5 pr-10 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all hover:bg-gray-100 appearance-none">
                    <option value="">Todos los estados</option>
                    <option value="confirmed">Confirmado</option>
                    <option value="draft">Borrador</option>
                    <option value="cancelled">Cancelado</option>
                </select>
                <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
            <div class="flex items-center gap-2">
                <label class="text-xs font-medium text-gray-500 whitespace-nowrap">Desde</label>
                <input wire:model.live="dateFrom" type="date" aria-label="Desde fecha"
                    class="flex-1 border border-gray-200 rounded-xl px-3 py-2.5 text-sm bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
            </div>
            <div class="flex items-center gap-2">
                <label class="text-xs font-medium text-gray-500 whitespace-nowrap">Hasta</label>
                <input wire:model.live="dateTo" type="date" aria-label="Hasta fecha"
                    class="flex-1 border border-gray-200 rounded-xl px-3 py-2.5 text-sm bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[640px]">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="text-left px-5 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Folio</th>
                        <th class="text-left px-5 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Tipo</th>
                        <th class="text-left px-5 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Almacén</th>
                        <th class="text-left px-5 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider hidden sm:table-cell">Productos</th>
                        <th class="text-left px-5 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider hidden md:table-cell">Usuario</th>
                        <th class="text-left px-5 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider hidden md:table-cell">Fecha</th>
                        <th class="text-left px-5 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Estado</th>
                        <th class="px-5 py-4 w-16"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($movements as $movement)
                        @php
                            $typeColors = [
                                'entry'      => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                'exit'       => 'bg-red-50 text-red-700 border-red-100',
                                'adjustment' => 'bg-amber-50 text-amber-700 border-amber-100',
                                'transfer'   => 'bg-blue-50 text-blue-700 border-blue-100',
                                'return'     => 'bg-purple-50 text-purple-700 border-purple-100',
                            ];
                            $statusColors = [
                                'confirmed' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                'draft'     => 'bg-gray-100 text-gray-500 border-gray-200',
                                'cancelled' => 'bg-red-50 text-red-700 border-red-100',
                            ];
                        @endphp
                        <tr class="hover:bg-gray-50/70 transition-colors duration-150 group">
                            <td class="px-5 py-4">
                                <span class="font-mono text-xs font-semibold text-gray-900">{{ $movement->folio }}</span>
                                <p class="text-xs text-gray-400 md:hidden mt-0.5">{{ $movement->moved_at->format('d/m/Y') }}</p>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-medium border {{ $typeColors[$movement->type] ?? 'bg-gray-100 text-gray-500 border-gray-200' }}">
                                    {{ \App\Models\StockMovement::TYPES[$movement->type] ?? $movement->type }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <p class="font-medium text-gray-800">{{ $movement->warehouse?->name ?? '—' }}</p>
                                @if($movement->warehouseDestination)
                                    <p class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                        </svg>
                                        {{ $movement->warehouseDestination->name }}
                                    </p>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-gray-600 hidden sm:table-cell">
                                <span class="inline-flex items-center justify-center min-w-[28px] px-2 py-0.5 text-xs font-semibold text-indigo-600 bg-indigo-50 rounded-lg">
                                    {{ $movement->items_count }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-gray-600 hidden md:table-cell">{{ $movement->user?->name ?? '—' }}</td>
                            <td class="px-5 py-4 text-gray-500 text-xs hidden md:table-cell whitespace-nowrap">{{ $movement->moved_at->format('d/m/Y H:i') }}</td>
                            <td class="px-5 py-4">
                                <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-medium border {{ $statusColors[$movement->status] ?? 'bg-gray-100 text-gray-500 border-gray-200' }}">
                                    {{ \App\Models\StockMovement::STATUS[$movement->status] ?? $movement->status }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a wire:navigate href="{{ route('inventory.movements.show', $movement) }}"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-indigo-500 hover:text-white hover:bg-indigo-600 bg-indigo-50 opacity-0 group-hover:opacity-100 transition-all duration-200 cursor-pointer"
                                    aria-label="Ver detalle">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center">
                                <x-empty-state message="No se encontraron movimientos de stock." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($movements->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">{{ $movements->links() }}</div>
        @endif
    </div>
</div>

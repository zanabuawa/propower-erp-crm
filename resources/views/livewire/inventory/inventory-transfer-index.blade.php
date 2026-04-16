<div>
    <x-page-header title="Transferencias de inventario" description="Movimientos entre almacenes">
        <x-slot:actions>
            @can('adjust inventory')
            <a wire:navigate href="{{ route('inventory.transfers.create') }}"
                class="inline-flex items-center justify-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-medium px-5 py-2 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nueva transferencia
            </a>
            @endcan
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
                    aria-label="Buscar transferencias"
                    class="w-full pl-9 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
            </div>
            <div class="relative">
                <select wire:model.live="filterStatus" aria-label="Filtrar por estado"
                    class="w-full px-4 py-2.5 pr-10 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all hover:bg-gray-100 appearance-none">
                    <option value="">Todos los estados</option>
                    @foreach(\App\Models\StockMovement::TRANSFER_STATUSES as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
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
                        <th class="text-left px-5 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Origen</th>
                        <th class="text-left px-5 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Destino</th>
                        <th class="text-left px-5 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider hidden sm:table-cell">Productos</th>
                        <th class="text-left px-5 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider hidden md:table-cell">Usuario</th>
                        <th class="text-left px-5 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider hidden md:table-cell">Fecha</th>
                        <th class="text-left px-5 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Estado</th>
                        <th class="px-5 py-4 w-16"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($transfers as $transfer)
                        @php
                            $statusColors = [
                                'requested'          => 'bg-blue-50 text-blue-700 border-blue-100',
                                'in_transit'         => 'bg-amber-50 text-amber-700 border-amber-100',
                                'partially_received' => 'bg-orange-50 text-orange-700 border-orange-100',
                                'completed'          => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                'confirmed'          => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                'cancelled'          => 'bg-red-50 text-red-700 border-red-100',
                            ];
                        @endphp
                        <tr class="hover:bg-gray-50/70 transition-colors duration-150 group">
                            <td class="px-5 py-4">
                                <span class="font-mono text-xs font-semibold text-gray-900">{{ $transfer->folio }}</span>
                                <p class="text-xs text-gray-400 md:hidden mt-0.5">{{ $transfer->moved_at->format('d/m/Y') }}</p>
                            </td>
                            <td class="px-5 py-4">
                                <p class="font-semibold text-gray-800">{{ $transfer->warehouse?->name ?? '—' }}</p>
                                <p class="text-xs text-gray-400">{{ $transfer->warehouse?->branch?->name ?? '' }}</p>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-start gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                    </svg>
                                    <div>
                                        <p class="font-semibold text-gray-800">{{ $transfer->warehouseDestination?->name ?? '—' }}</p>
                                        <p class="text-xs text-gray-400">{{ $transfer->warehouseDestination?->branch?->name ?? '' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-gray-600 hidden sm:table-cell">
                                <span class="inline-flex items-center justify-center min-w-[28px] px-2 py-0.5 text-xs font-semibold text-indigo-600 bg-indigo-50 rounded-lg">
                                    {{ $transfer->items_count }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-gray-600 hidden md:table-cell">{{ $transfer->user?->name ?? '—' }}</td>
                            <td class="px-5 py-4 text-gray-500 text-xs hidden md:table-cell whitespace-nowrap">{{ $transfer->moved_at->format('d/m/Y H:i') }}</td>
                            <td class="px-5 py-4">
                                <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-medium border {{ $statusColors[$transfer->status] ?? 'bg-gray-100 text-gray-500 border-gray-200' }}">
                                    {{ (\App\Models\StockMovement::TRANSFER_STATUSES + \App\Models\StockMovement::STATUS)[$transfer->status] ?? $transfer->status }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a wire:navigate href="{{ route('inventory.movements.show', $transfer) }}"
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
                                <x-empty-state message="No se encontraron transferencias." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transfers->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">{{ $transfers->links() }}</div>
        @endif
    </div>
</div>

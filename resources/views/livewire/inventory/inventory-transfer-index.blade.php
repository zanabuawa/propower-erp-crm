<div>
    <x-page-header title="Transferencias de inventario" description="Movimientos entre almacenes">
        <x-slot:actions>
            @can('adjust inventory')
            <a wire:navigate href="{{ route('inventory.transfers.create') }}"
                class="inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nueva transferencia
            </a>
            @endcan
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
                aria-label="Buscar transferencias"
                class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-transparent transition">
        </div>
        <select wire:model.live="filterStatus" aria-label="Filtrar por estado"
            class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
            <option value="">Todos los estados</option>
            @foreach(\App\Models\StockMovement::TRANSFER_STATUSES as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </select>
        <div></div>
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
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Origen</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Destino</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden sm:table-cell">Productos</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden md:table-cell">Usuario</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden md:table-cell">Fecha</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Estado</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($transfers as $transfer)
                        @php
                            $statusColors = [
                                'requested'          => 'bg-blue-50 text-blue-700',
                                'in_transit'         => 'bg-amber-50 text-amber-700',
                                'partially_received' => 'bg-orange-50 text-orange-700',
                                'completed'          => 'bg-emerald-50 text-emerald-700',
                                'confirmed'          => 'bg-emerald-50 text-emerald-700',
                                'cancelled'          => 'bg-red-50 text-red-700',
                            ];
                        @endphp
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-5 py-3">
                                <span class="font-mono text-xs font-medium text-gray-900">{{ $transfer->folio }}</span>
                                <p class="text-xs text-gray-400 md:hidden mt-0.5">{{ $transfer->moved_at->format('d/m/Y') }}</p>
                            </td>
                            <td class="px-5 py-3 text-gray-700">
                                <p class="font-medium">{{ $transfer->warehouse?->name ?? '—' }}</p>
                                <p class="text-xs text-gray-400">{{ $transfer->warehouse?->branch?->name ?? '' }}</p>
                            </td>
                            <td class="px-5 py-3 text-gray-700">
                                <p class="font-medium">{{ $transfer->warehouseDestination?->name ?? '—' }}</p>
                                <p class="text-xs text-gray-400">{{ $transfer->warehouseDestination?->branch?->name ?? '' }}</p>
                            </td>
                            <td class="px-5 py-3 text-gray-600 hidden sm:table-cell">{{ $transfer->items_count }} producto(s)</td>
                            <td class="px-5 py-3 text-gray-600 hidden md:table-cell">{{ $transfer->user?->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-gray-500 text-xs hidden md:table-cell">{{ $transfer->moved_at->format('d/m/Y H:i') }}</td>
                            <td class="px-5 py-3">
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$transfer->status] ?? 'bg-gray-100 text-gray-500' }}">
                                    {{ (\App\Models\StockMovement::TRANSFER_STATUSES + \App\Models\StockMovement::STATUS)[$transfer->status] ?? $transfer->status }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <a wire:navigate href="{{ route('inventory.movements.show', $transfer) }}"
                                    class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Ver →</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8"><x-empty-state message="No se encontraron transferencias." /></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transfers->hasPages())
            <div class="px-5 py-3 border-t border-gray-100">{{ $transfers->links() }}</div>
        @endif
    </div>
</div>

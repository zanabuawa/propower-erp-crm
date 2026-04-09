<div class="space-y-5">

    {{-- Encabezado --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-medium text-gray-900">Lotes de inventario</h1>
            <p class="text-sm text-gray-500 mt-0.5">Trazabilidad PEPS por lote</p>
        </div>
        <a wire:navigate href="{{ route('inventory.movements.create') }}"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-medium transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/>
            </svg>
            Nueva entrada
        </a>
    </div>

    {{-- Filtros --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 space-y-3">
        <div class="flex flex-wrap gap-3">
            {{-- Búsqueda --}}
            <div class="relative flex-1 min-w-48">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input wire:model.live.debounce.300ms="search"
                    type="text" placeholder="Buscar por lote, código de barras, referencia…"
                    class="w-full pl-9 pr-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
            </div>

            {{-- Estado --}}
            <select wire:model.live="filterStatus"
                class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                <option value="">Todos los estados</option>
                @foreach($statuses as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>

            {{-- Almacén --}}
            <select wire:model.live="filterWarehouse"
                class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                <option value="">Todos los almacenes</option>
                @foreach($warehouses as $wh)
                    <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                @endforeach
            </select>

            {{-- Limpiar --}}
            <button wire:click="clearFilters"
                class="px-3 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition text-gray-500">
                Limpiar
            </button>
        </div>

        {{-- Búsqueda de producto --}}
        <div class="relative max-w-sm" x-data>
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0H4"/>
            </svg>
            <input wire:model.live.debounce.300ms="productSearch"
                type="text" placeholder="Filtrar por producto…"
                class="w-full pl-9 pr-8 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
            @if($filterProduct)
                <button wire:click="clearProductFilter" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            @endif
            @if(!empty($productResults))
                <div class="absolute top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg z-20 overflow-hidden">
                    @foreach($productResults as $p)
                        <button type="button" wire:click="selectProduct({{ $p['id'] }}, '{{ addslashes($p['name']) }}')"
                            class="w-full text-left px-3 py-2.5 hover:bg-gray-50 text-sm border-b border-gray-100 last:border-0">
                            <p class="font-medium text-gray-800">{{ $p['name'] }}</p>
                            <p class="text-xs text-gray-400 font-mono">{{ $p['sku'] }}</p>
                        </button>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Tabla --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">

        {{-- Mobile --}}
        <div class="divide-y divide-gray-100 lg:hidden">
            @forelse($lots as $lot)
                @php
                    $statusColors = [
                        'active'   => 'bg-green-100 text-green-700',
                        'depleted' => 'bg-gray-100 text-gray-500',
                        'expired'  => 'bg-red-100 text-red-600',
                    ];
                    $isExpiringSoon = $lot->expiry_date && $lot->expiry_date->diffInDays() < 30 && $lot->expiry_date->isFuture();
                @endphp
                <a wire:navigate href="{{ route('inventory.lots.show', $lot) }}"
                    class="block p-4 hover:bg-gray-50 transition">
                    <div class="flex items-center justify-between gap-2 mb-1">
                        <span class="font-mono text-sm font-medium text-gray-800">{{ $lot->lot_number }}</span>
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $statusColors[$lot->status] ?? 'bg-gray-100 text-gray-500' }}">
                            {{ \App\Models\ProductLot::STATUSES[$lot->status] ?? $lot->status }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-600 truncate">{{ $lot->product->name }}</p>
                    <div class="flex items-center gap-3 mt-1.5 text-xs text-gray-500">
                        <span>{{ $lot->warehouse->name }}</span>
                        <span>·</span>
                        <span>Ingreso: {{ $lot->entry_date->format('d/m/Y') }}</span>
                        <span>·</span>
                        <span class="font-medium {{ (float)$lot->quantity > 0 ? 'text-gray-700' : 'text-red-500' }}">
                            {{ number_format($lot->quantity, 2) }} uds
                        </span>
                    </div>
                    @if($isExpiringSoon)
                        <p class="mt-1 text-xs text-amber-600">Vence: {{ $lot->expiry_date->format('d/m/Y') }}</p>
                    @endif
                </a>
            @empty
                <div class="py-12 text-center text-sm text-gray-400">No se encontraron lotes.</div>
            @endforelse
        </div>

        {{-- Desktop --}}
        <div class="hidden lg:block overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Lote</th>
                        <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Código de barras</th>
                        <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Producto</th>
                        <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Almacén</th>
                        <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Ingreso</th>
                        <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Vencimiento</th>
                        <th class="text-right px-5 py-3 text-xs font-medium text-gray-500">Inicial</th>
                        <th class="text-right px-5 py-3 text-xs font-medium text-gray-500">Disponible</th>
                        <th class="text-right px-5 py-3 text-xs font-medium text-gray-500">Costo unit.</th>
                        <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Estado</th>
                        <th class="w-10"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($lots as $lot)
                        @php
                            $statusColors = [
                                'active'   => 'bg-green-100 text-green-700',
                                'depleted' => 'bg-gray-100 text-gray-500',
                                'expired'  => 'bg-red-100 text-red-600',
                            ];
                            $isExpiringSoon = $lot->expiry_date && $lot->expiry_date->diffInDays() < 30 && $lot->expiry_date->isFuture();
                        @endphp
                        <tr class="hover:bg-gray-50 transition group">
                            <td class="px-5 py-3 font-mono font-medium text-gray-800">{{ $lot->lot_number }}</td>
                            <td class="px-5 py-3 font-mono text-xs text-gray-500">{{ $lot->barcode }}</td>
                            <td class="px-5 py-3">
                                <p class="font-medium text-gray-900">{{ $lot->product->name }}</p>
                                <p class="text-xs text-gray-400 font-mono">{{ $lot->product->sku }}</p>
                            </td>
                            <td class="px-5 py-3 text-gray-600">{{ $lot->warehouse->name }}</td>
                            <td class="px-5 py-3 text-gray-600">{{ $lot->entry_date->format('d/m/Y') }}</td>
                            <td class="px-5 py-3">
                                @if($lot->expiry_date)
                                    <span class="{{ $lot->expiry_date->isPast() ? 'text-red-600 font-medium' : ($isExpiringSoon ? 'text-amber-600 font-medium' : 'text-gray-600') }}">
                                        {{ $lot->expiry_date->format('d/m/Y') }}
                                    </span>
                                    @if($isExpiringSoon)
                                        <span class="ml-1 text-[10px] bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded-full">Pronto</span>
                                    @endif
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right text-gray-600">{{ number_format($lot->initial_quantity, 2) }}</td>
                            <td class="px-5 py-3 text-right font-semibold {{ (float)$lot->quantity > 0 ? 'text-gray-800' : 'text-red-500' }}">
                                {{ number_format($lot->quantity, 2) }}
                            </td>
                            <td class="px-5 py-3 text-right text-gray-600">${{ number_format($lot->unit_cost, 2) }}</td>
                            <td class="px-5 py-3">
                                <span class="text-xs px-2.5 py-1 rounded-full {{ $statusColors[$lot->status] ?? 'bg-gray-100 text-gray-500' }}">
                                    {{ \App\Models\ProductLot::STATUSES[$lot->status] ?? $lot->status }}
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                <a wire:navigate href="{{ route('inventory.lots.show', $lot) }}"
                                    class="text-indigo-500 hover:text-indigo-700 opacity-0 group-hover:opacity-100 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="px-5 py-12 text-center text-sm text-gray-400">
                                No se encontraron lotes con los filtros seleccionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($lots->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">
                {{ $lots->links() }}
            </div>
        @endif
    </div>
</div>

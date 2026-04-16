<div class="space-y-6">

    {{-- Encabezado --}}
    <x-page-header title="Lotes de inventario" description="Trazabilidad FIFO/PEPS por lote de producto">
        <x-slot:actions>
            <a wire:navigate href="{{ route('inventory.movements.create') }}"
                class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-medium px-5 py-2 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nueva entrada
            </a>
        </x-slot:actions>
    </x-page-header>

    {{-- Filtros --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm space-y-3">
        <div class="flex flex-wrap gap-3">
            {{-- Búsqueda --}}
            <div class="relative flex-1 min-w-48">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input wire:model.live.debounce.300ms="search"
                    type="text" placeholder="Buscar por lote, código de barras, referencia…"
                    aria-label="Buscar lotes"
                    class="w-full pl-9 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
            </div>

            {{-- Estado --}}
            <div class="relative">
                <select wire:model.live="filterStatus" aria-label="Filtrar por estado"
                    class="px-4 py-2.5 pr-10 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all hover:bg-gray-100 appearance-none">
                    <option value="">Todos los estados</option>
                    @foreach($statuses as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>

            {{-- Almacén --}}
            <div class="relative">
                <select wire:model.live="filterWarehouse" aria-label="Filtrar por almacén"
                    class="px-4 py-2.5 pr-10 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all hover:bg-gray-100 appearance-none">
                    <option value="">Todos los almacenes</option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                    @endforeach
                </select>
                <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>

            {{-- Limpiar --}}
            <button wire:click="clearFilters"
                class="px-3 py-2.5 text-sm border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors text-gray-500 cursor-pointer">
                Limpiar
            </button>
        </div>

        {{-- Búsqueda de producto --}}
        <div class="relative max-w-sm" x-data>
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            <input wire:model.live.debounce.300ms="productSearch"
                type="text" placeholder="Filtrar por producto…"
                aria-label="Filtrar por producto"
                class="w-full pl-9 pr-8 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
            @if($filterProduct)
                <button wire:click="clearProductFilter" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors cursor-pointer" aria-label="Limpiar filtro de producto">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            @endif
            @if(!empty($productResults))
                <div class="absolute top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-lg z-20 overflow-hidden">
                    @foreach($productResults as $p)
                        <button type="button" wire:click="selectProduct({{ $p['id'] }}, '{{ addslashes($p['name']) }}')"
                            class="w-full text-left px-3 py-2.5 hover:bg-gray-50 text-sm border-b border-gray-100 last:border-0 transition-colors cursor-pointer">
                            <p class="font-medium text-gray-800">{{ $p['name'] }}</p>
                            <p class="text-xs text-gray-400 font-mono">{{ $p['sku'] }}</p>
                        </button>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Tabla --}}
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">

        {{-- Mobile --}}
        <div class="divide-y divide-gray-100 lg:hidden">
            @forelse($lots as $lot)
                @php
                    $statusColors = [
                        'active'   => 'bg-emerald-100 text-emerald-700',
                        'depleted' => 'bg-gray-100 text-gray-500',
                        'expired'  => 'bg-red-100 text-red-600',
                    ];
                    $isExpiringSoon = $lot->expiry_date && $lot->expiry_date->diffInDays() < 30 && $lot->expiry_date->isFuture();
                @endphp
                <a wire:navigate href="{{ route('inventory.lots.show', $lot) }}"
                    class="block p-4 hover:bg-gray-50 transition-colors cursor-pointer">
                    <div class="flex items-center justify-between gap-2 mb-1">
                        <span class="font-mono text-sm font-semibold text-gray-800">{{ $lot->lot_number }}</span>
                        <span class="text-xs px-2.5 py-1 rounded-lg font-medium {{ $statusColors[$lot->status] ?? 'bg-gray-100 text-gray-500' }}">
                            {{ \App\Models\ProductLot::STATUSES[$lot->status] ?? $lot->status }}
                        </span>
                    </div>
                    <p class="text-sm font-medium text-gray-700 truncate">{{ $lot->product->name }}</p>
                    <div class="flex items-center gap-3 mt-1.5 text-xs text-gray-500">
                        <span>{{ $lot->warehouse->name }}</span>
                        <span class="text-gray-300">·</span>
                        <span>Ingreso: {{ $lot->entry_date->format('d/m/Y') }}</span>
                        <span class="text-gray-300">·</span>
                        <span class="font-semibold {{ (float)$lot->quantity > 0 ? 'text-gray-800' : 'text-red-500' }}">
                            {{ number_format($lot->quantity, 2) }} uds
                        </span>
                    </div>
                    @if($isExpiringSoon)
                        <p class="mt-1.5 text-xs text-amber-600 font-medium">Vence: {{ $lot->expiry_date->format('d/m/Y') }}</p>
                    @endif
                </a>
            @empty
                <div class="py-12 text-center">
                    <x-empty-state message="No se encontraron lotes." />
                </div>
            @endforelse
        </div>

        {{-- Desktop --}}
        <div class="hidden lg:block overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="text-left px-5 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Lote</th>
                        <th class="text-left px-5 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Código de barras</th>
                        <th class="text-left px-5 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Producto</th>
                        <th class="text-left px-5 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Almacén</th>
                        <th class="text-left px-5 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Ingreso</th>
                        <th class="text-left px-5 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Vencimiento</th>
                        <th class="text-right px-5 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Inicial</th>
                        <th class="text-right px-5 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Disponible</th>
                        <th class="text-right px-5 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Costo unit.</th>
                        <th class="text-left px-5 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Estado</th>
                        <th class="w-12 px-5 py-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($lots as $lot)
                        @php
                            $statusColors = [
                                'active'   => 'bg-emerald-100 text-emerald-700',
                                'depleted' => 'bg-gray-100 text-gray-500',
                                'expired'  => 'bg-red-100 text-red-600',
                            ];
                            $isExpiringSoon = $lot->expiry_date && $lot->expiry_date->diffInDays() < 30 && $lot->expiry_date->isFuture();
                        @endphp
                        <tr class="hover:bg-gray-50/70 transition-colors duration-150 group">
                            <td class="px-5 py-4 font-mono font-semibold text-gray-800">{{ $lot->lot_number }}</td>
                            <td class="px-5 py-4 font-mono text-xs text-gray-500">{{ $lot->barcode ?? '—' }}</td>
                            <td class="px-5 py-4">
                                <p class="font-semibold text-gray-900">{{ $lot->product->name }}</p>
                                <p class="text-xs text-gray-400 font-mono">{{ $lot->product->sku }}</p>
                            </td>
                            <td class="px-5 py-4 text-gray-600">{{ $lot->warehouse->name }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ $lot->entry_date->format('d/m/Y') }}</td>
                            <td class="px-5 py-4">
                                @if($lot->expiry_date)
                                    <span class="font-medium {{ $lot->expiry_date->isPast() ? 'text-red-600' : ($isExpiringSoon ? 'text-amber-600' : 'text-gray-600') }}">
                                        {{ $lot->expiry_date->format('d/m/Y') }}
                                    </span>
                                    @if($isExpiringSoon)
                                        <span class="ml-1 text-[10px] bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded-lg font-medium">Pronto</span>
                                    @endif
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right text-gray-600">{{ number_format($lot->initial_quantity, 2) }}</td>
                            <td class="px-5 py-4 text-right font-bold {{ (float)$lot->quantity > 0 ? 'text-gray-900' : 'text-red-500' }}">
                                {{ number_format($lot->quantity, 2) }}
                            </td>
                            <td class="px-5 py-4 text-right text-gray-600">${{ number_format($lot->unit_cost, 2) }}</td>
                            <td class="px-5 py-4">
                                <span class="text-xs px-2.5 py-1 rounded-lg font-medium {{ $statusColors[$lot->status] ?? 'bg-gray-100 text-gray-500' }}">
                                    {{ \App\Models\ProductLot::STATUSES[$lot->status] ?? $lot->status }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <a wire:navigate href="{{ route('inventory.lots.show', $lot) }}"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-indigo-500 hover:text-white hover:bg-indigo-600 bg-indigo-50 opacity-0 group-hover:opacity-100 transition-all duration-200 cursor-pointer"
                                    aria-label="Ver detalle">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="px-5 py-12 text-center">
                                <x-empty-state message="No se encontraron lotes con los filtros seleccionados." />
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

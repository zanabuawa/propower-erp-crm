<div class="space-y-5">

    {{-- Encabezado --}}
    <div>
        <h1 class="text-xl font-medium text-gray-900">Kardex PEPS</h1>
        <p class="text-sm text-gray-500 mt-0.5">Tarjeta de almacén por artículo — Primeras Entradas, Primeras Salidas</p>
    </div>

    {{-- Filtros --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 space-y-3">

        {{-- Fila 1: producto, almacén, dirección, tipo --}}
        <div class="flex flex-wrap gap-3">

            {{-- Búsqueda de producto --}}
            <div class="relative flex-1 min-w-56" x-data>
                @if($selectedProductName)
                    <div class="flex items-center gap-2 border border-indigo-300 rounded-lg px-3 py-2 bg-indigo-50">
                        <svg class="w-4 h-4 text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0H4"/>
                        </svg>
                        <span class="text-sm font-medium text-indigo-700 truncate">{{ $selectedProductName }}</span>
                        <button wire:click="clearProduct" class="ml-auto text-indigo-400 hover:text-indigo-600">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                @else
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input wire:model.live.debounce.300ms="productSearch"
                        type="text" placeholder="Buscar artículo…"
                        class="w-full pl-9 pr-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    @if(!empty($productResults))
                        <div class="absolute top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg z-30 overflow-hidden">
                            @foreach($productResults as $p)
                                <button type="button" wire:click="selectProduct({{ $p['id'] }}, '{{ addslashes($p['name']) }}')"
                                    class="w-full text-left px-3 py-2.5 hover:bg-gray-50 text-sm border-b border-gray-100 last:border-0">
                                    <p class="font-medium text-gray-800">{{ $p['name'] }}</p>
                                    <p class="text-xs text-gray-400 font-mono">{{ $p['sku'] }}</p>
                                </button>
                            @endforeach
                        </div>
                    @endif
                @endif
            </div>

            {{-- Almacén --}}
            <select wire:model.live="warehouseId"
                class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                <option value="">Todos los almacenes</option>
                @foreach($warehouses as $wh)
                    <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                @endforeach
            </select>

            {{-- Dirección --}}
            <select wire:model.live="filterDirection"
                class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                <option value="">Entradas y salidas</option>
                <option value="in">Solo entradas</option>
                <option value="out">Solo salidas</option>
            </select>

            {{-- Tipo de movimiento --}}
            <select wire:model.live="filterType"
                class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                <option value="">Todos los tipos</option>
                @foreach($types as $key => $meta)
                    <option value="{{ $key }}">{{ $meta['label'] }}</option>
                @endforeach
            </select>
        </div>

        {{-- Fila 2: fechas + limpiar --}}
        <div class="flex flex-wrap gap-3 items-center">
            <div class="flex items-center gap-2">
                <label class="text-xs text-gray-500 whitespace-nowrap">Desde</label>
                <input wire:model.live="dateFrom" type="date"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
            </div>
            <div class="flex items-center gap-2">
                <label class="text-xs text-gray-500 whitespace-nowrap">Hasta</label>
                <input wire:model.live="dateTo" type="date"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
            </div>
            <button wire:click="clearFilters"
                class="px-3 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition text-gray-500">
                Limpiar filtros
            </button>
        </div>
    </div>

    {{-- Tarjetas de totales --}}
    @if($totals)
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        {{-- Entradas --}}
        <div class="bg-white rounded-xl border border-green-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Cant. entradas</p>
            <p class="text-lg font-semibold text-green-700">{{ number_format($totals->total_in_qty, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-green-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Valor entradas</p>
            <p class="text-lg font-semibold text-green-700">${{ number_format($totals->total_in_value, 2) }}</p>
        </div>
        {{-- Salidas --}}
        <div class="bg-white rounded-xl border border-red-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Cant. salidas</p>
            <p class="text-lg font-semibold text-red-600">{{ number_format($totals->total_out_qty, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-red-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Costo salidas</p>
            <p class="text-lg font-semibold text-red-600">${{ number_format($totals->total_out_value, 2) }}</p>
        </div>
        {{-- Ventas --}}
        <div class="bg-white rounded-xl border border-indigo-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Total facturado</p>
            <p class="text-lg font-semibold text-indigo-700">${{ number_format($totals->total_revenue, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-teal-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Utilidad total</p>
            @php $profit = (float) $totals->total_profit; @endphp
            <p class="text-lg font-semibold {{ $profit >= 0 ? 'text-teal-600' : 'text-red-600' }}">
                ${{ number_format($profit, 2) }}
            </p>
        </div>
    </div>
    @endif

    {{-- Tabla kardex --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">

        @if($entries->isEmpty())
            <div class="py-16 text-center">
                <svg class="w-10 h-10 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="text-sm text-gray-400">No hay registros PEPS con los filtros seleccionados.</p>
                @unless($productId)
                    <p class="text-xs text-gray-400 mt-1">Selecciona un artículo para ver su tarjeta de almacén.</p>
                @endunless
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-xs min-w-[1100px]">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="text-left px-4 py-3 font-medium text-gray-500">Fecha</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-500">Artículo</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-500">Lote</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-500">Almacén</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-500">Tipo</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-500">Referencia</th>
                            {{-- Entrada --}}
                            <th class="text-right px-4 py-3 font-medium text-green-600 bg-green-50/50">Cant. entrada</th>
                            @can('view prices')
                            <th class="text-right px-4 py-3 font-medium text-green-600 bg-green-50/50">P. obtención</th>
                            <th class="text-right px-4 py-3 font-medium text-green-600 bg-green-50/50">Total entrada</th>
                            @endcan
                            {{-- Salida --}}
                            <th class="text-right px-4 py-3 font-medium text-red-500 bg-red-50/50">Cant. salida</th>
                            @can('view prices')
                            <th class="text-right px-4 py-3 font-medium text-red-500 bg-red-50/50">Costo PEPS</th>
                            <th class="text-right px-4 py-3 font-medium text-red-500 bg-red-50/50">P. venta</th>
                            <th class="text-right px-4 py-3 font-medium text-red-500 bg-red-50/50">Total venta</th>
                            <th class="text-right px-4 py-3 font-medium text-teal-600 bg-teal-50/50">Utilidad</th>
                            <th class="text-right px-4 py-3 font-medium text-teal-600 bg-teal-50/50">% Util.</th>
                            @endcan
                            {{-- Saldo --}}
                            <th class="text-right px-4 py-3 font-medium text-gray-600 bg-gray-100">Saldo cant.</th>
                            @can('view prices')
                            <th class="text-right px-4 py-3 font-medium text-gray-600 bg-gray-100">Saldo valor</th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($entries as $entry)
                            @php
                                $typeInfo = \App\Models\PepsKardex::MOVEMENT_TYPES[$entry->movement_type] ?? ['label' => $entry->movement_type, 'color' => 'gray'];
                                $isIn     = $entry->direction === 'in';
                                $colorMap = [
                                    'green'  => 'bg-green-100 text-green-700',
                                    'red'    => 'bg-red-100 text-red-600',
                                    'orange' => 'bg-orange-100 text-orange-600',
                                    'teal'   => 'bg-teal-100 text-teal-700',
                                    'blue'   => 'bg-blue-100 text-blue-700',
                                    'violet' => 'bg-violet-100 text-violet-700',
                                    'gray'   => 'bg-gray-100 text-gray-500',
                                ];
                                $badgeClass = $colorMap[$typeInfo['color']] ?? 'bg-gray-100 text-gray-500';
                            @endphp
                            <tr class="hover:bg-gray-50 transition {{ $isIn ? 'bg-green-50/20' : '' }}">
                                <td class="px-4 py-2.5 whitespace-nowrap text-gray-600">
                                    {{ $entry->moved_at->format('d/m/Y') }}
                                    <span class="block text-gray-400">{{ $entry->moved_at->format('H:i') }}</span>
                                </td>
                                <td class="px-4 py-2.5">
                                    <p class="font-medium text-gray-800">{{ $entry->product->name }}</p>
                                    <p class="text-gray-400 font-mono">{{ $entry->product->sku }}</p>
                                </td>
                                <td class="px-4 py-2.5 font-mono text-gray-600">
                                    {{ $entry->lot_number ?? '—' }}
                                </td>
                                <td class="px-4 py-2.5 text-gray-600">{{ $entry->warehouse->name }}</td>
                                <td class="px-4 py-2.5">
                                    <span class="px-2 py-0.5 rounded-full {{ $badgeClass }}">{{ $typeInfo['label'] }}</span>
                                </td>
                                <td class="px-4 py-2.5 font-mono text-gray-500">{{ $entry->reference ?? '—' }}</td>

                                {{-- Entradas --}}
                                <td class="px-4 py-2.5 text-right font-medium text-green-700 bg-green-50/30">
                                    {{ $isIn ? number_format($entry->quantity, 4) : '—' }}
                                </td>
                                @can('view prices')
                                <td class="px-4 py-2.5 text-right text-green-600 bg-green-50/30">
                                    {{ $isIn ? '$' . number_format($entry->unit_cost, 4) : '—' }}
                                </td>
                                <td class="px-4 py-2.5 text-right font-medium text-green-700 bg-green-50/30">
                                    {{ $isIn ? '$' . number_format($entry->total_cost, 2) : '—' }}
                                </td>
                                @endcan

                                {{-- Salidas --}}
                                <td class="px-4 py-2.5 text-right font-medium text-red-600 bg-red-50/30">
                                    {{ !$isIn ? number_format($entry->quantity, 4) : '—' }}
                                </td>
                                @can('view prices')
                                <td class="px-4 py-2.5 text-right text-red-500 bg-red-50/30">
                                    {{ !$isIn ? '$' . number_format($entry->unit_cost, 4) : '—' }}
                                </td>
                                <td class="px-4 py-2.5 text-right text-gray-700 bg-red-50/30">
                                    {{ (!$isIn && $entry->unit_price) ? '$' . number_format($entry->unit_price, 4) : '—' }}
                                </td>
                                <td class="px-4 py-2.5 text-right font-medium text-gray-800 bg-red-50/30">
                                    {{ (!$isIn && $entry->total_revenue) ? '$' . number_format($entry->total_revenue, 2) : '—' }}
                                </td>

                                {{-- Utilidad --}}
                                <td class="px-4 py-2.5 text-right font-semibold bg-teal-50/30
                                    {{ !$isIn && $entry->profit !== null
                                        ? ((float)$entry->profit >= 0 ? 'text-teal-600' : 'text-red-600')
                                        : 'text-gray-300' }}">
                                    {{ (!$isIn && $entry->profit !== null) ? '$' . number_format($entry->profit, 2) : '—' }}
                                </td>
                                <td class="px-4 py-2.5 text-right bg-teal-50/30
                                    {{ !$isIn && $entry->profit_pct !== null
                                        ? ((float)$entry->profit_pct >= 0 ? 'text-teal-600' : 'text-red-600')
                                        : 'text-gray-300' }}">
                                    {{ (!$isIn && $entry->profit_pct !== null) ? number_format($entry->profit_pct, 2) . '%' : '—' }}
                                </td>
                                @endcan

                                {{-- Saldo --}}
                                <td class="px-4 py-2.5 text-right font-semibold text-gray-800 bg-gray-50">
                                    {{ number_format($entry->balance_quantity, 4) }}
                                </td>
                                @can('view prices')
                                <td class="px-4 py-2.5 text-right font-semibold text-gray-800 bg-gray-50">
                                    ${{ number_format($entry->balance_value, 2) }}
                                </td>
                                @endcan
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($entries->hasPages())
                <div class="px-5 py-4 border-t border-gray-100">
                    {{ $entries->links() }}
                </div>
            @endif
        @endif
    </div>

</div>

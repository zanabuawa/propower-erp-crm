<div class="space-y-6">

    {{-- Encabezado --}}
    <x-page-header title="Kardex PEPS" description="Tarjeta de almacén — Primeras Entradas, Primeras Salidas">
        <x-slot:actions>
            <a wire:navigate href="{{ route('inventory.lots.index') }}"
                class="inline-flex items-center gap-2 text-sm bg-white hover:bg-gray-50 border border-gray-300 hover:border-gray-400 px-3 py-2 rounded-xl transition-all duration-200 text-gray-700 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                Ver lotes
            </a>
        </x-slot:actions>
    </x-page-header>

    {{-- Filtros --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm space-y-4">

        {{-- Fila 1: producto, almacén, dirección, tipo --}}
        <div class="flex flex-wrap gap-3">

            {{-- Búsqueda de producto --}}
            <div class="relative flex-1 min-w-56" x-data>
                @if($selectedProductName)
                    <div class="flex items-center gap-2 border border-indigo-300 rounded-xl px-3 py-2.5 bg-indigo-50">
                        <svg class="w-4 h-4 text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        <span class="text-sm font-medium text-indigo-700 truncate">{{ $selectedProductName }}</span>
                        <button wire:click="clearProduct" class="ml-auto text-indigo-400 hover:text-indigo-600 transition-colors" aria-label="Limpiar producto">
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
                        class="w-full pl-9 pr-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                    @if(!empty($productResults))
                        <div class="absolute top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-lg z-30 overflow-hidden">
                            @foreach($productResults as $p)
                                <button type="button" wire:click="selectProduct({{ $p['id'] }}, '{{ addslashes($p['name']) }}')"
                                    class="w-full text-left px-3 py-2.5 hover:bg-gray-50 text-sm border-b border-gray-100 last:border-0 transition-colors cursor-pointer">
                                    <p class="font-medium text-gray-800">{{ $p['name'] }}</p>
                                    <p class="text-xs text-gray-400 font-mono">{{ $p['sku'] }}</p>
                                </button>
                            @endforeach
                        </div>
                    @endif
                @endif
            </div>

            {{-- Almacén --}}
            <div class="relative">
                <select wire:model.live="warehouseId" aria-label="Almacén"
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

            {{-- Dirección --}}
            <div class="relative">
                <select wire:model.live="filterDirection" aria-label="Dirección"
                    class="px-4 py-2.5 pr-10 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all hover:bg-gray-100 appearance-none">
                    <option value="">Entradas y salidas</option>
                    <option value="in">Solo entradas</option>
                    <option value="out">Solo salidas</option>
                </select>
                <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>

            {{-- Tipo de movimiento --}}
            <div class="relative">
                <select wire:model.live="filterType" aria-label="Tipo de movimiento"
                    class="px-4 py-2.5 pr-10 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all hover:bg-gray-100 appearance-none">
                    <option value="">Todos los tipos</option>
                    @foreach($types as $key => $meta)
                        <option value="{{ $key }}">{{ $meta['label'] }}</option>
                    @endforeach
                </select>
                <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
        </div>

        {{-- Fila 2: fechas + limpiar --}}
        <div class="flex flex-wrap gap-3 items-center">
            <div class="flex items-center gap-2">
                <label class="text-xs font-medium text-gray-500 whitespace-nowrap">Desde</label>
                <input wire:model.live="dateFrom" type="date" aria-label="Fecha desde"
                    class="border border-gray-200 rounded-xl px-3 py-2 text-sm bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
            </div>
            <div class="flex items-center gap-2">
                <label class="text-xs font-medium text-gray-500 whitespace-nowrap">Hasta</label>
                <input wire:model.live="dateTo" type="date" aria-label="Fecha hasta"
                    class="border border-gray-200 rounded-xl px-3 py-2 text-sm bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
            </div>
            <button wire:click="clearFilters"
                class="px-3 py-2 text-sm border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors text-gray-500 cursor-pointer">
                Limpiar filtros
            </button>
        </div>
    </div>

    {{-- Tarjetas de totales --}}
    @if($totals)
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        {{-- Entradas --}}
        <div class="bg-white rounded-2xl border border-green-200 p-4 shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-7 h-7 rounded-lg bg-green-50 flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
                <p class="text-xs font-medium text-gray-500">Cant. entradas</p>
            </div>
            <p class="text-xl font-bold text-green-700">{{ number_format($totals->total_in_qty, 2) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-green-200 p-4 shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-7 h-7 rounded-lg bg-green-50 flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <p class="text-xs font-medium text-gray-500">Valor entradas</p>
            </div>
            <p class="text-xl font-bold text-green-700">${{ number_format($totals->total_in_value, 2) }}</p>
        </div>
        {{-- Salidas --}}
        <div class="bg-white rounded-2xl border border-red-200 p-4 shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-7 h-7 rounded-lg bg-red-50 flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                    </svg>
                </div>
                <p class="text-xs font-medium text-gray-500">Cant. salidas</p>
            </div>
            <p class="text-xl font-bold text-red-600">{{ number_format($totals->total_out_qty, 2) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-red-200 p-4 shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-7 h-7 rounded-lg bg-red-50 flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <p class="text-xs font-medium text-gray-500">Costo salidas</p>
            </div>
            <p class="text-xl font-bold text-red-600">${{ number_format($totals->total_out_value, 2) }}</p>
        </div>
        {{-- Ventas --}}
        <div class="bg-white rounded-2xl border border-indigo-200 p-4 shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-7 h-7 rounded-lg bg-indigo-50 flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <p class="text-xs font-medium text-gray-500">Total facturado</p>
            </div>
            <p class="text-xl font-bold text-indigo-700">${{ number_format($totals->total_revenue, 2) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-teal-200 p-4 shadow-sm">
            @php $profit = (float) $totals->total_profit; @endphp
            <div class="flex items-center gap-2 mb-2">
                <div class="w-7 h-7 rounded-lg {{ $profit >= 0 ? 'bg-teal-50' : 'bg-red-50' }} flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 {{ $profit >= 0 ? 'text-teal-600' : 'text-red-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
                <p class="text-xs font-medium text-gray-500">Utilidad total</p>
            </div>
            <p class="text-xl font-bold {{ $profit >= 0 ? 'text-teal-600' : 'text-red-600' }}">
                ${{ number_format($profit, 2) }}
            </p>
        </div>
    </div>
    @endif

    {{-- Tabla kardex --}}
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">

        @if($entries->isEmpty())
            <div class="py-16 text-center">
                <div class="w-14 h-14 rounded-2xl bg-gray-50 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <p class="text-sm font-medium text-gray-500">No hay registros PEPS con los filtros seleccionados.</p>
                @unless($productId)
                    <p class="text-xs text-gray-400 mt-1">Selecciona un artículo para ver su tarjeta de almacén.</p>
                @endunless
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-xs min-w-[1100px]">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="text-left px-4 py-3.5 font-semibold text-gray-600 uppercase tracking-wider">Fecha</th>
                            <th class="text-left px-4 py-3.5 font-semibold text-gray-600 uppercase tracking-wider">Artículo</th>
                            <th class="text-left px-4 py-3.5 font-semibold text-gray-600 uppercase tracking-wider">Lote</th>
                            <th class="text-left px-4 py-3.5 font-semibold text-gray-600 uppercase tracking-wider">Almacén</th>
                            <th class="text-left px-4 py-3.5 font-semibold text-gray-600 uppercase tracking-wider">Tipo</th>
                            <th class="text-left px-4 py-3.5 font-semibold text-gray-600 uppercase tracking-wider">Referencia</th>
                            {{-- Entrada --}}
                            <th class="text-right px-4 py-3.5 font-semibold text-green-700 bg-green-50/60 uppercase tracking-wider">Cant. entrada</th>
                            @can('view prices')
                            <th class="text-right px-4 py-3.5 font-semibold text-green-700 bg-green-50/60 uppercase tracking-wider">P. obtención</th>
                            <th class="text-right px-4 py-3.5 font-semibold text-green-700 bg-green-50/60 uppercase tracking-wider">Total entrada</th>
                            @endcan
                            {{-- Salida --}}
                            <th class="text-right px-4 py-3.5 font-semibold text-red-600 bg-red-50/60 uppercase tracking-wider">Cant. salida</th>
                            @can('view prices')
                            <th class="text-right px-4 py-3.5 font-semibold text-red-600 bg-red-50/60 uppercase tracking-wider">Costo PEPS</th>
                            <th class="text-right px-4 py-3.5 font-semibold text-red-600 bg-red-50/60 uppercase tracking-wider">P. venta</th>
                            <th class="text-right px-4 py-3.5 font-semibold text-red-600 bg-red-50/60 uppercase tracking-wider">Total venta</th>
                            <th class="text-right px-4 py-3.5 font-semibold text-teal-700 bg-teal-50/60 uppercase tracking-wider">Utilidad</th>
                            <th class="text-right px-4 py-3.5 font-semibold text-teal-700 bg-teal-50/60 uppercase tracking-wider">% Util.</th>
                            @endcan
                            {{-- Saldo --}}
                            <th class="text-right px-4 py-3.5 font-semibold text-gray-700 bg-gray-100 uppercase tracking-wider">Saldo cant.</th>
                            @can('view prices')
                            <th class="text-right px-4 py-3.5 font-semibold text-gray-700 bg-gray-100 uppercase tracking-wider">Saldo valor</th>
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
                            <tr class="hover:bg-gray-50/70 transition-colors duration-150 {{ $isIn ? 'bg-green-50/20' : '' }}">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="font-medium text-gray-800">{{ $entry->moved_at->format('d/m/Y') }}</span>
                                    <span class="block text-gray-400">{{ $entry->moved_at->format('H:i') }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-gray-900">{{ $entry->product->name }}</p>
                                    <p class="text-gray-400 font-mono">{{ $entry->product->sku }}</p>
                                </td>
                                <td class="px-4 py-3 font-mono text-gray-600">{{ $entry->lot_number ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $entry->warehouse->name }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 rounded-lg {{ $badgeClass }} font-medium">{{ $typeInfo['label'] }}</span>
                                </td>
                                <td class="px-4 py-3 font-mono text-gray-500">{{ $entry->reference ?? '—' }}</td>

                                {{-- Entradas --}}
                                <td class="px-4 py-3 text-right font-semibold text-green-700 bg-green-50/30">
                                    {{ $isIn ? number_format($entry->quantity, 4) : '—' }}
                                </td>
                                @can('view prices')
                                <td class="px-4 py-3 text-right text-green-600 bg-green-50/30">
                                    {{ $isIn ? '$' . number_format($entry->unit_cost, 4) : '—' }}
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-green-700 bg-green-50/30">
                                    {{ $isIn ? '$' . number_format($entry->total_cost, 2) : '—' }}
                                </td>
                                @endcan

                                {{-- Salidas --}}
                                <td class="px-4 py-3 text-right font-semibold text-red-600 bg-red-50/30">
                                    {{ !$isIn ? number_format($entry->quantity, 4) : '—' }}
                                </td>
                                @can('view prices')
                                <td class="px-4 py-3 text-right text-red-500 bg-red-50/30">
                                    {{ !$isIn ? '$' . number_format($entry->unit_cost, 4) : '—' }}
                                </td>
                                <td class="px-4 py-3 text-right text-gray-700 bg-red-50/30">
                                    {{ (!$isIn && $entry->unit_price) ? '$' . number_format($entry->unit_price, 4) : '—' }}
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-gray-900 bg-red-50/30">
                                    {{ (!$isIn && $entry->total_revenue) ? '$' . number_format($entry->total_revenue, 2) : '—' }}
                                </td>

                                {{-- Utilidad --}}
                                <td class="px-4 py-3 text-right font-bold bg-teal-50/30
                                    {{ !$isIn && $entry->profit !== null
                                        ? ((float)$entry->profit >= 0 ? 'text-teal-600' : 'text-red-600')
                                        : 'text-gray-300' }}">
                                    {{ (!$isIn && $entry->profit !== null) ? '$' . number_format($entry->profit, 2) : '—' }}
                                </td>
                                <td class="px-4 py-3 text-right bg-teal-50/30
                                    {{ !$isIn && $entry->profit_pct !== null
                                        ? ((float)$entry->profit_pct >= 0 ? 'text-teal-600' : 'text-red-600')
                                        : 'text-gray-300' }}">
                                    {{ (!$isIn && $entry->profit_pct !== null) ? number_format($entry->profit_pct, 2) . '%' : '—' }}
                                </td>
                                @endcan

                                {{-- Saldo --}}
                                <td class="px-4 py-3 text-right font-bold text-gray-900 bg-gray-50">
                                    {{ number_format($entry->balance_quantity, 4) }}
                                </td>
                                @can('view prices')
                                <td class="px-4 py-3 text-right font-bold text-gray-900 bg-gray-50">
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

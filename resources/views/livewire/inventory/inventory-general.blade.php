<div>
    <x-page-header title="Inventario general" description="Existencias totales de todos los almacenes">
        <x-slot:actions>
            <a href="{{ route('inventory.general.print', ['search' => $search, 'category_id' => $category_id, 'stock_filter' => $stock_filter]) }}"
                target="_blank"
                class="inline-flex items-center gap-2 text-sm bg-white hover:bg-gray-50 border border-gray-300 hover:border-gray-400 px-3 py-2 rounded-xl transition-all duration-200 text-gray-700 shadow-sm mr-2">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Imprimir
            </a>
            <a wire:navigate href="{{ route('inventory.warehouse-stock') }}"
                class="inline-flex items-center gap-2 text-sm bg-white hover:bg-gray-50 border border-gray-300 hover:border-gray-400 px-3 py-2 rounded-xl transition-all duration-200 text-gray-700 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                </svg>
                Por almacén
            </a>
        </x-slot:actions>
    </x-page-header>

    {{-- KPIs --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        {{-- Total productos --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500">Total productos</p>
                <p class="text-2xl font-bold text-gray-900 mt-0.5">{{ $totalProducts }}</p>
            </div>
        </div>
        {{-- Sin existencias --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500">Sin existencias</p>
                <p class="text-2xl font-bold text-red-500 mt-0.5">{{ $outOfStock }}</p>
            </div>
        </div>
        {{-- Stock bajo --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500">Stock bajo</p>
                <p class="text-2xl font-bold text-amber-500 mt-0.5">{{ $lowStock }}</p>
            </div>
        </div>
        {{-- Valor en inventario --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500">Valor en inventario</p>
                <p class="text-2xl font-bold text-indigo-600 mt-0.5">${{ number_format($totalStockValue, 2) }}</p>
            </div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-5 mb-6 shadow-sm">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="relative sm:col-span-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input wire:model.live.debounce.300ms="search" type="text"
                    placeholder="Buscar por nombre, SKU o código..."
                    aria-label="Buscar productos"
                    class="w-full pl-9 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
            </div>
            <div class="relative">
                <select wire:model.live="category_id" aria-label="Filtrar por categoría"
                    class="w-full px-4 py-2.5 pr-10 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all hover:bg-gray-100 appearance-none">
                    <option value="">Todas las categorías</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
                <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
            <div class="relative">
                <select wire:model.live="stock_filter" aria-label="Filtrar por estado de stock"
                    class="w-full px-4 py-2.5 pr-10 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all hover:bg-gray-100 appearance-none">
                    <option value="">Todos los estados</option>
                    <option value="ok">Stock normal</option>
                    <option value="low">Stock bajo</option>
                    <option value="out">Sin existencias</option>
                </select>
                <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[640px]">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="text-left text-xs text-gray-600 font-semibold uppercase tracking-wider px-5 py-4">Producto</th>
                        <th class="text-left text-xs text-gray-600 font-semibold uppercase tracking-wider px-5 py-4 hidden sm:table-cell">SKU</th>
                        <th class="text-left text-xs text-gray-600 font-semibold uppercase tracking-wider px-5 py-4 hidden md:table-cell">Categoría</th>
                        <th class="text-right text-xs text-gray-600 font-semibold uppercase tracking-wider px-5 py-4">Existencias</th>
                        <th class="text-right text-xs text-gray-600 font-semibold uppercase tracking-wider px-5 py-4 hidden md:table-cell">Mín.</th>
                        @can('view prices')
                        <th class="text-right text-xs text-gray-600 font-semibold uppercase tracking-wider px-5 py-4 hidden lg:table-cell">Precio costo</th>
                        <th class="text-right text-xs text-gray-600 font-semibold uppercase tracking-wider px-5 py-4 hidden lg:table-cell">Precio venta</th>
                        <th class="text-right text-xs text-gray-600 font-semibold uppercase tracking-wider px-5 py-4 hidden sm:table-cell">Valor total</th>
                        @endcan
                        <th class="text-center text-xs text-gray-600 font-semibold uppercase tracking-wider px-5 py-4">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($products as $product)
                        @php
                            $qty = $product->total_qty;
                            $isOut = $qty <= 0;
                            $isLow = $qty > 0 && $qty <= $product->min_stock;
                        @endphp
                        <tr class="hover:bg-gray-50/70 transition-colors duration-150">
                            <td class="px-5 py-4">
                                <a wire:navigate href="{{ route('inventory.products.edit', $product) }}"
                                    class="font-semibold text-gray-900 hover:text-indigo-600 transition-colors">
                                    {{ $product->name }}
                                </a>
                                @if($product->unitOfMeasure)
                                    <span class="text-xs text-gray-400 ml-1">/ {{ $product->unitOfMeasure->abbreviation }}</span>
                                @endif
                                <p class="text-xs text-gray-400 sm:hidden mt-0.5">
                                    {{ $product->sku ?? '' }}{{ $product->sku && $product->category ? ' · ' : '' }}{{ $product->category?->name ?? '' }}
                                </p>
                            </td>
                            <td class="px-5 py-4 text-gray-500 font-mono text-xs hidden sm:table-cell">{{ $product->sku ?? '—' }}</td>
                            <td class="px-5 py-4 hidden md:table-cell">
                                @if($product->category)
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full flex-shrink-0" style="background-color: {{ $product->category->color }}"></span>
                                        <span class="text-sm text-gray-600">{{ $product->category->name }}</span>
                                    </div>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                <span class="font-bold text-base {{ $isOut ? 'text-red-500' : ($isLow ? 'text-amber-500' : 'text-gray-900') }}">
                                    {{ number_format($qty, 2) }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right text-gray-400 text-xs hidden md:table-cell">{{ number_format($product->min_stock, 2) }}</td>
                            @can('view prices')
                            <td class="px-5 py-4 text-right text-gray-600 hidden lg:table-cell">${{ number_format($product->purchase_price, 2) }}</td>
                            <td class="px-5 py-4 text-right text-gray-800 hidden lg:table-cell">${{ number_format($product->sale_price, 2) }}</td>
                            <td class="px-5 py-4 text-right font-medium text-indigo-600 hidden sm:table-cell">
                                ${{ number_format($qty * (float)$product->purchase_price, 2) }}
                            </td>
                            @endcan
                            <td class="px-5 py-4 text-center">
                                @if($isOut)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium rounded-lg bg-red-50 text-red-600 border border-red-100">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                        Sin stock
                                    </span>
                                @elseif($isLow)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium rounded-lg bg-amber-50 text-amber-600 border border-amber-100">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Stock bajo
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-100">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Normal
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-5 py-12 text-center">
                                <x-empty-state message="No se encontraron productos con los filtros aplicados." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>

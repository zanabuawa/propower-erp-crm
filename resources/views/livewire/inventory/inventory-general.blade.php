<div>
    <x-page-header title="Inventario general" description="Existencias totales de todos los almacenes">
        <x-slot:actions>
            <a wire:navigate href="{{ route('inventory.warehouse-stock') }}"
                class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                Ver por almacén →
            </a>
        </x-slot:actions>
    </x-page-header>

    {{-- KPIs --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-400 mb-1">Total productos</p>
            <p class="text-2xl font-semibold text-gray-800">{{ $totalProducts }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-400 mb-1">Sin existencias</p>
            <p class="text-2xl font-semibold text-red-500">{{ $outOfStock }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-400 mb-1">Stock bajo</p>
            <p class="text-2xl font-semibold text-amber-500">{{ $lowStock }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-400 mb-1">Valor en inventario</p>
            <p class="text-2xl font-semibold text-indigo-600">${{ number_format($totalStockValue, 2) }}</p>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            <div>
                <input wire:model.live="search" type="text" placeholder="Buscar por nombre, SKU o código..."
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
            </div>
            <div>
                <select wire:model.live="category_id"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    <option value="">Todas las categorías</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select wire:model.live="stock_filter"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    <option value="">Todos los estados</option>
                    <option value="ok">Stock normal</option>
                    <option value="low">Stock bajo</option>
                    <option value="out">Sin existencias</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[640px]">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="text-left text-xs text-gray-600 font-semibold uppercase tracking-wide px-4 py-3">Producto</th>
                        <th class="text-left text-xs text-gray-600 font-semibold uppercase tracking-wide px-4 py-3 hidden sm:table-cell">SKU</th>
                        <th class="text-left text-xs text-gray-600 font-semibold uppercase tracking-wide px-4 py-3 hidden md:table-cell">Categoría</th>
                        <th class="text-right text-xs text-gray-600 font-semibold uppercase tracking-wide px-4 py-3">Existencias</th>
                        <th class="text-right text-xs text-gray-600 font-semibold uppercase tracking-wide px-4 py-3 hidden md:table-cell">Mín.</th>
                        <th class="text-right text-xs text-gray-600 font-semibold uppercase tracking-wide px-4 py-3 hidden lg:table-cell">Precio costo</th>
                        <th class="text-right text-xs text-gray-600 font-semibold uppercase tracking-wide px-4 py-3 hidden lg:table-cell">Precio venta</th>
                        <th class="text-right text-xs text-gray-600 font-semibold uppercase tracking-wide px-4 py-3 hidden sm:table-cell">Valor total</th>
                        <th class="text-center text-xs text-gray-600 font-semibold uppercase tracking-wide px-4 py-3">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($products as $product)
                        @php
                            $qty = $product->total_qty;
                            $isOut = $qty <= 0;
                            $isLow = $qty > 0 && $qty <= $product->min_stock;
                        @endphp
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3">
                                <a wire:navigate href="{{ route('inventory.products.edit', $product) }}"
                                    class="font-medium text-gray-800 hover:text-indigo-600">
                                    {{ $product->name }}
                                </a>
                                @if($product->unitOfMeasure)
                                    <span class="text-xs text-gray-400 ml-1">/ {{ $product->unitOfMeasure->abbreviation }}</span>
                                @endif
                                <p class="text-xs text-gray-400 sm:hidden mt-0.5">
                                    {{ $product->sku ?? '' }}{{ $product->sku && $product->category ? ' · ' : '' }}{{ $product->category?->name ?? '' }}
                                </p>
                            </td>
                            <td class="px-4 py-3 text-gray-500 font-mono text-xs hidden sm:table-cell">{{ $product->sku ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-500 hidden md:table-cell">{{ $product->category?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-right font-semibold {{ $isOut ? 'text-red-500' : ($isLow ? 'text-amber-500' : 'text-gray-800') }}">
                                {{ number_format($qty, 2) }}
                            </td>
                            <td class="px-4 py-3 text-right text-gray-400 text-xs hidden md:table-cell">{{ number_format($product->min_stock, 2) }}</td>
                            <td class="px-4 py-3 text-right text-gray-600 hidden lg:table-cell">${{ number_format($product->purchase_price, 2) }}</td>
                            <td class="px-4 py-3 text-right text-gray-800 hidden lg:table-cell">${{ number_format($product->sale_price, 2) }}</td>
                            <td class="px-4 py-3 text-right font-medium text-indigo-600 hidden sm:table-cell">
                                ${{ number_format($qty * (float)$product->purchase_price, 2) }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($isOut)
                                    <span class="inline-flex px-2 py-0.5 text-xs rounded-full bg-red-50 text-red-600 font-medium">Sin stock</span>
                                @elseif($isLow)
                                    <span class="inline-flex px-2 py-0.5 text-xs rounded-full bg-amber-50 text-amber-600 font-medium">Stock bajo</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 text-xs rounded-full bg-emerald-50 text-emerald-600 font-medium">Normal</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9"><x-empty-state message="No se encontraron productos con los filtros aplicados." /></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

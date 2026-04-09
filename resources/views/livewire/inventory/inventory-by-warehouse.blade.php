<div>
    <x-page-header title="Inventario por almacén" description="Existencias locales por almacén">
        <x-slot:actions>
            <a wire:navigate href="{{ route('inventory.general') }}"
                class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                ← Inventario general
            </a>
        </x-slot:actions>
    </x-page-header>

    {{-- Selector de almacén --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-5">
        <div class="flex flex-wrap gap-2">
            @foreach($warehouses as $wh)
                <button type="button"
                    wire:click="$set('warehouse_id', {{ $wh->id }})"
                    class="px-4 py-2 rounded-lg text-sm font-medium border transition
                        {{ $warehouse_id == $wh->id
                            ? 'bg-indigo-600 text-white border-indigo-600'
                            : 'bg-white text-gray-600 border-gray-200 hover:border-indigo-300' }}">
                    {{ $wh->name }}
                    @if($wh->branch)
                        <span class="text-xs opacity-70 ml-1">({{ $wh->branch->name }})</span>
                    @endif
                </button>
            @endforeach
            @if($warehouses->isEmpty())
                <p class="text-sm text-gray-400">No hay almacenes configurados.</p>
            @endif
        </div>
    </div>

    @if($selectedWarehouse)
        {{-- KPIs del almacén seleccionado --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <p class="text-xs text-gray-400 mb-1">Almacén</p>
                <p class="text-sm font-semibold text-gray-800 truncate">{{ $selectedWarehouse->name }}</p>
                @if($selectedWarehouse->location)
                    <p class="text-xs text-gray-400 mt-0.5 truncate">{{ $selectedWarehouse->location }}</p>
                @endif
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <p class="text-xs text-gray-400 mb-1">Referencias</p>
                <p class="text-2xl font-semibold text-gray-800">{{ $stocks->count() }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <p class="text-xs text-gray-400 mb-1">Sin stock / Stock bajo</p>
                <p class="text-2xl font-semibold">
                    <span class="text-red-500">{{ $outCount }}</span>
                    <span class="text-gray-300 mx-1">/</span>
                    <span class="text-amber-500">{{ $lowCount }}</span>
                </p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <p class="text-xs text-gray-400 mb-1">Valor en almacén</p>
                <p class="text-2xl font-semibold text-indigo-600">${{ number_format($totalValue, 2) }}</p>
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
                            <th class="text-right text-xs text-gray-600 font-semibold uppercase tracking-wide px-4 py-3 hidden lg:table-cell">Desc. máx.</th>
                            <th class="text-right text-xs text-gray-600 font-semibold uppercase tracking-wide px-4 py-3 hidden sm:table-cell">Valor</th>
                            <th class="text-center text-xs text-gray-600 font-semibold uppercase tracking-wide px-4 py-3">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($stocks as $stock)
                            @php
                                $p      = $stock->product;
                                $qty    = $stock->quantity;
                                $isOut  = $qty <= 0;
                                $isLow  = $qty > 0 && $qty <= $p->min_stock;
                                $cost    = (float)$p->purchase_price;
                                $maxDisc = max(0, round(
                                    $cost * (1 + (float)$p->profit_margin / 100)
                                    - $cost * (1 + (float)$p->operational_costs / 100),
                                    2
                                ));
                            @endphp
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3">
                                    <a wire:navigate href="{{ route('inventory.products.edit', $p) }}"
                                        class="font-medium text-gray-800 hover:text-indigo-600">
                                        {{ $p->name }}
                                    </a>
                                    @if($p->unitOfMeasure)
                                        <span class="text-xs text-gray-400 ml-1">/ {{ $p->unitOfMeasure->abbreviation }}</span>
                                    @endif
                                    <p class="text-xs text-gray-400 sm:hidden mt-0.5">
                                        {{ $p->sku ?? '' }}{{ $p->sku && $p->category ? ' · ' : '' }}{{ $p->category?->name ?? '' }}
                                    </p>
                                </td>
                                <td class="px-4 py-3 text-gray-500 font-mono text-xs hidden sm:table-cell">{{ $p->sku ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-500 hidden md:table-cell">{{ $p->category?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-right font-semibold {{ $isOut ? 'text-red-500' : ($isLow ? 'text-amber-500' : 'text-gray-800') }}">
                                    {{ number_format($qty, 2) }}
                                </td>
                                <td class="px-4 py-3 text-right text-gray-400 text-xs hidden md:table-cell">{{ number_format($p->min_stock, 2) }}</td>
                                <td class="px-4 py-3 text-right text-gray-600 hidden lg:table-cell">${{ number_format($p->purchase_price, 2) }}</td>
                                <td class="px-4 py-3 text-right text-gray-800 hidden lg:table-cell">${{ number_format($p->sale_price, 2) }}</td>
                                <td class="px-4 py-3 text-right hidden lg:table-cell {{ $maxDisc > 0 ? 'text-emerald-600' : 'text-gray-300' }}">
                                    ${{ number_format($maxDisc, 2) }}
                                </td>
                                <td class="px-4 py-3 text-right font-medium text-indigo-600 hidden sm:table-cell">
                                    ${{ number_format($qty * (float)$p->purchase_price, 2) }}
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
                                <td colspan="10" class="px-4 py-10 text-center text-sm text-gray-400">
                                    No hay productos con existencias en este almacén.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="bg-white rounded-xl border border-gray-200 p-10 text-center text-sm text-gray-400">
            Selecciona un almacén para ver sus existencias.
        </div>
    @endif
</div>

<div class="max-w-4xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('purchases.goods-receipts.index') }}" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-xl font-medium text-gray-900">Nueva recepción de mercancías</h1>
            <p class="text-sm text-gray-400 mt-0.5">Registro de entrada directa — actualiza stock y precios del producto</p>
        </div>
    </div>

    <form wire:submit="save" class="space-y-5">

        {{-- Datos generales --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">Datos de la recepción</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Almacén destino *</label>
                    <select wire:model="warehouse_id"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        <option value="">— Seleccionar almacén —</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->name }}{{ $wh->branch ? ' — '.$wh->branch->name : '' }}</option>
                        @endforeach
                    </select>
                    @error('warehouse_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Proveedor</label>
                    <select wire:model="supplier_id"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        <option value="">— Sin proveedor —</option>
                        @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Referencia (factura / remisión)</label>
                    <input wire:model="reference" type="text"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                        placeholder="Ej. FAC-001234">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Notas</label>
                    <input wire:model="notes" type="text"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                        placeholder="Observaciones">
                </div>
            </div>
        </div>

        {{-- Buscar y agregar productos --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">Productos recibidos</h2>

            <div class="relative">
                <input wire:model.live.debounce.300ms="productSearch" type="text"
                    placeholder="Buscar producto por nombre, SKU o código de barras..."
                    class="w-full border border-gray-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                @if(count($productResults) > 0)
                    <div class="absolute top-full left-0 right-0 bg-white border border-gray-200 rounded-lg shadow-lg z-20 mt-1">
                        @foreach($productResults as $result)
                            <button type="button" wire:click="addProduct({{ $result['id'] }})"
                                class="w-full text-left px-4 py-2.5 hover:bg-gray-50 transition flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $result['name'] }}</p>
                                    <p class="text-xs text-gray-400">SKU: {{ $result['sku'] ?? '—' }} · Costo actual: ${{ number_format($result['purchase_price'], 2) }}</p>
                                </div>
                                <span class="text-xs text-indigo-600 font-medium ml-4">+ Agregar</span>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            @error('items') <p class="text-xs text-red-500">{{ $message }}</p> @enderror

            @if(count($items) > 0)
                <div class="border border-gray-100 rounded-lg overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100">
                                <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Producto</th>
                                <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-24">Cantidad</th>
                                <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-28">Precio costo</th>
                                <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-24">Margen %</th>
                                <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-24">Gastos op. %</th>
                                <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500 w-28">Precio venta</th>
                                <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500 w-28">Precio mín.</th>
                                <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500 w-24">Desc. máx.</th>
                                <th class="w-8"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($items as $index => $item)
                                @php
                                    $cost      = (float)($item['purchase_price'] ?? 0);
                                    $margin    = (float)($item['profit_margin'] ?? 0);
                                    $opPct     = (float)($item['operational_cost'] ?? 0);
                                    $salePrice = round($cost * (1 + $margin / 100), 2);
                                    $minPrice  = round($cost * (1 + $opPct / 100), 2);
                                    $maxDiscPct = $salePrice > 0
                                        ? round(max(0, ($salePrice - $minPrice) / $salePrice * 100), 2)
                                        : 0;
                                    $hasMargin = $salePrice > $minPrice;
                                @endphp
                                <tr>
                                    <td class="px-4 py-3">
                                        <p class="font-medium text-gray-900 text-sm">{{ $item['product_name'] }}</p>
                                        <p class="text-xs text-gray-400 font-mono">{{ $item['sku'] }}</p>
                                    </td>
                                    <td class="px-4 py-2">
                                        <input wire:model.live="items.{{ $index }}.quantity"
                                            type="number" step="0.01" min="0.01"
                                            class="w-full border border-gray-200 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-300">
                                        @error("items.{$index}.quantity")
                                            <p class="text-xs text-red-500">{{ $message }}</p>
                                        @enderror
                                    </td>
                                    <td class="px-4 py-2">
                                        <div class="relative">
                                            <span class="absolute left-2 top-1.5 text-xs text-gray-400">$</span>
                                            <input wire:model.live="items.{{ $index }}.purchase_price"
                                                type="number" step="0.01" min="0"
                                                class="w-full border border-gray-200 rounded pl-5 pr-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-300">
                                        </div>
                                        @error("items.{$index}.purchase_price")
                                            <p class="text-xs text-red-500">{{ $message }}</p>
                                        @enderror
                                    </td>
                                    <td class="px-4 py-2">
                                        <div class="relative">
                                            <input wire:model.live="items.{{ $index }}.profit_margin"
                                                type="number" step="0.01" min="0" max="999"
                                                class="w-full border border-gray-200 rounded pl-2 pr-6 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-300">
                                            <span class="absolute right-2 top-1.5 text-xs text-gray-400">%</span>
                                        </div>
                                        @error("items.{$index}.profit_margin")
                                            <p class="text-xs text-red-500">{{ $message }}</p>
                                        @enderror
                                    </td>
                                    <td class="px-4 py-2">
                                        <div class="relative">
                                            <input wire:model.live="items.{{ $index }}.operational_cost"
                                                type="number" step="0.01" min="0" max="999"
                                                class="w-full border border-amber-200 rounded pl-2 pr-6 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-amber-300">
                                            <span class="absolute right-2 top-1.5 text-xs text-amber-400">%</span>
                                        </div>
                                        @error("items.{$index}.operational_cost")
                                            <p class="text-xs text-red-500">{{ $message }}</p>
                                        @enderror
                                    </td>
                                    {{-- Precio de venta calculado --}}
                                    <td class="px-4 py-3 text-right">
                                        <p class="text-sm font-semibold text-indigo-600">${{ number_format($salePrice, 2) }}</p>
                                        <p class="text-[10px] text-gray-400">costo + {{ number_format($margin, 2) }}%</p>
                                    </td>
                                    {{-- Precio mínimo --}}
                                    <td class="px-4 py-3 text-right">
                                        <p class="text-sm font-semibold text-amber-600">${{ number_format($minPrice, 2) }}</p>
                                        <p class="text-[10px] text-gray-400">costo + {{ number_format($opPct, 2) }}%</p>
                                    </td>
                                    {{-- Descuento máximo --}}
                                    <td class="px-4 py-3 text-right">
                                        @if($hasMargin)
                                            <p class="text-sm font-semibold text-green-600">{{ number_format($maxDiscPct, 2) }}%</p>
                                        @else
                                            <p class="text-sm text-gray-300">0%</p>
                                            <p class="text-[10px] text-amber-500">sin margen</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        <button type="button" wire:click="removeItem({{ $index }})"
                                            class="text-red-400 hover:text-red-600">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Leyenda --}}
                <div class="bg-blue-50 border border-blue-100 rounded-lg px-4 py-3">
                    <p class="text-xs text-blue-700">
                        <strong>Precio venta</strong> = costo × (1 + margen%) ·
                        <strong>Precio mínimo</strong> = costo × (1 + gastos op.%) ·
                        <strong>Desc. máximo</strong> = margen% que puede cederse sin perder capital ·
                        Al guardar estos valores se actualizan en el producto.
                    </p>
                </div>
            @else
                <div class="border-2 border-dashed border-gray-200 rounded-lg py-10 text-center text-sm text-gray-400">
                    Busca y agrega los productos que estás recibiendo.
                </div>
            @endif
        </div>

        @if(count($items) > 0)
            <div class="flex items-center justify-end gap-3 pb-6">
                <a href="{{ route('purchases.goods-receipts.index') }}"
                    class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                    Cancelar
                </a>
                <button type="submit"
                    class="px-5 py-2 text-sm bg-teal-600 hover:bg-teal-700 text-white rounded-lg font-medium transition">
                    Confirmar recepción
                </button>
            </div>
        @endif
    </form>
</div>

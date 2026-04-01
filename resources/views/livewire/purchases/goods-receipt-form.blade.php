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

    <div class="space-y-5">

        {{-- Datos generales --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">Datos de la recepción</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                {{-- Tipo de recepción --}}
                <div class="sm:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">Tipo de recepción *</label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        @foreach(\App\Models\PurchaseReceipt::RECEPTION_TYPES as $value => $label)
                            <label class="flex items-center gap-2 p-3 border rounded-lg cursor-pointer transition
                                {{ $reception_type === $value
                                    ? 'border-indigo-400 bg-indigo-50 text-indigo-700'
                                    : 'border-gray-200 hover:border-gray-300 text-gray-600' }}">
                                <input type="radio" wire:model.live="reception_type" value="{{ $value }}" class="sr-only">
                                <span class="text-sm font-medium">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    @if($reception_type === 'defective')
                        <p class="mt-2 text-xs text-amber-600">
                            Los productos defectuosos se enviarán al almacén de defectuosos de tu sucursal. No se actualizarán precios.
                        </p>
                    @endif
                </div>

                {{-- Almacén --}}
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Almacén destino *</label>
                    <select wire:model="warehouse_id"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        <option value="">— Seleccionar almacén —</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->name }}{{ $wh->branch ? ' — '.$wh->branch->name : '' }}{{ $wh->is_defective ? ' (Defectuosos)' : '' }}</option>
                        @endforeach
                    </select>
                    @error('warehouse_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Proveedor --}}
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

                {{-- Referencia --}}
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Referencia (factura / remisión)</label>
                    <input wire:model="reference" type="text"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                        placeholder="Ej. FAC-001234">
                </div>

                {{-- Gastos de operación --}}
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Gastos de operación generales ($)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-sm text-gray-400">$</span>
                        <input wire:model="operating_expenses" type="number" step="0.01" min="0"
                            class="w-full border border-gray-200 rounded-lg pl-7 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-300"
                            placeholder="0.00">
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Flete, maniobras, almacenaje, etc.</p>
                    @error('operating_expenses') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Notas --}}
                <div class="sm:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">Notas</label>
                    <input wire:model="notes" type="text"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                        placeholder="Observaciones">
                </div>
            </div>
        </div>

        {{-- Productos --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h2 class="text-sm font-medium text-gray-700">Productos recibidos</h2>
                <livewire:shared.product-picker />
            </div>

            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input wire:model.live.debounce.250ms="productSearch" type="text"
                    placeholder="Búsqueda rápida: nombre, SKU o código de barras..."
                    class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                @if(count($productResults) > 0)
                    <div class="absolute top-full left-0 right-0 bg-white border border-gray-200 rounded-xl shadow-xl z-20 mt-1 overflow-hidden">
                        @foreach($productResults as $result)
                            <button type="button" wire:click="addProduct({{ $result['id'] }})"
                                class="w-full text-left px-4 py-2.5 hover:bg-indigo-50 transition flex items-center justify-between border-b border-gray-50 last:border-0">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $result['name'] }}</p>
                                    <p class="text-xs text-gray-400">
                                        SKU: {{ $result['sku'] ?? '—' }}
                                        @if($result['barcode'] ?? null) · CB: {{ $result['barcode'] }} @endif
                                        · Costo: ${{ number_format($result['purchase_price'], 2) }}
                                    </p>
                                </div>
                                <div class="text-right flex-shrink-0 ml-4">
                                    <p class="text-xs text-indigo-400">+ Agregar</p>
                                </div>
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
                                @if($reception_type !== 'defective')
                                    <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-28">Precio costo</th>
                                    <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-24">Margen %</th>
                                    <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-24">Gastos op. %</th>
                                    <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500 w-28">Precio venta</th>
                                    <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500 w-28">Precio mín.</th>
                                @endif
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
                                    @if($reception_type !== 'defective')
                                        <td class="px-4 py-2">
                                            <div class="relative">
                                                <span class="absolute left-2 top-1.5 text-xs text-gray-400">$</span>
                                                <input wire:model.live="items.{{ $index }}.purchase_price"
                                                    type="number" step="0.01" min="0"
                                                    class="w-full border border-gray-200 rounded pl-5 pr-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-300">
                                            </div>
                                        </td>
                                        <td class="px-4 py-2">
                                            <div class="relative">
                                                <input wire:model.live="items.{{ $index }}.profit_margin"
                                                    type="number" step="0.01" min="0" max="999"
                                                    class="w-full border border-gray-200 rounded pl-2 pr-6 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-300">
                                                <span class="absolute right-2 top-1.5 text-xs text-gray-400">%</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-2">
                                            <div class="relative">
                                                <input wire:model.live="items.{{ $index }}.operational_cost"
                                                    type="number" step="0.01" min="0" max="999"
                                                    class="w-full border border-amber-200 rounded pl-2 pr-6 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-amber-300">
                                                <span class="absolute right-2 top-1.5 text-xs text-amber-400">%</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <p class="text-sm font-semibold text-indigo-600">${{ number_format($salePrice, 2) }}</p>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <p class="text-sm font-semibold text-amber-600">${{ number_format($minPrice, 2) }}</p>
                                        </td>
                                    @endif
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
                <button type="button" wire:click="confirm"
                    class="px-5 py-2 text-sm bg-teal-600 hover:bg-teal-700 text-white rounded-lg font-medium transition">
                    Revisar y confirmar
                </button>
            </div>
        @endif
    </div>

    {{-- Modal de confirmación --}}
    @if($showConfirmModal)
        <div class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg">
                <div class="p-6 border-b border-gray-100">
                    <h2 class="text-base font-semibold text-gray-900">Confirmar recepción de mercancías</h2>
                    <p class="text-sm text-gray-500 mt-1">
                        Tipo: <strong>{{ \App\Models\PurchaseReceipt::RECEPTION_TYPES[$reception_type] }}</strong>
                        @php $wh = $warehouses->firstWhere('id', $warehouse_id); @endphp
                        @if($wh) · Almacén: <strong>{{ $wh->name }}</strong> @endif
                    </p>
                </div>
                <div class="p-6 space-y-3">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Productos que se agregarán al inventario</p>
                    <div class="border border-gray-100 rounded-lg overflow-hidden">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-100 text-xs text-gray-500">
                                    <th class="text-left px-4 py-2">Producto</th>
                                    <th class="text-right px-4 py-2">Cantidad</th>
                                    @if($reception_type !== 'defective')
                                        <th class="text-right px-4 py-2">Precio venta</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($items as $item)
                                    @php
                                        $sp = round((float)($item['purchase_price'] ?? 0) * (1 + (float)($item['profit_margin'] ?? 0) / 100), 2);
                                    @endphp
                                    <tr>
                                        <td class="px-4 py-2.5 font-medium text-gray-900">{{ $item['product_name'] }}</td>
                                        <td class="px-4 py-2.5 text-right text-gray-700">{{ $item['quantity'] }}</td>
                                        @if($reception_type !== 'defective')
                                            <td class="px-4 py-2.5 text-right text-indigo-600 font-semibold">${{ number_format($sp, 2) }}</td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($operating_expenses > 0)
                        <p class="text-xs text-amber-700 bg-amber-50 rounded-lg px-3 py-2">
                            Gastos de operación generales: <strong>${{ number_format($operating_expenses, 2) }}</strong>
                        </p>
                    @endif
                    @if($reception_type === 'defective')
                        <p class="text-xs text-red-700 bg-red-50 rounded-lg px-3 py-2">
                            Estos productos se registrarán en el almacén de defectuosos. No se actualizarán precios ni stock de venta.
                        </p>
                    @endif
                </div>
                <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
                    <button type="button" wire:click="cancelConfirm"
                        class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                        Revisar
                    </button>
                    <button type="button" wire:click="save"
                        class="px-5 py-2 text-sm bg-teal-600 hover:bg-teal-700 text-white rounded-lg font-medium transition">
                        Confirmar recepción
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

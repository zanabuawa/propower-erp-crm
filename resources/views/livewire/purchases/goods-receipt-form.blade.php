<div class="max-w-5xl">
    <div class="flex items-center gap-3 mb-6">
        <a wire:navigate href="{{ route('purchases.goods-receipts.index') }}" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-xl font-medium text-gray-900">Nueva recepción de mercancías</h1>
            <p class="text-sm text-gray-400 mt-0.5">Registra la entrada de productos al inventario</p>
        </div>
    </div>

    <div class="space-y-5">

        {{-- Orden de compra (opcional) --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">Vincular a orden de compra</h2>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Orden de compra</label>
                <select wire:model.live="purchase_order_id"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    <option value="">— Sin orden de compra (entrada directa) —</option>
                    @foreach($purchaseOrders as $po)
                        <option value="{{ $po->id }}">
                            {{ $po->folio }} — {{ $po->supplier->name }}
                            ({{ \App\Models\PurchaseOrder::STATUS[$po->status] ?? $po->status }})
                        </option>
                    @endforeach
                </select>
                @if($purchase_order_id)
                    <p class="text-xs text-teal-600 mt-1">Los productos pendientes de esta orden se cargaron automáticamente.</p>
                @else
                    <p class="text-xs text-gray-400 mt-1">Opcional — los productos de la orden se cargarán automáticamente en la lista.</p>
                @endif
            </div>
        </div>

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
                    <label class="block text-xs text-gray-500 mb-1">Notas generales</label>
                    <input wire:model="notes" type="text"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                        placeholder="Observaciones de la recepción">
                </div>
            </div>
        </div>

        {{-- Productos --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <div>
                    <h2 class="text-sm font-medium text-gray-700">Productos recibidos</h2>
                    @if(count($items) > 0)
                        <p class="text-xs text-gray-400 mt-0.5">
                            Marca los productos que llegaron y agrega notas si es necesario
                        </p>
                    @endif
                </div>
            </div>

            {{-- Buscador manual --}}
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input wire:model.live.debounce.250ms="productSearch" type="text"
                    placeholder="Agregar producto extra: nombre, SKU o código de barras..."
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
                                <span class="text-xs text-indigo-400 flex-shrink-0 ml-4">+ Agregar</span>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            @error('items') <p class="text-xs text-red-500">{{ $message }}</p> @enderror

            @if(count($items) > 0)
                <div class="border border-gray-100 rounded-lg overflow-x-auto">
                    <table class="w-full text-sm min-w-[680px]">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100">
                                <th class="px-3 py-2.5 w-10">
                                    <span class="text-xs font-medium text-gray-500">Ok</span>
                                </th>
                                <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Producto</th>
                                <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-24">Cantidad</th>
                                @if($reception_type !== 'defective')
                                    <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-28">Precio costo</th>
                                    <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-20">Margen %</th>
                                    <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500 w-24">Precio venta</th>
                                @endif
                                <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Notas / Observaciones</th>
                                <th class="w-8"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($items as $index => $item)
                                @php
                                    $received  = $item['received'] ?? true;
                                    $cost      = (float)($item['purchase_price'] ?? 0);
                                    $margin    = (float)($item['profit_margin'] ?? 0);
                                    $salePrice = round($cost * (1 + $margin / 100), 2);
                                @endphp
                                <tr class="{{ $received ? '' : 'opacity-50 bg-gray-50' }}">
                                    <td class="px-3 py-3 text-center">
                                        <input wire:model.live="items.{{ $index }}.received"
                                            type="checkbox"
                                            class="w-4 h-4 rounded text-teal-600 border-gray-300 focus:ring-teal-400"
                                            {{ $received ? 'checked' : '' }}>
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="font-medium text-gray-900 text-sm">{{ $item['product_name'] }}</p>
                                        @if($item['sku'])
                                            <p class="text-xs text-gray-400 font-mono">{{ $item['sku'] }}</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2">
                                        <input wire:model.live="items.{{ $index }}.quantity"
                                            type="number" step="0.01" min="0.01"
                                            {{ !$received ? 'disabled' : '' }}
                                            class="w-full border border-gray-200 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-300 disabled:bg-gray-100">
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
                                                    {{ !$received ? 'disabled' : '' }}
                                                    class="w-full border border-gray-200 rounded pl-5 pr-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-300 disabled:bg-gray-100">
                                            </div>
                                        </td>
                                        <td class="px-4 py-2">
                                            <div class="relative">
                                                <input wire:model.live="items.{{ $index }}.profit_margin"
                                                    type="number" step="0.01" min="0" max="999"
                                                    {{ !$received ? 'disabled' : '' }}
                                                    class="w-full border border-gray-200 rounded pl-2 pr-6 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-300 disabled:bg-gray-100">
                                                <span class="absolute right-2 top-1.5 text-xs text-gray-400">%</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <p class="text-sm font-semibold {{ $received ? 'text-indigo-600' : 'text-gray-400' }}">
                                                ${{ number_format($salePrice, 2) }}
                                            </p>
                                        </td>
                                    @endif
                                    <td class="px-4 py-2">
                                        <input wire:model="items.{{ $index }}.notes"
                                            type="text"
                                            {{ !$received ? 'disabled' : '' }}
                                            placeholder="Ej: llegó golpeado, falta embalaje..."
                                            class="w-full border border-gray-200 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-amber-300 disabled:bg-gray-100"
                                            style="min-width:160px">
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

                {{-- Resumen --}}
                @php
                    $receivedCount = collect($items)->where('received', true)->count();
                    $totalCount    = count($items);
                @endphp
                @if($totalCount > 0)
                    <p class="text-xs text-gray-500">
                        {{ $receivedCount }} de {{ $totalCount }} producto(s) marcado(s) como recibido(s)
                        @if($receivedCount < $totalCount)
                            <span class="text-amber-600">— {{ $totalCount - $receivedCount }} pendiente(s)</span>
                        @endif
                    </p>
                @endif
            @else
                <div class="border-2 border-dashed border-gray-200 rounded-lg py-10 text-center text-sm text-gray-400">
                    @if($purchase_order_id)
                        Todos los productos de esta orden ya fueron recibidos.
                    @else
                        Selecciona una orden de compra o busca productos manualmente.
                    @endif
                </div>
            @endif
        </div>

        @if(count($items) > 0)
            <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pb-6">
                <a wire:navigate href="{{ route('purchases.goods-receipts.index') }}"
                    class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition text-center">
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
                <div class="p-6 space-y-3 max-h-80 overflow-y-auto">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Productos a registrar</p>
                    <div class="border border-gray-100 rounded-lg overflow-hidden">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-100 text-xs text-gray-500">
                                    <th class="text-left px-4 py-2">Producto</th>
                                    <th class="text-right px-4 py-2">Cantidad</th>
                                    @if($reception_type !== 'defective')
                                        <th class="text-right px-4 py-2">Precio venta</th>
                                    @endif
                                    <th class="text-left px-4 py-2">Notas</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($items as $item)
                                    @if($item['received'] ?? true)
                                        @php
                                            $sp = round((float)($item['purchase_price'] ?? 0) * (1 + (float)($item['profit_margin'] ?? 0) / 100), 2);
                                        @endphp
                                        <tr>
                                            <td class="px-4 py-2.5 font-medium text-gray-900">{{ $item['product_name'] }}</td>
                                            <td class="px-4 py-2.5 text-right text-gray-700">{{ $item['quantity'] }}</td>
                                            @if($reception_type !== 'defective')
                                                <td class="px-4 py-2.5 text-right text-indigo-600 font-semibold">${{ number_format($sp, 2) }}</td>
                                            @endif
                                            <td class="px-4 py-2.5 text-xs text-gray-500">{{ $item['notes'] ?: '—' }}</td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($operating_expenses > 0)
                        <p class="text-xs text-amber-700 bg-amber-50 rounded-lg px-3 py-2">
                            Gastos de operación: <strong>${{ number_format($operating_expenses, 2) }}</strong>
                        </p>
                    @endif
                    @if($reception_type === 'defective')
                        <p class="text-xs text-red-700 bg-red-50 rounded-lg px-3 py-2">
                            Estos productos se registrarán en el almacén de defectuosos. No se actualizarán precios.
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

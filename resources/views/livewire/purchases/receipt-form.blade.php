<div class="max-w-3xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('purchases.orders.show', $order) }}" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h1 class="text-xl font-medium text-gray-900">Registrar recepción</h1>
            <p class="text-sm text-gray-500">Orden: {{ $order->folio }} — {{ $order->supplier->name }}</p>
        </div>
    </div>

    <form class="space-y-5">

        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">Datos de recepción</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">Almacén de recepción *</label>
                    <select wire:model.live="warehouse_id"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        <option value="">— Seleccionar almacén —</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ $warehouse_id == $warehouse->id ? 'selected' : '' }}>
                                {{ $warehouse->name }} — {{ $warehouse->branch->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('warehouse_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">Notas</label>
                    <input wire:model="notes" type="text"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                        placeholder="Observaciones de la recepción">
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-sm font-medium text-gray-700">Productos a recibir</h2>
                <p class="text-xs text-gray-400">Los gastos de operación se acumulan al costo del producto</p>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Producto</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500">Pendiente</th>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-28">Cant. a recibir</th>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-28">Precio costo</th>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-28">Gastos op. % *</th>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Almacén destino</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($items as $index => $item)
                        <tr>
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900">{{ $item['product_name'] }}</p>
                            </td>
                            <td class="px-4 py-3 text-right text-gray-600">{{ $item['quantity_pending'] }}</td>
                            <td class="px-4 py-3">
                                <input wire:model="items.{{ $index }}.quantity_received" type="number" step="0.01" min="0"
                                    max="{{ $item['quantity_pending'] }}"
                                    class="w-full border border-gray-200 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-300">
                                @error("items.{$index}.quantity_received")
                                    <p class="text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </td>
                            <td class="px-4 py-3">
                                <div class="relative">
                                    <span class="absolute left-2 top-1.5 text-xs text-gray-400">$</span>
                                    <input wire:model="items.{{ $index }}.purchase_price" type="number" step="0.01" min="0"
                                        class="w-full border border-gray-200 rounded pl-5 pr-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-300">
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="relative">
                                    <input wire:model="items.{{ $index }}.operational_cost" type="number" step="0.01" min="0" max="999"
                                        class="w-full border border-gray-200 rounded pl-3 pr-7 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-amber-300"
                                        placeholder="0.00">
                                    <span class="absolute right-2 top-1.5 text-xs text-gray-400">%</span>
                                </div>
                                @error("items.{$index}.operational_cost")
                                    <p class="text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </td>
                            <td class="px-4 py-3">
                                <select wire:model="items.{{ $index }}.warehouse_id"
                                    class="w-full border border-gray-200 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-300">
                                    @foreach($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}" {{ $item['warehouse_id'] == $warehouse->id ? 'selected' : '' }}>
                                            {{ $warehouse->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-gray-400 text-sm">
                                Todos los productos ya fueron recibidos.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if(count($items) > 0)
                <div class="px-5 py-3 bg-amber-50 border-t border-amber-100">
                    <p class="text-xs text-amber-700">
                        * Gastos de operación en % sobre el precio de obtención (flete, maniobras, almacenaje, etc.).
                        Al guardar se actualiza el producto: <strong>precio mínimo venta = costo × (1 + gastos%)</strong>,
                        el descuento máximo = margen% − gastos%, nunca se vende por debajo del costo + gastos.
                    </p>
                </div>
            @endif
        </div>

        @if(count($items) > 0)
            <div class="flex items-center justify-end gap-3 pb-6">
                <a href="{{ route('purchases.orders.show', $order) }}"
                    class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                    Cancelar
                </a>
                <button type="button" wire:click="save"
                    class="px-5 py-2 text-sm bg-teal-600 hover:bg-teal-700 text-white rounded-lg font-medium transition">
                    Confirmar recepción
                </button>
            </div>
        @endif
    </form>
</div>
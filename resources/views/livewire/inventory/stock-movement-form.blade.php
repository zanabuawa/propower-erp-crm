<div class="max-w-4xl">
    <div class="flex items-center gap-3 mb-6">
        <a wire:navigate href="{{ route('inventory.movements.index') }}" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="text-xl font-medium text-gray-900">Nuevo movimiento de stock</h1>
    </div>

    <form wire:submit="save" class="space-y-5">

        {{-- Tipo y almacén --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">Información del movimiento</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Tipo de movimiento *</label>
                    <select wire:model.live="type"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        <option value="entry">Entrada</option>
                        <option value="exit">Salida</option>
                        <option value="adjustment">Ajuste de inventario</option>
                        <option value="transfer">Transferencia entre almacenes</option>
                        <option value="return">Devolución de cliente</option>
                    </select>
                    @error('type') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Fecha y hora *</label>
                    <input wire:model="moved_at" type="datetime-local" value="{{ $moved_at }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    @error('moved_at') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">
                        {{ $type === 'transfer' ? 'Almacén origen *' : 'Almacén *' }}
                    </label>
                    <select wire:model="warehouse_id"
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
                @if($type === 'transfer')
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Almacén destino *</label>
                        <select wire:model="warehouse_destination_id"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                            <option value="">— Seleccionar almacén —</option>
                            @foreach($warehouses as $warehouse)
                                @if($warehouse->id != $warehouse_id)
                                    <option value="{{ $warehouse->id }}" {{ $warehouse_destination_id == $warehouse->id ? 'selected' : '' }}>
                                        {{ $warehouse->name }} — {{ $warehouse->branch->name }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        @error('warehouse_destination_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                @endif
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Referencia</label>
                    <input wire:model="reference" type="text" value="{{ $reference }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                        placeholder="Ej: Factura #123, OC-456">
                </div>
                <div class="{{ $type === 'transfer' ? '' : 'sm:col-span-2' }}">
                    <label class="block text-xs text-gray-500 mb-1">Notas</label>
                    <input wire:model="notes" type="text" value="{{ $notes }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>
            </div>
        </div>

        {{-- Productos --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">Productos</h2>

            {{-- Buscador de productos --}}
            <div class="relative">
                <input wire:model.live.debounce.300ms="productSearch" type="text"
                    placeholder="Buscar producto por nombre, SKU o código de barras..."
                    class="w-full border border-gray-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                @if(count($productResults) > 0)
                    <div class="absolute top-full left-0 right-0 bg-white border border-gray-200 rounded-lg shadow-lg z-10 mt-1">
                        @foreach($productResults as $result)
                            <button type="button" wire:click="addProduct({{ $result['id'] }})"
                                class="w-full text-left px-4 py-2.5 hover:bg-gray-50 transition flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $result['name'] }}</p>
                                    <p class="text-xs text-gray-400">SKU: {{ $result['sku'] ?? '—' }}</p>
                                </div>
                                <span class="text-xs text-indigo-600 font-medium">+ Agregar</span>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            @error('items') <p class="text-xs text-red-500">{{ $message }}</p> @enderror

            {{-- Tabla de productos agregados --}}
            @if(count($items) > 0)
                <div class="border border-gray-100 rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm min-w-[480px]">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100">
                                <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Producto</th>
                                <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-32">Cantidad</th>
                                <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-36">Precio unit.</th>
                                <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-32">Subtotal</th>
                                <th class="w-10"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($items as $index => $item)
                                <tr>
                                    <td class="px-4 py-2.5">
                                        <p class="font-medium text-gray-900">{{ $item['product_name'] }}</p>
                                        <p class="text-xs text-gray-400 font-mono">{{ $item['sku'] ?? '' }}</p>
                                    </td>
                                    <td class="px-4 py-2.5">
                                        <input wire:model.live="items.{{ $index }}.quantity"
                                            type="number" step="0.01" min="0.01"
                                            class="w-full border border-gray-200 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-300">
                                    </td>
                                    <td class="px-4 py-2.5">
                                        <div class="relative">
                                            <span class="absolute left-2 top-1 text-xs text-gray-400">$</span>
                                            <input wire:model.live="items.{{ $index }}.unit_price"
                                                type="number" step="0.01" min="0"
                                                class="w-full border border-gray-200 rounded pl-5 pr-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-300">
                                        </div>
                                    </td>
                                    <td class="px-4 py-2.5 text-gray-700 font-medium">
                                        ${{ number_format($item['quantity'] * $item['unit_price'], 2) }}
                                    </td>
                                    <td class="px-4 py-2.5">
                                        <button type="button" wire:click="removeItem({{ $index }})"
                                            class="text-red-400 hover:text-red-600 transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-50 border-t border-gray-100">
                                <td colspan="3" class="px-4 py-2.5 text-xs font-medium text-gray-500 text-right">Total:</td>
                                <td class="px-4 py-2.5 font-medium text-gray-900">
                                    ${{ number_format(collect($items)->sum(fn($i) => $i['quantity'] * $i['unit_price']), 2) }}
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                </div>
            @else
                <div class="border-2 border-dashed border-gray-200 rounded-lg py-8 text-center text-gray-400 text-sm">
                    Busca y agrega productos al movimiento
                </div>
            @endif
        </div>

        <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pb-6">
            <a wire:navigate href="{{ route('inventory.movements.index') }}"
                class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition text-center">
                Cancelar
            </a>
            <button type="submit"
                class="px-5 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition">
                Confirmar movimiento
            </button>
        </div>
    </form>
</div>
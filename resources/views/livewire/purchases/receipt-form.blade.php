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

    <div class="space-y-5">

        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">Datos de recepción</h2>

            {{-- Tipo de recepción --}}
            <div>
                <label class="block text-xs text-gray-500 mb-2">Tipo de recepción *</label>
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
                        Los productos se enviarán al almacén de defectuosos de tu sucursal. No se actualizarán precios.
                    </p>
                @endif
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Almacén --}}
                <div class="sm:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">Almacén de recepción *</label>
                    <select wire:model.live="warehouse_id"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        <option value="">— Seleccionar almacén —</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ $warehouse_id == $warehouse->id ? 'selected' : '' }}>
                                {{ $warehouse->name }} — {{ $warehouse->branch->name }}{{ $warehouse->is_defective ? ' (Defectuosos)' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('warehouse_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
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
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Notas</label>
                    <input wire:model="notes" type="text"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                        placeholder="Observaciones de la recepción">
                </div>
            </div>
        </div>

        {{-- Tabla de productos --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100">
                <h2 class="text-sm font-medium text-gray-700">Productos a recibir</h2>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Producto</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500">Pendiente</th>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-28">Cant. a recibir</th>
                        @if($reception_type !== 'defective')
                            <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-28">Precio costo</th>
                            <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-28">Gastos op. %</th>
                        @endif
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
                            @if($reception_type !== 'defective')
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
                            @endif
                            <td class="px-4 py-3">
                                @if($reception_type === 'defective')
                                    <span class="text-xs text-amber-600">Almacén defectuosos</span>
                                @else
                                    <select wire:model="items.{{ $index }}.warehouse_id"
                                        class="w-full border border-gray-200 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-300">
                                        @foreach($warehouses as $warehouse)
                                            <option value="{{ $warehouse->id }}" {{ $item['warehouse_id'] == $warehouse->id ? 'selected' : '' }}>
                                                {{ $warehouse->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                @endif
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
        </div>

        @if(count($items) > 0)
            <div class="flex items-center justify-end gap-3 pb-6">
                <a href="{{ route('purchases.orders.show', $order) }}"
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
                        Orden: <strong>{{ $order->folio }}</strong> ·
                        Tipo: <strong>{{ \App\Models\PurchaseReceipt::RECEPTION_TYPES[$reception_type] }}</strong>
                    </p>
                </div>
                <div class="p-6 space-y-3">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Productos que se registrarán</p>
                    <div class="border border-gray-100 rounded-lg overflow-hidden">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-100 text-xs text-gray-500">
                                    <th class="text-left px-4 py-2">Producto</th>
                                    <th class="text-right px-4 py-2">Cantidad</th>
                                    <th class="text-left px-4 py-2">Almacén</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($items as $item)
                                    @if($item['quantity_received'] > 0)
                                        @php
                                            $wh = $warehouses->firstWhere('id', $reception_type === 'defective' ? $warehouse_id : $item['warehouse_id']);
                                        @endphp
                                        <tr>
                                            <td class="px-4 py-2.5 font-medium text-gray-900">{{ $item['product_name'] }}</td>
                                            <td class="px-4 py-2.5 text-right text-gray-700">{{ $item['quantity_received'] }}</td>
                                            <td class="px-4 py-2.5 text-gray-500 text-xs">{{ $wh?->name ?? '—' }}</td>
                                        </tr>
                                    @endif
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

<div class="max-w-5xl">
    {{-- Header --}}
    <div class="flex items-start justify-between gap-3 mb-6">
        <div class="flex items-center gap-3">
            <a wire:navigate href="{{ route('inventory.transfers.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-medium text-gray-900">
                    @if($mode === 'create')
                        Nueva transferencia de inventario
                    @else
                        Transferencia {{ $stockMovement->folio }}
                    @endif
                </h1>
                @if($stockMovement)
                    @php
                        $statusColors = [
                            'requested'          => 'bg-blue-50 text-blue-700',
                            'in_transit'         => 'bg-amber-50 text-amber-700',
                            'partially_received' => 'bg-orange-50 text-orange-700',
                            'completed'          => 'bg-emerald-50 text-emerald-700',
                            'cancelled'          => 'bg-red-50 text-red-700',
                        ];
                    @endphp
                    <span class="inline-flex mt-1 px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$stockMovement->status] ?? 'bg-gray-100 text-gray-500' }}">
                        {{ \App\Models\StockMovement::TRANSFER_STATUSES[$stockMovement->status] ?? $stockMovement->status }}
                    </span>
                @endif
            </div>
        </div>

        {{-- Action buttons (receive mode) --}}
        @if($mode === 'receive')
            <div class="flex gap-2 flex-wrap justify-end">
                @if($stockMovement->status === 'requested')
                    <button wire:click="markInTransit" wire:confirm="¿Marcar como en tránsito?"
                        class="px-3 py-1.5 text-xs font-medium bg-amber-500 hover:bg-amber-600 text-white rounded-lg transition">
                        Marcar en tránsito
                    </button>
                @endif
                <button wire:click="cancelTransfer" wire:confirm="¿Cancelar esta transferencia?"
                    class="px-3 py-1.5 text-xs font-medium border border-red-200 text-red-600 hover:bg-red-50 rounded-lg transition">
                    Cancelar transferencia
                </button>
            </div>
        @endif
    </div>

    <x-alert />

    {{-- Warehouses & meta --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4 mb-5">
        <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">Origen y destino</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Almacén origen *</label>
                @if($mode === 'create')
                    <select wire:model.live="warehouse_id"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        <option value="">— Seleccionar —</option>
                        @foreach($warehouses as $w)
                            <option value="{{ $w->id }}">{{ $w->name }} — {{ $w->branch->name }}</option>
                        @endforeach
                    </select>
                    @error('warehouse_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                @else
                    <p class="text-sm text-gray-900 py-2">{{ $stockMovement->warehouse?->name }} <span class="text-xs text-gray-400">— {{ $stockMovement->warehouse?->branch?->name }}</span></p>
                @endif
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Almacén destino *</label>
                @if($mode === 'create')
                    <select wire:model="warehouse_destination_id"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        <option value="">— Seleccionar —</option>
                        @foreach($warehouses as $w)
                            @if($w->id != $warehouse_id)
                                <option value="{{ $w->id }}">{{ $w->name }} — {{ $w->branch->name }}</option>
                            @endif
                        @endforeach
                    </select>
                    @error('warehouse_destination_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                @else
                    <p class="text-sm text-gray-900 py-2">{{ $stockMovement->warehouseDestination?->name }} <span class="text-xs text-gray-400">— {{ $stockMovement->warehouseDestination?->branch?->name }}</span></p>
                @endif
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Fecha solicitada</label>
                @if($mode === 'create')
                    <input wire:model="moved_at" type="datetime-local"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                @else
                    <p class="text-sm text-gray-900 py-2">{{ $stockMovement->moved_at->format('d/m/Y H:i') }}</p>
                @endif
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Referencia</label>
                @if($mode !== 'readonly')
                    <input wire:model="reference" type="text"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                        placeholder="OC, nota de remisión...">
                @else
                    <p class="text-sm text-gray-900 py-2">{{ $reference ?: '—' }}</p>
                @endif
            </div>
            <div class="sm:col-span-2 lg:col-span-4">
                <label class="block text-xs text-gray-500 mb-1">Notas</label>
                @if($mode !== 'readonly')
                    <input wire:model="notes" type="text"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                @else
                    <p class="text-sm text-gray-700 py-2">{{ $notes ?: '—' }}</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Transfer items table --}}
    <div class="bg-white rounded-xl border border-gray-200 mb-5 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3.5 border-b border-gray-100">
            <h2 class="text-sm font-medium text-gray-700">
                Productos solicitados
                <span class="ml-2 text-xs font-normal text-gray-400">{{ count($items) }} producto(s)</span>
                @if($mode === 'receive')
                    <span class="ml-2 text-xs font-normal text-amber-600 bg-amber-50 border border-amber-100 px-2 py-0.5 rounded-full">Puedes agregar productos adicionales</span>
                @endif
            </h2>
            @if($mode !== 'readonly')
                <livewire:shared.product-picker :warehouseId="$warehouse_id" :wire:key="'transfer-picker-' . ($warehouse_id ?? 0)" />
            @endif
        </div>

        @error('items') <p class="text-xs text-red-500 px-5 py-2">{{ $message }}</p> @enderror

        @if(count($items) > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm" style="min-width:700px">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Producto</th>
                            <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-28">Stock origen</th>
                            <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-28">Cant. solicitada</th>
                            @if($mode === 'receive')
                                <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-28">Cant. recibida</th>
                                <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-32">Fecha recepción</th>
                            @endif
                            <th class="w-10"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($items as $index => $item)
                            <tr class="{{ $item['is_late_addition'] ? 'bg-amber-50/40' : '' }} hover:bg-gray-50 transition">
                                <td class="px-4 py-2.5">
                                    <div class="flex items-start gap-2">
                                        <div>
                                            <p class="font-medium text-gray-900 text-sm">{{ $item['product_name'] }}</p>
                                            @if($item['sku'])
                                                <p class="text-xs text-gray-400 font-mono">{{ $item['sku'] }}</p>
                                            @endif
                                        </div>
                                        @if($item['is_late_addition'])
                                            <span class="flex-shrink-0 inline-flex items-center gap-1 bg-amber-100 text-amber-700 border border-amber-200 text-[10px] font-medium px-1.5 py-0.5 rounded-full">
                                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                </svg>
                                                Agregado {{ $item['added_at'] }}
                                            </span>
                                        @endif
                                        @if(!empty($item['received_quantity']) && $item['received_quantity'] > 0)
                                            <span class="flex-shrink-0 inline-flex items-center bg-emerald-100 text-emerald-700 text-[10px] font-medium px-1.5 py-0.5 rounded-full border border-emerald-200">
                                                Recibido
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-2.5 text-gray-600 text-sm">
                                    {{ number_format($item['stock_origen'], 2) }}
                                </td>
                                <td class="px-4 py-2.5">
                                    @if($mode === 'create' || ($item['is_late_addition'] && empty($item['received_quantity'])))
                                        <input wire:model.live="items.{{ $index }}.quantity"
                                            @if($item['is_late_addition'] && $item['item_id'])
                                                wire:change="updateLateItemQty({{ $index }})"
                                            @endif
                                            type="number" step="0.01" min="0.01"
                                            max="{{ $item['stock_origen'] }}"
                                            class="w-full border border-gray-200 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-300">
                                    @else
                                        <span class="text-sm text-gray-700">{{ number_format($item['quantity'], 2) }}</span>
                                    @endif
                                </td>
                                @if($mode === 'receive')
                                    <td class="px-4 py-2.5">
                                        @if(empty($item['received_quantity']))
                                            <input wire:model="items.{{ $index }}.received_quantity"
                                                type="number" step="0.01" min="0"
                                                max="{{ $item['quantity'] }}"
                                                placeholder="0"
                                                class="w-full border border-gray-200 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-300">
                                        @else
                                            <span class="text-sm font-medium text-emerald-700">{{ number_format($item['received_quantity'], 2) }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2.5">
                                        @if(empty($item['received_quantity']))
                                            <input wire:model="items.{{ $index }}.received_at"
                                                type="date"
                                                class="w-full border border-gray-200 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-300">
                                        @else
                                            <span class="text-xs text-gray-500">
                                                {{ $item['received_at'] ? \Carbon\Carbon::parse($item['received_at'])->format('d/m/Y') : '—' }}
                                            </span>
                                        @endif
                                    </td>
                                @endif
                                <td class="px-4 py-2.5">
                                    @if($mode !== 'readonly' && empty($item['received_quantity']))
                                        <button type="button" wire:click="removeItem({{ $index }})"
                                            class="text-red-400 hover:text-red-600 transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="py-10 text-center text-gray-400 text-sm">
                {{ $warehouse_id ? 'Agrega productos desde el catálogo de arriba.' : 'Selecciona un almacén origen para ver el catálogo de productos.' }}
            </div>
        @endif
    </div>

    {{-- Footer actions --}}
    @if($mode === 'create')
        <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pb-6">
            <a wire:navigate href="{{ route('inventory.transfers.index') }}"
                class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition text-center">
                Cancelar
            </a>
            <button wire:click="save" wire:loading.attr="disabled"
                class="px-5 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition disabled:opacity-60">
                <span wire:loading.remove wire:target="save">Solicitar transferencia</span>
                <span wire:loading wire:target="save">Guardando...</span>
            </button>
        </div>
    @elseif($mode === 'receive')
        <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pb-6">
            <a wire:navigate href="{{ route('inventory.transfers.index') }}"
                class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition text-center">
                Cerrar
            </a>
            <button wire:click="saveReceipt" wire:loading.attr="disabled"
                class="px-5 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition disabled:opacity-60">
                <span wire:loading.remove wire:target="saveReceipt">Guardar recepción</span>
                <span wire:loading wire:target="saveReceipt">Guardando...</span>
            </button>
        </div>
    @else
        <div class="flex justify-end pb-6">
            <a wire:navigate href="{{ route('inventory.transfers.index') }}"
                class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                Volver al listado
            </a>
        </div>
    @endif
</div>

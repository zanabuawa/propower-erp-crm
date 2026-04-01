<div class="max-w-4xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('purchases.index') }}" wire:navigate class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="text-xl font-medium text-gray-900">Nueva requisición de compra</h1>
    </div>

    <form wire:submit="save" class="space-y-5">

        {{-- Datos generales --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">Datos generales</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Moneda</label>
                    <select wire:model="currency"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        <option value="MXN">MXN — Peso mexicano</option>
                        <option value="USD">USD — Dólar americano</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Fecha requerida *</label>
                    <input wire:model="needed_by" type="date" value="{{ $needed_by }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    @error('needed_by') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">Justificación *</label>
                    <textarea wire:model="justification" rows="3"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                        placeholder="¿Por qué se necesita esta compra?"></textarea>
                    @error('justification') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Productos --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h2 class="text-sm font-medium text-gray-700">Productos solicitados</h2>
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
                    <div class="absolute top-full left-0 right-0 bg-white border border-gray-200 rounded-xl shadow-xl z-10 mt-1 overflow-hidden">
                        @foreach($productResults as $result)
                            <div class="px-4 py-2.5 border-b border-gray-50 last:border-0 hover:bg-indigo-50 transition">
                                <div class="flex items-center justify-between mb-1">
                                    <p class="text-sm font-medium text-gray-900">{{ $result['name'] }}</p>
                                    <span class="text-xs font-semibold text-indigo-600">${{ number_format($result['purchase_price'], 2) }}</span>
                                </div>
                                <p class="text-xs text-gray-400 mb-1.5">SKU: {{ $result['sku'] ?? '—' }}@if($result['barcode'] ?? null) · CB: {{ $result['barcode'] }}@endif</p>
                                <div class="flex gap-2 flex-wrap">
                                    @foreach($items as $index => $item)
                                        <button type="button" wire:click="selectProduct({{ $index }}, {{ $result['id'] }})"
                                            class="text-xs bg-indigo-50 hover:bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded transition">
                                            → Línea {{ $index + 1 }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            @error('items') <p class="text-xs text-red-500">{{ $message }}</p> @enderror

            <div class="space-y-3">
                @foreach($items as $index => $item)
                    <div class="border border-gray-100 rounded-lg p-4">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div class="sm:col-span-3">
                                <label class="block text-xs text-gray-500 mb-1">Descripción *</label>
                                <input wire:model="items.{{ $index }}.description" type="text"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                                    placeholder="Nombre o descripción del producto">
                                @error("items.{$index}.description") <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Cantidad *</label>
                                <input wire:model="items.{{ $index }}.quantity" type="number" step="0.01" min="0.01"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                                @error("items.{$index}.quantity") <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Unidad</label>
                                <input wire:model="items.{{ $index }}.unit" type="text"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                                    placeholder="pz, kg, lt...">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Precio estimado</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2 text-xs text-gray-400">$</span>
                                    <input wire:model="items.{{ $index }}.unit_price" type="number" step="0.01" min="0"
                                        class="w-full border border-gray-200 rounded-lg pl-6 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                                </div>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs text-gray-500 mb-1">Notas</label>
                                <input wire:model="items.{{ $index }}.notes" type="text"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                                    placeholder="Especificaciones adicionales">
                            </div>
                        </div>
                        @if(count($items) > 1)
                            <div class="flex justify-end mt-2">
                                <button type="button" wire:click="removeItem({{ $index }})"
                                    class="text-xs text-red-500 hover:text-red-700">Eliminar línea</button>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <button type="button" wire:click="addItem"
                class="w-full py-2 border-2 border-dashed border-gray-200 rounded-lg text-sm text-gray-400 hover:border-indigo-300 hover:text-indigo-500 transition">
                + Agregar otro producto
            </button>

            {{-- Total estimado --}}
            @php
                $total = collect($items)->sum(fn($i) => ($i['quantity'] ?? 0) * ($i['unit_price'] ?? 0));
            @endphp
            @if($total > 0)
                <div class="flex justify-end text-sm font-medium text-gray-900">
                    Total estimado: {{ $currency }} ${{ number_format($total, 2) }}
                </div>
            @endif
        </div>

        <div class="flex items-center justify-end gap-3 pb-6">
            <a href="{{ route('purchases.index') }}" wire:navigate
                class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                Cancelar
            </a>
            <button type="submit"
                class="px-5 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition">
                Enviar requisición
            </button>
        </div>
    </form>
</div>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center gap-3 mb-6">
        <a wire:navigate href="{{ route('purchases.index') }}" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-xl font-medium text-gray-900">Nueva requisición de compra</h1>
            <p class="text-sm text-gray-500">Solicita productos para que el área de compras los cotice</p>
        </div>
    </div>

    <form wire:submit="save" class="space-y-5">

        {{-- ── Datos generales ────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 lg:p-6 space-y-4 shadow-sm">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">Datos generales</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 lg:gap-5">
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
                    <input wire:model="needed_by" type="date"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    @error('needed_by') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2 lg:col-span-1 xl:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">Justificación *</label>
                    <textarea wire:model="justification" rows="2"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                        placeholder="¿Por qué se necesita esta compra?"></textarea>
                    @error('justification') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- ── Productos solicitados ───────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4 shadow-sm">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <div>
                    <h2 class="text-sm font-medium text-gray-700">Productos solicitados</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Agrega los artículos que necesitas</p>
                </div>
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
                            <button type="button" wire:click="addProduct({{ $result['id'] }})"
                                class="w-full text-left px-4 py-2.5 hover:bg-indigo-50 transition flex items-center justify-between border-b border-gray-50 last:border-0">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $result['name'] }}</p>
                                    <p class="text-xs text-gray-400">SKU: {{ $result['sku'] ?? '—' }}</p>
                                </div>
                                <div class="text-right flex-shrink-0 ml-4">
                                    <p class="text-xs font-semibold text-indigo-600">${{ number_format($result['purchase_price'], 2) }}</p>
                                    <p class="text-xs text-indigo-400">+ Agregar</p>
                                </div>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            @error('items') <p class="text-xs text-red-500">{{ $message }}</p> @enderror

            <div class="border border-gray-100 rounded-lg overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Descripción / Producto</th>
                            <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-28">Cantidad</th>
                            <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-24">Unidad</th>
                            <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-32">Precio est.</th>
                            <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Notas</th>
                            <th class="w-10"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($items as $index => $item)
                            <tr>
                                <td class="px-4 py-2.5">
                                    <input wire:model="items.{{ $index }}.description" type="text"
                                        class="w-full border-none focus:ring-0 p-0 text-sm placeholder-gray-300"
                                        placeholder="Nombre del producto...">
                                    @error("items.{$index}.description") <p class="text-[10px] text-red-500">{{ $message }}</p> @enderror
                                </td>
                                <td class="px-4 py-2.5">
                                    <input wire:model="items.{{ $index }}.quantity" type="number" step="0.01" min="0.01"
                                        class="w-full border-gray-200 rounded px-2 py-1 text-sm focus:ring-indigo-300">
                                    @error("items.{$index}.quantity") <p class="text-[10px] text-red-500">{{ $message }}</p> @enderror
                                </td>
                                <td class="px-4 py-2.5">
                                    <input wire:model="items.{{ $index }}.unit" type="text"
                                        class="w-full border-gray-200 rounded px-2 py-1 text-sm focus:ring-indigo-300 placeholder-gray-300"
                                        placeholder="pz, kg...">
                                </td>
                                <td class="px-4 py-2.5">
                                    <div class="relative">
                                        <span class="absolute left-2 top-1.5 text-xs text-gray-400">$</span>
                                        <input wire:model="items.{{ $index }}.unit_price" type="number" step="0.01" min="0"
                                            class="w-full border-gray-200 rounded pl-5 pr-2 py-1 text-sm focus:ring-indigo-300">
                                    </div>
                                    @error("items.{$index}.unit_price") <p class="text-[10px] text-red-500">{{ $message }}</p> @enderror
                                </td>
                                <td class="px-4 py-2.5">
                                    <input wire:model="items.{{ $index }}.notes" type="text"
                                        class="w-full border-none focus:ring-0 p-0 text-sm placeholder-gray-300"
                                        placeholder="Notas...">
                                </td>
                                <td class="px-4 py-2.5 text-center">
                                    <button type="button" wire:click="removeItem({{ $index }})"
                                        class="text-gray-300 hover:text-red-500 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-400 italic">
                                    No hay productos agregados. Usa el buscador o el botón de abajo.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <button type="button" wire:click="addItem"
                class="w-full py-2 border-2 border-dashed border-gray-200 rounded-lg text-sm text-gray-400 hover:border-indigo-300 hover:text-indigo-500 transition-all flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Agregar línea manual
            </button>

            {{-- Total estimado --}}
            @php
                $total = collect($items)->sum(fn($i) => ($i['quantity'] ?? 0) * ($i['unit_price'] ?? 0));
            @endphp
            @if($total > 0)
                <div class="flex justify-end pr-4 text-sm">
                    <div class="flex flex-col items-end">
                        <span class="text-gray-500 text-xs">Total estimado</span>
                        <span class="text-lg font-bold text-gray-900">{{ $currency }} ${{ number_format($total, 2) }}</span>
                    </div>
                </div>
            @endif
        </div>

        <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pb-6">
            <a wire:navigate href="{{ route('purchases.index') }}"
                class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition text-center">
                Cancelar
            </a>
            <button type="submit"
                class="px-5 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition shadow-sm hover:shadow-md">
                Crear requisición
            </button>
        </div>
    </form>
</div>

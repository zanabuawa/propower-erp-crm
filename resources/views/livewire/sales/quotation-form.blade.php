<div class="max-w-4xl">
    <div class="flex items-center gap-3 mb-6">
        <a wire:navigate href="{{ route('sales.index') }}" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="text-xl font-medium text-gray-900">Nueva cotización</h1>
    </div>

    <form wire:submit="save" class="space-y-5">

        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">Datos generales</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">Cliente *</label>
                    <select wire:model.live="customer_id"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        <option value="">— Seleccionar cliente —</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ $customer_id == $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('customer_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Lista de precios</label>
                    <select wire:model="price_list_id"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        <option value="">— Precio estándar —</option>
                        @foreach($priceLists as $list)
                            <option value="{{ $list->id }}" {{ $price_list_id == $list->id ? 'selected' : '' }}>
                                {{ $list->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Moneda</label>
                    <select wire:model="currency"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        <option value="MXN">MXN — Peso mexicano</option>
                        <option value="USD">USD — Dólar americano</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Vigencia (días)</label>
                    <input wire:model="valid_days" type="number" min="1" value="{{ $valid_days }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Descuento global %</label>
                    <input wire:model.live="global_discount" type="number" step="0.01" min="0" max="100" value="{{ $global_discount }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">Notas</label>
                    <textarea wire:model="notes" rows="2"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"></textarea>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">Términos y condiciones</label>
                    <textarea wire:model="terms" rows="2"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"></textarea>
                </div>
            </div>
        </div>

        {{-- Productos --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h2 class="text-sm font-medium text-gray-700">Productos y servicios</h2>
                <livewire:shared.product-picker />
            </div>

            {{-- Nota IVA + controles bulk --}}
            <div class="flex flex-col sm:flex-row sm:items-center gap-3 bg-amber-50 border border-amber-200 rounded-lg px-4 py-2.5">
                <div class="flex items-start gap-2 flex-1 min-w-0">
                    <svg class="w-4 h-4 text-amber-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-xs text-amber-700">
                        <strong>IVA:</strong> por defecto se asume que el precio ya incluye IVA. Marca la casilla en cada producto cuyo precio <em>no</em> incluye IVA para que se calcule el 16% adicional.
                    </p>
                </div>
                <div class="flex gap-2 shrink-0">
                    <button type="button" wire:click="setAllIva(true)"
                        class="px-2.5 py-1 text-xs border border-amber-300 text-amber-700 hover:bg-amber-100 rounded-lg transition whitespace-nowrap">
                        Marcar todos
                    </button>
                    <button type="button" wire:click="setAllIva(false)"
                        class="px-2.5 py-1 text-xs border border-amber-300 text-amber-700 hover:bg-amber-100 rounded-lg transition whitespace-nowrap">
                        Quitar todos
                    </button>
                </div>
            </div>

            {{-- Búsqueda rápida inline --}}
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
                                    <p class="text-xs text-gray-400">
                                        SKU: {{ $result['sku'] ?? '—' }}
                                        @if($result['barcode'] ?? null) · CB: {{ $result['barcode'] }} @endif
                                    </p>
                                </div>
                                <div class="text-right flex-shrink-0 ml-4">
                                    <p class="text-xs font-semibold text-indigo-600">${{ number_format($result['sale_price'], 2) }}</p>
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
                            <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Descripción</th>
                            <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-20">Cant.</th>
                            <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-28">Precio</th>
                            <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-20">Desc %</th>
                            <th class="text-center px-4 py-2.5 text-xs font-medium text-gray-500 w-28">IVA</th>
                            <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-28">Subtotal</th>
                            <th class="w-8"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($items as $index => $item)
                            @php
                                $unitPrice    = (float)($item['unit_price'] ?? 0);
                                $discPct      = (float)($item['discount_pct'] ?? 0);
                                $minPrice     = (float)($item['min_sale_price'] ?? 0);
                                $maxDiscPct   = (float)($item['max_discount_pct'] ?? 100);
                                $finalPrice   = round($unitPrice * (1 - $discPct / 100), 2);
                                $discExceeded = $minPrice > 0 && $finalPrice < $minPrice;
                                $sub  = ($item['quantity'] ?? 0) * $unitPrice;
                                $disc = $sub * ($discPct / 100);
                            @endphp
                            <tr class="{{ $discExceeded ? 'bg-red-50' : '' }}">
                                <td class="px-4 py-2">
                                    <input wire:model="items.{{ $index }}.description" type="text"
                                        class="w-full border border-gray-200 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-300">
                                </td>
                                <td class="px-4 py-2">
                                    <input wire:model.live="items.{{ $index }}.quantity" type="number" step="0.01" min="0.01"
                                        class="w-full border border-gray-200 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-300">
                                </td>
                                <td class="px-4 py-2">
                                    <div class="relative">
                                        <span class="absolute left-2 top-1 text-xs text-gray-400">$</span>
                                        <input wire:model.live="items.{{ $index }}.unit_price" type="number" step="0.01" min="0"
                                            class="w-full border border-gray-200 rounded pl-5 pr-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-300">
                                    </div>
                                </td>
                                <td class="px-4 py-2">
                                    <input wire:model.live="items.{{ $index }}.discount_pct" type="number" step="0.01" min="0"
                                        max="{{ $maxDiscPct }}"
                                        class="w-full border rounded px-2 py-1 text-sm focus:outline-none focus:ring-1
                                            {{ $discExceeded ? 'border-red-400 focus:ring-red-300' : 'border-gray-200 focus:ring-indigo-300' }}">
                                    @if($minPrice > 0)
                                        <p class="text-[10px] mt-0.5 {{ $discExceeded ? 'text-red-500 font-medium' : 'text-gray-400' }}">
                                            Máx: {{ number_format($maxDiscPct, 2) }}%
                                            @if($discExceeded) · mín ${{ number_format($minPrice, 2) }} @endif
                                        </p>
                                    @endif
                                    @error("items.{$index}.discount_pct")
                                        <p class="text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </td>
                                <td class="px-4 py-2">
                                    @php $ivaNoIncluido = ($item['tax_rate'] ?? 0) != 0; @endphp
                                    <label class="flex items-center gap-2 cursor-pointer justify-center select-none">
                                        <input type="checkbox"
                                            wire:click="toggleItemIva({{ $index }})"
                                            {{ $ivaNoIncluido ? 'checked' : '' }}
                                            class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                                        <span class="text-xs {{ $ivaNoIncluido ? 'text-gray-700' : 'text-indigo-600 font-medium' }}">
                                            {{ $ivaNoIncluido ? '+ 16%' : 'Incluido' }}
                                        </span>
                                    </label>
                                </td>
                                <td class="px-4 py-2 font-medium text-xs {{ $discExceeded ? 'text-red-600' : 'text-gray-700' }}">
                                    ${{ number_format($sub - $disc, 2) }}
                                </td>
                                <td class="px-4 py-2">
                                    @if(count($items) > 1)
                                        <button type="button" wire:click="removeItem({{ $index }})"
                                            class="text-red-400 hover:text-red-600">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-50 border-t border-gray-100">
                            <td colspan="5" class="px-4 py-2 text-xs text-gray-500 text-right">Subtotal:</td>
                            <td class="px-4 py-2 text-sm font-medium">${{ number_format($this->subtotal, 2) }}</td>
                            <td></td>
                        </tr>
                        <tr class="bg-gray-50">
                            <td colspan="5" class="px-4 py-2 text-xs text-gray-500 text-right">Descuento:</td>
                            <td class="px-4 py-2 text-sm font-medium text-red-600">-${{ number_format($this->discount, 2) }}</td>
                            <td></td>
                        </tr>
                        <tr class="bg-gray-50">
                            <td colspan="5" class="px-4 py-2 text-xs text-gray-500 text-right">IVA:</td>
                            <td class="px-4 py-2 text-sm font-medium">${{ number_format($this->tax, 2) }}</td>
                            <td></td>
                        </tr>
                        <tr class="bg-gray-50 border-t border-gray-200">
                            <td colspan="5" class="px-4 py-2 text-xs font-semibold text-gray-700 text-right">Total:</td>
                            <td class="px-4 py-2 text-sm font-semibold text-gray-900">
                                {{ $currency }} ${{ number_format($this->total, 2) }}
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <button type="button" wire:click="addItem"
                class="w-full py-2 border-2 border-dashed border-gray-200 rounded-lg text-sm text-gray-400 hover:border-indigo-300 hover:text-indigo-500 transition">
                + Agregar producto
            </button>
        </div>

        <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pb-6">
            <a wire:navigate href="{{ route('sales.index') }}"
                class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                Cancelar
            </a>
            <button type="submit"
                class="px-5 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition">
                Crear cotización
            </button>
        </div>
    </form>
</div>
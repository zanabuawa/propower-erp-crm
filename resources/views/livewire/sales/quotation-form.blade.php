<div class="max-w-5xl mx-auto">
    <div class="flex items-center gap-3 mb-6 px-1">
        <a wire:navigate href="{{ route('sales.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-xl font-medium text-gray-900">Nueva cotización</h1>
            <p class="text-sm text-gray-500">Crea una propuesta comercial para tu cliente</p>
        </div>
    </div>

    <form wire:submit="save" class="space-y-5">

        {{-- ── Datos generales ────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4 shadow-sm">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">Datos generales</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="sm:col-span-2 lg:col-span-2">
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
                <div class="lg:col-span-1">
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
                <div class="lg:col-span-1">
                    <label class="block text-xs text-gray-500 mb-1">Moneda</label>
                    <select wire:model="currency"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        <option value="MXN">MXN — Peso mexicano</option>
                        <option value="USD">USD — Dólar americano</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Vigencia (días)</label>
                    <input wire:model="valid_days" type="number" min="1"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Descuento global %</label>
                    <input wire:model.live="global_discount" type="number" step="0.01" min="0" max="100"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>
                <div class="sm:col-span-2 lg:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">Notas</label>
                    <input wire:model="notes" type="text"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                        placeholder="Notas para el cliente...">
                </div>
                <div class="sm:col-span-2 lg:col-span-4">
                    <label class="block text-xs text-gray-500 mb-1">Términos y condiciones</label>
                    <textarea wire:model="terms" rows="2"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                        placeholder="Condiciones de pago, entrega, etc."></textarea>
                </div>
            </div>
        </div>

        {{-- ── Productos y servicios ────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4 shadow-sm">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <div>
                    <h2 class="text-sm font-medium text-gray-700">Productos y servicios</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Agrega los ítems de la cotización</p>
                </div>
                <livewire:shared.product-picker />
            </div>

            {{-- Nota IVA + controles bulk --}}
            <div class="flex flex-col sm:flex-row sm:items-center gap-3 bg-amber-50 border border-amber-200 rounded-lg px-4 py-2.5">
                <div class="flex items-start gap-2 flex-1 min-w-0">
                    <svg class="w-4 h-4 text-amber-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-[11px] text-amber-700 leading-tight">
                        <strong>IVA:</strong> Por defecto el precio incluye IVA. Marca "Exento/Desglosar" si el precio <em>no</em> incluye IVA para sumar el 16%.
                    </p>
                </div>
                <div class="flex gap-2 shrink-0">
                    <button type="button" wire:click="setAllIva(true)"
                        class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider border border-amber-300 text-amber-700 hover:bg-amber-100 rounded-lg transition">
                        Masivo: +IVA
                    </button>
                    <button type="button" wire:click="setAllIva(false)"
                        class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider border border-amber-300 text-amber-700 hover:bg-amber-100 rounded-lg transition">
                        Masivo: Sin IVA
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
                    <div class="absolute top-full left-0 right-0 bg-white border border-gray-200 rounded-xl shadow-xl z-20 mt-1 overflow-hidden">
                        @foreach($productResults as $result)
                            <button type="button" wire:click="addProduct({{ $result['id'] }})"
                                class="w-full text-left px-4 py-2.5 hover:bg-indigo-50 transition flex items-center justify-between border-b border-gray-50 last:border-0">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $result['name'] }}</p>
                                    <p class="text-xs text-gray-400">SKU: {{ $result['sku'] ?? '—' }}</p>
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
                <table class="w-full text-sm min-w-[700px]">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Descripción / Producto</th>
                            <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-20">Cant.</th>
                            <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-28">Precio Unit.</th>
                            <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-20">Desc %</th>
                            <th class="text-center px-4 py-2.5 text-xs font-medium text-gray-500 w-32">IVA (16%)</th>
                            <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500 w-28">Subtotal</th>
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
                                    @error("items.{$index}.description") <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p> @enderror
                                </td>
                                <td class="px-4 py-2.5">
                                    <input wire:model.live="items.{{ $index }}.quantity" type="number" step="0.01" min="0.01"
                                        class="w-full border-gray-200 rounded px-2 py-1 text-sm focus:ring-indigo-300">
                                    @error("items.{$index}.quantity") <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p> @enderror
                                </td>
                                <td class="px-4 py-2.5">
                                    <div class="relative">
                                        <span class="absolute left-2 top-1.5 text-xs text-gray-400">$</span>
                                        <input wire:model.live="items.{{ $index }}.unit_price" type="number" step="0.01" min="0"
                                            class="w-full border-gray-200 rounded pl-5 pr-2 py-1 text-sm focus:ring-indigo-300">
                                    </div>
                                    @error("items.{$index}.unit_price") <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p> @enderror
                                </td>
                                <td class="px-4 py-2.5">
                                    <input wire:model.live="items.{{ $index }}.discount_pct" type="number" step="0.01" min="0" max="100"
                                        class="w-full border-gray-200 rounded px-2 py-1 text-sm focus:ring-indigo-300">
                                    @error("items.{$index}.discount_pct") <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p> @enderror
                                </td>
                                <td class="px-4 py-2.5 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <span class="text-[10px] font-medium {{ ($item['tax_rate'] ?? 0) > 0 ? 'text-indigo-600' : 'text-gray-400' }}">
                                            {{ ($item['tax_rate'] ?? 0) > 0 ? '+IVA (16%)' : 'Exento' }}
                                        </span>
                                        <button type="button" wire:click="toggleItemIva({{ $index }})"
                                            class="w-8 h-4 rounded-full relative transition-colors focus:outline-none {{ ($item['tax_rate'] ?? 0) > 0 ? 'bg-indigo-600' : 'bg-gray-200' }}">
                                            <div class="absolute top-0.5 left-0.5 w-3 h-3 rounded-full bg-white transition-transform {{ ($item['tax_rate'] ?? 0) > 0 ? 'translate-x-4' : '' }}"></div>
                                        </button>
                                    </div>
                                </td>
                                <td class="px-4 py-2.5 text-right text-gray-900 font-medium">
                                    @php
                                        $sub = ($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0);
                                        $disc = $sub * (($item['discount_pct'] ?? 0) / 100);
                                        $tax = ($sub - $disc) * (($item['tax_rate'] ?? 0) / 100);
                                    @endphp
                                    ${{ number_format($sub - $disc + $tax, 2) }}
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
                                <td colspan="7" class="px-4 py-8 text-center text-gray-400 italic">
                                    No hay productos agregados. Usa el buscador o agrega una línea manual.
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
        </div>

        {{-- ── Totales ──────────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
            <div class="flex flex-col items-end space-y-2">
                <div class="flex justify-between w-full sm:w-64 text-sm text-gray-500">
                    <span>Subtotal</span>
                    <span>${{ number_format($this->subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between w-full sm:w-64 text-sm text-red-500">
                    <span>Descuento</span>
                    <span>- ${{ number_format($this->discount, 2) }}</span>
                </div>
                <div class="flex justify-between w-full sm:w-64 text-sm text-gray-500">
                    <span>IVA</span>
                    <span>${{ number_format($this->tax, 2) }}</span>
                </div>
                <div class="flex justify-between w-full sm:w-64 pt-2 border-t border-gray-100">
                    <span class="text-base font-medium text-gray-900">Total</span>
                    <span class="text-xl font-bold text-indigo-600">{{ $currency }} ${{ number_format($this->total, 2) }}</span>
                </div>
            </div>
        </div>

        <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pb-6">
            <a wire:navigate href="{{ route('sales.index') }}"
                class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition text-center">
                Cancelar
            </a>
            <button type="submit"
                class="px-6 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-bold transition shadow-sm hover:shadow-md active:scale-95">
                Crear cotización
            </button>
        </div>
    </form>
</div>

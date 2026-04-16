<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center gap-3 mb-6">
        <a wire:navigate href="{{ route('purchases.orders.index') }}" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-xl font-medium text-gray-900">Nueva orden de compra</h1>
            @if($sourceLabel)
                <p class="text-xs text-indigo-600 mt-0.5">Desde requisición <span class="font-mono font-medium">{{ $sourceLabel }}</span></p>
            @endif
        </div>
    </div>

    <form wire:submit="save" class="space-y-5">

        {{-- ── Datos generales ────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 lg:p-6 space-y-4 shadow-sm">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">Datos generales</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 lg:gap-5">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Sucursal (destino)</label>
                    <select wire:model="branch_id"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        <option value="">— Sin especificar —</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ $branch_id == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
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
                    <label class="block text-xs text-gray-500 mb-1">Días de crédito</label>
                    <input wire:model="payment_terms" type="number" min="0"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                        placeholder="0 = contado">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Fecha de entrega esperada *</label>
                    <input wire:model="expected_at" type="date"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    @error('expected_at') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Fecha requerida</label>
                    <input wire:model="required_at" type="date"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    <p class="text-xs text-gray-400 mt-1">Fecha límite que requiere el solicitante</p>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Dirección de envío</label>
                    <input wire:model="shipping_address" type="text"
                        placeholder="Calle, número, colonia, ciudad…"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Dirección de facturación</label>
                    <input wire:model="billing_address" type="text"
                        placeholder="Igual a envío si se deja vacío"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Idioma del documento impreso</label>
                    <select wire:model="print_language"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        <option value="es">Español</option>
                        <option value="en">English</option>
                    </select>
                </div>
                <div class="sm:col-span-2 lg:col-span-3 xl:col-span-4">
                    <label class="block text-xs text-gray-500 mb-1">Notas</label>
                    <textarea wire:model="notes" rows="2"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"></textarea>
                </div>
            </div>
        </div>

        {{-- ── Productos de la orden ───────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4 shadow-sm">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <div>
                    <h2 class="text-sm font-medium text-gray-700">Productos de la orden</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Asigna el proveedor de cada producto de forma independiente</p>
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
                                    <p class="text-xs text-gray-400">
                                        SKU: {{ $result['sku'] ?? '—' }}
                                        @if($result['barcode'] ?? null) · CB: {{ $result['barcode'] }} @endif
                                    </p>
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
                            <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Descripción</th>
                            <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-40">Proveedor</th>
                            <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-24">Cantidad</th>
                            <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-20">Unidad</th>
                            @can('view prices')
                            <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-32">Precio unit.</th>
                            <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-20">IVA %</th>
                            <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-28">Subtotal</th>
                            @endcan
                            <th class="w-10"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($items as $index => $item)
                            <tr>
                                <td class="px-4 py-2.5">
                                    <input wire:model="items.{{ $index }}.description" type="text"
                                        class="w-full border-none focus:ring-0 p-0 text-sm placeholder-gray-300"
                                        placeholder="Descripción...">
                                    @error("items.{$index}.description") <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p> @enderror
                                </td>
                                <td class="px-4 py-2.5">
                                    <select wire:model="items.{{ $index }}.supplier_id"
                                        class="w-full border-gray-200 rounded px-2 py-1 text-sm focus:ring-indigo-300">
                                        <option value="">— Sin asignar —</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}" {{ ($item['supplier_id'] ?? null) == $supplier->id ? 'selected' : '' }}>
                                                {{ $supplier->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-4 py-2.5">
                                    <input wire:model.live="items.{{ $index }}.quantity" type="number" step="0.01" min="0.01"
                                        class="w-full border-gray-200 rounded px-2 py-1 text-sm focus:ring-indigo-300">
                                </td>
                                <td class="px-4 py-2.5">
                                    <input wire:model="items.{{ $index }}.unit" type="text"
                                        class="w-full border-gray-200 rounded px-2 py-1 text-sm focus:ring-indigo-300"
                                        placeholder="pza">
                                </td>
                                @can('view prices')
                                <td class="px-4 py-2.5">
                                    <div class="relative">
                                        <span class="absolute left-2 top-1.5 text-xs text-gray-400">$</span>
                                        <input wire:model.live="items.{{ $index }}.unit_price" type="number" step="0.01" min="0"
                                            class="w-full border-gray-200 rounded pl-5 pr-2 py-1 text-sm focus:ring-indigo-300">
                                    </div>
                                </td>
                                <td class="px-4 py-2.5">
                                    <input wire:model.live="items.{{ $index }}.tax_rate" type="number" step="0.01" min="0" max="100"
                                        class="w-full border-gray-200 rounded px-2 py-1 text-sm focus:ring-indigo-300">
                                </td>
                                <td class="px-4 py-2.5 text-gray-700 font-medium text-right">
                                    ${{ number_format(($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0), 2) }}
                                </td>
                                @endcan
                                <td class="px-4 py-2.5">
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
                    @can('view prices')
                    <tfoot>
                        <tr class="bg-gray-50 border-t border-gray-100">
                            <td colspan="6" class="px-4 py-2 text-xs text-gray-500 text-right">Subtotal:</td>
                            <td class="px-4 py-2 text-sm font-medium text-gray-900">${{ number_format($this->subtotal, 2) }}</td>
                            <td></td>
                        </tr>
                        <tr class="bg-gray-50">
                            <td colspan="6" class="px-4 py-2 text-xs text-gray-500 text-right">IVA:</td>
                            <td class="px-4 py-2 text-sm font-medium text-gray-900">${{ number_format($this->tax, 2) }}</td>
                            <td></td>
                        </tr>
                        <tr class="bg-gray-50 border-t border-gray-200">
                            <td colspan="6" class="px-4 py-2 text-xs font-semibold text-gray-700 text-right">Total:</td>
                            <td class="px-4 py-2 text-sm font-semibold text-gray-900">{{ $currency }} ${{ number_format($this->total, 2) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                    @endcan
                </table>
            </div>

            <button type="button" wire:click="addItem"
                class="w-full py-2 border-2 border-dashed border-gray-200 rounded-lg text-sm text-gray-400 hover:border-indigo-300 hover:text-indigo-500 transition">
                + Agregar producto libre
            </button>
        </div>

        <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pb-6">
            <a wire:navigate href="{{ route('purchases.orders.index') }}"
                class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition text-center">Cancelar</a>
            <button type="submit"
                class="px-5 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition">
                Crear orden de compra
            </button>
        </div>
    </form>
</div>

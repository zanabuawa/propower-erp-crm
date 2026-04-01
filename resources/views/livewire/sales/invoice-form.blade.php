<div class="max-w-4xl">
    <div class="flex items-center gap-3 mb-6">
        <a wire:navigate href="{{ route('sales.invoices.index') }}" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="text-xl font-medium text-gray-900">Nueva factura</h1>
    </div>

    <form wire:submit="save" class="space-y-5">
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">Datos generales</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">Cliente *</label>
                    <select wire:model="customer_id"
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
                    <label class="block text-xs text-gray-500 mb-1">Tipo</label>
                    <select wire:model="type"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        <option value="internal">Interna</option>
                        <option value="cfdi">CFDI (SAT)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Moneda</label>
                    <select wire:model="currency"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        <option value="MXN">MXN</option>
                        <option value="USD">USD</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Forma de pago</label>
                    <select wire:model="payment_method"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        @foreach(\App\Models\SaleInvoice::PAYMENT_METHODS as $key => $label)
                            <option value="{{ $key }}" {{ $payment_method === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Días de crédito</label>
                    <input wire:model="payment_terms" type="number" min="0" value="{{ $payment_terms }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Fecha emisión *</label>
                    <input wire:model="issued_at" type="date" value="{{ $issued_at }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Descuento global %</label>
                    <input wire:model.live="global_discount" type="number" step="0.01" min="0" max="100"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">Notas</label>
                    <textarea wire:model="notes" rows="2"
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

            <div class="border border-gray-100 rounded-lg overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Descripción</th>
                            <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-20">Cant.</th>
                            <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-28">Precio</th>
                            <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-20">Desc %</th>
                            <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-16">IVA %</th>
                            <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-28">Subtotal</th>
                            <th class="w-8"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($items as $index => $item)
                            <tr>
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
                                    <input wire:model.live="items.{{ $index }}.discount_pct" type="number" step="0.01" min="0" max="100"
                                        class="w-full border border-gray-200 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-300">
                                </td>
                                <td class="px-4 py-2">
                                    <input wire:model.live="items.{{ $index }}.tax_rate" type="number" step="0.01" min="0" max="100"
                                        class="w-full border border-gray-200 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-300">
                                </td>
                                <td class="px-4 py-2 text-xs font-medium text-gray-900">
                                    @php
                                        $sub = ($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0);
                                        $disc = $sub * (($item['discount_pct'] ?? 0) / 100);
                                    @endphp
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
            <a wire:navigate href="{{ route('sales.invoices.index') }}"
                class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                Cancelar
            </a>
            <button type="submit"
                class="px-5 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition">
                Crear factura
            </button>
        </div>
    </form>
</div>
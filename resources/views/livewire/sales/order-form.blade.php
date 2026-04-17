<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center gap-3 mb-6">
        <a wire:navigate href="{{ route('sales.orders.index') }}" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-xl font-medium text-gray-900">Nueva orden de venta</h1>
            @if($this->sourceLabel)
                <p class="text-xs text-indigo-600 mt-0.5 font-medium">Desde cotización: {{ $this->sourceLabel }}</p>
            @else
                <p class="text-sm text-gray-500">Registra un pedido confirmado de cliente</p>
            @endif
        </div>
    </div>

    <form wire:submit="save" class="space-y-5">

        {{-- ── Datos generales ─────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 lg:p-6 space-y-4 shadow-sm">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">Datos generales</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 lg:gap-5">
                <div class="sm:col-span-2 lg:col-span-1 xl:col-span-2">
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
                    <label class="block text-xs text-gray-500 mb-1">Forma de pago</label>
                    <select wire:model="payment_method"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        @foreach(\App\Models\SaleOrder::PAYMENT_METHODS as $key => $label)
                            <option value="{{ $key }}" {{ $payment_method === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Días de crédito</label>
                    <input wire:model="payment_terms" type="number" min="0"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                        placeholder="0 = contado">
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
                    <label class="block text-xs text-gray-500 mb-1">Fecha requerida</label>
                    <input wire:model="required_at" type="date"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Descuento global %</label>
                    <input wire:model.live="global_discount" type="number" step="0.01" min="0" max="100"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>
                <div class="sm:col-span-2 lg:col-span-3 xl:col-span-4">
                    <label class="block text-xs text-gray-500 mb-1">Notas</label>
                    <textarea wire:model="notes" rows="2"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                        placeholder="Observaciones de la orden..."></textarea>
                </div>
            </div>
        </div>

        {{-- ── Productos y servicios ────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4 shadow-sm">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <div>
                    <h2 class="text-sm font-medium text-gray-700">Productos y servicios</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Agrega los ítems de la orden</p>
                </div>
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
                <table class="w-full text-sm min-w-[860px]">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Descripción / Producto</th>
                            <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-20">Cant.</th>
                            <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-28">Precio Unit.</th>
                            <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-20">Desc %</th>
                            <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-20">IEPS %</th>
                            <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-20">IVA %</th>
                            <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500 w-28">Subtotal</th>
                            <th class="w-10"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($items as $index => $item)
                            @php
                                $unitPrice    = (float)($item['unit_price'] ?? 0);
                                $qty          = (float)($item['quantity'] ?? 0);
                                $discPct      = (float)($item['discount_pct'] ?? 0);
                                $iepsPct      = (float)($item['ieps_rate'] ?? 0);
                                $taxPct       = (float)($item['tax_rate'] ?? 0);
                                $minPrice     = (float)($item['min_sale_price'] ?? 0);
                                $maxDiscPct   = (float)($item['max_discount_pct'] ?? 100);
                                $finalPrice   = round($unitPrice * (1 - $discPct / 100), 2);
                                $discExceeded = $minPrice > 0 && $finalPrice < $minPrice;
                                $base    = $qty * $unitPrice;
                                $disc    = $base * ($discPct / 100);
                                $baseNet = $base - $disc;
                                $ieps    = $baseNet * ($iepsPct / 100);
                                $tax     = ($baseNet + $ieps) * ($taxPct / 100);
                                $lineTotal = $baseNet + $ieps + $tax;
                            @endphp
                            <tr class="{{ $discExceeded ? 'bg-red-50/50' : '' }}">
                                <td class="px-4 py-2.5">
                                    <input wire:model="items.{{ $index }}.description" type="text"
                                        class="w-full border-none focus:ring-0 p-0 text-sm placeholder-gray-300 bg-transparent"
                                        placeholder="Nombre del producto...">
                                    @error("items.{$index}.description") <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p> @enderror
                                    @if($discExceeded)
                                        <p class="text-[10px] text-red-600 font-medium mt-0.5">
                                            Precio final (${{ number_format($finalPrice, 2) }}) &lt; mínimo (${{ number_format($minPrice, 2) }})
                                        </p>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5">
                                    <input wire:model.live="items.{{ $index }}.quantity" type="number" step="0.01" min="0.01"
                                        class="w-full border-gray-200 rounded px-2 py-1 text-sm focus:ring-indigo-300">
                                </td>
                                <td class="px-4 py-2.5">
                                    <div class="relative">
                                        <span class="absolute left-2 top-1.5 text-xs text-gray-400">$</span>
                                        <input wire:model.live="items.{{ $index }}.unit_price" type="number" step="0.01" min="0"
                                            class="w-full border-gray-200 rounded pl-5 pr-2 py-1 text-sm focus:ring-indigo-300">
                                    </div>
                                </td>
                                <td class="px-4 py-2.5">
                                    <input wire:model.live="items.{{ $index }}.discount_pct" type="number" step="0.01" min="0" max="100"
                                        class="w-full border-gray-200 rounded px-2 py-1 text-sm {{ $discExceeded ? 'border-red-300 focus:ring-red-300' : 'focus:ring-indigo-300' }}">
                                    @if($maxDiscPct < 100)
                                        <p class="text-[10px] {{ $discExceeded ? 'text-red-500 font-semibold' : 'text-gray-400' }} mt-0.5">Máx: {{ number_format($maxDiscPct, 1) }}%</p>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5">
                                    <input wire:model.live="items.{{ $index }}.ieps_rate" type="number" step="0.01" min="0" max="100"
                                        class="w-full border-gray-200 rounded px-2 py-1 text-sm focus:ring-orange-300 {{ $iepsPct > 0 ? 'border-orange-300 bg-orange-50' : '' }}">
                                </td>
                                <td class="px-4 py-2.5">
                                    <input wire:model.live="items.{{ $index }}.tax_rate" type="number" step="0.01" min="0"
                                        class="w-full border-gray-200 rounded px-2 py-1 text-sm focus:ring-indigo-300">
                                </td>
                                <td class="px-4 py-2.5 text-right text-gray-900 font-medium">
                                    ${{ number_format($lineTotal, 2) }}
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
                        @endforeach
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
                @if($this->discount > 0)
                <div class="flex justify-between w-full sm:w-64 text-sm text-red-500">
                    <span>Descuento</span>
                    <span>- ${{ number_format($this->discount, 2) }}</span>
                </div>
                @endif
                @if($this->ieps > 0)
                <div class="flex justify-between w-full sm:w-64 text-sm text-orange-600">
                    <span>IEPS</span>
                    <span>+ ${{ number_format($this->ieps, 2) }}</span>
                </div>
                @endif
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
            <a wire:navigate href="{{ route('sales.orders.index') }}"
                class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition text-center">
                Cancelar
            </a>
            <button type="submit"
                class="px-6 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-bold transition shadow-sm hover:shadow-md active:scale-95">
                Guardar orden
            </button>
        </div>
    </form>

    {{-- ── Modal de autorización de descuento ─────────────────────────────── --}}
    @if($needsApproval)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
            <div class="p-6 border-b border-gray-100 flex items-start gap-4">
                <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-900">Descuento excede el límite</h3>
                    <p class="text-sm text-gray-500 mt-1">
                        Uno o más ítems supera el descuento máximo permitido
                        <span class="font-semibold text-amber-700">({{ number_format($exceedingMaxPct, 2) }}%)</span>.
                        Agrega una justificación y envía a autorización del gerente.
                    </p>
                </div>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-xs text-gray-500 mb-1.5">Justificación (opcional)</label>
                    <textarea wire:model="approvalNotes" rows="3"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-300"
                        placeholder="Motivo del descuento especial..."></textarea>
                </div>
                <div class="flex gap-3 justify-end pt-2">
                    <button type="button" wire:click="$set('needsApproval', false)"
                        class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                        Cancelar
                    </button>
                    <button type="button" wire:click="save(true)"
                        class="px-5 py-2 text-sm bg-amber-500 hover:bg-amber-600 text-white rounded-lg font-semibold transition">
                        Enviar a autorización
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<div>
    <x-page-header title="Registrar factura de proveedor" description="Asocia la factura recibida a una OC (3-Way Match automático)">
        <x-slot:actions>
            <a wire:navigate href="{{ route('purchases.invoices.index') }}"
               class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 px-3 py-2 rounded-lg border border-gray-200 hover:bg-gray-50 transition">
                ← Volver
            </a>
        </x-slot:actions>
    </x-page-header>

    <x-alert />

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

        {{-- ── Formulario principal ───────────────────────────────────────── --}}
        <div class="xl:col-span-2 space-y-5">

            {{-- Header de factura --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Datos de la factura</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                    {{-- OC (opcional) --}}
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">
                            Orden de Compra <span class="text-gray-400">(opcional — se carga automáticamente)</span>
                        </label>
                        <select wire:model.live="orderId"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
                            <option value="">Sin OC asociada</option>
                            @foreach($orderOptions as $o)
                                <option value="{{ $o['id'] }}">
                                    {{ $o['folio'] }} — {{ $o['supplier']['name'] ?? '' }}
                                    (${{ number_format($o['total'], 2) }})
                                </option>
                            @endforeach
                        </select>
                        @if($loadedOrder)
                        <p class="text-xs text-indigo-600 mt-1">
                            OC cargada: {{ $loadedOrder['folio'] }} — Total OC: ${{ number_format($loadedOrder['total'], 2) }}
                        </p>
                        @endif
                    </div>

                    {{-- Proveedor --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Proveedor <span class="text-red-400">*</span></label>
                        <select wire:model.live="supplierId"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white {{ $errors->has('supplierId') ? 'border-red-400' : '' }}">
                            <option value="">Seleccionar proveedor</option>
                            @foreach($supplierOptions as $s)
                                <option value="{{ $s['id'] }}">{{ $s['name'] }}</option>
                            @endforeach
                        </select>
                        @error('supplierId')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- N° de factura del proveedor --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">N° Factura Proveedor <span class="text-red-400">*</span></label>
                        <input wire:model="supplierInvoiceNumber" type="text"
                               placeholder="Ej. FAC-2024-001"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 {{ $errors->has('supplierInvoiceNumber') ? 'border-red-400' : '' }}">
                        @error('supplierInvoiceNumber')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Fecha emisión --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Fecha emisión <span class="text-red-400">*</span></label>
                        <input wire:model.live="issuedAt" type="date"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        @error('issuedAt')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Fecha recepción --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Fecha recepción</label>
                        <input wire:model="receivedAt" type="date"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    </div>

                    {{-- Días de crédito --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Días de crédito</label>
                        <input wire:model.live="paymentTermsDays" type="number" min="0"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    </div>

                    {{-- Fecha vencimiento --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Vence el <span class="text-red-400">*</span></label>
                        <input wire:model.live="dueAt" type="date"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 {{ $errors->has('dueAt') ? 'border-red-400' : '' }}">
                        @error('dueAt')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Moneda --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Moneda</label>
                        <select wire:model="currency"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
                            <option value="MXN">MXN — Peso mexicano</option>
                            <option value="USD">USD — Dólar</option>
                            <option value="EUR">EUR — Euro</option>
                        </select>
                    </div>

                    {{-- Notas --}}
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Notas internas</label>
                        <textarea wire:model="notes" rows="2"
                                  placeholder="Observaciones, referencia interna..."
                                  class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 resize-none"></textarea>
                    </div>
                </div>
            </div>

            {{-- Partidas --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-700">Partidas de factura</h3>
                    <button wire:click="addItem" type="button"
                            class="inline-flex items-center gap-1 text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Agregar partida
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm min-w-[700px]">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100">
                                <th class="text-left px-4 py-2 text-xs font-semibold text-gray-500">Descripción</th>
                                <th class="text-right px-4 py-2 text-xs font-semibold text-gray-500 w-24">Cant.</th>
                                <th class="text-right px-4 py-2 text-xs font-semibold text-gray-500 w-32">P. Unitario</th>
                                <th class="text-right px-4 py-2 text-xs font-semibold text-gray-500 w-20">IVA %</th>
                                <th class="text-right px-4 py-2 text-xs font-semibold text-gray-500 w-28">Subtotal</th>
                                <th class="w-10 px-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($items as $idx => $item)
                            <tr class="hover:bg-gray-50/50">
                                <td class="px-4 py-2">
                                    <input wire:model.live="items.{{ $idx }}.description"
                                           type="text" placeholder="Descripción del producto/servicio"
                                           class="w-full border-0 bg-transparent text-sm focus:outline-none focus:ring-1 focus:ring-indigo-300 rounded px-1 py-0.5 {{ $errors->has("items.{$idx}.description") ? 'ring-1 ring-red-400' : '' }}">
                                    @if(!empty($item['qty_ordered']))
                                    <div class="text-xs text-gray-400 mt-0.5 flex gap-3">
                                        <span>OC: {{ $item['qty_ordered'] }}</span>
                                        <span class="{{ (float)($item['qty_received'] ?? 0) >= (float)$item['qty_ordered'] ? 'text-green-600' : 'text-amber-600' }}">
                                            Recibido: {{ $item['qty_received'] ?? 0 }}
                                        </span>
                                        <span>P.OC: ${{ number_format($item['price_ordered'], 2) }}</span>
                                    </div>
                                    @endif
                                </td>
                                <td class="px-4 py-2">
                                    <input wire:model.live="items.{{ $idx }}.quantity"
                                           type="number" min="0" step="0.001"
                                           class="w-full text-right border border-gray-200 rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                                </td>
                                <td class="px-4 py-2">
                                    <input wire:model.live="items.{{ $idx }}.unit_price"
                                           type="number" min="0" step="0.01"
                                           class="w-full text-right border border-gray-200 rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                                </td>
                                <td class="px-4 py-2">
                                    <input wire:model.live="items.{{ $idx }}.tax_rate"
                                           type="number" min="0" max="100" step="0.01"
                                           class="w-full text-right border border-gray-200 rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                                </td>
                                <td class="px-4 py-2 text-right font-medium text-gray-700">
                                    ${{ number_format($item['subtotal'] ?? 0, 2) }}
                                </td>
                                <td class="px-2 py-2 text-center">
                                    @if(count($items) > 1)
                                    <button wire:click="removeItem({{ $idx }})" type="button"
                                            class="text-red-400 hover:text-red-600 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($errors->has('items') || $errors->has('items.*') || $errors->has('items.*.description'))
                <div class="px-5 py-2 bg-red-50 border-t border-red-100">
                    <p class="text-xs text-red-500">Revisa las partidas: todos los campos obligatorios deben estar completos.</p>
                </div>
                @endif
            </div>

        </div>

        {{-- ── Panel lateral: totales y acción ───────────────────────────── --}}
        <div class="space-y-4">

            {{-- Totales --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Resumen</h3>

                <div class="space-y-2 text-sm">
                    <div class="flex justify-between text-gray-600">
                        <span>Subtotal</span>
                        <span>${{ number_format($subtotal, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>IVA</span>
                        <span>${{ number_format($taxAmount, 2) }}</span>
                    </div>
                    <div class="border-t border-gray-100 pt-2 flex justify-between font-bold text-gray-900 text-base">
                        <span>Total</span>
                        <span>${{ number_format($total, 2) }}</span>
                    </div>
                </div>

                @if($dueAt)
                <div class="mt-4 p-3 rounded-lg bg-amber-50 border border-amber-100">
                    <p class="text-xs font-medium text-amber-700">Vence el</p>
                    <p class="text-sm font-semibold text-amber-900">{{ \Carbon\Carbon::parse($dueAt)->format('d/m/Y') }}</p>
                    <p class="text-xs text-amber-600">({{ $paymentTermsDays }} días crédito)</p>
                </div>
                @endif
            </div>

            {{-- 3-Way Match info --}}
            <div class="bg-indigo-50 rounded-xl border border-indigo-100 p-4">
                <div class="flex items-start gap-2">
                    <svg class="w-4 h-4 text-indigo-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <p class="text-xs font-semibold text-indigo-700 mb-1">Cotejo 3-Way automático</p>
                        <p class="text-xs text-indigo-600 leading-relaxed">
                            Al guardar se ejecutará automáticamente el cotejo entre la OC, la(s) recepción(es) y esta factura. Se detectarán discrepancias de cantidad y precio.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Botón guardar --}}
            <button wire:click="save" wire:loading.attr="disabled"
                    class="w-full inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white text-sm font-medium px-4 py-3 rounded-xl transition shadow-sm">
                <span wire:loading.remove wire:target="save">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Registrar factura
                </span>
                <span wire:loading wire:target="save">Guardando...</span>
            </button>

        </div>
    </div>
</div>

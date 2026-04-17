<div>
    <div class="flex items-center gap-3 mb-6">
        <a wire:navigate href="{{ route('sales.credit-notes.index') }}" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-xl font-medium text-gray-900">Nueva nota de crédito</h1>
            <p class="text-sm text-gray-500">Completa los datos de la nota de crédito</p>
        </div>
    </div>

    <x-alert />

    <form wire:submit="save" class="space-y-6">

        {{-- ── Encabezado ─────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm space-y-4">
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Datos generales</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Factura relacionada <span class="text-red-500">*</span></label>
                    <select wire:model.live="invoiceId"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
                        <option value="">— Selecciona una factura —</option>
                        @foreach($invoiceOptions as $inv)
                            <option value="{{ $inv['id'] }}">
                                {{ $inv['folio'] }}
                            </option>
                        @endforeach
                    </select>
                    @error('invoiceId') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cliente</label>
                    <select wire:model.live="customerId"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
                        <option value="">— Selecciona un cliente —</option>
                        @foreach($customerOptions as $c)
                            <option value="{{ $c['id'] }}">{{ $c['name'] }}</option>
                        @endforeach
                    </select>
                    @error('customerId') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Motivo de la nota de crédito <span class="text-red-500">*</span></label>
                <textarea wire:model="reason" rows="2"
                    placeholder="Ej. Devolución de mercancía, descuento por acuerdo, error en precio…"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 resize-none"></textarea>
                @error('reason') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- ── Partidas ────────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Partidas</h2>
                <button type="button" wire:click="addItem"
                    class="inline-flex items-center gap-1.5 text-sm text-indigo-600 hover:text-indigo-800 font-medium transition">
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
                            <th class="text-left px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide w-[40%]">Descripción</th>
                            <th class="text-right px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide w-[12%]">Cant.</th>
                            <th class="text-right px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide w-[18%]">Precio unit.</th>
                            <th class="text-right px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide w-[12%]">IVA %</th>
                            <th class="text-right px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide w-[14%]">Subtotal</th>
                            <th class="w-[4%]"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($items as $i => $item)
                        <tr>
                            <td class="px-4 py-2">
                                <input wire:model.live="items.{{ $i }}.description" type="text"
                                    placeholder="Descripción"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                                @error("items.{$i}.description") <p class="text-xs text-red-500 mt-0.5">{{ $message }}</p> @enderror
                            </td>
                            <td class="px-4 py-2">
                                <input wire:model.live="items.{{ $i }}.quantity" type="number" min="0.01" step="0.01"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-sm text-right focus:outline-none focus:ring-2 focus:ring-indigo-300">
                            </td>
                            <td class="px-4 py-2">
                                <input wire:model.live="items.{{ $i }}.unit_price" type="number" min="0" step="0.01"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-sm text-right focus:outline-none focus:ring-2 focus:ring-indigo-300">
                            </td>
                            <td class="px-4 py-2">
                                <select wire:model.live="items.{{ $i }}.tax_rate"
                                    class="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
                                    <option value="0">0%</option>
                                    <option value="8">8%</option>
                                    <option value="16">16%</option>
                                </select>
                            </td>
                            <td class="px-4 py-2 text-right font-medium text-gray-900">
                                ${{ number_format($item['subtotal'] ?? 0, 2) }}
                            </td>
                            <td class="px-4 py-2 text-center">
                                @if(count($items) > 1)
                                <button type="button" wire:click="removeItem({{ $i }})"
                                    class="text-gray-300 hover:text-red-500 transition">
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

            {{-- Totales --}}
            <div class="border-t border-gray-100 px-6 py-4">
                <div class="flex flex-col items-end gap-1 text-sm">
                    <div class="flex gap-8 text-gray-500">
                        <span>Subtotal</span>
                        <span class="font-medium text-gray-900 w-28 text-right">${{ number_format($subtotal, 2) }}</span>
                    </div>
                    <div class="flex gap-8 text-gray-500">
                        <span>IVA</span>
                        <span class="font-medium text-gray-900 w-28 text-right">${{ number_format($tax, 2) }}</span>
                    </div>
                    <div class="flex gap-8 font-semibold text-gray-900 border-t border-gray-200 pt-2 mt-1">
                        <span>Total</span>
                        <span class="w-28 text-right text-lg">${{ number_format($total, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Acciones ────────────────────────────────────────────────────── --}}
        <div class="flex justify-end gap-3">
            <a wire:navigate href="{{ route('sales.credit-notes.index') }}"
                class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition text-gray-700">
                Cancelar
            </a>
            <button type="submit" wire:loading.attr="disabled" wire:target="save"
                class="px-5 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white rounded-lg transition font-medium">
                <span wire:loading.remove wire:target="save">Guardar nota de crédito</span>
                <span wire:loading wire:target="save">Guardando…</span>
            </button>
        </div>

    </form>
</div>

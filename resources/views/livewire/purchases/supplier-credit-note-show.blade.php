<div>
    <x-page-header title="NC {{ $creditNote->folio }}"
                   description="{{ $creditNote->supplier->name ?? '' }} — {{ \App\Models\SupplierCreditNote::REASONS[$creditNote->reason] ?? $creditNote->reason }}">
        <x-slot:actions>
            <a wire:navigate href="{{ route('purchases.credit-notes.index') }}"
               class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 px-3 py-2 rounded-lg border border-gray-200 hover:bg-gray-50 transition">
                ← Notas de crédito
            </a>
        </x-slot:actions>
    </x-page-header>

    <x-alert />

    {{-- KPIs --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-5">
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Total NC</p>
            <p class="text-xl font-bold text-gray-900">${{ number_format($creditNote->total, 2) }}</p>
            <p class="text-xs text-gray-400">{{ $creditNote->currency }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Aplicado</p>
            <p class="text-xl font-bold text-green-600">${{ number_format($creditNote->applied_amount, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl border {{ $creditNote->balance > 0 ? 'border-l-4 border-l-amber-400' : 'border-gray-200' }} p-4 shadow-sm">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Saldo disponible</p>
            <p class="text-xl font-bold {{ $creditNote->balance > 0 ? 'text-amber-600' : 'text-gray-400' }}">${{ number_format($creditNote->balance, 2) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

        {{-- Partidas --}}
        <div class="xl:col-span-2 space-y-5">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-700">Partidas</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm min-w-[480px]">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50 text-xs font-semibold text-gray-500 uppercase">
                                <th class="text-left px-5 py-2">Descripción</th>
                                <th class="text-right px-4 py-2">Cantidad</th>
                                <th class="text-right px-4 py-2">P. Unit.</th>
                                <th class="text-right px-4 py-2">IVA %</th>
                                <th class="text-right px-4 py-2">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($creditNote->items as $item)
                            <tr class="hover:bg-gray-50/50">
                                <td class="px-5 py-3 text-gray-800">{{ $item->description }}</td>
                                <td class="px-4 py-3 text-right text-gray-600">{{ number_format($item->quantity, 3) }}</td>
                                <td class="px-4 py-3 text-right text-gray-600">${{ number_format($item->unit_price, 2) }}</td>
                                <td class="px-4 py-3 text-right text-gray-500">{{ $item->tax_rate }}%</td>
                                <td class="px-4 py-3 text-right font-medium text-gray-900">${{ number_format($item->subtotal, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="border-t border-gray-200 bg-gray-50">
                            <tr>
                                <td colspan="4" class="px-5 py-2 text-right text-xs font-semibold text-gray-500 uppercase">Subtotal</td>
                                <td class="px-4 py-2 text-right font-medium">${{ number_format($creditNote->subtotal, 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="px-5 py-2 text-right text-xs font-semibold text-gray-500 uppercase">IVA</td>
                                <td class="px-4 py-2 text-right font-medium">${{ number_format($creditNote->tax, 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="px-5 py-2 text-right text-sm font-bold text-gray-800 uppercase">Total</td>
                                <td class="px-4 py-2 text-right text-base font-bold text-gray-900">${{ number_format($creditNote->total, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Modal: aplicar --}}
            @if($showApplyModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
                <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4">
                    <h3 class="text-base font-bold text-gray-900 mb-1">Aplicar nota de crédito</h3>
                    <p class="text-sm text-gray-500 mb-4">
                        Saldo disponible: <strong class="text-gray-800">${{ number_format($creditNote->balance, 2) }}</strong>
                        @if($creditNote->invoice)
                        — Se reducirá el saldo de la factura
                        <a wire:navigate href="{{ route('purchases.invoices.show', $creditNote->invoice) }}"
                           class="text-indigo-600 hover:underline">{{ $creditNote->invoice->folio }}</a>
                        @endif
                    </p>

                    @if($applyError)
                    <div class="mb-3 p-3 bg-red-50 border border-red-200 rounded-lg text-xs text-red-600">{{ $applyError }}</div>
                    @endif

                    <div class="space-y-3 mb-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Monto a aplicar <span class="text-red-400">*</span></label>
                            <input wire:model.live="applyAmount" type="number" min="0.01" step="0.01"
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                            @error('applyAmount')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Notas</label>
                            <input wire:model="applyNotes" type="text"
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <button wire:click="apply"
                                class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                            Aplicar
                        </button>
                        <button wire:click="$set('showApplyModal', false)"
                                class="flex-1 text-gray-600 text-sm font-medium px-4 py-2 rounded-lg border border-gray-200 hover:bg-gray-50 transition">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- Panel lateral --}}
        <div class="space-y-4">

            {{-- Acciones --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-700">Estado</h3>
                    <span class="inline-flex px-2 py-1 rounded-full text-xs font-medium
                        {{ \App\Models\SupplierCreditNote::STATUS_COLORS[$creditNote->status] ?? 'bg-gray-100 text-gray-500' }}">
                        {{ \App\Models\SupplierCreditNote::STATUS[$creditNote->status] ?? $creditNote->status }}
                    </span>
                </div>

                @if(!in_array($creditNote->status, ['applied', 'cancelled']) && $creditNote->balance > 0)
                <div class="space-y-2">
                    <button wire:click="openApplyModal"
                            class="w-full inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-3 py-2 rounded-lg transition">
                        Aplicar a factura
                    </button>
                    @can('manage purchases')
                    <button wire:click="$set('showCancelModal', true)"
                            class="w-full text-sm text-red-500 hover:text-red-700 font-medium px-3 py-2 rounded-lg border border-red-100 hover:bg-red-50 transition">
                        Cancelar NC
                    </button>
                    @endcan
                </div>
                @endif
            </div>

            {{-- Detalles --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 space-y-2 text-xs">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Datos generales</h3>
                <div class="flex justify-between gap-2">
                    <span class="text-gray-500">Folio</span>
                    <span class="font-mono font-medium text-gray-800">{{ $creditNote->folio }}</span>
                </div>
                @if($creditNote->supplier_credit_note_number)
                <div class="flex justify-between gap-2">
                    <span class="text-gray-500">N° NC Prov.</span>
                    <span class="text-gray-800">{{ $creditNote->supplier_credit_note_number }}</span>
                </div>
                @endif
                <div class="flex justify-between gap-2">
                    <span class="text-gray-500">Proveedor</span>
                    <span class="text-gray-800">{{ $creditNote->supplier->name ?? '—' }}</span>
                </div>
                @if($creditNote->invoice)
                <div class="flex justify-between gap-2">
                    <span class="text-gray-500">Factura</span>
                    <a wire:navigate href="{{ route('purchases.invoices.show', $creditNote->invoice) }}"
                       class="font-mono text-indigo-600 hover:underline">{{ $creditNote->invoice->folio }}</a>
                </div>
                @endif
                <div class="flex justify-between gap-2">
                    <span class="text-gray-500">Motivo</span>
                    <span class="text-gray-800">{{ \App\Models\SupplierCreditNote::REASONS[$creditNote->reason] ?? $creditNote->reason }}</span>
                </div>
                <div class="flex justify-between gap-2">
                    <span class="text-gray-500">Fecha emisión</span>
                    <span class="text-gray-800">{{ $creditNote->issued_at->format('d/m/Y') }}</span>
                </div>
                @if($creditNote->applied_at)
                <div class="flex justify-between gap-2">
                    <span class="text-gray-500">Aplicada el</span>
                    <span class="text-green-600">{{ $creditNote->applied_at->format('d/m/Y') }}</span>
                </div>
                @endif
                @if($creditNote->createdBy)
                <div class="flex justify-between gap-2">
                    <span class="text-gray-500">Registrado por</span>
                    <span class="text-gray-800">{{ $creditNote->createdBy->name }}</span>
                </div>
                @endif
                @if($creditNote->notes)
                <div class="pt-2 border-t border-gray-100">
                    <p class="text-gray-500 mb-1">Notas</p>
                    <p class="text-gray-600 leading-relaxed whitespace-pre-line">{{ $creditNote->notes }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Modal cancelar --}}
    @if($showCancelModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4">
            <h3 class="text-base font-bold text-gray-900 mb-2">Cancelar nota de crédito</h3>
            <p class="text-sm text-gray-500 mb-4">
                ¿Confirmas la cancelación de <strong class="text-gray-800">{{ $creditNote->folio }}</strong>?
            </p>
            <div class="flex gap-3">
                <button wire:click="cancel"
                        class="flex-1 bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                    Sí, cancelar
                </button>
                <button wire:click="$set('showCancelModal', false)"
                        class="flex-1 text-gray-600 text-sm font-medium px-4 py-2 rounded-lg border border-gray-200 hover:bg-gray-50 transition">
                    Volver
                </button>
            </div>
        </div>
    </div>
    @endif
</div>

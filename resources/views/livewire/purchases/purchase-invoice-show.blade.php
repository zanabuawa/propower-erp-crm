<div>
    @php
        $isOverdue = $invoice->due_at && $invoice->due_at->isPast()
                     && !in_array($invoice->status, ['paid','cancelled']);
        $balance   = $invoice->total - $invoice->paid_amount;
    @endphp

    <x-page-header title="Factura {{ $invoice->folio }}"
                   description="{{ $invoice->supplier->name ?? '' }} — {{ $invoice->supplier_invoice_number }}">
        <x-slot:actions>
            <a wire:navigate href="{{ route('purchases.invoices.index') }}"
               class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 px-3 py-2 rounded-lg border border-gray-200 hover:bg-gray-50 transition">
                ← Facturas
            </a>
        </x-slot:actions>
    </x-page-header>

    <x-alert />

    {{-- ── Resumen superior ────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Total</p>
            <p class="text-xl font-bold text-gray-900">${{ number_format($invoice->total, 2) }}</p>
            <p class="text-xs text-gray-400">{{ $invoice->currency }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Pagado</p>
            <p class="text-xl font-bold text-green-600">${{ number_format($invoice->paid_amount, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl border {{ $balance > 0 ? 'border-l-4 border-l-red-400' : 'border-gray-200' }} p-4 shadow-sm">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Saldo</p>
            <p class="text-xl font-bold {{ $balance > 0 ? 'text-red-600' : 'text-gray-400' }}">${{ number_format($balance, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl border {{ $isOverdue ? 'border-l-4 border-l-orange-400' : 'border-gray-200' }} p-4 shadow-sm">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Vencimiento</p>
            <p class="text-sm font-bold {{ $isOverdue ? 'text-orange-600' : 'text-gray-700' }}">
                {{ $invoice->due_at?->format('d/m/Y') ?? '—' }}
            </p>
            @if($isOverdue)
            <p class="text-xs text-orange-500">{{ $invoice->due_at->diffForHumans() }}</p>
            @endif
        </div>
    </div>

    {{-- ── Layout 2 columnas ───────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

        {{-- ── Columna principal ────────────────────────────────────────── --}}
        <div class="xl:col-span-2 space-y-5">

            {{-- Tabs --}}
            <div class="border-b border-gray-200">
                <nav class="flex gap-1">
                    @foreach([
                        'match'    => '3-Way Match',
                        'items'    => 'Partidas',
                        'payments' => 'Pagos',
                    ] as $tab => $label)
                    <button wire:click="$set('activeTab', '{{ $tab }}')"
                            class="px-4 py-2.5 text-sm font-medium border-b-2 transition
                                {{ $activeTab === $tab
                                    ? 'border-indigo-600 text-indigo-600'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        {{ $label }}
                        @if($tab === 'match' && $invoice->match_status === 'discrepancy')
                            <span class="ml-1 inline-flex items-center justify-center w-4 h-4 rounded-full bg-orange-100 text-orange-600 text-[10px] font-bold">!</span>
                        @endif
                    </button>
                    @endforeach
                </nav>
            </div>

            {{-- Tab: 3-Way Match --}}
            @if($activeTab === 'match')
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <h3 class="text-sm font-semibold text-gray-700">Resultado del cotejo</h3>
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                            {{ \App\Models\PurchaseInvoice::MATCH_COLORS[$invoice->match_status] ?? 'bg-gray-100 text-gray-500' }}">
                            {{ \App\Models\PurchaseInvoice::MATCH_STATUS[$invoice->match_status] ?? $invoice->match_status }}
                        </span>
                    </div>
                    <div class="flex gap-2">
                        @if($invoice->match_status === 'discrepancy')
                        <button wire:click="$set('showApproveModal', true)"
                                class="text-xs text-green-600 hover:text-green-800 font-medium px-3 py-1.5 border border-green-200 rounded-lg hover:bg-green-50 transition">
                            Aprobar manualmente
                        </button>
                        @endif
                        @if(!in_array($invoice->status, ['paid','cancelled']))
                        <button wire:click="runMatch" wire:loading.attr="disabled"
                                class="text-xs text-indigo-600 hover:text-indigo-800 font-medium px-3 py-1.5 border border-indigo-200 rounded-lg hover:bg-indigo-50 transition">
                            <span wire:loading.remove wire:target="runMatch">Re-ejecutar cotejo</span>
                            <span wire:loading wire:target="runMatch">Ejecutando...</span>
                        </button>
                        @endif
                    </div>
                </div>

                @if($invoice->order)
                <div class="px-5 py-3 bg-gray-50 border-b border-gray-100 text-xs text-gray-500 flex flex-wrap gap-4">
                    <span>OC:
                        <a wire:navigate href="{{ route('purchases.orders.show', $invoice->order) }}"
                           class="text-indigo-600 hover:underline font-mono">{{ $invoice->order->folio }}</a>
                    </span>
                    <span>Proveedor: <span class="text-gray-700">{{ $invoice->supplier->name ?? '—' }}</span></span>
                    @if($invoice->order->receipts->count())
                    <span>Recepciones: <span class="text-gray-700">{{ $invoice->order->receipts->count() }}</span></span>
                    @else
                    <span class="text-orange-500">Sin recepciones registradas</span>
                    @endif
                </div>
                @endif

                <div class="overflow-x-auto">
                    <table class="w-full text-sm min-w-[640px]">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50 text-xs font-semibold text-gray-500 uppercase">
                                <th class="text-left px-5 py-2">Producto / Descripción</th>
                                <th class="text-right px-4 py-2">Cant. OC</th>
                                <th class="text-right px-4 py-2">Recibida</th>
                                <th class="text-right px-4 py-2 text-red-500">Rechazada</th>
                                <th class="text-right px-4 py-2">Facturada</th>
                                <th class="text-right px-4 py-2">P. OC</th>
                                <th class="text-right px-4 py-2">P. Factura</th>
                                <th class="text-center px-4 py-2">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($invoice->items as $item)
                            <tr class="hover:bg-gray-50/50">
                                <td class="px-5 py-3">
                                    <p class="font-medium text-gray-800">{{ $item->description }}</p>
                                    @if($item->variance_notes)
                                    <p class="text-xs text-orange-500 mt-0.5">{{ $item->variance_notes }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right text-gray-500 text-xs">
                                    {{ $item->qty_ordered !== null ? number_format($item->qty_ordered, 2) : '—' }}
                                </td>
                                <td class="px-4 py-3 text-right text-xs
                                    {{ $item->qty_received !== null && $item->qty_ordered !== null && $item->qty_received < $item->qty_ordered ? 'text-amber-600 font-medium' : 'text-gray-500' }}">
                                    {{ $item->qty_received !== null ? number_format($item->qty_received, 2) : '—' }}
                                </td>
                                <td class="px-4 py-3 text-right text-xs {{ (float)($item->qty_rejected ?? 0) > 0 ? 'text-red-600 font-semibold' : 'text-gray-300' }}">
                                    {{ (float)($item->qty_rejected ?? 0) > 0 ? number_format($item->qty_rejected, 2) : '—' }}
                                </td>
                                <td class="px-4 py-3 text-right font-medium text-gray-700 text-xs">
                                    {{ number_format($item->quantity, 2) }}
                                </td>
                                <td class="px-4 py-3 text-right text-gray-500 text-xs">
                                    {{ $item->price_ordered !== null ? '$'.number_format($item->price_ordered, 2) : '—' }}
                                </td>
                                <td class="px-4 py-3 text-right font-medium text-gray-700 text-xs">
                                    ${{ number_format($item->unit_price, 2) }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                                        {{ \App\Models\PurchaseInvoiceItem::MATCH_COLORS[$item->match_status] ?? 'bg-gray-100 text-gray-500' }}">
                                        {{ \App\Models\PurchaseInvoiceItem::MATCH_STATUS[$item->match_status] ?? $item->match_status }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- Tab: Partidas --}}
            @if($activeTab === 'items')
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-700">Partidas de la factura</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm min-w-[500px]">
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
                            @foreach($invoice->items as $item)
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
                                <td class="px-4 py-2 text-right font-medium text-gray-700">${{ number_format($invoice->subtotal, 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="px-5 py-2 text-right text-xs font-semibold text-gray-500 uppercase">IVA</td>
                                <td class="px-4 py-2 text-right font-medium text-gray-700">${{ number_format($invoice->tax, 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="px-5 py-2 text-right text-sm font-bold text-gray-800 uppercase">Total</td>
                                <td class="px-4 py-2 text-right text-base font-bold text-gray-900">${{ number_format($invoice->total, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            @endif

            {{-- Tab: Pagos --}}
            @if($activeTab === 'payments')
            <div class="space-y-4">

                {{-- Formulario de nuevo pago --}}
                @if($showPaymentForm && !in_array($invoice->status, ['paid','cancelled']))
                <div class="bg-white rounded-xl border border-indigo-200 shadow-sm p-5">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4">Registrar pago</h3>

                    {{-- Discrepancy block banner --}}
                    @if($invoice->match_status === 'discrepancy')
                    <div class="mb-4 rounded-lg border border-red-300 bg-red-50 p-3 space-y-2">
                        <p class="text-xs font-semibold text-red-800 flex items-center gap-1.5">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                            </svg>
                            Cotejo 3-way con discrepancias
                        </p>
                        <p class="text-xs text-red-700">Esta factura tiene partidas con sobre-facturación, rechazos o varianza de precio. Revisa el cotejo antes de pagar.</p>
                        <label class="flex items-center gap-2 cursor-pointer mt-1">
                            <input type="checkbox" wire:model.live="bypassMatchBlock"
                                class="w-4 h-4 rounded border-red-400 text-red-600 focus:ring-red-400">
                            <span class="text-xs text-red-800 font-medium">Entiendo las discrepancias y quiero pagar de todas formas</span>
                        </label>
                    </div>
                    @endif

                    @if($paymentError)
                    <div class="mb-3 p-3 bg-red-50 border border-red-200 rounded-lg text-xs text-red-600">{{ $paymentError }}</div>
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Monto <span class="text-red-400">*</span></label>
                            <input wire:model.live="paymentAmount" type="number" min="0.01" step="0.01"
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                            @error('paymentAmount')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Método <span class="text-red-400">*</span></label>
                            <select wire:model="paymentMethod"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
                                <option value="transfer">Transferencia</option>
                                <option value="check">Cheque</option>
                                <option value="cash">Efectivo</option>
                                <option value="credit_card">Tarjeta de crédito</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Cuenta de egreso <span class="text-red-400">*</span></label>
                            <select wire:model="paymentAccountId"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white {{ $errors->has('paymentAccountId') ? 'border-red-400' : '' }}">
                                <option value="">Seleccionar cuenta</option>
                                @foreach($financeAccounts as $acc)
                                <option value="{{ $acc['id'] }}">
                                    {{ $acc['name'] }} ({{ $acc['currency'] }}) — ${{ number_format($acc['current_balance'], 2) }}
                                </option>
                                @endforeach
                            </select>
                            @error('paymentAccountId')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Referencia</label>
                            <input wire:model="paymentReference" type="text"
                                   placeholder="N° transferencia, cheque..."
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Notas</label>
                            <input wire:model="paymentNotes" type="text"
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        </div>
                    </div>

                    <div class="flex gap-2 mt-4">
                        <button wire:click="savePayment" wire:loading.attr="disabled"
                                class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                            <span wire:loading.remove wire:target="savePayment">Guardar pago</span>
                            <span wire:loading wire:target="savePayment">Guardando...</span>
                        </button>
                        <button wire:click="$set('showPaymentForm', false)"
                                class="text-sm text-gray-500 hover:text-gray-700 px-4 py-2 rounded-lg border border-gray-200 hover:bg-gray-50 transition">
                            Cancelar
                        </button>
                    </div>
                </div>
                @endif

                {{-- Historial de pagos --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-700">Historial de pagos</h3>
                        @if(!$showPaymentForm && !in_array($invoice->status, ['paid','cancelled']))
                        <button wire:click="openPaymentForm"
                                class="inline-flex items-center gap-1 text-xs bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-3 py-1.5 rounded-lg transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Registrar pago
                        </button>
                        @endif
                    </div>

                    @if($invoice->payments->isEmpty())
                    <div class="px-5 py-8 text-center text-gray-400 text-sm">No hay pagos registrados.</div>
                    @else
                    <div class="divide-y divide-gray-100">
                        @foreach($invoice->payments as $pmt)
                        <div class="px-5 py-3 flex items-center justify-between hover:bg-gray-50/50">
                            <div>
                                <p class="text-sm font-medium text-gray-800 font-mono">{{ $pmt->folio }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ $pmt->paid_at?->format('d/m/Y') }} —
                                    {{ \App\Models\SupplierPayment::PAYMENT_METHODS[$pmt->payment_method] ?? $pmt->payment_method }}
                                    @if($pmt->financeAccount) · {{ $pmt->financeAccount->name }} @endif
                                    @if($pmt->reference) · Ref: {{ $pmt->reference }} @endif
                                </p>
                                @if($pmt->notes)
                                <p class="text-xs text-gray-400">{{ $pmt->notes }}</p>
                                @endif
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-bold text-green-600">${{ number_format($pmt->amount, 2) }}</p>
                                <p class="text-xs text-gray-400">{{ $pmt->createdBy->name ?? '' }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
            @endif

        </div>

        {{-- ── Panel lateral ────────────────────────────────────────────── --}}
        <div class="space-y-4">

            {{-- Estado y acciones --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-700">Estado</h3>
                    <div class="flex flex-col items-end gap-1.5">
                        <span class="inline-flex px-2 py-1 rounded-full text-xs font-medium
                            {{ \App\Models\PurchaseInvoice::STATUS_COLORS[$invoice->status] ?? 'bg-gray-100 text-gray-500' }}">
                            {{ \App\Models\PurchaseInvoice::STATUS[$invoice->status] ?? $invoice->status }}
                        </span>
                        <span class="inline-flex px-2 py-1 rounded-full text-xs font-medium
                            {{ \App\Models\PurchaseInvoice::MATCH_COLORS[$invoice->match_status] ?? 'bg-gray-100 text-gray-500' }}">
                            3-Way: {{ \App\Models\PurchaseInvoice::MATCH_STATUS[$invoice->match_status] ?? $invoice->match_status }}
                        </span>
                    </div>
                </div>

                @if(!in_array($invoice->status, ['paid','cancelled']))
                <div class="space-y-2">
                    @if(!$showPaymentForm)
                    <button wire:click="openPaymentForm"
                            class="w-full inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-3 py-2 rounded-lg transition">
                        Registrar pago
                    </button>
                    @endif
                    @can('create purchases')
                    <a wire:navigate href="{{ route('purchases.credit-notes.create') }}?invoice={{ $invoice->id }}"
                       class="w-full inline-flex items-center justify-center text-sm text-amber-600 hover:text-amber-800 font-medium px-3 py-2 rounded-lg border border-amber-100 hover:bg-amber-50 transition">
                        Registrar nota de crédito
                    </a>
                    @endcan
                    @can('manage purchases')
                    <button wire:click="$set('showCancelModal', true)"
                            class="w-full text-sm text-red-500 hover:text-red-700 font-medium px-3 py-2 rounded-lg border border-red-100 hover:bg-red-50 transition">
                        Cancelar factura
                    </button>
                    @endcan
                </div>
                @endif
            </div>

            {{-- Datos de la factura --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 space-y-3 text-sm">
                <h3 class="font-semibold text-gray-700">Datos generales</h3>

                <div class="space-y-2 text-xs">
                    <div class="flex justify-between gap-2">
                        <span class="text-gray-500">Folio interno</span>
                        <span class="font-mono font-medium text-gray-800">{{ $invoice->folio }}</span>
                    </div>
                    <div class="flex justify-between gap-2">
                        <span class="text-gray-500">N° fact. proveedor</span>
                        <span class="font-medium text-gray-800">{{ $invoice->supplier_invoice_number }}</span>
                    </div>
                    <div class="flex justify-between gap-2">
                        <span class="text-gray-500">Proveedor</span>
                        <span class="font-medium text-gray-800">{{ $invoice->supplier->name ?? '—' }}</span>
                    </div>
                    @if($invoice->order)
                    <div class="flex justify-between gap-2">
                        <span class="text-gray-500">OC</span>
                        <a wire:navigate href="{{ route('purchases.orders.show', $invoice->order) }}"
                           class="font-mono text-indigo-600 hover:underline">{{ $invoice->order->folio }}</a>
                    </div>
                    @endif
                    <div class="flex justify-between gap-2">
                        <span class="text-gray-500">Fecha emisión</span>
                        <span class="text-gray-800">{{ $invoice->issued_at?->format('d/m/Y') ?? '—' }}</span>
                    </div>
                    @if($invoice->received_at)
                    <div class="flex justify-between gap-2">
                        <span class="text-gray-500">Fecha recepción</span>
                        <span class="text-gray-800">{{ $invoice->received_at->format('d/m/Y') }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between gap-2">
                        <span class="text-gray-500">Vence</span>
                        <span class="{{ $isOverdue ? 'text-orange-600 font-semibold' : 'text-gray-800' }}">
                            {{ $invoice->due_at?->format('d/m/Y') ?? '—' }}
                        </span>
                    </div>
                    <div class="flex justify-between gap-2">
                        <span class="text-gray-500">Moneda</span>
                        <span class="text-gray-800">{{ $invoice->currency }}</span>
                    </div>
                    @if($invoice->createdBy)
                    <div class="flex justify-between gap-2">
                        <span class="text-gray-500">Registrado por</span>
                        <span class="text-gray-800">{{ $invoice->createdBy->name }}</span>
                    </div>
                    @endif
                    @if($invoice->paid_at)
                    <div class="flex justify-between gap-2">
                        <span class="text-gray-500">Pagado el</span>
                        <span class="text-green-600">{{ $invoice->paid_at->format('d/m/Y') }}</span>
                    </div>
                    @endif
                </div>

                @if($invoice->notes)
                <div class="pt-2 border-t border-gray-100">
                    <p class="text-xs font-medium text-gray-500 mb-1">Notas</p>
                    <p class="text-xs text-gray-600 leading-relaxed whitespace-pre-line">{{ $invoice->notes }}</p>
                </div>
                @endif
            </div>

        </div>
    </div>

    {{-- ── Modal aprobación manual ──────────────────────────────────────── --}}
    @if($showApproveModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4">
            <h3 class="text-base font-bold text-gray-900 mb-2">Aprobar factura manualmente</h3>
            <p class="text-sm text-gray-500 mb-4">
                El cotejo automático detectó discrepancias. ¿Confirmas que esta factura puede aprobarse y pagarse?
            </p>
            <div class="mb-4">
                <label class="block text-xs font-medium text-gray-600 mb-1">Justificación</label>
                <textarea wire:model="approveNote" rows="3"
                          placeholder="Motivo de la aprobación manual..."
                          class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 resize-none"></textarea>
            </div>
            <div class="flex gap-3">
                <button wire:click="approveManually"
                        class="flex-1 bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                    Sí, aprobar
                </button>
                <button wire:click="$set('showApproveModal', false)"
                        class="flex-1 text-gray-600 hover:text-gray-800 text-sm font-medium px-4 py-2 rounded-lg border border-gray-200 hover:bg-gray-50 transition">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ── Modal cancelar ───────────────────────────────────────────────── --}}
    @if($showCancelModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4">
            <h3 class="text-base font-bold text-gray-900 mb-2">Cancelar factura</h3>
            <p class="text-sm text-gray-500 mb-4">
                ¿Confirmas la cancelación de la factura <strong class="text-gray-800">{{ $invoice->folio }}</strong>?
                Esta acción no puede deshacerse si no hay pagos aplicados.
            </p>
            <div class="flex gap-3">
                <button wire:click="cancel"
                        class="flex-1 bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                    Sí, cancelar
                </button>
                <button wire:click="$set('showCancelModal', false)"
                        class="flex-1 text-gray-600 hover:text-gray-800 text-sm font-medium px-4 py-2 rounded-lg border border-gray-200 hover:bg-gray-50 transition">
                    Volver
                </button>
            </div>
        </div>
    </div>
    @endif

</div>

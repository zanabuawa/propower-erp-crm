<div>
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('sales.invoices.index') }}" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div class="flex-1">
            <div class="flex items-center gap-3">
                <h1 class="text-xl font-medium text-gray-900">{{ $invoice->folio }}</h1>
                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                    {{ \App\Models\SaleInvoice::STATUS_COLORS[$invoice->status] ?? '' }}">
                    {{ \App\Models\SaleInvoice::STATUS[$invoice->status] ?? $invoice->status }}
                </span>
                <span class="text-xs text-gray-500">{{ $invoice->type === 'cfdi' ? 'CFDI' : 'Interna' }}</span>
            </div>
            <p class="text-sm text-gray-500">{{ $invoice->customer->name }}</p>
        </div>
        <div class="flex gap-2">
            @if($invoice->status === 'draft')
                <button wire:click="markAsStamped"
                    class="px-4 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition">
                    Marcar como timbrada
                </button>
            @endif
            @if(in_array($invoice->status, ['stamped', 'draft']) && $invoice->balance > 0)
                <button wire:click="$set('showPaymentForm', true)"
                    class="px-4 py-2 text-sm bg-green-600 hover:bg-green-700 text-white rounded-lg transition">
                    Registrar pago
                </button>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="space-y-4">
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h2 class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-3">Información</h2>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Cliente</span>
                        <span class="font-medium">{{ $invoice->customer->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Forma de pago</span>
                        <span class="font-medium">{{ \App\Models\SaleInvoice::PAYMENT_METHODS[$invoice->payment_method] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Fecha emisión</span>
                        <span class="font-medium">{{ $invoice->issued_at?->format('d/m/Y') }}</span>
                    </div>
                    @if($invoice->due_at)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Vencimiento</span>
                            <span class="font-medium">{{ $invoice->due_at->format('d/m/Y') }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h2 class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-3">Saldo</h2>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Total</span>
                        <span class="font-medium">{{ $invoice->currency }} ${{ number_format($invoice->total, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Pagado</span>
                        <span class="font-medium text-green-600">${{ number_format($invoice->paid_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between border-t border-gray-100 pt-2">
                        <span class="font-medium text-gray-900">Saldo pendiente</span>
                        <span class="font-medium {{ $invoice->balance > 0 ? 'text-red-600' : 'text-green-600' }}">
                            ${{ number_format($invoice->balance, 2) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2 space-y-4">
            <div class="flex gap-1 bg-gray-100 p-1 rounded-lg w-fit">
                <button wire:click="$set('activeTab', 'items')"
                    class="px-4 py-1.5 text-sm rounded-md transition {{ $activeTab === 'items' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                    Productos
                </button>
                <button wire:click="$set('activeTab', 'payments')"
                    class="px-4 py-1.5 text-sm rounded-md transition {{ $activeTab === 'payments' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                    Pagos ({{ $invoice->payments->count() }})
                </button>
            </div>

            @if($activeTab === 'items')
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100">
                                <th class="text-left px-5 py-2.5 text-xs font-medium text-gray-500">Descripción</th>
                                <th class="text-left px-5 py-2.5 text-xs font-medium text-gray-500">Cant.</th>
                                <th class="text-left px-5 py-2.5 text-xs font-medium text-gray-500">Precio</th>
                                <th class="text-left px-5 py-2.5 text-xs font-medium text-gray-500">Desc.</th>
                                <th class="text-left px-5 py-2.5 text-xs font-medium text-gray-500">IVA</th>
                                <th class="text-left px-5 py-2.5 text-xs font-medium text-gray-500">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($invoice->items as $item)
                                <tr>
                                    <td class="px-5 py-3 font-medium text-gray-900">{{ $item->description }}</td>
                                    <td class="px-5 py-3 text-gray-700">{{ $item->quantity }}</td>
                                    <td class="px-5 py-3 text-gray-700">${{ number_format($item->unit_price, 2) }}</td>
                                    <td class="px-5 py-3 text-gray-600">{{ $item->discount_pct }}%</td>
                                    <td class="px-5 py-3 text-gray-600">{{ $item->tax_rate }}%</td>
                                    <td class="px-5 py-3 font-medium">${{ number_format($item->subtotal, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-50 border-t border-gray-100">
                                <td colspan="5" class="px-5 py-2 text-xs text-gray-500 text-right">Subtotal:</td>
                                <td class="px-5 py-2 text-sm font-medium">${{ number_format($invoice->subtotal, 2) }}</td>
                            </tr>
                            @if($invoice->discount_amount > 0)
                                <tr class="bg-gray-50">
                                    <td colspan="5" class="px-5 py-2 text-xs text-gray-500 text-right">Descuento:</td>
                                    <td class="px-5 py-2 text-sm font-medium text-red-600">-${{ number_format($invoice->discount_amount, 2) }}</td>
                                </tr>
                            @endif
                            <tr class="bg-gray-50">
                                <td colspan="5" class="px-5 py-2 text-xs text-gray-500 text-right">IVA:</td>
                                <td class="px-5 py-2 text-sm font-medium">${{ number_format($invoice->tax, 2) }}</td>
                            </tr>
                            <tr class="bg-gray-50 border-t border-gray-200">
                                <td colspan="5" class="px-5 py-2 text-xs font-semibold text-gray-700 text-right">Total:</td>
                                <td class="px-5 py-2 text-sm font-semibold text-gray-900">
                                    {{ $invoice->currency }} ${{ number_format($invoice->total, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif

            @if($activeTab === 'payments')
                <div class="space-y-3">
                    @if($showPaymentForm)
                        <div class="bg-white rounded-xl border border-indigo-200 p-5 space-y-3">
                            <h2 class="text-sm font-medium text-gray-700">Registrar pago</h2>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Monto *</label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-2 text-sm text-gray-400">$</span>
                                        <input wire:model="paymentAmount" type="number" step="0.01" min="0.01"
                                            class="w-full border border-gray-200 rounded-lg pl-7 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                                    </div>
                                    @error('paymentAmount') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Forma de pago</label>
                                    <select wire:model="paymentMethod"
                                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                                        @foreach(\App\Models\SalePayment::PAYMENT_METHODS as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Referencia</label>
                                    <input wire:model="paymentReference" type="text"
                                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                                        placeholder="No. transferencia, cheque...">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Notas</label>
                                    <input wire:model="paymentNotes" type="text"
                                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                                </div>
                            </div>
                            <div class="flex justify-end gap-2">
                                <button type="button" wire:click="$set('showPaymentForm', false)"
                                    class="px-3 py-1.5 text-xs border border-gray-200 rounded-lg hover:bg-gray-50">Cancelar</button>
                                <button type="button" wire:click="savePayment"
                                    class="px-3 py-1.5 text-xs bg-green-600 text-white rounded-lg hover:bg-green-700">Guardar pago</button>
                            </div>
                        </div>
                    @endif

                    @forelse($invoice->payments as $payment)
                        <div class="bg-white rounded-xl border border-gray-200 p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">
                                        ${{ number_format($payment->amount, 2) }} {{ $payment->currency }}
                                    </p>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        {{ \App\Models\SalePayment::PAYMENT_METHODS[$payment->payment_method] }}
                                        @if($payment->reference) · {{ $payment->reference }} @endif
                                        · {{ $payment->paid_at->format('d/m/Y H:i') }}
                                    </p>
                                </div>
                                <span class="text-xs bg-green-50 text-green-700 px-2 py-0.5 rounded-full">Aplicado</span>
                            </div>
                        </div>
                    @empty
                        <div class="bg-white rounded-xl border border-gray-200 p-8 text-center text-gray-400 text-sm">
                            No se han registrado pagos.
                        </div>
                    @endforelse
                </div>
            @endif
        </div>
    </div>
</div>
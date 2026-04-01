<div>
    <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-6">
        <div class="flex items-center gap-3 flex-1">
            <a wire:navigate href="{{ route('sales.invoices.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-3 flex-wrap">
                    <h1 class="text-xl font-medium text-gray-900">{{ $invoice->folio }}</h1>
                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                        {{ \App\Models\SaleInvoice::STATUS_COLORS[$invoice->status] ?? '' }}">
                        {{ \App\Models\SaleInvoice::STATUS[$invoice->status] ?? $invoice->status }}
                    </span>
                    <span class="text-xs text-gray-500">{{ $invoice->type === 'cfdi' ? 'CFDI' : 'Interna' }}</span>
                </div>
                <p class="text-sm text-gray-500">{{ $invoice->customer->name }}</p>
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            @if($invoice->status === 'draft' && auth()->user()->can('stamp invoices'))
                <button wire:click="stamp" wire:loading.attr="disabled" wire:target="stamp"
                    class="px-4 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white rounded-lg transition flex items-center gap-2">
                    <span wire:loading.remove wire:target="stamp">Timbrar CFDI</span>
                    <span wire:loading wire:target="stamp" class="flex items-center gap-1.5">
                        <svg class="animate-spin h-3.5 w-3.5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                        </svg>
                        Timbrando…
                    </span>
                </button>
            @endif
            @if($invoice->cfdi_uuid)
                <a href="{{ route('sales.invoices.download', [$invoice, 'pdf']) }}?inline=1" target="_blank"
                    class="px-4 py-2 text-sm border border-gray-200 hover:bg-gray-50 rounded-lg transition flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Imprimir
                </a>
                <a href="{{ route('sales.invoices.download', [$invoice, 'pdf']) }}"
                    class="px-4 py-2 text-sm border border-gray-200 hover:bg-gray-50 rounded-lg transition flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    PDF
                </a>
                <a href="{{ route('sales.invoices.download', [$invoice, 'xml']) }}"
                    class="px-4 py-2 text-sm border border-gray-200 hover:bg-gray-50 rounded-lg transition flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                    </svg>
                    XML
                </a>
            @endif
            @if(in_array($invoice->status, ['stamped', 'draft']) && $invoice->balance > 0)
                <button wire:click="$set('showPaymentForm', true)"
                    class="px-4 py-2 text-sm bg-green-600 hover:bg-green-700 text-white rounded-lg transition">
                    Registrar pago
                </button>
            @endif
            @if(in_array($invoice->status, ['stamped', 'paid']) && $invoice->cfdi_uuid && auth()->user()->can('cancel invoices'))
                <button wire:click="$set('showCancelModal', true)"
                    class="px-4 py-2 text-sm border border-red-300 text-red-600 hover:bg-red-50 rounded-lg transition">
                    Cancelar CFDI
                </button>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if($stampError)
        <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg">
            {{ $stampErrorMessage }}
        </div>
    @endif

    @if($cancelError)
        <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg">
            {{ $cancelErrorMessage }}
        </div>
    @endif

    {{-- Modal de cancelación CFDI --}}
    @if($showCancelModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="background:rgba(0,0,0,0.4)">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 space-y-5">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">Cancelar CFDI</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Folio: {{ $invoice->folio }}</p>
                    </div>
                    <button wire:click="$set('showCancelModal', false)" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 text-xs text-amber-700">
                    Esta acción cancela la factura ante el SAT. La solicitud puede quedar en estado
                    <strong>pendiente</strong> si el receptor debe aceptarla.
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Motivo de cancelación *</label>
                        <select wire:model.live="cancelMotive"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                            @foreach(\App\Livewire\Sales\InvoiceShow::CANCEL_MOTIVES as $code => $label)
                                <option value="{{ $code }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('cancelMotive') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    @if($cancelMotive === '01')
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">UUID del comprobante sustituto *</label>
                            <input wire:model="cancelUuidReplacement" type="text"
                                placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-red-300">
                            @error('cancelUuidReplacement') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                    @endif
                </div>

                <div class="pt-1 border-t border-gray-100">
                    <p class="text-xs text-gray-400 mb-3">UUID: <span class="font-mono">{{ $invoice->cfdi_uuid }}</span></p>
                    <div class="flex justify-end gap-2">
                        <button type="button" wire:click="$set('showCancelModal', false)"
                            class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50">
                            No cancelar
                        </button>
                        <button type="button" wire:click="cancelCfdi"
                            wire:loading.attr="disabled" wire:target="cancelCfdi"
                            class="px-4 py-2 text-sm bg-red-600 hover:bg-red-700 disabled:opacity-60 text-white rounded-lg transition flex items-center gap-2">
                            <span wire:loading.remove wire:target="cancelCfdi">Cancelar ante SAT</span>
                            <span wire:loading wire:target="cancelCfdi" class="flex items-center gap-1.5">
                                <svg class="animate-spin h-3.5 w-3.5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                </svg>
                                Cancelando…
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        <div class="space-y-4">
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h2 class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-3">Información</h2>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Cliente</span>
                        <span class="font-medium">{{ $invoice->customer->name }}</span>
                    </div>
                    @if($invoice->customer->rfc)
                        <div class="flex justify-between">
                            <span class="text-gray-500">RFC</span>
                            <span class="font-mono text-xs font-medium">{{ $invoice->customer->rfc }}</span>
                        </div>
                    @endif
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
                    @if($invoice->cfdi_uuid)
                        <div class="pt-2 border-t border-gray-100">
                            <p class="text-gray-500 text-xs mb-1">UUID CFDI</p>
                            <p class="font-mono text-xs text-indigo-700 break-all">{{ $invoice->cfdi_uuid }}</p>
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

            @if($invoice->status === 'draft')
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-xs text-amber-700 space-y-1">
                    <p class="font-medium">Requisitos para timbrar</p>
                    <ul class="list-disc list-inside space-y-0.5 text-amber-600">
                        <li>RFC, régimen fiscal y CP fiscal de la empresa</li>
                        <li>RFC, régimen fiscal y CP del cliente</li>
                        <li>Clave SAT (ClaveProdServ) en cada producto</li>
                    </ul>
                </div>
            @endif
        </div>

        <div class="md:col-span-1 lg:col-span-2 space-y-4">
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
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm min-w-[480px]">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-100">
                                    <th class="text-left px-5 py-2.5 text-xs font-medium text-gray-500">Descripción</th>
                                    <th class="text-left px-5 py-2.5 text-xs font-medium text-gray-500">Cant.</th>
                                    <th class="text-left px-5 py-2.5 text-xs font-medium text-gray-500">Precio</th>
                                    <th class="text-left px-5 py-2.5 text-xs font-medium text-gray-500 hidden sm:table-cell">Desc.</th>
                                    <th class="text-left px-5 py-2.5 text-xs font-medium text-gray-500 hidden sm:table-cell">IVA</th>
                                    <th class="text-left px-5 py-2.5 text-xs font-medium text-gray-500">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($invoice->items as $item)
                                    <tr>
                                        <td class="px-5 py-3 font-medium text-gray-900">
                                            {{ $item->description }}
                                            @if($item->product?->sat_product_code)
                                                <p class="text-xs text-gray-400 mt-0.5 font-mono">SAT: {{ $item->product->sat_product_code }}</p>
                                            @endif
                                            <p class="text-xs text-gray-400 sm:hidden mt-0.5">Desc. {{ $item->discount_pct }}% · IVA {{ $item->tax_rate }}%</p>
                                        </td>
                                        <td class="px-5 py-3 text-gray-700">{{ $item->quantity }}</td>
                                        <td class="px-5 py-3 text-gray-700">${{ number_format($item->unit_price, 2) }}</td>
                                        <td class="px-5 py-3 text-gray-600 hidden sm:table-cell">{{ $item->discount_pct }}%</td>
                                        <td class="px-5 py-3 text-gray-600 hidden sm:table-cell">{{ $item->tax_rate }}%</td>
                                        <td class="px-5 py-3 font-medium">${{ number_format($item->subtotal, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="bg-gray-50 border-t border-gray-100">
                                    <td colspan="5" class="px-5 py-2 text-xs text-gray-500 text-right hidden sm:table-cell">Subtotal:</td>
                                    <td colspan="2" class="px-5 py-2 text-xs text-gray-500 text-right sm:hidden">Subtotal:</td>
                                    <td class="px-5 py-2 text-sm font-medium">${{ number_format($invoice->subtotal, 2) }}</td>
                                </tr>
                                @if($invoice->discount_amount > 0)
                                    <tr class="bg-gray-50">
                                        <td colspan="5" class="px-5 py-2 text-xs text-gray-500 text-right hidden sm:table-cell">Descuento:</td>
                                        <td colspan="2" class="px-5 py-2 text-xs text-gray-500 text-right sm:hidden">Descuento:</td>
                                        <td class="px-5 py-2 text-sm font-medium text-red-600">-${{ number_format($invoice->discount_amount, 2) }}</td>
                                    </tr>
                                @endif
                                <tr class="bg-gray-50">
                                    <td colspan="5" class="px-5 py-2 text-xs text-gray-500 text-right hidden sm:table-cell">IVA:</td>
                                    <td colspan="2" class="px-5 py-2 text-xs text-gray-500 text-right sm:hidden">IVA:</td>
                                    <td class="px-5 py-2 text-sm font-medium">${{ number_format($invoice->tax, 2) }}</td>
                                </tr>
                                <tr class="bg-gray-50 border-t border-gray-200">
                                    <td colspan="5" class="px-5 py-2 text-xs font-semibold text-gray-700 text-right hidden sm:table-cell">Total:</td>
                                    <td colspan="2" class="px-5 py-2 text-xs font-semibold text-gray-700 text-right sm:hidden">Total:</td>
                                    <td class="px-5 py-2 text-sm font-semibold text-gray-900">
                                        {{ $invoice->currency }} ${{ number_format($invoice->total, 2) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @endif

            @if($activeTab === 'payments')
                <div class="space-y-3">
                    @if($showPaymentForm)
                        <div class="bg-white rounded-xl border border-indigo-200 p-5 space-y-3">
                            <h2 class="text-sm font-medium text-gray-700">Registrar pago</h2>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
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

<div>
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('purchases.orders.index') }}" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div class="flex-1">
            <div class="flex items-center gap-3">
                <h1 class="text-xl font-medium text-gray-900">{{ $order->folio }}</h1>
                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                    {{ \App\Models\PurchaseOrder::STATUS_COLORS[$order->status] ?? '' }}">
                    {{ \App\Models\PurchaseOrder::STATUS[$order->status] ?? $order->status }}
                </span>
            </div>
            <p class="text-sm text-gray-500">
                Creada por {{ $order->createdBy->name }} el {{ $order->created_at->format('d/m/Y') }}
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('purchases.orders.print', $order) }}" target="_blank"
                class="px-3 py-2 text-sm border border-gray-200 text-gray-600 hover:bg-gray-50 rounded-lg transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Imprimir
            </a>
            @if($order->status === 'draft')
                <button wire:click="markAsSent"
                    class="px-4 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition">
                    Marcar como enviada
                </button>
                <button wire:click="cancel"
                    class="px-4 py-2 text-sm border border-red-200 text-red-600 hover:bg-red-50 rounded-lg transition">
                    Cancelar
                </button>
            @endif
            @if($order->status === 'sent')
                <button wire:click="markAsWaitingDelivery"
                    class="px-4 py-2 text-sm bg-violet-600 hover:bg-violet-700 text-white rounded-lg transition">
                    Esperando mercancía
                </button>
            @endif
            @if(in_array($order->status, ['sent', 'waiting_delivery', 'partial_received']))
                <a href="{{ route('purchases.receipts.create', $order) }}"
                    class="px-4 py-2 text-sm bg-teal-600 hover:bg-teal-700 text-white rounded-lg transition">
                    Registrar recepción
                </a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Columna izquierda --}}
        <div class="space-y-4">
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h2 class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-3">Información</h2>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Proveedor</span>
                        <span class="font-medium">{{ $order->supplier->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Moneda</span>
                        <span class="font-medium">{{ $order->currency }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Días de crédito</span>
                        <span class="font-medium">{{ $order->payment_terms == 0 ? 'Contado' : $order->payment_terms . ' días' }}</span>
                    </div>
                    @if($order->expected_at)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Entrega esperada</span>
                            <span class="font-medium">{{ $order->expected_at->format('d/m/Y') }}</span>
                        </div>
                    @endif
                    @if($order->branch)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Sucursal</span>
                            <span class="font-medium">{{ $order->branch->name }}</span>
                        </div>
                    @endif
                    @if($order->requisition)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Requisición</span>
                            <a href="{{ route('purchases.requisitions.show', $order->requisition) }}"
                                class="text-indigo-600 hover:text-indigo-800 font-medium text-xs">
                                {{ $order->requisition->folio }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h2 class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-3">Totales</h2>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Subtotal</span>
                        <span>${{ number_format($order->subtotal, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">IVA</span>
                        <span>${{ number_format($order->tax, 2) }}</span>
                    </div>
                    <div class="flex justify-between border-t border-gray-100 pt-2">
                        <span class="font-medium text-gray-900">Total</span>
                        <span class="font-medium text-gray-900">
                            {{ $order->currency }} ${{ number_format($order->total, 2) }}
                        </span>
                    </div>
                </div>
            </div>

            @if($order->supplierBankAccount)
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <h2 class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-3">Cuenta de pago</h2>
                    <div class="space-y-1 text-sm">
                        <p class="font-medium text-gray-900">{{ $order->supplierBankAccount->bank_name }}</p>
                        @if($order->supplierBankAccount->account_number)
                            <p class="text-xs text-gray-600 font-mono">{{ $order->supplierBankAccount->account_number }}</p>
                        @endif
                        @if($order->supplierBankAccount->clabe)
                            <p class="text-xs text-gray-600 font-mono">{{ $order->supplierBankAccount->clabe }}</p>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        {{-- Columna derecha --}}
        <div class="lg:col-span-2 space-y-4">

            <div class="flex gap-1 bg-gray-100 p-1 rounded-lg w-fit">
                <button wire:click="$set('activeTab', 'items')"
                    class="px-4 py-1.5 text-sm rounded-md transition {{ $activeTab === 'items' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                    Productos
                </button>
                <button wire:click="$set('activeTab', 'receipts')"
                    class="px-4 py-1.5 text-sm rounded-md transition {{ $activeTab === 'receipts' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                    Recepciones ({{ $order->receipts->count() }})
                </button>
            </div>

            @if($activeTab === 'items')
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100">
                                <th class="text-left px-5 py-2.5 text-xs font-medium text-gray-500">Producto</th>
                                <th class="text-left px-5 py-2.5 text-xs font-medium text-gray-500">Cant.</th>
                                <th class="text-left px-5 py-2.5 text-xs font-medium text-gray-500">Recibido</th>
                                <th class="text-left px-5 py-2.5 text-xs font-medium text-gray-500">Precio</th>
                                <th class="text-left px-5 py-2.5 text-xs font-medium text-gray-500">IVA</th>
                                <th class="text-left px-5 py-2.5 text-xs font-medium text-gray-500">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($order->items as $item)
                                <tr>
                                    <td class="px-5 py-3">
                                        <p class="font-medium text-gray-900">{{ $item->description }}</p>
                                    </td>
                                    <td class="px-5 py-3 text-gray-700">{{ $item->quantity }}</td>
                                    <td class="px-5 py-3">
                                        <span class="{{ $item->quantity_received >= $item->quantity ? 'text-green-600' : 'text-amber-600' }} font-medium">
                                            {{ $item->quantity_received }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-gray-700">${{ number_format($item->unit_price, 2) }}</td>
                                    <td class="px-5 py-3 text-gray-600">{{ $item->tax_rate }}%</td>
                                    <td class="px-5 py-3 font-medium text-gray-900">${{ number_format($item->subtotal, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if($activeTab === 'receipts')
                <div class="space-y-3">
                    @forelse($order->receipts as $receipt)
                        <div class="bg-white rounded-xl border border-gray-200 p-5">
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $receipt->folio }}</p>
                                    <p class="text-xs text-gray-500">
                                        Recibido por {{ $receipt->receivedBy->name }}
                                        el {{ $receipt->received_at->format('d/m/Y H:i') }}
                                    </p>
                                </div>
                            </div>
                            <table class="w-full text-xs">
                                <thead>
                                    <tr class="text-gray-500 border-b border-gray-100">
                                        <th class="text-left py-1.5">Producto</th>
                                        <th class="text-left py-1.5">Almacén</th>
                                        <th class="text-left py-1.5">Cantidad</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($receipt->items as $item)
                                        <tr>
                                            <td class="py-1.5 text-gray-700">{{ $item->product?->name ?? '—' }}</td>
                                            <td class="py-1.5 text-gray-600">{{ $item->warehouse?->name ?? '—' }}</td>
                                            <td class="py-1.5 font-medium text-gray-900">{{ $item->quantity_received }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @empty
                        <div class="bg-white rounded-xl border border-gray-200 p-8 text-center text-gray-400 text-sm">
                            No se han registrado recepciones.
                        </div>
                    @endforelse
                </div>
            @endif
        </div>
    </div>
</div>
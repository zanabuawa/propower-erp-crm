<div>
    <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-6">
        <div class="flex items-center gap-3 flex-1">
            <a wire:navigate href="{{ route('purchases.orders.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-3 flex-wrap">
                    <h1 class="text-xl font-medium text-gray-900">{{ $order->folio }}</h1>
                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                        {{ \App\Models\PurchaseOrder::STATUS_COLORS[$order->status] ?? '' }}">
                        {{ \App\Models\PurchaseOrder::STATUS[$order->status] ?? $order->status }}
                    </span>
                    @if($order->status === 'cancelled')
                        <span class="text-xs text-red-500">Cancelada</span>
                    @endif
                </div>
                <p class="text-sm text-gray-500">
                    Creada por {{ $order->createdBy->name }} el {{ $order->created_at->format('d/m/Y') }}
                </p>
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
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
                <button wire:click="cancel" wire:confirm="¿Cancelar esta orden de compra?"
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
            @if(in_array($order->status, ['draft', 'sent', 'waiting_delivery', 'partial_received']))
                <a wire:navigate href="{{ route('purchases.receipts.create', $order) }}"
                    class="px-4 py-2 text-sm bg-teal-600 hover:bg-teal-700 text-white rounded-lg transition">
                    Registrar recepción
                </a>
            @endif
            @if(in_array($order->status, ['received', 'partial_received', 'waiting_delivery']) && !$order->invoices->count())
                @can('create purchases')
                <a wire:navigate href="{{ route('purchases.invoices.create') }}?order={{ $order->id }}"
                    class="px-4 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition">
                    Registrar factura
                </a>
                @endcan
            @endif
        </div>
    </div>

    <x-alert />

    {{-- ── Timeline de progreso ─────────────────────────────────────────── --}}
    @if($order->status !== 'cancelled')
    @php
        $flow         = \App\Models\PurchaseOrder::STATUS_FLOW;
        $currentIndex = array_search($order->status, $flow);
        $labels = [
            'draft'            => ['Borrador',         'Creada en el sistema'],
            'sent'             => ['Enviada',           'Comunicada al proveedor'],
            'waiting_delivery' => ['En tránsito',       'Esperando mercancía'],
            'partial_received' => ['Recep. parcial',    'Parte de la mercancía recibida'],
            'received'         => ['Recibida',          'Toda la mercancía recibida'],
            'invoiced'         => ['Facturada',         'Factura del proveedor registrada'],
            'paid'             => ['Pagada',            'Pago al proveedor completado'],
        ];
    @endphp
    <div class="bg-white rounded-xl border border-gray-200 p-5 mb-5 shadow-sm overflow-x-auto">
        <div class="flex items-start min-w-[560px]">
            @foreach($flow as $i => $step)
                @php
                    $isDone    = $currentIndex !== false && $i < $currentIndex;
                    $isCurrent = $currentIndex !== false && $i === $currentIndex;
                    $isLast    = $i === array_key_last($flow);
                @endphp
                <div class="flex-1 flex flex-col items-center relative">
                    {{-- Línea conectora --}}
                    @if(!$isLast)
                        <div class="absolute top-4 left-1/2 w-full h-0.5
                            {{ $isDone || $isCurrent ? 'bg-indigo-400' : 'bg-gray-200' }}">
                        </div>
                    @endif

                    {{-- Círculo de paso --}}
                    <div class="relative z-10 w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0
                        {{ $isCurrent ? 'bg-indigo-600 text-white ring-4 ring-indigo-100'
                            : ($isDone   ? 'bg-indigo-500 text-white'
                                         : 'bg-gray-100 text-gray-400') }}">
                        @if($isDone)
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                        @else
                            {{ $i + 1 }}
                        @endif
                    </div>

                    {{-- Etiqueta --}}
                    <div class="mt-2 text-center px-1">
                        <p class="text-xs font-medium
                            {{ $isCurrent ? 'text-indigo-700' : ($isDone ? 'text-gray-700' : 'text-gray-400') }}">
                            {{ $labels[$step][0] ?? $step }}
                        </p>
                        @if($isCurrent)
                            <p class="text-[10px] text-indigo-400 mt-0.5 leading-tight">
                                {{ $labels[$step][1] ?? '' }}
                            </p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Barra de progreso de pago si tiene invoices --}}
        @if($order->status === 'invoiced' || $order->status === 'paid')
            @php
                $pct = $order->total > 0
                    ? min(100, round((float)$order->paid_amount / (float)$order->total * 100))
                    : 0;
            @endphp
            <div class="mt-4 pt-4 border-t border-gray-100">
                <div class="flex justify-between text-xs text-gray-500 mb-1.5">
                    <span>Pagado al proveedor</span>
                    <span class="font-semibold {{ $pct >= 100 ? 'text-emerald-600' : 'text-gray-700' }}">
                        ${{ number_format($order->paid_amount, 2) }} / ${{ number_format($order->total, 2) }}
                        ({{ $pct }}%)
                    </span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2">
                    <div class="h-2 rounded-full transition-all
                        {{ $pct >= 100 ? 'bg-emerald-500' : 'bg-indigo-500' }}"
                        style="width: {{ $pct }}%"></div>
                </div>
            </div>
        @endif
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">

        {{-- Columna izquierda --}}
        <div class="space-y-4">
            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
                <h2 class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-3">Información</h2>
                <div class="space-y-2 text-sm">
                    @if($order->supplier)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Proveedor</span>
                        <span class="font-medium">{{ $order->supplier->name }}</span>
                    </div>
                    @endif
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
                            <a wire:navigate href="{{ route('purchases.requisitions.show', $order->requisition) }}"
                                class="text-indigo-600 hover:text-indigo-800 font-medium text-xs">
                                {{ $order->requisition->folio }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            @can('view prices')
            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
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
            @endcan

            @if($order->supplierBankAccount)
                <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
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
        <div class="md:col-span-1 lg:col-span-2 space-y-4">

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
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm min-w-[500px]">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-100">
                                    <th class="text-left px-5 py-2.5 text-xs font-medium text-gray-500">Producto</th>
                                    <th class="text-left px-5 py-2.5 text-xs font-medium text-gray-500 hidden sm:table-cell">Proveedor</th>
                                    <th class="text-left px-5 py-2.5 text-xs font-medium text-gray-500">Cant.</th>
                                    <th class="text-left px-5 py-2.5 text-xs font-medium text-gray-500">Recibido</th>
                                    @can('view prices')
                                    <th class="text-left px-5 py-2.5 text-xs font-medium text-gray-500 hidden sm:table-cell">Precio</th>
                                    <th class="text-left px-5 py-2.5 text-xs font-medium text-gray-500 hidden sm:table-cell">IVA</th>
                                    <th class="text-left px-5 py-2.5 text-xs font-medium text-gray-500">Subtotal</th>
                                    @endcan
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($order->items as $item)
                                    <tr>
                                        <td class="px-5 py-3">
                                            <p class="font-medium text-gray-900">{{ $item->description }}</p>
                                            @can('view prices')
                                            <p class="text-xs text-gray-400 sm:hidden mt-0.5">${{ number_format($item->unit_price, 2) }} · IVA {{ $item->tax_rate == 0 ? 'incluido' : $item->tax_rate . '%' }}</p>
                                            @endcan
                                        </td>
                                        <td class="px-5 py-3 text-gray-600 hidden sm:table-cell text-sm">
                                            {{ $item->supplier?->name ?? '—' }}
                                        </td>
                                        <td class="px-5 py-3 text-gray-700">{{ $item->quantity }}</td>
                                        <td class="px-5 py-3">
                                            <span class="{{ $item->quantity_received >= $item->quantity ? 'text-emerald-600' : 'text-amber-600' }} font-medium">
                                                {{ $item->quantity_received }}
                                            </span>
                                        </td>
                                        @can('view prices')
                                        <td class="px-5 py-3 text-gray-700 hidden sm:table-cell">${{ number_format($item->unit_price, 2) }}</td>
                                        <td class="px-5 py-3 text-gray-600 hidden sm:table-cell">{{ $item->tax_rate == 0 ? 'Incluido' : $item->tax_rate . '%' }}</td>
                                        <td class="px-5 py-3 font-medium text-gray-900">${{ number_format($item->subtotal, 2) }}</td>
                                        @endcan
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            @if($activeTab === 'receipts')
                <div class="space-y-3">
                    @forelse($order->receipts as $receipt)
                        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
                            <div class="flex items-start justify-between mb-3 gap-3">
                                <div>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <p class="text-sm font-medium text-gray-900">{{ $receipt->folio }}</p>
                                        <span class="text-xs px-2 py-0.5 rounded-full bg-teal-50 text-teal-700 font-medium">
                                            {{ \App\Models\PurchaseReceipt::RECEPTION_TYPES[$receipt->reception_type] ?? $receipt->reception_type }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        Recibido por {{ $receipt->receivedBy->name }}
                                        el {{ $receipt->received_at->format('d/m/Y H:i') }}
                                    </p>
                                    @if($receipt->notes)
                                        <p class="text-xs text-gray-500 mt-1 italic">{{ $receipt->notes }}</p>
                                    @endif
                                </div>
                                <a href="{{ route('purchases.receipts.print', $receipt) }}" target="_blank"
                                    class="flex-shrink-0 px-3 py-1.5 text-xs border border-gray-200 text-gray-500 hover:bg-gray-50 rounded-lg transition flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                    </svg>
                                    Imprimir
                                </a>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-xs min-w-[380px]">
                                    <thead>
                                        <tr class="text-gray-500 border-b border-gray-100">
                                            <th class="text-left py-1.5">Producto</th>
                                            <th class="text-left py-1.5">Almacén</th>
                                            <th class="text-right py-1.5">Cantidad</th>
                                            <th class="text-left py-1.5 pl-4">Notas</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach($receipt->items as $item)
                                            <tr>
                                                <td class="py-1.5 text-gray-700">{{ $item->product?->name ?? '—' }}</td>
                                                <td class="py-1.5 text-gray-600">{{ $item->warehouse?->name ?? '—' }}</td>
                                                <td class="py-1.5 font-medium text-gray-900 text-right">{{ $item->quantity_received }}</td>
                                                <td class="py-1.5 text-gray-500 pl-4 italic">{{ $item->notes ?: '—' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @if($receipt->operating_expenses > 0)
                                <p class="text-xs text-amber-700 mt-3 pt-3 border-t border-gray-100">
                                    Gastos de operación: <strong>${{ number_format($receipt->operating_expenses, 2) }}</strong>
                                </p>
                            @endif
                        </div>
                    @empty
                        <div class="bg-white rounded-xl border border-gray-200 p-8 text-center text-gray-400 text-sm">
                            No se han registrado recepciones para esta orden.
                        </div>
                    @endforelse
                </div>
            @endif
        </div>
    </div>
</div>

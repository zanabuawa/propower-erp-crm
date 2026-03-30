<div>
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('sales.index') }}" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div class="flex-1">
            <div class="flex items-center gap-3">
                <h1 class="text-xl font-medium text-gray-900">{{ $quotation->folio }}</h1>
                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                    {{ \App\Models\SaleQuotation::STATUS_COLORS[$quotation->status] ?? '' }}">
                    {{ \App\Models\SaleQuotation::STATUS[$quotation->status] ?? $quotation->status }}
                </span>
            </div>
            <p class="text-sm text-gray-500">
                {{ $quotation->customer->name }} · Creada por {{ $quotation->createdBy->name }}
            </p>
        </div>
        <div class="flex gap-2">
            @if($quotation->status === 'draft')
                <button wire:click="markAsSent"
                    class="px-4 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition">
                    Marcar como enviada
                </button>
            @endif
            @if(in_array($quotation->status, ['sent']))
                <button wire:click="accept"
                    class="px-4 py-2 text-sm bg-green-600 hover:bg-green-700 text-white rounded-lg transition">
                    Aceptar
                </button>
                <button wire:click="reject"
                    class="px-4 py-2 text-sm border border-red-200 text-red-600 hover:bg-red-50 rounded-lg transition">
                    Rechazar
                </button>
            @endif
            @if($quotation->status === 'accepted' && !$quotation->order)
                <a href="{{ route('sales.orders.create') }}?quotation={{ $quotation->id }}"
                    class="px-4 py-2 text-sm bg-teal-600 hover:bg-teal-700 text-white rounded-lg transition">
                    Generar orden de venta
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
        <div class="space-y-4">
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h2 class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-3">Información</h2>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Cliente</span>
                        <span class="font-medium">{{ $quotation->customer->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Moneda</span>
                        <span class="font-medium">{{ $quotation->currency }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Válida hasta</span>
                        <span class="font-medium">{{ $quotation->valid_until?->format('d/m/Y') ?? '—' }}</span>
                    </div>
                    @if($quotation->priceList)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Lista de precios</span>
                            <span class="font-medium">{{ $quotation->priceList->name }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h2 class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-3">Totales</h2>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Subtotal</span>
                        <span>${{ number_format($quotation->subtotal, 2) }}</span>
                    </div>
                    @if($quotation->discount_amount > 0)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Descuento</span>
                            <span class="text-red-600">-${{ number_format($quotation->discount_amount, 2) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-gray-500">IVA</span>
                        <span>${{ number_format($quotation->tax, 2) }}</span>
                    </div>
                    <div class="flex justify-between border-t border-gray-100 pt-2">
                        <span class="font-medium text-gray-900">Total</span>
                        <span class="font-medium text-gray-900">
                            {{ $quotation->currency }} ${{ number_format($quotation->total, 2) }}
                        </span>
                    </div>
                </div>
            </div>

            @if($quotation->order)
                <div class="bg-green-50 border border-green-200 rounded-xl p-4">
                    <p class="text-sm font-medium text-green-800">Orden generada</p>
                    <a href="{{ route('sales.orders.show', $quotation->order) }}"
                        class="text-xs text-green-700 hover:text-green-900 font-medium">
                        {{ $quotation->order->folio }} →
                    </a>
                </div>
            @endif
        </div>

        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-100">
                    <h2 class="text-sm font-medium text-gray-700">Productos</h2>
                </div>
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
                        @foreach($quotation->items as $item)
                            <tr>
                                <td class="px-5 py-3 font-medium text-gray-900">{{ $item->description }}</td>
                                <td class="px-5 py-3 text-gray-700">{{ $item->quantity }}</td>
                                <td class="px-5 py-3 text-gray-700">${{ number_format($item->unit_price, 2) }}</td>
                                <td class="px-5 py-3 text-gray-600">{{ $item->discount_pct }}%</td>
                                <td class="px-5 py-3 text-gray-600">{{ $item->tax_rate }}%</td>
                                <td class="px-5 py-3 font-medium text-gray-900">${{ number_format($item->subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($quotation->notes)
                <div class="mt-4 bg-white rounded-xl border border-gray-200 p-5">
                    <h2 class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Notas</h2>
                    <p class="text-sm text-gray-700">{{ $quotation->notes }}</p>
                </div>
            @endif

            @if($quotation->terms)
                <div class="mt-4 bg-white rounded-xl border border-gray-200 p-5">
                    <h2 class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Términos y condiciones</h2>
                    <p class="text-sm text-gray-700">{{ $quotation->terms }}</p>
                </div>
            @endif
        </div>
    </div>
</div>
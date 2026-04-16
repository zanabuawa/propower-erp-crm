<div class="max-w-4xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-6">
        <div class="flex items-center gap-3 flex-1">
            <a wire:navigate href="{{ route('sales.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-3 flex-wrap">
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
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('sales.quotations.print', $quotation) }}" target="_blank"
                class="inline-flex items-center gap-1.5 px-3 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-600 transition"
                title="Imprimir / PDF">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Imprimir
            </a>
            @if($quotation->status === 'draft')
                <button wire:click="markAsSent"
                    class="px-4 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition">
                    Marcar como enviada
                </button>
            @endif
            @if(in_array($quotation->status, ['sent']))
                <button wire:click="accept"
                    class="px-4 py-2 text-sm bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition">
                    Aceptar
                </button>
                <button wire:click="reject"
                    class="px-4 py-2 text-sm border border-red-200 text-red-600 hover:bg-red-50 rounded-lg transition">
                    Rechazar
                </button>
            @endif
            @if($quotation->status === 'accepted' && !$quotation->order)
                <a wire:navigate href="{{ route('sales.orders.create') }}?quotation={{ $quotation->id }}"
                    class="px-4 py-2 text-sm bg-teal-600 hover:bg-teal-700 text-white rounded-lg transition">
                    Generar orden de venta
                </a>
            @endif
        </div>
    </div>

    <x-alert />

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        <div class="space-y-4">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
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

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
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
                <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4">
                    <p class="text-sm font-medium text-emerald-800">Orden generada</p>
                    <a wire:navigate href="{{ route('sales.orders.show', $quotation->order) }}"
                        class="text-xs text-emerald-700 hover:text-emerald-900 font-medium">
                        {{ $quotation->order->folio }} →
                    </a>
                </div>
            @endif
        </div>

        <div class="md:col-span-1 lg:col-span-2 space-y-4">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-100">
                    <h2 class="text-sm font-medium text-gray-700">Productos</h2>
                </div>
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
                            @foreach($quotation->items as $item)
                                <tr>
                                    <td class="px-5 py-3 font-medium text-gray-900">
                                        {{ $item->description }}
                                        <p class="text-xs text-gray-400 sm:hidden mt-0.5">Desc. {{ $item->discount_pct }}% · IVA {{ $item->tax_rate }}%</p>
                                    </td>
                                    <td class="px-5 py-3 text-gray-700">{{ $item->quantity }}</td>
                                    <td class="px-5 py-3 text-gray-700">${{ number_format($item->unit_price, 2) }}</td>
                                    <td class="px-5 py-3 text-gray-600 hidden sm:table-cell">{{ $item->discount_pct }}%</td>
                                    <td class="px-5 py-3 text-gray-600 hidden sm:table-cell">{{ $item->tax_rate }}%</td>
                                    <td class="px-5 py-3 font-medium text-gray-900">${{ number_format($item->subtotal, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            @if($quotation->notes)
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <h2 class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Notas</h2>
                    <p class="text-sm text-gray-700">{{ $quotation->notes }}</p>
                </div>
            @endif

            @if($quotation->terms)
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <h2 class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Términos y condiciones</h2>
                    <p class="text-sm text-gray-700">{{ $quotation->terms }}</p>
                </div>
            @endif
        </div>
    </div>
</div>

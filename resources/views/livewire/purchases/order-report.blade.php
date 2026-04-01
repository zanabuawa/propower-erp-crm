<div class="space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-medium text-gray-900">Reporte de compras</h1>
            <p class="text-xs text-gray-400 mt-0.5">Órdenes de compra · resumen y detalle</p>
        </div>
        <button wire:click="clearFilters"
            class="px-3 py-1.5 text-xs border border-gray-200 rounded-lg text-gray-500 hover:bg-gray-50 transition">
            Limpiar filtros
        </button>
    </div>

    {{-- Filtros --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">

            <div>
                <label class="block text-[10px] text-gray-400 mb-1 font-medium uppercase tracking-wide">Desde</label>
                <input type="date" wire:model.live="dateFrom"
                    class="w-full border border-gray-200 rounded-lg px-2.5 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
            </div>

            <div>
                <label class="block text-[10px] text-gray-400 mb-1 font-medium uppercase tracking-wide">Hasta</label>
                <input type="date" wire:model.live="dateTo"
                    class="w-full border border-gray-200 rounded-lg px-2.5 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
            </div>

            <div>
                <label class="block text-[10px] text-gray-400 mb-1 font-medium uppercase tracking-wide">Estatus</label>
                <select wire:model.live="status"
                    class="w-full border border-gray-200 rounded-lg px-2.5 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    <option value="">Todos</option>
                    @foreach($statuses as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] text-gray-400 mb-1 font-medium uppercase tracking-wide">Proveedor</label>
                <select wire:model.live="supplierId"
                    class="w-full border border-gray-200 rounded-lg px-2.5 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    <option value="">Todos</option>
                    @foreach($suppliers as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] text-gray-400 mb-1 font-medium uppercase tracking-wide">Sucursal</label>
                <select wire:model.live="branchId"
                    class="w-full border border-gray-200 rounded-lg px-2.5 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    <option value="">Todas</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] text-gray-400 mb-1 font-medium uppercase tracking-wide">Moneda</label>
                <select wire:model.live="currency"
                    class="w-full border border-gray-200 rounded-lg px-2.5 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    <option value="">Todas</option>
                    <option value="MXN">MXN</option>
                    <option value="USD">USD</option>
                </select>
            </div>

        </div>
    </div>

    {{-- Tarjetas de resumen --}}
    @php $totals = $this->totals; @endphp
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">

        <div class="bg-white rounded-xl border border-gray-200 px-4 py-3">
            <p class="text-[10px] text-gray-400 uppercase tracking-wide font-medium">Total órdenes</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($totals['count']) }}</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 px-4 py-3">
            <p class="text-[10px] text-gray-400 uppercase tracking-wide font-medium">Subtotal</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">${{ number_format($totals['subtotal'], 2) }}</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 px-4 py-3">
            <p class="text-[10px] text-gray-400 uppercase tracking-wide font-medium">IVA total</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">${{ number_format($totals['tax'], 2) }}</p>
        </div>

        <div class="bg-indigo-600 rounded-xl px-4 py-3">
            <p class="text-[10px] text-indigo-200 uppercase tracking-wide font-medium">Total general</p>
            <p class="text-2xl font-bold text-white mt-1">${{ number_format($totals['total'], 2) }}</p>
        </div>

    </div>

    {{-- Desglose por estatus --}}
    @if($totals['by_status']->count() > 0)
    <div class="bg-white rounded-xl border border-gray-200 px-5 py-3">
        <p class="text-xs font-medium text-gray-500 mb-3">Distribución por estatus</p>
        <div class="flex flex-wrap gap-2">
            @foreach($totals['by_status'] as $st => $cnt)
            @php $colors = \App\Models\PurchaseOrder::STATUS_COLORS; $labels = \App\Models\PurchaseOrder::STATUS; @endphp
            <span class="px-3 py-1 rounded-full text-xs font-medium {{ $colors[$st] ?? 'bg-gray-100 text-gray-600' }}">
                {{ $labels[$st] ?? $st }}: {{ $cnt }}
            </span>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Tabla de órdenes --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-sm font-medium text-gray-700">Detalle de órdenes</h2>
            <span class="text-xs text-gray-400">{{ $orders->total() }} registros</span>
        </div>

        @if($orders->isEmpty())
        <div class="px-5 py-10 text-center text-sm text-gray-400">
            No hay órdenes con los filtros seleccionados.
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500">
                    <tr>
                        <th class="px-4 py-2 text-left">Folio</th>
                        <th class="px-4 py-2 text-left">Fecha</th>
                        <th class="px-4 py-2 text-left">Proveedor</th>
                        <th class="px-4 py-2 text-left">Sucursal</th>
                        <th class="px-4 py-2 text-left">Responsable</th>
                        <th class="px-4 py-2 text-center">Moneda</th>
                        <th class="px-4 py-2 text-right">Subtotal</th>
                        <th class="px-4 py-2 text-right">IVA</th>
                        <th class="px-4 py-2 text-right">Total</th>
                        <th class="px-4 py-2 text-center">Estatus</th>
                        <th class="px-4 py-2 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($orders as $order)
                    @php $colors = \App\Models\PurchaseOrder::STATUS_COLORS; $labels = \App\Models\PurchaseOrder::STATUS; @endphp
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-2.5 font-medium text-indigo-700">{{ $order->folio }}</td>
                        <td class="px-4 py-2.5 text-gray-500">{{ $order->created_at->format('d/m/Y') }}</td>
                        <td class="px-4 py-2.5 text-gray-800">{{ $order->supplier?->name ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-gray-500 text-xs">{{ $order->branch?->name ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-gray-500 text-xs">{{ $order->createdBy?->name ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-center">
                            <span class="text-[10px] font-medium px-2 py-0.5 rounded-full
                                {{ $order->currency === 'USD' ? 'bg-amber-50 text-amber-700' : 'bg-blue-50 text-blue-700' }}">
                                {{ $order->currency }}
                            </span>
                        </td>
                        <td class="px-4 py-2.5 text-right font-mono text-xs">${{ number_format((float)$order->subtotal, 2) }}</td>
                        <td class="px-4 py-2.5 text-right font-mono text-xs text-gray-500">${{ number_format((float)$order->tax, 2) }}</td>
                        <td class="px-4 py-2.5 text-right font-mono text-xs font-semibold">${{ number_format((float)$order->total, 2) }}</td>
                        <td class="px-4 py-2.5 text-center">
                            <span class="px-2 py-0.5 text-[10px] rounded-full font-medium {{ $colors[$order->status] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ $labels[$order->status] ?? $order->status }}
                            </span>
                        </td>
                        <td class="px-4 py-2.5 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('purchases.orders.show', $order) }}" wire:navigate
                                    class="text-indigo-500 hover:text-indigo-700 transition" title="Ver">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                <a href="{{ route('purchases.orders.print', $order) }}" target="_blank"
                                    class="text-gray-400 hover:text-gray-700 transition" title="Imprimir">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3 border-t border-gray-100">
            {{ $orders->links() }}
        </div>
        @endif
    </div>

</div>

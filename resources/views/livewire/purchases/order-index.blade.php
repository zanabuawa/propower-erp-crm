<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-medium text-gray-900">Órdenes de compra</h1>
            <p class="text-sm text-gray-500 mt-0.5">Gestiona las órdenes de compra</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('purchases.orders.create') }}"
                class="inline-flex items-center gap-2 bg-white hover:bg-gray-50 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg border border-gray-200 transition">
                + Nueva orden
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    {{-- Tabs --}}
    <div class="flex border-b border-gray-200 mb-5 gap-1">
        <button wire:click="switchTab('orders')"
            class="px-4 py-2 text-sm font-medium transition rounded-t-lg
                {{ $tab === 'orders' ? 'text-indigo-600 border-b-2 border-indigo-600 bg-indigo-50/40' : 'text-gray-500 hover:text-gray-700' }}">
            Todas las órdenes
        </button>
        <button wire:click="switchTab('from_requisition')"
            class="px-4 py-2 text-sm font-medium transition rounded-t-lg flex items-center gap-1.5
                {{ $tab === 'from_requisition' ? 'text-indigo-600 border-b-2 border-indigo-600 bg-indigo-50/40' : 'text-gray-500 hover:text-gray-700' }}">
            Desde requisición
            @if($requisitions->total() > 0)
                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full text-xs font-medium
                    {{ $tab === 'from_requisition' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600' }}">
                    {{ $requisitions->total() }}
                </span>
            @endif
        </button>
    </div>

    {{-- Tab: Todas las órdenes --}}
    @if($tab === 'orders')
        <div class="flex flex-wrap gap-3 mb-4">
            <input wire:model.live.debounce.300ms="search" type="text"
                placeholder="Buscar por folio..."
                class="flex-1 min-w-[200px] border border-gray-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
            <select wire:model.live="filterStatus"
                class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                <option value="">Todos los estados</option>
                @foreach(\App\Models\PurchaseOrder::STATUS as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50">
                        <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Folio</th>
                        <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Proveedor</th>
                        <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Sucursal</th>
                        <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Productos</th>
                        <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Total</th>
                        <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Esperado</th>
                        <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Estado</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($orders as $order)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-5 py-3 font-mono text-xs font-medium text-gray-900">{{ $order->folio }}</td>
                            <td class="px-5 py-3 text-gray-700">{{ $order->supplier?->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-gray-600">{{ $order->branch?->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-gray-600">{{ $order->items_count }}</td>
                            <td class="px-5 py-3 font-medium text-gray-900">
                                {{ $order->currency }} ${{ number_format($order->total, 2) }}
                            </td>
                            <td class="px-5 py-3 text-gray-600">{{ $order->expected_at?->format('d/m/Y') ?? '—' }}</td>
                            <td class="px-5 py-3">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                                    {{ \App\Models\PurchaseOrder::STATUS_COLORS[$order->status] ?? '' }}">
                                    {{ \App\Models\PurchaseOrder::STATUS[$order->status] ?? $order->status }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('purchases.orders.show', $order) }}"
                                    class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Ver detalle</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-10 text-center text-gray-400 text-sm">
                                No se encontraron órdenes de compra.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if($orders->hasPages())
                <div class="px-5 py-3 border-t border-gray-100">{{ $orders->links() }}</div>
            @endif
        </div>
    @endif

    {{-- Tab: Desde requisición --}}
    @if($tab === 'from_requisition')
        <div class="mb-4">
            <p class="text-sm text-gray-500 mb-3">
                Requisiciones autorizadas listas para generar orden de compra.
            </p>
            <input wire:model.live.debounce.300ms="searchReq" type="text"
                placeholder="Buscar por folio o justificación..."
                class="w-full max-w-sm border border-gray-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
        </div>

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50">
                        <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Requisición</th>
                        <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Solicitante</th>
                        <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Sucursal</th>
                        <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Partidas</th>
                        <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Total cotizado</th>
                        <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Estado</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($requisitions as $req)
                        @php
                            $q = $req->finalQuotation;
                            $hasOrder = $req->order !== null;
                        @endphp
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-5 py-3">
                                <div class="font-mono text-xs font-medium text-gray-900">{{ $req->folio }}</div>
                                @if($req->justification)
                                    <div class="text-xs text-gray-400 truncate max-w-[200px]" title="{{ $req->justification }}">
                                        {{ $req->justification }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-gray-700">{{ $req->requestedBy?->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-gray-600">{{ $req->branch?->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-gray-600">{{ $q?->items->count() ?? '—' }}</td>
                            <td class="px-5 py-3 font-medium text-gray-900">
                                @if($q)
                                    {{ $req->currency }} ${{ number_format($q->total, 2) }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                @if($hasOrder)
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                        OC generada
                                    </span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-amber-700">
                                        Pendiente OC
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right">
                                @if($hasOrder)
                                    <a href="{{ route('purchases.orders.show', $req->order) }}"
                                        class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                                        Ver OC
                                    </a>
                                @elseif($q)
                                    <a href="{{ route('purchases.orders.create', ['quotation' => $q->id]) }}"
                                        class="inline-flex items-center gap-1 text-xs bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-3 py-1.5 rounded-lg transition">
                                        + Crear OC
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center text-gray-400 text-sm">
                                No hay requisiciones autorizadas pendientes de orden de compra.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if($requisitions->hasPages())
                <div class="px-5 py-3 border-t border-gray-100">{{ $requisitions->links() }}</div>
            @endif
        </div>
    @endif
</div>

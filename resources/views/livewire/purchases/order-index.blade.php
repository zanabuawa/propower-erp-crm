<div>
    <x-page-header title="Órdenes de compra" description="Gestiona las órdenes de compra">
        <x-slot:actions>
            <a wire:navigate href="{{ route('purchases.orders.create') }}"
                class="inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nueva orden
            </a>
        </x-slot:actions>
    </x-page-header>

    <x-alert />

    {{-- Tabs --}}
    <div class="flex border-b border-gray-200 mb-5 gap-1" role="tablist">
        <button wire:click="switchTab('orders')" role="tab"
            :aria-selected="{{ $tab === 'orders' ? 'true' : 'false' }}"
            class="px-4 py-2 text-sm font-medium transition rounded-t-lg
                {{ $tab === 'orders' ? 'text-indigo-600 border-b-2 border-indigo-600 bg-indigo-50/40' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50' }}">
            Todas las órdenes
        </button>
        <button wire:click="switchTab('from_requisition')" role="tab"
            :aria-selected="{{ $tab === 'from_requisition' ? 'true' : 'false' }}"
            class="px-4 py-2 text-sm font-medium transition rounded-t-lg flex items-center gap-1.5
                {{ $tab === 'from_requisition' ? 'text-indigo-600 border-b-2 border-indigo-600 bg-indigo-50/40' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50' }}">
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
        <div class="flex flex-col sm:flex-row gap-3 mb-5">
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input wire:model.live.debounce.300ms="search" type="text"
                    placeholder="Buscar por folio..."
                    aria-label="Buscar órdenes de compra"
                    class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-transparent transition">
            </div>
            <select wire:model.live="filterStatus" aria-label="Filtrar por estado"
                class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
                <option value="">Todos los estados</option>
                @foreach(\App\Models\PurchaseOrder::STATUS as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[700px]">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50">
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Folio</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Proveedor</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden md:table-cell">Sucursal</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden md:table-cell">Productos</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Total</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden sm:table-cell">Esperado</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Estado</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($orders as $order)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-5 py-3">
                                    <span class="font-mono text-xs font-medium text-gray-900">{{ $order->folio }}</span>
                                    <p class="text-xs text-gray-400 sm:hidden mt-0.5">{{ $order->supplier?->name ?? '—' }}</p>
                                </td>
                                <td class="px-5 py-3 text-gray-700">{{ $order->supplier?->name ?? '—' }}</td>
                                <td class="px-5 py-3 text-gray-600 hidden md:table-cell">{{ $order->branch?->name ?? '—' }}</td>
                                <td class="px-5 py-3 text-gray-600 hidden md:table-cell">{{ $order->items_count }}</td>
                                <td class="px-5 py-3 font-semibold text-gray-900">
                                    {{ $order->currency }} ${{ number_format($order->total, 2) }}
                                </td>
                                <td class="px-5 py-3 text-gray-500 text-xs hidden sm:table-cell">{{ $order->expected_at?->format('d/m/Y') ?? '—' }}</td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ \App\Models\PurchaseOrder::STATUS_COLORS[$order->status] ?? 'bg-gray-100 text-gray-500' }}">
                                        {{ \App\Models\PurchaseOrder::STATUS[$order->status] ?? $order->status }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <a wire:navigate href="{{ route('purchases.orders.show', $order) }}"
                                        class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Ver →</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8"><x-empty-state message="No se encontraron órdenes de compra." /></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($orders->hasPages())
                <div class="px-5 py-3 border-t border-gray-100">{{ $orders->links() }}</div>
            @endif
        </div>
    @endif

    {{-- Tab: Desde requisición --}}
    @if($tab === 'from_requisition')
        <div class="mb-5">
            <p class="text-sm text-gray-500 mb-3">Requisiciones autorizadas listas para generar orden de compra.</p>
            <div class="relative w-full sm:w-80">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input wire:model.live.debounce.300ms="searchReq" type="text"
                    placeholder="Buscar por folio o justificación..."
                    aria-label="Buscar requisiciones"
                    class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-transparent transition">
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[580px]">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50">
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Requisición</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden sm:table-cell">Solicitante</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden md:table-cell">Sucursal</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden md:table-cell">Partidas</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Total cotizado</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Estado OC</th>
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
                                        <div class="text-xs text-gray-400 truncate max-w-[200px]">{{ $req->justification }}</div>
                                    @endif
                                    <p class="text-xs text-gray-400 sm:hidden mt-0.5">{{ $req->requestedBy?->name ?? '—' }}</p>
                                </td>
                                <td class="px-5 py-3 text-gray-700 hidden sm:table-cell">{{ $req->requestedBy?->name ?? '—' }}</td>
                                <td class="px-5 py-3 text-gray-600 hidden md:table-cell">{{ $req->branch?->name ?? '—' }}</td>
                                <td class="px-5 py-3 text-gray-600 hidden md:table-cell">{{ $q?->items->count() ?? '—' }}</td>
                                <td class="px-5 py-3 font-semibold text-gray-900">
                                    @if($q)
                                        {{ $req->currency }} ${{ number_format($q->total, 2) }}
                                    @else
                                        <span class="text-gray-400 font-normal text-xs">Sin cotizar</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3">
                                    @if($hasOrder)
                                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700">OC generada</span>
                                    @else
                                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-amber-700">Pendiente OC</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-right">
                                    @if($hasOrder)
                                        <a wire:navigate href="{{ route('purchases.orders.show', $req->order) }}"
                                            class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Ver OC →</a>
                                    @elseif($q)
                                        <a wire:navigate href="{{ route('purchases.orders.create', ['quotation' => $q->id]) }}"
                                            class="inline-flex items-center gap-1 text-xs bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-3 py-1.5 rounded-lg transition">
                                            + Crear OC
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7"><x-empty-state message="No hay requisiciones autorizadas pendientes de orden de compra." /></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($requisitions->hasPages())
                <div class="px-5 py-3 border-t border-gray-100">{{ $requisitions->links() }}</div>
            @endif
        </div>
    @endif
</div>

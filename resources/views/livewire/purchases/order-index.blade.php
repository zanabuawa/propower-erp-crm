<div>
    {{-- Header --}}
    <x-page-header title="Órdenes de compra" description="Gestión del ciclo de vida de órdenes de compra a proveedores">
        <x-slot:actions>
            <button type="button"
                x-data
                @click="
                    const base = '{{ route('purchases.orders.report.print') }}';
                    const params = new URLSearchParams();
                    if ($wire.search)       params.set('search', $wire.search);
                    if ($wire.filterStatus) params.set('status', $wire.filterStatus);
                    window.open(base + '?' + params.toString(), '_blank');
                "
                class="inline-flex items-center gap-2 text-sm bg-white hover:bg-gray-50 border border-gray-300 hover:border-gray-400 px-4 py-2 rounded-xl transition-all duration-200 text-gray-700 shadow-sm font-medium">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Reporte
            </button>
            <a wire:navigate href="{{ route('purchases.orders.create') }}"
                class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-medium px-5 py-2 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:scale-105">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nueva orden
            </a>
        </x-slot:actions>
    </x-page-header>

    <x-alert />

    {{-- Tabs --}}
    <div class="flex items-center p-1 bg-gray-100/80 rounded-2xl mb-6 w-fit border border-gray-200/50">
        <button wire:click="switchTab('orders')"
            class="px-5 py-2 text-sm font-semibold rounded-xl transition-all duration-200 {{ $tab === 'orders' ? 'bg-white text-indigo-600 shadow-sm ring-1 ring-black/5' : 'text-gray-500 hover:text-gray-700' }}">
            Órdenes activas
        </button>
        <button wire:click="switchTab('from_requisition')"
            class="px-5 py-2 text-sm font-semibold rounded-xl transition-all duration-200 flex items-center gap-2 {{ $tab === 'from_requisition' ? 'bg-white text-indigo-600 shadow-sm ring-1 ring-black/5' : 'text-gray-500 hover:text-gray-700' }}">
            Pendientes de OC
            @if($requisitions->total() > 0)
                <span class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-lg text-[10px] font-bold {{ $tab === 'from_requisition' ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-200 text-gray-600' }}">
                    {{ $requisitions->total() }}
                </span>
            @endif
        </button>
    </div>

    {{-- Contenido según Tab --}}
    @if($tab === 'orders')
        {{-- Filtros --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-5 mb-6 shadow-sm">
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1 relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input wire:model.live.debounce.300ms="search" type="text"
                        placeholder="Buscar por folio o proveedor..."
                        class="w-full pl-9 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                </div>
                
                <div class="relative">
                    <select wire:model.live="filterStatus"
                        class="pl-4 pr-10 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all hover:bg-gray-100 appearance-none min-w-[180px]">
                        <option value="">Todos los estados</option>
                        @foreach(\App\Models\PurchaseOrder::STATUS as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- VISTA MÓVIL --}}
        <div class="space-y-4 lg:hidden mb-6">
            @forelse($orders as $order)
                <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <span class="font-mono text-xs font-bold text-indigo-600">{{ $order->folio }}</span>
                            <h3 class="font-bold text-gray-900 mt-0.5">{{ $order->supplier?->name ?? '—' }}</h3>
                        </div>
                        <x-status-badge :status="$order->status" :label="\App\Models\PurchaseOrder::STATUS[$order->status] ?? $order->status" />
                    </div>

                    <div class="grid grid-cols-2 gap-4 text-sm mb-4">
                        <div class="bg-gray-50 rounded-xl p-3">
                            <p class="text-xs text-gray-500 mb-1">Total</p>
                            <p class="font-bold text-gray-900">{{ $order->currency }} ${{ number_format($order->total, 2) }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-3">
                            <p class="text-xs text-gray-500 mb-1">Items</p>
                            <p class="font-medium text-gray-700">{{ $order->items_count }} productos</p>
                        </div>
                        <div class="col-span-2 bg-gray-50 rounded-xl p-3">
                            <p class="text-xs text-gray-500 mb-1">Sucursal / Fecha</p>
                            <p class="text-xs text-gray-700">
                                {{ $order->branch?->name ?? '—' }} • 
                                {{ $order->created_at->format('d/m/Y') }}
                            </p>
                        </div>
                    </div>

                    <a wire:navigate href="{{ route('purchases.orders.show', $order) }}"
                        class="flex items-center justify-center w-full py-2.5 bg-indigo-50 text-indigo-600 rounded-xl text-sm font-bold hover:bg-indigo-600 hover:text-white transition-all">
                        Ver detalles
                    </a>
                </div>
            @empty
                <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center shadow-sm">
                    <x-empty-state message="No se encontraron órdenes de compra." />
                </div>
            @endforelse
            {{ $orders->links() }}
        </div>

        {{-- VISTA ESCRITORIO --}}
        <div class="hidden lg:block bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-left">
                        <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Orden & Proveedor</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Detalles</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center">Estado</th>
                        <th class="px-6 py-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($orders as $order)
                        <tr class="hover:bg-gray-50 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="font-mono text-xs font-bold text-indigo-600 tracking-tight">{{ $order->folio }}</span>
                                    <span class="font-semibold text-gray-900">{{ $order->supplier?->name ?? '—' }}</span>
                                    <span class="text-[10px] text-gray-500 mt-1 uppercase tracking-wider font-medium">{{ $order->branch?->name ?? '—' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-1">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg bg-gray-100 text-gray-600 text-[10px] font-bold w-fit">
                                        {{ $order->items_count }} productos
                                    </span>
                                    @if($order->expected_at)
                                        <span class="text-[10px] text-gray-500">Expira: {{ $order->expected_at->format('d/m/Y') }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm font-bold text-gray-900 tracking-tight">
                                    {{ $order->currency }} ${{ number_format($order->total, 2) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <x-status-badge :status="$order->status" :label="\App\Models\PurchaseOrder::STATUS[$order->status] ?? $order->status" />
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a wire:navigate href="{{ route('purchases.orders.show', $order) }}"
                                    class="inline-flex items-center justify-center p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <x-empty-state message="No se encontraron órdenes de compra." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if($orders->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    @endif

    {{-- Tab: Desde Requisición --}}
    @if($tab === 'from_requisition')
        <div class="bg-indigo-50/50 border border-indigo-100 rounded-2xl p-4 mb-6 flex items-start gap-4">
            <div class="p-2.5 bg-white text-indigo-600 rounded-xl shadow-sm ring-1 ring-indigo-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-bold text-indigo-950">Conversión de Requisiciones</p>
                <p class="text-xs text-indigo-700 mt-0.5 leading-relaxed">Las siguientes requisiciones han sido autorizadas y están listas para convertirse en órdenes de compra formales.</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 p-5 mb-6 shadow-sm">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input wire:model.live.debounce.300ms="searchReq" type="text"
                    placeholder="Filtrar requisiciones pendientes..."
                    class="w-full pl-9 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all">
            </div>
        </div>

        {{-- VISTA MÓVIL (Requisiciones) --}}
        <div class="space-y-4 lg:hidden mb-6">
            @forelse($requisitions as $req)
                @php
                    $q = $req->finalQuotation;
                    $hasOrder = $req->order !== null;
                @endphp
                <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <span class="font-mono text-xs font-bold text-indigo-600">{{ $req->folio }}</span>
                            <h3 class="font-bold text-gray-900 mt-0.5 line-clamp-1">{{ $req->justification ?? 'Sin justificación' }}</h3>
                        </div>
                        @if($hasOrder)
                            <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 text-[10px] font-bold rounded-lg uppercase tracking-wider">Completado</span>
                        @else
                            <span class="px-2 py-0.5 bg-amber-100 text-amber-700 text-[10px] font-bold rounded-lg uppercase tracking-wider animate-pulse">Pendiente</span>
                        @endif
                    </div>

                    <div class="bg-gray-50 rounded-xl p-3 mb-4 text-sm space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Solicitante:</span>
                            <span class="font-medium text-gray-900">{{ $req->requestedBy?->name ?? '—' }}</span>
                        </div>
                        <div class="flex justify-between border-t border-gray-200/50 pt-2">
                            <span class="text-gray-500">Total cotizado:</span>
                            @if($q)
                                <span class="font-bold text-indigo-600">{{ $req->currency }} ${{ number_format($q->total, 2) }}</span>
                            @else
                                <span class="text-gray-400">N/A</span>
                            @endif
                        </div>
                    </div>

                    @if($hasOrder)
                        <a wire:navigate href="{{ route('purchases.orders.show', $req->order) }}"
                            class="flex items-center justify-center w-full py-2 bg-indigo-50 text-indigo-600 rounded-xl text-xs font-bold">
                            Ver OC Generada
                        </a>
                    @elseif($q)
                        <a wire:navigate href="{{ route('purchases.orders.create', ['quotation' => $q->id]) }}"
                            class="flex items-center justify-center w-full py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-md shadow-indigo-200">
                            Crear Orden de Compra
                        </a>
                    @endif
                </div>
            @empty
                <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center shadow-sm">
                    <x-empty-state message="No hay requisiciones pendientes." />
                </div>
            @endforelse
            {{ $requisitions->links('vendor.pagination.tailwind') }}
        </div>

        {{-- VISTA ESCRITORIO (Requisiciones) --}}
        <div class="hidden lg:block bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-left">
                        <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Requisición</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center">Estado</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider text-right">Total Cotizado</th>
                        <th class="px-6 py-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($requisitions as $req)
                        @php
                            $q = $req->finalQuotation;
                            $hasOrder = $req->order !== null;
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="font-mono text-xs font-bold text-indigo-600">{{ $req->folio }}</span>
                                    <span class="font-medium text-gray-900 line-clamp-1">{{ $req->justification ?? 'Sin justificación' }}</span>
                                    <span class="text-[10px] text-gray-500 mt-1 uppercase">{{ $req->requestedBy?->name ?? '—' }} • {{ $req->branch?->name ?? '—' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($hasOrder)
                                    <span class="inline-flex px-2 py-0.5 rounded-lg bg-emerald-100 text-emerald-700 text-[10px] font-bold uppercase tracking-wider">Completado</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-lg bg-amber-100 text-amber-700 text-[10px] font-bold uppercase tracking-wider animate-pulse">Pendiente de OC</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if($q)
                                    <span class="font-bold text-gray-900">{{ $req->currency }} ${{ number_format($q->total, 2) }}</span>
                                @else
                                    <span class="text-xs text-gray-400 italic">No disponible</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if($hasOrder)
                                    <a wire:navigate href="{{ route('purchases.orders.show', $req->order) }}"
                                        class="inline-flex items-center gap-1.5 text-xs font-bold text-indigo-600 hover:text-indigo-800 transition-colors">
                                        Ver OC
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                @elseif($q)
                                    <a wire:navigate href="{{ route('purchases.orders.create', ['quotation' => $q->id]) }}"
                                        class="inline-flex items-center gap-2 text-xs bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-4 py-2 rounded-xl transition-all shadow-sm hover:shadow-indigo-200">
                                        + Generar OC
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-gray-400">
                                <x-empty-state message="No hay requisiciones pendientes de procesar." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if($requisitions->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                    {{ $requisitions->links('vendor.pagination.tailwind') }}
                </div>
            @endif
        </div>
    @endif
</div>


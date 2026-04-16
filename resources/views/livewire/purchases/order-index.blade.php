<div>
    <x-page-header title="Órdenes de compra" description="Gestión del ciclo de vida de órdenes de compra a proveedores">
        <x-slot:actions>
            <div class="flex items-center gap-2">
                <button type="button"
                    x-data
                    @click="
                        const base = '{{ route('purchases.orders.report.print') }}';
                        const params = new URLSearchParams();
                        if ($wire.search)       params.set('search', $wire.search);
                        if ($wire.filterStatus) params.set('status', $wire.filterStatus);
                        window.open(base + '?' + params.toString(), '_blank');
                    "
                    class="hidden sm:inline-flex items-center gap-2 text-sm bg-white hover:bg-gray-50 border border-gray-300 px-4 py-2.5 rounded-xl transition-all duration-200 text-gray-700 shadow-sm font-medium">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Imprimir reporte
                </button>
                <a wire:navigate href="{{ route('purchases.orders.create') }}"
                    class="inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-all duration-200 shadow-sm hover:shadow-md active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    Nueva orden
                </a>
            </div>
        </x-slot:actions>
    </x-page-header>

    <x-alert />

    {{-- Styled Tabs --}}
    <div class="flex items-center p-1 bg-gray-100 rounded-xl mb-6 w-fit">
        <button wire:click="switchTab('orders')"
            class="px-5 py-2 text-sm font-bold rounded-lg transition-all duration-200 {{ $tab === 'orders' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
            Órdenes Activas
        </button>
        <button wire:click="switchTab('from_requisition')"
            class="px-5 py-2 text-sm font-bold rounded-lg transition-all duration-200 flex items-center gap-2 {{ $tab === 'from_requisition' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
            Pendientes de OC
            @if($requisitions->total() > 0)
                <span class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full text-[10px] font-black {{ $tab === 'from_requisition' ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-200 text-gray-600' }}">
                    {{ $requisitions->total() }}
                </span>
            @endif
        </button>
    </div>

    {{-- Tab: Todas las órdenes --}}
    @if($tab === 'orders')
        <div class="bg-white rounded-2xl border border-gray-200 p-4 mb-6 shadow-sm">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="relative flex-grow group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400 group-focus-within:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input wire:model.live.debounce.300ms="search" type="text"
                        placeholder="Buscar por folio o proveedor..."
                        class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all duration-200">
                </div>
                <select wire:model.live="filterStatus"
                    class="min-w-[180px] bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all duration-200 appearance-none cursor-pointer">
                    <option value="">Todos los estados</option>
                    @foreach(\App\Models\PurchaseOrder::STATUS as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto overflow-visible">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/50">
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest">Orden & Proveedor</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest hidden md:table-cell">Detalles</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest">Total</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest">Estado</th>
                            <th class="px-6 py-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($orders as $order)
                            <tr class="group hover:bg-gray-50/80 transition-all duration-200">
                                <td class="px-6 py-5">
                                    <div class="flex flex-col">
                                        <span class="font-mono text-xs font-bold text-indigo-600 mb-0.5 tracking-tight">
                                            {{ $order->folio }}
                                        </span>
                                        <span class="font-semibold text-gray-900">{{ $order->supplier?->name ?? '—' }}</span>
                                        <span class="text-[10px] text-gray-400 mt-1">
                                            Sucursal: {{ $order->branch?->name ?? '—' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-5 hidden md:table-cell">
                                    <div class="flex flex-col gap-1">
                                        <span class="px-2 py-0.5 bg-gray-100 text-gray-600 text-[10px] font-bold rounded-lg w-fit">{{ $order->items_count }} productos</span>
                                        @if($order->expected_at)
                                            <span class="text-[10px] text-gray-500">Exp: {{ $order->expected_at->format('d/m/Y') }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-gray-900 tracking-tight">{{ $order->currency }} ${{ number_format($order->total, 2) }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <x-status-badge :status="$order->status" :label="\App\Models\PurchaseOrder::STATUS[$order->status] ?? $order->status" />
                                </td>
                                <td class="px-6 py-5 text-right">
                                    <a wire:navigate href="{{ route('purchases.orders.show', $order) }}"
                                        class="inline-flex items-center justify-center p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all duration-200">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
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
            </div>
            @if($orders->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/30">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    @endif

    {{-- Tab: Desde requisición --}}
    @if($tab === 'from_requisition')
        <div class="bg-indigo-50/50 border border-indigo-100 rounded-2xl p-4 mb-6 flex items-start gap-3">
            <div class="p-2 bg-indigo-100 text-indigo-600 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-sm font-bold text-indigo-900">Conversión de Requisiciones</p>
                <p class="text-xs text-indigo-700">Las siguientes requisiciones han sido autorizadas y están listas para convertirse en órdenes de compra formales.</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 p-4 mb-6 shadow-sm">
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400 group-focus-within:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input wire:model.live.debounce.300ms="searchReq" type="text"
                    placeholder="Filtrar requisiciones pendientes..."
                    class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all duration-200">
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto overflow-visible">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/50">
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest">Requisición</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest hidden md:table-cell">Solicitante</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest">Total Cotizado</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest text-center">Estado</th>
                            <th class="px-6 py-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($requisitions as $req)
                            @php
                                $q = $req->finalQuotation;
                                $hasOrder = $req->order !== null;
                            @endphp
                            <tr class="group hover:bg-gray-50/80 transition-all duration-200">
                                <td class="px-6 py-5">
                                    <div class="flex flex-col">
                                        <span class="font-mono text-xs font-bold text-indigo-600 mb-0.5 tracking-tight">
                                            {{ $req->folio }}
                                        </span>
                                        <span class="text-sm font-medium text-gray-900 line-clamp-1">{{ $req->justification ?? 'Sin justificación' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-5 hidden md:table-cell">
                                    <div class="flex flex-col">
                                        <span class="text-gray-700 font-medium">{{ $req->requestedBy?->name ?? '—' }}</span>
                                        <span class="text-[10px] text-gray-400">{{ $req->branch?->name ?? '—' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    @if($q)
                                        <span class="text-sm font-bold text-gray-900 tracking-tight">{{ $req->currency }} ${{ number_format($q->total, 2) }}</span>
                                    @else
                                        <span class="text-[10px] text-gray-400 italic">No disponible</span>
                                    @endif
                                </td>
                                <td class="px-6 py-5 text-center">
                                    @if($hasOrder)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-widest bg-emerald-100 text-emerald-700">Completado</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-widest bg-amber-100 text-amber-700 animate-pulse">Pendiente</span>
                                    @endif
                                </td>
                                <td class="px-6 py-5 text-right">
                                    @if($hasOrder)
                                        <a wire:navigate href="{{ route('purchases.orders.show', $req->order) }}"
                                            class="inline-flex items-center gap-1.5 text-xs font-bold text-indigo-600 hover:text-indigo-800 transition-colors">
                                            Ver OC
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7-7 7"/></svg>
                                        </a>
                                    @elseif($q)
                                        <a wire:navigate href="{{ route('purchases.orders.create', ['quotation' => $q->id]) }}"
                                            class="inline-flex items-center gap-2 text-xs bg-indigo-600 hover:bg-indigo-700 text-white font-black px-4 py-2 rounded-xl transition-all duration-200 shadow-sm hover:shadow-indigo-200">
                                            + Crear OC
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-400 text-sm">
                                    No hay requisiciones pendientes de procesar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($requisitions->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/30">
                    {{ $requisitions->links() }}
                </div>
            @endif
        </div>
    @endif
</div>

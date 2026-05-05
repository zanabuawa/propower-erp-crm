<div>
    {{-- Header --}}
    <x-page-header title="Recepciones" description="Entradas de mercancía: compras, transferencias y devoluciones">
        <x-slot:actions>
            @can('create purchases')
            <a wire:navigate href="{{ route('purchases.goods-receipts.create') }}"
                class="inline-flex items-center gap-2 bg-gradient-to-r from-teal-600 to-teal-700 hover:from-teal-700 hover:to-teal-800 text-white text-sm font-medium px-5 py-2 rounded-xl transition-all duration-200 shadow-lg shadow-teal-500/25 hover:shadow-teal-500/40 hover:scale-105">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nueva recepción
            </a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <x-alert />

    {{-- Filtros --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-5 mb-6 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input wire:model.live.debounce.300ms="search" type="text"
                    placeholder="Buscar por folio o referencia..."
                    class="w-full pl-9 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500 transition-all">
            </div>

            <div class="relative">
                <select wire:model.live="filterType"
                    class="w-full pl-4 pr-10 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-teal-500 cursor-pointer transition-all hover:bg-gray-100 appearance-none">
                    <option value="">Todos los tipos</option>
                    @foreach($types as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>

            <div class="relative">
                <select wire:model.live="filterWarehouse"
                    class="w-full pl-4 pr-10 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-teal-500 cursor-pointer transition-all hover:bg-gray-100 appearance-none">
                    <option value="">Todos los almacenes</option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                    @endforeach
                </select>
                <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- SECCIÓN: RECEPCIONES PENDIENTES --}}
    @if($pending->isNotEmpty())
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-4 ml-1">
                <div class="flex h-3 w-3 relative">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-500"></span>
                </div>
                <h2 class="text-base font-bold text-gray-800">Recepciones pendientes</h2>
                <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-bold bg-amber-100 text-amber-700">
                    {{ $pending->count() }}
                </span>
            </div>

            {{-- VISTA MÓVIL (Pendientes) --}}
            <div class="space-y-4 lg:hidden mb-6">
                @foreach($pending as $mov)
                    <div class="bg-white rounded-2xl border border-amber-200 p-5 shadow-sm">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <span class="font-mono text-xs font-bold text-teal-600">{{ $mov->folio }}</span>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-sm font-semibold text-gray-700">{{ $mov->warehouse?->name }}</span>
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                                    <span class="text-sm font-bold text-teal-700">{{ $mov->warehouseDestination?->name }}</span>
                                </div>
                            </div>
                            @php
                                $pStatus = match($mov->status) {
                                    'requested'          => ['bg-blue-100 text-blue-700',   'Solicitada'],
                                    'in_transit'         => ['bg-amber-100 text-amber-700', 'En tránsito'],
                                    'partially_received' => ['bg-orange-100 text-orange-700', 'Parcial'],
                                    default              => ['bg-gray-100 text-gray-600',   $mov->status],
                                };
                            @endphp
                            <span class="inline-flex px-2 py-0.5 rounded-lg text-[10px] font-bold uppercase tracking-wider {{ $pStatus[0] }}">
                                {{ $pStatus[1] }}
                            </span>
                        </div>

                        <div class="bg-gray-50 rounded-xl p-3 mb-4">
                            <p class="text-[10px] text-gray-500 uppercase font-bold tracking-wider mb-2">Productos a recibir</p>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($mov->items as $item)
                                    <span class="inline-flex items-center px-2 py-1 rounded-lg bg-white border border-gray-200 text-[10px] font-medium text-gray-700">
                                        {{ $item->product?->name }} <span class="text-teal-600 font-bold ml-1">×{{ number_format($item->quantity, 0) }}</span>
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        <a wire:navigate href="{{ route('purchases.goods-receipts.create', ['transfer_id' => $mov->id]) }}"
                            class="flex items-center justify-center w-full py-2.5 bg-teal-600 text-white rounded-xl text-sm font-bold shadow-md shadow-teal-200">
                            Recibir Mercancía
                        </a>
                    </div>
                @endforeach
            </div>

            {{-- VISTA ESCRITORIO (Pendientes) --}}
            <div class="hidden lg:block bg-white rounded-2xl border border-amber-200 overflow-hidden shadow-sm">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-amber-50/50 border-b border-amber-100 text-left">
                            <th class="px-6 py-4 text-xs font-semibold text-amber-800 uppercase tracking-wider">Movimiento</th>
                            <th class="px-6 py-4 text-xs font-semibold text-amber-800 uppercase tracking-wider">Origen → Destino</th>
                            <th class="px-6 py-4 text-xs font-semibold text-amber-800 uppercase tracking-wider">Productos</th>
                            <th class="px-6 py-4 text-xs font-semibold text-amber-800 uppercase tracking-wider text-center">Estado</th>
                            <th class="px-6 py-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-amber-100/50">
                        @foreach($pending as $mov)
                            <tr class="hover:bg-amber-50/30 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="font-mono text-xs font-bold text-teal-600">{{ $mov->folio }}</span>
                                        <span class="text-[10px] text-gray-500 mt-1 uppercase">{{ $mov->moved_at?->format('d/m/Y H:i') }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm text-gray-600">{{ $mov->warehouse?->name }}</span>
                                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                                        <span class="text-sm font-bold text-teal-700">{{ $mov->warehouseDestination?->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($mov->items->take(3) as $item)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-lg bg-white border border-gray-200 text-[10px] text-gray-600 font-medium">
                                                {{ $item->product?->name }} <span class="text-teal-600 font-bold ml-1">×{{ number_format($item->quantity, 0) }}</span>
                                            </span>
                                        @endforeach
                                        @if($mov->items->count() > 3)
                                            <span class="text-[10px] text-gray-400">+{{ $mov->items->count() - 3 }} más</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @php
                                        $pStatus = match($mov->status) {
                                            'requested'          => ['bg-blue-100 text-blue-700',   'Solicitada'],
                                            'in_transit'         => ['bg-amber-100 text-amber-700', 'En tránsito'],
                                            'partially_received' => ['bg-orange-100 text-orange-700', 'Parcial'],
                                            default              => ['bg-gray-100 text-gray-600',   $mov->status],
                                        };
                                    @endphp
                                    <span class="inline-flex px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider {{ $pStatus[0] }}">
                                        {{ $pStatus[1] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a wire:navigate href="{{ route('purchases.goods-receipts.create', ['transfer_id' => $mov->id]) }}"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold bg-teal-600 hover:bg-teal-700 text-white rounded-lg transition-all shadow-sm">
                                        Recibir
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- SECCIÓN: HISTORIAL DE RECEPCIONES --}}
    <div>
        <div class="flex items-center gap-3 mb-4 ml-1">
            <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h2 class="text-base font-bold text-gray-800">Historial de recepciones</h2>
        </div>

        {{-- VISTA MÓVIL (Historial) --}}
        <div class="space-y-4 lg:hidden mb-6">
            @forelse($receipts as $receipt)
                @php
                    $hType = match($receipt->reception_type) {
                        'purchase'  => ['bg-teal-100 text-teal-700',   'Compra', 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z'],
                        'return'    => ['bg-rose-100 text-rose-700',   'Devolución', 'M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6'],
                        'transfer'  => ['bg-indigo-100 text-indigo-700', 'Transferencia', 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4'],
                        default     => ['bg-gray-100 text-gray-600',   $receipt->reception_type, 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
                    };
                @endphp
                <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <span class="font-mono text-xs font-bold text-teal-600">{{ $receipt->folio }}</span>
                            <h3 class="font-bold text-gray-900 mt-0.5 line-clamp-1">{{ $receipt->warehouse?->name }}</h3>
                        </div>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-bold uppercase tracking-wider {{ $hType[0] }}">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $hType[2] }}"/></svg>
                            {{ $hType[1] }}
                        </span>
                    </div>

                    <div class="bg-gray-50 rounded-xl p-3 mb-4 space-y-2">
                        @if($receipt->order)
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-gray-500">Orden de Compra:</span>
                                <span class="font-mono font-bold text-teal-600">{{ $receipt->order->folio }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-gray-500">Recibido por:</span>
                            <span class="font-medium text-gray-700">{{ $receipt->receivedBy?->name }}</span>
                        </div>
                        <div class="flex justify-between items-center text-xs pt-1 border-t border-gray-200/50">
                            <span class="text-gray-500">Fecha:</span>
                            <span class="text-gray-700 font-semibold">{{ $receipt->received_at?->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>

                    <a href="{{ route('purchases.receipts.print', $receipt) }}" target="_blank"
                        class="flex items-center justify-center w-full py-2.5 bg-teal-50 text-teal-700 border border-teal-100 rounded-xl text-sm font-bold hover:bg-teal-100 transition-all">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Ver Comprobante
                    </a>
                </div>
            @empty
                <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center shadow-sm">
                    <x-empty-state message="No se encontraron recepciones." />
                </div>
            @endforelse
            {{ $receipts->links() }}
        </div>

        {{-- VISTA ESCRITORIO (Historial) --}}
        <div class="hidden lg:block bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-left">
                        <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Folio / Almacén</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center">Tipo</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Referencia / Origen</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center">Recibido por</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center">Fecha</th>
                        <th class="px-6 py-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($receipts as $receipt)
                        @php
                            $hType = match($receipt->reception_type) {
                                'purchase'  => ['bg-teal-100 text-teal-700',   'Compra', 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z'],
                                'return'    => ['bg-rose-100 text-rose-700',   'Devolución', 'M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6'],
                                'transfer'  => ['bg-indigo-100 text-indigo-700', 'Transferencia', 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4'],
                                default     => ['bg-gray-100 text-gray-600',   $receipt->reception_type, 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
                            };
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="font-mono text-xs font-bold text-teal-600">{{ $receipt->folio }}</span>
                                    <span class="font-semibold text-gray-900 line-clamp-1">{{ $receipt->warehouse?->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider {{ $hType[0] }}">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $hType[2] }}"/></svg>
                                    {{ $hType[1] }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($receipt->reception_type === 'transfer' && $receipt->originMovement)
                                    <div class="flex items-center gap-2 text-xs font-medium text-gray-700">
                                        <span class="px-1.5 py-0.5 bg-indigo-50 text-indigo-600 rounded">Traspaso</span>
                                        <span class="text-gray-400">de</span>
                                        <span>{{ $receipt->originMovement->warehouse?->name }}</span>
                                    </div>
                                @elseif($receipt->reception_type === 'purchase' && $receipt->order)
                                    <div class="flex items-center gap-2 text-xs font-medium text-gray-700">
                                        <span class="font-mono text-teal-600 font-bold underline">{{ $receipt->order->folio }}</span>
                                        <span class="text-gray-400">•</span>
                                        <span class="line-clamp-1 text-[10px]">{{ $receipt->order->supplier?->name }}</span>
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400 italic">{{ $receipt->notes ?: 'Sin referencia' }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-teal-50 flex items-center justify-center text-[10px] font-bold text-teal-700 border border-teal-100">
                                        {{ mb_substr($receipt->receivedBy?->name ?? '?', 0, 1) }}
                                    </div>
                                    <span class="text-xs text-gray-700 font-medium">{{ $receipt->receivedBy?->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex flex-col">
                                    <span class="text-xs text-gray-700 font-semibold">{{ $receipt->received_at?->format('d/m/Y') }}</span>
                                    <span class="text-[10px] text-gray-400 font-medium">{{ $receipt->received_at?->format('H:i') }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('purchases.receipts.print', $receipt) }}" target="_blank"
                                    class="inline-flex items-center justify-center p-2 text-gray-400 hover:text-teal-600 hover:bg-teal-50 rounded-xl transition-all"
                                    title="Imprimir comprobante">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <x-empty-state message="No se encontraron recepciones registradas." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($receipts->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 mt-1">
                {{ $receipts->links('vendor.pagination.tailwind') }}
            </div>
        @endif    </div>
</div>

<div>
    <x-page-header title="Recepciones" description="Entradas de mercancía: compras, transferencias, devoluciones y defectuosos.">
        <x-slot:actions>
            @can('create purchases')
            <a wire:navigate href="{{ route('purchases.goods-receipts.create') }}"
                class="inline-flex items-center justify-center gap-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-all duration-200 shadow-sm hover:shadow-md active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                Nueva recepción
            </a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <x-alert />

    {{-- ── Filtros ────────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-4 mb-6 shadow-sm">
        <div class="flex flex-col sm:flex-row gap-3">
            {{-- Buscador --}}
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input wire:model.live.debounce.300ms="search" type="text"
                    placeholder="Buscar por folio o referencia…"
                    class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all">
            </div>

            {{-- Tipo --}}
            <div class="relative">
                <select wire:model.live="filterType"
                    class="pl-4 pr-10 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-700 focus:ring-2 focus:ring-teal-500 appearance-none cursor-pointer transition-all">
                    <option value="">Todos los tipos</option>
                    @foreach($types as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </div>

            {{-- Almacén --}}
            <div class="relative">
                <select wire:model.live="filterWarehouse"
                    class="pl-4 pr-10 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-700 focus:ring-2 focus:ring-teal-500 appearance-none cursor-pointer transition-all">
                    <option value="">Todos los almacenes</option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                    @endforeach
                </select>
                <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════
         SECCIÓN 1: RECEPCIONES PENDIENTES (transferencias en tránsito)
    ══════════════════════════════════════════════════════════════════ --}}
    @if($pending->isNotEmpty())
    <div class="mb-8">
        <div class="flex items-center gap-2 mb-3">
            <span class="relative flex h-2.5 w-2.5">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-amber-500"></span>
            </span>
            <h2 class="text-sm font-bold text-gray-800">Recepciones pendientes</h2>
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-700">
                {{ $pending->count() }}
            </span>
        </div>

        <div class="bg-white rounded-2xl border border-amber-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="bg-amber-50/60 border-b border-amber-100">
                            <th class="px-5 py-3.5 text-xs font-bold text-amber-700 uppercase tracking-widest">Folio</th>
                            <th class="px-5 py-3.5 text-xs font-bold text-amber-700 uppercase tracking-widest">Origen → Destino</th>
                            <th class="px-5 py-3.5 text-xs font-bold text-amber-700 uppercase tracking-widest hidden md:table-cell">Enviado por</th>
                            <th class="px-5 py-3.5 text-xs font-bold text-amber-700 uppercase tracking-widest hidden sm:table-cell">Productos</th>
                            <th class="px-5 py-3.5 text-xs font-bold text-amber-700 uppercase tracking-widest text-center">Estado</th>
                            <th class="px-5 py-3.5"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-amber-50">
                        @foreach($pending as $mov)
                        <tr class="hover:bg-amber-50/40 transition-colors group">
                            <td class="px-5 py-4">
                                <span class="font-mono text-xs font-bold text-teal-600 tracking-tight">{{ $mov->folio }}</span>
                                <p class="text-[10px] text-gray-400 mt-0.5">{{ $mov->moved_at?->format('d/m/Y H:i') }}</p>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2 text-sm">
                                    <span class="font-medium text-gray-700">{{ $mov->warehouse?->name ?? '—' }}</span>
                                    <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                    </svg>
                                    <span class="font-semibold text-teal-700">{{ $mov->warehouseDestination?->name ?? '—' }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-4 hidden md:table-cell">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center text-[9px] font-bold text-gray-600 flex-shrink-0">
                                        {{ mb_substr($mov->user?->name ?? '?', 0, 1) }}
                                    </div>
                                    <span class="text-sm text-gray-700">{{ $mov->user?->name ?? '—' }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-4 hidden sm:table-cell">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($mov->items->take(2) as $item)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg bg-gray-100 text-gray-600 text-[10px] font-bold">
                                            {{ $item->product?->name ?? '—' }}
                                            <span class="text-teal-600 ml-1">×{{ number_format($item->quantity, 0) }}</span>
                                        </span>
                                    @endforeach
                                    @if($mov->items->count() > 2)
                                        <span class="text-[10px] text-gray-400">+{{ $mov->items->count() - 2 }} más</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-4 text-center">
                                @php
                                    $statusConfig = match($mov->status) {
                                        'requested'          => ['bg-blue-100 text-blue-700',   'Solicitada'],
                                        'in_transit'         => ['bg-amber-100 text-amber-700', 'En tránsito'],
                                        'partially_received' => ['bg-orange-100 text-orange-700', 'Parcial'],
                                        default              => ['bg-gray-100 text-gray-600',   $mov->status],
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold {{ $statusConfig[0] }}">
                                    {{ $statusConfig[1] }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                @can('create purchases')
                                <a wire:navigate href="{{ route('purchases.goods-receipts.create', ['transfer_id' => $mov->id]) }}"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold bg-teal-600 hover:bg-teal-700 text-white rounded-lg transition-all opacity-0 group-hover:opacity-100">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Recibir
                                </a>
                                @endcan
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════════
         SECCIÓN 2: HISTORIAL DE RECEPCIONES COMPLETADAS
    ══════════════════════════════════════════════════════════════════ --}}
    <div>
        <div class="flex items-center gap-2 mb-3">
            <svg class="w-4 h-4 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h2 class="text-sm font-bold text-gray-800">Historial de recepciones</h2>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="bg-gray-50/50 border-b border-gray-100">
                            <th class="px-5 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest">Folio & Almacén</th>
                            <th class="px-5 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest">Tipo</th>
                            <th class="px-5 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest hidden lg:table-cell">Origen / Ref.</th>
                            <th class="px-5 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest hidden sm:table-cell">Productos</th>
                            <th class="px-5 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest hidden md:table-cell">Recibido por</th>
                            <th class="px-5 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest hidden md:table-cell">Fecha</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($receipts as $receipt)
                        @php
                            $typeConfig = match($receipt->reception_type) {
                                'purchase'  => ['bg-teal-100 text-teal-700',   'Compra'],
                                'return'    => ['bg-rose-100 text-rose-700',   'Devolución'],
                                'transfer'  => ['bg-indigo-100 text-indigo-700', 'Transferencia'],
                                'defective' => ['bg-orange-100 text-orange-700', 'Defectuoso'],
                                default     => ['bg-gray-100 text-gray-600',   $receipt->reception_type],
                            };
                        @endphp
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="px-5 py-4">
                                <span class="font-mono text-xs font-bold text-teal-600 block tracking-tight">{{ $receipt->folio }}</span>
                                <span class="text-sm font-semibold text-gray-800">{{ $receipt->warehouse?->name ?? '—' }}</span>
                            </td>

                            <td class="px-5 py-4">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold {{ $typeConfig[0] }}">
                                    @if($receipt->reception_type === 'return')
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                    @elseif($receipt->reception_type === 'transfer')
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                    @elseif($receipt->reception_type === 'purchase')
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                    @endif
                                    {{ $typeConfig[1] }}
                                </span>
                            </td>

                            <td class="px-5 py-4 hidden lg:table-cell">
                                @if($receipt->reception_type === 'transfer' && $receipt->originMovement)
                                    <div class="flex items-center gap-1.5 text-xs text-gray-600">
                                        <svg class="w-3.5 h-3.5 text-indigo-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/>
                                        </svg>
                                        <span class="font-medium">{{ $receipt->originMovement->warehouse?->name ?? '—' }}</span>
                                    </div>
                                    <p class="text-[10px] text-gray-400 mt-0.5 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        Envió: {{ $receipt->originMovement->user?->name ?? '—' }}
                                    </p>
                                @elseif($receipt->reception_type === 'purchase' && $receipt->order)
                                    <div class="flex items-center gap-1.5 text-xs text-gray-600">
                                        <svg class="w-3.5 h-3.5 text-teal-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                        </svg>
                                        <span class="font-mono font-medium">{{ $receipt->order->folio }}</span>
                                    </div>
                                    @if($receipt->order->supplier)
                                        <p class="text-[10px] text-gray-400 mt-0.5">{{ $receipt->order->supplier->name }}</p>
                                    @endif
                                @elseif($receipt->notes)
                                    <span class="text-xs text-gray-500 italic">{{ $receipt->notes }}</span>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>

                            <td class="px-5 py-4 hidden sm:table-cell">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($receipt->items->take(2) as $item)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg bg-gray-100 text-gray-600 text-[10px] font-bold">
                                            {{ $item->product?->name ?? '—' }}
                                            <span class="text-teal-600 ml-1">×{{ number_format($item->quantity, 0) }}</span>
                                        </span>
                                    @endforeach
                                    @if($receipt->items->count() > 2)
                                        <span class="text-[10px] text-gray-400">+{{ $receipt->items->count() - 2 }} más</span>
                                    @endif
                                </div>
                            </td>

                            <td class="px-5 py-4 hidden md:table-cell">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-teal-100 flex items-center justify-center text-[10px] font-bold text-teal-700 flex-shrink-0">
                                        {{ mb_substr($receipt->receivedBy?->name ?? '?', 0, 1) }}
                                    </div>
                                    <span class="text-sm text-gray-700">{{ $receipt->receivedBy?->name ?? '—' }}</span>
                                </div>
                            </td>

                            <td class="px-5 py-4 hidden md:table-cell">
                                <span class="text-sm text-gray-700">{{ $receipt->received_at?->format('d/m/Y') }}</span>
                                <p class="text-[10px] text-gray-400">{{ $receipt->received_at?->format('H:i') }}</p>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-400 text-sm italic">
                                No se encontraron recepciones con los filtros aplicados.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($receipts->hasPages())
                <div class="px-5 py-4 border-t border-gray-100 bg-gray-50/30">
                    {{ $receipts->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

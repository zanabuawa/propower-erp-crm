<div class="max-w-5xl space-y-5">

    {{-- ── HEADER ───────────────────────────────────────────────────────────── --}}
    <div class="flex items-start justify-between gap-3">
        <div class="flex items-center gap-3">
            <a wire:navigate href="{{ route('inventory.transfers.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-xl font-medium text-gray-900">{{ $stockMovement->folio }}</h1>
                    @php
                        $statusColors = [
                            'requested'          => 'bg-blue-50 text-blue-700 border-blue-100',
                            'accepted'           => 'bg-indigo-50 text-indigo-700 border-indigo-100',
                            'in_transit'         => 'bg-amber-50 text-amber-700 border-amber-100',
                            'partially_received' => 'bg-orange-50 text-orange-700 border-orange-100',
                            'completed'          => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                            'rejected'           => 'bg-red-50 text-red-700 border-red-100',
                            'cancelled'          => 'bg-red-50 text-red-700 border-red-100',
                        ];
                        $isLocal = $stockMovement->warehouse?->branch_id &&
                                   $stockMovement->warehouse->branch_id === $stockMovement->warehouseDestination?->branch_id;
                    @endphp
                    @if($isLocal)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-teal-50 text-teal-700 border border-teal-100">
                            Local
                        </span>
                    @endif
                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $statusColors[$stockMovement->status] ?? 'bg-gray-100 text-gray-500 border-gray-200' }}">
                        {{ \App\Models\StockMovement::TRANSFER_STATUSES[$stockMovement->status] ?? $stockMovement->status }}
                    </span>
                </div>
                <p class="text-xs text-gray-400 mt-0.5">
                    Solicitada por <strong>{{ $stockMovement->user?->name ?? '—' }}</strong>
                    el {{ $stockMovement->created_at->format('d/m/Y H:i') }}
                </p>
            </div>
        </div>

        {{-- Action button for authorized users --}}
        @if($stockMovement->isEditable())
            <a wire:navigate href="{{ route('inventory.transfers.action', $stockMovement) }}"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Gestionar transferencia
            </a>
        @endif
    </div>

    <x-alert />

    {{-- ── STATUS STEPPER ────────────────────────────────────────────────────── --}}
    @php
        $steps = [
            ['key' => 'requested',          'label' => 'Solicitada',   'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
            ['key' => 'accepted',           'label' => 'Aceptada',     'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0'],
            ['key' => 'in_transit',         'label' => 'Enviada',      'icon' => 'M12 19l9 2-9-18-9 18 9-2zm0 0v-8'],
            ['key' => 'partially_received', 'label' => 'Recepción parcial', 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
            ['key' => 'completed',          'label' => 'Completada',   'icon' => 'M5 13l4 4L19 7'],
        ];

        $rejectedOrCancelled = in_array($stockMovement->status, ['rejected', 'cancelled']);

        $statusOrder = ['requested' => 0, 'accepted' => 1, 'in_transit' => 2, 'partially_received' => 3, 'completed' => 4];
        $currentIdx  = $statusOrder[$stockMovement->status] ?? -1;
    @endphp

    <div class="bg-white rounded-2xl border border-gray-200 p-5">
        @if($rejectedOrCancelled)
            <div class="flex items-center gap-3 py-2">
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-red-700">
                        {{ $stockMovement->status === 'rejected' ? 'Transferencia rechazada' : 'Transferencia cancelada' }}
                    </p>
                    @if($stockMovement->dispatch_notes)
                        <p class="text-xs text-red-500 mt-0.5">{{ $stockMovement->dispatch_notes }}</p>
                    @endif
                </div>
            </div>
        @else
            <ol class="flex items-center w-full">
                @foreach($steps as $i => $step)
                    @php
                        $stepIdx  = $statusOrder[$step['key']] ?? $i;
                        $done     = $currentIdx > $stepIdx;
                        $active   = $currentIdx === $stepIdx;
                        $isLast   = $i === count($steps) - 1;
                    @endphp
                    <li class="flex items-center {{ $isLast ? '' : 'flex-1' }}">
                        <div class="flex flex-col items-center">
                            <div @class([
                                'w-9 h-9 rounded-full flex items-center justify-center border-2 transition-all',
                                'bg-indigo-600 border-indigo-600 text-white' => $done || $active,
                                'bg-white border-gray-300 text-gray-400' => !$done && !$active,
                            ])>
                                @if($done)
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                @else
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $step['icon'] }}"/>
                                    </svg>
                                @endif
                            </div>
                            <span @class([
                                'text-[10px] font-medium mt-1.5 text-center leading-tight',
                                'text-indigo-700' => $done || $active,
                                'text-gray-400'   => !$done && !$active,
                            ])>{{ $step['label'] }}</span>
                        </div>
                        @if(!$isLast)
                            <div @class([
                                'flex-1 h-0.5 mx-2 mb-4',
                                'bg-indigo-500' => $currentIdx > $stepIdx,
                                'bg-gray-200'   => $currentIdx <= $stepIdx,
                            ])></div>
                        @endif
                    </li>
                @endforeach
            </ol>
        @endif
    </div>

    {{-- ── ORIGIN / DESTINATION ─────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-5">
        <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4">Origen y destino</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
            <div>
                <p class="text-xs text-gray-400 mb-0.5">Almacén origen</p>
                <p class="font-medium text-gray-900">{{ $stockMovement->warehouse?->name ?? '—' }}</p>
                <p class="text-xs text-gray-500">{{ $stockMovement->warehouse?->branch?->name }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-0.5">Almacén destino</p>
                <p class="font-medium text-gray-900">{{ $stockMovement->warehouseDestination?->name ?? '—' }}</p>
                <p class="text-xs text-gray-500">{{ $stockMovement->warehouseDestination?->branch?->name }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-0.5">Fecha solicitada</p>
                <p class="font-medium text-gray-900">{{ $stockMovement->moved_at->format('d/m/Y H:i') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-0.5">Referencia</p>
                <p class="font-medium text-gray-900">{{ $stockMovement->reference ?: '—' }}</p>
            </div>
            @if($stockMovement->notes)
                <div class="sm:col-span-2 lg:col-span-4">
                    <p class="text-xs text-gray-400 mb-0.5">Notas</p>
                    <p class="text-gray-700">{{ $stockMovement->notes }}</p>
                </div>
            @endif
            @if($stockMovement->dispatch_notes && !in_array($stockMovement->status, ['rejected','cancelled']))
                <div class="sm:col-span-2 lg:col-span-4">
                    <p class="text-xs text-gray-400 mb-0.5">Justificación del almacén origen</p>
                    <p class="text-gray-700 italic">{{ $stockMovement->dispatch_notes }}</p>
                </div>
            @endif
        </div>
    </div>

    {{-- ── PRODUCTS: solicitados vs enviados vs recibidos ─────────────────────── --}}
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-sm font-medium text-gray-700">
                Productos
                <span class="ml-2 text-xs font-normal text-gray-400">{{ $stockMovement->items->count() }} producto(s)</span>
            </h2>
            @if($stockMovement->dispatch_is_final && in_array($stockMovement->status, ['in_transit','partially_received']))
                <span class="text-xs text-amber-700 bg-amber-50 border border-amber-100 px-2 py-0.5 rounded-full">
                    Envío final — no se esperan más despachos
                </span>
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm" style="min-width:600px">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Producto</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500 w-28">Solicitado</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500 w-28">Despachado</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500 w-28">Recibido</th>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-28">Fec. recepción</th>
                        <th class="px-4 py-2.5 w-24"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($stockMovement->items as $item)
                        @php
                            $dispatched = $item->dispatched_quantity ?? $item->quantity;
                            $received   = $item->received_quantity;
                            $isFullDispatched = (float)$dispatched >= (float)$item->quantity;
                            $isFullReceived   = $received !== null && (float)$received >= (float)$dispatched;
                        @endphp
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900">{{ $item->product?->name ?? '—' }}</p>
                                @if($item->product?->sku)
                                    <p class="text-xs text-gray-400 font-mono">{{ $item->product->sku }}</p>
                                @endif
                                @if($item->is_late_addition)
                                    <span class="inline-flex items-center gap-1 text-[10px] text-amber-700 bg-amber-50 border border-amber-100 px-1.5 py-0.5 rounded-full mt-0.5">
                                        Adición tardía · {{ $item->added_at?->format('d/m H:i') }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right text-gray-700">
                                {{ number_format($item->quantity, 2) }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if($item->dispatched_quantity !== null)
                                    <span @class([
                                        'font-medium',
                                        'text-emerald-700' => $isFullDispatched,
                                        'text-amber-600'   => !$isFullDispatched,
                                    ])>{{ number_format($dispatched, 2) }}</span>
                                    @if(!$isFullDispatched)
                                        <p class="text-[10px] text-amber-500">de {{ number_format($item->quantity, 2) }}</p>
                                    @endif
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if($received !== null)
                                    <span @class([
                                        'font-medium',
                                        'text-emerald-700' => $isFullReceived,
                                        'text-orange-600'  => !$isFullReceived,
                                    ])>{{ number_format($received, 2) }}</span>
                                    @if(!$isFullReceived)
                                        <p class="text-[10px] text-orange-400">de {{ number_format($dispatched, 2) }}</p>
                                    @endif
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500">
                                {{ $item->received_at?->format('d/m/Y') ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if($received !== null && (float)$received >= (float)$dispatched)
                                    <span class="inline-flex items-center gap-1 text-[10px] font-medium text-emerald-700 bg-emerald-50 border border-emerald-100 px-2 py-0.5 rounded-full">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        Completo
                                    </span>
                                @elseif($received !== null)
                                    <span class="text-[10px] font-medium text-orange-600 bg-orange-50 border border-orange-100 px-2 py-0.5 rounded-full">Parcial</span>
                                @elseif($item->dispatched_quantity !== null)
                                    <span class="text-[10px] text-gray-400 bg-gray-50 border border-gray-100 px-2 py-0.5 rounded-full">En tránsito</span>
                                @else
                                    <span class="text-[10px] text-blue-500 bg-blue-50 border border-blue-100 px-2 py-0.5 rounded-full">Pendiente</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                {{-- Totals row --}}
                @php
                    $totalReq  = $stockMovement->items->sum('quantity');
                    $totalDisp = $stockMovement->items->sum(fn($i) => $i->dispatched_quantity ?? $i->quantity);
                    $totalRecv = $stockMovement->items->whereNotNull('received_quantity')->sum('received_quantity');
                @endphp
                <tfoot>
                    <tr class="bg-gray-50 border-t border-gray-200 font-medium text-sm">
                        <td class="px-4 py-2.5 text-gray-600">Totales</td>
                        <td class="px-4 py-2.5 text-right text-gray-700">{{ number_format($totalReq, 2) }}</td>
                        <td class="px-4 py-2.5 text-right text-gray-700">
                            {{ $stockMovement->items->whereNotNull('dispatched_quantity')->count() ? number_format($totalDisp, 2) : '—' }}
                        </td>
                        <td class="px-4 py-2.5 text-right text-gray-700">
                            {{ $stockMovement->items->whereNotNull('received_quantity')->count() ? number_format($totalRecv, 2) : '—' }}
                        </td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- ── TIMELINE ─────────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-5">
        <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-5">Historial de acciones</h2>

        @if($stockMovement->events->isEmpty())
            <p class="text-sm text-gray-400 text-center py-4">Sin eventos registrados.</p>
        @else
            <ol class="relative border-l border-gray-200 ml-3 space-y-6">
                @foreach($stockMovement->events as $event)
                    @php
                        $color  = \App\Models\StockMovementEvent::COLORS[$event->action]  ?? 'gray';
                        $label  = \App\Models\StockMovementEvent::LABELS[$event->action]  ?? $event->action;
                        $dotBg  = match($color) {
                            'emerald' => 'bg-emerald-500',
                            'indigo'  => 'bg-indigo-500',
                            'amber'   => 'bg-amber-500',
                            'orange'  => 'bg-orange-500',
                            'red'     => 'bg-red-500',
                            'blue'    => 'bg-blue-500',
                            default   => 'bg-gray-400',
                        };
                    @endphp
                    <li class="ml-6">
                        <span class="absolute -left-[9px] flex items-center justify-center w-4 h-4 rounded-full ring-4 ring-white {{ $dotBg }}"></span>
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $label }}</p>
                                @if($event->notes)
                                    <p class="text-xs text-gray-500 mt-0.5 italic">"{{ $event->notes }}"</p>
                                @endif
                                @if(!empty($event->data['items']))
                                    <div class="mt-2 space-y-0.5">
                                        @foreach($event->data['items'] as $ei)
                                            <p class="text-xs text-gray-500">
                                                • {{ $ei['product'] }}:
                                                <span class="text-gray-700 font-medium">{{ number_format($ei['dispatched'], 2) }}</span>
                                                @if(isset($ei['requested']) && $ei['requested'] != $ei['dispatched'])
                                                    <span class="text-gray-400"> de {{ number_format($ei['requested'], 2) }} solicitados</span>
                                                @endif
                                            </p>
                                        @endforeach
                                    </div>
                                @endif
                                <p class="text-xs text-gray-400 mt-1">
                                    Por <strong>{{ $event->user?->name ?? 'Sistema' }}</strong>
                                </p>
                            </div>
                            <time class="text-xs text-gray-400 whitespace-nowrap flex-shrink-0">
                                {{ $event->created_at->format('d/m/Y H:i') }}
                            </time>
                        </div>
                    </li>
                @endforeach
            </ol>
        @endif
    </div>

</div>

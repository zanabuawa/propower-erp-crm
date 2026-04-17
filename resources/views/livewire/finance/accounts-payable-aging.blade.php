<div>
    <x-page-header title="Antigüedad de saldos por pagar" description="CxP vencidas y por vencer agrupadas por tramo de mora">
    </x-page-header>

    <x-alert />

    {{-- ── Tarjetas de resumen por bucket ──────────────────────────────── --}}
    @php
        $buckets = [
            'current' => ['label' => 'Al corriente',  'color' => 'text-green-700',  'bg' => 'bg-green-50',  'border' => 'border-green-200'],
            '1-30'    => ['label' => '1 – 30 días',   'color' => 'text-yellow-700', 'bg' => 'bg-yellow-50', 'border' => 'border-yellow-200'],
            '31-60'   => ['label' => '31 – 60 días',  'color' => 'text-orange-700', 'bg' => 'bg-orange-50', 'border' => 'border-orange-200'],
            '61-90'   => ['label' => '61 – 90 días',  'color' => 'text-red-600',    'bg' => 'bg-red-50',    'border' => 'border-red-200'],
            '90+'     => ['label' => 'Más de 90 días','color' => 'text-red-800',    'bg' => 'bg-red-100',   'border' => 'border-red-300'],
        ];
    @endphp

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-5">

        {{-- Total general --}}
        <button wire:click="$set('filterBucket', '')"
                class="rounded-xl border p-4 shadow-sm text-left transition
                    {{ $filterBucket === '' ? 'bg-indigo-600 border-indigo-600 text-white' : 'bg-white border-gray-200 hover:border-indigo-300' }}">
            <p class="text-xs font-medium {{ $filterBucket === '' ? 'text-indigo-100' : 'text-gray-500' }} uppercase tracking-wide mb-1">Total CxP</p>
            <p class="text-lg font-bold">${{ number_format($totalBalance, 0) }}</p>
            <p class="text-xs {{ $filterBucket === '' ? 'text-indigo-200' : 'text-gray-400' }}">
                {{ collect($summary)->sum('count') }} facturas
            </p>
        </button>

        {{-- Por bucket --}}
        @foreach($buckets as $key => $meta)
        <button wire:click="$set('filterBucket', '{{ $key }}')"
                class="rounded-xl border p-4 shadow-sm text-left transition
                    {{ $filterBucket === $key
                        ? 'ring-2 ring-offset-1 ring-indigo-400 ' . $meta['bg'] . ' ' . $meta['border']
                        : 'bg-white border-gray-200 hover:' . $meta['border'] }}">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">{{ $meta['label'] }}</p>
            <p class="text-lg font-bold {{ $meta['color'] }}">${{ number_format($summary[$key]['amount'], 0) }}</p>
            <p class="text-xs text-gray-400">{{ $summary[$key]['count'] }} facturas</p>
        </button>
        @endforeach
    </div>

    {{-- Filtros --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-5">
        <div class="relative sm:col-span-2">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input wire:model.live.debounce.300ms="search" type="text"
                   placeholder="Folio o proveedor..."
                   class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
        </div>
        <select wire:model.live="filterSupplier" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
            <option value="">Todos los proveedores</option>
            @foreach($suppliers as $s)
                <option value="{{ $s->id }}">{{ $s->name }}</option>
            @endforeach
        </select>
    </div>

    {{-- Tabla --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[800px]">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Proveedor</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden md:table-cell">Folio</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden lg:table-cell">OC</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Vencimiento</th>
                        <th class="text-center px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Días mora</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Total</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Saldo</th>
                        <th class="text-center px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Tramo</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($paged as $row)
                    @php
                        $inv    = $row['invoice'];
                        $days   = $row['days_overdue'];
                        $bucket = $row['bucket'];
                        $rowBg  = match($bucket) {
                            '90+'   => 'bg-red-50/50',
                            '61-90' => 'bg-orange-50/40',
                            '31-60' => 'bg-yellow-50/40',
                            default => '',
                        };
                    @endphp
                    <tr class="hover:bg-gray-50 transition {{ $rowBg }}">
                        <td class="px-5 py-3 font-medium text-gray-900 max-w-[160px] truncate">{{ $inv->supplier->name ?? '—' }}</td>
                        <td class="px-5 py-3 hidden md:table-cell font-mono text-xs text-gray-500">{{ $inv->folio }}</td>
                        <td class="px-5 py-3 hidden lg:table-cell">
                            @if($inv->order)
                                <a wire:navigate href="{{ route('purchases.orders.show', $inv->order) }}"
                                   class="text-xs font-mono text-indigo-500 hover:underline">{{ $inv->order->folio }}</a>
                            @else
                                <span class="text-xs text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-xs {{ $days > 0 ? 'text-red-600 font-semibold' : 'text-gray-600' }}">
                            {{ $inv->due_at?->format('d/m/Y') ?? '—' }}
                        </td>
                        <td class="px-5 py-3 text-center">
                            @if($days > 0)
                                <span class="inline-flex items-center justify-center w-12 h-6 rounded-full text-xs font-bold
                                    {{ $days > 90 ? 'bg-red-100 text-red-700' : ($days > 60 ? 'bg-orange-100 text-orange-700' : ($days > 30 ? 'bg-yellow-100 text-yellow-700' : 'bg-amber-50 text-amber-600')) }}">
                                    {{ $days }}d
                                </span>
                            @else
                                <span class="text-xs text-green-600 font-medium">Vigente</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right font-semibold text-gray-900">${{ number_format($inv->total, 2) }}</td>
                        <td class="px-5 py-3 text-right font-medium {{ $row['balance'] > 0 ? 'text-red-600' : 'text-gray-400' }}">
                            ${{ number_format($row['balance'], 2) }}
                        </td>
                        <td class="px-5 py-3 text-center">
                            @php
                                $bMeta = $buckets[$bucket];
                            @endphp
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $bMeta['bg'] }} {{ $bMeta['color'] }} border {{ $bMeta['border'] }}">
                                {{ $bMeta['label'] }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <a wire:navigate href="{{ route('purchases.invoices.show', $inv) }}"
                               class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Ver →</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-5 py-10 text-center text-gray-400 text-sm">No hay facturas pendientes de pago.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación manual --}}
        @if($paged->hasPages())
        <div class="px-5 py-3 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
            <span>{{ $paged->firstItem() }}–{{ $paged->lastItem() }} de {{ $paged->total() }} facturas</span>
            <div class="flex gap-1">
                @if($paged->onFirstPage())
                    <span class="px-3 py-1 rounded border border-gray-200 text-gray-300 cursor-not-allowed">‹</span>
                @else
                    <button wire:click="setPage({{ $paged->currentPage() - 1 }})"
                            class="px-3 py-1 rounded border border-gray-200 hover:bg-gray-50 transition">‹</button>
                @endif

                @foreach($paged->getUrlRange(max(1, $paged->currentPage()-2), min($paged->lastPage(), $paged->currentPage()+2)) as $p => $url)
                    <button wire:click="setPage({{ $p }})"
                            class="px-3 py-1 rounded border transition
                                {{ $p === $paged->currentPage() ? 'bg-indigo-600 border-indigo-600 text-white' : 'border-gray-200 hover:bg-gray-50' }}">
                        {{ $p }}
                    </button>
                @endforeach

                @if($paged->hasMorePages())
                    <button wire:click="setPage({{ $paged->currentPage() + 1 }})"
                            class="px-3 py-1 rounded border border-gray-200 hover:bg-gray-50 transition">›</button>
                @else
                    <span class="px-3 py-1 rounded border border-gray-200 text-gray-300 cursor-not-allowed">›</span>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

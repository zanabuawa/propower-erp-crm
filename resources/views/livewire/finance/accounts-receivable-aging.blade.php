<div>
    <x-page-header title="Antigüedad de saldos" description="Cuentas por cobrar pendientes por vencimiento">
        <x-slot:actions>
            <div class="flex items-center gap-2">
                <label class="text-xs text-gray-500 whitespace-nowrap">Fecha de corte:</label>
                <input wire:model.live="filterDateAt" type="date"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
            </div>
        </x-slot:actions>
    </x-page-header>

    <x-alert />

    {{-- ── Tarjetas de resumen por bucket ──────────────────────────────── --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-5">
        {{-- Total general --}}
        <div wire:click="$set('filterBucket','')"
            class="col-span-2 sm:col-span-3 lg:col-span-1 bg-white rounded-xl border border-gray-200 p-4 shadow-sm cursor-pointer
                   {{ $filterBucket === '' ? 'ring-2 ring-indigo-400' : 'hover:border-indigo-300' }}">
            <div class="text-xs text-gray-500 font-medium mb-1">Total por cobrar</div>
            <div class="text-lg font-bold text-gray-900">${{ number_format($total, 2) }}</div>
            <div class="text-xs text-gray-400 mt-0.5">Todos los vencimientos</div>
        </div>

        {{-- Vigente --}}
        <div wire:click="$set('filterBucket','current')"
            class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm cursor-pointer
                   {{ $filterBucket === 'current' ? 'ring-2 ring-green-400' : 'hover:border-green-300' }}">
            <div class="text-xs text-gray-500 font-medium mb-1">Vigente</div>
            <div class="text-base font-bold text-green-700">${{ number_format($buckets['current'], 2) }}</div>
            @if($total > 0)
            <div class="text-xs text-gray-400 mt-0.5">{{ number_format($buckets['current'] / $total * 100, 1) }}%</div>
            @endif
        </div>

        {{-- 1-30 --}}
        <div wire:click="$set('filterBucket','1-30')"
            class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm cursor-pointer
                   {{ $filterBucket === '1-30' ? 'ring-2 ring-yellow-400' : 'hover:border-yellow-300' }}">
            <div class="text-xs text-gray-500 font-medium mb-1">1 – 30 días</div>
            <div class="text-base font-bold text-yellow-600">${{ number_format($buckets['1-30'], 2) }}</div>
            @if($total > 0)
            <div class="text-xs text-gray-400 mt-0.5">{{ number_format($buckets['1-30'] / $total * 100, 1) }}%</div>
            @endif
        </div>

        {{-- 31-60 --}}
        <div wire:click="$set('filterBucket','31-60')"
            class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm cursor-pointer
                   {{ $filterBucket === '31-60' ? 'ring-2 ring-orange-400' : 'hover:border-orange-300' }}">
            <div class="text-xs text-gray-500 font-medium mb-1">31 – 60 días</div>
            <div class="text-base font-bold text-orange-600">${{ number_format($buckets['31-60'], 2) }}</div>
            @if($total > 0)
            <div class="text-xs text-gray-400 mt-0.5">{{ number_format($buckets['31-60'] / $total * 100, 1) }}%</div>
            @endif
        </div>

        {{-- 61-90 --}}
        <div wire:click="$set('filterBucket','61-90')"
            class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm cursor-pointer
                   {{ $filterBucket === '61-90' ? 'ring-2 ring-red-300' : 'hover:border-red-300' }}">
            <div class="text-xs text-gray-500 font-medium mb-1">61 – 90 días</div>
            <div class="text-base font-bold text-red-500">${{ number_format($buckets['61-90'], 2) }}</div>
            @if($total > 0)
            <div class="text-xs text-gray-400 mt-0.5">{{ number_format($buckets['61-90'] / $total * 100, 1) }}%</div>
            @endif
        </div>

        {{-- +90 --}}
        <div wire:click="$set('filterBucket','90+')"
            class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm cursor-pointer
                   {{ $filterBucket === '90+' ? 'ring-2 ring-red-600' : 'hover:border-red-400' }}">
            <div class="text-xs text-gray-500 font-medium mb-1">+90 días</div>
            <div class="text-base font-bold text-red-700">${{ number_format($buckets['90+'], 2) }}</div>
            @if($total > 0)
            <div class="text-xs text-gray-400 mt-0.5">{{ number_format($buckets['90+'] / $total * 100, 1) }}%</div>
            @endif
        </div>
    </div>

    {{-- ── Filtros ──────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-5">
        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input wire:model.live.debounce.300ms="search" type="text"
                placeholder="Buscar por folio o cliente..."
                class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
        </div>
        <select wire:model.live="filterCustomer" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
            <option value="">Todos los clientes</option>
            @foreach($customers as $c)
                <option value="{{ $c->id }}">{{ $c->name }}</option>
            @endforeach
        </select>
        <select wire:model.live="filterBucket" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
            <option value="">Todos los vencimientos</option>
            <option value="current">Vigente</option>
            <option value="1-30">1 – 30 días</option>
            <option value="31-60">31 – 60 días</option>
            <option value="61-90">61 – 90 días</option>
            <option value="90+">Más de 90 días</option>
        </select>
    </div>

    {{-- ── Tabla de facturas ────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[860px]">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Cliente</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Folio</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden md:table-cell">Emisión</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Vencimiento</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden lg:table-cell">Total</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden lg:table-cell">Pagado</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Saldo</th>
                        <th class="text-center px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Antigüedad</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @php $grandBalance = 0; @endphp
                    @forelse($invoices as $inv)
                    @php
                        $balance     = $inv->total - $inv->paid_amount;
                        $grandBalance += $balance;
                        $daysOverdue = $inv->due_at
                            ? (int) $cutoff->copy()->startOfDay()->diffInDays($inv->due_at, false) * -1
                            : 0;
                        $bucket = \App\Livewire\Finance\AccountsReceivableAging::bucket($daysOverdue);

                        $bucketLabel = match($bucket) {
                            'current' => 'Vigente',
                            '1-30'    => '1-30 días',
                            '31-60'   => '31-60 días',
                            '61-90'   => '61-90 días',
                            '90+'     => '+90 días',
                        };
                        $bucketColor = match($bucket) {
                            'current' => 'bg-green-100 text-green-700',
                            '1-30'    => 'bg-yellow-100 text-yellow-700',
                            '31-60'   => 'bg-orange-100 text-orange-700',
                            '61-90'   => 'bg-red-100 text-red-600',
                            '90+'     => 'bg-red-200 text-red-800',
                        };
                    @endphp
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-5 py-3">
                            <div class="font-medium text-gray-900 truncate max-w-[180px]">{{ $inv->customer->name ?? '—' }}</div>
                        </td>
                        <td class="px-5 py-3 font-mono text-xs text-gray-500">
                            <a wire:navigate href="{{ route('sales.invoices.show', $inv) }}"
                               class="hover:text-indigo-600 transition">{{ $inv->folio }}</a>
                        </td>
                        <td class="px-5 py-3 hidden md:table-cell text-gray-500 text-xs">
                            {{ $inv->issued_at->format('d/m/Y') }}
                        </td>
                        <td class="px-5 py-3 text-xs {{ $daysOverdue > 0 ? 'text-red-600 font-medium' : 'text-gray-600' }}">
                            {{ $inv->due_at ? $inv->due_at->format('d/m/Y') : '—' }}
                            @if($daysOverdue > 0)
                                <div class="text-red-400 font-normal">{{ $daysOverdue }} d vencida</div>
                            @elseif($inv->due_at)
                                <div class="text-green-500 font-normal">{{ abs($daysOverdue) }} d restantes</div>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right hidden lg:table-cell text-gray-600">
                            ${{ number_format($inv->total, 2) }}
                        </td>
                        <td class="px-5 py-3 text-right hidden lg:table-cell text-gray-500">
                            ${{ number_format($inv->paid_amount, 2) }}
                        </td>
                        <td class="px-5 py-3 text-right font-semibold text-gray-900">
                            ${{ number_format($balance, 2) }}
                        </td>
                        <td class="px-5 py-3 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $bucketColor }}">
                                {{ $bucketLabel }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <a wire:navigate href="{{ route('sales.invoices.show', $inv) }}"
                               class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">Ver</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-5 py-10 text-center text-gray-400 text-sm">
                            No hay facturas pendientes de cobro con los filtros seleccionados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($invoices->count())
                <tfoot>
                    <tr class="border-t-2 border-gray-200 bg-gray-50 font-semibold">
                        <td colspan="6" class="px-5 py-3 text-right text-xs text-gray-600 uppercase tracking-wide hidden lg:table-cell">
                            Total en esta página
                        </td>
                        <td colspan="3" class="px-5 py-3 text-right hidden lg:table-cell text-gray-900">
                            ${{ number_format($grandBalance, 2) }}
                        </td>
                        <td colspan="5" class="px-5 py-3 text-right text-xs text-gray-600 uppercase tracking-wide lg:hidden">
                            Total: ${{ number_format($grandBalance, 2) }}
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
        @if($invoices->hasPages())
        <div class="px-5 py-3 border-t border-gray-100">{{ $invoices->links('vendor.pagination.tailwind') }}</div>
        @endif
    </div>
</div>

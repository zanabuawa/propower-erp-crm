<div>
    <x-page-header title="Facturas de proveedor" description="Registro y control de cuentas por pagar">
        <x-slot:actions>
            @can('create purchases')
            <a wire:navigate href="{{ route('purchases.invoices.create') }}"
                class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Registrar factura
            </a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <x-alert />

    {{-- KPIs --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Por pagar</p>
            <p class="text-xl font-bold text-gray-900">${{ number_format($kpis['total_pending'], 0) }}</p>
            <p class="text-xs text-gray-400">{{ $kpis['pending'] }} facturas pendientes</p>
        </div>
        <div class="bg-white rounded-xl border {{ $kpis['overdue'] > 0 ? 'border-l-4 border-l-red-400' : 'border-gray-200' }} p-4 shadow-sm">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Vencidas</p>
            <p class="text-xl font-bold {{ $kpis['overdue'] > 0 ? 'text-red-600' : 'text-gray-400' }}">{{ $kpis['overdue'] }}</p>
            <p class="text-xs text-gray-400">requieren pago urgente</p>
        </div>
        <div class="bg-white rounded-xl border {{ $kpis['discrepancy'] > 0 ? 'border-l-4 border-l-orange-400' : 'border-gray-200' }} p-4 shadow-sm">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Con discrepancias</p>
            <p class="text-xl font-bold {{ $kpis['discrepancy'] > 0 ? 'text-orange-600' : 'text-gray-400' }}">{{ $kpis['discrepancy'] }}</p>
            <p class="text-xs text-gray-400">cotejo 3-way fallido</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">3-Way Match</p>
            <p class="text-xs text-gray-500 mt-2 leading-relaxed">
                OC → Recepción → Factura validados automáticamente al registrar.
            </p>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 mb-5">
        <div class="relative sm:col-span-2">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input wire:model.live.debounce.300ms="search" type="text"
                placeholder="Folio, N° factura proveedor o proveedor..."
                class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
        </div>
        <select wire:model.live="filterSupplier" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
            <option value="">Todos los proveedores</option>
            @foreach($suppliers as $s)
                <option value="{{ $s->id }}">{{ $s->name }}</option>
            @endforeach
        </select>
        <select wire:model.live="filterStatus" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
            <option value="">Todos los estados</option>
            @foreach(\App\Models\PurchaseInvoice::STATUS as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </select>
        <select wire:model.live="filterMatch" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
            <option value="">Todos los cotejos</option>
            @foreach(\App\Models\PurchaseInvoice::MATCH_STATUS as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </select>
    </div>

    {{-- Tabla --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[900px]">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Folio</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Proveedor</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden md:table-cell">N° Fact. Prov.</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden lg:table-cell">OC</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Vencimiento</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Total</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden sm:table-cell">Saldo</th>
                        <th class="text-center px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">3-Way</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Estado</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($invoices as $inv)
                    @php
                        $isOverdue = $inv->due_at && $inv->due_at->isPast() && ! in_array($inv->status, ['paid','cancelled']);
                        $balance   = $inv->total - $inv->paid_amount;
                    @endphp
                    <tr class="hover:bg-gray-50 transition {{ $isOverdue ? 'bg-red-50/40' : '' }}">
                        <td class="px-5 py-3 font-mono text-xs text-gray-600">{{ $inv->folio }}</td>
                        <td class="px-5 py-3 font-medium text-gray-900 max-w-[160px] truncate">{{ $inv->supplier->name ?? '—' }}</td>
                        <td class="px-5 py-3 hidden md:table-cell text-gray-500 text-xs">{{ $inv->supplier_invoice_number ?? '—' }}</td>
                        <td class="px-5 py-3 hidden lg:table-cell">
                            @if($inv->order)
                                <a wire:navigate href="{{ route('purchases.orders.show', $inv->order) }}"
                                   class="text-xs font-mono text-indigo-500 hover:underline">{{ $inv->order->folio }}</a>
                            @else
                                <span class="text-xs text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-xs {{ $isOverdue ? 'text-red-600 font-semibold' : 'text-gray-600' }}">
                            {{ $inv->due_at?->format('d/m/Y') ?? '—' }}
                            @if($isOverdue)<div class="text-red-400 font-normal">{{ $inv->due_at->diffForHumans() }}</div>@endif
                        </td>
                        <td class="px-5 py-3 text-right font-semibold text-gray-900">${{ number_format($inv->total, 2) }}</td>
                        <td class="px-5 py-3 text-right hidden sm:table-cell {{ $balance > 0 ? 'text-red-600 font-medium' : 'text-gray-400' }}">
                            ${{ number_format($balance, 2) }}
                        </td>
                        <td class="px-5 py-3 text-center">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                                {{ \App\Models\PurchaseInvoice::MATCH_COLORS[$inv->match_status] ?? 'bg-gray-100 text-gray-500' }}">
                                {{ \App\Models\PurchaseInvoice::MATCH_STATUS[$inv->match_status] ?? $inv->match_status }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                                {{ \App\Models\PurchaseInvoice::STATUS_COLORS[$inv->status] ?? 'bg-gray-100 text-gray-500' }}">
                                {{ \App\Models\PurchaseInvoice::STATUS[$inv->status] ?? $inv->status }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <a wire:navigate href="{{ route('purchases.invoices.show', $inv) }}"
                               class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Ver →</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="px-5 py-10 text-center text-gray-400 text-sm">No se encontraron facturas de proveedor.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($invoices->hasPages())
        <div class="px-5 py-3 border-t border-gray-100">{{ $invoices->links() }}</div>
        @endif
    </div>
</div>

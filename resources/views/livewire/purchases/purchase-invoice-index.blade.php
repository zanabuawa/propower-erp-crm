<div>
    {{-- Header --}}
    <x-page-header title="Facturas de proveedor" description="Registro y control de cuentas por pagar">
        <x-slot:actions>
            @can('create purchases')
            <a wire:navigate href="{{ route('purchases.invoices.create') }}"
                class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-medium px-5 py-2 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:scale-105">
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
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Por pagar</p>
            <div class="flex items-baseline gap-1">
                <span class="text-2xl font-bold text-gray-900">${{ number_format($kpis['total_pending'], 0) }}</span>
            </div>
            <p class="text-[10px] text-gray-400 mt-1 font-medium">{{ $kpis['pending'] }} facturas pendientes</p>
        </div>
        <div class="bg-white rounded-2xl border {{ $kpis['overdue'] > 0 ? 'border-red-200 ring-1 ring-red-50' : 'border-gray-200' }} p-5 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Vencidas</p>
            <div class="flex items-center gap-2">
                <span class="text-2xl font-bold {{ $kpis['overdue'] > 0 ? 'text-red-600' : 'text-gray-400' }}">{{ $kpis['overdue'] }}</span>
                @if($kpis['overdue'] > 0)
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                @endif
            </div>
            <p class="text-[10px] text-gray-400 mt-1 font-medium">Requieren pago urgente</p>
        </div>
        <div class="bg-white rounded-2xl border {{ $kpis['discrepancy'] > 0 ? 'border-amber-200 ring-1 ring-amber-50' : 'border-gray-200' }} p-5 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Con discrepancias</p>
            <div class="flex items-center gap-2">
                <span class="text-2xl font-bold {{ $kpis['discrepancy'] > 0 ? 'text-amber-600' : 'text-gray-400' }}">{{ $kpis['discrepancy'] }}</span>
                @if($kpis['discrepancy'] > 0)
                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                @endif
            </div>
            <p class="text-[10px] text-gray-400 mt-1 font-medium">Cotejo 3-way fallido</p>
        </div>
        <div class="bg-indigo-600 rounded-2xl p-5 shadow-lg shadow-indigo-200 hidden lg:block">
            <p class="text-xs font-bold text-indigo-100 uppercase tracking-wider mb-2">3-Way Match</p>
            <p class="text-[11px] text-white/90 leading-relaxed font-medium">
                OC → Recepción → Factura validados automáticamente al registrar el documento.
            </p>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-5 mb-6 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div class="md:col-span-2 relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input wire:model.live.debounce.300ms="search" type="text"
                    placeholder="Folio, N° factura o proveedor..."
                    class="w-full pl-9 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all">
            </div>
            
            <div class="relative">
                <select wire:model.live="filterSupplier"
                    class="w-full pl-4 pr-10 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all hover:bg-gray-100 appearance-none">
                    <option value="">Todos los proveedores</option>
                    @foreach($suppliers as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
                <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>

            <div class="relative">
                <select wire:model.live="filterStatus"
                    class="w-full pl-4 pr-10 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all hover:bg-gray-100 appearance-none">
                    <option value="">Todos los estados</option>
                    @foreach(\App\Models\PurchaseInvoice::STATUS as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>

            <div class="relative">
                <select wire:model.live="filterMatch"
                    class="w-full pl-4 pr-10 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all hover:bg-gray-100 appearance-none">
                    <option value="">Todos los cotejos</option>
                    @foreach(\App\Models\PurchaseInvoice::MATCH_STATUS as $key => $label)
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
        @forelse($invoices as $inv)
            @php
                $isOverdue = $inv->due_at && $inv->due_at->isPast() && ! in_array($inv->status, ['paid','cancelled']);
                $balance   = $inv->total - $inv->paid_amount;
            @endphp
            <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm {{ $isOverdue ? 'border-l-4 border-l-red-500' : '' }}">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <span class="font-mono text-xs font-bold text-indigo-600">{{ $inv->folio }}</span>
                        <h3 class="font-bold text-gray-900 mt-1 line-clamp-1">{{ $inv->supplier->name ?? '—' }}</h3>
                        <p class="text-[10px] text-gray-500 mt-0.5">N° Fact: {{ $inv->supplier_invoice_number ?? '—' }}</p>
                    </div>
                    <x-status-badge :status="$inv->status" :label="\App\Models\PurchaseInvoice::STATUS[$inv->status] ?? $inv->status" />
                </div>

                <div class="grid grid-cols-2 gap-3 mb-4 text-sm">
                    <div class="bg-gray-50 rounded-xl p-3">
                        <p class="text-xs text-gray-500 mb-1">Total</p>
                        <p class="font-bold text-gray-900">${{ number_format($inv->total, 2) }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3">
                        <p class="text-xs text-gray-500 mb-1">Saldo</p>
                        <p class="font-bold {{ $balance > 0 ? 'text-red-600' : 'text-emerald-600' }}">${{ number_format($balance, 2) }}</p>
                    </div>
                    <div class="col-span-2 bg-gray-50 rounded-xl p-3 flex justify-between items-center">
                        <div>
                            <p class="text-xs text-gray-500 mb-0.5">Vencimiento</p>
                            <p class="text-xs font-semibold {{ $isOverdue ? 'text-red-600' : 'text-gray-700' }}">
                                {{ $inv->due_at?->format('d/m/Y') ?? '—' }}
                                @if($isOverdue) <span class="ml-1 text-[10px] text-red-400">({{ $inv->due_at->diffForHumans() }})</span> @endif
                            </p>
                        </div>
                        <span class="inline-flex px-2 py-0.5 rounded-lg text-[10px] font-bold {{ \App\Models\PurchaseInvoice::MATCH_COLORS[$inv->match_status] ?? 'bg-gray-100 text-gray-500' }}">
                            Match: {{ \App\Models\PurchaseInvoice::MATCH_STATUS[$inv->match_status] ?? $inv->match_status }}
                        </span>
                    </div>
                </div>

                <a wire:navigate href="{{ route('purchases.invoices.show', $inv) }}"
                    class="flex items-center justify-center w-full py-2.5 bg-indigo-50 text-indigo-600 rounded-xl text-sm font-bold hover:bg-indigo-600 hover:text-white transition-all">
                    Ver factura
                </a>
            </div>
        @empty
        <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center shadow-sm">
            <x-empty-state message="No se encontraron facturas." />
        </div>
        @endforelse
        {{ $invoices->links('vendor.pagination.tailwind') }}
        </div>

        {{-- VISTA ESCRITORIO --}}    <div class="hidden lg:block bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200 text-left">
                    <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Folio / Proveedor</th>
                    <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center">N° Factura / OC</th>
                    <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center">Vencimiento</th>
                    <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider text-right">Total / Saldo</th>
                    <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center">3-Way Match</th>
                    <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center">Estado</th>
                    <th class="px-6 py-4"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($invoices as $inv)
                    @php
                        $isOverdue = $inv->due_at && $inv->due_at->isPast() && ! in_array($inv->status, ['paid','cancelled']);
                        $balance   = $inv->total - $inv->paid_amount;
                    @endphp
                    <tr class="hover:bg-gray-50 transition-colors {{ $isOverdue ? 'bg-red-50/20' : '' }}">
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="font-mono text-xs font-bold text-indigo-600">{{ $inv->folio }}</span>
                                <span class="font-semibold text-gray-900 line-clamp-1">{{ $inv->supplier->name ?? '—' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex flex-col gap-1">
                                <span class="text-xs text-gray-600 font-medium">{{ $inv->supplier_invoice_number ?? '—' }}</span>
                                @if($inv->order)
                                    <a wire:navigate href="{{ route('purchases.orders.show', $inv->order) }}"
                                       class="text-[10px] font-mono text-indigo-500 hover:underline">{{ $inv->order->folio }}</a>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex flex-col">
                                <span class="text-xs {{ $isOverdue ? 'text-red-600 font-bold' : 'text-gray-700' }}">
                                    {{ $inv->due_at?->format('d/m/Y') ?? '—' }}
                                </span>
                                @if($isOverdue)
                                    <span class="text-[10px] text-red-400 font-medium">{{ $inv->due_at->diffForHumans() }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-gray-900">${{ number_format($inv->total, 2) }}</span>
                                <span class="text-[10px] {{ $balance > 0 ? 'text-red-500 font-bold' : 'text-emerald-600 font-medium' }}">
                                    Saldo: ${{ number_format($balance, 2) }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex px-2 py-0.5 rounded-lg text-[10px] font-bold {{ \App\Models\PurchaseInvoice::MATCH_COLORS[$inv->match_status] ?? 'bg-gray-100 text-gray-500' }}">
                                {{ \App\Models\PurchaseInvoice::MATCH_STATUS[$inv->match_status] ?? $inv->match_status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <x-status-badge :status="$inv->status" :label="\App\Models\PurchaseInvoice::STATUS[$inv->status] ?? $inv->status" />
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a wire:navigate href="{{ route('purchases.invoices.show', $inv) }}"
                                class="inline-flex items-center justify-center p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <x-empty-state message="No se encontraron facturas de proveedor." />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($invoices->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>
</div>

iv>


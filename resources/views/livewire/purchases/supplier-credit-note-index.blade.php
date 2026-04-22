<div>
    {{-- Header --}}
    <x-page-header title="Notas de crédito de proveedor" description="Devoluciones y ajustes recibidos de proveedores">
        <x-slot:actions>
            @can('create purchases')
            <a wire:navigate href="{{ route('purchases.credit-notes.create') }}"
                class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-medium px-5 py-2 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:scale-105">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Registrar NC
            </a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <x-alert />

    {{-- KPIs --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Por aplicar</p>
            <p class="text-2xl font-bold text-gray-900">${{ number_format($kpis['total_pending'], 0) }}</p>
            <p class="text-[10px] text-gray-400 mt-1 font-medium">{{ $kpis['draft'] }} notas en borrador</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Aplicadas (Mes)</p>
            <p class="text-2xl font-bold text-emerald-600">${{ number_format($kpis['applied_month'], 0) }}</p>
            <p class="text-[10px] text-gray-400 mt-1 font-medium">Reducción de pasivos</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Total registros</p>
            <p class="text-2xl font-bold text-gray-900">{{ $kpis['total_count'] }}</p>
            <p class="text-[10px] text-gray-400 mt-1 font-medium">Historial acumulado</p>
        </div>
        <div class="bg-indigo-600 rounded-2xl p-5 shadow-lg shadow-indigo-200 hidden lg:block">
            <p class="text-xs font-bold text-indigo-100 uppercase tracking-wider mb-2">¿Qué es una NC?</p>
            <p class="text-[11px] text-white/90 leading-relaxed font-medium">
                Documento que reduce el saldo de una factura por devolución, ajuste de precio o errores de facturación.
            </p>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-5 mb-6 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="md:col-span-2 relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input wire:model.live.debounce.300ms="search" type="text"
                    placeholder="Folio, N° NC o proveedor..."
                    class="w-full pl-9 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all">
            </div>
            
            <div class="relative">
                <select wire:model.live="filterStatus"
                    class="w-full pl-4 pr-10 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all hover:bg-gray-100 appearance-none">
                    <option value="">Todos los estados</option>
                    @foreach(\App\Models\SupplierCreditNote::STATUS as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>

            <div class="relative">
                <select wire:model.live="filterReason"
                    class="w-full pl-4 pr-10 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all hover:bg-gray-100 appearance-none">
                    <option value="">Todos los motivos</option>
                    @foreach(\App\Models\SupplierCreditNote::REASONS as $key => $label)
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
        @forelse($notes as $cn)
            <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <span class="font-mono text-xs font-bold text-indigo-600">{{ $cn->folio }}</span>
                        <h3 class="font-bold text-gray-900 mt-1 line-clamp-1">{{ $cn->supplier->name ?? '—' }}</h3>
                        <p class="text-[10px] text-gray-500 mt-0.5">N° NC: {{ $cn->supplier_credit_note_number ?? '—' }}</p>
                    </div>
                    <x-status-badge :status="$cn->status" :label="\App\Models\SupplierCreditNote::STATUS[$cn->status] ?? $cn->status" />
                </div>

                <div class="grid grid-cols-2 gap-3 mb-4 text-sm">
                    <div class="bg-gray-50 rounded-xl p-3">
                        <p class="text-xs text-gray-500 mb-1">Monto Total</p>
                        <p class="font-bold text-gray-900">${{ number_format($cn->total, 2) }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3">
                        <p class="text-xs text-gray-500 mb-1">Saldo Pend.</p>
                        <p class="font-bold {{ $cn->balance > 0 ? 'text-amber-600' : 'text-gray-400' }}">${{ number_format($cn->balance, 2) }}</p>
                    </div>
                    <div class="col-span-2 bg-gray-50 rounded-xl p-3 flex justify-between items-center">
                        <div class="flex flex-col">
                            <span class="text-[10px] text-gray-500 uppercase font-bold tracking-wider">Motivo</span>
                            <span class="text-xs text-gray-700 font-medium">{{ \App\Models\SupplierCreditNote::REASONS[$cn->reason] ?? $cn->reason }}</span>
                        </div>
                        @if($cn->invoice)
                            <div class="text-right">
                                <span class="text-[10px] text-gray-500 uppercase font-bold tracking-wider">Factura</span>
                                <span class="block font-mono text-[10px] text-indigo-600 font-bold underline">{{ $cn->invoice->folio }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <a wire:navigate href="{{ route('purchases.credit-notes.show', $cn) }}"
                    class="flex items-center justify-center w-full py-2.5 bg-indigo-50 text-indigo-600 rounded-xl text-sm font-bold hover:bg-indigo-600 hover:text-white transition-all">
                    Ver detalles
                </a>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center shadow-sm">
                <x-empty-state message="No se encontraron notas de crédito." />
            </div>
        @endforelse
        {{ $notes->links() }}
    </div>

    {{-- VISTA ESCRITORIO --}}
    <div class="hidden lg:block bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200 text-left">
                    <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Folio / Proveedor</th>
                    <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center">N° NC / Factura</th>
                    <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Motivo de Ajuste</th>
                    <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider text-right">Total / Saldo</th>
                    <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider text-center">Estado</th>
                    <th class="px-6 py-4"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($notes as $cn)
                    <tr class="hover:bg-gray-50 transition-colors group">
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="font-mono text-xs font-bold text-indigo-600 tracking-tight">{{ $cn->folio }}</span>
                                <span class="font-semibold text-gray-900 line-clamp-1">{{ $cn->supplier->name ?? '—' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex flex-col gap-1">
                                <span class="text-xs text-gray-600 font-medium">{{ $cn->supplier_credit_note_number ?? '—' }}</span>
                                @if($cn->invoice)
                                    <a wire:navigate href="{{ route('purchases.invoices.show', $cn->invoice) }}"
                                       class="text-[10px] font-mono text-indigo-500 hover:underline">{{ $cn->invoice->folio }}</a>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-xs text-gray-600 font-medium line-clamp-1">
                                {{ \App\Models\SupplierCreditNote::REASONS[$cn->reason] ?? $cn->reason }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-gray-900">${{ number_format($cn->total, 2) }}</span>
                                <span class="text-[10px] {{ $cn->balance > 0 ? 'text-amber-600 font-bold' : 'text-gray-400 font-medium' }}">
                                    Saldo: ${{ number_format($cn->balance, 2) }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <x-status-badge :status="$cn->status" :label="\App\Models\SupplierCreditNote::STATUS[$cn->status] ?? $cn->status" />
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a wire:navigate href="{{ route('purchases.credit-notes.show', $cn) }}"
                                class="inline-flex items-center justify-center p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <x-empty-state message="No se encontraron notas de crédito." />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($notes->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                {{ $notes->links() }}
            </div>
        @endif
    </div>
</div>


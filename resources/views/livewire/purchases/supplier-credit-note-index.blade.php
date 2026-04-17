<div>
    <x-page-header title="Notas de crédito de proveedor" description="Devoluciones y ajustes recibidos de proveedores">
        <x-slot:actions>
            @can('create purchases')
            <a wire:navigate href="{{ route('purchases.credit-notes.create') }}"
               class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition shadow-sm">
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
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Por aplicar</p>
            <p class="text-xl font-bold text-gray-900">${{ number_format($kpis['total_pending'], 0) }}</p>
            <p class="text-xs text-gray-400">{{ $kpis['draft'] }} notas en borrador</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Aplicadas este mes</p>
            <p class="text-xl font-bold text-green-600">${{ number_format($kpis['applied_month'], 0) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Total registradas</p>
            <p class="text-xl font-bold text-gray-900">{{ $kpis['total_count'] }}</p>
        </div>
        <div class="bg-indigo-50 rounded-xl border border-indigo-100 p-4">
            <p class="text-xs font-semibold text-indigo-700 mb-1">¿Qué es una NC?</p>
            <p class="text-xs text-indigo-600 leading-relaxed">Documento del proveedor que reduce el saldo de una factura por devolución, ajuste de precio o error.</p>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
        <div class="relative sm:col-span-2">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input wire:model.live.debounce.300ms="search" type="text"
                   placeholder="Folio, N° NC proveedor o proveedor..."
                   class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
        </div>
        <select wire:model.live="filterStatus" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
            <option value="">Todos los estados</option>
            @foreach(\App\Models\SupplierCreditNote::STATUS as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </select>
        <select wire:model.live="filterReason" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
            <option value="">Todos los motivos</option>
            @foreach(\App\Models\SupplierCreditNote::REASONS as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </select>
    </div>

    {{-- Tabla --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[800px]">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Folio</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Proveedor</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden md:table-cell">N° NC Prov.</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden lg:table-cell">Factura</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden lg:table-cell">Motivo</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Total</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden sm:table-cell">Saldo</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Estado</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($notes as $cn)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-5 py-3 font-mono text-xs text-gray-600">{{ $cn->folio }}</td>
                        <td class="px-5 py-3 font-medium text-gray-900 max-w-[150px] truncate">{{ $cn->supplier->name ?? '—' }}</td>
                        <td class="px-5 py-3 hidden md:table-cell text-gray-500 text-xs">{{ $cn->supplier_credit_note_number ?? '—' }}</td>
                        <td class="px-5 py-3 hidden lg:table-cell">
                            @if($cn->invoice)
                                <a wire:navigate href="{{ route('purchases.invoices.show', $cn->invoice) }}"
                                   class="text-xs font-mono text-indigo-500 hover:underline">{{ $cn->invoice->folio }}</a>
                            @else
                                <span class="text-xs text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 hidden lg:table-cell text-xs text-gray-500">
                            {{ \App\Models\SupplierCreditNote::REASONS[$cn->reason] ?? $cn->reason }}
                        </td>
                        <td class="px-5 py-3 text-right font-semibold text-gray-900">${{ number_format($cn->total, 2) }}</td>
                        <td class="px-5 py-3 text-right hidden sm:table-cell {{ $cn->balance > 0 ? 'text-amber-600 font-medium' : 'text-gray-400' }}">
                            ${{ number_format($cn->balance, 2) }}
                        </td>
                        <td class="px-5 py-3">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                                {{ \App\Models\SupplierCreditNote::STATUS_COLORS[$cn->status] ?? 'bg-gray-100 text-gray-500' }}">
                                {{ \App\Models\SupplierCreditNote::STATUS[$cn->status] ?? $cn->status }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <a wire:navigate href="{{ route('purchases.credit-notes.show', $cn) }}"
                               class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Ver →</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-5 py-10 text-center text-gray-400 text-sm">No se encontraron notas de crédito.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($notes->hasPages())
        <div class="px-5 py-3 border-t border-gray-100">{{ $notes->links() }}</div>
        @endif
    </div>
</div>

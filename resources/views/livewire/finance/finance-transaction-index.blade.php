<div>
    <x-page-header title="Transacciones" description="Ingresos, egresos y transferencias">
        <x-slot:actions>
            @can('create finance')
            <a wire:navigate href="{{ route('finance.transactions.create') }}"
                class="inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nueva transacción
            </a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <x-alert />

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
        <div class="relative sm:col-span-2">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input wire:model.live.debounce.300ms="search" type="text"
                placeholder="Buscar por folio, concepto o referencia..."
                class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
        </div>
        <select wire:model.live="filterType" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
            <option value="">Todos los tipos</option>
            <option value="ingreso">Ingreso</option>
            <option value="egreso">Egreso</option>
            <option value="transferencia">Transferencia</option>
        </select>
        <select wire:model.live="filterAccount" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
            <option value="">Todas las cuentas</option>
            @foreach($accounts as $account)
                <option value="{{ $account->id }}">{{ $account->name }}</option>
            @endforeach
        </select>
        <input wire:model.live="filterDateFrom" type="date"
            class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
        <input wire:model.live="filterDateTo" type="date"
            class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[720px]">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Folio</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Fecha</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Concepto</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden md:table-cell">Cuenta</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Tipo</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Monto</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden lg:table-cell">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($transactions as $tx)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-5 py-3 font-mono text-xs text-gray-500">{{ $tx->folio }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ $tx->transaction_date->format('d/m/Y') }}</td>
                        <td class="px-5 py-3">
                            <div class="font-medium text-gray-900 truncate max-w-[200px]">{{ $tx->concept }}</div>
                            @if($tx->reference)<div class="text-xs text-gray-400">Ref: {{ $tx->reference }}</div>@endif
                        </td>
                        <td class="px-5 py-3 hidden md:table-cell text-gray-600">{{ $tx->account->name }}</td>
                        <td class="px-5 py-3">
                            @php
                                $typeColors = ['ingreso'=>'green','egreso'=>'red','transferencia'=>'blue'];
                                $c = $typeColors[$tx->type] ?? 'gray';
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $c }}-100 text-{{ $c }}-700 capitalize">
                                {{ $tx->type }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right font-semibold {{ $tx->type === 'egreso' ? 'text-red-600' : 'text-green-600' }}">
                            {{ $tx->type === 'egreso' ? '-' : '+' }}{{ $tx->currency }} {{ number_format($tx->amount, 2) }}
                        </td>
                        <td class="px-5 py-3 hidden lg:table-cell">
                            @php $sc = ['pendiente'=>'yellow','confirmado'=>'green','cancelado'=>'red'][$tx->status] ?? 'gray'; @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $sc }}-100 text-{{ $sc }}-700 capitalize">
                                {{ $tx->status }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-10 text-center text-gray-400 text-sm">No se encontraron transacciones.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transactions->hasPages())
        <div class="px-5 py-3 border-t border-gray-100">{{ $transactions->links() }}</div>
        @endif
    </div>
</div>

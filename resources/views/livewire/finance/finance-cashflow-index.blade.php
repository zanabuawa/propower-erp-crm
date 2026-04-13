<div>
    <x-page-header title="Flujo de caja" description="Proyección y control de entradas y salidas">
        <x-slot:actions>
            @can('create finance')
            <a wire:navigate href="{{ route('finance.cashflow.create') }}"
                class="inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nuevo movimiento
            </a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <x-alert />

    {{-- Resumen --}}
    <div class="grid grid-cols-3 gap-3 mb-5">
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-400 mb-1">Total entradas</p>
            <p class="text-lg font-semibold text-green-600">+ {{ number_format($totalEntradas, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-400 mb-1">Total salidas</p>
            <p class="text-lg font-semibold text-red-600">- {{ number_format($totalSalidas, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-400 mb-1">Saldo neto</p>
            @php $neto = $totalEntradas - $totalSalidas; @endphp
            <p class="text-lg font-semibold {{ $neto >= 0 ? 'text-gray-900' : 'text-red-600' }}">
                {{ $neto >= 0 ? '+' : '' }}{{ number_format($neto, 2) }}
            </p>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
        <div class="relative col-span-2">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input wire:model.live.debounce.300ms="search" type="text"
                placeholder="Buscar por concepto..."
                class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
        </div>
        <select wire:model.live="filterFlow" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
            <option value="">Entradas y salidas</option>
            <option value="entrada">Entradas</option>
            <option value="salida">Salidas</option>
        </select>
        <select wire:model.live="filterType" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
            <option value="">Proyectado y real</option>
            <option value="proyectado">Proyectado</option>
            <option value="real">Real</option>
        </select>
        <select wire:model.live="filterAccount" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
            <option value="">Todas las cuentas</option>
            @foreach($accounts as $account)
                <option value="{{ $account->id }}">{{ $account->name }}</option>
            @endforeach
        </select>
        <input wire:model.live="filterDateFrom" type="date" placeholder="Desde"
            class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
        <input wire:model.live="filterDateTo" type="date" placeholder="Hasta"
            class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[720px]">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Fecha esperada</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Concepto</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden md:table-cell">Cuenta</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Flujo</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden lg:table-cell">Tipo</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Monto</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Realizado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($cashflows as $cf)
                    <tr class="hover:bg-gray-50 transition {{ !$cf->is_realized && $cf->expected_date->isPast() ? 'bg-red-50' : '' }}">
                        <td class="px-5 py-3 text-gray-600">
                            {{ $cf->expected_date->format('d/m/Y') }}
                            @if($cf->realized_date)
                            <div class="text-xs text-green-600">Real: {{ $cf->realized_date->format('d/m/Y') }}</div>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <div class="font-medium text-gray-900 truncate max-w-[200px]">{{ $cf->concept }}</div>
                            @if($cf->project)<div class="text-xs text-gray-400">{{ $cf->project->code }}</div>@endif
                        </td>
                        <td class="px-5 py-3 hidden md:table-cell text-gray-600">{{ $cf->account->name }}</td>
                        <td class="px-5 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $cf->flow === 'entrada' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }} capitalize">
                                {{ $cf->flow }}
                            </span>
                        </td>
                        <td class="px-5 py-3 hidden lg:table-cell">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $cf->type === 'real' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600' }} capitalize">
                                {{ $cf->type }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right font-semibold {{ $cf->flow === 'salida' ? 'text-red-600' : 'text-green-600' }}">
                            {{ $cf->flow === 'salida' ? '-' : '+' }}{{ $cf->currency }} {{ number_format($cf->amount, 2) }}
                        </td>
                        <td class="px-5 py-3">
                            @if($cf->is_realized)
                                <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            @else
                                <span class="text-xs text-gray-400">Pendiente</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-10 text-center text-gray-400 text-sm">No se encontraron movimientos.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($cashflows->hasPages())
        <div class="px-5 py-3 border-t border-gray-100">{{ $cashflows->links() }}</div>
        @endif
    </div>
</div>

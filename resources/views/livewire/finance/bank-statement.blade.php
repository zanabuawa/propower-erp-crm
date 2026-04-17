<div>
    <x-page-header title="Extracto bancario" description="Saldos diarios y movimientos por cuenta">
    </x-page-header>

    <x-alert />

    {{-- Filtros --}}
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 mb-5">
        <div class="sm:col-span-2">
            <label class="block text-xs font-medium text-gray-600 mb-1">Cuenta</label>
            <select wire:model.live="accountId"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
                <option value="">Seleccionar cuenta</option>
                @foreach($accounts as $acc)
                    <option value="{{ $acc->id }}">{{ $acc->name }} ({{ $acc->currency }}) — ${{ number_format($acc->current_balance, 2) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Desde</label>
            <input wire:model.live="dateFrom" type="date"
                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Hasta</label>
            <input wire:model.live="dateTo" type="date"
                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
        </div>
    </div>

    @if($account)

    {{-- KPIs del período --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Saldo apertura</p>
            <p class="text-xl font-bold text-gray-700">${{ number_format($summary['opening'], 2) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-green-200 p-4 shadow-sm">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Ingresos</p>
            <p class="text-xl font-bold text-green-600">+${{ number_format($summary['income'], 2) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-red-100 p-4 shadow-sm">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Egresos</p>
            <p class="text-xl font-bold text-red-500">−${{ number_format($summary['expense'], 2) }}</p>
        </div>
        <div class="bg-white rounded-xl border {{ $summary['closing'] >= 0 ? 'border-indigo-200' : 'border-red-300' }} p-4 shadow-sm">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Saldo cierre</p>
            <p class="text-xl font-bold {{ $summary['closing'] >= 0 ? 'text-indigo-700' : 'text-red-600' }}">${{ number_format($summary['closing'], 2) }}</p>
        </div>
    </div>

    {{-- Tabs vista --}}
    <div class="border-b border-gray-200 mb-4">
        <nav class="flex gap-1">
            <button wire:click="$set('view', 'statement')"
                    class="px-4 py-2.5 text-sm font-medium border-b-2 transition
                        {{ $view === 'statement' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                Movimientos
            </button>
            <button wire:click="$set('view', 'daily')"
                    class="px-4 py-2.5 text-sm font-medium border-b-2 transition
                        {{ $view === 'daily' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                Saldos diarios
            </button>
        </nav>
    </div>

    {{-- Vista: Movimientos con saldo corriente --}}
    @if($view === 'statement')
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[700px]">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Fecha</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Concepto</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden lg:table-cell">Categoría</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden md:table-cell">Ref.</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Cargo</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Abono</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Saldo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    {{-- Saldo inicial --}}
                    <tr class="bg-indigo-50/30">
                        <td class="px-5 py-2 text-xs text-gray-500">{{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }}</td>
                        <td colspan="4" class="px-5 py-2 text-xs font-semibold text-indigo-600">Saldo de apertura</td>
                        <td></td>
                        <td class="px-5 py-2 text-right text-xs font-bold text-indigo-700">${{ number_format($summary['opening'], 2) }}</td>
                    </tr>
                    @forelse($transactions as $tx)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-5 py-3 text-xs text-gray-500 whitespace-nowrap">{{ \Carbon\Carbon::parse($tx->transaction_date)->format('d/m/Y') }}</td>
                        <td class="px-5 py-3 max-w-[220px]">
                            <p class="font-medium text-gray-800 truncate">{{ $tx->concept }}</p>
                            @if($tx->folio)
                            <p class="text-xs font-mono text-gray-400">{{ $tx->folio }}</p>
                            @endif
                        </td>
                        <td class="px-5 py-3 hidden lg:table-cell text-xs text-gray-500 capitalize">{{ $tx->category ?? '—' }}</td>
                        <td class="px-5 py-3 hidden md:table-cell text-xs text-gray-400">{{ $tx->reference ?? '—' }}</td>
                        <td class="px-5 py-3 text-right text-sm {{ $tx->type === 'egreso' ? 'text-red-600 font-medium' : 'text-gray-200' }}">
                            {{ $tx->type === 'egreso' ? '−$'.number_format($tx->amount, 2) : '' }}
                        </td>
                        <td class="px-5 py-3 text-right text-sm {{ $tx->type === 'ingreso' ? 'text-green-600 font-medium' : 'text-gray-200' }}">
                            {{ $tx->type === 'ingreso' ? '+$'.number_format($tx->amount, 2) : '' }}
                        </td>
                        <td class="px-5 py-3 text-right font-semibold {{ $tx->running_balance >= 0 ? 'text-gray-900' : 'text-red-600' }}">
                            ${{ number_format($tx->running_balance, 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-10 text-center text-gray-400 text-sm">No hay movimientos en el período seleccionado.</td>
                    </tr>
                    @endforelse
                    {{-- Saldo final --}}
                    @if($transactions->count())
                    <tr class="bg-gray-50 border-t-2 border-gray-200">
                        <td colspan="5" class="px-5 py-3 text-right text-xs font-bold text-gray-600 uppercase">Saldo al cierre del período</td>
                        <td></td>
                        <td class="px-5 py-3 text-right text-base font-bold {{ $summary['closing'] >= 0 ? 'text-gray-900' : 'text-red-600' }}">
                            ${{ number_format($summary['closing'], 2) }}
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Vista: Saldos diarios --}}
    @if($view === 'daily')
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[580px]">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Fecha</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Apertura</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Ingresos</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Egresos</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Cierre</th>
                        <th class="text-center px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Movs.</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($dailyBalances as $db)
                    @php $change = (float)$db->closing_balance - (float)$db->opening_balance; @endphp
                    <tr class="hover:bg-gray-50 transition {{ $db->transaction_count === 0 ? 'opacity-50' : '' }}">
                        <td class="px-5 py-3 text-sm font-medium text-gray-800">
                            {{ $db->balance_date->isoFormat('ddd DD/MM/YY') }}
                        </td>
                        <td class="px-5 py-3 text-right text-gray-600">${{ number_format($db->opening_balance, 2) }}</td>
                        <td class="px-5 py-3 text-right {{ $db->total_income > 0 ? 'text-green-600 font-medium' : 'text-gray-300' }}">
                            {{ $db->total_income > 0 ? '+$'.number_format($db->total_income, 2) : '—' }}
                        </td>
                        <td class="px-5 py-3 text-right {{ $db->total_expense > 0 ? 'text-red-500 font-medium' : 'text-gray-300' }}">
                            {{ $db->total_expense > 0 ? '−$'.number_format($db->total_expense, 2) : '—' }}
                        </td>
                        <td class="px-5 py-3 text-right font-semibold {{ $db->closing_balance >= 0 ? 'text-gray-900' : 'text-red-600' }}">
                            ${{ number_format($db->closing_balance, 2) }}
                        </td>
                        <td class="px-5 py-3 text-center">
                            @if($db->transaction_count > 0)
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold">
                                {{ $db->transaction_count }}
                            </span>
                            @else
                            <span class="text-gray-300 text-xs">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-gray-400 text-sm">
                            Sin datos. Selecciona una cuenta y rango de fechas.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @else
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm flex flex-col items-center justify-center py-20 text-center">
        <svg class="w-12 h-12 text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
        </svg>
        <p class="text-sm text-gray-400">Selecciona una cuenta bancaria para ver el extracto</p>
    </div>
    @endif
</div>

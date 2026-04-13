<div>
    <x-page-header title="Cuentas" description="Cuentas bancarias y cajas">
        <x-slot:actions>
            @can('create finance')
            <a wire:navigate href="{{ route('finance.accounts.create') }}"
                class="inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nueva cuenta
            </a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <x-alert />

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-5">
        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input wire:model.live.debounce.300ms="search" type="text"
                placeholder="Buscar por código, nombre o banco..."
                class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
        </div>
        <select wire:model.live="filterType" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
            <option value="">Todos los tipos</option>
            <option value="banco">Banco</option>
            <option value="caja">Caja</option>
            <option value="credito">Crédito</option>
            <option value="inversion">Inversión</option>
            <option value="otro">Otro</option>
        </select>
        <select wire:model.live="filterBranch" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
            <option value="">Todas las sucursales</option>
            @foreach($branches as $branch)
                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[640px]">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Código</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Nombre</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden md:table-cell">Banco</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Tipo</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Saldo actual</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden lg:table-cell">Estado</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($accounts as $account)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-5 py-3 font-mono text-xs text-gray-500">{{ $account->code }}</td>
                        <td class="px-5 py-3 font-medium text-gray-900">{{ $account->name }}</td>
                        <td class="px-5 py-3 hidden md:table-cell text-gray-600">{{ $account->bank_name ?? '—' }}</td>
                        <td class="px-5 py-3 capitalize text-gray-600">{{ $account->type }}</td>
                        <td class="px-5 py-3 text-right font-medium {{ $account->current_balance < 0 ? 'text-red-600' : 'text-gray-900' }}">
                            {{ $account->currency }} {{ number_format($account->current_balance, 2) }}
                        </td>
                        <td class="px-5 py-3 hidden lg:table-cell">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $account->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $account->is_active ? 'Activa' : 'Inactiva' }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            @can('edit finance')
                            <a wire:navigate href="{{ route('finance.accounts.edit', $account) }}"
                                class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">Editar</a>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-10 text-center text-gray-400 text-sm">No se encontraron cuentas.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($accounts->hasPages())
        <div class="px-5 py-3 border-t border-gray-100">{{ $accounts->links() }}</div>
        @endif
    </div>
</div>

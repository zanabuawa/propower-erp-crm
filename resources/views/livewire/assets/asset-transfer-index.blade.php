<div>
    <x-page-header title="Transferencias de activos fijos" description="Historial de movimientos de equipo entre sucursales y usuarios">
        <x-slot:actions>
            @can('transfer assets')
            <a wire:navigate href="{{ route('assets.transfers.create') }}"
                class="inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nueva transferencia
            </a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <x-alert />

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-5">
        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input wire:model.live.debounce.300ms="search" type="text"
                placeholder="Buscar por folio o activo..."
                aria-label="Buscar transferencias"
                class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-transparent transition">
        </div>
        <input wire:model.live="dateFrom" type="date" aria-label="Desde fecha"
            class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
        <input wire:model.live="dateTo" type="date" aria-label="Hasta fecha"
            class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[700px]">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Folio</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Activo</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Origen</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Destino</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden md:table-cell">Registrado por</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden md:table-cell">Fecha</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($transfers as $transfer)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-5 py-3">
                                <span class="font-mono text-xs font-medium text-gray-900">{{ $transfer->folio }}</span>
                            </td>
                            <td class="px-5 py-3">
                                <p class="font-medium text-gray-900">{{ $transfer->asset?->name ?? '—' }}</p>
                                <p class="text-xs text-gray-400 font-mono">{{ $transfer->asset?->folio ?? '' }}</p>
                            </td>
                            <td class="px-5 py-3 text-gray-700">
                                <p>{{ $transfer->fromBranch?->name ?? '—' }}</p>
                                @if($transfer->fromUser)
                                    <p class="text-xs text-gray-400">{{ $transfer->fromUser->name }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-gray-700">
                                <p>{{ $transfer->toBranch?->name ?? '—' }}</p>
                                @if($transfer->toUser)
                                    <p class="text-xs text-gray-400">{{ $transfer->toUser->name }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-gray-600 hidden md:table-cell">{{ $transfer->requestedBy?->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-gray-500 text-xs hidden md:table-cell">{{ $transfer->transferred_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6"><x-empty-state message="No se encontraron transferencias de activos." /></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transfers->hasPages())
            <div class="px-5 py-3 border-t border-gray-100">{{ $transfers->links('vendor.pagination.tailwind') }}</div>
        @endif
    </div>
</div>

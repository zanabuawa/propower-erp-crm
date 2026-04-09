<div>
    <x-page-header title="Recepción de mercancías" description="Entradas directas de inventario — sin orden de compra">
        <x-slot:actions>
            <a wire:navigate href="{{ route('purchases.goods-receipts.create') }}"
                class="inline-flex items-center justify-center gap-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nueva recepción
            </a>
        </x-slot:actions>
    </x-page-header>

    <x-alert />

    <div class="mb-5">
        <div class="relative w-full sm:w-80">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input wire:model.live.debounce.300ms="search" type="text"
                placeholder="Buscar por folio o referencia..."
                aria-label="Buscar recepciones"
                class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-transparent transition">
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[580px]">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Folio</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Almacén</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden sm:table-cell">Referencia</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden sm:table-cell">Productos</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden md:table-cell">Registrado por</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden md:table-cell">Fecha</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($movements as $movement)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-5 py-3">
                                <span class="font-mono text-xs font-medium text-indigo-600">{{ $movement->folio }}</span>
                                <p class="text-xs text-gray-400 sm:hidden mt-0.5">{{ $movement->reference ?? '—' }}</p>
                            </td>
                            <td class="px-5 py-3 text-gray-700">{{ $movement->warehouse?->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-gray-500 hidden sm:table-cell">{{ $movement->reference ?? '—' }}</td>
                            <td class="px-5 py-3 hidden sm:table-cell">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($movement->items->take(3) as $mItem)
                                        <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">
                                            {{ $mItem->product?->name ?? '—' }}
                                            <span class="text-gray-400">×{{ number_format($mItem->quantity, 2) }}</span>
                                        </span>
                                    @endforeach
                                    @if($movement->items->count() > 3)
                                        <span class="text-xs text-gray-400">+{{ $movement->items->count() - 3 }} más</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-3 text-gray-500 hidden md:table-cell">{{ $movement->user?->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-gray-500 text-xs hidden md:table-cell">{{ $movement->moved_at?->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6"><x-empty-state message="No hay recepciones de mercancías registradas." /></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($movements->hasPages())
            <div class="px-5 py-3 border-t border-gray-100">{{ $movements->links() }}</div>
        @endif
    </div>
</div>

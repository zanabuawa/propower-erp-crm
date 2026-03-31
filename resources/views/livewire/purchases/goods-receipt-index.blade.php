<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-medium text-gray-900">Recepción de mercancías</h1>
            <p class="text-sm text-gray-400 mt-0.5">Entradas directas de inventario — sin orden de compra</p>
        </div>
        <a href="{{ route('purchases.goods-receipts.create') }}"
            class="inline-flex items-center gap-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
            + Nueva recepción
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-xl border border-gray-200 mb-4 p-4">
        <input wire:model.live.debounce.300ms="search" type="text"
            placeholder="Buscar por folio o referencia..."
            class="w-full sm:w-80 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Folio</th>
                    <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Almacén</th>
                    <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Referencia</th>
                    <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Productos</th>
                    <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Registrado por</th>
                    <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Fecha</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($movements as $movement)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-5 py-3">
                            <span class="font-mono text-xs font-medium text-indigo-600">{{ $movement->folio }}</span>
                        </td>
                        <td class="px-5 py-3 text-gray-700">{{ $movement->warehouse?->name ?? '—' }}</td>
                        <td class="px-5 py-3 text-gray-500">{{ $movement->reference ?? '—' }}</td>
                        <td class="px-5 py-3">
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
                        <td class="px-5 py-3 text-gray-500">{{ $movement->user?->name ?? '—' }}</td>
                        <td class="px-5 py-3 text-gray-500 text-xs">
                            {{ $movement->moved_at?->format('d/m/Y H:i') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-sm text-gray-400">
                            No hay recepciones registradas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($movements->hasPages())
            <div class="px-5 py-3 border-t border-gray-100">
                {{ $movements->links() }}
            </div>
        @endif
    </div>
</div>

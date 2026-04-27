<div>
    <x-page-header title="Listas de precios" description="Gestiona tus listas de precios por cliente">
        <x-slot:actions>
            <a wire:navigate href="{{ route('sales.price-lists.comparison') }}"
                class="inline-flex items-center justify-center gap-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Comparador
            </a>
            <a wire:navigate href="{{ route('sales.price-lists.create') }}"
                class="inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nueva lista
            </a>
        </x-slot:actions>
    </x-page-header>

    <x-alert />

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[580px]">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Nombre</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden sm:table-cell">Moneda</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden md:table-cell">Productos</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden sm:table-cell">Vigencia</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Estado</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($priceLists as $list)
                        <tr class="hover:bg-gray-50 transition group">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-gray-900">{{ $list->name }}</span>
                                    @if($list->is_default)
                                        <span class="text-xs bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded-full font-medium">Predeterminada</span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-400 sm:hidden mt-0.5">{{ $list->currency }}</p>
                            </td>
                            <td class="px-5 py-3 text-gray-600 hidden sm:table-cell">{{ $list->currency }}</td>
                            <td class="px-5 py-3 text-gray-600 hidden md:table-cell">{{ $list->items_count }}</td>
                            <td class="px-5 py-3 text-gray-500 text-xs hidden sm:table-cell">
                                @if($list->valid_from || $list->valid_to)
                                    {{ $list->valid_from?->format('d/m/Y') ?? '—' }} — {{ $list->valid_to?->format('d/m/Y') ?? '—' }}
                                @else
                                    Sin vencimiento
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $list->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $list->is_active ? 'Activa' : 'Inactiva' }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <div class="flex items-center justify-end gap-2 transition">
                                    <a wire:navigate href="{{ route('sales.price-lists.edit', $list) }}"
                                        class="text-xs text-indigo-600 hover:text-indigo-800 font-medium px-2 py-1 rounded hover:bg-indigo-50 transition">Editar</a>
                                    <button wire:click="confirmDelete({{ $list->id }})"
                                        class="text-xs text-red-500 hover:text-red-700 font-medium px-2 py-1 rounded hover:bg-red-50 transition">Eliminar</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6"><x-empty-state message="No se encontraron listas de precios." /></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($priceLists->hasPages())
            <div class="px-5 py-3 border-t border-gray-100">{{ $priceLists->links() }}</div>
        @endif
    </div>

    <x-delete-modal
        :show="$confirmingDelete"
        title="¿Eliminar lista de precios?"
        description="Esta acción no se puede deshacer."
    />
</div>

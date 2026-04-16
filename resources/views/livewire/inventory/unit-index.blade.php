<div>
    <x-page-header title="Unidades de medida" description="Define las unidades para tus productos">
        <x-slot:actions>
            <a wire:navigate href="{{ route('inventory.units.create') }}"
                class="inline-flex items-center justify-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-medium px-5 py-2 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nueva unidad
            </a>
        </x-slot:actions>
    </x-page-header>

    <x-alert />

    {{-- Filtro --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-5 mb-6 shadow-sm">
        <div class="relative w-full sm:w-80">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input wire:model.live.debounce.300ms="search" type="text"
                placeholder="Buscar unidad..."
                aria-label="Buscar unidad de medida"
                class="w-full pl-9 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[400px]">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="text-left px-5 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Nombre</th>
                        <th class="text-left px-5 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Abreviación</th>
                        <th class="text-left px-5 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider hidden sm:table-cell">Productos</th>
                        <th class="text-left px-5 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Estado</th>
                        <th class="px-5 py-4 w-32"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($units as $unit)
                        <tr class="hover:bg-gray-50/70 transition-colors duration-150 group">
                            <td class="px-5 py-4 font-semibold text-gray-900">{{ $unit->name }}</td>
                            <td class="px-5 py-4">
                                <span class="bg-gray-100 text-gray-700 text-xs px-2.5 py-1 rounded-lg font-mono font-medium">{{ $unit->abbreviation }}</span>
                            </td>
                            <td class="px-5 py-4 hidden sm:table-cell">
                                <span class="inline-flex items-center justify-center min-w-[28px] px-2 py-0.5 text-xs font-semibold text-indigo-600 bg-indigo-50 rounded-lg">
                                    {{ $unit->products_count }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium
                                    {{ $unit->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-gray-100 text-gray-500 border border-gray-200' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $unit->is_active ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                                    {{ $unit->is_active ? 'Activa' : 'Inactiva' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-1.5 opacity-0 group-hover:opacity-100 transition-all duration-200">
                                    <a wire:navigate href="{{ route('inventory.units.edit', $unit) }}"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm text-indigo-600 hover:text-white bg-indigo-50 hover:bg-indigo-600 rounded-lg transition-all duration-200 font-medium cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Editar
                                    </a>
                                    <button wire:click="confirmDelete({{ $unit->id }})"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm text-red-600 hover:text-white bg-red-50 hover:bg-red-600 rounded-lg transition-all duration-200 font-medium cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Eliminar
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center">
                                <x-empty-state message="No se encontraron unidades de medida." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($units->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">{{ $units->links() }}</div>
        @endif
    </div>

    <x-delete-modal
        :show="$confirmingDelete"
        title="¿Eliminar unidad?"
        description="Los productos asociados quedarán sin unidad de medida."
    />
</div>

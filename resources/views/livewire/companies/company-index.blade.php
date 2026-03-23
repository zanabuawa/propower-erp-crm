<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-medium text-gray-900">Empresas</h1>
            <p class="text-sm text-gray-500 mt-0.5">Gestiona las empresas del sistema</p>
        </div>
        <a href="{{ route('companies.create') }}"
            class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
            + Nueva empresa
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-4">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar por nombre o RFC..."
            class="w-full sm:w-80 border border-gray-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50">
                    <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Empresa</th>
                    <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">RFC</th>
                    <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Sucursales</th>
                    <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Usuarios</th>
                    <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Estado</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($companies as $company)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                @if($company->icon)
                                    <img src="{{ Storage::url($company->icon) }}" class="w-8 h-8 rounded-lg object-contain">
                                @else
                                    <div
                                        class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center text-indigo-600 font-semibold text-xs">
                                        {{ strtoupper(substr($company->name, 0, 2)) }}
                                    </div>
                                @endif
                                <div>
                                    <p class="font-medium text-gray-900">{{ $company->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $company->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-gray-600">{{ $company->rfc ?? '—' }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ $company->branches_count }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ $company->users_count }}</td>
                        <td class="px-5 py-3">
                            <span
                                class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                                        {{ $company->is_active ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $company->is_active ? 'Activa' : 'Inactiva' }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('companies.edit', $company) }}"
                                    class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                                    Editar
                                </a>
                                <button wire:click="confirmDelete({{ $company->id }})"
                                    class="text-xs text-red-500 hover:text-red-700 font-medium">
                                    Eliminar
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-gray-400 text-sm">
                            No se encontraron empresas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($companies->hasPages())
            <div class="px-5 py-3 border-t border-gray-100">
                {{ $companies->links() }}
            </div>
        @endif
    </div>

    @if($confirmingDelete)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl border border-gray-200 p-6 w-full max-w-sm mx-4">
                <h3 class="font-medium text-gray-900 mb-1">¿Eliminar empresa?</h3>
                <p class="text-sm text-gray-500 mb-5">Esta acción no se puede deshacer. Se eliminarán también sus
                    sucursales.</p>
                <div class="flex gap-3 justify-end">
                    <button wire:click="cancelDelete"
                        class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                        Cancelar
                    </button>
                    <button wire:click="delete"
                        class="px-4 py-2 text-sm bg-red-600 hover:bg-red-700 text-white rounded-lg transition">
                        Sí, eliminar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
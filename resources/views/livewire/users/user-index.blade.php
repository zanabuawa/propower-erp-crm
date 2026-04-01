<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl font-medium text-gray-900">Usuarios</h1>
            <p class="text-sm text-gray-500 mt-0.5">Gestiona los usuarios del sistema</p>
        </div>
        <a wire:navigate href="{{ route('users.create') }}"
            class="inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
            + Nuevo usuario
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg">{{ session('error') }}</div>
    @endif

    <div class="mb-4">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar por nombre o email..."
            class="w-full sm:w-80 border border-gray-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[480px]">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50">
                        <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Usuario</th>
                        <th class="text-left px-5 py-3 text-xs font-medium text-gray-500 hidden md:table-cell">Empresa</th>
                        <th class="text-left px-5 py-3 text-xs font-medium text-gray-500 hidden md:table-cell">Sucursal</th>
                        <th class="text-left px-5 py-3 text-xs font-medium text-gray-500 hidden sm:table-cell">Rol</th>
                        <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Estado</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-semibold text-xs flex-shrink-0">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $user->name }}</p>
                                        <p class="text-xs text-gray-400">{{ $user->email }}</p>
                                        <p class="text-xs text-gray-400 sm:hidden">{{ $user->roles->first()?->name ?? '—' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-gray-600 hidden md:table-cell">{{ $user->company?->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-gray-600 hidden md:table-cell">{{ $user->branch?->name ?? '—' }}</td>
                            <td class="px-5 py-3 hidden sm:table-cell">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700">
                                    {{ $user->roles->first()?->name ?? '—' }}
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                                    {{ $user->is_active ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $user->is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <a wire:navigate href="{{ route('users.edit', $user) }}"
                                        class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Editar</a>
                                    @if($user->id !== auth()->id())
                                        <button wire:click="confirmDelete({{ $user->id }})"
                                            class="text-xs text-red-500 hover:text-red-700 font-medium">Eliminar</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-gray-400 text-sm">No se encontraron usuarios.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
            <div class="px-5 py-3 border-t border-gray-100">{{ $users->links() }}</div>
        @endif
    </div>

    @if($confirmingDelete)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-xl border border-gray-200 p-6 w-full max-w-sm">
                <h3 class="font-medium text-gray-900 mb-1">¿Eliminar usuario?</h3>
                <p class="text-sm text-gray-500 mb-5">Esta acción no se puede deshacer.</p>
                <div class="flex gap-3 justify-end">
                    <button wire:click="cancelDelete" class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition">Cancelar</button>
                    <button wire:click="delete" class="px-4 py-2 text-sm bg-red-600 hover:bg-red-700 text-white rounded-lg transition">Sí, eliminar</button>
                </div>
            </div>
        </div>
    @endif
</div>

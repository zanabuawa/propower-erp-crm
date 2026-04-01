<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl font-medium text-gray-900">Proveedores</h1>
            <p class="text-sm text-gray-500 mt-0.5">Gestiona tu cartera de proveedores</p>
        </div>
        <a wire:navigate href="{{ route('suppliers.create') }}"
            class="inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
            + Nuevo proveedor
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
        <input wire:model.live.debounce.300ms="search" type="text"
            placeholder="Buscar por nombre o RFC..."
            class="col-span-1 sm:col-span-2 border border-gray-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
        <select wire:model.live="filterType"
            class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
            <option value="">Todos los tipos</option>
            <option value="person">Persona física</option>
            <option value="company">Empresa</option>
        </select>
        <select wire:model.live="filterStatus"
            class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
            <option value="">Todos los estados</option>
            <option value="active">Activo</option>
            <option value="inactive">Inactivo</option>
        </select>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[580px]">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50">
                        <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Proveedor</th>
                        <th class="text-left px-5 py-3 text-xs font-medium text-gray-500 hidden sm:table-cell">Tipo</th>
                        <th class="text-left px-5 py-3 text-xs font-medium text-gray-500 hidden sm:table-cell">RFC</th>
                        <th class="text-left px-5 py-3 text-xs font-medium text-gray-500 hidden md:table-cell">Teléfono</th>
                        <th class="text-left px-5 py-3 text-xs font-medium text-gray-500 hidden md:table-cell">Contactos</th>
                        <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Estado</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($suppliers as $supplier)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    @if($supplier->image)
                                        <img src="{{ Storage::url($supplier->image) }}"
                                            class="w-9 h-9 rounded-full object-cover border border-gray-100">
                                    @else
                                        <div class="w-9 h-9 rounded-full bg-teal-100 flex items-center justify-center text-teal-600 font-semibold text-xs">
                                            {{ strtoupper(substr($supplier->name, 0, 2)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $supplier->name }}</p>
                                        <p class="text-xs text-gray-400">{{ $supplier->primary_email ?? $supplier->city }}</p>
                                        <p class="text-xs text-gray-400 sm:hidden">
                                            {{ $supplier->type === 'company' ? 'Empresa' : 'Persona' }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3 hidden sm:table-cell">
                                <span class="text-xs {{ $supplier->type === 'company' ? 'text-blue-600' : 'text-purple-600' }}">
                                    {{ $supplier->type === 'company' ? 'Empresa' : 'Persona' }}
                                </span>
                            </td>
                            <td class="px-5 py-3 font-mono text-xs text-gray-600 hidden sm:table-cell">{{ $supplier->rfc ?? '—' }}</td>
                            <td class="px-5 py-3 text-gray-600 hidden md:table-cell">{{ $supplier->primary_phone ?? '—' }}</td>
                            <td class="px-5 py-3 text-gray-600 hidden md:table-cell">{{ $supplier->contacts_count }}</td>
                            <td class="px-5 py-3">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                                    {{ $supplier->status === 'active' ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                    {{ \App\Models\Supplier::STATUS[$supplier->status] ?? $supplier->status }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a wire:navigate href="{{ route('suppliers.show', $supplier) }}"
                                        class="text-xs text-gray-500 hover:text-gray-800 font-medium">Ver</a>
                                    <a wire:navigate href="{{ route('suppliers.edit', $supplier) }}"
                                        class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Editar</a>
                                    <button wire:click="confirmDelete({{ $supplier->id }})"
                                        class="text-xs text-red-500 hover:text-red-700 font-medium">Eliminar</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center text-gray-400 text-sm">
                                No se encontraron proveedores.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($suppliers->hasPages())
            <div class="px-5 py-3 border-t border-gray-100">
                {{ $suppliers->links() }}
            </div>
        @endif
    </div>

    @if($confirmingDelete)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-xl border border-gray-200 p-6 w-full max-w-sm mx-4">
                <h3 class="font-medium text-gray-900 mb-1">¿Eliminar proveedor?</h3>
                <p class="text-sm text-gray-500 mb-5">Esta acción no se puede deshacer.</p>
                <div class="flex gap-3 justify-end">
                    <button wire:click="cancelDelete" class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50">Cancelar</button>
                    <button wire:click="delete" class="px-4 py-2 text-sm bg-red-600 hover:bg-red-700 text-white rounded-lg">Sí, eliminar</button>
                </div>
            </div>
        </div>
    @endif
</div>

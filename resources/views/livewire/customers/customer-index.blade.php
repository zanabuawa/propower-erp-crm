<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-medium text-gray-900">Clientes</h1>
            <p class="text-sm text-gray-500 mt-0.5">Gestiona tu cartera de clientes</p>
        </div>
        <a href="{{ route('contacts.create') }}"
            class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
            + Nuevo cliente
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-wrap gap-3 mb-4">
        <input wire:model.live.debounce.300ms="search" type="text"
            placeholder="Buscar por nombre o RFC..."
            class="flex-1 min-w-[200px] border border-gray-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
        <select wire:model.live="filterStatus"
            class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
            <option value="">Todos los estados</option>
            <option value="prospect">Prospecto</option>
            <option value="active">Activo</option>
            <option value="inactive">Inactivo</option>
        </select>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50">
                    <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Cliente</th>
                    <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">RFC</th>
                    <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Teléfono</th>
                    <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Contactos</th>
                    <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Estado</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($customers as $customer)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                @if($customer->image)
                                    <img src="{{ Storage::url($customer->image) }}"
                                        class="w-9 h-9 rounded-full object-cover border border-gray-100">
                                @else
                                    <div class="w-9 h-9 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-semibold text-xs">
                                        {{ strtoupper(substr($customer->name, 0, 2)) }}
                                    </div>
                                @endif
                                <div>
                                    <p class="font-medium text-gray-900">{{ $customer->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $customer->primary_email ?? $customer->city }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3 font-mono text-xs text-gray-600">{{ $customer->rfc ?? '—' }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ $customer->primary_phone ?? '—' }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ $customer->contacts_count }}</td>
                        <td class="px-5 py-3">
                            @php
                                $statusColors = [
                                    'prospect' => 'bg-amber-50 text-amber-700',
                                    'active'   => 'bg-green-50 text-green-700',
                                    'inactive' => 'bg-gray-100 text-gray-500',
                                ];
                            @endphp
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$customer->status] ?? '' }}">
                                {{ \App\Models\Customer::STATUS[$customer->status] ?? $customer->status }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('contacts.show', $customer) }}"
                                    class="text-xs text-gray-500 hover:text-gray-800 font-medium">Ver</a>
                                <a href="{{ route('contacts.edit', $customer) }}"
                                    class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Editar</a>
                                <button wire:click="confirmDelete({{ $customer->id }})"
                                    class="text-xs text-red-500 hover:text-red-700 font-medium">Eliminar</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-gray-400 text-sm">
                            No se encontraron clientes.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($customers->hasPages())
            <div class="px-5 py-3 border-t border-gray-100">
                {{ $customers->links() }}
            </div>
        @endif
    </div>

    @if($confirmingDelete)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl border border-gray-200 p-6 w-full max-w-sm mx-4">
                <h3 class="font-medium text-gray-900 mb-1">¿Eliminar cliente?</h3>
                <p class="text-sm text-gray-500 mb-5">Esta acción no se puede deshacer.</p>
                <div class="flex gap-3 justify-end">
                    <button wire:click="cancelDelete" class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50">Cancelar</button>
                    <button wire:click="delete" class="px-4 py-2 text-sm bg-red-600 hover:bg-red-700 text-white rounded-lg">Sí, eliminar</button>
                </div>
            </div>
        </div>
    @endif
</div>
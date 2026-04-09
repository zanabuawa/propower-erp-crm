<div>
    <x-page-header title="Proveedores" description="Gestiona tu cartera de proveedores">
        <x-slot:actions>
            <a wire:navigate href="{{ route('suppliers.create') }}"
                class="inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nuevo proveedor
            </a>
        </x-slot:actions>
    </x-page-header>

    <x-alert />

    {{-- Barra de búsqueda + filtros --}}
    <div class="space-y-3 mb-5">
        {{-- Fila 1: búsqueda y filtros básicos --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="relative sm:col-span-2">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input wire:model.live.debounce.300ms="search" type="text"
                    placeholder="Buscar por nombre, RFC o ciudad..."
                    aria-label="Buscar proveedores"
                    class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-transparent transition">
            </div>
            <select wire:model.live="filterType" aria-label="Filtrar por tipo"
                class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
                <option value="">Todos los tipos</option>
                <option value="person">Persona física</option>
                <option value="company">Empresa</option>
            </select>
            <select wire:model.live="filterStatus" aria-label="Filtrar por estado"
                class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
                <option value="">Todos los estados</option>
                <option value="active">Activo</option>
                <option value="inactive">Inactivo</option>
            </select>
        </div>

        {{-- Fila 2: filtros de rubro y ubicación --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <select wire:model.live="filterServiceType" aria-label="Filtrar por tipo de servicio"
                class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
                <option value="">Todo tipo de servicio</option>
                @foreach(\App\Models\Supplier::SERVICE_TYPES as $val => $label)
                    <option value="{{ $val }}">{{ $label }}</option>
                @endforeach
            </select>
            <select wire:model.live="filterCategory" aria-label="Filtrar por rubro"
                class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
                <option value="">Todos los rubros</option>
                @foreach(\App\Models\Supplier::CATEGORIES as $val => $label)
                    <option value="{{ $val }}">{{ $label }}</option>
                @endforeach
            </select>
            <select wire:model.live="filterCountry" aria-label="Filtrar por país"
                class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
                <option value="">Todos los países</option>
                @foreach($countries as $country)
                    <option value="{{ $country }}">{{ $country }}</option>
                @endforeach
            </select>
            <select wire:model.live="filterState" aria-label="Filtrar por estado"
                class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white"
                @disabled(!$filterCountry && $states->isEmpty())>
                <option value="">{{ $filterCountry ? 'Todos los estados' : 'Todos los estados' }}</option>
                @foreach($states as $state)
                    <option value="{{ $state }}">{{ $state }}</option>
                @endforeach
            </select>
            <div class="flex items-center gap-2">
                <select wire:model.live="filterCity" aria-label="Filtrar por ciudad"
                    class="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white"
                    @disabled(!$filterState && $cities->isEmpty())>
                    <option value="">Todas las ciudades</option>
                    @foreach($cities as $city)
                        <option value="{{ $city }}">{{ $city }}</option>
                    @endforeach
                </select>
                @if(count($activeFilters) > 0 || $search)
                    <button wire:click="clearFilters" type="button"
                        title="Limpiar filtros"
                        class="flex-shrink-0 p-2 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                @endif
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[580px]">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Proveedor</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden sm:table-cell">Tipo</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden lg:table-cell">Rubro</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden sm:table-cell">RFC</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden md:table-cell">Teléfono</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden md:table-cell">Contactos</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Estado</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($suppliers as $supplier)
                        <tr class="hover:bg-gray-50 transition group">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    @if($supplier->image)
                                        <img src="{{ Storage::url($supplier->image) }}"
                                            class="w-9 h-9 rounded-full object-cover border border-gray-100 flex-shrink-0"
                                            alt="{{ $supplier->name }}">
                                    @else
                                        <div class="w-9 h-9 rounded-full bg-teal-100 flex items-center justify-center text-teal-600 font-semibold text-xs flex-shrink-0"
                                            aria-hidden="true">
                                            {{ strtoupper(substr($supplier->name, 0, 2)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $supplier->name }}</p>
                                        <p class="text-xs text-gray-400">{{ $supplier->primary_email ?? $supplier->city }}</p>
                                        <p class="text-xs text-gray-400 sm:hidden mt-0.5">
                                            {{ $supplier->type === 'company' ? 'Empresa' : 'Persona' }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3 hidden sm:table-cell">
                                <span class="text-xs font-medium {{ $supplier->type === 'company' ? 'text-blue-600' : 'text-purple-600' }}">
                                    {{ $supplier->type === 'company' ? 'Empresa' : 'Persona' }}
                                </span>
                            </td>
                            <td class="px-5 py-3 hidden lg:table-cell">
                                <div class="space-y-1">
                                    @if($supplier->supplier_category)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-teal-50 text-teal-700">
                                            {{ \App\Models\Supplier::CATEGORIES[$supplier->supplier_category] ?? $supplier->supplier_category }}
                                        </span>
                                    @endif
                                    @if($supplier->service_type)
                                        @php
                                            $stColors = [
                                                'product_supplier'   => 'bg-blue-50 text-blue-700',
                                                'service_contractor' => 'bg-amber-50 text-amber-700',
                                                'both'               => 'bg-purple-50 text-purple-700',
                                            ];
                                        @endphp
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium {{ $stColors[$supplier->service_type] ?? 'bg-gray-100 text-gray-600' }}">
                                            {{ \App\Models\Supplier::SERVICE_TYPES[$supplier->service_type] ?? $supplier->service_type }}
                                        </span>
                                    @endif
                                    @if(!$supplier->supplier_category && !$supplier->service_type)
                                        <span class="text-xs text-gray-400">—</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-3 font-mono text-xs text-gray-600 hidden sm:table-cell">{{ $supplier->rfc ?? '—' }}</td>
                            <td class="px-5 py-3 text-gray-600 hidden md:table-cell">{{ $supplier->primary_phone ?? '—' }}</td>
                            <td class="px-5 py-3 text-gray-600 hidden md:table-cell">{{ $supplier->contacts_count }}</td>
                            <td class="px-5 py-3">
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $supplier->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                                    {{ \App\Models\Supplier::STATUS[$supplier->status] ?? $supplier->status }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition">
                                    <a wire:navigate href="{{ route('suppliers.show', $supplier) }}"
                                        class="text-xs text-gray-500 hover:text-gray-800 font-medium px-2 py-1 rounded hover:bg-gray-50 transition">Ver</a>
                                    <a wire:navigate href="{{ route('suppliers.edit', $supplier) }}"
                                        class="text-xs text-indigo-600 hover:text-indigo-800 font-medium px-2 py-1 rounded hover:bg-indigo-50 transition">Editar</a>
                                    <button wire:click="confirmDelete({{ $supplier->id }})"
                                        class="text-xs text-red-500 hover:text-red-700 font-medium px-2 py-1 rounded hover:bg-red-50 transition">Eliminar</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8"><x-empty-state message="No se encontraron proveedores." /></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($suppliers->hasPages())
            <div class="px-5 py-3 border-t border-gray-100">{{ $suppliers->links() }}</div>
        @endif
    </div>

    <x-delete-modal
        :show="$confirmingDelete"
        title="¿Eliminar proveedor?"
        description="Esta acción no se puede deshacer."
    />
</div>

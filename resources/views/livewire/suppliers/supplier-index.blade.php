<div class="min-h-screen bg-slate-50/50 -m-4 sm:-m-6 lg:-m-8">
    {{-- STICKY HEADER --}}
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 max-w-full mx-auto">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-9 h-9 rounded-xl bg-orange-600 flex items-center justify-center text-white shrink-0 shadow-lg shadow-orange-600/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2 1 7-1m0 0V8m0 8l2.5 1L21 9l-2-4h-6"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl font-bold text-slate-800 truncate">Proveedores</h1>
                    <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">Gestiona tu cartera de proveedores</p>
                </div>
            </div>
            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <a wire:navigate href="{{ route('suppliers.create') }}"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:scale-[1.02] active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Nuevo proveedor</span>
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-full mx-auto p-4 sm:p-6 lg:p-8 space-y-8">
        @if(session('success'))
            <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl animate-in fade-in slide-in-from-top-4 duration-300">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-sm font-semibold">{{ session('success') }}</p>
            </div>
        @endif

        {{-- Filters --}}
        <div class="bg-white p-4 rounded-3xl border border-slate-200/60 shadow-sm space-y-3">
            {{-- Fila 1: búsqueda y filtros básicos --}}
            <div class="flex flex-wrap gap-3 items-center">
                <div class="flex-1 min-w-[280px] relative group">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-indigo-500 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar por nombre, RFC o ciudad..."
                        class="w-full pl-11 pr-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 text-sm">
                </div>
                <select wire:model.live="filterType"
                    class="px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 text-sm font-bold text-slate-600">
                    <option value="">Todos los tipos</option>
                    <option value="person">Persona física</option>
                    <option value="company">Empresa</option>
                </select>
                <select wire:model.live="filterStatus"
                    class="px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 text-sm font-bold text-slate-600">
                    <option value="">Todos los estados</option>
                    <option value="active">Activo</option>
                    <option value="inactive">Inactivo</option>
                </select>
            </div>

            {{-- Fila 2: filtros de rubro y ubicación --}}
            <div class="flex flex-wrap gap-3 items-center">
                <select wire:model.live="filterServiceType"
                    class="px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 text-sm font-bold text-slate-600">
                    <option value="">Todo tipo de servicio</option>
                    @foreach(\App\Models\Supplier::SERVICE_TYPES as $val => $label)
                        <option value="{{ $val }}">{{ $label }}</option>
                    @endforeach
                </select>
                <select wire:model.live="filterCategory"
                    class="px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 text-sm font-bold text-slate-600">
                    <option value="">Todos los rubros</option>
                    @foreach(\App\Models\Supplier::CATEGORIES as $val => $label)
                        <option value="{{ $val }}">{{ $label }}</option>
                    @endforeach
                </select>
                <select wire:model.live="filterCountry"
                    class="px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 text-sm font-bold text-slate-600">
                    <option value="">Todos los países</option>
                    @foreach($countries as $country)
                        <option value="{{ $country }}">{{ $country }}</option>
                    @endforeach
                </select>
                <select wire:model.live="filterState"
                    class="px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 text-sm font-bold text-slate-600"
                    @disabled(!$filterCountry && $states->isEmpty())>
                    <option value="">{{ $filterCountry ? 'Todos los estados' : 'Todos los estados' }}</option>
                    @foreach($states as $state)
                        <option value="{{ $state }}">{{ $state }}</option>
                    @endforeach
                </select>
                <div class="flex items-center gap-2">
                    <select wire:model.live="filterCity"
                        class="px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 text-sm font-bold text-slate-600"
                        @disabled(!$filterState && $cities->isEmpty())>
                        <option value="">Todas las ciudades</option>
                        @foreach($cities as $city)
                            <option value="{{ $city }}">{{ $city }}</option>
                        @endforeach
                    </select>
                    @if(count($activeFilters) > 0 || $search)
                        <button wire:click="clearFilters" type="button"
                            title="Limpiar filtros"
                            class="shrink-0 p-2.5 rounded-2xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100">
                            <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Proveedor</th>
                            <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest hidden sm:table-cell">Tipo</th>
                            <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest hidden lg:table-cell">Rubro</th>
                            <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest hidden sm:table-cell">RFC</th>
                            <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest hidden md:table-cell">Teléfono</th>
                            <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest hidden md:table-cell">Contactos</th>
                            <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Estado</th>
                            <th class="px-6 py-4 text-right text-[10px] font-bold text-slate-400 uppercase tracking-widest">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($suppliers as $supplier)
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        @if($supplier->image)
                                            <img src="{{ Storage::url($supplier->image) }}"
                                                class="w-9 h-9 rounded-xl object-cover border border-slate-100 shrink-0"
                                                alt="{{ $supplier->name }}">
                                        @else
                                            <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 font-bold text-xs shrink-0">
                                                {{ strtoupper(substr($supplier->name, 0, 2)) }}
                                            </div>
                                        @endif
                                        <div>
                                            <p class="text-sm font-bold text-slate-700">{{ $supplier->name }}</p>
                                            <p class="text-[10px] font-medium text-slate-400 uppercase tracking-tight">{{ $supplier->primary_email ?? $supplier->city }}</p>
                                            <p class="text-[10px] font-medium text-slate-400 uppercase tracking-tight sm:hidden mt-0.5">
                                                {{ $supplier->type === 'company' ? 'Empresa' : 'Persona' }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 hidden sm:table-cell">
                                    <span class="text-xs font-medium {{ $supplier->type === 'company' ? 'text-blue-600' : 'text-purple-600' }}">
                                        {{ $supplier->type === 'company' ? 'Empresa' : 'Persona' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 hidden lg:table-cell">
                                    <div class="space-y-1">
                                        @if($supplier->supplier_category)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-bold bg-teal-50 text-teal-700 border border-teal-200">
                                                {{ \App\Models\Supplier::CATEGORIES[$supplier->supplier_category] ?? $supplier->supplier_category }}
                                            </span>
                                        @endif
                                        @if($supplier->service_type)
                                            @php
                                                $stColors = [
                                                    'product_supplier'   => 'bg-blue-50 text-blue-700 border border-blue-200',
                                                    'service_contractor' => 'bg-amber-50 text-amber-700 border border-amber-200',
                                                    'both'               => 'bg-purple-50 text-purple-700 border border-purple-200',
                                                ];
                                            @endphp
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-bold {{ $stColors[$supplier->service_type] ?? 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                                                {{ \App\Models\Supplier::SERVICE_TYPES[$supplier->service_type] ?? $supplier->service_type }}
                                            </span>
                                        @endif
                                        @if(!$supplier->supplier_category && !$supplier->service_type)
                                            <span class="text-xs text-slate-400">—</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-mono text-xs text-slate-600 hidden sm:table-cell">{{ $supplier->rfc ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm text-slate-600 hidden md:table-cell">{{ $supplier->primary_phone ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm text-slate-600 hidden md:table-cell">{{ $supplier->contacts_count }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider {{ $supplier->status === 'active' ? 'bg-emerald-50 text-emerald-600 border border-emerald-200' : 'bg-slate-100 text-slate-400 border border-slate-200' }}">
                                        {{ \App\Models\Supplier::STATUS[$supplier->status] ?? $supplier->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a wire:navigate href="{{ route('suppliers.show', $supplier) }}" class="p-2 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 hover:shadow-sm transition-all" title="Ver">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                        <a wire:navigate href="{{ route('suppliers.edit', $supplier) }}" class="p-2 rounded-xl text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 hover:shadow-sm transition-all" title="Editar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        </a>
                                        <button wire:click="confirmDelete({{ $supplier->id }})" class="p-2 rounded-xl text-slate-400 hover:text-red-500 hover:bg-red-50 transition-all" title="Eliminar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <svg class="w-10 h-10 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2 1 7-1m0 0V8m0 8l2.5 1L21 9l-2-4h-6"/></svg>
                                        <p class="text-slate-400 text-sm font-medium">No se encontraron proveedores.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($suppliers->hasPages())
                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/30">
                    {{ $suppliers->links() }}
                </div>
            @endif
        </div>
    </div>

    <x-delete-modal
        :show="$confirmingDelete"
        title="¿Eliminar proveedor?"
        description="Esta acción no se puede deshacer."
    />
</div>

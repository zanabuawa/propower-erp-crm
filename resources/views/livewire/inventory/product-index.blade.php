<div>
    {{-- Header --}}
    <x-page-header title="Productos y servicios" description="Catálogo de productos, materiales y servicios">
        <x-slot:actions>
            <a wire:navigate href="{{ route('inventory.general') }}"
                class="inline-flex items-center gap-2 text-sm bg-white hover:bg-gray-50 border border-gray-300 hover:border-gray-400 px-3 py-2 rounded-xl transition-all duration-200 text-gray-700 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Existencias
            </a>
            <a wire:navigate href="{{ route('inventory.warehouse-stock') }}"
                class="inline-flex items-center gap-2 text-sm bg-white hover:bg-gray-50 border border-gray-300 hover:border-gray-400 px-3 py-2 rounded-xl transition-all duration-200 text-gray-700 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                Por almacén
            </a>
            <a wire:navigate href="{{ route('inventory.categories.index') }}"
                class="inline-flex items-center gap-2 text-sm bg-white hover:bg-gray-50 border border-gray-300 hover:border-gray-400 px-3 py-2 rounded-xl transition-all duration-200 text-gray-700 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                </svg>
                Categorías
            </a>
            <a wire:navigate href="{{ route('inventory.warehouses.index') }}"
                class="inline-flex items-center gap-2 text-sm bg-white hover:bg-gray-50 border border-gray-300 hover:border-gray-400 px-3 py-2 rounded-xl transition-all duration-200 text-gray-700 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                </svg>
                Almacenes
            </a>
            <a wire:navigate href="{{ route('inventory.products.create') }}"
                class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-medium px-5 py-2 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:scale-105">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nuevo
            </a>
        </x-slot:actions>
    </x-page-header>

    <x-alert />

    {{-- Filtros --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-5 mb-6 shadow-sm">
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1 relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input wire:model.live.debounce.300ms="search" type="text"
                    placeholder="Buscar por nombre, SKU o código..."
                    aria-label="Buscar productos"
                    class="w-full pl-9 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all appearance-none">
            </div>
            
            <div class="relative">
                <select wire:model.live="filterType" aria-label="Filtrar por tipo"
                    class="px-4 py-2.5 pr-10 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all hover:bg-gray-100 appearance-none">
                    <option value="">Todos los tipos</option>
                    <option value="product">Productos</option>
                    <option value="service">Servicios</option>
                </select>
                <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>

            <div class="relative">
                <select wire:model.live="filterCategory" aria-label="Filtrar por categoría"
                    class="px-4 py-2.5 pr-10 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all hover:bg-gray-100 appearance-none">
                    <option value="">Todas las categorías</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
                <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>

            <div class="relative">
                <select wire:model.live="filterStatus" aria-label="Filtrar por estado"
                    class="px-4 py-2.5 pr-10 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all hover:bg-gray-100 appearance-none">
                    <option value="">Todos los estados</option>
                    <option value="1">Activos</option>
                    <option value="0">Inactivos</option>
                </select>
                <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- VISTA MÓVIL: Tarjetas --}}
    <div class="space-y-4 lg:hidden">
        @forelse($products as $product)
            @php $isService = $product->type === 'service'; @endphp
            @php $totalStock = $isService ? null : $product->stocks->sum('quantity'); @endphp
            <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm hover:shadow-md transition-all duration-300">
                <div class="flex items-start gap-4">
                    {{-- Imagen / Icono de servicio --}}
                    @if($isService && $product->primaryImage)
                        <img src="{{ Storage::url($product->primaryImage->path) }}"
                            class="w-16 h-16 rounded-2xl object-cover border border-violet-200 shadow-sm flex-shrink-0">
                    @elseif($isService)
                        <div class="w-16 h-16 rounded-2xl bg-violet-50 flex items-center justify-center flex-shrink-0 border border-violet-200">
                            <svg class="w-7 h-7 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                    @elseif($product->primaryImage)
                        <img src="{{ Storage::url($product->primaryImage->path) }}"
                            class="w-16 h-16 rounded-2xl object-cover border border-gray-200 shadow-sm flex-shrink-0">
                    @else
                        <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center flex-shrink-0 border border-gray-200">
                            <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <rect x="3" y="3" width="18" height="18" rx="2" stroke-width="1.5"/>
                                <circle cx="8.5" cy="8.5" r="1.5" stroke-width="1.5"/>
                                <path stroke-width="1.5" stroke-linecap="round" d="M21 15l-5-5L5 21"/>
                            </svg>
                        </div>
                    @endif

                    {{-- Información --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-start gap-2">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="font-semibold text-gray-900 truncate">{{ $product->name }}</h3>
                                    <span class="inline-flex px-2 py-0.5 rounded-lg text-xs font-medium
                                        {{ $isService ? 'bg-violet-100 text-violet-700' : 'bg-indigo-100 text-indigo-700' }}">
                                        {{ $isService ? 'Servicio' : 'Producto' }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 font-mono mt-1">{{ $product->sku ?? 'Sin SKU' }}</p>
                                @if($product->brand || $product->model)
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ implode(' · ', array_filter([$product->brand, $product->model])) }}
                                    </p>
                                @endif
                            </div>
                            <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-medium flex-shrink-0
                                {{ $product->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $product->is_active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </div>

                        {{-- Grid de información --}}
                        <div class="grid grid-cols-2 gap-3 mt-4 text-sm">
                            <div class="bg-gray-50 rounded-lg p-2">
                                <p class="text-xs text-gray-500">Categoría</p>
                                @if($product->category)
                                    <div class="flex items-center gap-1.5 mt-1">
                                        <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $product->category->color }}"></span>
                                        <span class="text-xs text-gray-700 truncate">{{ $product->category->name }}</span>
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </div>
                            <div class="bg-gray-50 rounded-lg p-2">
                                <p class="text-xs text-gray-500">Unidad</p>
                                <p class="text-xs text-gray-700 mt-1 font-medium">{{ $product->unitOfMeasure?->abbreviation ?? 'unidad' }}</p>
                            </div>
                            @can('view prices')
                            <div class="bg-gray-50 rounded-lg p-2">
                                <p class="text-xs text-gray-500">Precio compra</p>
                                <p class="text-sm text-gray-700 mt-1">${{ number_format($product->purchase_price, 2) }}</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-2">
                                <p class="text-xs text-gray-500">Precio venta</p>
                                <p class="text-sm font-bold text-indigo-600 mt-1">${{ number_format($product->sale_price, 2) }}</p>
                            </div>
                            @endcan
                            @if(!$isService)
                                <div class="col-span-2 bg-gray-50 rounded-lg p-2">
                                    <p class="text-xs text-gray-500">Stock total</p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="font-semibold {{ $totalStock <= $product->min_stock ? 'text-red-600' : 'text-gray-900' }}">
                                            {{ number_format($totalStock, 2) }}
                                        </span>
                                        @if($totalStock <= $product->min_stock && $product->min_stock > 0)
                                            <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                            </svg>
                                            <span class="text-xs text-red-600">bajo mínimo</span>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Acciones --}}
                        <div class="flex gap-2 mt-4 pt-3 border-t border-gray-100">
                            <a wire:navigate href="{{ route('inventory.products.edit', $product) }}"
                                class="flex-1 text-center text-sm text-indigo-600 hover:text-white font-medium py-2 px-3 rounded-xl hover:bg-indigo-600 transition-all duration-200">
                                Editar
                            </a>
                            <button wire:click="confirmDelete({{ $product->id }})"
                                class="flex-1 text-center text-sm text-red-600 hover:text-white font-medium py-2 px-3 rounded-xl hover:bg-red-600 transition-all duration-200">
                                Eliminar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center">
                <x-empty-state message="No se encontraron productos." />
            </div>
        @endforelse

        {{-- Paginación móvil --}}
        @if($products->hasPages())
            <div class="mt-6">
                {{ $products->links() }}
            </div>
        @endif
    </div>

    {{-- VISTA ESCRITORIO: Tabla --}}
    <div class="hidden lg:block bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="text-left px-6 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Nombre</th>
                        <th class="text-left px-6 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">SKU</th>
                        <th class="text-left px-6 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Tipo / Categoría</th>
                        @can('view prices')
                        <th class="text-right px-6 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Precio compra</th>
                        <th class="text-right px-6 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Precio venta</th>
                        @endcan
                        <th class="text-right px-6 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Stock</th>
                        <th class="text-center px-6 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Estado</th>
                        <th class="text-right px-6 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($products as $product)
                        @php $isService = $product->type === 'service'; @endphp
                        @php $totalStock = $isService ? null : $product->stocks->sum('quantity'); @endphp
                        <tr class="hover:bg-gray-50 transition-all duration-200 group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    @if($isService && $product->primaryImage)
                                        <img src="{{ Storage::url($product->primaryImage->path) }}"
                                            class="w-12 h-12 rounded-xl object-cover border border-violet-200 shadow-sm flex-shrink-0">
                                    @elseif($isService)
                                        <div class="w-12 h-12 rounded-xl bg-violet-50 flex items-center justify-center flex-shrink-0 border border-violet-200">
                                            <svg class="w-6 h-6 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        </div>
                                    @elseif($product->primaryImage)
                                        <img src="{{ Storage::url($product->primaryImage->path) }}"
                                            class="w-12 h-12 rounded-xl object-cover border border-gray-200 shadow-sm flex-shrink-0">
                                    @else
                                        <div class="w-12 h-12 rounded-xl bg-gray-100 flex items-center justify-center flex-shrink-0 border border-gray-200">
                                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <rect x="3" y="3" width="18" height="18" rx="2" stroke-width="1.5"/>
                                                <circle cx="8.5" cy="8.5" r="1.5" stroke-width="1.5"/>
                                                <path stroke-width="1.5" stroke-linecap="round" d="M21 15l-5-5L5 21"/>
                                            </svg>
                                        </div>
                                    @endif
                                    <div>
                                        <p class="font-semibold text-gray-900">{{ $product->name }}</p>
                                        <div class="flex items-center gap-1.5 mt-0.5 flex-wrap">
                                            <span class="text-xs text-gray-500">{{ $product->unitOfMeasure?->abbreviation ?? 'unidad' }}</span>
                                            @if($product->brand || $product->model)
                                                <span class="text-gray-300">·</span>
                                                <span class="text-xs text-gray-500">
                                                    {{ implode(' ', array_filter([$product->brand, $product->model])) }}
                                                </span>
                                            @endif
                                            @if($product->subcategory)
                                                <span class="text-gray-300">·</span>
                                                <span class="text-xs text-gray-500">{{ $product->subcategory->name }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-mono text-xs text-gray-600">{{ $product->sku ?? '—' }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="space-y-1.5">
                                    <span class="inline-flex px-2 py-0.5 rounded-lg text-xs font-medium
                                        {{ $isService ? 'bg-violet-100 text-violet-700' : 'bg-indigo-100 text-indigo-700' }}">
                                        {{ $isService ? 'Servicio' : 'Producto' }}
                                    </span>
                                    @if($product->category)
                                        <div class="flex items-center gap-1.5">
                                            <span class="w-2 h-2 rounded-full" style="background-color: {{ $product->category->color }}"></span>
                                            <span class="text-xs text-gray-600">{{ $product->category->name }}</span>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            @can('view prices')
                            <td class="px-6 py-4 text-right">
                                <span class="text-gray-700">${{ number_format($product->purchase_price, 2) }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="font-bold text-indigo-600">${{ number_format($product->sale_price, 2) }}</span>
                            </td>
                            @endcan
                            <td class="px-6 py-4 text-right">
                                @if($isService)
                                    <span class="text-xs text-gray-400 italic">N/A</span>
                                @else
                                    <div class="flex items-center justify-end gap-1.5">
                                        <span class="font-semibold {{ $totalStock <= $product->min_stock ? 'text-red-600' : 'text-gray-900' }}">
                                            {{ number_format($totalStock, 2) }}
                                        </span>
                                        @if($totalStock <= $product->min_stock && $product->min_stock > 0)
                                            <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" title="Stock bajo mínimo">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                            </svg>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-medium
                                    {{ $product->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $product->is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a wire:navigate href="{{ route('inventory.products.edit', $product) }}"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm text-indigo-600 hover:text-white bg-indigo-50 hover:bg-indigo-600 rounded-lg transition-all duration-200 font-medium">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Editar
                                    </a>
                                    <button wire:click="confirmDelete({{ $product->id }})"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm text-red-600 hover:text-white bg-red-50 hover:bg-red-600 rounded-lg transition-all duration-200 font-medium">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Eliminar
                                    </button>
                                </div>
                             </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <x-empty-state message="No se encontraron productos." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $products->links() }}
            </div>
        @endif
    </div>

    <x-delete-modal
        :show="$confirmingDelete"
        title="¿Eliminar producto?"
        description="Esta acción eliminará el registro y todo su historial de stock (si aplica)."
    />
</div>
<div>
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Productos y servicios</h1>
            <p class="text-sm text-gray-500 mt-1">Catálogo de productos, materiales y servicios</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('inventory.general') }}"
                class="inline-flex items-center gap-2 text-sm border border-gray-200 hover:border-gray-300 hover:bg-gray-50 px-3 py-2 rounded-lg transition text-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Existencias
            </a>
            <a href="{{ route('inventory.warehouse-stock') }}"
                class="inline-flex items-center gap-2 text-sm border border-gray-200 hover:border-gray-300 hover:bg-gray-50 px-3 py-2 rounded-lg transition text-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                Por almacén
            </a>
            <a href="{{ route('inventory.categories.index') }}"
                class="inline-flex items-center gap-2 text-sm border border-gray-200 hover:border-gray-300 hover:bg-gray-50 px-3 py-2 rounded-lg transition text-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                </svg>
                Categorías
            </a>
            <a href="{{ route('inventory.units.index') }}"
                class="inline-flex items-center gap-2 text-sm border border-gray-200 hover:border-gray-300 hover:bg-gray-50 px-3 py-2 rounded-lg transition text-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-4 0h4"/>
                </svg>
                Unidades
            </a>
            <a href="{{ route('inventory.warehouses.index') }}"
                class="inline-flex items-center gap-2 text-sm border border-gray-200 hover:border-gray-300 hover:bg-gray-50 px-3 py-2 rounded-lg transition text-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                </svg>
                Almacenes
            </a>
            <a href="{{ route('inventory.products.create') }}"
                class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nuevo
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-5 px-4 py-3 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 text-sm rounded-r-lg shadow-sm">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ session('success') }}
            </div>
        </div>
    @endif

    {{-- Filtros --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-5 shadow-sm">
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1 relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input wire:model.live.debounce.300ms="search" type="text"
                    placeholder="Buscar por nombre, SKU o código..."
                    class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-transparent transition">
            </div>
            <select wire:model.live="filterType"
                class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
                <option value="">Todos los tipos</option>
                <option value="product">Productos</option>
                <option value="service">Servicios</option>
            </select>
            <select wire:model.live="filterCategory"
                class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
                <option value="">Todas las categorías</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="filterStatus"
                class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
                <option value="">Todos los estados</option>
                <option value="1">Activos</option>
                <option value="0">Inactivos</option>
            </select>
        </div>
    </div>

    {{-- VISTA MÓVIL: Tarjetas --}}
    <div class="space-y-3 lg:hidden">
        @forelse($products as $product)
            @php $isService = $product->type === 'service'; @endphp
            @php $totalStock = $isService ? null : $product->stocks->sum('quantity'); @endphp
            <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm hover:shadow-md transition">
                <div class="flex items-start gap-3">
                    {{-- Imagen / Icono de servicio --}}
                    @if($isService)
                        <div class="w-14 h-14 rounded-lg bg-violet-50 flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                    @elseif($product->primaryImage)
                        <img src="{{ Storage::url($product->primaryImage->path) }}"
                            class="w-14 h-14 rounded-lg object-cover border border-gray-200 flex-shrink-0">
                    @else
                        <div class="w-14 h-14 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <h3 class="font-medium text-gray-900 truncate">{{ $product->name }}</h3>
                                    <span class="inline-flex px-1.5 py-0.5 rounded text-xs font-medium
                                        {{ $isService ? 'bg-violet-100 text-violet-700' : 'bg-indigo-100 text-indigo-700' }}">
                                        {{ $isService ? 'Servicio' : 'Producto' }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-400 font-mono mt-0.5">{{ $product->sku ?? 'Sin SKU' }}</p>
                                @if($product->brand || $product->model)
                                    <p class="text-xs text-gray-400 mt-0.5">
                                        {{ implode(' · ', array_filter([$product->brand, $product->model])) }}
                                    </p>
                                @endif
                            </div>
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium flex-shrink-0
                                {{ $product->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $product->is_active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </div>

                        {{-- Grid de información --}}
                        <div class="grid grid-cols-2 gap-2 mt-3 text-sm">
                            <div>
                                <p class="text-xs text-gray-400">Categoría</p>
                                @if($product->category)
                                    <div class="flex items-center gap-1 mt-0.5">
                                        <span class="w-2 h-2 rounded-full" style="background-color: {{ $product->category->color }}"></span>
                                        <span class="text-xs text-gray-700 truncate">{{ $product->category->name }}</span>
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </div>
                            <div>
                                <p class="text-xs text-gray-400">Unidad</p>
                                <p class="text-xs text-gray-700 mt-0.5">{{ $product->unitOfMeasure?->abbreviation ?? 'unidad' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400">Precio compra</p>
                                <p class="text-sm text-gray-700 mt-0.5">${{ number_format($product->purchase_price, 2) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400">Precio venta</p>
                                <p class="text-sm font-semibold text-indigo-600 mt-0.5">${{ number_format($product->sale_price, 2) }}</p>
                            </div>
                            @if(!$isService)
                                <div class="col-span-2">
                                    <p class="text-xs text-gray-400">Stock total</p>
                                    <div class="flex items-center gap-1 mt-0.5">
                                        <span class="font-medium {{ $totalStock <= $product->min_stock ? 'text-red-600' : 'text-gray-900' }}">
                                            {{ number_format($totalStock, 2) }}
                                        </span>
                                        @if($totalStock <= $product->min_stock && $product->min_stock > 0)
                                            <svg class="w-3.5 h-3.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                            </svg>
                                            <span class="text-xs text-red-500">bajo mínimo</span>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Acciones --}}
                        <div class="flex gap-2 mt-3 pt-2 border-t border-gray-100">
                            <a href="{{ route('inventory.products.edit', $product) }}"
                                class="flex-1 text-center text-sm text-indigo-600 hover:text-indigo-800 font-medium py-1.5 px-2 rounded-lg hover:bg-indigo-50 transition">
                                Editar
                            </a>
                            <button wire:click="confirmDelete({{ $product->id }})"
                                class="flex-1 text-center text-sm text-red-500 hover:text-red-700 font-medium py-1.5 px-2 rounded-lg hover:bg-red-50 transition">
                                Eliminar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
                <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7h-4.5M20 7l-4.5-4.5M20 7l-4.5 4.5M4 7h4.5M4 7l4.5-4.5M4 7l4.5 4.5M12 3v4.5m0 0v4.5m0-4.5h4.5m-4.5 0H7.5"/>
                </svg>
                <p class="text-gray-500 text-sm">No se encontraron resultados.</p>
            </div>
        @endforelse

        {{-- Paginación móvil --}}
        @if($products->hasPages())
            <div class="mt-4">
                {{ $products->links() }}
            </div>
        @endif
    </div>

    {{-- VISTA ESCRITORIO: Tabla --}}
    <div class="hidden lg:block bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">Nombre</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">SKU</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">Tipo / Categoría</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">Precio compra</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">Precio venta</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">Stock</th>
                        <th class="text-center px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">Estado</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($products as $product)
                        @php $isService = $product->type === 'service'; @endphp
                        @php $totalStock = $isService ? null : $product->stocks->sum('quantity'); @endphp
                        <tr class="hover:bg-gray-50 transition group">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    @if($isService)
                                        <div class="w-10 h-10 rounded-lg bg-violet-50 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-5 h-5 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        </div>
                                    @elseif($product->primaryImage)
                                        <img src="{{ Storage::url($product->primaryImage->path) }}"
                                            class="w-10 h-10 rounded-lg object-cover border border-gray-200 flex-shrink-0">
                                    @else
                                        <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <rect x="3" y="3" width="18" height="18" rx="2" stroke-width="1.5"/>
                                                <circle cx="8.5" cy="8.5" r="1.5" stroke-width="1.5"/>
                                                <path stroke-width="1.5" stroke-linecap="round" d="M21 15l-5-5L5 21"/>
                                            </svg>
                                        </div>
                                    @endif
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $product->name }}</p>
                                        <div class="flex items-center gap-1.5 mt-0.5 flex-wrap">
                                            <span class="text-xs text-gray-400">{{ $product->unitOfMeasure?->abbreviation ?? 'unidad' }}</span>
                                            @if($product->brand || $product->model)
                                                <span class="text-gray-300">·</span>
                                                <span class="text-xs text-gray-400">
                                                    {{ implode(' ', array_filter([$product->brand, $product->model])) }}
                                                </span>
                                            @endif
                                            @if($product->subcategory)
                                                <span class="text-gray-300">·</span>
                                                <span class="text-xs text-gray-400">{{ $product->subcategory->name }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3">
                                <span class="font-mono text-xs text-gray-500">{{ $product->sku ?? '—' }}</span>
                            </td>
                            <td class="px-5 py-3">
                                <div class="space-y-1">
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                                        {{ $isService ? 'bg-violet-100 text-violet-700' : 'bg-indigo-100 text-indigo-700' }}">
                                        {{ $isService ? 'Servicio' : 'Producto' }}
                                    </span>
                                    @if($product->category)
                                        <div class="flex items-center gap-1.5">
                                            <span class="w-2 h-2 rounded-full" style="background-color: {{ $product->category->color }}"></span>
                                            <span class="text-xs text-gray-500">{{ $product->category->name }}</span>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <span class="text-gray-600">${{ number_format($product->purchase_price, 2) }}</span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <span class="font-semibold text-gray-900">${{ number_format($product->sale_price, 2) }}</span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                @if($isService)
                                    <span class="text-xs text-gray-400 italic">N/A</span>
                                @else
                                    <div class="flex items-center justify-end gap-1">
                                        <span class="font-medium {{ $totalStock <= $product->min_stock ? 'text-red-600' : 'text-gray-900' }}">
                                            {{ number_format($totalStock, 2) }}
                                        </span>
                                        @if($totalStock <= $product->min_stock && $product->min_stock > 0)
                                            <svg class="w-3.5 h-3.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" title="Stock bajo mínimo">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                            </svg>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-center">
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $product->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $product->is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition">
                                    <a href="{{ route('inventory.products.edit', $product) }}"
                                        class="p-1.5 text-indigo-600 hover:text-indigo-800 rounded-md hover:bg-indigo-50 transition"
                                        title="Editar">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    <button wire:click="confirmDelete({{ $product->id }})"
                                        class="p-1.5 text-red-500 hover:text-red-700 rounded-md hover:bg-red-50 transition"
                                        title="Eliminar">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                             </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center">
                                <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7h-4.5M20 7l-4.5-4.5M20 7l-4.5 4.5M4 7h4.5M4 7l4.5-4.5M4 7l4.5 4.5M12 3v4.5m0 0v4.5m0-4.5h4.5m-4.5 0H7.5"/>
                                </svg>
                                <p class="text-gray-500 text-sm">No se encontraron resultados.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
            <div class="px-5 py-3 border-t border-gray-100">
                {{ $products->links() }}
            </div>
        @endif
    </div>

    {{-- Modal de confirmación --}}
    @if($confirmingDelete)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl border border-gray-200 p-6 w-full max-w-sm mx-4">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </div>
                    <h3 class="font-medium text-gray-900">¿Eliminar registro?</h3>
                </div>
                <p class="text-sm text-gray-500 mb-5">Esta acción eliminará el registro y todo su historial de stock (si aplica).</p>
                <div class="flex gap-3 justify-end">
                    <button wire:click="cancelDelete"
                        class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition">Cancelar</button>
                    <button wire:click="delete"
                        class="px-4 py-2 text-sm bg-red-600 hover:bg-red-700 text-white rounded-lg transition">Sí, eliminar</button>
                </div>
            </div>
        </div>
    @endif
</div>

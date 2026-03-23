<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-medium text-gray-900">Productos</h1>
            <p class="text-sm text-gray-500 mt-0.5">Catálogo de productos e inventario</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('inventory.categories.index') }}"
                class="text-sm border border-gray-200 hover:bg-gray-50 px-3 py-2 rounded-lg transition text-gray-600">
                Categorías
            </a>
            <a href="{{ route('inventory.units.index') }}"
                class="text-sm border border-gray-200 hover:bg-gray-50 px-3 py-2 rounded-lg transition text-gray-600">
                Unidades
            </a>
            <a href="{{ route('inventory.warehouses.index') }}"
                class="text-sm border border-gray-200 hover:bg-gray-50 px-3 py-2 rounded-lg transition text-gray-600">
                Almacenes
            </a>
            <a href="{{ route('inventory.products.create') }}"
                class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                + Nuevo producto
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    {{-- Filtros --}}
    <div class="flex flex-wrap gap-3 mb-4">
        <input wire:model.live.debounce.300ms="search" type="text"
            placeholder="Buscar por nombre, SKU o código..."
            class="flex-1 min-w-[200px] border border-gray-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
        <select wire:model.live="filterCategory"
            class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
            <option value="">Todas las categorías</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}">{{ $category->name }}</option>
            @endforeach
        </select>
        <select wire:model.live="filterStatus"
            class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
            <option value="">Todos los estados</option>
            <option value="1">Activos</option>
            <option value="0">Inactivos</option>
        </select>
    </div>

    {{-- Tabla --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50">
                    <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Producto</th>
                    <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">SKU</th>
                    <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Categoría</th>
                    <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Precio compra</th>
                    <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Precio venta</th>
                    <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Stock total</th>
                    <th class="text-left px-5 py-3 text-xs font-medium text-gray-500">Estado</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($products as $product)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                @if($product->primaryImage)
                                    <img src="{{ Storage::url($product->primaryImage->path) }}"
                                        class="w-10 h-10 rounded-lg object-cover border border-gray-100">
                                @else
                                    <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <rect x="3" y="3" width="18" height="18" rx="2" stroke-width="1.5"/>
                                            <circle cx="8.5" cy="8.5" r="1.5" stroke-width="1.5"/>
                                            <path stroke-width="1.5" stroke-linecap="round" d="M21 15l-5-5L5 21"/>
                                        </svg>
                                    </div>
                                @endif
                                <div>
                                    <p class="font-medium text-gray-900">{{ $product->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $product->unitOfMeasure?->abbreviation }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            <span class="font-mono text-xs text-gray-600">{{ $product->sku ?? '—' }}</span>
                        </td>
                        <td class="px-5 py-3">
                            @if($product->category)
                                <span class="inline-flex items-center gap-1 text-xs">
                                    <span class="w-2 h-2 rounded-full" style="background-color: {{ $product->category->color }}"></span>
                                    {{ $product->category->name }}
                                </span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-gray-600">${{ number_format($product->purchase_price, 2) }}</td>
                        <td class="px-5 py-3 font-medium text-gray-900">${{ number_format($product->sale_price, 2) }}</td>
                        <td class="px-5 py-3">
                            @php $totalStock = $product->stocks->sum('quantity'); @endphp
                            <span class="font-medium {{ $totalStock <= $product->min_stock ? 'text-red-600' : 'text-gray-900' }}">
                                {{ number_format($totalStock, 2) }}
                            </span>
                            @if($totalStock <= $product->min_stock && $product->min_stock > 0)
                                <span class="text-xs text-red-500 ml-1">bajo mínimo</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                                {{ $product->is_active ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $product->is_active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('inventory.products.edit', $product) }}"
                                    class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Editar</a>
                                <button wire:click="confirmDelete({{ $product->id }})"
                                    class="text-xs text-red-500 hover:text-red-700 font-medium">Eliminar</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-5 py-10 text-center text-gray-400 text-sm">
                            No se encontraron productos.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($products->hasPages())
            <div class="px-5 py-3 border-t border-gray-100">
                {{ $products->links() }}
            </div>
        @endif
    </div>

    @if($confirmingDelete)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl border border-gray-200 p-6 w-full max-w-sm mx-4">
                <h3 class="font-medium text-gray-900 mb-1">¿Eliminar producto?</h3>
                <p class="text-sm text-gray-500 mb-5">Se eliminará el producto y todo su historial de stock.</p>
                <div class="flex gap-3 justify-end">
                    <button wire:click="cancelDelete"
                        class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50">Cancelar</button>
                    <button wire:click="delete"
                        class="px-4 py-2 text-sm bg-red-600 hover:bg-red-700 text-white rounded-lg">Sí, eliminar</button>
                </div>
            </div>
        </div>
    @endif
</div>
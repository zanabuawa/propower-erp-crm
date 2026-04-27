<div>
    {{-- Botón trigger --}}
    <button type="button" wire:click="open"
        class="inline-flex items-center gap-2 px-3 py-2 text-sm border border-dashed border-indigo-300 text-indigo-600 hover:bg-indigo-50 rounded-lg transition font-medium">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
        </svg>
        Ver catálogo
    </button>

    {{-- Modal --}}
    @if($isOpen)
        <div
            x-data
            x-on:keydown.escape.window="$wire.close()"
            class="fixed inset-0 z-50 flex flex-col"
            wire:key="picker-modal">

            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-black/50" wire:click="close"></div>

            {{-- Panel --}}
            <div class="relative z-10 flex flex-col bg-white w-full h-full sm:h-auto sm:max-h-[90vh]
                        sm:rounded-2xl sm:shadow-2xl sm:max-w-4xl sm:mx-auto sm:my-auto sm:mt-[5vh]">

                {{-- Header --}}
                <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-200 flex-shrink-0">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                    </svg>
                    <div class="flex-1">
                        <h2 class="text-base font-semibold text-gray-900">
                            {{ $warehouseId ? 'Productos disponibles' : 'Catálogo de productos' }}
                        </h2>
                        @if($warehouse)
                            <p class="text-xs text-gray-400 mt-0.5">Almacén: {{ $warehouse->name }}</p>
                        @endif
                    </div>
                    <button type="button" wire:click="close" class="text-gray-400 hover:text-gray-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Barra de búsqueda y filtros --}}
                <div class="px-5 py-3 border-b border-gray-100 flex-shrink-0 space-y-2">
                    {{-- Search --}}
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input
                            wire:model.live.debounce.250ms="search"
                            type="text"
                            placeholder="Buscar por nombre, SKU o código de barras..."
                            autofocus
                            class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-lg text-sm
                                   focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-transparent">
                    </div>

                    {{-- Filtros --}}
                    <div class="flex gap-2 flex-wrap">
                        <select wire:model.live="categoryId"
                            class="px-3 py-1.5 border border-gray-200 rounded-lg text-xs bg-white
                                   focus:outline-none focus:ring-2 focus:ring-indigo-300 text-gray-600">
                            <option value="">Todas las categorías</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        @if(!$warehouseId)
                        <select wire:model.live="supplierId"
                            class="px-3 py-1.5 border border-gray-200 rounded-lg text-xs bg-white
                                   focus:outline-none focus:ring-2 focus:ring-indigo-300 text-gray-600">
                            <option value="">Todos los proveedores</option>
                            @foreach($suppliers as $sup)
                                <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                            @endforeach
                        </select>
                        @endif
                        @if($search || $categoryId || $supplierId)
                            <button type="button" wire:click="$set('search',''); $set('categoryId', null); $set('supplierId', null)"
                                class="px-3 py-1.5 text-xs text-gray-500 hover:text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                                Limpiar filtros
                            </button>
                        @endif
                        <span class="ml-auto text-xs text-gray-400 self-center">
                            {{ $products->count() }} resultado{{ $products->count() !== 1 ? 's' : '' }}
                        </span>
                    </div>
                </div>

                {{-- Grid de productos --}}
                <div class="flex-1 overflow-y-auto p-4">
                    @if($products->isEmpty())
                        <div class="flex flex-col items-center justify-center h-48 text-center">
                            <svg class="w-10 h-10 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-sm text-gray-400">Sin resultados. Prueba con otro término o filtro.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                            @foreach($products as $product)
                                @php
                                    $isService    = $product->type === 'service';
                                    $displayStock = $warehouseId
                                        ? (float) ($product->stocks->first()?->quantity ?? 0)
                                        : (float) $product->stocks->sum('quantity');
                                    $totalStock   = $displayStock;
                                    $lowStock     = !$isService && !$warehouseId && $totalStock <= $product->min_stock && $product->min_stock > 0;
                                @endphp
                                <button
                                    type="button"
                                    wire:click="pick({{ $product->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="pick({{ $product->id }})"
                                    class="group relative flex flex-col text-left border border-gray-200 rounded-xl
                                           overflow-hidden hover:border-indigo-300 hover:shadow-md transition
                                           focus:outline-none focus:ring-2 focus:ring-indigo-400
                                           bg-white">

                                    {{-- Imagen --}}
                                    <div class="w-full aspect-square bg-gray-50 overflow-hidden flex-shrink-0">
                                        @if($isService)
                                            <div class="w-full h-full flex items-center justify-center bg-violet-50">
                                                <svg class="w-8 h-8 text-violet-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                            </div>
                                        @elseif($product->primaryImage)
                                            <img src="{{ Storage::url($product->primaryImage->path) }}"
                                                class="w-full h-full object-cover group-hover:scale-105 transition duration-200">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center bg-gray-100">
                                                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <rect x="3" y="3" width="18" height="18" rx="2" stroke-width="1.5"/>
                                                    <circle cx="8.5" cy="8.5" r="1.5" stroke-width="1.5"/>
                                                    <path stroke-width="1.5" stroke-linecap="round" d="M21 15l-5-5L5 21"/>
                                                </svg>
                                            </div>
                                        @endif

                                        {{-- Tipo badge --}}
                                        <span class="absolute top-1.5 left-1.5 text-[9px] font-bold px-1.5 py-0.5 rounded
                                            {{ $isService ? 'bg-violet-100 text-violet-700' : 'bg-indigo-100 text-indigo-700' }}">
                                            {{ $isService ? 'SRV' : 'PRD' }}
                                        </span>

                                        {{-- Stock badge --}}
                                        @if(!$isService)
                                            <span class="absolute top-1.5 right-1.5 text-[9px] font-bold px-1.5 py-0.5 rounded
                                                {{ $lowStock ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700' }}">
                                                {{ number_format($totalStock, 0) }}
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Info --}}
                                    <div class="p-2.5 flex-1 flex flex-col gap-0.5">
                                        <p class="text-xs font-semibold text-gray-900 leading-tight line-clamp-2">
                                            {{ $product->name }}
                                        </p>
                                        <p class="text-[10px] text-gray-400 font-mono">{{ $product->sku ?? '—' }}</p>
                                        @if($product->category)
                                            <div class="flex items-center gap-1 mt-0.5">
                                                <span class="w-1.5 h-1.5 rounded-full flex-shrink-0"
                                                    style="background-color: {{ $product->category->color ?? '#94a3b8' }}"></span>
                                                <span class="text-[10px] text-gray-400 truncate">{{ $product->category->name }}</span>
                                            </div>
                                        @endif
                                        @if($warehouseId)
                                            <p class="text-sm font-bold text-emerald-600 mt-auto pt-1">
                                                Stock: {{ number_format($displayStock, 2) }}
                                            </p>
                                        @else
                                            <p class="text-sm font-bold text-indigo-600 mt-auto pt-1">
                                                ${{ number_format($product->sale_price, 2) }}
                                            </p>
                                        @endif
                                    </div>

                                    {{-- Hover overlay add --}}
                                    <div class="absolute inset-0 bg-indigo-600/0 group-hover:bg-indigo-600/5 transition flex items-center justify-center">
                                        <span class="transition bg-indigo-600 text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-lg">
                                            + Agregar
                                        </span>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

            </div>
        </div>
    @endif
</div>

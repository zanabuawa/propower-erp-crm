<div>
    <x-page-header title="Existencias por almacén" description="Control detallado de inventario local desglosado por punto de almacenamiento.">
        <x-slot:actions>
            <div class="flex items-center gap-2">
                <button type="button"
                    x-data
                    @click="
                        const base = '{{ route('inventory.warehouse-stock.print') }}';
                        const params = new URLSearchParams();
                        if ($wire.warehouse_id) params.set('warehouse_id', $wire.warehouse_id);
                        if ($wire.search)       params.set('search', $wire.search);
                        if ($wire.category_id)  params.set('category_id', $wire.category_id);
                        if ($wire.stock_filter) params.set('stock_filter', $wire.stock_filter);
                        window.open(base + '?' + params.toString(), '_blank');
                    "
                    class="hidden sm:inline-flex items-center gap-2 text-sm bg-white hover:bg-gray-50 border border-gray-300 px-4 py-2 rounded-xl transition-all duration-200 text-gray-700 shadow-sm font-medium">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Imprimir reporte
                </button>
                <a wire:navigate href="{{ route('inventory.general') }}"
                    class="inline-flex items-center gap-2 text-sm bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 px-4 py-2 rounded-xl transition-all duration-200 text-indigo-700 shadow-sm font-medium group">
                    <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z"/>
                    </svg>
                    Inventario general
                </a>
            </div>
        </x-slot:actions>
    </x-page-header>

    {{-- Selector de almacén: Navegación Estilizada --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-1.5 mb-8 shadow-sm overflow-x-auto scrollbar-hide flex items-center gap-1.5 min-h-[64px]">
        @foreach($warehouses as $wh)
            <button type="button"
                wire:click="$set('warehouse_id', {{ $wh->id }})"
                class="flex items-center gap-2.5 px-4 py-2.5 rounded-xl text-sm font-semibold transition-all duration-300 whitespace-nowrap group
                    {{ $warehouse_id == $wh->id
                        ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200 ring-2 ring-indigo-600/10'
                        : 'text-gray-600 hover:bg-gray-50 hover:text-indigo-600' }}">
                <div class="p-1 rounded-lg {{ $warehouse_id == $wh->id ? 'bg-indigo-500' : 'bg-gray-100 group-hover:bg-indigo-50' }}">
                    <svg class="w-4 h-4 {{ $warehouse_id == $wh->id ? 'text-white' : 'text-gray-500 group-hover:text-indigo-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div class="text-left">
                    <p class="leading-none">{{ $wh->name }}</p>
                    @if($wh->branch)
                        <span class="text-[10px] uppercase tracking-wider opacity-70 block mt-1">{{ $wh->branch->name }}</span>
                    @endif
                </div>
            </button>
        @endforeach
    </div>

    @if($selectedWarehouse)
        {{-- KPIs Modernos --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            {{-- Ubicación --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm relative overflow-hidden group">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 flex items-center justify-center border border-indigo-100 flex-shrink-0">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Ubicación</p>
                        <p class="text-sm font-bold text-gray-900 mt-0.5 truncate">{{ $selectedWarehouse->location ?? 'Sin dirección' }}</p>
                    </div>
                </div>
            </div>

            {{-- Referencias --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm relative overflow-hidden group">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-violet-50 flex items-center justify-center border border-violet-100 flex-shrink-0">
                        <svg class="w-6 h-6 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Productos</p>
                        <p class="text-2xl font-black text-gray-900 mt-0.5">{{ number_format($stocks->count()) }}</p>
                    </div>
                </div>
            </div>

            {{-- Alertas --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm relative overflow-hidden group">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl {{ ($outCount > 0) ? 'bg-red-50' : 'bg-amber-50' }} flex items-center justify-center border {{ ($outCount > 0) ? 'border-red-100' : 'border-amber-100' }} flex-shrink-0">
                        @if($outCount > 0)
                            <span class="absolute -top-1 -right-1 flex h-3 w-3">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                            </span>
                        @endif
                        <svg class="w-6 h-6 {{ ($outCount > 0) ? 'text-red-600' : 'text-amber-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Alertas Stock</p>
                        <div class="flex items-baseline gap-2 mt-0.5">
                            <span class="text-2xl font-black {{ ($outCount > 0) ? 'text-red-600' : 'text-gray-900' }}">{{ $outCount }}</span>
                            <span class="text-gray-300 mx-1">/</span>
                            <span class="text-lg font-bold text-amber-500">{{ $lowCount }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Valor Hero KPI --}}
            <div class="bg-indigo-600 rounded-2xl p-5 shadow-lg shadow-indigo-200 relative overflow-hidden group">
                <div class="absolute right-0 top-0 p-2 opacity-10 group-hover:scale-110 transition-transform duration-500">
                    <svg class="w-20 h-20 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="relative z-10 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center border border-white/30 flex-shrink-0">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-indigo-100 uppercase tracking-widest">Valor total</p>
                        <p class="text-2xl font-black text-white mt-0.5">${{ number_format($totalValue, 2) }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Toolbar de Filtros --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-4 mb-6 shadow-sm">
            <div class="flex flex-col lg:flex-row gap-4">
                <div class="relative flex-grow">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input wire:model.live.debounce.300ms="search" type="text"
                        placeholder="Buscar por nombre, SKU o código de barras..."
                        class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all placeholder-gray-400 font-medium">
                </div>
                <div class="flex flex-wrap sm:flex-nowrap gap-3">
                    <div class="relative min-w-[200px] w-full sm:w-auto">
                        <select wire:model.live="category_id"
                            class="w-full pl-4 pr-10 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 appearance-none cursor-pointer transition-all font-medium text-gray-700">
                            <option value="">Todas las categorías</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                    <div class="relative min-w-[180px] w-full sm:w-auto">
                        <select wire:model.live="stock_filter"
                            class="w-full pl-4 pr-10 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 appearance-none cursor-pointer transition-all font-medium text-gray-700">
                            <option value="">Todos los estados</option>
                            <option value="ok">Stock Óptimo</option>
                            <option value="low">Stock Bajo</option>
                            <option value="out">Sin Existencias</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabla de Productos --}}
        <div class="relative bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden group">
            {{-- Cargando Overlay --}}
            <div wire:loading.flex class="absolute inset-0 bg-white/70 backdrop-blur-[1px] z-20 items-center justify-center">
                <div class="flex flex-col items-center">
                    <div class="w-10 h-10 border-4 border-indigo-600/10 border-t-indigo-600 rounded-full animate-spin"></div>
                    <p class="text-xs font-bold text-indigo-600 mt-3 uppercase tracking-widest">Actualizando...</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="bg-gray-50/80 border-b border-gray-200">
                            <th class="px-6 py-4 text-[11px] font-bold text-gray-500 uppercase tracking-widest">Producto</th>
                            <th class="px-6 py-4 text-[11px] font-bold text-gray-500 uppercase tracking-widest hidden lg:table-cell">Identificación</th>
                            <th class="px-6 py-4 text-[11px] font-bold text-gray-500 uppercase tracking-widest text-right">Existencias</th>
                            @can('view prices')
                            <th class="px-6 py-4 text-[11px] font-bold text-gray-500 uppercase tracking-widest text-right hidden md:table-cell">Precios</th>
                            <th class="px-6 py-4 text-[11px] font-bold text-gray-500 uppercase tracking-widest text-right hidden sm:table-cell">Valor Local</th>
                            @endcan
                            <th class="px-6 py-4 text-[11px] font-bold text-gray-500 uppercase tracking-widest text-center">Estado</th>
                            <th class="px-6 py-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($stocks as $stock)
                            @php
                                $p      = $stock->product;
                                $qty    = $stock->quantity;
                                $isOut  = $qty <= 0;
                                $isLow  = $qty > 0 && $qty <= $p->min_stock;
                                $cost   = (float)$p->purchase_price;
                            @endphp
                            <tr class="hover:bg-indigo-50/30 transition-all duration-150 group/row">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        {{-- Avatar de Categoría --}}
                                        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xs font-bold flex-shrink-0"
                                            style="background-color: {{ $p->category?->color ?? '#E5E7EB' }}20; border: 1px solid {{ $p->category?->color ?? '#E5E7EB' }}40">
                                            <span style="color: {{ $p->category?->color ?? '#9CA3AF' }}">
                                                {{ mb_substr($p->name, 0, 1) }}
                                            </span>
                                        </div>
                                        <div>
                                            <a wire:navigate href="{{ route('inventory.products.edit', $p) }}"
                                                class="text-sm font-bold text-gray-900 hover:text-indigo-600 transition-colors block">
                                                {{ $p->name }}
                                            </a>
                                            <span class="text-[10px] text-gray-400 flex items-center gap-1 mt-0.5">
                                                {{ $p->category?->name ?? 'Sin categoría' }}
                                                @if($p->unitOfMeasure)
                                                    <span class="text-gray-300 mx-1">•</span>
                                                    {{ $p->unitOfMeasure->abbreviation }}
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 hidden lg:table-cell">
                                    <div class="flex flex-col gap-1">
                                        <span class="font-mono text-[11px] text-gray-500 px-2 py-0.5 bg-gray-100 rounded-md w-fit">{{ $p->sku ?? 'S/SKU' }}</span>
                                        @if($p->barcode)
                                            <span class="text-[10px] text-gray-400 font-medium">UPC: {{ $p->barcode }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="inline-block text-right">
                                        <p class="text-base font-black {{ $isOut ? 'text-red-600' : ($isLow ? 'text-amber-600' : 'text-gray-900') }}">
                                            {{ number_format($qty, 2) }}
                                        </p>
                                        <p class="text-[10px] text-gray-400 font-medium -mt-1">Min: {{ number_format($p->min_stock, 2) }}</p>
                                    </div>
                                </td>
                                @can('view prices')
                                <td class="px-6 py-4 text-right hidden md:table-cell">
                                    <div class="text-xs space-y-0.5">
                                        <div class="text-gray-400">Costo: <span class="text-gray-600 font-medium">${{ number_format($p->purchase_price, 2) }}</span></div>
                                        <div class="text-gray-900 font-bold">Venta: ${{ number_format($p->sale_price, 2) }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right hidden sm:table-cell font-black text-indigo-600">
                                    ${{ number_format($qty * $cost, 2) }}
                                </td>
                                @endcan
                                <td class="px-6 py-4 text-center">
                                    @if($isOut)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-red-100 text-red-700 ring-4 ring-red-50">Agotado</span>
                                    @elseif($isLow)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-amber-100 text-amber-700 ring-4 ring-amber-50">Stock Bajo</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-emerald-100 text-emerald-700 ring-4 ring-emerald-50">Óptimo</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a wire:navigate href="{{ route('inventory.products.edit', $p) }}"
                                        class="p-2 text-gray-300 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all opacity-0 group-hover/row:opacity-100">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-6 py-20 text-center">
                                    <div class="max-w-xs mx-auto">
                                        <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-gray-100">
                                            <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                        </div>
                                        <h3 class="text-sm font-bold text-gray-900">Sin existencias</h3>
                                        <p class="text-xs text-gray-500 mt-1">No hay productos que coincidan con los filtros en este almacén.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($stocks->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $stocks->links('vendor.pagination.tailwind') }}
                </div>
            @endif
        </div>
    @else
        {{-- Estado inicial sin selección --}}
        <div class="bg-white rounded-3xl border-2 border-dashed border-gray-200 p-20 text-center shadow-sm">
            <div class="w-20 h-20 rounded-3xl bg-indigo-50 flex items-center justify-center mx-auto mb-6 border border-indigo-100">
                <svg class="w-10 h-10 text-indigo-500 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <h2 class="text-xl font-black text-gray-900 tracking-tight">Selección de Punto de Inventario</h2>
            <p class="text-gray-500 mt-2 max-w-sm mx-auto">Elige un almacén en la barra superior para visualizar las existencias locales, valorización y alertas de stock.</p>
        </div>
    @endif
</div>

<div class="min-h-screen bg-slate-50/50 -m-4 sm:-m-6 lg:-m-8">
    {{-- STICKY HEADER --}}
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 max-w-full mx-auto">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-9 h-9 rounded-xl bg-amber-600 flex items-center justify-center text-white shrink-0 shadow-lg shadow-amber-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl font-bold text-slate-800 truncate">Lotes de inventario</h1>
                    <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">Trazabilidad FIFO/PEPS por lote de producto</p>
                </div>
            </div>
            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <a wire:navigate href="{{ route('inventory.movements.create') }}"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:scale-[1.02] active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Nueva entrada</span>
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
            <div class="flex flex-wrap gap-3 items-center">
                <div class="flex-1 min-w-[280px] relative group">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-indigo-500 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar por lote, código de barras, referencia…"
                        class="w-full pl-11 pr-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 text-sm">
                </div>
                <select wire:model.live="filterStatus"
                    class="px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 text-sm font-bold text-slate-600">
                    <option value="">Todos los estados</option>
                    @foreach($statuses as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                <select wire:model.live="filterWarehouse"
                    class="px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 text-sm font-bold text-slate-600">
                    <option value="">Todos los almacenes</option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                    @endforeach
                </select>
                <button wire:click="clearFilters"
                    class="px-4 py-3 text-sm font-bold text-slate-500 border border-slate-200 rounded-2xl hover:bg-slate-50 transition-colors">
                    Limpiar
                </button>
            </div>

            {{-- Búsqueda de producto --}}
            <div class="relative max-w-sm" x-data>
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </span>
                <input wire:model.live.debounce.300ms="productSearch" type="text" placeholder="Filtrar por producto…"
                    class="w-full pl-11 pr-8 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 text-sm">
                @if($filterProduct)
                    <button wire:click="clearProductFilter" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors" aria-label="Limpiar filtro de producto">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                @endif
                @if(!empty($productResults))
                    <div class="absolute top-full left-0 right-0 mt-1 bg-white border border-slate-200 rounded-2xl shadow-lg z-20 overflow-hidden">
                        @foreach($productResults as $p)
                            <button type="button" wire:click="selectProduct({{ $p['id'] }}, '{{ addslashes($p['name']) }}')"
                                class="w-full text-left px-4 py-3 hover:bg-slate-50 text-sm border-b border-slate-100 last:border-0 transition-colors">
                                <p class="font-bold text-slate-700">{{ $p['name'] }}</p>
                                <p class="text-[10px] text-slate-400 font-mono">{{ $p['sku'] }}</p>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">

            {{-- Mobile --}}
            <div class="divide-y divide-slate-100 lg:hidden">
                @forelse($lots as $lot)
                    @php
                        $statusColors = [
                            'active'   => 'bg-emerald-50 text-emerald-600 border border-emerald-200',
                            'depleted' => 'bg-slate-100 text-slate-400 border border-slate-200',
                            'expired'  => 'bg-red-50 text-red-600 border border-red-200',
                        ];
                        $isExpiringSoon = $lot->expiry_date && $lot->expiry_date->diffInDays() < 30 && $lot->expiry_date->isFuture();
                    @endphp
                    <a wire:navigate href="{{ route('inventory.lots.show', $lot) }}"
                        class="block p-4 hover:bg-slate-50/50 transition-colors">
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <span class="font-mono text-sm font-bold text-slate-700">{{ $lot->lot_number }}</span>
                            <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider {{ $statusColors[$lot->status] ?? 'bg-slate-100 text-slate-400 border border-slate-200' }}">
                                {{ \App\Models\ProductLot::STATUSES[$lot->status] ?? $lot->status }}
                            </span>
                        </div>
                        <p class="text-sm font-medium text-slate-600 truncate">{{ $lot->product->name }}</p>
                        <div class="flex items-center gap-3 mt-1.5 text-[10px] font-medium text-slate-400">
                            <span>{{ $lot->warehouse->name }}</span>
                            <span>·</span>
                            <span>Ingreso: {{ $lot->entry_date->format('d/m/Y') }}</span>
                            <span>·</span>
                            <span class="font-bold {{ (float)$lot->quantity > 0 ? 'text-slate-600' : 'text-red-500' }}">
                                {{ number_format($lot->quantity, 2) }} uds
                            </span>
                        </div>
                        @if($isExpiringSoon)
                            <p class="mt-1.5 text-[10px] text-amber-600 font-bold">Vence: {{ $lot->expiry_date->format('d/m/Y') }}</p>
                        @endif
                    </a>
                @empty
                    <div class="py-16 text-center">
                        <div class="flex flex-col items-center gap-2">
                            <svg class="w-10 h-10 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            <p class="text-slate-400 text-sm font-medium">No se encontraron lotes.</p>
                        </div>
                    </div>
                @endforelse
            </div>

            {{-- Desktop --}}
            <div class="hidden lg:block overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100">
                            <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Lote</th>
                            <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Código de barras</th>
                            <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Producto</th>
                            <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Almacén</th>
                            <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Ingreso</th>
                            <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Vencimiento</th>
                            <th class="px-6 py-4 text-right text-[10px] font-bold text-slate-400 uppercase tracking-widest">Inicial</th>
                            <th class="px-6 py-4 text-right text-[10px] font-bold text-slate-400 uppercase tracking-widest">Disponible</th>
                            <th class="px-6 py-4 text-right text-[10px] font-bold text-slate-400 uppercase tracking-widest">Costo unit.</th>
                            <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Estado</th>
                            <th class="w-12 px-6 py-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($lots as $lot)
                            @php
                                $statusColors = [
                                    'active'   => 'bg-emerald-50 text-emerald-600 border border-emerald-200',
                                    'depleted' => 'bg-slate-100 text-slate-400 border border-slate-200',
                                    'expired'  => 'bg-red-50 text-red-600 border border-red-200',
                                ];
                                $isExpiringSoon = $lot->expiry_date && $lot->expiry_date->diffInDays() < 30 && $lot->expiry_date->isFuture();
                            @endphp
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="px-6 py-4">
                                    <span class="font-mono text-sm font-bold text-slate-700">{{ $lot->lot_number }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-mono text-[10px] font-medium text-slate-400">{{ $lot->barcode ?? '—' }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm font-bold text-slate-700">{{ $lot->product->name }}</p>
                                    <p class="text-[10px] font-medium text-slate-400 font-mono">{{ $lot->product->sku }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm font-medium text-slate-600">{{ $lot->warehouse->name }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm font-medium text-slate-600">{{ $lot->entry_date->format('d/m/Y') }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    @if($lot->expiry_date)
                                        <span class="text-sm font-medium {{ $lot->expiry_date->isPast() ? 'text-red-600' : ($isExpiringSoon ? 'text-amber-600' : 'text-slate-600') }}">
                                            {{ $lot->expiry_date->format('d/m/Y') }}
                                        </span>
                                        @if($isExpiringSoon)
                                            <span class="ml-1 text-[10px] bg-amber-50 text-amber-600 px-1.5 py-0.5 rounded-lg font-black border border-amber-200">Pronto</span>
                                        @endif
                                    @else
                                        <span class="text-slate-300">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="text-sm font-medium text-slate-600">{{ number_format($lot->initial_quantity, 2) }}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="text-sm font-bold {{ (float)$lot->quantity > 0 ? 'text-slate-700' : 'text-red-500' }}">
                                        {{ number_format($lot->quantity, 2) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="text-sm font-medium text-slate-600">${{ number_format($lot->unit_cost, 2) }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider {{ $statusColors[$lot->status] ?? 'bg-slate-100 text-slate-400 border border-slate-200' }}">
                                        {{ \App\Models\ProductLot::STATUSES[$lot->status] ?? $lot->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <a wire:navigate href="{{ route('inventory.lots.show', $lot) }}"
                                        class="p-2 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 hover:shadow-sm transition-all inline-flex" title="Ver">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <svg class="w-10 h-10 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                        <p class="text-slate-400 text-sm font-medium">No se encontraron lotes con los filtros seleccionados.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($lots->hasPages())
                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/30">
                    {{ $lots->links('vendor.pagination.tailwind') }}
                </div>
            @endif
        </div>
    </div>
</div>

<div>
    <x-page-header title="Comparador de precios" description="Consulta, compara e historial de precios por producto">
        <x-slot:actions>
            <a wire:navigate href="{{ route('sales.price-lists.index') }}"
                class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 px-3 py-2 rounded-lg hover:bg-gray-100 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Listas de precios
            </a>
        </x-slot:actions>
    </x-page-header>

    {{-- ── Tabs ──────────────────────────────────────────────────────────────── --}}
    <div class="flex gap-1 bg-gray-100 p-1 rounded-xl mb-6 w-fit">
        <button wire:click="switchTab('products')"
            class="px-4 py-2 rounded-lg text-sm font-medium transition
                {{ $tab === 'products' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
            <span class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                Por producto
            </span>
        </button>
        <button wire:click="switchTab('compare')"
            class="px-4 py-2 rounded-lg text-sm font-medium transition
                {{ $tab === 'compare' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
            <span class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Comparar
            </span>
        </button>
        <button wire:click="switchTab('history')"
            class="px-4 py-2 rounded-lg text-sm font-medium transition
                {{ $tab === 'history' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
            <span class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Historial
            </span>
        </button>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    {{-- TAB: POR PRODUCTO                                                       --}}
    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    @if($tab === 'products')
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
            {{-- Buscador --}}
            <div class="px-5 py-4 border-b border-gray-100">
                <div class="relative max-w-sm">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" wire:model.live.debounce.350ms="searchProduct"
                        placeholder="Buscar por nombre o SKU…"
                        class="pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm w-full focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>
            </div>

            {{-- Tabla --}}
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[700px]">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Producto</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide hidden md:table-cell">Categoría</th>
                            @foreach($priceLists as $pl)
                                <th class="px-4 py-3 text-right text-xs font-semibold text-indigo-600 uppercase tracking-wide whitespace-nowrap">
                                    {{ $pl->name }}<span class="text-gray-400 font-normal ml-1 normal-case">({{ $pl->currency }})</span>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($productsWithPrices as $product)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-5 py-3">
                                    <div class="font-medium text-gray-900">
                                        {{ $product->name }}
                                        @if($product->supplier)
                                            <span class="text-gray-400 font-normal">({{ $product->supplier->name }})</span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-gray-400">{{ $product->sku }}</div>
                                </td>
                                <td class="px-5 py-3 text-gray-500 text-xs hidden md:table-cell">{{ $product->category?->name ?? '—' }}</td>
                                @foreach($priceLists as $pl)
                                    @php
                                        $item = $product->priceListItems->firstWhere('price_list_id', $pl->id);
                                    @endphp
                                    <td class="px-4 py-3 text-right">
                                        @if($item)
                                            <div class="font-semibold text-gray-900">${{ number_format($item->price, 2) }}</div>
                                            @if($item->discount_pct > 0)
                                                <div class="text-xs text-emerald-600">-{{ $item->discount_pct }}%
                                                    <span class="text-gray-400">${{ number_format($item->price * (1 - $item->discount_pct / 100), 2) }}</span>
                                                </div>
                                            @endif
                                        @else
                                            <span class="text-gray-300">—</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 2 + $priceLists->count() }}">
                                    <x-empty-state message="No se encontraron productos." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($productsWithPrices instanceof \Illuminate\Pagination\LengthAwarePaginator && $productsWithPrices->hasPages())
                <div class="px-5 py-3 border-t border-gray-100">{{ $productsWithPrices->links() }}</div>
            @endif
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    {{-- TAB: COMPARADOR                                                         --}}
    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    @if($tab === 'compare')
        {{-- Selección de productos --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            {{-- Producto A --}}
            <div class="bg-white rounded-xl border border-indigo-200 shadow-sm p-4">
                <p class="text-xs font-semibold text-indigo-600 uppercase tracking-wide mb-2">Producto A</p>
                @if($compareProductA)
                    <div class="flex items-center justify-between">
                        <span class="font-medium text-gray-900">{{ $compareNameA }}</span>
                        <button wire:click="clearCompareA" class="text-gray-400 hover:text-red-500 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                @else
                    <div class="relative" x-data>
                        <input type="text" wire:model.live.debounce.300ms="compareSearchA"
                            placeholder="Buscar producto A…"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        @if(count($compareResultsA))
                            <div class="absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                                @foreach($compareResultsA as $r)
                                    <button wire:click="selectCompareA({{ $r['id'] }}, '{{ addslashes($r['name']) }}')"
                                        class="w-full text-left px-3 py-2 text-sm hover:bg-indigo-50 transition">
                                        <span class="font-medium">{{ $r['name'] }}</span>
                                        @if(!empty($r['supplier']))
                                            <span class="text-gray-500 text-xs">({{ $r['supplier'] }})</span>
                                        @endif
                                        <span class="text-gray-400 ml-1 text-xs">{{ $r['sku'] }}</span>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Producto B --}}
            <div class="bg-white rounded-xl border border-violet-200 shadow-sm p-4">
                <p class="text-xs font-semibold text-violet-600 uppercase tracking-wide mb-2">Producto B</p>
                @if($compareProductB)
                    <div class="flex items-center justify-between">
                        <span class="font-medium text-gray-900">{{ $compareNameB }}</span>
                        <button wire:click="clearCompareB" class="text-gray-400 hover:text-red-500 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                @else
                    <div class="relative">
                        <input type="text" wire:model.live.debounce.300ms="compareSearchB"
                            placeholder="Buscar producto B…"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-300">
                        @if(count($compareResultsB))
                            <div class="absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                                @foreach($compareResultsB as $r)
                                    <button wire:click="selectCompareB({{ $r['id'] }}, '{{ addslashes($r['name']) }}')"
                                        class="w-full text-left px-3 py-2 text-sm hover:bg-violet-50 transition">
                                        <span class="font-medium">{{ $r['name'] }}</span>
                                        @if(!empty($r['supplier']))
                                            <span class="text-gray-500 text-xs">({{ $r['supplier'] }})</span>
                                        @endif
                                        <span class="text-gray-400 ml-1 text-xs">{{ $r['sku'] }}</span>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        {{-- Tabla comparativa --}}
        @if($comparisonData->isEmpty())
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                <x-empty-state message="Selecciona al menos un producto para comparar." />
            </div>
        @else
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Lista de precios</th>
                                @foreach($comparisonData as $product)
                                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide
                                        {{ $loop->first ? 'text-indigo-600' : 'text-violet-600' }}">
                                        {{ $product->name }}
                                        @if($product->supplier)
                                            <span class="font-normal normal-case">({{ $product->supplier->name }})</span>
                                        @endif
                                        <div class="text-gray-400 font-normal normal-case text-[11px]">{{ $product->sku }}</div>
                                    </th>
                                @endforeach
                                @if($comparisonData->count() === 2)
                                    <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Diferencia</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($priceLists as $pl)
                                @php
                                    $prices = $comparisonData->map(fn($p) => $p->priceListItems->firstWhere('price_list_id', $pl->id));
                                    $hasAny = $prices->filter()->isNotEmpty();
                                @endphp
                                @if($hasAny)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-5 py-3">
                                            <div class="font-medium text-gray-800">{{ $pl->name }}</div>
                                            <div class="text-xs text-gray-400">{{ $pl->currency }}</div>
                                        </td>
                                        @foreach($comparisonData as $idx => $product)
                                            @php $item = $product->priceListItems->firstWhere('price_list_id', $pl->id); @endphp
                                            <td class="px-5 py-3 text-right">
                                                @if($item)
                                                    <div class="font-semibold text-gray-900">${{ number_format($item->price, 2) }}</div>
                                                    @if($item->discount_pct > 0)
                                                        <div class="text-xs text-emerald-600">
                                                            -{{ $item->discount_pct }}% →
                                                            ${{ number_format($item->price * (1 - $item->discount_pct / 100), 2) }}
                                                        </div>
                                                    @endif
                                                @else
                                                    <span class="text-gray-300 text-xs">Sin precio</span>
                                                @endif
                                            </td>
                                        @endforeach
                                        @if($comparisonData->count() === 2)
                                            @php
                                                $itemA = $comparisonData->first()->priceListItems->firstWhere('price_list_id', $pl->id);
                                                $itemB = $comparisonData->last()->priceListItems->firstWhere('price_list_id', $pl->id);
                                                $effA = $itemA ? $itemA->price * (1 - $itemA->discount_pct / 100) : null;
                                                $effB = $itemB ? $itemB->price * (1 - $itemB->discount_pct / 100) : null;
                                                $diff = ($effA !== null && $effB !== null) ? $effB - $effA : null;
                                            @endphp
                                            <td class="px-5 py-3 text-right">
                                                @if($diff !== null)
                                                    <span class="font-semibold {{ $diff > 0 ? 'text-red-500' : ($diff < 0 ? 'text-emerald-600' : 'text-gray-400') }}">
                                                        {{ $diff > 0 ? '+' : '' }}${{ number_format($diff, 2) }}
                                                    </span>
                                                    @if($effA > 0)
                                                        <div class="text-xs {{ $diff > 0 ? 'text-red-400' : ($diff < 0 ? 'text-emerald-500' : 'text-gray-400') }}">
                                                            {{ $diff > 0 ? '+' : '' }}{{ number_format(($diff / $effA) * 100, 1) }}%
                                                        </div>
                                                    @endif
                                                @else
                                                    <span class="text-gray-300 text-xs">—</span>
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endif

    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    {{-- TAB: HISTORIAL                                                          --}}
    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    @if($tab === 'history')
        {{-- Selección de producto --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 mb-4">
            <div class="flex flex-wrap gap-4 items-end">
                {{-- Autocomplete producto --}}
                <div class="relative flex-1 min-w-[200px]">
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Producto</label>
                    @if($historyProductId)
                        <div class="flex items-center gap-2 px-3 py-2 border border-gray-200 rounded-lg bg-gray-50">
                            <span class="text-sm font-medium text-gray-900 flex-1">{{ $historyProductName }}</span>
                            <button wire:click="clearHistoryProduct" class="text-gray-400 hover:text-red-500 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    @else
                        <div class="relative">
                            <input type="text" wire:model.live.debounce.300ms="historySearch"
                                placeholder="Buscar producto…"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                            @if(count($historyResults))
                                <div class="absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                                    @foreach($historyResults as $r)
                                        <button wire:click="selectHistoryProduct({{ $r['id'] }}, '{{ addslashes($r['name']) }}')"
                                            class="w-full text-left px-3 py-2 text-sm hover:bg-indigo-50 transition">
                                            <span class="font-medium">{{ $r['name'] }}</span>
                                            @if(!empty($r['supplier']))
                                                <span class="text-gray-500 text-xs">({{ $r['supplier'] }})</span>
                                            @endif
                                            <span class="text-gray-400 ml-1 text-xs">{{ $r['sku'] }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Filtro lista de precios --}}
                <div class="min-w-[180px]">
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Lista de precios</label>
                    <select wire:model.live="historyPriceListId"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                        {{ ! $historyProductId ? 'disabled' : '' }}>
                        <option value="">Todas</option>
                        @foreach($historyListsForProduct as $hl)
                            <option value="{{ $hl->id }}">{{ $hl->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Desde --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Desde</label>
                    <input type="date" wire:model.live="historyDateFrom"
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                        {{ ! $historyProductId ? 'disabled' : '' }}>
                </div>

                {{-- Hasta --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Hasta</label>
                    <input type="date" wire:model.live="historyDateTo"
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                        {{ ! $historyProductId ? 'disabled' : '' }}>
                </div>
            </div>
        </div>

        @if(! $historyProductId)
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                <x-empty-state message="Selecciona un producto para ver su historial de precios." />
            </div>
        @elseif($historyEntries->isEmpty())
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                <x-empty-state message="No hay historial para los filtros seleccionados." />
            </div>
        @else
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm min-w-[680px]">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Fecha</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Lista de precios</th>
                                <th class="px-5 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wide">Precio anterior</th>
                                <th class="px-5 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wide">Nuevo precio</th>
                                <th class="px-5 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wide">Variación</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide hidden md:table-cell">Modificado por</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($historyEntries as $entry)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-5 py-3 text-gray-500 text-xs whitespace-nowrap">
                                        {{ $entry->changed_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-5 py-3">
                                        <span class="text-sm font-medium text-gray-800">{{ $entry->priceList?->name ?? '—' }}</span>
                                        <span class="text-xs text-gray-400 ml-1">{{ $entry->priceList?->currency }}</span>
                                    </td>
                                    <td class="px-5 py-3 text-right">
                                        @if($entry->old_price !== null)
                                            <div class="text-gray-500">${{ number_format($entry->old_price, 2) }}</div>
                                            @if($entry->old_discount_pct > 0)
                                                <div class="text-xs text-gray-400">
                                                    -{{ $entry->old_discount_pct }}% → ${{ number_format($entry->old_price * (1 - $entry->old_discount_pct / 100), 2) }}
                                                </div>
                                            @endif
                                        @else
                                            <span class="text-xs bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded-full">Nuevo</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-right">
                                        <div class="font-semibold text-gray-900">${{ number_format($entry->new_price, 2) }}</div>
                                        @if($entry->new_discount_pct > 0)
                                            <div class="text-xs text-emerald-600">
                                                -{{ $entry->new_discount_pct }}% → ${{ number_format($entry->new_price * (1 - $entry->new_discount_pct / 100), 2) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-right">
                                        @if($entry->old_price !== null)
                                            @php
                                                $var    = $entry->variation;
                                                $varPct = $entry->variation_pct;
                                            @endphp
                                            <span class="font-semibold {{ $var > 0 ? 'text-red-500' : ($var < 0 ? 'text-emerald-600' : 'text-gray-400') }}">
                                                {{ $var > 0 ? '+' : '' }}${{ number_format($var, 2) }}
                                            </span>
                                            @if($varPct !== null)
                                                <div class="text-xs {{ $var > 0 ? 'text-red-400' : ($var < 0 ? 'text-emerald-500' : 'text-gray-400') }}">
                                                    {{ $var > 0 ? '+' : '' }}{{ number_format($varPct, 1) }}%
                                                </div>
                                            @endif
                                        @else
                                            <span class="text-gray-300">—</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 hidden md:table-cell">
                                        @if($entry->changedBy)
                                            <div class="flex items-center gap-2">
                                                <div class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs font-bold uppercase flex-shrink-0">
                                                    {{ substr($entry->changedBy->name, 0, 1) }}
                                                </div>
                                                <span class="text-sm text-gray-700">{{ $entry->changedBy->name }}</span>
                                            </div>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($historyEntries->hasPages())
                    <div class="px-5 py-3 border-t border-gray-100">{{ $historyEntries->links() }}</div>
                @endif
            </div>
        @endif
    @endif
</div>

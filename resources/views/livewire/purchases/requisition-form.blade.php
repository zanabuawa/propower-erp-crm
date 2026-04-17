<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center gap-3 mb-6">
        <a wire:navigate href="{{ route('purchases.index') }}" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-xl font-medium text-gray-900">Nueva requisición de compra</h1>
            <p class="text-sm text-gray-500">Solicita productos, servicios o herramientas al área de compras</p>
        </div>
    </div>

    <form wire:submit="save" class="space-y-5">

        {{-- ── Tipo de requisición ────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm space-y-3">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">¿Qué tipo de requisición es?</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2">
                @foreach(\App\Models\PurchaseRequisition::REQUISITION_TYPES as $value => $label)
                    @php
                        $icons = ['material' => '📦', 'service' => '🛠️', 'tool' => '🔧', 'asset' => '🏢', 'mixed' => '📋'];
                    @endphp
                    <label class="flex flex-col items-center gap-1.5 p-3 border rounded-xl cursor-pointer transition text-center
                        {{ $requisition_type === $value
                            ? 'border-indigo-400 bg-indigo-50 text-indigo-700 shadow-sm'
                            : 'border-gray-200 hover:border-gray-300 text-gray-500 hover:bg-gray-50' }}">
                        <input type="radio" wire:model.live="requisition_type" value="{{ $value }}" class="sr-only">
                        <span class="text-xl">{{ $icons[$value] ?? '📋' }}</span>
                        <span class="text-xs font-medium leading-tight">{{ $label }}</span>
                    </label>
                @endforeach
            </div>
            @error('requisition_type') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        {{-- ── Datos generales ────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 lg:p-6 space-y-4 shadow-sm">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">Datos generales</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-5">

                {{-- Prioridad --}}
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Prioridad <span class="text-red-400">*</span></label>
                    <div class="grid grid-cols-4 gap-1">
                        @foreach(\App\Models\PurchaseRequisition::PRIORITY as $val => $lbl)
                            <label class="text-center py-1.5 px-1 border rounded-lg cursor-pointer transition text-xs font-medium
                                {{ $priority === $val
                                    ? \App\Models\PurchaseRequisition::PRIORITY_COLORS[$val] . ' border-current ring-1 ring-current'
                                    : 'border-gray-200 text-gray-400 hover:border-gray-300' }}">
                                <input type="radio" wire:model.live="priority" value="{{ $val }}" class="sr-only">
                                {{ $lbl }}
                            </label>
                        @endforeach
                    </div>
                    @error('priority') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Fecha requerida --}}
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Fecha requerida *</label>
                    <input wire:model="needed_by" type="date"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    @error('needed_by') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Moneda --}}
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Moneda</label>
                    <select wire:model="currency"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        <option value="MXN">MXN — Peso mexicano</option>
                        <option value="USD">USD — Dólar americano</option>
                    </select>
                </div>

                {{-- Tipo de gasto --}}
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Tipo de gasto</label>
                    <select wire:model="expense_type"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
                        <option value="">— Sin clasificar —</option>
                        @foreach(\App\Models\PurchaseRequisition::EXPENSE_TYPES as $val => $lbl)
                            <option value="{{ $val }}">{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Proyecto --}}
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Proyecto / referencia</label>
                    <input wire:model="project_name" type="text"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                        placeholder="Ej: Obra Norte, Expansión 2026...">
                </div>

                {{-- Justificación --}}
                <div class="sm:col-span-2 lg:col-span-3">
                    <label class="block text-xs text-gray-500 mb-1">Justificación *</label>
                    <textarea wire:model="justification" rows="2"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                        placeholder="¿Por qué se necesita esta compra?"></textarea>
                    @error('justification') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- ── Partidas ──────────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4 shadow-sm">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <div>
                    <h2 class="text-sm font-medium text-gray-700">Partidas solicitadas</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Agrega productos, servicios o cualquier artículo que necesites</p>
                </div>
                <livewire:shared.product-picker />
            </div>

            {{-- Buscador de productos --}}
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input wire:model.live.debounce.250ms="productSearch" type="text"
                    placeholder="Búsqueda rápida de producto: nombre, SKU o código de barras..."
                    class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">

                @if(count($productResults) > 0)
                    <div class="absolute top-full left-0 right-0 bg-white border border-gray-200 rounded-xl shadow-xl z-10 mt-1 overflow-hidden">
                        @foreach($productResults as $result)
                            <button type="button" wire:click="addProduct({{ $result['id'] }})"
                                class="w-full text-left px-4 py-2.5 hover:bg-indigo-50 transition flex items-center justify-between border-b border-gray-50 last:border-0">
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-gray-900">{{ $result['name'] }}</p>
                                    <p class="text-xs text-gray-400">SKU: {{ $result['sku'] ?? '—' }}</p>
                                </div>
                                <div class="text-right flex-shrink-0 ml-4 space-y-0.5">
                                    @can('view prices')
                                    <p class="text-xs font-semibold text-indigo-600">${{ number_format($result['purchase_price'], 2) }}</p>
                                    @endcan
                                    @php $avail = (float)($result['stock_available'] ?? 0); @endphp
                                    <p class="text-[10px] font-medium {{ $avail > 0 ? 'text-emerald-600' : 'text-gray-400' }}">
                                        {{ $avail > 0 ? 'Stock: ' . number_format($avail, 0) : 'Sin stock' }}
                                    </p>
                                </div>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            @error('items') <p class="text-xs text-red-500">{{ $message }}</p> @enderror

            {{-- Tabla de partidas --}}
            <div class="border border-gray-100 rounded-lg overflow-x-auto">
                <table class="w-full text-sm min-w-[700px]">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="text-left px-3 py-2.5 text-xs font-medium text-gray-500 w-28">Tipo</th>
                            <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Descripción / Producto</th>
                            <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-24">Cantidad</th>
                            <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-20">Unidad</th>
                            <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-32">Precio est.</th>
                            <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-24 hidden lg:table-cell text-emerald-600">Stock act.</th>
                            <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Notas</th>
                            <th class="w-10"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($items as $index => $item)
                            @php
                                $stockInfo  = $item['stock_info'] ?? null;
                                $stockAvail = $stockInfo ? (float)$stockInfo['available'] : null;
                                $qtyNeeded  = (float)($item['quantity'] ?? 1);
                                $hasEnough  = $stockAvail !== null && $stockAvail >= $qtyNeeded;
                            @endphp
                            <tr class="{{ ($stockAvail !== null && $stockAvail > 0 && $item['item_type'] === 'product') ? 'bg-emerald-50/30' : '' }}">
                                <td class="px-3 py-2.5">
                                    <select wire:model.live="items.{{ $index }}.item_type"
                                        class="w-full border border-gray-200 rounded px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-indigo-300 bg-white">
                                        @foreach(\App\Models\PurchaseRequisitionItem::ITEM_TYPES as $val => $lbl)
                                            <option value="{{ $val }}">{{ $lbl }}</option>
                                        @endforeach
                                    </select>
                                    @error("items.{$index}.item_type") <p class="text-[10px] text-red-500">{{ $message }}</p> @enderror
                                </td>
                                <td class="px-4 py-2.5">
                                    <input wire:model="items.{{ $index }}.description" type="text"
                                        class="w-full border-none focus:ring-0 p-0 text-sm placeholder-gray-300"
                                        placeholder="{{ $item['item_type'] === 'service' ? 'Nombre del servicio...' : ($item['item_type'] === 'tool' ? 'Nombre de la herramienta...' : 'Nombre del producto...') }}">
                                    @error("items.{$index}.description") <p class="text-[10px] text-red-500">{{ $message }}</p> @enderror
                                </td>
                                <td class="px-4 py-2.5">
                                    <input wire:model.live="items.{{ $index }}.quantity" type="number" step="0.01" min="0.01"
                                        class="w-full border-gray-200 rounded px-2 py-1 text-sm focus:ring-indigo-300">
                                    @error("items.{$index}.quantity") <p class="text-[10px] text-red-500">{{ $message }}</p> @enderror
                                </td>
                                <td class="px-4 py-2.5">
                                    <input wire:model="items.{{ $index }}.unit" type="text"
                                        class="w-full border-gray-200 rounded px-2 py-1 text-sm focus:ring-indigo-300 placeholder-gray-300"
                                        placeholder="{{ $item['item_type'] === 'service' ? 'hr, svc...' : 'pz, kg...' }}">
                                </td>
                                <td class="px-4 py-2.5">
                                    <div class="relative">
                                        <span class="absolute left-2 top-1.5 text-xs text-gray-400">$</span>
                                        <input wire:model="items.{{ $index }}.unit_price" type="number" step="0.01" min="0"
                                            class="w-full border-gray-200 rounded pl-5 pr-2 py-1 text-sm focus:ring-indigo-300">
                                    </div>
                                    @error("items.{$index}.unit_price") <p class="text-[10px] text-red-500">{{ $message }}</p> @enderror
                                </td>
                                <td class="px-4 py-2.5 hidden lg:table-cell">
                                    @if($stockAvail !== null && $item['item_type'] === 'product')
                                        <div class="text-xs {{ $hasEnough ? 'text-emerald-600' : ($stockAvail > 0 ? 'text-amber-600' : 'text-gray-400') }}">
                                            <span class="font-medium">{{ number_format($stockAvail, 0) }}</span>
                                            @if($hasEnough)
                                                <span class="block text-[10px]">Suficiente</span>
                                            @elseif($stockAvail > 0)
                                                <span class="block text-[10px]">Parcial</span>
                                            @else
                                                <span class="block text-[10px]">Sin stock</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-300">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5">
                                    <input wire:model="items.{{ $index }}.notes" type="text"
                                        class="w-full border-none focus:ring-0 p-0 text-sm placeholder-gray-300"
                                        placeholder="Notas...">
                                </td>
                                <td class="px-4 py-2.5 text-center">
                                    <button type="button" wire:click="removeItem({{ $index }})"
                                        class="text-gray-300 hover:text-red-500 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-gray-400 italic">
                                    No hay partidas. Usa el buscador o los botones de abajo.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Botones de agregar partida por tipo --}}
            <div class="flex flex-wrap gap-2">
                <button type="button" wire:click="addItem('product')"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs border-2 border-dashed border-gray-200 rounded-lg text-gray-400 hover:border-blue-300 hover:text-blue-500 transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Producto / Material
                </button>
                <button type="button" wire:click="addItem('service')"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs border-2 border-dashed border-gray-200 rounded-lg text-gray-400 hover:border-purple-300 hover:text-purple-500 transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Servicio
                </button>
                <button type="button" wire:click="addItem('tool')"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs border-2 border-dashed border-gray-200 rounded-lg text-gray-400 hover:border-amber-300 hover:text-amber-500 transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Herramienta / Equipo
                </button>
                <button type="button" wire:click="addItem('asset')"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs border-2 border-dashed border-gray-200 rounded-lg text-gray-400 hover:border-emerald-300 hover:text-emerald-500 transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Activo fijo
                </button>
            </div>

            {{-- Stock warning --}}
            @php
                $itemsWithEnoughStock = collect($items)->filter(function($i) {
                    $s = $i['stock_info'] ?? null;
                    return $i['item_type'] === 'product' && $s && (float)$s['available'] >= (float)($i['quantity'] ?? 1);
                });
            @endphp
            @if($itemsWithEnoughStock->count() > 0)
                <div class="flex items-start gap-2 bg-emerald-50 border border-emerald-200 rounded-lg px-3 py-2.5">
                    <svg class="w-4 h-4 text-emerald-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-xs text-emerald-700">
                        <strong>{{ $itemsWithEnoughStock->count() }} partida(s)</strong> tienen existencias suficientes en inventario.
                        Verifica si realmente necesitas pedirlas o si pueden tomarse del almacén.
                    </p>
                </div>
            @endif

            {{-- Total estimado --}}
            @php $total = collect($items)->sum(fn($i) => ($i['quantity'] ?? 0) * ($i['unit_price'] ?? 0)); @endphp
            @if($total > 0)
                <div class="flex justify-end pr-4 text-sm">
                    <div class="flex flex-col items-end">
                        <span class="text-gray-500 text-xs">Total estimado</span>
                        <span class="text-lg font-bold text-gray-900">{{ $currency }} ${{ number_format($total, 2) }}</span>
                    </div>
                </div>
            @endif
        </div>

        <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pb-6">
            <a wire:navigate href="{{ route('purchases.index') }}"
                class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition text-center">
                Cancelar
            </a>
            <button type="submit"
                class="px-5 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition shadow-sm hover:shadow-md">
                Crear requisición
            </button>
        </div>
    </form>
</div>

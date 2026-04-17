<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-medium text-gray-900">Recomendaciones de reabastecimiento</h1>
    </div>

    {{-- Parámetros --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-5">
        <p class="text-xs text-gray-500 mb-3 font-medium">Parámetros del análisis</p>
        <div class="flex flex-wrap gap-4">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Período histórico (meses)</label>
                <select wire:model.live="analysisPeriodMonths"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    <option value="1">1 mes</option>
                    <option value="3">3 meses</option>
                    <option value="6">6 meses</option>
                    <option value="12">12 meses</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Plazo de entrega (días)</label>
                <input wire:model.live.debounce.500ms="leadTimeDays" type="number" min="1" max="180"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm w-24 focus:outline-none focus:ring-2 focus:ring-indigo-300">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Stock de seguridad (días)</label>
                <input wire:model.live.debounce.500ms="safetyStockDays" type="number" min="0" max="90"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm w-24 focus:outline-none focus:ring-2 focus:ring-indigo-300">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Categoría</label>
                <select wire:model.live="filterCategory"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    <option value="">— Todas —</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        @if($recommendations->isEmpty())
            <div class="p-10 text-center">
                <p class="text-gray-400 text-sm">No hay productos que requieran reabastecimiento con los parámetros actuales.</p>
            </div>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                        <th class="px-4 py-3 text-left">Producto</th>
                        <th class="px-4 py-3 text-right">Disponible</th>
                        <th class="px-4 py-3 text-right">Comprometido</th>
                        <th class="px-4 py-3 text-right">Consumo diario</th>
                        <th class="px-4 py-3 text-right">Punto de reorden</th>
                        <th class="px-4 py-3 text-right">Días de stock</th>
                        <th class="px-4 py-3 text-right">Cantidad sugerida</th>
                        <th class="px-4 py-3 text-left">Proveedor</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($recommendations as $r)
                        @php
                            $urgent = $r['days_of_stock'] !== null && $r['days_of_stock'] <= $leadTimeDays;
                        @endphp
                        <tr class="hover:bg-gray-50 transition {{ $urgent ? 'bg-red-50' : '' }}">
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-800">{{ $r['product']->name }}</div>
                                <div class="text-xs text-gray-400">{{ $r['product']->sku }}</div>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <span class="{{ $r['available_stock'] <= 0 ? 'text-red-600 font-medium' : 'text-gray-700' }}">
                                    {{ number_format($r['available_stock'], 2) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right text-orange-600">
                                {{ $r['committed_stock'] > 0 ? number_format($r['committed_stock'], 2) : '—' }}
                            </td>
                            <td class="px-4 py-3 text-right text-gray-600">
                                {{ number_format($r['avg_daily_consumption'], 3) }}
                            </td>
                            <td class="px-4 py-3 text-right text-gray-600">
                                {{ number_format($r['reorder_point'], 2) }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if($r['days_of_stock'] !== null)
                                    <span class="font-medium {{ $urgent ? 'text-red-600' : 'text-gray-700' }}">
                                        {{ $r['days_of_stock'] }} días
                                    </span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <span class="font-semibold text-indigo-700">
                                    {{ number_format($r['suggested_qty'], 2) }}
                                </span>
                                <span class="text-xs text-gray-400 ml-1">{{ $r['product']->unitOfMeasure?->abbreviation }}</span>
                            </td>
                            <td class="px-4 py-3 text-gray-600 text-xs">
                                {{ $r['product']->supplier?->name ?? '—' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
    <p class="text-xs text-gray-400 mt-3">
        Cantidad sugerida = (stock máximo configurado OR punto de reorden × 3) − stock disponible.
        Punto de reorden = consumo diario promedio × (plazo de entrega + días de seguridad).
    </p>
</div>

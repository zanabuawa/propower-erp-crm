<div>
    <x-page-header title="Rotación de inventario" description="Análisis de velocidad de movimiento por producto">
    </x-page-header>

    {{-- Parámetros --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-5 mb-6 shadow-sm">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Período de análisis</label>
                <select wire:model.live="analysisPeriodMonths"
                    class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="1">Último mes</option>
                    <option value="3">Últimos 3 meses</option>
                    <option value="6">Últimos 6 meses</option>
                    <option value="12">Último año</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Categoría</label>
                <select wire:model.live="filterCategory"
                    class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Todas las categorías</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Almacén</label>
                <select wire:model.live="filterWarehouse"
                    class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Todos los almacenes</option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Mostrar tier</label>
                <select wire:model.live="tierFilter"
                    class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Todos</option>
                    <option value="high">Alta rotación</option>
                    <option value="medium">Rotación media</option>
                    <option value="low">Baja rotación</option>
                    <option value="dead">Sin movimiento</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Resumen por tier --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <button wire:click="$set('tierFilter', 'high')"
            class="bg-white rounded-2xl border p-4 text-left hover:border-emerald-300 transition cursor-pointer {{ $tierFilter === 'high' ? 'border-emerald-400 ring-2 ring-emerald-200' : 'border-gray-200' }}">
            <p class="text-2xl font-bold text-emerald-600">{{ $summary['high'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Alta rotación <span class="text-gray-400">(≥ 4×)</span></p>
        </button>
        <button wire:click="$set('tierFilter', 'medium')"
            class="bg-white rounded-2xl border p-4 text-left hover:border-blue-300 transition cursor-pointer {{ $tierFilter === 'medium' ? 'border-blue-400 ring-2 ring-blue-200' : 'border-gray-200' }}">
            <p class="text-2xl font-bold text-blue-600">{{ $summary['medium'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Rotación media <span class="text-gray-400">(1×–4×)</span></p>
        </button>
        <button wire:click="$set('tierFilter', 'low')"
            class="bg-white rounded-2xl border p-4 text-left hover:border-amber-300 transition cursor-pointer {{ $tierFilter === 'low' ? 'border-amber-400 ring-2 ring-amber-200' : 'border-gray-200' }}">
            <p class="text-2xl font-bold text-amber-600">{{ $summary['low'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Baja rotación <span class="text-gray-400">(&lt; 1×)</span></p>
        </button>
        <button wire:click="$set('tierFilter', 'dead')"
            class="bg-white rounded-2xl border p-4 text-left hover:border-red-300 transition cursor-pointer {{ $tierFilter === 'dead' ? 'border-red-400 ring-2 ring-red-200' : 'border-gray-200' }}">
            <p class="text-2xl font-bold text-red-600">{{ $summary['dead'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Sin movimiento</p>
        </button>
    </div>
    @if($tierFilter)
        <div class="mb-4">
            <button wire:click="$set('tierFilter', '')" class="text-xs text-indigo-600 hover:underline">← Ver todos</button>
        </div>
    @endif

    {{-- Tabla --}}
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[700px]">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="text-left px-5 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Producto</th>
                        <th class="text-right px-5 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Stock actual</th>
                        <th class="text-right px-5 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            <button wire:click="toggleSort('consumed')" class="flex items-center gap-1 ml-auto hover:text-indigo-600">
                                Consumido
                                @if($sortBy === 'consumed')
                                    <svg class="w-3 h-3 {{ $sortDir === 'desc' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                @endif
                            </button>
                        </th>
                        <th class="text-right px-5 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            <button wire:click="toggleSort('turnover_ratio')" class="flex items-center gap-1 ml-auto hover:text-indigo-600">
                                Rotación
                                @if($sortBy === 'turnover_ratio')
                                    <svg class="w-3 h-3 {{ $sortDir === 'desc' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                @endif
                            </button>
                        </th>
                        <th class="text-right px-5 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            <button wire:click="toggleSort('days_in_stock')" class="flex items-center gap-1 ml-auto hover:text-indigo-600">
                                Días en stock
                                @if($sortBy === 'days_in_stock')
                                    <svg class="w-3 h-3 {{ $sortDir === 'desc' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                @endif
                            </button>
                        </th>
                        <th class="text-center px-5 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Clasificación</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($data as $row)
                        @php
                            $tierConfig = [
                                'high'   => ['bg-emerald-50 text-emerald-700 border-emerald-100', 'Alta rotación'],
                                'medium' => ['bg-blue-50 text-blue-700 border-blue-100', 'Media'],
                                'low'    => ['bg-amber-50 text-amber-700 border-amber-100', 'Baja rotación'],
                                'dead'   => ['bg-red-50 text-red-700 border-red-100', 'Sin movimiento'],
                            ];
                            [$tierClass, $tierLabel] = $tierConfig[$row['tier']] ?? ['bg-gray-100 text-gray-500 border-gray-200', $row['tier']];
                        @endphp
                        <tr class="hover:bg-gray-50/70 transition-colors">
                            <td class="px-5 py-4">
                                <p class="font-medium text-gray-900">{{ $row['product']->name }}</p>
                                <p class="text-xs text-gray-400">{{ $row['product']->category?->name }} · {{ $row['product']->sku }}</p>
                            </td>
                            <td class="px-5 py-4 text-right text-gray-700">
                                {{ number_format($row['current_stock'], 2) }}
                                <span class="text-xs text-gray-400">{{ $row['unit'] }}</span>
                            </td>
                            <td class="px-5 py-4 text-right text-gray-700">
                                {{ number_format($row['consumed'], 2) }}
                                <span class="text-xs text-gray-400">{{ $row['unit'] }}</span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                @if($row['turnover_ratio'] === 999)
                                    <span class="text-emerald-600 font-semibold">∞</span>
                                @elseif($row['turnover_ratio'] === 0.0)
                                    <span class="text-gray-400">0.00×</span>
                                @else
                                    <span class="font-semibold text-gray-900">{{ number_format($row['turnover_ratio'], 2) }}×</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right text-gray-600">
                                @if($row['days_in_stock'] !== null)
                                    <span class="{{ $row['days_in_stock'] > 90 ? 'text-amber-600 font-medium' : '' }}">
                                        {{ number_format($row['days_in_stock']) }} días
                                    </span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-medium border {{ $tierClass }}">
                                    {{ $tierLabel }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center">
                                <x-empty-state message="No hay productos que coincidan con los filtros." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <p class="mt-3 text-xs text-gray-400">
        Rotación = unidades consumidas en el período ÷ stock promedio del período.
        Períodos de {{ $analysisPeriodMonths }} {{ $analysisPeriodMonths === 1 ? 'mes' : 'meses' }}.
    </p>
</div>

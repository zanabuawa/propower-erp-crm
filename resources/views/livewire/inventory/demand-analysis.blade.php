<div>
    <x-page-header title="Análisis de demanda" description="Clasificación de productos por nivel de demanda y tendencia">
    </x-page-header>

    {{-- Filtros --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-5 mb-6 shadow-sm">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Período</label>
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
                <label class="block text-xs font-medium text-gray-500 mb-1">Nivel de demanda</label>
                <select wire:model.live="demandTier"
                    class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Todos</option>
                    <option value="high">Alta demanda</option>
                    <option value="medium">Demanda media</option>
                    <option value="low">Baja demanda</option>
                    <option value="zero">Sin demanda</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Resumen --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-gray-200 p-4 lg:col-span-1">
            <p class="text-2xl font-bold text-indigo-600">{{ number_format($summary['total_consumed'], 0) }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Unidades totales consumidas</p>
        </div>
        <button wire:click="$set('demandTier', 'high')"
            class="bg-white rounded-2xl border p-4 text-left cursor-pointer hover:border-emerald-300 transition {{ $demandTier === 'high' ? 'border-emerald-400 ring-2 ring-emerald-200' : 'border-gray-200' }}">
            <p class="text-2xl font-bold text-emerald-600">{{ $summary['high'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Alta demanda <span class="text-gray-400">(top 20%)</span></p>
        </button>
        <button wire:click="$set('demandTier', 'medium')"
            class="bg-white rounded-2xl border p-4 text-left cursor-pointer hover:border-blue-300 transition {{ $demandTier === 'medium' ? 'border-blue-400 ring-2 ring-blue-200' : 'border-gray-200' }}">
            <p class="text-2xl font-bold text-blue-600">{{ $summary['medium'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Demanda media</p>
        </button>
        <button wire:click="$set('demandTier', 'low')"
            class="bg-white rounded-2xl border p-4 text-left cursor-pointer hover:border-amber-300 transition {{ $demandTier === 'low' ? 'border-amber-400 ring-2 ring-amber-200' : 'border-gray-200' }}">
            <p class="text-2xl font-bold text-amber-600">{{ $summary['low'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Baja demanda</p>
        </button>
        <button wire:click="$set('demandTier', 'zero')"
            class="bg-white rounded-2xl border p-4 text-left cursor-pointer hover:border-red-300 transition {{ $demandTier === 'zero' ? 'border-red-400 ring-2 ring-red-200' : 'border-gray-200' }}">
            <p class="text-2xl font-bold text-red-600">{{ $summary['zero'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Sin demanda</p>
        </button>
    </div>
    @if($demandTier)
        <div class="mb-4">
            <button wire:click="$set('demandTier', '')" class="text-xs text-indigo-600 hover:underline">← Ver todos</button>
        </div>
    @endif

    {{-- Tabla --}}
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[720px]">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="text-left px-5 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Producto</th>
                        <th class="text-right px-5 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            <button wire:click="toggleSort('total_consumed')" class="flex items-center gap-1 ml-auto hover:text-indigo-600">
                                Total consumido
                                @if($sortBy === 'total_consumed')
                                    <svg class="w-3 h-3 {{ $sortDir === 'asc' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                @endif
                            </button>
                        </th>
                        <th class="text-right px-5 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            <button wire:click="toggleSort('avg_daily')" class="flex items-center gap-1 ml-auto hover:text-indigo-600">
                                Promedio diario
                                @if($sortBy === 'avg_daily')
                                    <svg class="w-3 h-3 {{ $sortDir === 'asc' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                @endif
                            </button>
                        </th>
                        <th class="text-center px-5 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Movimientos</th>
                        <th class="text-right px-5 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            <button wire:click="toggleSort('trend_pct')" class="flex items-center gap-1 ml-auto hover:text-indigo-600">
                                Tendencia
                                @if($sortBy === 'trend_pct')
                                    <svg class="w-3 h-3 {{ $sortDir === 'asc' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                @endif
                            </button>
                        </th>
                        <th class="text-center px-5 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Nivel</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($data as $row)
                        @php
                            $tierConfig = [
                                'high'   => ['bg-emerald-50 text-emerald-700 border-emerald-100', 'Alta demanda'],
                                'medium' => ['bg-blue-50 text-blue-700 border-blue-100', 'Media'],
                                'low'    => ['bg-amber-50 text-amber-700 border-amber-100', 'Baja demanda'],
                                'zero'   => ['bg-red-50 text-red-700 border-red-100', 'Sin demanda'],
                            ];
                            [$tierClass, $tierLabel] = $tierConfig[$row['tier']] ?? ['bg-gray-100 text-gray-500 border-gray-200', $row['tier']];
                        @endphp
                        <tr class="hover:bg-gray-50/70 transition-colors">
                            <td class="px-5 py-4">
                                <p class="font-medium text-gray-900">{{ $row['product']->name }}</p>
                                <p class="text-xs text-gray-400">{{ $row['product']->category?->name }} · {{ $row['product']->sku }}</p>
                            </td>
                            <td class="px-5 py-4 text-right text-gray-700">
                                {{ number_format($row['total_consumed'], 2) }}
                                <span class="text-xs text-gray-400">{{ $row['unit'] }}</span>
                            </td>
                            <td class="px-5 py-4 text-right text-gray-600 tabular-nums">
                                {{ number_format($row['avg_daily'], 3) }}
                                <span class="text-xs text-gray-400">/día</span>
                            </td>
                            <td class="px-5 py-4 text-center text-gray-500">{{ $row['movement_count'] }}</td>
                            <td class="px-5 py-4 text-right">
                                @if($row['trend_pct'] === null)
                                    <span class="text-gray-400 text-xs">N/D</span>
                                @elseif($row['trend_pct'] > 10)
                                    <span class="text-emerald-600 font-medium text-xs flex items-center justify-end gap-0.5">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                                        +{{ $row['trend_pct'] }}%
                                    </span>
                                @elseif($row['trend_pct'] < -10)
                                    <span class="text-red-500 font-medium text-xs flex items-center justify-end gap-0.5">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                                        {{ $row['trend_pct'] }}%
                                    </span>
                                @else
                                    <span class="text-gray-400 text-xs">≈ estable</span>
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
        Clasificación basada en distribución ABC: alta = top 20% por consumo, media = 20–60%, baja = 60–100%.
        Tendencia compara la segunda mitad del período vs la primera.
    </p>
</div>

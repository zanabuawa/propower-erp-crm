<div>
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <a wire:navigate href="{{ route('assets.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1 class="text-xl font-medium text-gray-900">Depreciación de activos fijos</h1>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ session('success') }}</div>
    @endif
    @if(session('info'))
        <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-700">{{ session('info') }}</div>
    @endif

    {{-- Filtros --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-5">
        <div class="flex flex-wrap gap-3">
            <select wire:model.live="assetId"
                class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 min-w-48">
                <option value="">— Todos los activos —</option>
                @foreach($assets as $a)
                    <option value="{{ $a->id }}">{{ $a->folio }} — {{ $a->name }}</option>
                @endforeach
            </select>

            <select wire:model.live="filterType"
                class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                <option value="contable">Depreciación contable</option>
                <option value="fiscal">Depreciación fiscal SAT</option>
            </select>

            <select wire:model.live="filterYear"
                class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                @for($y = now()->year; $y >= now()->year - 5; $y--)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endfor
            </select>

            @if($assetId)
                <button wire:click="runDepreciation" wire:confirm="¿Registrar depreciación del período actual?"
                    class="ml-auto px-4 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition">
                    Registrar período actual
                </button>
            @endif
        </div>
    </div>

    {{-- Info del activo seleccionado --}}
    @if($selectedAsset)
        <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4 mb-5 grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
            <div>
                <p class="text-xs text-indigo-400 mb-1">Costo de adquisición</p>
                <p class="font-medium text-indigo-900">${{ number_format($selectedAsset->acquisition_cost, 2) }}</p>
            </div>
            <div>
                <p class="text-xs text-indigo-400 mb-1">Método</p>
                <p class="font-medium text-indigo-900">{{ \App\Models\AssetDepreciation::METHODS[$selectedAsset->depreciation_method] ?? $selectedAsset->depreciation_method }}</p>
            </div>
            <div>
                <p class="text-xs text-indigo-400 mb-1">Vida útil</p>
                <p class="font-medium text-indigo-900">{{ $selectedAsset->useful_life_years }} años</p>
            </div>
            <div>
                <p class="text-xs text-indigo-400 mb-1">Valor en libros actual</p>
                <p class="font-medium text-indigo-900">${{ number_format($selectedAsset->current_book_value ?? $selectedAsset->acquisition_cost, 2) }}</p>
            </div>
        </div>
    @endif

    {{-- Tabla de depreciaciones --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        @if($depreciations->isEmpty())
            <div class="p-10 text-center text-gray-400 text-sm">
                No hay registros de depreciación para los filtros seleccionados.
            </div>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                        @if(!$assetId)
                            <th class="px-4 py-3 text-left">Activo</th>
                        @endif
                        <th class="px-4 py-3 text-left">Período</th>
                        <th class="px-4 py-3 text-left">Método</th>
                        <th class="px-4 py-3 text-right">Valor inicial</th>
                        <th class="px-4 py-3 text-right">Depreciación</th>
                        <th class="px-4 py-3 text-right">Acumulada</th>
                        <th class="px-4 py-3 text-right">Valor final</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($depreciations as $dep)
                        <tr class="hover:bg-gray-50 transition">
                            @if(!$assetId)
                                <td class="px-4 py-3 text-gray-700">
                                    <div class="font-medium">{{ $dep->asset->folio }}</div>
                                    <div class="text-xs text-gray-400">{{ $dep->asset->name }}</div>
                                </td>
                            @endif
                            <td class="px-4 py-3 text-gray-700">{{ $dep->period_label }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ \App\Models\AssetDepreciation::METHODS[$dep->method] ?? $dep->method }}</td>
                            <td class="px-4 py-3 text-right text-gray-700">${{ number_format($dep->book_value_start, 2) }}</td>
                            <td class="px-4 py-3 text-right font-medium text-red-600">-${{ number_format($dep->depreciation_amount, 2) }}</td>
                            <td class="px-4 py-3 text-right text-gray-600">${{ number_format($dep->accumulated_depreciation, 2) }}</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900">${{ number_format($dep->book_value_end, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t border-gray-200 bg-gray-50 font-medium">
                        <td colspan="{{ $assetId ? 3 : 4 }}" class="px-4 py-3 text-right text-gray-600 text-xs uppercase">Total depreciado {{ $filterYear }}</td>
                        <td class="px-4 py-3 text-right text-red-600">-${{ number_format($totalDepreciation, 2) }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        @endif
    </div>
</div>

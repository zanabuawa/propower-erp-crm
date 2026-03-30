<div class="max-w-3xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('sales.price-lists.index') }}" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="text-xl font-medium text-gray-900">
            {{ $priceList?->exists ? 'Editar lista de precios' : 'Nueva lista de precios' }}
        </h1>
    </div>

    <form wire:submit="save" class="space-y-5">
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">Configuración</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">Nombre *</label>
                    <input wire:model="name" type="text" value="{{ $name }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Moneda</label>
                    <select wire:model="currency"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        <option value="MXN">MXN</option>
                        <option value="USD">USD</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Válida desde</label>
                    <input wire:model="valid_from" type="date" value="{{ $valid_from }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Válida hasta</label>
                    <input wire:model="valid_to" type="date" value="{{ $valid_to }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>
                <div class="sm:col-span-2 flex gap-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input wire:model="is_default" type="checkbox" class="rounded text-indigo-600"
                            {{ $is_default ? 'checked' : '' }}>
                        <span class="text-sm text-gray-700">Lista predeterminada</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input wire:model="is_active" type="checkbox" class="rounded text-indigo-600"
                            {{ $is_active ? 'checked' : '' }}>
                        <span class="text-sm text-gray-700">Lista activa</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">Productos y precios</h2>

            <div class="relative">
                <input wire:model.live.debounce.300ms="productSearch" type="text"
                    placeholder="Buscar producto para agregar..."
                    class="w-full border border-gray-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                @if(count($productResults) > 0)
                    <div class="absolute top-full left-0 right-0 bg-white border border-gray-200 rounded-lg shadow-lg z-10 mt-1">
                        @foreach($productResults as $result)
                            <button type="button" wire:click="addProduct({{ $result['id'] }})"
                                class="w-full text-left px-4 py-2.5 hover:bg-gray-50 transition flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $result['name'] }}</p>
                                    <p class="text-xs text-gray-400">Precio estándar: ${{ number_format($result['sale_price'], 2) }}</p>
                                </div>
                                <span class="text-xs text-indigo-600">+ Agregar</span>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            @if(count($items) > 0)
                <div class="border border-gray-100 rounded-lg overflow-hidden">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100">
                                <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Producto</th>
                                <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-32">Precio lista</th>
                                <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 w-24">Descuento %</th>
                                <th class="w-8"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($items as $index => $item)
                                <tr>
                                    <td class="px-4 py-2.5 font-medium text-gray-900">{{ $item['product_name'] }}</td>
                                    <td class="px-4 py-2.5">
                                        <div class="relative">
                                            <span class="absolute left-2 top-1 text-xs text-gray-400">$</span>
                                            <input wire:model="items.{{ $index }}.price" type="number" step="0.01" min="0"
                                                class="w-full border border-gray-200 rounded pl-5 pr-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-300">
                                        </div>
                                    </td>
                                    <td class="px-4 py-2.5">
                                        <input wire:model="items.{{ $index }}.discount_pct" type="number" step="0.01" min="0" max="100"
                                            class="w-full border border-gray-200 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-300">
                                    </td>
                                    <td class="px-4 py-2.5">
                                        <button type="button" wire:click="removeItem({{ $index }})"
                                            class="text-red-400 hover:text-red-600">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="border-2 border-dashed border-gray-200 rounded-lg py-8 text-center text-gray-400 text-sm">
                    Busca y agrega productos a esta lista
                </div>
            @endif
        </div>

        <div class="flex items-center justify-end gap-3 pb-6">
            <a href="{{ route('sales.price-lists.index') }}"
                class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                Cancelar
            </a>
            <button type="submit"
                class="px-5 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition">
                {{ $priceList?->exists ? 'Guardar cambios' : 'Crear lista' }}
            </button>
        </div>
    </form>
</div>
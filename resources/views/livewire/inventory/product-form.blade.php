<div class="max-w-3xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('inventory.index') }}" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="text-xl font-medium text-gray-900">
            {{ $product?->exists ? 'Editar producto' : 'Nuevo producto' }}
        </h1>
    </div>

    <form wire:submit="save" class="space-y-5">

        {{-- Información general --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">Información general</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">Nombre del producto *</label>
                    <input wire:model.live="name" type="text"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Categoría</label>
                    <select wire:model="category_id"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        <option value="">— Sin categoría —</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Unidad de medida</label>
                    <select wire:model="unit_of_measure_id"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        <option value="">— Sin unidad —</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->abbreviation }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Proveedor</label>
                    <select wire:model="supplier_id"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        <option value="">— Sin proveedor —</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                    @error('supplier_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">Descripción</label>
                    <textarea wire:model="description" rows="3"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"></textarea>
                </div>
            </div>
        </div>

        {{-- Identificadores --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">Identificadores</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">SKU</label>
                    <div class="flex gap-2">
                        <input wire:model="sku" type="text"
                            class="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 font-mono"
                            placeholder="Auto-generado">
                        <button type="button" wire:click="regenerateSku"
                            class="px-3 py-2 text-xs border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-500 whitespace-nowrap">
                            Regenerar
                        </button>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Se genera automáticamente al escribir el nombre</p>
                    @error('sku') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Código de barras</label>
                    <div class="flex gap-2">
                        <input wire:model="barcode" type="text"
                            class="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 font-mono"
                            placeholder="EAN-13">
                        <button type="button" wire:click="regenerateBarcode"
                            class="px-3 py-2 text-xs border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-500 whitespace-nowrap">
                            Regenerar
                        </button>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">EAN-13 generado automáticamente</p>
                    @error('barcode') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Precios --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">Precios y margen</h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Precio de obtención *</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-sm text-gray-400">$</span>
                        <input wire:model.live="purchase_price" type="number" step="0.01" min="0"
                            class="w-full border border-gray-200 rounded-lg pl-7 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    </div>
                    @error('purchase_price') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Margen de utilidad *</label>
                    <div class="relative">
                        <input wire:model.live="profit_margin" type="number" step="0.01" min="0" max="999"
                            class="w-full border border-gray-200 rounded-lg pl-3 pr-8 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        <span class="absolute right-3 top-2 text-sm text-gray-400">%</span>
                    </div>
                    @error('profit_margin') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Gastos de operación</label>
                    <div class="relative">
                        <input wire:model.live="operational_costs" type="number" step="0.01" min="0" max="999"
                            class="w-full border border-gray-200 rounded-lg pl-3 pr-8 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        <span class="absolute right-3 top-2 text-sm text-gray-400">%</span>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">% sobre precio de obtención (flete, maniobras, etc.)</p>
                    @error('operational_costs') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Panel de resumen de precios --}}
            <div class="mt-3 bg-gray-50 rounded-lg border border-gray-100 p-4">
                <p class="text-xs font-medium text-gray-600 mb-3">Resumen de precios</p>
                <div class="grid grid-cols-3 gap-4 text-center">
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Precio de venta normal</p>
                        <p class="text-base font-semibold text-indigo-600">${{ number_format($this->normalSalePrice, 2) }}</p>
                        <p class="text-xs text-gray-400">costo × (1 + {{ number_format((float)$profit_margin, 2) }}%)</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Precio mínimo de venta</p>
                        <p class="text-base font-semibold text-amber-600">${{ number_format($this->minSalePrice, 2) }}</p>
                        <p class="text-xs text-gray-400">costo × (1 + {{ number_format((float)$operational_costs, 2) }}%)</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Desc. máx. (margen disponible)</p>
                        @php
                            $maxDiscPct = $this->normalSalePrice > 0
                                ? round(max(0, ($this->normalSalePrice - $this->minSalePrice) / $this->normalSalePrice * 100), 2)
                                : 0;
                        @endphp
                        <p class="text-base font-semibold {{ $maxDiscPct > 0 ? 'text-green-600' : 'text-gray-400' }}">
                            {{ number_format($maxDiscPct, 2) }}%
                        </p>
                        <p class="text-xs text-gray-400">${{ number_format($this->maxDiscount, 2) }} sobre precio venta</p>
                    </div>
                </div>
                @if($this->maxDiscount <= 0 && (float)$purchase_price > 0)
                    <p class="text-xs text-amber-600 mt-3 text-center">
                        Los gastos de operación superan o igualan el margen — no hay descuento posible sin perder capital.
                    </p>
                @endif
            </div>
        </div>

        {{-- Stock --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">Control de stock</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Stock mínimo</label>
                    <input wire:model="min_stock" type="number" step="0.01" min="0"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    <p class="text-xs text-gray-400 mt-1">Alerta cuando el stock baje de este nivel</p>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Stock máximo</label>
                    <input wire:model="max_stock" type="number" step="0.01" min="0"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    <p class="text-xs text-gray-400 mt-1">Nivel máximo recomendado de inventario</p>
                </div>
            </div>
        </div>

        {{-- Imágenes --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">Imágenes</h2>

            @if(count($existingImages) > 0)
                <div class="grid grid-cols-4 gap-3 mb-3">
                    @foreach($existingImages as $image)
                        <div class="relative group">
                            <img src="{{ Storage::url($image['path']) }}"
                                class="w-full aspect-square object-cover rounded-lg border {{ $image['is_primary'] ? 'border-indigo-400 ring-2 ring-indigo-300' : 'border-gray-200' }}">
                            <div class="absolute inset-0 bg-black/40 rounded-lg opacity-0 group-hover:opacity-100 transition flex items-center justify-center gap-1">
                                @if(!$image['is_primary'])
                                    <button type="button" wire:click="setPrimaryImage({{ $image['id'] }})"
                                        class="text-xs bg-white text-gray-700 px-2 py-1 rounded font-medium">Principal</button>
                                @endif
                                <button type="button" wire:click="removeExistingImage({{ $image['id'] }})"
                                    class="text-xs bg-red-500 text-white px-2 py-1 rounded font-medium">Quitar</button>
                            </div>
                            @if($image['is_primary'])
                                <span class="absolute top-1 left-1 text-[10px] bg-indigo-500 text-white px-1.5 py-0.5 rounded">Principal</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            <div>
                <label class="block text-xs text-gray-500 mb-1">
                    {{ count($existingImages) > 0 ? 'Agregar más imágenes' : 'Imágenes del producto' }}
                </label>
                <input wire:model="images" type="file" accept="image/*" multiple
                    class="w-full text-sm text-gray-500">
                <p class="text-xs text-gray-400 mt-1">Puedes seleccionar múltiples imágenes. La primera será la principal.</p>
            </div>
        </div>

        {{-- Estado --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <label class="flex items-center gap-3 cursor-pointer">
                <input wire:model="is_active" type="checkbox" class="w-4 h-4 rounded text-indigo-600">
                <div>
                    <p class="text-sm font-medium text-gray-800">Producto activo</p>
                    <p class="text-xs text-gray-400">Los productos inactivos no aparecerán en ventas ni compras</p>
                </div>
            </label>
        </div>

        <div class="flex items-center justify-end gap-3 pb-6">
            <a href="{{ route('inventory.index') }}"
                class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                Cancelar
            </a>
            <button type="submit"
                class="px-5 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition">
                {{ $product?->exists ? 'Guardar cambios' : 'Crear producto' }}
            </button>
        </div>
    </form>
</div>

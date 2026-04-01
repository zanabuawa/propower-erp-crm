<div class="max-w-3xl">
    <div class="flex items-center gap-3 mb-6">
        <a wire:navigate href="{{ route('inventory.index') }}" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="text-xl font-medium text-gray-900">
            {{ $product?->exists ? 'Editar ' . ($product->type === 'service' ? 'servicio' : 'producto') : 'Nuevo producto / servicio' }}
        </h1>
    </div>

    <form wire:submit="save" class="space-y-5">

        {{-- ── Tipo ─────────────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="text-sm font-medium text-gray-700 mb-3">Tipo</h2>
            <div class="flex gap-3">
                <label class="flex-1 cursor-pointer">
                    <input wire:model.live="type" type="radio" value="product" class="sr-only peer">
                    <div class="flex items-center gap-3 border-2 rounded-xl px-4 py-3 transition
                        peer-checked:border-indigo-500 peer-checked:bg-indigo-50 border-gray-200 hover:border-gray-300">
                        <div class="w-9 h-9 rounded-lg bg-indigo-100 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-800">Producto</p>
                            <p class="text-xs text-gray-400">Artículo físico con inventario</p>
                        </div>
                    </div>
                </label>
                <label class="flex-1 cursor-pointer">
                    <input wire:model.live="type" type="radio" value="service" class="sr-only peer">
                    <div class="flex items-center gap-3 border-2 rounded-xl px-4 py-3 transition
                        peer-checked:border-violet-500 peer-checked:bg-violet-50 border-gray-200 hover:border-gray-300">
                        <div class="w-9 h-9 rounded-lg bg-violet-100 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-800">Servicio</p>
                            <p class="text-xs text-gray-400">Servicio ofrecido por la empresa</p>
                        </div>
                    </div>
                </label>
            </div>
        </div>

        {{-- ── Información general ──────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">Información general</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                <div class="sm:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">Nombre *</label>
                    <input wire:model.live="name" type="text"
                        placeholder="{{ $type === 'service' ? 'Ej. Instalación eléctrica, Consultoría…' : 'Ej. Cable coaxial RG6…' }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Categoría --}}
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="text-xs text-gray-500">Categoría</label>
                        <button type="button" wire:click="$set('showCategoryModal', true)"
                            class="text-[10px] text-indigo-500 hover:text-indigo-700 flex items-center gap-0.5">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Nueva
                        </button>
                    </div>
                    <select wire:model.live="category_id"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        <option value="">— Sin categoría —</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Subcategoría --}}
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="text-xs text-gray-500">Subcategoría</label>
                        @if($category_id)
                        <button type="button" wire:click="$set('showSubcategoryModal', true)"
                            class="text-[10px] text-indigo-500 hover:text-indigo-700 flex items-center gap-0.5">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Nueva
                        </button>
                        @endif
                    </div>
                    <select wire:model="subcategory_id"
                        @if($subcategories->isEmpty()) disabled @endif
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 disabled:bg-gray-50 disabled:text-gray-400">
                        <option value="">{{ $subcategories->isEmpty() ? '— Selecciona una categoría —' : '— Sin subcategoría —' }}</option>
                        @foreach($subcategories as $sub)
                            <option value="{{ $sub->id }}">{{ $sub->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Unidad de medida --}}
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="text-xs text-gray-500">Unidad de medida</label>
                        <button type="button" wire:click="$set('showUnitModal', true)"
                            class="text-[10px] text-indigo-500 hover:text-indigo-700 flex items-center gap-0.5">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Nueva
                        </button>
                    </div>
                    <select wire:model="unit_of_measure_id"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        <option value="">— Sin unidad —</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->abbreviation }})</option>
                        @endforeach
                    </select>
                </div>

                {{-- Proveedor (solo productos) --}}
                @if($type === 'product')
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="text-xs text-gray-500">Proveedor</label>
                        <button type="button" wire:click="$set('showSupplierModal', true)"
                            class="text-[10px] text-indigo-500 hover:text-indigo-700 flex items-center gap-0.5">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Nuevo
                        </button>
                    </div>
                    <select wire:model="supplier_id"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        <option value="">— Sin proveedor —</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                {{-- Marca / Modelo / Color (solo productos) --}}
                @if($type === 'product')
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Marca</label>
                    <input wire:model="brand" type="text" placeholder="Ej. Samsung, Bosch…"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Modelo</label>
                    <input wire:model="model" type="text" placeholder="Ej. Galaxy S24…"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Color</label>
                    <input wire:model="color" type="text" placeholder="Ej. Negro, RAL 5015…"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>
                @endif

                <div class="{{ $type === 'product' ? '' : 'sm:col-span-2' }}">
                    <label class="block text-xs text-gray-500 mb-1">Descripción</label>
                    <textarea wire:model="description" rows="3"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"></textarea>
                </div>
            </div>
        </div>

        {{-- ── Identificadores ──────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">Identificadores</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">SKU</label>
                    <div class="flex gap-2">
                        <input wire:model.live="sku" type="text"
                            class="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 font-mono"
                            placeholder="Auto-generado al escribir nombre">
                        <button type="button" wire:click="regenerateSku"
                            class="px-3 py-2 text-xs border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-500 whitespace-nowrap">
                            Regenerar
                        </button>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Se genera automáticamente (mín. 3 caracteres)</p>
                    @error('sku') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                @if($type === 'product')
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
                </div>
                @endif
            </div>
        </div>

        {{-- ── Códigos SAT (CFDI) ───────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">Códigos SAT (CFDI)</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Clave de producto/servicio</label>
                    <select wire:model="sat_product_code"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        <option value="">— Seleccionar —</option>
                        @foreach(\App\Models\Product::SAT_PRODUCT_CODES as $code => $label)
                            <option value="{{ $code }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-400 mt-1">ClaveProdServ del SAT requerida para CFDI</p>
                    @error('sat_product_code') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Clave de unidad</label>
                    <select wire:model="sat_unit_code"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        <option value="">— Seleccionar —</option>
                        @foreach(\App\Models\Product::SAT_UNIT_CODES as $code => $label)
                            <option value="{{ $code }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-400 mt-1">ClaveUnidad del SAT requerida para CFDI</p>
                    @error('sat_unit_code') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- ── Precios ───────────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">Precios y margen</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">
                        {{ $type === 'service' ? 'Costo del servicio *' : 'Precio de obtención *' }}
                    </label>
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
            </div>
            <div class="bg-gray-50 rounded-lg border border-gray-100 p-4 grid grid-cols-2 gap-4 text-center">
                <div>
                    <p class="text-xs text-gray-400 mb-1">Precio de venta</p>
                    <p class="text-base font-semibold text-indigo-600">${{ number_format($this->normalSalePrice, 2) }}</p>
                    <p class="text-xs text-gray-400">costo × (1 + {{ number_format((float)$profit_margin, 2) }}%)</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-1">{{ $type === 'service' ? 'Notas' : 'Gastos de operación' }}</p>
                    <p class="text-sm font-medium text-gray-500">
                        {{ $type === 'service' ? 'El precio puede ajustarse por cotización' : 'Se asignan en recepción de mercancías' }}
                    </p>
                </div>
            </div>
        </div>

        {{-- ── Control de stock (solo productos) ───────────────────────────── --}}
        @if($type === 'product')
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
        @endif

        {{-- ── Imágenes (solo productos) ────────────────────────────────────── --}}
        @if($type === 'product')
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">Imágenes</h2>
            @if(count($existingImages) > 0)
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-3">
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
                <input wire:model="images" type="file" accept="image/*" multiple class="w-full text-sm text-gray-500">
                <p class="text-xs text-gray-400 mt-1">Puedes seleccionar múltiples imágenes. La primera será la principal.</p>
            </div>
        </div>
        @endif

        {{-- ── Estado ───────────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <label class="flex items-center gap-3 cursor-pointer">
                <input wire:model="is_active" type="checkbox" class="w-4 h-4 rounded text-indigo-600">
                <div>
                    <p class="text-sm font-medium text-gray-800">Activo</p>
                    <p class="text-xs text-gray-400">Los registros inactivos no aparecerán en ventas ni compras</p>
                </div>
            </label>
        </div>

        <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pb-6">
            <a wire:navigate href="{{ route('inventory.index') }}"
                class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition text-center">
                Cancelar
            </a>
            <button type="submit"
                class="px-5 py-2 text-sm {{ $type === 'service' ? 'bg-violet-600 hover:bg-violet-700' : 'bg-indigo-600 hover:bg-indigo-700' }} text-white rounded-lg font-medium transition">
                {{ $product?->exists ? 'Guardar cambios' : ($type === 'service' ? 'Crear servicio' : 'Crear producto') }}
            </button>
        </div>
    </form>

    {{-- ═══════════════════════════════════════════════════
         MODALES CREAR AL VUELO
    ═══════════════════════════════════════════════════ --}}

    {{-- Modal: Nueva categoría --}}
    @if($showCategoryModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40"
         x-data x-on:keydown.escape.window="$wire.set('showCategoryModal', false)">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 space-y-4" @click.stop>
            <div class="flex items-center justify-between">
                <h3 class="text-base font-semibold text-gray-800">Nueva categoría</h3>
                <button type="button" wire:click="$set('showCategoryModal', false)" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Nombre *</label>
                <input wire:model="newCategoryName" type="text" autofocus
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                    placeholder="Ej. Electrónica, Ferretería…">
                @error('newCategoryName') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Color</label>
                <div class="flex items-center gap-3">
                    <input wire:model="newCategoryColor" type="color"
                        class="h-8 w-14 rounded border border-gray-200 cursor-pointer p-0.5">
                    <span class="text-xs text-gray-400 font-mono">{{ $newCategoryColor }}</span>
                </div>
            </div>
            <div class="flex gap-2 pt-2">
                <button type="button" wire:click="saveCategory"
                    class="flex-1 px-4 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition">
                    Crear categoría
                </button>
                <button type="button" wire:click="$set('showCategoryModal', false)"
                    class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal: Nueva subcategoría --}}
    @if($showSubcategoryModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40"
         x-data x-on:keydown.escape.window="$wire.set('showSubcategoryModal', false)">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 space-y-4" @click.stop>
            <div class="flex items-center justify-between">
                <h3 class="text-base font-semibold text-gray-800">Nueva subcategoría</h3>
                <button type="button" wire:click="$set('showSubcategoryModal', false)" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <p class="text-xs text-gray-400 -mt-2">
                Se creará dentro de
                <span class="font-semibold text-gray-700">{{ $categories->firstWhere('id', $category_id)?->name ?? '—' }}</span>
            </p>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Nombre *</label>
                <input wire:model="newSubcategoryName" type="text" autofocus
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                    placeholder="Ej. Cables, Conectores…">
                @error('newSubcategoryName') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="flex gap-2 pt-2">
                <button type="button" wire:click="saveSubcategory"
                    class="flex-1 px-4 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition">
                    Crear subcategoría
                </button>
                <button type="button" wire:click="$set('showSubcategoryModal', false)"
                    class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal: Nueva unidad --}}
    @if($showUnitModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40"
         x-data x-on:keydown.escape.window="$wire.set('showUnitModal', false)">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 space-y-4" @click.stop>
            <div class="flex items-center justify-between">
                <h3 class="text-base font-semibold text-gray-800">Nueva unidad de medida</h3>
                <button type="button" wire:click="$set('showUnitModal', false)" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Nombre *</label>
                <input wire:model="newUnitName" type="text" autofocus
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                    placeholder="Ej. Kilogramo, Metro, Litro…">
                @error('newUnitName') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Abreviatura *</label>
                <input wire:model="newUnitAbbr" type="text" maxlength="10"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                    placeholder="Ej. kg, m, L, pza…">
                @error('newUnitAbbr') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="flex gap-2 pt-2">
                <button type="button" wire:click="saveUnit"
                    class="flex-1 px-4 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition">
                    Crear unidad
                </button>
                <button type="button" wire:click="$set('showUnitModal', false)"
                    class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal: Nuevo proveedor --}}
    @if($showSupplierModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40"
         x-data x-on:keydown.escape.window="$wire.set('showSupplierModal', false)">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 space-y-4" @click.stop>
            <div class="flex items-center justify-between">
                <h3 class="text-base font-semibold text-gray-800">Nuevo proveedor</h3>
                <button type="button" wire:click="$set('showSupplierModal', false)" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <p class="text-xs text-gray-400 -mt-2">Registro rápido. Puedes completar sus datos desde el catálogo de proveedores.</p>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Nombre / Empresa *</label>
                <input wire:model="newSupplierName" type="text" autofocus
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                    placeholder="Ej. Distribuidora Norte S.A.">
                @error('newSupplierName') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Teléfono</label>
                <input wire:model="newSupplierPhone" type="text"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                    placeholder="Opcional">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Email</label>
                <input wire:model="newSupplierEmail" type="email"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                    placeholder="Opcional">
            </div>
            <div class="flex gap-2 pt-2">
                <button type="button" wire:click="saveSupplier"
                    class="flex-1 px-4 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition">
                    Crear proveedor
                </button>
                <button type="button" wire:click="$set('showSupplierModal', false)"
                    class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
    @endif

</div>

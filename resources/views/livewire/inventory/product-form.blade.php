<div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center gap-3 mb-6 lg:mb-8">
        <a wire:navigate href="{{ route('inventory.index') }}" 
           class="text-gray-400 hover:text-gray-600 transition-colors duration-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="text-xl sm:text-2xl font-semibold bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent">
            {{ $product?->exists ? 'Editar ' . ($product->type === 'service' ? 'servicio' : 'producto') : 'Nuevo producto / servicio' }}
        </h1>
    </div>

    <form wire:submit="save" class="space-y-5 lg:space-y-6">

        {{-- ── Tipo ─────────────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl lg:rounded-2xl border border-gray-200 p-4 lg:p-6 shadow-sm hover:shadow-md transition-shadow duration-300">
            <h2 class="text-sm font-semibold text-gray-700 mb-3 lg:mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l5 5a2 2 0 01.586 1.414V19a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z"/>
                </svg>
                Tipo
            </h2>
            <div class="flex flex-col sm:flex-row gap-3 lg:gap-4">
                <label class="flex-1 cursor-pointer group">
                    <input wire:model.live="type" type="radio" value="product" class="sr-only peer">
                    <div class="flex items-center gap-3 border-2 rounded-xl px-3 lg:px-4 py-2 lg:py-3 transition-all duration-200
                        peer-checked:border-indigo-500 peer-checked:bg-indigo-50 peer-checked:shadow-sm
                        border-gray-200 hover:border-indigo-300 group-hover:shadow-md">
                        <div class="w-8 h-8 lg:w-10 lg:h-10 rounded-lg lg:rounded-xl bg-indigo-100 flex items-center justify-center shrink-0 transition-all group-hover:scale-105">
                            <svg class="w-4 h-4 lg:w-5 lg:h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-800">Producto</p>
                            <p class="text-xs text-gray-400 hidden sm:block">Artículo físico con inventario</p>
                        </div>
                    </div>
                </label>
                <label class="flex-1 cursor-pointer group">
                    <input wire:model.live="type" type="radio" value="service" class="sr-only peer">
                    <div class="flex items-center gap-3 border-2 rounded-xl px-3 lg:px-4 py-2 lg:py-3 transition-all duration-200
                        peer-checked:border-violet-500 peer-checked:bg-violet-50 peer-checked:shadow-sm
                        border-gray-200 hover:border-violet-300 group-hover:shadow-md">
                        <div class="w-8 h-8 lg:w-10 lg:h-10 rounded-lg lg:rounded-xl bg-violet-100 flex items-center justify-center shrink-0 transition-all group-hover:scale-105">
                            <svg class="w-4 h-4 lg:w-5 lg:h-5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-800">Servicio</p>
                            <p class="text-xs text-gray-400 hidden sm:block">Servicio ofrecido por la empresa</p>
                        </div>
                    </div>
                </label>
            </div>
        </div>

        {{-- ── Información general ──────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl lg:rounded-2xl border border-gray-200 p-4 lg:p-6 shadow-sm hover:shadow-md transition-shadow duration-300">
            <h2 class="text-sm font-semibold text-gray-700 mb-3 lg:mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Información general
            </h2>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-5">

                <div class="lg:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Nombre *</label>
                    <input wire:model.live="name" type="text"
                        placeholder="{{ $type === 'service' ? 'Ej. Instalación eléctrica, Consultoría…' : 'Ej. Cable coaxial RG6…' }}"
                        class="w-full border border-gray-200 rounded-lg lg:rounded-xl px-3 lg:px-4 py-2 lg:py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                    @error('name') <p class="text-xs text-red-500 mt-1.5 flex items-center gap-1"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>{{ $message }}</p> @enderror
                </div>

                {{-- Categoría --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="text-xs font-medium text-gray-600">Categoría</label>
                        <button type="button" wire:click="$set('showCategoryModal', true)"
                            class="text-xs text-indigo-600 hover:text-indigo-700 flex items-center gap-1 font-medium transition-colors">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Nueva
                        </button>
                    </div>
                    <select wire:model.live="category_id"
                        class="w-full border border-gray-200 rounded-lg lg:rounded-xl px-3 lg:px-4 py-2 lg:py-2.5 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all cursor-pointer hover:border-indigo-300">
                        <option value="">— Sin categoría —</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Subcategoría --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="text-xs font-medium text-gray-600">Subcategoría</label>
                        @if($category_id)
                        <button type="button" wire:click="$set('showSubcategoryModal', true)"
                            class="text-xs text-indigo-600 hover:text-indigo-700 flex items-center gap-1 font-medium transition-colors">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Nueva
                        </button>
                        @endif
                    </div>
                    <select wire:model="subcategory_id"
                        @if($subcategories->isEmpty()) disabled @endif
                        class="w-full border border-gray-200 rounded-lg lg:rounded-xl px-3 lg:px-4 py-2 lg:py-2.5 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all disabled:bg-gray-50 disabled:text-gray-400 disabled:cursor-not-allowed">
                        <option value="">{{ $subcategories->isEmpty() ? '— Selecciona una categoría —' : '— Sin subcategoría —' }}</option>
                        @foreach($subcategories as $sub)
                            <option value="{{ $sub->id }}">{{ $sub->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Unidad de medida --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="text-xs font-medium text-gray-600">Unidad de medida</label>
                        <button type="button" wire:click="$set('showUnitModal', true)"
                            class="text-xs text-indigo-600 hover:text-indigo-700 flex items-center gap-1 font-medium transition-colors">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Nueva
                        </button>
                    </div>
                    <select wire:model="unit_of_measure_id"
                        class="w-full border border-gray-200 rounded-lg lg:rounded-xl px-3 lg:px-4 py-2 lg:py-2.5 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all cursor-pointer hover:border-indigo-300">
                        <option value="">— Sin unidad —</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->abbreviation }})</option>
                        @endforeach
                    </select>
                </div>

                {{-- Proveedor (solo productos) --}}
                @if($type === 'product')
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="text-xs font-medium text-gray-600">Proveedor</label>
                        <button type="button" wire:click="$set('showSupplierModal', true)"
                            class="text-xs text-indigo-600 hover:text-indigo-700 flex items-center gap-1 font-medium transition-colors">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Nuevo
                        </button>
                    </div>
                    <select wire:model="supplier_id"
                        class="w-full border border-gray-200 rounded-lg lg:rounded-xl px-3 lg:px-4 py-2 lg:py-2.5 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all cursor-pointer hover:border-indigo-300">
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
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Marca</label>
                    <input wire:model="brand" type="text" placeholder="Ej. Samsung, Bosch…"
                        class="w-full border border-gray-200 rounded-lg lg:rounded-xl px-3 lg:px-4 py-2 lg:py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Modelo</label>
                    <input wire:model="model" type="text" placeholder="Ej. Galaxy S24…"
                        class="w-full border border-gray-200 rounded-lg lg:rounded-xl px-3 lg:px-4 py-2 lg:py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Color</label>
                    <input wire:model="color" type="text" placeholder="Ej. Negro, RAL 5015…"
                        class="w-full border border-gray-200 rounded-lg lg:rounded-xl px-3 lg:px-4 py-2 lg:py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                </div>
                @endif

                <div class="{{ $type === 'product' ? 'lg:col-span-2' : 'lg:col-span-2' }}">
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Descripción</label>
                    <textarea wire:model="description" rows="3"
                        class="w-full border border-gray-200 rounded-lg lg:rounded-xl px-3 lg:px-4 py-2 lg:py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all resize-none"></textarea>
                </div>
            </div>
        </div>

        {{-- ── Identificadores ──────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl lg:rounded-2xl border border-gray-200 p-4 lg:p-6 shadow-sm hover:shadow-md transition-shadow duration-300">
            <h2 class="text-sm font-semibold text-gray-700 mb-3 lg:mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20h10M5 8h14M5 4h14M5 12h14M5 16h10"/>
                </svg>
                Identificadores
            </h2>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-5">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">SKU</label>
                    <div class="flex gap-2">
                        <input wire:model.live="sku" type="text"
                            class="flex-1 border border-gray-200 rounded-lg lg:rounded-xl px-3 lg:px-4 py-2 lg:py-2.5 text-sm font-mono text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                            placeholder="Auto-generado al escribir nombre">
                        <button type="button" wire:click="regenerateSku"
                            class="px-3 lg:px-4 py-2 lg:py-2.5 text-xs font-medium border border-gray-200 rounded-lg lg:rounded-xl hover:bg-gray-50 hover:border-gray-300 text-gray-600 transition-all whitespace-nowrap">
                            Regenerar
                        </button>
                    </div>
                    <p class="text-xs text-gray-400 mt-1.5">Se genera automáticamente (mín. 3 caracteres)</p>
                    @error('sku') <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p> @enderror
                </div>
                @if($type === 'product')
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Código de barras</label>
                    <div class="flex gap-2">
                        <input wire:model="barcode" type="text"
                            class="flex-1 border border-gray-200 rounded-lg lg:rounded-xl px-3 lg:px-4 py-2 lg:py-2.5 text-sm font-mono text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                            placeholder="EAN-13">
                        <button type="button" wire:click="regenerateBarcode"
                            class="px-3 lg:px-4 py-2 lg:py-2.5 text-xs font-medium border border-gray-200 rounded-lg lg:rounded-xl hover:bg-gray-50 hover:border-gray-300 text-gray-600 transition-all whitespace-nowrap">
                            Regenerar
                        </button>
                    </div>
                    <p class="text-xs text-gray-400 mt-1.5">EAN-13 generado automáticamente</p>
                </div>
                @endif
            </div>
        </div>

        {{-- ── Códigos SAT (CFDI) ───────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl lg:rounded-2xl border border-gray-200 p-4 lg:p-6 shadow-sm hover:shadow-md transition-shadow duration-300">
            <h2 class="text-sm font-semibold text-gray-700 mb-3 lg:mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Códigos SAT (CFDI)
            </h2>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-5">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Clave de producto/servicio</label>
                    <select wire:model="sat_product_code"
                        class="w-full border border-gray-200 rounded-lg lg:rounded-xl px-3 lg:px-4 py-2 lg:py-2.5 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all cursor-pointer hover:border-indigo-300">
                        <option value="">— Seleccionar —</option>
                        @foreach(\App\Models\Product::SAT_PRODUCT_CODES as $code => $label)
                            <option value="{{ $code }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-400 mt-1.5">ClaveProdServ del SAT requerida para CFDI</p>
                    @error('sat_product_code') <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Clave de unidad</label>
                    <select wire:model="sat_unit_code"
                        class="w-full border border-gray-200 rounded-lg lg:rounded-xl px-3 lg:px-4 py-2 lg:py-2.5 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all cursor-pointer hover:border-indigo-300">
                        <option value="">— Seleccionar —</option>
                        @foreach(\App\Models\Product::SAT_UNIT_CODES as $code => $label)
                            <option value="{{ $code }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-400 mt-1.5">ClaveUnidad del SAT requerida para CFDI</p>
                    @error('sat_unit_code') <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- ── Precios ───────────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl lg:rounded-2xl border border-gray-200 p-4 lg:p-6 shadow-sm hover:shadow-md transition-shadow duration-300">
            <h2 class="text-sm font-semibold text-gray-700 mb-3 lg:mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Precios y margen
            </h2>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-5">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">
                        {{ $type === 'service' ? 'Costo del servicio *' : 'Precio de obtención *' }}
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 lg:left-4 top-1/2 -translate-y-1/2 text-sm text-gray-400">$</span>
                        <input wire:model.live="purchase_price" type="number" step="0.01" min="0"
                            class="w-full border border-gray-200 rounded-lg lg:rounded-xl pl-7 lg:pl-8 pr-3 lg:pr-4 py-2 lg:py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                    </div>
                    @error('purchase_price') <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Margen de utilidad *</label>
                    <div class="relative">
                        <input wire:model.live="profit_margin" type="number" step="0.01" min="0" max="999"
                            class="w-full border border-gray-200 rounded-lg lg:rounded-xl px-3 lg:px-4 py-2 lg:py-2.5 pr-7 lg:pr-8 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                        <span class="absolute right-3 lg:right-4 top-1/2 -translate-y-1/2 text-sm text-gray-400">%</span>
                    </div>
                    @error('profit_margin') <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="mt-4 lg:mt-5 bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg lg:rounded-xl border border-gray-200 p-4 lg:p-5 grid grid-cols-1 sm:grid-cols-2 gap-4 lg:gap-5">
                <div class="text-center">
                    <p class="text-xs font-medium text-gray-500 mb-1.5">Precio de venta</p>
                    <p class="text-xl lg:text-2xl font-bold text-indigo-600">${{ number_format($this->normalSalePrice, 2) }}</p>
                    <p class="text-xs text-gray-400 mt-1">costo × (1 + {{ number_format((float)$profit_margin, 2) }}%)</p>
                </div>
                <div class="text-center">
                    <p class="text-xs font-medium text-gray-500 mb-1.5">{{ $type === 'service' ? 'Notas' : 'Gastos de operación' }}</p>
                    <p class="text-xs lg:text-sm text-gray-600">
                        {{ $type === 'service' ? 'El precio puede ajustarse por cotización' : 'Se asignan en recepción de mercancías' }}
                    </p>
                </div>
            </div>
        </div>

        {{-- ── Control de stock (solo productos) ───────────────────────────── --}}
        @if($type === 'product')
        <div class="bg-white rounded-xl lg:rounded-2xl border border-gray-200 p-4 lg:p-6 shadow-sm hover:shadow-md transition-shadow duration-300">
            <h2 class="text-sm font-semibold text-gray-700 mb-3 lg:mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                Control de stock
            </h2>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-5">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Stock mínimo</label>
                    <input wire:model="min_stock" type="number" step="0.01" min="0"
                        class="w-full border border-gray-200 rounded-lg lg:rounded-xl px-3 lg:px-4 py-2 lg:py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                    <p class="text-xs text-gray-400 mt-1.5">Alerta cuando el stock baje de este nivel</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Stock máximo</label>
                    <input wire:model="max_stock" type="number" step="0.01" min="0"
                        class="w-full border border-gray-200 rounded-lg lg:rounded-xl px-3 lg:px-4 py-2 lg:py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                    <p class="text-xs text-gray-400 mt-1.5">Nivel máximo recomendado de inventario</p>
                </div>
            </div>
        </div>
        @endif

        {{-- ── Imágenes (solo productos) ────────────────────────────────────── --}}
        @if($type === 'product')
        <div class="bg-white rounded-xl lg:rounded-2xl border border-gray-200 p-4 lg:p-6 shadow-sm hover:shadow-md transition-shadow duration-300">
            <h2 class="text-sm font-semibold text-gray-700 mb-3 lg:mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Imágenes
            </h2>
            @if(count($existingImages) > 0)
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 lg:gap-4 mb-4 lg:mb-5">
                    @foreach($existingImages as $image)
                        <div class="relative group">
                            <img src="{{ Storage::url($image['path']) }}"
                                class="w-full aspect-square object-cover rounded-lg lg:rounded-xl border-2 {{ $image['is_primary'] ? 'border-indigo-500 shadow-lg shadow-indigo-100' : 'border-gray-200' }} transition-all group-hover:shadow-md">
                            <div class="absolute inset-0 bg-black/50 rounded-lg lg:rounded-xl opacity-0 group-hover:opacity-100 transition-all duration-200 flex items-center justify-center gap-2">
                                @if(!$image['is_primary'])
                                    <button type="button" wire:click="setPrimaryImage({{ $image['id'] }})"
                                        class="text-xs bg-white text-gray-700 px-2 py-1 rounded-lg font-medium hover:bg-gray-100 transition">Principal</button>
                                @endif
                                <button type="button" wire:click="removeExistingImage({{ $image['id'] }})"
                                    class="text-xs bg-red-500 text-white px-2 py-1 rounded-lg font-medium hover:bg-red-600 transition">Quitar</button>
                            </div>
                            @if($image['is_primary'])
                                <span class="absolute top-2 left-2 text-xs bg-indigo-500 text-white px-2 py-0.5 rounded-lg shadow-sm">Principal</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">
                    {{ count($existingImages) > 0 ? 'Agregar más imágenes' : 'Imágenes del producto' }}
                </label>
                <input wire:model="images" type="file" accept="image/*" multiple 
                    class="w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg lg:file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition-all cursor-pointer">
                <p class="text-xs text-gray-400 mt-1.5">Puedes seleccionar múltiples imágenes. La primera será la principal.</p>
            </div>
        </div>
        @endif

        {{-- ── Estado ───────────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl lg:rounded-2xl border border-gray-200 p-4 lg:p-6 shadow-sm hover:shadow-md transition-shadow duration-300">
            <label class="flex items-center gap-3 cursor-pointer group">
                <input wire:model="is_active" type="checkbox" class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                <div>
                    <p class="text-sm font-semibold text-gray-800 group-hover:text-indigo-600 transition-colors">Activo</p>
                    <p class="text-xs text-gray-400">Los registros inactivos no aparecerán en ventas ni compras</p>
                </div>
            </label>
        </div>

        {{-- ── Botones de acción con contenedor transparente ─────────────────── --}}
        <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pb-6 lg:pb-8 mt-4 lg:mt-6">
            <a wire:navigate href="{{ route('inventory.index') }}"
                class="px-4 lg:px-6 py-2 lg:py-2.5 text-sm font-medium border border-gray-300 hover:border-gray-400 rounded-lg lg:rounded-xl transition-all text-center text-gray-700 hover:bg-gray-50">
                Cancelar
            </a>
            <button type="submit"
                class="px-4 lg:px-6 py-2 lg:py-2.5 text-sm font-semibold {{ $type === 'service' ? 'bg-violet-600 hover:bg-violet-700' : 'bg-indigo-600 hover:bg-indigo-700' }} text-white rounded-lg lg:rounded-xl transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-[1.02]">
                {{ $product?->exists ? 'Guardar cambios' : ($type === 'service' ? 'Crear servicio' : 'Crear producto') }}
            </button>
        </div>
    </form>

    {{-- ═══════════════════════════════════════════════════
         MODALES CREAR AL VUELO
    ═══════════════════════════════════════════════════ --}}

    {{-- Modal: Nueva categoría --}}
    @if($showCategoryModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
         x-data x-on:keydown.escape.window="$wire.set('showCategoryModal', false)">
        <div class="bg-white rounded-xl lg:rounded-2xl shadow-2xl w-full max-w-md p-5 lg:p-6 space-y-4 lg:space-y-5" @click.stop>
            <div class="flex items-center justify-between">
                <h3 class="text-base lg:text-lg font-semibold text-gray-900">Nueva categoría</h3>
                <button type="button" wire:click="$set('showCategoryModal', false)" 
                    class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Nombre *</label>
                <input wire:model="newCategoryName" type="text" autofocus
                    class="w-full border border-gray-200 rounded-lg lg:rounded-xl px-3 lg:px-4 py-2 lg:py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                    placeholder="Ej. Electrónica, Ferretería…">
                @error('newCategoryName') <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Color</label>
                <div class="flex items-center gap-3">
                    <input wire:model="newCategoryColor" type="color"
                        class="h-8 lg:h-9 w-14 lg:w-16 rounded-lg border border-gray-200 cursor-pointer p-1">
                    <span class="text-xs text-gray-500 font-mono">{{ $newCategoryColor }}</span>
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" wire:click="saveCategory"
                    class="flex-1 px-4 py-2 lg:py-2.5 text-sm font-semibold bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg lg:rounded-xl transition-all">
                    Crear categoría
                </button>
                <button type="button" wire:click="$set('showCategoryModal', false)"
                    class="px-4 py-2 lg:py-2.5 text-sm font-medium border border-gray-200 rounded-lg lg:rounded-xl hover:bg-gray-50 transition-all">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal: Nueva subcategoría --}}
    @if($showSubcategoryModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
         x-data x-on:keydown.escape.window="$wire.set('showSubcategoryModal', false)">
        <div class="bg-white rounded-xl lg:rounded-2xl shadow-2xl w-full max-w-md p-5 lg:p-6 space-y-4 lg:space-y-5" @click.stop>
            <div class="flex items-center justify-between">
                <h3 class="text-base lg:text-lg font-semibold text-gray-900">Nueva subcategoría</h3>
                <button type="button" wire:click="$set('showSubcategoryModal', false)" 
                    class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <p class="text-xs text-gray-500 -mt-2">
                Se creará dentro de
                <span class="font-semibold text-indigo-600">{{ $categories->firstWhere('id', $category_id)?->name ?? '—' }}</span>
            </p>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Nombre *</label>
                <input wire:model="newSubcategoryName" type="text" autofocus
                    class="w-full border border-gray-200 rounded-lg lg:rounded-xl px-3 lg:px-4 py-2 lg:py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                    placeholder="Ej. Cables, Conectores…">
                @error('newSubcategoryName') <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p> @enderror
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" wire:click="saveSubcategory"
                    class="flex-1 px-4 py-2 lg:py-2.5 text-sm font-semibold bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg lg:rounded-xl transition-all">
                    Crear subcategoría
                </button>
                <button type="button" wire:click="$set('showSubcategoryModal', false)"
                    class="px-4 py-2 lg:py-2.5 text-sm font-medium border border-gray-200 rounded-lg lg:rounded-xl hover:bg-gray-50 transition-all">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal: Nueva unidad --}}
    @if($showUnitModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
         x-data x-on:keydown.escape.window="$wire.set('showUnitModal', false)">
        <div class="bg-white rounded-xl lg:rounded-2xl shadow-2xl w-full max-w-md p-5 lg:p-6 space-y-4 lg:space-y-5" @click.stop>
            <div class="flex items-center justify-between">
                <h3 class="text-base lg:text-lg font-semibold text-gray-900">Nueva unidad de medida</h3>
                <button type="button" wire:click="$set('showUnitModal', false)" 
                    class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Nombre *</label>
                <input wire:model="newUnitName" type="text" autofocus
                    class="w-full border border-gray-200 rounded-lg lg:rounded-xl px-3 lg:px-4 py-2 lg:py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                    placeholder="Ej. Kilogramo, Metro, Litro…">
                @error('newUnitName') <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Abreviatura *</label>
                <input wire:model="newUnitAbbr" type="text" maxlength="10"
                    class="w-full border border-gray-200 rounded-lg lg:rounded-xl px-3 lg:px-4 py-2 lg:py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                    placeholder="Ej. kg, m, L, pza…">
                @error('newUnitAbbr') <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p> @enderror
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" wire:click="saveUnit"
                    class="flex-1 px-4 py-2 lg:py-2.5 text-sm font-semibold bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg lg:rounded-xl transition-all">
                    Crear unidad
                </button>
                <button type="button" wire:click="$set('showUnitModal', false)"
                    class="px-4 py-2 lg:py-2.5 text-sm font-medium border border-gray-200 rounded-lg lg:rounded-xl hover:bg-gray-50 transition-all">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal: Nuevo proveedor --}}
    @if($showSupplierModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
         x-data x-on:keydown.escape.window="$wire.set('showSupplierModal', false)">
        <div class="bg-white rounded-xl lg:rounded-2xl shadow-2xl w-full max-w-md p-5 lg:p-6 space-y-4 lg:space-y-5" @click.stop>
            <div class="flex items-center justify-between">
                <h3 class="text-base lg:text-lg font-semibold text-gray-900">Nuevo proveedor</h3>
                <button type="button" wire:click="$set('showSupplierModal', false)" 
                    class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <p class="text-xs text-gray-500 -mt-2">Registro rápido. Puedes completar sus datos desde el catálogo de proveedores.</p>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Nombre / Empresa *</label>
                <input wire:model="newSupplierName" type="text" autofocus
                    class="w-full border border-gray-200 rounded-lg lg:rounded-xl px-3 lg:px-4 py-2 lg:py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                    placeholder="Ej. Distribuidora Norte S.A.">
                @error('newSupplierName') <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Teléfono</label>
                <input wire:model="newSupplierPhone" type="text"
                    class="w-full border border-gray-200 rounded-lg lg:rounded-xl px-3 lg:px-4 py-2 lg:py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                    placeholder="Opcional">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Email</label>
                <input wire:model="newSupplierEmail" type="email"
                    class="w-full border border-gray-200 rounded-lg lg:rounded-xl px-3 lg:px-4 py-2 lg:py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                    placeholder="Opcional">
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" wire:click="saveSupplier"
                    class="flex-1 px-4 py-2 lg:py-2.5 text-sm font-semibold bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg lg:rounded-xl transition-all">
                    Crear proveedor
                </button>
                <button type="button" wire:click="$set('showSupplierModal', false)"
                    class="px-4 py-2 lg:py-2.5 text-sm font-medium border border-gray-200 rounded-lg lg:rounded-xl hover:bg-gray-50 transition-all">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
    @endif

</div>
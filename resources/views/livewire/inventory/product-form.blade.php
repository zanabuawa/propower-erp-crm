<div class="min-h-screen bg-slate-50/50 -m-4 lg:-m-6">
    {{-- ── STICKY HEADER ────────────────────────────────────────────────── --}}
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 max-w-full mx-auto">
            <div class="flex items-center gap-3 min-w-0">
                <a wire:navigate href="{{ route('inventory.index') }}" 
                   class="group flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-100 hover:shadow-sm transition-all duration-200">
                    <svg class="w-5 h-5 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl font-bold text-slate-800 truncate">
                        {{ $product?->exists ? 'Editar ' . ($product->type === 'service' ? 'servicio' : 'producto') : 'Nuevo producto / servicio' }}
                    </h1>
                    <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">
                        {{ $product?->exists ? 'ID: ' . $product->id : 'Registro de nuevo catálogo' }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <a wire:navigate href="{{ route('inventory.index') }}"
                    class="hidden sm:inline-flex px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 transition-colors">
                    Cancelar
                </a>
                <button type="button" wire:click="save"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:scale-[1.02] active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>{{ $product?->exists ? 'Guardar cambios' : 'Crear registro' }}</span>
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-full mx-auto p-4 sm:p-6 lg:p-8">
        <form wire:submit="save" class="grid grid-cols-1 xl:grid-cols-12 gap-6 lg:gap-8">

            {{-- ── COLUMNA IZQUIERDA: Principal (8 cols) ────────────────────── --}}
            <div class="xl:col-span-8 space-y-6 lg:space-y-8">
                
                {{-- Card: Tipo y Nombre --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="p-6 lg:p-8 space-y-8">
                        {{-- Selector de Tipo --}}
                        <div class="space-y-4">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Tipo de registro</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <label class="relative cursor-pointer group">
                                    <input wire:model.live="type" type="radio" value="product" class="sr-only peer">
                                    <div class="flex items-center gap-4 p-4 rounded-2xl border-2 border-slate-100 transition-all duration-300 peer-checked:border-indigo-500 peer-checked:bg-indigo-50/30 group-hover:border-slate-200">
                                        <div class="w-12 h-12 rounded-xl bg-indigo-100 flex items-center justify-center shrink-0 transition-transform group-hover:scale-110">
                                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-800">Producto</p>
                                            <p class="text-xs text-slate-400">Bien físico tangible</p>
                                        </div>
                                        <div class="absolute top-4 right-4 opacity-0 peer-checked:opacity-100 transition-opacity">
                                            <div class="w-5 h-5 rounded-full bg-indigo-600 flex items-center justify-center shadow-sm">
                                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                                <label class="relative cursor-pointer group">
                                    <input wire:model.live="type" type="radio" value="service" class="sr-only peer">
                                    <div class="flex items-center gap-4 p-4 rounded-2xl border-2 border-slate-100 transition-all duration-300 peer-checked:border-violet-500 peer-checked:bg-violet-50/30 group-hover:border-slate-200">
                                        <div class="w-12 h-12 rounded-xl bg-violet-100 flex items-center justify-center shrink-0 transition-transform group-hover:scale-110">
                                            <svg class="w-6 h-6 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-800">Servicio</p>
                                            <p class="text-xs text-slate-400">Actividad o labor</p>
                                        </div>
                                        <div class="absolute top-4 right-4 opacity-0 peer-checked:opacity-100 transition-opacity">
                                            <div class="w-5 h-5 rounded-full bg-violet-600 flex items-center justify-center shadow-sm">
                                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- Nombre y Descripción --}}
                        <div class="space-y-6">
                            <div class="relative group">
                                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-2 group-focus-within:text-indigo-500 transition-colors">Nombre completo *</label>
                                <input wire:model.live="name" type="text"
                                    placeholder="{{ $type === 'service' ? 'Ej. Instalación de Redes, Consultoría Especializada...' : 'Ej. Cable Coaxial RG6 Premium, Kit de Herramientas...' }}"
                                    class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-lg font-semibold text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500/20 transition-all">
                                @error('name') <p class="text-xs text-rose-500 mt-2 font-medium flex items-center gap-1"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>{{ $message }}</p> @enderror
                            </div>

                            <div class="relative group">
                                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-2 group-focus-within:text-indigo-500 transition-colors">Descripción detallada</label>
                                <textarea wire:model="description" rows="4"
                                    placeholder="Detalla las especificaciones, características técnicas o alcances..."
                                    class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm text-slate-700 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500/20 transition-all resize-none"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card: Clasificación y Detalles Técnicos --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/30">
                        <h2 class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Clasificación y Atributos</h2>
                    </div>
                    <div class="p-6 lg:p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                        
                        <div class="space-y-6">
                            {{-- Categoría --}}
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <label class="text-xs font-bold text-slate-600">Categoría principal</label>
                                    <button type="button" wire:click="$set('showCategoryModal', true)"
                                        class="text-[10px] font-bold text-indigo-600 hover:text-indigo-700 uppercase tracking-wider">+ Nueva</button>
                                </div>
                                <div class="relative">
                                    <select wire:model.live="category_id"
                                        class="w-full bg-slate-50 border-none rounded-xl px-4 py-3 text-sm text-slate-700 focus:ring-2 focus:ring-indigo-500/20 cursor-pointer appearance-none pr-10" style="-webkit-appearance: none; -moz-appearance: none;">
                                        <option value="">— Sin categoría —</option>
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </div>
                                </div>
                            </div>

                            {{-- Subcategoría --}}
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <label class="text-xs font-bold text-slate-600">Subcategoría</label>
                                    @if($category_id)
                                    <button type="button" wire:click="$set('showSubcategoryModal', true)"
                                        class="text-[10px] font-bold text-indigo-600 hover:text-indigo-700 uppercase tracking-wider">+ Nueva</button>
                                    @endif
                                </div>
                                <div class="relative">
                                    <select wire:model="subcategory_id"
                                        @if($subcategories->isEmpty()) disabled @endif
                                        class="w-full bg-slate-50 border-none rounded-xl px-4 py-3 text-sm text-slate-700 focus:ring-2 focus:ring-indigo-500/20 disabled:opacity-50 disabled:cursor-not-allowed appearance-none pr-10" style="-webkit-appearance: none; -moz-appearance: none;">
                                        <option value="">{{ $subcategories->isEmpty() ? '— Selecciona una categoría —' : '— Sin subcategoría —' }}</option>
                                        @foreach($subcategories as $sub)
                                            <option value="{{ $sub->id }}">{{ $sub->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-6">
                            @if($type === 'product')
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <label class="text-xs font-bold text-slate-600">Marca</label>
                                        <input wire:model="brand" type="text" placeholder="Ej. Bosch"
                                            class="w-full bg-slate-50 border-none rounded-xl px-4 py-3 text-sm text-slate-700 focus:ring-2 focus:ring-indigo-500/20">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-xs font-bold text-slate-600">Modelo</label>
                                        <input wire:model="model" type="text" placeholder="Ej. Pro-2024"
                                            class="w-full bg-slate-50 border-none rounded-xl px-4 py-3 text-sm text-slate-700 focus:ring-2 focus:ring-indigo-500/20">
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs font-bold text-slate-600">Color / Variante</label>
                                    <input wire:model="color" type="text" placeholder="Ej. Azul Industrial, RAL 7035..."
                                        class="w-full bg-slate-50 border-none rounded-xl px-4 py-3 text-sm text-slate-700 focus:ring-2 focus:ring-indigo-500/20">
                                </div>
                            @endif

                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <label class="text-xs font-bold text-slate-600">Proveedor preferente</label>
                                    <button type="button" wire:click="$set('showSupplierModal', true)"
                                        class="text-[10px] font-bold text-indigo-600 hover:text-indigo-700 uppercase tracking-wider">+ Nuevo</button>
                                </div>
                                <div class="relative">
                                    <select wire:model="supplier_id"
                                        class="w-full bg-slate-50 border-none rounded-xl px-4 py-3 text-sm text-slate-700 focus:ring-2 focus:ring-indigo-500/20 appearance-none pr-10" style="-webkit-appearance: none; -moz-appearance: none;">
                                        <option value="">— Sin proveedor —</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card: Precios y Fiscal --}}
                @canany(['edit product prices', 'view product cost'])
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/30 flex items-center justify-between">
                        <h2 class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Precios y Configuración Fiscal</h2>
                        <div class="flex items-center gap-3 bg-white px-3 py-1.5 rounded-xl border border-slate-100 shadow-sm">
                            <span class="text-[10px] font-bold {{ $purchase_price_includes_iva ? 'text-indigo-600' : 'text-slate-400' }} uppercase tracking-tight">
                                {{ $purchase_price_includes_iva ? 'Costo con IVA incluido' : 'Costo sin IVA' }}
                            </span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input wire:model.live="purchase_price_includes_iva" type="checkbox" class="sr-only peer">
                                <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-indigo-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all"></div>
                            </label>
                        </div>
                    </div>
                    <div class="p-6 lg:p-8">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="space-y-2">
                                <label class="text-xs font-bold text-slate-600">{{ $type === 'service' ? 'Costo base *' : 'Precio de compra *' }}</label>
                                <div class="relative group">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">$</span>
                                    <input wire:model.live="purchase_price" type="number" step="0.01"
                                        class="w-full bg-slate-50 border-none rounded-xl pl-8 pr-4 py-3 text-lg font-bold text-slate-800 focus:ring-2 focus:ring-indigo-500/20">
                                </div>
                                @error('purchase_price') <p class="text-[10px] text-rose-500 font-medium">{{ $message }}</p> @enderror
                            </div>

<div class="space-y-2">
                                <label class="text-xs font-bold text-slate-600">Margen de utilidad *</label>
                                <div class="relative">
                                    <input wire:model.live="profit_margin" type="number" step="0.1"
                                        class="w-full bg-slate-50 border-none rounded-xl px-4 py-3 text-lg font-bold text-slate-800 focus:ring-2 focus:ring-indigo-500/20">
                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">%</span>
                                </div>
                                @error('profit_margin') <p class="text-[10px] text-rose-500 font-medium">{{ $message }}</p> @enderror
                            </div>

                            <div class="bg-indigo-600 rounded-2xl p-4 text-white shadow-lg shadow-indigo-200">
                                <p class="text-[10px] font-bold uppercase tracking-widest opacity-80">Precio sugerido de venta</p>
                                <div class="flex items-baseline gap-1 mt-1">
                                    <span class="text-sm font-bold opacity-80">$</span>
                                    <span class="text-2xl font-black">{{ number_format($this->normalSalePrice, 2) }}</span>
                                </div>
                                <p class="text-[9px] mt-2 font-medium opacity-70 italic">* Costo / (1 − margen%)</p>
                            </div>
                        </div>

                        {{-- Códigos SAT --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-10 pt-8 border-t border-slate-100">
                            {{-- SAT Producto --}}
                            @php
                                $satProductList = collect(\App\Models\Product::SAT_PRODUCT_CODES)->map(fn($l,$c)=>['code'=>$c,'label'=>$l])->values();
                                $satUnitList = collect(\App\Models\Product::SAT_UNIT_CODES)->map(fn($l,$c)=>['code'=>$c,'label'=>$l])->values();
                            @endphp
                            
                            {{-- SAT Producto Modal Selector --}}
                            <div class="space-y-2" x-data="{ 
                                open: false, 
                                search: '', 
                                value: $wire.entangle('sat_product_code'), 
                                items: {{ $satProductList->toJson() }},
                                get filteredItems() {
                                    return this.items.filter(i => 
                                        i.label.toLowerCase().includes(this.search.toLowerCase()) || 
                                        i.code.includes(this.search)
                                    )
                                }
                            }">
                                <label class="text-xs font-bold text-slate-600">Clave SAT (Prod/Serv)</label>
                                <button type="button" @click="open = true; search = ''" 
                                    class="w-full bg-slate-50 text-left border border-slate-100 rounded-xl px-4 py-3 text-sm text-slate-700 flex items-center justify-between hover:bg-slate-100 transition-colors">
                                    <span class="truncate" x-text="items.find(i => i.code === value)?.label || 'Seleccionar clave...'"></span>
                                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                </button>

                                {{-- Modal --}}
                                <template x-teleport="body">
                                    <div x-show="open" 
                                        class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6"
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0"
                                        x-transition:enter-end="opacity-100"
                                        x-transition:leave="transition ease-in duration-150"
                                        x-transition:leave-start="opacity-100"
                                        x-transition:leave-end="opacity-0">
                                        
                                        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="open = false"></div>
                                        
                                        <div class="relative bg-white w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden"
                                            x-transition:enter="transition ease-out duration-300"
                                            x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                                            x-transition:enter-end="opacity-100 scale-100 translate-y-0">
                                            
                                            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                                                <h3 class="font-bold text-slate-800">Clave SAT (Producto/Servicio)</h3>
                                                <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </div>

                                            <div class="p-4 bg-slate-50">
                                                <div class="relative">
                                                    <input x-model="search" type="text" placeholder="Buscar por código o descripción..." 
                                                        class="w-full bg-white border-none rounded-2xl px-11 py-3 text-sm focus:ring-2 focus:ring-indigo-500/20 shadow-sm"
                                                        x-init="$watch('open', v => v && $nextTick(() => $el.focus()))">
                                                    <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                                </div>
                                            </div>

                                            <div class="max-h-[400px] overflow-y-auto p-2">
                                                <template x-for="item in filteredItems" :key="item.code">
                                                    <button type="button" @click="value = item.code; open = false" 
                                                        class="w-full text-left px-4 py-3 rounded-xl transition-all duration-200 group"
                                                        :class="value === item.code ? 'bg-indigo-600 text-white shadow-md' : 'hover:bg-indigo-50 text-slate-600'">
                                                        <div class="flex flex-col">
                                                            <span class="text-[10px] font-bold uppercase tracking-wider mb-0.5" :class="value === item.code ? 'text-indigo-200' : 'text-indigo-600'">Código: <span x-text="item.code"></span></span>
                                                            <span class="text-sm font-medium" x-text="item.label.split('- ').pop()"></span>
                                                        </div>
                                                    </button>
                                                </template>
                                                <div x-show="filteredItems.length === 0" class="p-8 text-center">
                                                    <p class="text-sm text-slate-400 italic">No se encontraron resultados...</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            {{-- SAT Unidad Modal Selector --}}
                            <div class="space-y-2" x-data="{ 
                                open: false, 
                                search: '', 
                                value: $wire.entangle('sat_unit_code'), 
                                items: {{ $satUnitList->toJson() }},
                                get filteredItems() {
                                    return this.items.filter(i => 
                                        i.label.toLowerCase().includes(this.search.toLowerCase()) || 
                                        i.code.includes(this.search)
                                    )
                                }
                            }">
                                <label class="text-xs font-bold text-slate-600">Clave SAT (Unidad)</label>
                                <button type="button" @click="open = true; search = ''" 
                                    class="w-full bg-slate-50 text-left border border-slate-100 rounded-xl px-4 py-3 text-sm text-slate-700 flex items-center justify-between hover:bg-slate-100 transition-colors">
                                    <span class="truncate" x-text="items.find(i => i.code === value)?.label || 'Seleccionar unidad...'"></span>
                                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                </button>

                                {{-- Modal --}}
                                <template x-teleport="body">
                                    <div x-show="open" 
                                        class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6"
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0"
                                        x-transition:enter-end="opacity-100"
                                        x-transition:leave="transition ease-in duration-150"
                                        x-transition:leave-start="opacity-100"
                                        x-transition:leave-end="opacity-0">
                                        
                                        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="open = false"></div>
                                        
                                        <div class="relative bg-white w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden"
                                            x-transition:enter="transition ease-out duration-300"
                                            x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                                            x-transition:enter-end="opacity-100 scale-100 translate-y-0">
                                            
                                            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                                                <h3 class="font-bold text-slate-800">Clave SAT (Unidad de Medida)</h3>
                                                <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </div>

                                            <div class="p-4 bg-slate-50">
                                                <div class="relative">
                                                    <input x-model="search" type="text" placeholder="Buscar unidad..." 
                                                        class="w-full bg-white border-none rounded-2xl px-11 py-3 text-sm focus:ring-2 focus:ring-indigo-500/20 shadow-sm"
                                                        x-init="$watch('open', v => v && $nextTick(() => $el.focus()))">
                                                    <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                                </div>
                                            </div>

                                            <div class="max-h-[400px] overflow-y-auto p-2">
                                                <template x-for="item in filteredItems" :key="item.code">
                                                    <button type="button" @click="value = item.code; open = false" 
                                                        class="w-full text-left px-4 py-3 rounded-xl transition-all duration-200 group"
                                                        :class="value === item.code ? 'bg-indigo-600 text-white shadow-md' : 'hover:bg-indigo-50 text-slate-600'">
                                                        <div class="flex flex-col">
                                                            <span class="text-[10px] font-bold uppercase tracking-wider mb-0.5" :class="value === item.code ? 'text-indigo-200' : 'text-indigo-600'">Clave: <span x-text="item.code"></span></span>
                                                            <span class="text-sm font-medium" x-text="item.label.split('- ').pop()"></span>
                                                        </div>
                                                    </button>
                                                </template>
                                                <div x-show="filteredItems.length === 0" class="p-8 text-center">
                                                    <p class="text-sm text-slate-400 italic">No se encontraron resultados...</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
                @endcanany

            </div>

            {{-- ── COLUMNA DERECHA: Identificadores y Control (4 cols) ────────── --}}
            <div class="xl:col-span-4 space-y-6 lg:space-y-8">
                
                {{-- Card: Identificadores --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="p-6 lg:p-8 space-y-6">
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">SKU / Referencia Interna</label>
                            <div class="flex gap-2">
                                <input wire:model.live="sku" type="text"
                                    class="flex-1 bg-slate-50 border-none rounded-xl px-4 py-3 text-sm font-mono font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500/20"
                                    placeholder="Auto-generado">
                                <button type="button" wire:click="regenerateSku"
                                    class="w-11 h-11 flex items-center justify-center bg-slate-100 rounded-xl text-slate-500 hover:bg-indigo-100 hover:text-indigo-600 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                </button>
                            </div>
                            @error('sku') <p class="text-[10px] text-rose-500 font-medium">{{ $message }}</p> @enderror
                        </div>

                        @if($type === 'product')
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Código de Barras (EAN-13)</label>
                            <div class="flex gap-2">
                                <input wire:model="barcode" type="text"
                                    class="flex-1 bg-slate-50 border-none rounded-xl px-4 py-3 text-sm font-mono font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500/20"
                                    placeholder="0000000000000">
                                <button type="button" wire:click="regenerateBarcode"
                                    class="w-11 h-11 flex items-center justify-center bg-slate-100 rounded-xl text-slate-500 hover:bg-indigo-100 hover:text-indigo-600 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                </button>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Card: Multimedia --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/30">
                        <h2 class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Multimedia</h2>
                    </div>
                    <div class="p-6 lg:p-8 space-y-6">
                        @if(count($existingImages) > 0)
                            <div class="grid grid-cols-2 gap-3">
                                @foreach($existingImages as $image)
                                    <div class="relative aspect-square group rounded-2xl overflow-hidden border border-slate-100 shadow-sm">
                                        <img src="{{ Storage::url($image['path']) }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                                        <div class="absolute inset-0 bg-slate-900/40 transition-opacity flex items-center justify-center gap-2 px-2">
                                            @if(!$image['is_primary'])
                                                <button type="button" wire:click="setPrimaryImage({{ $image['id'] }})" class="p-2 bg-white/20 hover:bg-white/40 text-white rounded-xl backdrop-blur-sm transition-colors" title="Marcar como principal">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                </button>
                                            @endif
                                            <button type="button" wire:click="removeExistingImage({{ $image['id'] }})" class="p-2 bg-rose-500/80 hover:bg-rose-600 text-white rounded-xl backdrop-blur-sm transition-colors" title="Eliminar">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                        @if($image['is_primary'])
                                            <div class="absolute top-2 left-2 bg-indigo-600 text-[8px] font-black text-white px-2 py-0.5 rounded-full uppercase tracking-widest">Principal</div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="relative group">
                            <label class="flex flex-col items-center justify-center w-full aspect-[4/3] rounded-3xl border-2 border-dashed border-slate-200 bg-slate-50/50 hover:bg-indigo-50/30 hover:border-indigo-200 transition-all cursor-pointer group">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <div class="w-12 h-12 rounded-2xl bg-white shadow-sm border border-slate-100 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                                        <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/></svg>
                                    </div>
                                    <p class="text-xs font-bold text-slate-600">Subir {{ $type === 'service' ? 'imagen' : 'imágenes' }}</p>
                                    <p class="text-[10px] text-slate-400 mt-1 uppercase tracking-wider">JPG, PNG hasta 2MB</p>
                                </div>
                                <input @if($type === 'service') wire:model="serviceImage" @else wire:model="images" multiple @endif type="file" class="hidden">
                            </label>
                            @if(is_object($serviceImage) || (is_array($images) && count($images) > 0))
                                <div class="mt-2 p-2 bg-emerald-50 rounded-xl border border-emerald-100 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <span class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider">Archivos listos para subir</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Card: Stock y Estado --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="p-6 lg:p-8 space-y-8">
                        @if($type === 'product')
                        <div class="space-y-4">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Límites de Inventario</label>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <label class="text-xs font-bold text-slate-600">Mínimo</label>
                                    <input wire:model="min_stock" type="number" step="0.1"
                                        class="w-full bg-slate-50 border-none rounded-xl px-4 py-3 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500/20">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs font-bold text-slate-600">Máximo</label>
                                    <input wire:model="max_stock" type="number" step="0.1"
                                        class="w-full bg-slate-50 border-none rounded-xl px-4 py-3 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500/20">
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="pt-6 border-t border-slate-100 flex items-center justify-between">
                            <div>
                                <p class="text-sm font-bold text-slate-800">Estado de disponibilidad</p>
                                <p class="text-[10px] text-slate-400 uppercase tracking-wider mt-0.5">{{ $is_active ? 'Visible en operaciones' : 'Registro inactivo' }}</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input wire:model="is_active" type="checkbox" class="sr-only peer">
                                <div class="w-12 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-emerald-500 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                            </label>
                        </div>
                    </div>
                </div>

            </div>

        </form>
    </div>

    {{-- ═══════════════════════════════════════════════════
         MODALES UIX PRO MAX
    ═══════════════════════════════════════════════════ --}}

    @if($showCategoryModal || $showSubcategoryModal || $showSupplierModal)
    <div class="fixed inset-0 z-[60] flex items-center justify-center p-4 sm:p-6 bg-slate-900/60 backdrop-blur-sm"
         x-data x-on:keydown.escape.window="$wire.set('showCategoryModal', false); $wire.set('showSubcategoryModal', false); $wire.set('showSupplierModal', false);">
        
        {{-- Modal Content --}}
        <div class="bg-white rounded-[2rem] shadow-2xl w-full max-w-lg overflow-hidden border border-white/20 animate-in fade-in zoom-in duration-300" @click.stop>
            
            {{-- Modal Header --}}
            <div class="px-8 py-6 bg-slate-50/50 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-bold text-slate-800">
                        @if($showCategoryModal) Nueva Categoría @elseif($showSubcategoryModal) Nueva Subcategoría @else Nuevo Proveedor @endif
                    </h3>
                    <p class="text-xs text-slate-400 font-medium mt-1">Completa los datos esenciales para continuar</p>
                </div>
                <button type="button" @click="$wire.set('showCategoryModal', false); $wire.set('showSubcategoryModal', false); $wire.set('showSupplierModal', false);" 
                    class="w-8 h-8 flex items-center justify-center rounded-full bg-white shadow-sm border border-slate-200 text-slate-400 hover:text-rose-500 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="p-8 space-y-6">
                @if($showCategoryModal)
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Nombre de la categoría *</label>
                            <input wire:model="newCategoryName" type="text" autofocus
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-semibold text-slate-800 focus:ring-2 focus:ring-indigo-500/20"
                                placeholder="Ej. Materiales Eléctricos...">
                            @error('newCategoryName') <p class="text-[10px] text-rose-500 font-medium">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Identificador Visual (Color)</label>
                            <div class="flex items-center gap-4 p-4 bg-slate-50 rounded-2xl">
                                <input wire:model="newCategoryColor" type="color" class="h-10 w-20 rounded-xl border-none cursor-pointer bg-transparent">
                                <span class="text-xs font-mono font-bold text-slate-600">{{ $newCategoryColor }}</span>
                            </div>
                        </div>
                    </div>
                    <button type="button" wire:click="saveCategory" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 rounded-2xl shadow-lg shadow-indigo-200 transition-all active:scale-[0.98]">
                        Crear Categoría
                    </button>

                @elseif($showSubcategoryModal)
                    <div class="space-y-4">
                        <div class="p-4 bg-indigo-50 rounded-2xl border border-indigo-100 mb-6">
                            <p class="text-[10px] text-indigo-400 font-black uppercase tracking-[0.2em] mb-1">Categoría Principal</p>
                            <p class="text-sm font-bold text-indigo-700">{{ $categories->firstWhere('id', $category_id)?->name ?? '—' }}</p>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Nombre de la subcategoría *</label>
                            <input wire:model="newSubcategoryName" type="text" autofocus
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-semibold text-slate-800 focus:ring-2 focus:ring-indigo-500/20"
                                placeholder="Ej. Cables de Media Tensión...">
                            @error('newSubcategoryName') <p class="text-[10px] text-rose-500 font-medium">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <button type="button" wire:click="saveSubcategory" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 rounded-2xl shadow-lg shadow-indigo-200 transition-all active:scale-[0.98]">
                        Crear Subcategoría
                    </button>

                @elseif($showSupplierModal)
                    <div class="space-y-6">
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Nombre Comercial / Empresa *</label>
                            <input wire:model="newSupplierName" type="text" autofocus
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-semibold text-slate-800 focus:ring-2 focus:ring-indigo-500/20"
                                placeholder="Ej. ProPower Solutions S.A.">
                            @error('newSupplierName') <p class="text-[10px] text-rose-500 font-medium">{{ $message }}</p> @enderror
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Teléfono</label>
                                <input wire:model="newSupplierPhone" type="text" class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-semibold text-slate-800 focus:ring-2 focus:ring-indigo-500/20">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Email</label>
                                <input wire:model="newSupplierEmail" type="email" class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-semibold text-slate-800 focus:ring-2 focus:ring-indigo-500/20">
                            </div>
                        </div>
                    </div>
                    <button type="button" wire:click="saveSupplier" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 rounded-2xl shadow-lg shadow-indigo-200 transition-all active:scale-[0.98] mt-4">
                        Registrar Proveedor
                    </button>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>

<div class="min-h-screen bg-slate-50/50 -m-4 lg:-m-6">
    {{-- ── STICKY HEADER ────────────────────────────────────────────────── --}}
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 max-w-full mx-auto">
            <div class="flex items-center gap-3 min-w-0">
                <a wire:navigate href="{{ route('sales.price-lists.index') }}" 
                   class="group flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-100 hover:shadow-sm transition-all duration-200">
                    <svg class="w-5 h-5 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl font-bold text-slate-800 truncate">
                        {{ $priceList?->exists ? 'Editar Lista de Precios' : 'Nueva Lista de Precios' }}
                    </h1>
                    <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">Gestión de esquemas comerciales</p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <a wire:navigate href="{{ route('sales.price-lists.index') }}"
                    class="hidden sm:inline-flex px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 transition-colors">
                    Cancelar
                </a>
                <button type="button" wire:click="save"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:scale-[1.02] active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ $priceList?->exists ? 'Guardar Cambios' : 'Crear Lista' }}</span>
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-full mx-auto p-4 sm:p-6 lg:p-8 space-y-8">
        <x-alert />

        <form wire:submit="save" class="space-y-8">
            {{-- ── SECCIÓN: CONFIGURACIÓN ────────────────────────────────────── --}}
            <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                <div class="p-6 lg:p-8 space-y-8">
                    <div class="flex items-center gap-3 border-b border-slate-100 pb-5">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <h2 class="text-base font-bold text-slate-800">Parámetros de la Lista</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                        {{-- Nombre --}}
                        <div class="md:col-span-2 space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Nombre de la Lista *</label>
                            <input wire:model="name" type="text" placeholder="Ej. Mayoristas 2026, VIP, Buen Fin..."
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 transition-all">
                            @error('name') <p class="text-[10px] text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Moneda --}}
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Moneda</label>
                            <div class="relative">
                                <select wire:model="currency"
                                    class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 appearance-none cursor-pointer">
                                    <option value="MXN">MXN — Peso Mexicano</option>
                                    <option value="USD">USD — Dólar</option>
                                </select>
                                <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                        </div>

                        {{-- Vigencia --}}
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Válida Desde</label>
                            <input wire:model="valid_from" type="date"
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10">
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Válida Hasta</label>
                            <input wire:model="valid_to" type="date"
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10">
                            @error('valid_to') <p class="text-[10px] text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Toggles --}}
                        <div class="md:col-span-2 flex flex-wrap gap-8 items-center pt-2">
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <div class="relative inline-flex items-center">
                                    <input type="checkbox" wire:model="is_default" class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-indigo-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all shadow-inner"></div>
                                </div>
                                <span class="text-xs font-black uppercase tracking-widest text-slate-500 group-hover:text-slate-700 transition-colors">Lista Predeterminada</span>
                            </label>

                            <label class="flex items-center gap-3 cursor-pointer group">
                                <div class="relative inline-flex items-center">
                                    <input type="checkbox" wire:model="is_active" class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-emerald-500 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all shadow-inner"></div>
                                </div>
                                <span class="text-xs font-black uppercase tracking-widest text-slate-500 group-hover:text-slate-700 transition-colors">Lista Activa</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── SECCIÓN: PRODUCTOS Y PRECIOS ───────────────────────────────── --}}
            <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                <div class="p-6 lg:p-8 space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-5">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-teal-50 flex items-center justify-center text-teal-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-slate-800">Productos Vinculados</h2>
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Definición de precios específicos</p>
                            </div>
                        </div>
                        <livewire:shared.product-picker :multi-select="true" />
                    </div>

                    {{-- Buscador Rápido --}}
                    <div class="relative group">
                        <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 group-focus-within:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input wire:model.live.debounce.300ms="productSearch" type="text"
                            placeholder="Buscar en catálogo para agregar a la lista..."
                            class="w-full pl-10 pr-4 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-800 placeholder-slate-400 focus:ring-4 focus:ring-indigo-500/10 transition-all">
                        
                        @if(count($productResults) > 0)
                            <div class="absolute top-full left-0 right-0 bg-white border border-slate-200 rounded-2xl shadow-2xl z-40 mt-2 overflow-hidden animate-in fade-in zoom-in-95">
                                @foreach($productResults as $result)
                                    <button type="button" wire:click="addProduct({{ $result['id'] }})"
                                        class="w-full text-left px-5 py-4 hover:bg-slate-50 transition flex items-center justify-between border-b border-slate-50 last:border-0 group">
                                        <div>
                                            <p class="text-sm font-bold text-slate-800 group-hover:text-indigo-600">{{ $result['name'] }}</p>
                                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">SKU: {{ $result['sku'] ?? '—' }} • Estándar: ${{ number_format($result['sale_price'], 2) }}</p>
                                        </div>
                                        <div class="text-right shrink-0 ml-4">
                                            <p class="text-[10px] text-indigo-400 font-black uppercase tracking-widest">+ Agregar</p>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Tabla de Precios --}}
                    @if(count($items) > 0)
                        <div class="bg-slate-50 rounded-2xl border border-slate-200/50 overflow-hidden shadow-inner">
                            <div class="overflow-x-auto overflow-visible">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="text-left border-b border-slate-200/60">
                                            <th class="px-5 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Producto</th>
                                            <th class="px-5 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest w-48 text-right">Precio de Lista</th>
                                            <th class="px-5 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest w-32 text-center">Descuento Sugerido</th>
                                            <th class="w-12"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-200/40 bg-white">
                                        @foreach($items as $index => $item)
                                            <tr class="hover:bg-slate-50/50 transition-colors">
                                                <td class="px-5 py-3">
                                                    <p class="font-bold text-slate-800 text-sm">{{ $item['product_name'] }}</p>
                                                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Ref: #{{ $item['product_id'] }}</p>
                                                </td>
                                                <td class="px-5 py-3">
                                                    <div class="flex items-center justify-end gap-1">
                                                        <span class="text-xs font-bold text-slate-400">$</span>
                                                        <input wire:model.live="items.{{ $index }}.price" type="number" step="0.01" min="0"
                                                            class="w-32 border-none focus:ring-0 p-0 text-sm font-black text-indigo-600 bg-transparent text-right">
                                                    </div>
                                                    @error("items.{$index}.price") <p class="text-[9px] text-rose-500 font-bold text-right">{{ $message }}</p> @enderror
                                                </td>
                                                <td class="px-5 py-3 text-center">
                                                    <div class="flex items-center justify-center gap-1">
                                                        <input wire:model.live="items.{{ $index }}.discount_pct" type="number" step="0.01" min="0" max="100"
                                                            class="w-16 border-none focus:ring-0 p-0 text-sm font-black text-center text-rose-600 bg-transparent">
                                                        <span class="text-xs font-bold text-rose-300">%</span>
                                                    </div>
                                                </td>
                                                <td class="px-5 py-3 text-center">
                                                    <button type="button" wire:click="removeItem({{ $index }})"
                                                        class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-300 hover:text-rose-600 hover:bg-rose-50 transition-all">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @else
                        <div class="py-12 text-center bg-slate-50 rounded-[2rem] border-2 border-dashed border-slate-200">
                            <div class="w-16 h-16 rounded-full bg-white flex items-center justify-center text-slate-200 mx-auto mb-4 shadow-sm border border-slate-50">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                            </div>
                            <p class="text-sm font-bold text-slate-400 uppercase tracking-widest">Busca productos para empezar a definir precios</p>
                        </div>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>
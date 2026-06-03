<div class="min-h-screen bg-slate-50/50 -m-4 lg:-m-6">
    {{-- ── STICKY HEADER ────────────────────────────────────────────────── --}}
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 max-w-full mx-auto">
            <div class="flex items-center gap-3 min-w-0">
                <a wire:navigate href="{{ route('purchases.orders.index') }}" 
                   class="group flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-100 hover:shadow-sm transition-all duration-200">
                    <svg class="w-5 h-5 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl font-bold text-slate-800 truncate">
                        Nueva orden de compra
                    </h1>
                    @if($sourceLabel)
                        <p class="text-[10px] text-indigo-600 font-bold uppercase tracking-wider">
                            Desde requisición: {{ $sourceLabel }}
                        </p>
                    @else
                        <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">
                            Generación de pedido a proveedor
                        </p>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <a wire:navigate href="{{ route('purchases.orders.index') }}"
                    class="hidden sm:inline-flex px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 transition-colors">
                    Cancelar
                </a>
                <button type="button" wire:click="save"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:scale-[1.02] active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Crear orden</span>
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-full mx-auto p-4 sm:p-6 lg:p-8">
        <form wire:submit="save" class="space-y-6 lg:space-y-8">
            
            {{-- ── SECCIÓN: DATOS GENERALES ────────────────────────────────────── --}}
            <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                <div class="p-6 lg:p-8 space-y-8">
                    <div class="flex items-center gap-3 border-b border-slate-100 pb-5">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <h2 class="text-base font-bold text-slate-800">Información General</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                        {{-- Sucursal --}}
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Sucursal Destino *</label>
                            <div class="relative">
                                <select wire:model="branch_id"
                                    class="w-full bg-slate-50 border-none rounded-2xl px-4 py-3.5 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500/20 appearance-none cursor-pointer">
                                    <option value="">— Seleccionar —</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                            @error('branch_id') <p class="text-[10px] text-rose-500 font-medium">{{ $message }}</p> @enderror
                        </div>

                        {{-- Moneda --}}
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Moneda</label>
                            <div class="relative">
                                <select wire:model="currency"
                                    class="w-full bg-slate-50 border-none rounded-2xl px-4 py-3.5 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500/20 appearance-none cursor-pointer">
                                    <option value="MXN">MXN — Peso Mexicano</option>
                                    <option value="USD">USD — Dólar Americano</option>
                                </select>
                                <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                        </div>

                        {{-- Días de crédito --}}
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Días de crédito</label>
                            <input wire:model="payment_terms" type="number" min="0" placeholder="0 = Contado"
                                class="w-full bg-slate-50 border-none rounded-2xl px-4 py-3.5 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500/20">
                        </div>

                        {{-- Entrega Esperada --}}
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Entrega Esperada *</label>
                            <input wire:model="expected_at" type="date"
                                class="w-full bg-slate-50 border-none rounded-2xl px-4 py-3.5 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500/20">
                            @error('expected_at') <p class="text-[10px] text-rose-500 font-medium">{{ $message }}</p> @enderror
                        </div>

                        {{-- Direcciones --}}
                        <div class="md:col-span-2 space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Dirección de Envío</label>
                            <input wire:model="shipping_address" type="text" placeholder="Calle, número, colonia, ciudad…"
                                class="w-full bg-slate-50 border-none rounded-2xl px-4 py-3.5 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500/20">
                        </div>

                        <div class="md:col-span-2 space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Dirección de Facturación</label>
                            <input wire:model="billing_address" type="text" placeholder="Igual a envío si se deja vacío"
                                class="w-full bg-slate-50 border-none rounded-2xl px-4 py-3.5 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500/20">
                        </div>

                        {{-- Notas --}}
                        <div class="md:col-span-4 space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Notas / Observaciones</label>
                            <textarea wire:model="notes" rows="2" placeholder="Instrucciones especiales para el proveedor..."
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-medium text-slate-700 focus:ring-2 focus:ring-indigo-500/20"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── SECCIÓN: PARTIDAS DE LA ORDEN ───────────────────────────────── --}}
            <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                <div class="p-6 lg:p-8 space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-5">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-teal-50 flex items-center justify-center text-teal-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-slate-800">Partidas de la Orden</h2>
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Listado de productos y servicios a comprar</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <livewire:shared.product-picker :multi-select="true" />
                        </div>
                    </div>

                    {{-- Buscador Rápido --}}
                    <div class="relative group">
                        <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 group-focus-within:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input wire:model.live.debounce.250ms="productSearch" type="text"
                            placeholder="Agregar por nombre, SKU o código de barras..."
                            class="w-full pl-10 pr-4 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500/20 transition-all">
                        
                        @if(count($productResults) > 0)
                            <div class="absolute top-full left-0 right-0 bg-white border border-slate-200 rounded-2xl shadow-2xl z-40 mt-2 overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                                @foreach($productResults as $result)
                                    <button type="button" wire:click="addProduct({{ $result['id'] }})"
                                        class="w-full text-left px-5 py-4 hover:bg-slate-50 transition flex items-center justify-between border-b border-slate-50 last:border-0 group">
                                        <div>
                                            <p class="text-sm font-bold text-slate-800 group-hover:text-indigo-600">{{ $result['name'] }}</p>
                                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">
                                                SKU: {{ $result['sku'] ?? '—' }}
                                                @if($result['barcode'] ?? null) · CB: {{ $result['barcode'] }} @endif
                                            </p>
                                        </div>
                                        <div class="text-right shrink-0 ml-4">
                                            <p class="text-sm font-black text-indigo-600">${{ number_format($result['purchase_price'], 2) }}</p>
                                            <p class="text-[10px] text-indigo-400 font-black uppercase tracking-widest">+ Agregar</p>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    @error('items') <p class="text-xs text-rose-500 font-bold">{{ $message }}</p> @enderror

                    {{-- Tabla de Partidas --}}
                    <div class="bg-slate-50 rounded-2xl border border-slate-200/50 overflow-hidden shadow-inner">
                        <div class="overflow-x-auto overflow-visible">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="text-left border-b border-slate-200/60">
                                        <th class="px-5 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Descripción</th>
                                        <th class="px-5 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest w-48">Proveedor</th>
                                        <th class="px-5 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest w-28">Cantidad</th>
                                        <th class="px-5 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest w-20">Unidad</th>
                                        @can('view prices')
                                            <th class="px-5 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest w-36">Precio Unit.</th>
                                            <th class="px-5 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest w-24">IVA %</th>
                                            <th class="px-5 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest w-32 text-right">Subtotal</th>
                                        @endcan
                                        <th class="w-12"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200/40 bg-white">
                                    @foreach($items as $index => $item)
                                        <tr class="hover:bg-slate-50/50 transition-colors">
                                            <td class="px-5 py-3">
                                                <input wire:model="items.{{ $index }}.description" type="text"
                                                    class="w-full border-none focus:ring-0 p-0 text-sm font-bold text-slate-800 placeholder-slate-300 bg-transparent"
                                                    placeholder="Descripción del producto...">
                                                @error("items.{$index}.description") <p class="text-[10px] text-rose-500 mt-1 font-bold">{{ $message }}</p> @enderror
                                            </td>
                                            <td class="px-5 py-3">
                                                <select wire:model="items.{{ $index }}.supplier_id"
                                                    class="w-full border-none focus:ring-0 p-0 text-xs font-bold text-slate-600 bg-transparent cursor-pointer">
                                                    <option value="">— Sin asignar —</option>
                                                    @foreach($suppliers as $supplier)
                                                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td class="px-5 py-3">
                                                <input wire:model.live="items.{{ $index }}.quantity" type="number" step="0.01" min="0.01"
                                                    class="w-full border-none focus:ring-0 p-0 text-sm font-black text-slate-800 bg-transparent">
                                            </td>
                                            <td class="px-5 py-3 text-slate-400 font-bold uppercase text-[10px]">
                                                <input wire:model="items.{{ $index }}.unit" type="text"
                                                    class="w-full border-none focus:ring-0 p-0 text-[10px] font-bold text-slate-400 uppercase bg-transparent"
                                                    placeholder="PZA">
                                            </td>
                                            @can('view prices')
                                                <td class="px-5 py-3">
                                                    <div class="flex items-center gap-1">
                                                        <span class="text-xs font-bold text-slate-400">$</span>
                                                        <input wire:model.live="items.{{ $index }}.unit_price" type="number" step="0.01" min="0"
                                                            class="w-full border-none focus:ring-0 p-0 text-sm font-black text-indigo-600 bg-transparent">
                                                    </div>
                                                </td>
                                                <td class="px-5 py-3">
                                                    <div class="flex items-center gap-1">
                                                        <input wire:model.live="items.{{ $index }}.tax_rate" type="number" step="0.01" min="0" max="100"
                                                            class="w-20 border-none focus:ring-0 p-0 text-sm font-bold text-slate-600 bg-transparent">
                                                        <span class="text-xs font-bold text-slate-400">%</span>
                                                    </div>
                                                </td>
                                                <td class="px-5 py-3 text-right">
                                                    <span class="text-sm font-black text-slate-900">
                                                        ${{ number_format(($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0), 2) }}
                                                    </span>
                                                </td>
                                            @endcan
                                            <td class="px-5 py-3 text-center">
                                                @if(count($items) > 1)
                                                    <button type="button" wire:click="removeItem({{ $index }})"
                                                        class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-all">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        {{-- Resumen de Totales --}}
                        @can('view prices')
                            <div class="bg-slate-100/50 border-t border-slate-200/60 p-6 flex flex-col items-end gap-3">
                                <div class="flex items-center gap-10">
                                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Subtotal:</span>
                                    <span class="text-base font-bold text-slate-700">${{ number_format($this->subtotal, 2) }}</span>
                                </div>
                                <div class="flex items-center gap-10">
                                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">IVA (Trasladado):</span>
                                    <span class="text-base font-bold text-slate-700">${{ number_format($this->tax, 2) }}</span>
                                </div>
                                <div class="flex items-center gap-10 pt-3 border-t border-slate-200 mt-1">
                                    <span class="text-[11px] font-black text-slate-900 uppercase tracking-widest">Total Orden:</span>
                                    <div class="flex items-baseline gap-1.5">
                                        <span class="text-xs font-bold text-indigo-600">{{ $currency }}</span>
                                        <span class="text-3xl font-black text-slate-900">${{ number_format($this->total, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        @endcan
                    </div>

                    <button type="button" wire:click="addItem"
                        class="w-full py-4 border-2 border-dashed border-slate-200 rounded-[2rem] text-sm font-bold text-slate-400 hover:border-indigo-300 hover:text-indigo-600 hover:bg-indigo-50/50 transition-all duration-200">
                        + Agregar producto libre / servicio
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>


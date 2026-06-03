<div class="min-h-screen bg-slate-50/50 -m-4 lg:-m-6">
    {{-- ── STICKY HEADER ────────────────────────────────────────────────── --}}
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 max-w-full mx-auto">
            <div class="flex items-center gap-3 min-w-0">
                <a wire:navigate href="{{ route('sales.orders.index') }}" 
                   class="group flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-100 hover:shadow-sm transition-all duration-200">
                    <svg class="w-5 h-5 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl font-bold text-slate-800 truncate">Nueva Orden de Venta</h1>
                    @if($this->sourceLabel)
                        <p class="text-[10px] text-indigo-600 font-bold uppercase tracking-wider">Desde Cotización: {{ $this->sourceLabel }}</p>
                    @else
                        <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">Registro de pedido confirmado</p>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <a wire:navigate href="{{ route('sales.orders.index') }}"
                    class="hidden sm:inline-flex px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 transition-colors">
                    Cancelar
                </a>
                <button type="button" wire:click="save"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:scale-[1.02] active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    <span>Guardar Pedido</span>
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-full mx-auto p-4 sm:p-6 lg:p-8 space-y-8">
        <x-alert />

        <form wire:submit="save" class="space-y-8">

            {{-- ── SECCIÓN: DATOS GENERALES ────────────────────────────────────── --}}
            <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                <div class="p-6 lg:p-8 space-y-8">
                    <div class="flex items-center gap-3 border-b border-slate-100 pb-5">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <h2 class="text-base font-bold text-slate-800">Información del Pedido</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                        {{-- Cliente --}}
                        <div class="md:col-span-2 space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Cliente *</label>
                            <div class="relative">
                                <select wire:model.live="customer_id"
                                    class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 appearance-none cursor-pointer">
                                    <option value="">— Seleccionar cliente —</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                            @error('customer_id') <p class="text-[10px] text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Forma de Pago --}}
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Forma de Pago</label>
                            <select wire:model="payment_method"
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 appearance-none cursor-pointer">
                                @foreach(\App\Models\SaleOrder::PAYMENT_METHODS as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Días de crédito --}}
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Días de Crédito</label>
                            <input wire:model="payment_terms" type="number" min="0" placeholder="0 = Contado"
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10">
                        </div>

                        {{-- Moneda --}}
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Moneda</label>
                            <select wire:model="currency"
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10">
                                <option value="MXN">MXN — Peso Mexicano</option>
                                <option value="USD">USD — Dólar Americano</option>
                            </select>
                        </div>

                        {{-- Fecha Requerida --}}
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Fecha Requerida</label>
                            <input wire:model="required_at" type="date"
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10">
                        </div>

                        {{-- Descuento Global --}}
                        <div class="space-y-2">
                            @php
                                $gCap     = $this->maxGlobalDiscountCap;
                                $gVal     = (float) $global_discount;
                                $gOverCap = $gCap < 100 && $gVal > $gCap;
                                $gNearCap = $gCap < 100 && !$gOverCap && $gVal >= ($gCap * 0.8);
                            @endphp
                            <div class="flex items-center justify-between">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Desc. Global %</label>
                                @if($gCap < 100)
                                    <span class="text-[9px] font-black uppercase tracking-wider {{ $gOverCap ? 'text-rose-500' : 'text-amber-500' }}">
                                        Máx. sin autorizar: {{ number_format($gCap, 1) }}%
                                    </span>
                                @endif
                            </div>
                            <div class="relative">
                                <input wire:model.live="global_discount" type="number" step="0.01" min="0" max="100"
                                    class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-black focus:ring-4 text-right
                                        {{ $gOverCap ? 'text-rose-600 focus:ring-rose-500/20 ring-2 ring-rose-300' : ($gNearCap ? 'text-amber-600 focus:ring-amber-500/10' : 'text-rose-600 focus:ring-rose-500/10') }}">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 font-bold {{ $gOverCap ? 'text-rose-400' : 'text-rose-300' }}">%</span>
                            </div>
                            @if($gOverCap)
                                <p class="text-[9px] text-rose-500 font-black uppercase tracking-wider flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                    Excede el límite — se solicitará autorización
                                </p>
                            @endif
                        </div>

                        {{-- Notas --}}
                        <div class="md:col-span-4 space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Observaciones del Pedido</label>
                            <textarea wire:model="notes" rows="2" placeholder="Instrucciones especiales para almacén o facturación..."
                                class="w-full bg-slate-50 border-none rounded-[2rem] px-6 py-5 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-indigo-500/10 transition-all resize-none"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── SECCIÓN: PARTIDAS ─────────────────────────────────────────── --}}
            <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                <div class="p-6 lg:p-8 space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-5">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-teal-50 flex items-center justify-center text-teal-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-slate-800">Partidas del Pedido</h2>
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Detalle de productos y servicios</p>
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
                            placeholder="Agregar producto por nombre, SKU o código de barras..."
                            class="w-full pl-10 pr-4 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-800 placeholder-slate-400 focus:ring-4 focus:ring-indigo-500/10 transition-all">
                        
                        @if(count($productResults) > 0)
                            <div class="absolute top-full left-0 right-0 bg-white border border-slate-200 rounded-2xl shadow-2xl z-40 mt-2 overflow-hidden animate-in fade-in zoom-in-95">
                                @foreach($productResults as $result)
                                    <button type="button" wire:click="addProduct({{ $result['id'] }})"
                                        class="w-full text-left px-5 py-4 hover:bg-slate-50 transition flex items-center justify-between border-b border-slate-50 last:border-0 group">
                                        <div>
                                            <p class="text-sm font-bold text-slate-800 group-hover:text-indigo-600">{{ $result['name'] }}</p>
                                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">SKU: {{ $result['sku'] ?? '—' }}</p>
                                        </div>
                                        <div class="text-right ml-4">
                                            <p class="text-sm font-black text-indigo-600">${{ number_format($result['sale_price'], 2) }}</p>
                                            <p class="text-[10px] text-indigo-400 font-black uppercase tracking-widest">+ Seleccionar</p>
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
                                        <th class="px-5 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Descripción / Producto</th>
                                        <th class="px-5 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest w-24 text-center">Cant.</th>
                                        <th class="px-5 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest w-32">Precio Unit.</th>
                                        <th class="px-5 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest w-24 text-center">Desc %</th>
                                        <th class="px-5 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest w-20 text-center">IVA</th>
                                        <th class="px-5 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest w-32 text-right">Subtotal</th>
                                        <th class="w-12"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200/40 bg-white">
                                    @foreach($items as $index => $item)
                                        @php
                                            $unitPrice    = (float)($item['unit_price'] ?? 0);
                                            $qty          = (float)($item['quantity'] ?? 0);
                                            $discPct      = (float)($item['discount_pct'] ?? 0);
                                            $taxPct       = (float)($item['tax_rate'] ?? 0);
                                            $minPrice     = (float)($item['min_sale_price'] ?? 0);
                                            $maxDiscPct   = (float)($item['max_discount_pct'] ?? 100);
                                            $gDiscPct     = (float) $global_discount;
                                            $effectivePrice = $unitPrice > 0
                                                ? round($unitPrice * (1 - $discPct / 100) * (1 - $gDiscPct / 100), 2)
                                                : 0;
                                            $discExceeded = ($discPct > $maxDiscPct)
                                                || ($minPrice > 0 && $effectivePrice > 0 && $effectivePrice < $minPrice);
                                            $base    = $qty * $unitPrice;
                                            $disc    = $base * ($discPct / 100);
                                            $baseNet = $base - $disc;
                                            $tax     = $baseNet * ($taxPct / 100);
                                            $lineTotal = $baseNet + $tax;
                                        @endphp
                                        <tr class="{{ $discExceeded ? "bg-rose-50/50" : "hover:bg-slate-50/50" }} transition-colors">
                                            <td class="px-5 py-3">
                                                <input wire:model="items.{{ $index }}.description" type="text"
                                                    class="w-full border-none focus:ring-0 p-0 text-sm font-bold text-slate-800 placeholder-slate-300 bg-transparent"
                                                    placeholder="Nombre del concepto...">
                                                @error("items.{$index}.description") <p class="text-[10px] text-rose-500 mt-1 font-bold">{{ $message }}</p> @enderror
                                                @if($discExceeded)
                                                    <p class="text-[9px] text-rose-600 font-black uppercase mt-1">Precio insuficiente (Mín: ${{ number_format($minPrice, 2) }})</p>
                                                @endif
                                            </td>
                                            <td class="px-5 py-3">
                                                <input wire:model.live="items.{{ $index }}.quantity" type="number" step="0.01" min="0.01"
                                                    class="w-full border-none focus:ring-0 p-0 text-sm font-black text-center text-slate-900 bg-transparent">
                                            </td>
                                            <td class="px-5 py-3">
                                                <div class="flex items-center gap-1">
                                                    <span class="text-xs font-bold text-slate-400">$</span>
                                                    <input wire:model.live="items.{{ $index }}.unit_price" type="number" step="0.01" min="0"
                                                        class="w-full border-none focus:ring-0 p-0 text-sm font-bold text-indigo-600 bg-transparent">
                                                </div>
                                            </td>
                                            <td class="px-5 py-3 text-center">
                                                <div class="space-y-1">
                                                    <div class="flex items-center justify-center gap-0.5">
                                                        <input wire:model.live="items.{{ $index }}.discount_pct" type="number" step="0.01" min="0" max="100"
                                                            class="w-12 border-none focus:ring-0 p-0 text-sm font-black text-center {{ $discExceeded ? "text-rose-600" : "text-slate-700" }} bg-transparent">
                                                        <span class="text-xs font-bold {{ $discExceeded ? "text-rose-400" : "text-slate-400" }}">%</span>
                                                    </div>
                                                    @if($discExceeded)
                                                        <p class="text-[9px] text-rose-500 font-black uppercase text-center tracking-tighter">
                                                            Mín: ${{ number_format($minPrice, 2) }}
                                                        </p>
                                                    @elseif($maxDiscPct < 100)
                                                        <p class="text-[9px] text-slate-400 font-bold text-center uppercase tracking-tighter">
                                                            Máx: {{ number_format($maxDiscPct, 1) }}%
                                                        </p>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-5 py-3 text-center">
                                                <div class="flex items-center justify-center gap-0.5">
                                                    <input wire:model.live="items.{{ $index }}.tax_rate" type="number" step="0.01" min="0"
                                                        class="w-10 border-none focus:ring-0 p-0 text-[10px] font-black text-center text-slate-600 bg-transparent">
                                                    <span class="text-[9px] font-bold text-slate-400">%</span>
                                                </div>
                                            </td>
                                            <td class="px-5 py-3 text-right">
                                                <span class="text-sm font-black text-slate-900">
                                                    ${{ number_format($lineTotal, 2) }}
                                                </span>
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
                        
                        {{-- Resumen de Totales --}}
                        <div class="bg-slate-100/50 border-t border-slate-200/60 p-6 lg:p-8 flex flex-col items-end gap-3">
                            <div class="flex items-center gap-10">
                                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Subtotal Bruto:</span>
                                <span class="text-sm font-bold text-slate-700">${{ number_format($this->subtotal, 2) }}</span>
                            </div>
                            @if($this->discount > 0)
                                <div class="flex items-center gap-10">
                                    <span class="text-[11px] font-bold text-rose-400 uppercase tracking-widest">Descuento:</span>
                                    <span class="text-sm font-black text-rose-600">- ${{ number_format($this->discount, 2) }}</span>
                                </div>
                            @endif
                            <div class="flex items-center gap-10">
                                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">IVA Trasladado:</span>
                                <span class="text-sm font-bold text-slate-700">${{ number_format($this->tax, 2) }}</span>
                            </div>
                            <div class="flex items-center gap-10 pt-6 border-t border-slate-200 mt-2">
                                <span class="text-[11px] font-black text-slate-900 uppercase tracking-widest">Total del Pedido:</span>
                                <div class="flex items-baseline gap-2">
                                    <span class="text-xs font-black text-indigo-600">{{ $currency }}</span>
                                    <span class="text-4xl font-black text-slate-900 tracking-tighter">${{ number_format($this->total, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="button" wire:click="addItem"
                        class="w-full py-4 border-2 border-dashed border-slate-200 rounded-[2rem] text-sm font-black uppercase tracking-widest text-slate-400 hover:border-indigo-300 hover:text-indigo-600 hover:bg-indigo-50/50 transition-all duration-200 flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        Agregar Línea Manual
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- ── MODAL: AUTORIZACIÓN ─────────────────────────────────────────── --}}
    @if($needsApproval)
    <div class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" wire:click="$set('needsApproval', false)"></div>
        <div class="relative bg-white rounded-[2.5rem] shadow-2xl p-10 w-full max-w-md mx-auto border border-slate-200 animate-in zoom-in-95">
            <div class="w-16 h-16 rounded-3xl bg-rose-50 flex items-center justify-center text-rose-500 mb-6 mx-auto">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
            </div>
            <h3 class="text-2xl font-black text-slate-800 text-center tracking-tight mb-2">Precio Crítico</h3>
            <p class="text-sm text-slate-500 text-center font-medium leading-relaxed mb-6">
                El descuento solicitado excede el margen permitido (máx. <strong>{{ number_format($exceedingMaxPct, 1) }}%</strong>). Se requiere justificación para enviar a revisión.
            </p>
            <div class="space-y-6">
                <textarea wire:model="approvalNotes" rows="3" placeholder="Justifica este precio especial..."
                    class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-rose-500/10 resize-none transition-all"></textarea>
                
                <div class="grid grid-cols-2 gap-4">
                    <button wire:click="$set('needsApproval', false)"
                        class="py-4 bg-slate-50 text-slate-500 rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-slate-100 transition-all">
                        Volver
                    </button>
                    <button wire:click="save(true)"
                        class="py-4 bg-rose-600 text-white rounded-2xl text-xs font-black uppercase tracking-widest shadow-lg shadow-rose-500/25 hover:bg-rose-700 hover:scale-[1.02] active:scale-[0.98] transition-all">
                        Enviar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

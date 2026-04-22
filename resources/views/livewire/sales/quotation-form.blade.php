<div class="min-h-screen bg-slate-50/50 -m-4 lg:-m-6">
    {{-- ── STICKY HEADER ────────────────────────────────────────────────── --}}
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 max-w-full mx-auto">
            <div class="flex items-center gap-3 min-w-0">
                <a wire:navigate href="{{ route('sales.index') }}" 
                   class="group flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-100 hover:shadow-sm transition-all duration-200">
                    <svg class="w-5 h-5 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl font-bold text-slate-800 truncate">Nueva Cotización</h1>
                    <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">Propuesta comercial para cliente</p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <a wire:navigate href="{{ route('sales.index') }}"
                    class="hidden sm:inline-flex px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 transition-colors">
                    Cancelar
                </a>
                <button type="button" wire:click="save"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:scale-[1.02] active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    <span>Crear Propuesta</span>
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-full mx-auto p-4 sm:p-6 lg:p-8 space-y-8">
        <x-alert />

        <form wire:submit="save" class="space-y-8">
            {{-- ── SECCIÓN: DATOS DEL CLIENTE ─────────────────────────────────── --}}
            <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                <div class="p-6 lg:p-8 space-y-8">
                    <div class="flex items-center gap-3 border-b border-slate-100 pb-5">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <h2 class="text-base font-bold text-slate-800">Información del Cliente</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                        {{-- Cliente --}}
                        <div class="md:col-span-2 space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Seleccionar Cliente *</label>
                            <div class="relative">
                                <select wire:model.live="customer_id"
                                    class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 appearance-none cursor-pointer">
                                    <option value="">— Buscar o elegir —</option>
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

                        {{-- Lista de Precios --}}
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Esquema de Precios</label>
                            <select wire:model="price_list_id"
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10">
                                <option value="">— Precio de Lista —</option>
                                @foreach($priceLists as $list)
                                    <option value="{{ $list->id }}">{{ $list->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Moneda --}}
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Moneda</label>
                            <select wire:model="currency"
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10">
                                <option value="MXN">MXN — Peso Mexicano</option>
                                <option value="USD">USD — Dólar</option>
                            </select>
                        </div>

                        {{-- Vigencia y Descuento --}}
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Días de Vigencia</label>
                            <input wire:model="valid_days" type="number" min="1"
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10">
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Desc. Global (%)</label>
                            <div class="relative">
                                <input wire:model.live="global_discount" type="number" step="0.01" min="0" max="100"
                                    class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-black text-rose-600 focus:ring-4 focus:ring-rose-500/10 text-right">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-rose-300 font-bold">%</span>
                            </div>
                        </div>

                        {{-- Notas y Términos --}}
                        <div class="md:col-span-2 space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Asunto / Referencia Corta</label>
                            <input wire:model="notes" type="text" placeholder="Ej. Proyecto Obra Civil 2026..."
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-indigo-500/10">
                        </div>

                        <div class="md:col-span-4 space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Términos y Condiciones del Servicio</label>
                            <textarea wire:model="terms" rows="2" placeholder="Describe condiciones de entrega, garantía y forma de pago..."
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
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-slate-800">Conceptos de Venta</h2>
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Productos y servicios presupuestados</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <livewire:shared.product-picker />
                        </div>
                    </div>

                    {{-- Bulk Actions & Info --}}
                    <div class="flex flex-col sm:flex-row sm:items-center gap-4 bg-amber-50/50 rounded-2xl border border-amber-100 p-4">
                        <div class="flex-1 flex items-start gap-2">
                            <svg class="w-4 h-4 text-amber-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p class="text-[10px] font-bold text-amber-800/80 leading-relaxed uppercase tracking-wider">
                                <strong>Control de IVA:</strong> Los precios unitarios se asumen con IVA incluido. Usa los interruptores para desglosar o exentar por partida.
                            </p>
                        </div>
                        <div class="flex gap-2 shrink-0">
                            <button type="button" wire:click="setAllIva(true)" class="px-3 py-1.5 bg-white text-[9px] font-black uppercase tracking-widest text-amber-700 rounded-lg border border-amber-200 hover:bg-amber-100 transition-all shadow-sm">Aplicar IVA Global</button>
                            <button type="button" wire:click="setAllIva(false)" class="px-3 py-1.5 bg-white text-[9px] font-black uppercase tracking-widest text-amber-700 rounded-lg border border-amber-200 hover:bg-amber-100 transition-all shadow-sm">Quitar IVA Global</button>
                        </div>
                    </div>

                    {{-- Buscador Rápido --}}
                    <div class="relative group">
                        <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 group-focus-within:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input wire:model.live.debounce.250ms="productSearch" type="text"
                            placeholder="Buscar en catálogo para agregar concepto..."
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
                                        <th class="px-5 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Descripción / Artículo</th>
                                        <th class="px-5 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest w-24 text-center">Cant.</th>
                                        <th class="px-5 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest w-32">Precio Unit.</th>
                                        <th class="px-5 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest w-24 text-center">Desc %</th>
                                        <th class="px-5 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest w-20 text-center">IEPS</th>
                                        <th class="px-5 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest w-32 text-center">Impuesto</th>
                                        <th class="px-5 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest w-32 text-right">Subtotal</th>
                                        <th class="w-12"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200/40 bg-white">
                                    @forelse($items as $index => $item)
                                        @php
                                            $discPct    = (float) ($item['discount_pct'] ?? 0);
                                            $maxDiscPct = (float) ($item['max_discount_pct'] ?? 100);
                                            $overLimit  = $discPct > 0 && $discPct > $maxDiscPct;
                                            $base       = ($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0);
                                            $discAmt    = $base * ($discPct / 100);
                                            $baseNet    = $base - $discAmt;
                                            $iepsAmt    = $baseNet * (($item['ieps_rate'] ?? 0) / 100);
                                            $taxAmt     = ($baseNet + $iepsAmt) * (($item['tax_rate'] ?? 0) / 100);
                                            $lineTotal  = $baseNet + $iepsAmt + $taxAmt;
                                        @endphp
                                        <tr class="{{ $overLimit ? "bg-rose-50/50" : "hover:bg-slate-50/50" }} transition-colors">
                                            <td class="px-5 py-3">
                                                <input wire:model="items.{{ $index }}.description" type="text"
                                                    class="w-full border-none focus:ring-0 p-0 text-sm font-bold text-slate-800 placeholder-slate-300 bg-transparent"
                                                    placeholder="Descripción del concepto...">
                                                @error("items.{$index}.description") <p class="text-[10px] text-rose-500 mt-1 font-bold">{{ $message }}</p> @enderror
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
                                            <td class="px-5 py-3">
                                                <div class="space-y-1">
                                                    <div class="flex items-center justify-center gap-0.5">
                                                        <input wire:model.live="items.{{ $index }}.discount_pct" type="number" step="0.01" min="0" max="100"
                                                            class="w-12 border-none focus:ring-0 p-0 text-sm font-black text-center {{ $overLimit ? "text-rose-600" : "text-slate-700" }} bg-transparent">
                                                        <span class="text-xs font-bold {{ $overLimit ? "text-rose-400" : "text-slate-400" }}">%</span>
                                                    </div>
                                                    @if($overLimit)
                                                        <p class="text-[9px] text-rose-500 font-black uppercase text-center tracking-tighter">Máx: {{ number_format($maxDiscPct, 0) }}%</p>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-5 py-3 text-center">
                                                <input wire:model.live="items.{{ $index }}.ieps_rate" type="number" step="0.01" min="0" max="100"
                                                    class="w-10 border-none focus:ring-0 p-0 text-[10px] font-black text-center text-orange-600 bg-transparent placeholder-slate-200"
                                                    placeholder="0">
                                            </td>
                                            <td class="px-5 py-3 text-center">
                                                <div class="flex flex-col items-center gap-1">
                                                    <span class="text-[9px] font-black uppercase tracking-widest {{ ($item['tax_rate'] ?? 0) > 0 ? 'text-indigo-600' : 'text-slate-400' }}">
                                                        {{ ($item['tax_rate'] ?? 0) > 0 ? 'IVA 16%' : 'Exento' }}
                                                    </span>
                                                    <button type="button" wire:click="toggleItemIva({{ $index }})"
                                                        class="w-9 h-5 rounded-full relative transition-all focus:outline-none ring-2 ring-transparent focus:ring-indigo-500/20 {{ ($item['tax_rate'] ?? 0) > 0 ? 'bg-indigo-600 shadow-sm shadow-indigo-100' : 'bg-slate-200' }}">
                                                        <div class="absolute top-1 left-1 w-3 h-3 rounded-full bg-white transition-transform {{ ($item['tax_rate'] ?? 0) > 0 ? 'translate-x-4' : '' }}"></div>
                                                    </button>
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
                                    @empty
                                        <tr>
                                            <td colspan="8" class="px-5 py-12 text-center">
                                                <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center text-slate-200 mx-auto mb-4">
                                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                                </div>
                                                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">No hay conceptos agregados a la cotización</p>
                                            </td>
                                        </tr>
                                    @endforelse
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
                                    <span class="text-[11px] font-bold text-rose-400 uppercase tracking-widest">Descuentos Aplicados:</span>
                                    <span class="text-sm font-black text-rose-600">- ${{ number_format($this->discount, 2) }}</span>
                                </div>
                            @endif
                            @if($this->ieps > 0)
                                <div class="flex items-center gap-10">
                                    <span class="text-[11px] font-bold text-orange-400 uppercase tracking-widest">IEPS Acumulado:</span>
                                    <span class="text-sm font-bold text-orange-600">+ ${{ number_format($this->ieps, 2) }}</span>
                                </div>
                            @endif
                            <div class="flex items-center gap-10">
                                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">IVA Trasladado (16%):</span>
                                <span class="text-sm font-bold text-slate-700">${{ number_format($this->tax, 2) }}</span>
                            </div>
                            <div class="flex items-center gap-10 pt-6 border-t border-slate-200 mt-2">
                                <span class="text-[11px] font-black text-slate-900 uppercase tracking-widest">Total Cotización:</span>
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
                        Agregar Línea Manual / Servicio Libre
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- ── MODAL: AUTORIZACIÓN (ESTILO INVENTARIOS) ──────────────────────── --}}
    @if($needsApproval)
    <div class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" wire:click="$set('needsApproval', false)"></div>
        <div class="relative bg-white rounded-[2.5rem] shadow-2xl p-10 w-full max-w-md mx-auto border border-slate-200 animate-in zoom-in-95">
            <div class="w-16 h-16 rounded-3xl bg-amber-50 flex items-center justify-center text-amber-500 mb-6 mx-auto">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <h3 class="text-2xl font-black text-slate-800 text-center tracking-tight mb-2">Autorización Requerida</h3>
            <p class="text-sm text-slate-500 text-center font-medium leading-relaxed mb-6">
                El descuento solicitado excede el límite permitido (máx. <strong>{{ number_format($exceedingMaxPct, 1) }}%</strong>). Se requiere justificación para enviar a revisión gerencial.
            </p>
            <div class="space-y-6">
                <textarea wire:model="approvalNotes" rows="3" placeholder="Justifica el descuento especial (Ej. Cliente estratégico, volumen...)"
                    class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-amber-500/10 resize-none transition-all"></textarea>
                
                <div class="grid grid-cols-2 gap-4">
                    <button wire:click="$set('needsApproval', false)"
                        class="py-4 bg-slate-50 text-slate-500 rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-slate-100 transition-all">
                        Volver
                    </button>
                    <button wire:click="save(true)"
                        class="py-4 bg-amber-500 text-white rounded-2xl text-xs font-black uppercase tracking-widest shadow-lg shadow-amber-500/25 hover:bg-amber-600 hover:scale-[1.02] active:scale-[0.98] transition-all">
                        Solicitar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>


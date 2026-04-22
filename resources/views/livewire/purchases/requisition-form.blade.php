<div class="min-h-screen bg-slate-50/50 -m-4 lg:-m-6">
    {{-- ── STICKY HEADER ────────────────────────────────────────────────── --}}
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 max-w-full mx-auto">
            <div class="flex items-center gap-3 min-w-0">
                <a wire:navigate href="{{ route('purchases.index') }}" 
                   class="group flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-100 hover:shadow-sm transition-all duration-200">
                    <svg class="w-5 h-5 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl font-bold text-slate-800 truncate">Nueva requisición</h1>
                    <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">Solicitud interna de suministro</p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <a wire:navigate href="{{ route('purchases.index') }}"
                    class="hidden sm:inline-flex px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 transition-colors">
                    Cancelar
                </a>
                <button type="button" wire:click="save"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:scale-[1.02] active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Enviar requisición</span>
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-full mx-auto p-4 sm:p-6 lg:p-8">
        <form wire:submit="save" class="space-y-6 lg:space-y-8">

            {{-- ── TIPO DE REQUISICIÓN ────────────────────────────────────────── --}}
            <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                <div class="p-6 lg:p-8">
                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] mb-6">¿Qué necesitas solicitar? *</label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                        @foreach(\App\Models\PurchaseRequisition::REQUISITION_TYPES as $value => $label)
                            @php
                                $icons = ['material' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4', 'service' => 'M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9', 'tool' => 'M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 011-1h1a2 2 0 100-4H7a1 1 0 01-1-1V7a1 1 0 011-1h3a1 1 0 001-1V4z', 'asset' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4', 'mixed' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'];
                                $colors = ['material' => 'indigo', 'service' => 'purple', 'tool' => 'amber', 'asset' => 'emerald', 'mixed' => 'slate'];
                                $color = $colors[$value] ?? 'slate';
                            @endphp
                            <label class="relative group cursor-pointer">
                                <input type="radio" wire:model.live="requisition_type" value="{{ $value }}" class="sr-only">
                                <div class="h-full flex flex-col items-center gap-3 p-5 rounded-[2rem] border-2 transition-all duration-200 
                                    {{ $requisition_type === $value 
                                        ? "bg-$color-50 border-$color-500 shadow-sm shadow-$color-100" 
                                        : "bg-white border-slate-100 hover:border-$color-200 hover:bg-slate-50/50" }}">
                                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center transition-transform group-hover:scale-110 
                                        {{ $requisition_type === $value ? "bg-white text-$color-600 shadow-sm" : "bg-slate-50 text-slate-400" }}">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icons[$value] }}"/></svg>
                                    </div>
                                    <span class="text-xs font-black uppercase tracking-wider text-center {{ $requisition_type === $value ? "text-$color-700" : "text-slate-500" }}">
                                        {{ $label }}
                                    </span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    @error('requisition_type') <p class="text-xs text-rose-500 mt-4 font-bold">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- ── DATOS GENERALES ────────────────────────────────────────────── --}}
            <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                <div class="p-6 lg:p-8 space-y-8">
                    <div class="flex items-center gap-3 border-b border-slate-100 pb-5">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <h2 class="text-base font-bold text-slate-800">Detalles de la Solicitud</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                        {{-- Prioridad --}}
                        <div class="md:col-span-2 space-y-3">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Nivel de Prioridad *</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach(\App\Models\PurchaseRequisition::PRIORITY as $val => $lbl)
                                    <label class="cursor-pointer">
                                        <input type="radio" wire:model.live="priority" value="{{ $val }}" class="sr-only">
                                        <span class="inline-flex px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider border-2 transition-all
                                            {{ $priority === $val 
                                                ? "bg-white border-slate-900 text-slate-900 shadow-md ring-4 ring-slate-100" 
                                                : "bg-slate-50 border-transparent text-slate-400 hover:bg-slate-100" }}">
                                            {{ $lbl }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                            @error('priority') <p class="text-[10px] text-rose-500 font-bold">{{ $message }}</p> @enderror
                        </div>

                        {{-- Fecha requerida --}}
                        <div class="space-y-3">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Fecha Requerida *</label>
                            <input wire:model="needed_by" type="date"
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-3.5 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500/20">
                            @error('needed_by') <p class="text-[10px] text-rose-500 font-bold">{{ $message }}</p> @enderror
                        </div>

                        {{-- Moneda --}}
                        <div class="space-y-3">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Moneda Ref.</label>
                            <div class="relative">
                                <select wire:model="currency"
                                    class="w-full bg-slate-50 border-none rounded-2xl px-5 py-3.5 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500/20 appearance-none cursor-pointer">
                                    <option value="MXN">MXN — Pesos</option>
                                    <option value="USD">USD — Dólares</option>
                                </select>
                                <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                        </div>

                        {{-- Tipo de gasto --}}
                        <div class="space-y-3">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Centro de Costo / Gasto</label>
                            <select wire:model="expense_type"
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-3.5 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500/20">
                                <option value="">— Sin clasificar —</option>
                                @foreach(\App\Models\PurchaseRequisition::EXPENSE_TYPES as $val => $lbl)
                                    <option value="{{ $val }}">{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Proyecto --}}
                        <div class="md:col-span-3 space-y-3">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Proyecto o Referencia</label>
                            <input wire:model="project_name" type="text" placeholder="Ej. Obra Norte, Mantenimiento Preventivo, Expansión..."
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-3.5 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500/20">
                        </div>

                        {{-- Justificación --}}
                        <div class="md:col-span-4 space-y-3">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Motivo / Justificación *</label>
                            <textarea wire:model="justification" rows="2" placeholder="Explica brevemente por qué se requiere esta compra..."
                                class="w-full bg-slate-50 border-none rounded-2xl px-6 py-5 text-base font-medium text-slate-700 focus:ring-2 focus:ring-indigo-500/20"></textarea>
                            @error('justification') <p class="text-[10px] text-rose-500 font-bold">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── PARTIDAS ────────────────────────────────────────────────── --}}
            <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                <div class="p-6 lg:p-8 space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-5">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-slate-800">Partidas Solicitadas</h2>
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Productos o servicios específicos</p>
                            </div>
                        </div>
                        <livewire:shared.product-picker />
                    </div>

                    {{-- Buscador Rápido --}}
                    <div class="relative group">
                        <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 group-focus-within:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input wire:model.live.debounce.250ms="productSearch" type="text"
                            placeholder="Buscar en catálogo para agregar rápidamente..."
                            class="w-full pl-10 pr-4 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500/20 transition-all">
                        
                        @if(count($productResults) > 0)
                            <div class="absolute top-full left-0 right-0 bg-white border border-slate-200 rounded-2xl shadow-2xl z-40 mt-2 overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                                @foreach($productResults as $result)
                                    <button type="button" wire:click="addProduct({{ $result['id'] }})"
                                        class="w-full text-left px-5 py-4 hover:bg-slate-50 transition flex items-center justify-between border-b border-slate-50 last:border-0 group">
                                        <div>
                                            <p class="text-sm font-bold text-slate-800 group-hover:text-indigo-600">{{ $result['name'] }}</p>
                                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">SKU: {{ $result['sku'] ?? '—' }}</p>
                                        </div>
                                        <div class="text-right shrink-0 ml-4 space-y-1">
                                            @php $avail = (float)($result['stock_available'] ?? 0); @endphp
                                            <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest {{ $avail > 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-50 text-slate-400' }}">
                                                {{ $avail > 0 ? "En Stock: " . number_format($avail, 0) : "Sin Stock" }}
                                            </span>
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
                                        <th class="px-5 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest w-32">Tipo</th>
                                        <th class="px-5 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Descripción</th>
                                        <th class="px-5 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest w-28">Cant.</th>
                                        <th class="px-5 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest w-24">Unidad</th>
                                        <th class="px-5 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest w-32">P. Estimado</th>
                                        <th class="px-5 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest w-12"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200/40 bg-white">
                                    @foreach($items as $index => $item)
                                        <tr class="hover:bg-slate-50/50 transition-colors">
                                            <td class="px-5 py-3">
                                                <select wire:model.live="items.{{ $index }}.item_type"
                                                    class="w-full border-none focus:ring-0 p-0 text-[10px] font-black uppercase tracking-wider text-indigo-600 bg-transparent cursor-pointer">
                                                    @foreach(\App\Models\PurchaseRequisitionItem::ITEM_TYPES as $val => $lbl)
                                                        <option value="{{ $val }}">{{ $lbl }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td class="px-5 py-3">
                                                <input wire:model="items.{{ $index }}.description" type="text"
                                                    class="w-full border-none focus:ring-0 p-0 text-sm font-bold text-slate-800 placeholder-slate-300 bg-transparent"
                                                    placeholder="¿Qué necesitas?">
                                                @error("items.{$index}.description") <p class="text-[10px] text-rose-500 mt-1 font-bold">{{ $message }}</p> @enderror
                                            </td>
                                            <td class="px-5 py-3">
                                                <input wire:model.live="items.{{ $index }}.quantity" type="number" step="0.01" min="0.01"
                                                    class="w-full border-none focus:ring-0 p-0 text-sm font-black text-slate-900 bg-transparent">
                                            </td>
                                            <td class="px-5 py-3">
                                                <input wire:model="items.{{ $index }}.unit" type="text"
                                                    class="w-full border-none focus:ring-0 p-0 text-[10px] font-bold text-slate-400 uppercase bg-transparent"
                                                    placeholder="PZA">
                                            </td>
                                            <td class="px-5 py-3">
                                                <div class="flex items-center gap-1">
                                                    <span class="text-xs font-bold text-slate-400">$</span>
                                                    <input wire:model.live="items.{{ $index }}.unit_price" type="number" step="0.01" min="0"
                                                        class="w-full border-none focus:ring-0 p-0 text-sm font-bold text-slate-600 bg-transparent">
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

                        {{-- Total Estimado --}}
                        @php $total = collect($items)->sum(fn($i) => ($i['quantity'] ?? 0) * ($i['unit_price'] ?? 0)); @endphp
                        <div class="bg-slate-100/50 border-t border-slate-200/60 p-6 flex flex-col items-end">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-1">Monto Estimado</span>
                            <div class="flex items-baseline gap-2">
                                <span class="text-xs font-black text-indigo-500">{{ $currency }}</span>
                                <span class="text-2xl font-black text-slate-900">${{ number_format($total, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Botones de acción rápida --}}
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <button type="button" wire:click="addItem('product')" class="flex items-center justify-center gap-2 p-3 border-2 border-dashed border-slate-200 rounded-2xl text-[10px] font-black uppercase tracking-wider text-slate-400 hover:border-indigo-200 hover:text-indigo-600 hover:bg-indigo-50/50 transition-all">+ Producto</button>
                        <button type="button" wire:click="addItem('service')" class="flex items-center justify-center gap-2 p-3 border-2 border-dashed border-slate-200 rounded-2xl text-[10px] font-black uppercase tracking-wider text-slate-400 hover:border-purple-200 hover:text-purple-600 hover:bg-purple-50/50 transition-all">+ Servicio</button>
                        <button type="button" wire:click="addItem('tool')" class="flex items-center justify-center gap-2 p-3 border-2 border-dashed border-slate-200 rounded-2xl text-[10px] font-black uppercase tracking-wider text-slate-400 hover:border-amber-200 hover:text-amber-600 hover:bg-amber-50/50 transition-all">+ Herramienta</button>
                        <button type="button" wire:click="addItem('asset')" class="flex items-center justify-center gap-2 p-3 border-2 border-dashed border-slate-200 rounded-2xl text-[10px] font-black uppercase tracking-wider text-slate-400 hover:border-emerald-200 hover:text-emerald-600 hover:bg-emerald-50/50 transition-all">+ Activo</button>
                    </div>

                    {{-- Alertas de Stock --}}
                    @php
                        $itemsWithEnoughStock = collect($items)->filter(function($i) {
                            $s = $i['stock_info'] ?? null;
                            return $i['item_type'] === 'product' && $s && (float)$s['available'] >= (float)($i['quantity'] ?? 1);
                        });
                    @endphp
                    @if($itemsWithEnoughStock->count() > 0)
                        <div class="bg-emerald-50/80 rounded-[2rem] p-5 border border-emerald-100 flex items-start gap-4 animate-in fade-in slide-in-from-top-4">
                            <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center text-emerald-500 shadow-sm shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-emerald-900">Optimización de Inventario</p>
                                <p class="text-[11px] text-emerald-800/80 leading-relaxed font-medium">
                                    Detectamos que <strong>{{ $itemsWithEnoughStock->count() }} partidas</strong> tienen existencias suficientes. Considera si es posible surtirlas desde almacén interno antes de proceder con la compra externa.
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>


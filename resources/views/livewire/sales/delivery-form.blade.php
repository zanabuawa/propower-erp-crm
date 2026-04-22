<div class="min-h-screen bg-slate-50/50 -m-4 lg:-m-6">
    {{-- ── STICKY HEADER ────────────────────────────────────────────────── --}}
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 max-w-full mx-auto">
            <div class="flex items-center gap-3 min-w-0">
                @if($order)
                    <a wire:navigate href="{{ route('sales.orders.show', $order) }}" 
                       class="group flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-teal-600 hover:border-teal-100 hover:shadow-sm transition-all duration-200">
                        <svg class="w-5 h-5 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                @endif
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl font-bold text-slate-800 truncate">Salida de Almacén</h1>
                    @if($order)
                        <p class="text-[11px] text-teal-600 font-black uppercase tracking-widest truncate">
                            Pedido confirmed: {{ $order->folio }} — {{ $order->customer->name }}
                        </p>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <a wire:navigate href="{{ $order ? route('sales.orders.show', $order) : route('inventory.movements.index') }}"
                    class="hidden sm:inline-flex px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 transition-colors">
                    Cancelar
                </a>
                @if(!empty($items))
                <button type="button" wire:click="save" wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-teal-600 to-teal-700 hover:from-teal-700 hover:to-teal-800 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all duration-200 shadow-lg shadow-teal-500/25 hover:shadow-teal-500/40 hover:scale-[1.02] active:scale-[0.98]">
                    <svg wire:loading.remove wire:target="save" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    <svg wire:loading wire:target="save" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span>Confirmar Entrega</span>
                </button>
                @endif
            </div>
        </div>
    </div>

    <div class="max-w-full mx-auto p-4 sm:p-6 lg:p-8 space-y-8">
        {{-- Flash Alerts --}}
        @if(session('scan_ok'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-black uppercase tracking-widest rounded-2xl px-6 py-4 shadow-sm animate-in fade-in slide-in-from-top-4">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    {{ session('scan_ok') }}
                </div>
            </div>
        @endif
        @if(session('scan_error'))
            <div class="bg-rose-50 border border-rose-200 text-rose-700 text-xs font-black uppercase tracking-widest rounded-2xl px-6 py-4 shadow-sm animate-in shake duration-300">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    {{ session('scan_error') }}
                </div>
            </div>
        @endif

        <form class="space-y-8">
            {{-- ── SECCIÓN: DATOS DE SALIDA ──────────────────────────────────── --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="p-6 lg:p-8 space-y-8">
                        <div class="flex items-center gap-3 border-b border-slate-100 pb-5">
                            <div class="w-10 h-10 rounded-xl bg-teal-50 flex items-center justify-center text-teal-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            </div>
                            <h2 class="text-base font-bold text-slate-800">Control de Almacén</h2>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Almacén de Surtido *</label>
                                <div class="relative">
                                    <select wire:model.live="warehouse_id"
                                        class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-teal-500/10 appearance-none cursor-pointer transition-all">
                                        <option value="">— Seleccionar origen —</option>
                                        @foreach($warehouses as $wh)
                                            <option value="{{ $wh->id }}">{{ $wh->name }} — {{ $wh->branch->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </div>
                                </div>
                                @error('warehouse_id') <p class="text-[10px] text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Motivo de la Salida *</label>
                                <select wire:model.live="reason"
                                    class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-teal-500/10 appearance-none cursor-pointer">
                                    @foreach($reasons as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="md:col-span-2 space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Notas / Referencias de Envío</label>
                                <input wire:model="notes" type="text" placeholder="Ej. Paquetería FedEx, Guía 99283..."
                                    class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-teal-500/10">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Escaneo --}}
                <div class="lg:col-span-1 bg-indigo-600 rounded-3xl p-8 text-white shadow-xl shadow-indigo-100 relative overflow-hidden group">
                    <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-white/10 rounded-full blur-3xl transition-transform group-hover:scale-150 duration-700"></div>
                    <div class="relative z-10 space-y-6">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center backdrop-blur-md">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1M12 19v1M4 12h1M19 12h1M6.22 6.22l.7.7M17.08 17.08l.7.7M6.22 17.78l.7-.7M17.08 6.92l.7-.7"/><rect x="7" y="7" width="10" height="10" rx="1" stroke-width="2"/></svg>
                            </div>
                            <h3 class="text-sm font-black uppercase tracking-[0.2em]">Escaneo de Lotes</h3>
                        </div>
                        <p class="text-xs font-medium text-indigo-100 leading-relaxed">
                            Agiliza el surtido escaneando el código de barras del lote. El sistema lo asignará automáticamente siguiendo la regla PEPS.
                        </p>
                        <div class="relative group/input">
                            <input wire:model.live.debounce.300ms="scanInput" type="text" placeholder="Escanear aquí..."
                                class="w-full bg-white/10 border-2 border-white/20 rounded-2xl px-5 py-4 text-sm font-bold text-white placeholder-white/40 focus:bg-white focus:text-indigo-900 focus:border-white focus:ring-0 transition-all outline-none">
                            <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-5 h-5 text-white/30 group-focus-within/input:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <p class="text-[9px] font-black uppercase tracking-widest text-indigo-200/60 text-center italic">Esperando lectura láser...</p>
                    </div>
                </div>
            </div>

            {{-- ── SECCIÓN: PRODUCTOS ────────────────────────────────────────── --}}
            <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                <div class="p-6 lg:p-8 space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-5">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-slate-800">Artículos a Surtir</h2>
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Asignación de inventario físico</p>
                            </div>
                        </div>
                        <div class="flex p-1 bg-slate-100 rounded-xl w-fit">
                            <button type="button" wire:click="setFullDelivery" class="px-4 py-1.5 text-[10px] font-black uppercase tracking-widest rounded-lg transition-all {{ $delivery_mode === 'full' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">Completa</button>
                            <button type="button" wire:click="setPartialDelivery" class="px-4 py-1.5 text-[10px] font-black uppercase tracking-widest rounded-lg transition-all {{ $delivery_mode === 'partial' ? 'bg-white text-amber-600 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">Parcial</button>
                        </div>
                    </div>

                    @error('items') <p class="text-[10px] text-rose-500 font-black uppercase tracking-widest px-2">{{ $message }}</p> @enderror

                    @if(empty($items))
                        <div class="py-12 text-center bg-slate-50 rounded-[2rem] border border-dashed border-slate-200">
                            <div class="w-16 h-16 rounded-full bg-white flex items-center justify-center text-emerald-500 mx-auto mb-4 shadow-sm border border-emerald-50">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <p class="text-sm font-bold text-slate-400 uppercase tracking-widest">Surtido Completo — No hay pendientes</p>
                        </div>
                    @else
                        <div class="space-y-6">
                            @foreach($items as $index => $item)
                                <div x-data="{ open: true }" class="bg-white rounded-3xl border border-slate-200/60 overflow-hidden group hover:border-teal-200 transition-all">
                                    <div class="p-5 flex items-center gap-6">
                                        {{-- Checkbox --}}
                                        <div class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" wire:model.live="items.{{ $index }}.include" class="sr-only peer">
                                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-teal-500 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                                        </div>

                                        <div class="flex-1 min-w-0">
                                            <div class="flex flex-wrap items-center gap-x-4 gap-y-2 mb-1">
                                                <h4 class="text-sm font-black text-slate-800 truncate">{{ $item['product_name'] }}</h4>
                                                @php
                                                    $lotAvail = (float)($item['lot_available'] ?? 0);
                                                    $needed   = (float)$item['quantity_to_deliver'];
                                                    $stockOk  = $lotAvail >= $needed;
                                                @endphp
                                                <span class="inline-flex px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-widest {{ $stockOk ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600 animate-pulse' }}">
                                                    {{ $lotAvail }} Disponibles
                                                </span>
                                            </div>
                                            <div class="flex items-center gap-6">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-[9px] font-black text-slate-400 uppercase">Pendiente:</span>
                                                    <span class="text-xs font-black text-slate-700">{{ $item['quantity_pending'] }}</span>
                                                </div>
                                                <div class="flex items-center gap-3">
                                                    <label class="text-[9px] font-black text-indigo-500 uppercase">A Entregar:</label>
                                                    <input wire:model.live.debounce.400ms="items.{{ $index }}.quantity_to_deliver"
                                                        type="number" step="0.01" min="0" max="{{ $item['quantity_pending'] }}"
                                                        {{ !$item['include'] ? 'disabled' : '' }}
                                                        class="w-24 bg-slate-50 border-none rounded-xl px-3 py-1.5 text-xs font-black text-center text-indigo-700 focus:ring-4 focus:ring-indigo-500/10 disabled:opacity-30">
                                                </div>
                                            </div>
                                        </div>

                                        <button type="button" @click="open = !open"
                                            class="p-2 rounded-xl bg-slate-50 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition-all">
                                            <svg class="w-5 h-5 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        </button>
                                    </div>

                                    {{-- Tabla de Lotes --}}
                                    <div x-show="open" x-transition class="px-5 pb-5 animate-in fade-in slide-in-from-top-2">
                                        <div class="bg-slate-50 rounded-2xl border border-slate-100 overflow-hidden shadow-inner">
                                            @if(empty($item['lot_lines']))
                                                <div class="p-8 text-center space-y-2">
                                                    <div class="w-12 h-12 rounded-full bg-white flex items-center justify-center text-amber-400 mx-auto shadow-sm border border-amber-50">
                                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                    </div>
                                                    <p class="text-[10px] font-black text-amber-700 uppercase tracking-widest">Sin existencias en este almacén</p>
                                                    @if(!$warehouse_id) <p class="text-[9px] text-slate-400 font-bold uppercase">Selecciona un almacén para habilitar el inventario</p> @endif
                                                </div>
                                            @else
                                                <table class="w-full text-[10px]">
                                                    <thead>
                                                        <tr class="bg-slate-100/50 border-b border-slate-200/50">
                                                            <th class="text-left px-5 py-3 font-black text-slate-400 uppercase tracking-widest">Folio de Lote / Regla PEPS</th>
                                                            <th class="text-left px-5 py-3 font-black text-slate-400 uppercase tracking-widest">Ubicación / CB</th>
                                                            <th class="text-left px-5 py-3 font-black text-slate-400 uppercase tracking-widest">Caducidad</th>
                                                            <th class="text-right px-5 py-3 font-black text-slate-400 uppercase tracking-widest">Disponible</th>
                                                            <th class="text-right px-5 py-3 font-black text-indigo-400 uppercase tracking-widest w-32">Cantidad a Descontar</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-slate-100 bg-white">
                                                        @foreach($item['lot_lines'] as $li => $line)
                                                            <tr class="{{ $li === 0 ? "bg-emerald-50/20" : "" }} group/line">
                                                                <td class="px-5 py-3">
                                                                    <span class="font-mono font-black text-slate-700">{{ $line['lot_number'] }}</span>
                                                                    @if($li === 0)
                                                                        <span class="ml-2 inline-flex px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 font-black uppercase text-[8px]">Prioridad PEPS</span>
                                                                    @endif
                                                                </td>
                                                                <td class="px-5 py-3 font-bold text-slate-500 tracking-tighter">{{ $line['barcode'] }}</td>
                                                                <td class="px-5 py-3">
                                                                    @if($line['expiry_date'])
                                                                        @php $exp = \Carbon\Carbon::parse($line['expiry_date']); @endphp
                                                                        <span class="font-bold {{ $exp->isPast() ? 'text-rose-600' : ($exp->diffInDays() < 30 ? 'text-amber-600' : 'text-slate-600') }}">
                                                                            {{ $exp->format('d/m/Y') }}
                                                                        </span>
                                                                    @else
                                                                        <span class="text-slate-300 font-bold italic">—</span>
                                                                    @endif
                                                                </td>
                                                                <td class="px-5 py-3 text-right font-black text-slate-800 text-sm tracking-tight">{{ number_format($line['available'], 2) }}</td>
                                                                <td class="px-5 py-3">
                                                                    <input wire:model.live.debounce.400ms="items.{{ $index }}.lot_lines.{{ $li }}.quantity"
                                                                        type="number" step="0.0001" min="0" max="{{ $line['available'] }}"
                                                                        class="w-full bg-slate-50 border-none rounded-lg px-3 py-1.5 text-right font-black text-indigo-600 focus:ring-4 focus:ring-indigo-500/10 {{ $li === 0 ? "ring-1 ring-emerald-200" : "" }}">
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                    <tfoot class="bg-slate-50 border-t border-slate-100">
                                                        <tr>
                                                            <td colspan="4" class="px-5 py-2.5 text-right text-[9px] font-black text-slate-400 uppercase tracking-widest">Total Asignado:</td>
                                                            <td class="px-5 py-2.5 text-right font-black text-slate-900 text-xs">
                                                                {{ number_format(collect($item['lot_lines'])->sum('quantity'), 4) }}
                                                            </td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            @endif
                                        </div>

                                        @php
                                            $assigned = collect($item['lot_lines'])->sum(fn($l) => (float)$l['quantity']);
                                            $delta    = abs($assigned - (float)$item['quantity_to_deliver']);
                                        @endphp
                                        @if($delta > 0.001 && (float)$item['quantity_to_deliver'] > 0)
                                            <div class="mt-3 flex items-center gap-2 px-4 py-2 bg-amber-50 rounded-xl border border-amber-100 text-[9px] font-black uppercase text-amber-700 animate-in fade-in zoom-in-95">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                Descuadre Detectado: Asignado ({{ number_format($assigned, 2) }}) ≠ Solicitado ({{ number_format((float)$item['quantity_to_deliver'], 2) }})
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>


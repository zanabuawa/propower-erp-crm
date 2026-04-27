<div class="min-h-screen bg-slate-50/50 -m-4 lg:-m-6">
    {{-- ── STICKY HEADER ────────────────────────────────────────────────── --}}
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 max-w-full mx-auto">
            <div class="flex items-center gap-3 min-w-0">
                <a wire:navigate href="{{ route('inventory.movements.index') }}" 
                   class="group flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-100 hover:shadow-sm transition-all duration-200">
                    <svg class="w-5 h-5 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl font-bold text-slate-800 truncate">Nuevo movimiento de stock</h1>
                    <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">Registro de operación logística</p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <a wire:navigate href="{{ route('inventory.movements.index') }}"
                    class="hidden sm:inline-flex px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 transition-colors">
                    Cancelar
                </a>
                <button type="button" wire:click="save"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:scale-[1.02] active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Confirmar movimiento</span>
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-full mx-auto p-4 sm:p-6 lg:p-8">
        <form wire:submit="save" class="grid grid-cols-1 xl:grid-cols-12 gap-6 lg:gap-8">

            {{-- ── COLUMNA IZQUIERDA: Configuración (4 cols) ────────────────── --}}
            <div class="xl:col-span-4 space-y-6">
                
                {{-- Card: Información Principal --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/30">
                        <h2 class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Detalles del movimiento</h2>
                    </div>
                    <div class="p-6 space-y-6">
                        {{-- Tipo --}}
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-600">Tipo de operación *</label>
                            <div class="relative">
                                <select wire:model.live="type"
                                    class="w-full bg-slate-50 border-none rounded-xl px-4 py-3 text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-indigo-500/20 cursor-pointer appearance-none pr-10" style="-webkit-appearance: none; -moz-appearance: none;">
                                    <option value="entry">Entrada de stock</option>
                                    <option value="exit">Salida de stock</option>
                                    <option value="adjustment">Ajuste de inventario</option>
                                    <option value="transfer">Transferencia entre almacenes</option>
                                    <option value="return">Devolución de cliente</option>
                                </select>
                                <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                        </div>

                        {{-- Fecha --}}
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-600">Fecha y hora de ejecución *</label>
                            <input wire:model="moved_at" type="datetime-local"
                                class="w-full bg-slate-50 border-none rounded-xl px-4 py-3 text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-indigo-500/20">
                        </div>

                        {{-- Almacén --}}
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-600">{{ $type === 'transfer' ? 'Almacén origen *' : 'Almacén de afectación *' }}</label>
                            <div class="relative">
                                <select wire:model="warehouse_id"
                                    class="w-full bg-slate-50 border-none rounded-xl px-4 py-3 text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-indigo-500/20 cursor-pointer appearance-none pr-10" style="-webkit-appearance: none; -moz-appearance: none;">
                                    <option value="">— Seleccionar almacén —</option>
                                    @foreach($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}">
                                            {{ $warehouse->name }} ({{ $warehouse->branch->name }})
                                        </option>
                                    @endforeach
                                </select>
                                <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                        </div>

                        @if($type === 'transfer')
                        <div class="space-y-2 animate-in slide-in-from-top-2 duration-300">
                            <label class="text-xs font-bold text-slate-600">Almacén destino *</label>
                            <div class="relative">
                                <select wire:model="warehouse_destination_id"
                                    class="w-full bg-slate-50 border-none rounded-xl px-4 py-3 text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-indigo-500/20 cursor-pointer appearance-none pr-10" style="-webkit-appearance: none; -moz-appearance: none;">
                                    <option value="">— Seleccionar almacén —</option>
                                    @foreach($warehouses as $warehouse)
                                        @if($warehouse->id != $warehouse_id)
                                            <option value="{{ $warehouse->id }}">
                                                {{ $warehouse->name }} ({{ $warehouse->branch->name }})
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                                <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Ajuste --}}
                        @if($type === 'adjustment')
                        <div class="space-y-4 animate-in slide-in-from-top-2 duration-300">
                            <div class="space-y-2">
                                <label class="text-xs font-bold text-slate-600">Motivo del ajuste *</label>
                                <select wire:model.live="adjustment_reason"
                                    class="w-full bg-slate-50 border-none rounded-xl px-4 py-3 text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-indigo-500/20 appearance-none">
                                    <option value="">— Selecciona motivo —</option>
                                    @foreach(\App\Models\StockMovement::ADJUSTMENT_REASONS as $k => $v)
                                        <option value="{{ $k }}">{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if($requiresApproval)
                                <div class="p-3 bg-amber-50 rounded-2xl border border-amber-100 flex items-start gap-2">
                                    <svg class="w-4 h-4 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <p class="text-[10px] text-amber-800 font-bold leading-tight">Este motivo requiere aprobación de gerencia mediante código OTP.</p>
                                </div>
                            @endif
                        </div>
                        @endif

                        {{-- Referencia y Notas --}}
                        <div class="space-y-4 pt-4 border-t border-slate-50">
                            <div class="space-y-2">
                                <label class="text-xs font-bold text-slate-600">Referencia / Folio ext.</label>
                                <input wire:model="reference" type="text" placeholder="Ej. Factura #123, OC-456"
                                    class="w-full bg-slate-50 border-none rounded-xl px-4 py-3 text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-indigo-500/20">
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-bold text-slate-600">Notas adicionales</label>
                                <textarea wire:model="notes" rows="3" placeholder="Observaciones sobre el movimiento..."
                                    class="w-full bg-slate-50 border-none rounded-xl px-4 py-3 text-sm text-slate-700 focus:ring-2 focus:ring-indigo-500/20 resize-none"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ── COLUMNA DERECHA: Productos (8 cols) ──────────────────────── --}}
            <div class="xl:col-span-8 space-y-6">
                
                {{-- Card: Selección de Productos --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/30 flex items-center justify-between">
                        <h2 class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Partidas del movimiento</h2>
                        <livewire:shared.product-picker />
                    </div>
                    <div class="p-6 space-y-6">
                        
                        {{-- Buscador Premium --}}
                        <div class="relative group" x-data="{ open: @entangle('productResults').defer }">
                            <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-indigo-500 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                            <input wire:model.live.debounce.300ms="productSearch" type="text"
                                placeholder="Escribe el nombre, SKU o escanea el código de barras..."
                                class="w-full bg-slate-50 border-none rounded-2xl pl-12 pr-4 py-4 text-sm font-semibold text-slate-800 focus:ring-2 focus:ring-indigo-500/20 transition-all shadow-inner">
                            
                            @if(count($productResults) > 0)
                                <div class="absolute top-full left-0 right-0 bg-white border border-slate-100 rounded-2xl shadow-2xl z-20 mt-2 overflow-hidden animate-in fade-in slide-in-from-top-2">
                                    @foreach($productResults as $result)
                                        <button type="button" wire:click="addProduct({{ $result['id'] }})"
                                            class="w-full text-left px-5 py-3 hover:bg-indigo-50 transition flex items-center justify-between border-b border-slate-50 last:border-0 group">
                                            <div class="min-w-0">
                                                <p class="text-sm font-bold text-slate-800 group-hover:text-indigo-700 truncate">{{ $result['name'] }}</p>
                                                <p class="text-[10px] text-slate-400 font-mono font-bold">{{ $result['sku'] ?? 'SIN SKU' }}</p>
                                            </div>
                                            <div class="shrink-0 flex items-center gap-3">
                                                <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest transition-opacity">Agregar Partida</span>
                                                <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                                </div>
                                            </div>
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        {{-- Listado de Partidas --}}
                        <div class="overflow-x-auto -mx-6">
                            @if(count($items) > 0)
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="text-[10px] font-black text-slate-400 uppercase tracking-[0.15em] border-y border-slate-50">
                                            <th class="px-6 py-4 text-left">Producto</th>
                                            <th class="px-4 py-4 text-center w-32">Cantidad</th>
                                            <th class="px-4 py-4 text-center w-36">Costo Unit.</th>
                                            @if(in_array($type, ['entry', 'return']))
                                                <th class="px-4 py-4 text-left w-48">Gestión de Lotes</th>
                                            @endif
                                            <th class="px-4 py-4 text-right w-36">Subtotal</th>
                                            <th class="px-6 py-4 w-12"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        @foreach($items as $index => $item)
                                            <tr class="group hover:bg-slate-50/50 transition-colors">
                                                <td class="px-6 py-4">
                                                    <p class="font-bold text-slate-800 leading-tight">{{ $item['product_name'] }}</p>
                                                    <p class="text-[10px] text-slate-400 font-mono font-bold mt-1 uppercase tracking-wider">{{ $item['sku'] ?? 'N/A' }}</p>
                                                </td>
                                                <td class="px-4 py-4">
                                                    <div class="relative">
                                                        <input wire:model.live="items.{{ $index }}.quantity"
                                                            type="number" step="0.01" min="0.01"
                                                            class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-sm font-bold text-center text-slate-800 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400">
                                                    </div>
                                                </td>
                                                <td class="px-4 py-4">
                                                    <div class="relative">
                                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-bold">$</span>
                                                        <input wire:model.live="items.{{ $index }}.unit_price"
                                                            type="number" step="0.01" min="0"
                                                            class="w-full bg-white border border-slate-200 rounded-xl pl-6 pr-3 py-2 text-sm font-bold text-right text-slate-800 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400">
                                                    </div>
                                                </td>
                                                @if(in_array($type, ['entry', 'return']))
                                                    <td class="px-4 py-4 space-y-2">
                                                        <div class="relative">
                                                            <input wire:model="items.{{ $index }}.expiry_date"
                                                                type="date"
                                                                class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 focus:ring-2 focus:ring-indigo-500/20">
                                                        </div>
                                                        <input wire:model="items.{{ $index }}.lot_notes"
                                                            type="text" placeholder="Notas lote (Ej. Lote A1)"
                                                            class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-[10px] font-medium text-slate-600 focus:ring-2 focus:ring-indigo-500/20">
                                                    </td>
                                                @endif
                                                <td class="px-4 py-4 text-right">
                                                    <p class="text-[10px] text-slate-400 font-bold mb-0.5">Monto partida</p>
                                                    <p class="text-sm font-black text-slate-800">${{ number_format($item['quantity'] * $item['unit_price'], 2) }}</p>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <button type="button" wire:click="removeItem({{ $index }})"
                                                        class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-300 hover:text-rose-500 hover:bg-rose-50 transition-all opacity-0 group-hover:opacity-100">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <div class="px-6 py-20 flex flex-col items-center justify-center text-center">
                                    <div class="w-16 h-16 rounded-3xl bg-slate-50 flex items-center justify-center text-slate-300 mb-4 border border-slate-100 shadow-inner">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                    </div>
                                    <h3 class="text-sm font-bold text-slate-800">No hay productos en el listado</h3>
                                    <p class="text-xs text-slate-400 mt-1 max-w-[280px]">Utiliza el buscador superior o el selector masivo para agregar partidas al movimiento.</p>
                                </div>
                            @endif
                        </div>

                        {{-- Resumen Final --}}
                        @if(count($items) > 0)
                            <div class="mt-6 pt-6 border-t border-slate-100 flex flex-col sm:flex-row items-end sm:items-center justify-between gap-4">
                                <div class="flex items-center gap-2">
                                    <span class="px-3 py-1 bg-indigo-50 text-indigo-600 rounded-lg text-[10px] font-black uppercase tracking-wider border border-indigo-100">
                                        {{ count($items) }} Partidas registradas
                                    </span>
                                </div>
                                <div class="flex items-center gap-6">
                                    <div class="text-right">
                                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Valor total del movimiento</p>
                                        <p class="text-3xl font-black text-indigo-600 mt-1">
                                            <span class="text-lg font-bold mr-0.5">$</span>{{ number_format(collect($items)->sum(fn($i) => $i['quantity'] * $i['unit_price']), 2) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

            </div>

        </form>
    </div>

    {{-- ═══════════════════════════════════════════════════
         MODAL OTP UIX PRO MAX
    ═══════════════════════════════════════════════════ --}}

    @if($showOtpModal)
    <div class="fixed inset-0 z-[60] flex items-center justify-center p-4 sm:p-6 bg-slate-900/60 backdrop-blur-sm animate-in fade-in duration-300">
        <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-sm overflow-hidden border border-white/20 animate-in zoom-in duration-300">
            
            <div class="p-8 text-center space-y-6">
                {{-- Icon --}}
                <div class="mx-auto w-20 h-20 rounded-[2rem] bg-indigo-50 flex items-center justify-center text-indigo-600 border border-indigo-100 shadow-inner">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>

                <div>
                    <h3 class="text-2xl font-black text-slate-800">Aprobación requerida</h3>
                    <p class="text-xs text-slate-400 font-medium mt-2 leading-relaxed px-4">
                        Se ha enviado un código de seguridad a tu correo para autorizar este ajuste.
                    </p>
                </div>

                <div class="space-y-4">
                    <div class="relative group">
                        <input wire:model="otpCode" type="text" maxlength="6"
                            class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-center tracking-[0.5em] text-2xl font-black text-indigo-600 placeholder-slate-200 focus:ring-2 focus:ring-indigo-500/20 transition-all"
                            placeholder="000000">
                    </div>
                    
                    @if(session('otp_sent'))
                        <p class="text-[10px] text-emerald-600 font-bold uppercase tracking-wider animate-pulse">{{ session('otp_sent') }}</p>
                    @endif
                    @if($otpError)
                        <p class="text-[10px] text-rose-500 font-bold uppercase tracking-wider">{{ $otpError }}</p>
                    @endif
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <button wire:click="sendOtp" type="button"
                        class="px-4 py-3.5 text-xs font-bold text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-2xl transition-all">
                        Reenviar código
                    </button>
                    <button wire:click="verifyOtp" type="button"
                        class="px-4 py-3.5 text-sm font-black bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl shadow-lg shadow-indigo-200 transition-all active:scale-[0.98]">
                        Aprobar
                    </button>
                </div>
            </div>

            {{-- Footer info --}}
            <div class="px-8 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-center gap-2">
                <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 animate-ping"></span>
                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">En espera de validación</span>
            </div>
        </div>
    </div>
    @endif
</div>

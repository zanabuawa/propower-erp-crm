<div class="min-h-screen bg-slate-50/50 -m-4 lg:-m-6">
    {{-- ── STICKY HEADER ────────────────────────────────────────────────── --}}
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 max-w-full mx-auto">
            <div class="flex items-center gap-3 min-w-0">
                <a wire:navigate href="{{ route('sales.credit-notes.index') }}" 
                   class="group flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-100 hover:shadow-sm transition-all duration-200">
                    <svg class="w-5 h-5 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl font-bold text-slate-800 truncate">Nueva Nota de Crédito</h1>
                    <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">Ajuste de saldo a favor del cliente</p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <a wire:navigate href="{{ route('sales.credit-notes.index') }}"
                    class="hidden sm:inline-flex px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 transition-colors">
                    Cancelar
                </a>
                <button type="button" wire:click="save" wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:scale-[1.02] active:scale-[0.98]">
                    <svg wire:loading.remove wire:target="save" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    <svg wire:loading wire:target="save" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span>Generar Nota</span>
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
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <h2 class="text-base font-bold text-slate-800">Referencia de Factura</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                        {{-- Factura --}}
                        <div class="md:col-span-2 space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Factura Relacionada *</label>
                            <div class="relative">
                                <select wire:model.live="invoiceId"
                                    class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 appearance-none cursor-pointer">
                                    <option value="">— Elegir factura para aplicar saldo —</option>
                                    @foreach($invoiceOptions as $inv)
                                        <option value="{{ $inv['id'] }}">{{ $inv['folio'] }} (Total: ${{ number_format($inv['total'], 2) }})</option>
                                    @endforeach
                                </select>
                                <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                            @error('invoiceId') <p class="text-[10px] text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Cliente --}}
                        <div class="md:col-span-2 space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Cliente</label>
                            <select wire:model.live="customerId"
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 appearance-none cursor-pointer">
                                <option value="">— Seleccionar cliente —</option>
                                @foreach($customerOptions as $c)
                                    <option value="{{ $c['id'] }}">{{ $c['name'] }}</option>
                                @endforeach
                            </select>
                            @error('customerId') <p class="text-[10px] text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Motivo --}}
                        <div class="md:col-span-4 space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Motivo / Justificación del Ajuste *</label>
                            <textarea wire:model="reason" rows="2" placeholder="Describe brevemente por qué se genera este saldo a favor (Ej. Devolución de producto, error en precio pactado...)"
                                class="w-full bg-slate-50 border-none rounded-[2rem] px-6 py-5 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-indigo-500/10 transition-all resize-none"></textarea>
                            @error('reason') <p class="text-[10px] text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── SECCIÓN: PARTIDAS ─────────────────────────────────────────── --}}
            <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                <div class="p-6 lg:p-8 space-y-6">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-5">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-teal-50 flex items-center justify-center text-teal-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-slate-800">Conceptos a Descontar</h2>
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Artículos o montos de ajuste</p>
                            </div>
                        </div>
                        <button type="button" wire:click="addItem"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-50 text-indigo-600 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-100 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            Agregar Partida
                        </button>
                    </div>

                    <div class="bg-slate-50 rounded-2xl border border-slate-200/50 overflow-hidden shadow-inner">
                        <div class="overflow-x-auto overflow-visible">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="text-left border-b border-slate-200/60">
                                        <th class="px-5 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Descripción / Artículo</th>
                                        <th class="px-5 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest w-24 text-center">Cant.</th>
                                        <th class="px-5 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest w-32 text-center">P. Unitario</th>
                                        <th class="px-5 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest w-20 text-center">IVA</th>
                                        <th class="px-5 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest w-32 text-right">Subtotal</th>
                                        <th class="w-12"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200/40 bg-white">
                                    @foreach($items as $i => $item)
                                        <tr class="hover:bg-slate-50/50 transition-colors">
                                            <td class="px-5 py-3">
                                                <input wire:model.live="items.{{ $i }}.description" type="text"
                                                    class="w-full border-none focus:ring-0 p-0 text-sm font-bold text-slate-800 placeholder-slate-300 bg-transparent"
                                                    placeholder="Descripción del ajuste...">
                                                @error("items.{$i}.description") <p class="text-[10px] text-rose-500 mt-1 font-bold">{{ $message }}</p> @enderror
                                            </td>
                                            <td class="px-5 py-3">
                                                <input wire:model.live="items.{{ $i }}.quantity" type="number" step="0.01" min="0.01"
                                                    class="w-full border-none focus:ring-0 p-0 text-sm font-black text-center text-slate-900 bg-transparent">
                                            </td>
                                            <td class="px-5 py-3">
                                                <div class="flex items-center justify-center gap-1">
                                                    <span class="text-xs font-bold text-slate-400">$</span>
                                                    <input wire:model.live="items.{{ $i }}.unit_price" type="number" step="0.01" min="0"
                                                        class="w-20 border-none focus:ring-0 p-0 text-sm font-bold text-indigo-600 bg-transparent text-center">
                                                </div>
                                            </td>
                                            <td class="px-5 py-3 text-center">
                                                <select wire:model.live="items.{{ $i }}.tax_rate"
                                                    class="w-full border-none focus:ring-0 p-0 text-[10px] font-black uppercase text-slate-600 bg-transparent cursor-pointer">
                                                    <option value="0">0%</option>
                                                    <option value="8">8%</option>
                                                    <option value="16">16%</option>
                                                </select>
                                            </td>
                                            <td class="px-5 py-3 text-right">
                                                <span class="text-sm font-black text-slate-900">
                                                    ${{ number_format($item['subtotal'] ?? 0, 2) }}
                                                </span>
                                            </td>
                                            <td class="px-5 py-3 text-center">
                                                @if(count($items) > 1)
                                                    <button type="button" wire:click="removeItem({{ $i }})"
                                                        class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-300 hover:text-rose-600 hover:bg-rose-50 transition-all">
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
                        <div class="bg-slate-100/50 border-t border-slate-200/60 p-6 lg:p-8 flex flex-col items-end gap-3">
                            <div class="flex items-center gap-10">
                                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Subtotal Acumulado:</span>
                                <span class="text-sm font-bold text-slate-700">${{ number_format($subtotal, 2) }}</span>
                            </div>
                            <div class="flex items-center gap-10">
                                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">IVA Aplicado:</span>
                                <span class="text-sm font-bold text-slate-700">${{ number_format($tax, 2) }}</span>
                            </div>
                            <div class="flex items-center gap-10 pt-6 border-t border-slate-200 mt-2">
                                <span class="text-[11px] font-black text-slate-900 uppercase tracking-widest">Total Nota de Crédito:</span>
                                <div class="flex items-baseline gap-2">
                                    <span class="text-xs font-black text-indigo-600">MXN</span>
                                    <span class="text-4xl font-black text-slate-900 tracking-tighter">${{ number_format($total, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>


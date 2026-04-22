<div class="min-h-screen bg-slate-50/50 -m-4 lg:-m-6">
    {{-- ── STICKY HEADER ────────────────────────────────────────────────── --}}
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 max-w-full mx-auto">
            <div class="flex items-center gap-3 min-w-0">
                <a wire:navigate href="{{ route('purchases.credit-notes.index') }}" 
                   class="group flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-100 hover:shadow-sm transition-all duration-200">
                    <svg class="w-5 h-5 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl font-bold text-slate-800 truncate">Registrar Nota de Crédito</h1>
                    <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">Ajuste de saldo a proveedor</p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <a wire:navigate href="{{ route('purchases.credit-notes.index') }}"
                    class="hidden sm:inline-flex px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 transition-colors">
                    Cancelar
                </a>
                <button type="button" wire:click="save" wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:scale-[1.02] active:scale-[0.98] disabled:opacity-50">
                    <svg wire:loading.remove wire:target="save" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    <svg wire:loading wire:target="save" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span wire:loading.remove wire:target="save">Registrar NC</span>
                    <span wire:loading wire:target="save">Guardando...</span>
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-full mx-auto p-4 sm:p-6 lg:p-8">
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">

            {{-- ── FORMULARIO PRINCIPAL ───────────────────────────────────────── --}}
            <div class="xl:col-span-2 space-y-8">
                
                {{-- Datos Generales --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="p-6 lg:p-8 space-y-8">
                        <div class="flex items-center gap-3 border-b border-slate-100 pb-5">
                            <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <h2 class="text-base font-bold text-slate-800">Datos del Documento</h2>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            {{-- Factura Vinculada --}}
                            <div class="md:col-span-2 space-y-3">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Factura a Corregir <span class="text-slate-300 normal-case">(Opcional)</span></label>
                                <div class="relative group">
                                    <select wire:model.live="invoiceId"
                                        class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500/20 appearance-none cursor-pointer">
                                        <option value="">— Ninguna (Ajuste libre) —</option>
                                        @foreach($invoiceOptions as $inv)
                                            <option value="{{ $inv['id'] }}">
                                                {{ $inv['folio'] }} — {{ $inv['supplier']['name'] ?? '' }}
                                                (${{ number_format($inv['total'], 2) }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </div>
                                </div>
                                @if($loadedInvoice)
                                    <div class="flex items-center gap-2 px-4 py-2 bg-indigo-50 rounded-xl border border-indigo-100 animate-in fade-in slide-in-from-left-4">
                                        <span class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></span>
                                        <p class="text-[11px] text-indigo-700 font-bold uppercase tracking-wider">
                                            Factura: {{ $loadedInvoice['folio'] }} • Saldo Actual: ${{ number_format($loadedInvoice['balance'], 2) }}
                                        </p>
                                    </div>
                                @endif
                            </div>

                            {{-- Proveedor --}}
                            <div class="space-y-3">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Proveedor *</label>
                                <div class="relative">
                                    <select wire:model.live="supplierId"
                                        class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500/20 appearance-none cursor-pointer {{ $errors->has('supplierId') ? 'ring-2 ring-rose-500/20' : '' }}">
                                        <option value="">— Seleccionar —</option>
                                        @foreach($supplierOptions as $s)
                                            <option value="{{ $s['id'] }}">{{ $s['name'] }}</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </div>
                                </div>
                                @error('supplierId') <p class="text-[10px] text-rose-500 font-bold">{{ $message }}</p> @enderror
                            </div>

                            {{-- N° NC --}}
                            <div class="space-y-3">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">N° Nota de Crédito</label>
                                <input wire:model="supplierCreditNoteNumber" type="text" placeholder="Ej. NC-00123"
                                    class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-mono font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500/20">
                            </div>

                            {{-- Fecha y Motivo --}}
                            <div class="space-y-3">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Fecha de Emisión *</label>
                                <input wire:model="issuedAt" type="date"
                                    class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500/20">
                                @error('issuedAt') <p class="text-[10px] text-rose-500 font-bold">{{ $message }}</p> @enderror
                            </div>

                            <div class="space-y-3">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Motivo de Ajuste *</label>
                                <select wire:model="reason"
                                    class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500/20 appearance-none cursor-pointer">
                                    @foreach(\App\Models\SupplierCreditNote::REASONS as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Moneda --}}
                            <div class="space-y-3">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Moneda</label>
                                <select wire:model="currency"
                                    class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500/20 appearance-none cursor-pointer">
                                    <option value="MXN">MXN — Peso</option>
                                    <option value="USD">USD — Dólar</option>
                                </select>
                            </div>

                            {{-- Notas --}}
                            <div class="md:col-span-2 space-y-3">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Comentarios / Justificación</label>
                                <textarea wire:model="notes" rows="2" placeholder="Describe brevemente la razón de este ajuste..."
                                    class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-medium text-slate-700 focus:ring-2 focus:ring-indigo-500/20 resize-none"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Partidas --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="p-6 lg:p-8 space-y-6">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-5">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                </div>
                                <div>
                                    <h2 class="text-base font-bold text-slate-800">Partidas de la Nota</h2>
                                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Artículos o conceptos a descontar</p>
                                </div>
                            </div>
                            <button wire:click="addItem" type="button"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-50 text-indigo-600 rounded-xl text-xs font-black uppercase tracking-wider hover:bg-indigo-100 transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Agregar Concepto
                            </button>
                        </div>

                        <div class="bg-slate-50 rounded-2xl border border-slate-200/50 overflow-hidden shadow-inner">
                            <div class="overflow-x-auto overflow-visible">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="text-left border-b border-slate-200/60">
                                            <th class="px-5 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Descripción / Concepto</th>
                                            <th class="px-5 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest w-24 text-center">Cant.</th>
                                            <th class="px-5 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest w-32">P. Unitario</th>
                                            <th class="px-5 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest w-20 text-center">IVA</th>
                                            <th class="px-5 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest w-32 text-right">Subtotal</th>
                                            <th class="w-12"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-200/40 bg-white">
                                        @foreach($items as $idx => $item)
                                        <tr class="hover:bg-slate-50/50 transition-colors">
                                            <td class="px-5 py-3">
                                                <input wire:model.live="items.{{ $idx }}.description" type="text" placeholder="¿A qué corresponde el ajuste?"
                                                    class="w-full border-none focus:ring-0 p-0 text-sm font-bold text-slate-800 placeholder-slate-300 bg-transparent">
                                            </td>
                                            <td class="px-5 py-3 text-center font-black text-slate-700">
                                                <input wire:model.live="items.{{ $idx }}.quantity" type="number" min="0" step="0.001"
                                                    class="w-full border-none focus:ring-0 p-0 text-sm font-black text-center text-slate-900 bg-transparent">
                                            </td>
                                            <td class="px-5 py-3">
                                                <div class="flex items-center gap-1">
                                                    <span class="text-xs font-bold text-slate-400">$</span>
                                                    <input wire:model.live="items.{{ $idx }}.unit_price" type="number" min="0" step="0.01"
                                                        class="w-full border-none focus:ring-0 p-0 text-sm font-bold text-indigo-600 bg-transparent">
                                                </div>
                                            </td>
                                            <td class="px-5 py-3 text-center">
                                                <input wire:model.live="items.{{ $idx }}.tax_rate" type="number" min="0" max="100" step="0.01"
                                                    class="w-full border-none focus:ring-0 p-0 text-sm font-bold text-center text-slate-600 bg-transparent">
                                            </td>
                                            <td class="px-5 py-3 text-right">
                                                <span class="text-sm font-black text-slate-900">
                                                    ${{ number_format($item['subtotal'] ?? 0, 2) }}
                                                </span>
                                            </td>
                                            <td class="px-5 py-3 text-center">
                                                @if(count($items) > 1)
                                                <button wire:click="removeItem({{ $idx }})" type="button"
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
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── PANEL LATERAL DE RESUMEN ───────────────────────────────────── --}}
            <div class="space-y-6">
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm p-8 space-y-6">
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 pb-4">Resumen de Aplicación</h3>
                    
                    <div class="space-y-4">
                        <div class="flex justify-between items-center text-sm font-bold">
                            <span class="text-slate-400 uppercase tracking-widest text-[10px]">Subtotal Acumulado</span>
                            <span class="text-slate-600">${{ number_format($subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm font-bold">
                            <span class="text-slate-400 uppercase tracking-widest text-[10px]">IVA Total</span>
                            <span class="text-slate-600">${{ number_format($taxAmount, 2) }}</span>
                        </div>
                        <div class="pt-6 border-t border-slate-200 mt-2">
                            <label class="text-[10px] font-black text-indigo-600 uppercase tracking-[0.2em] mb-2 block text-right">Total Nota de Crédito</label>
                            <div class="flex items-baseline justify-end gap-2">
                                <span class="text-xs font-black text-indigo-500">{{ $currency }}</span>
                                <span class="text-4xl font-black text-slate-900 tracking-tighter">${{ number_format($total, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-indigo-600 rounded-[2.5rem] p-8 text-white shadow-xl shadow-indigo-200 relative overflow-hidden group">
                    <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-white/10 rounded-full blur-3xl transition-transform group-hover:scale-150"></div>
                    <div class="relative z-10 space-y-4">
                        <h4 class="text-sm font-black uppercase tracking-[0.2em] opacity-80">Efecto Financiero</h4>
                        <p class="text-xs font-medium leading-relaxed">
                            Al registrar esta Nota de Crédito, el saldo pendiente con el proveedor se reducirá automáticamente, afectando tus Cuentas por Pagar y el flujo de caja proyectado.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


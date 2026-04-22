<div class="min-h-screen bg-slate-50/50 -m-4 lg:-m-6">
    {{-- ── STICKY HEADER ────────────────────────────────────────────────── --}}
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 max-w-full mx-auto">
            <div class="flex items-center gap-3 min-w-0">
                <a wire:navigate href="{{ route('purchases.invoices.index') }}" 
                   class="group flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-100 hover:shadow-sm transition-all duration-200">
                    <svg class="w-5 h-5 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl font-bold text-slate-800 truncate">Registrar Factura</h1>
                    <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">Comprobante Fiscal de Proveedor</p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <a wire:navigate href="{{ route('purchases.invoices.index') }}"
                    class="hidden sm:inline-flex px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 transition-colors">
                    Cancelar
                </a>
                <button type="button" wire:click="save" wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:scale-[1.02] active:scale-[0.98] disabled:opacity-50">
                    <svg wire:loading.remove wire:target="save" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    <svg wire:loading wire:target="save" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span wire:loading.remove wire:target="save">Registrar Factura</span>
                    <span wire:loading wire:target="save">Procesando...</span>
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-full mx-auto p-4 sm:p-6 lg:p-8">
        <div class="space-y-8 lg:space-y-10">
            <x-alert />

            {{-- ── SECCIÓN 1: VINCULACIÓN ────────────────────────────────────── --}}
            <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                <div class="p-6 lg:p-8 space-y-8">
                    <div class="flex items-center gap-3 border-b border-slate-100 pb-5">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.828a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                        </div>
                        <h2 class="text-base font-bold text-slate-800">Vinculación con Operación</h2>
                    </div>

                    <div class="space-y-3">
                        <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Asociar Orden de Compra <span class="text-slate-300 normal-case">(Recomendado)</span></label>
                        <div class="relative group">
                            <select wire:model.live="orderId"
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 appearance-none cursor-pointer transition-all">
                                <option value="">— Registrar factura sin orden previa —</option>
                                @foreach($orderOptions as $o)
                                    <option value="{{ $o['id'] }}">
                                        {{ $o['folio'] }} — {{ $o['supplier']['name'] ?? '' }}
                                        (${{ number_format($o['total'], 2) }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 group-focus-within:text-indigo-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>
                        @if($loadedOrder)
                            <div class="flex items-center gap-2 px-4 py-2.5 bg-emerald-50 rounded-2xl border border-emerald-100 animate-in fade-in slide-in-from-left-4">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                <p class="text-[10px] text-emerald-700 font-black uppercase tracking-wider">Datos de la OC cargados: {{ $loadedOrder['folio'] }} • ${{ number_format($loadedOrder['total'], 2) }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ── SECCIÓN 2: DATOS DEL COMPROBANTE ────────────────────────────── --}}
            <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                <div class="p-6 lg:p-8 space-y-8">
                    <div class="flex items-center gap-3 border-b border-slate-100 pb-5">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <h2 class="text-base font-bold text-slate-800">Detalles de la Factura</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-8">
                        {{-- Proveedor --}}
                        <div class="md:col-span-2 space-y-3">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Proveedor Emisor *</label>
                            <div class="relative">
                                <select wire:model="supplierId"
                                    class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 appearance-none cursor-pointer {{ $errors->has('supplierId') ? 'ring-2 ring-rose-500/20' : '' }}">
                                    <option value="">— Seleccionar proveedor —</option>
                                    @foreach($supplierOptions as $s)
                                        <option value="{{ $s['id'] }}">{{ $s['name'] }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                            @error('supplierId') <p class="text-[10px] text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- N° Factura --}}
                        <div class="space-y-3">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">N° de Factura del Proveedor *</label>
                            <input wire:model="supplierInvoiceNumber" type="text" placeholder="Ej. ABC-12345"
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-mono font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 {{ $errors->has('supplierInvoiceNumber') ? 'ring-2 ring-rose-500/20' : '' }}">
                            @error('supplierInvoiceNumber') <p class="text-[10px] text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Fecha --}}
                        <div class="space-y-3">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Fecha de Emisión (CFDI) *</label>
                            <input wire:model="issuedAt" type="date"
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 {{ $errors->has('issuedAt') ? 'ring-2 ring-rose-500/20' : '' }}">
                            @error('issuedAt') <p class="text-[10px] text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Moneda --}}
                        <div class="space-y-3">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Moneda del Documento</label>
                            <div class="relative">
                                <select wire:model="currency"
                                    class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 appearance-none cursor-pointer">
                                    <option value="MXN">MXN — Peso Mexicano</option>
                                    <option value="USD">USD — Dólar Americano</option>
                                </select>
                                <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── SECCIÓN 3: IMPORTES Y ARCHIVOS ────────────────────────────── --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                {{-- Columna: Importes --}}
                <div class="lg:col-span-1 bg-white rounded-3xl border border-slate-200/60 shadow-sm p-6 lg:p-8 space-y-6">
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 pb-4">Montos de la Factura</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase mb-1.5 block">Subtotal</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 font-bold">$</span>
                                <input wire:model="subtotalInput" type="number" step="0.01" class="w-full bg-slate-50 border-none rounded-xl pl-8 pr-4 py-3 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10">
                            </div>
                        </div>
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase mb-1.5 block">IVA (16%)</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 font-bold">$</span>
                                <input wire:model="taxInput" type="number" step="0.01" class="w-full bg-slate-50 border-none rounded-xl pl-8 pr-4 py-3 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10">
                            </div>
                        </div>
                        <div class="pt-6 border-t border-slate-100">
                            <label class="text-[10px] font-black text-indigo-600 uppercase mb-2 block text-right">Total Factura *</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-indigo-400 font-black text-lg">$</span>
                                <input wire:model="totalInput" type="number" step="0.01" 
                                    class="w-full bg-indigo-50 border-none rounded-2xl pl-10 pr-5 py-4 text-2xl font-black text-indigo-700 focus:ring-4 focus:ring-indigo-500/20 text-right {{ $errors->has('totalInput') ? 'ring-2 ring-rose-500/40' : '' }}">
                            </div>
                            @error('totalInput') <p class="text-[10px] text-rose-500 font-bold mt-2 text-right">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- Columna: Evidencia Digital --}}
                <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-200/60 shadow-sm p-6 lg:p-8 space-y-6">
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 pb-4">Evidencia Digital (CFDI)</h3>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        {{-- XML Uploader --}}
                        <div class="space-y-3">
                            <label class="text-[10px] font-bold text-slate-400 uppercase block">Archivo XML</label>
                            <div class="relative group">
                                <input wire:model="xmlFile" type="file" accept=".xml" class="sr-only" id="xml-upload">
                                <label for="xml-upload" class="flex flex-col items-center justify-center p-8 border-2 border-dashed border-slate-100 rounded-[2.5rem] bg-slate-50/50 hover:bg-white hover:border-indigo-200 transition-all cursor-pointer">
                                    <div class="w-12 h-12 rounded-2xl bg-white flex items-center justify-center text-slate-300 group-hover:text-indigo-500 shadow-sm border border-slate-100 group-hover:border-indigo-100 transition-all mb-3">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </div>
                                    <span class="text-[10px] font-black uppercase text-slate-500 group-hover:text-indigo-700 transition-colors">Subir XML</span>
                                    <div wire:loading wire:target="xmlFile" class="text-[9px] text-indigo-500 mt-2 animate-pulse">Procesando...</div>
                                </label>
                            </div>
                            @if($xmlFile)
                                <div class="flex items-center gap-2 px-4 py-2 bg-emerald-50 rounded-xl border border-emerald-100 animate-in zoom-in-95 duration-200">
                                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    <span class="text-[10px] font-bold text-emerald-700 truncate">{{ $xmlFile->getClientOriginalName() }}</span>
                                </div>
                            @endif
                            @error('xmlFile') <p class="text-[10px] text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- PDF Uploader --}}
                        <div class="space-y-3">
                            <label class="text-[10px] font-bold text-slate-400 uppercase block">Archivo PDF</label>
                            <div class="relative group">
                                <input wire:model="pdfFile" type="file" accept=".pdf" class="sr-only" id="pdf-upload">
                                <label for="pdf-upload" class="flex flex-col items-center justify-center p-8 border-2 border-dashed border-slate-100 rounded-[2.5rem] bg-slate-50/50 hover:bg-white hover:border-rose-200 transition-all cursor-pointer">
                                    <div class="w-12 h-12 rounded-2xl bg-white flex items-center justify-center text-slate-300 group-hover:text-rose-500 shadow-sm border border-slate-100 group-hover:border-rose-100 transition-all mb-3">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                    </div>
                                    <span class="text-[10px] font-black uppercase text-slate-500 group-hover:text-rose-700 transition-colors">Subir PDF</span>
                                    <div wire:loading wire:target="pdfFile" class="text-[9px] text-indigo-500 mt-2 animate-pulse">Procesando...</div>
                                </label>
                            </div>
                            @if($pdfFile)
                                <div class="flex items-center gap-2 px-4 py-2 bg-emerald-50 rounded-xl border border-emerald-100 animate-in zoom-in-95 duration-200">
                                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    <span class="text-[10px] font-bold text-emerald-700 truncate">{{ $pdfFile->getClientOriginalName() }}</span>
                                </div>
                            @endif
                            @error('pdfFile') <p class="text-[10px] text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── SECCIÓN 4: NOTAS ────────────────────────────────────────────── --}}
            <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm p-6 lg:p-8">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-4 block">Notas Internas / Auditoría</label>
                <textarea wire:model="notes" rows="2" placeholder="Observaciones sobre esta factura, diferencias de precios, etc..."
                    class="w-full bg-slate-50 border-none rounded-2xl px-6 py-5 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-indigo-500/10 resize-none transition-all"></textarea>
            </div>

        </div>
    </div>
</div>



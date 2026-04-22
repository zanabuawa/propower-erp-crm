<div class="min-h-screen bg-slate-50/50 -m-4 lg:-m-6">
    @php
        $isOverdue = $invoice->due_at && $invoice->due_at->isPast() && !in_array($invoice->status, ['paid','cancelled']);
        $balance   = $invoice->total - $invoice->paid_amount;
    @endphp

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
                    <div class="flex items-center gap-2">
                        <h1 class="text-lg sm:text-xl font-black text-slate-800 truncate">Factura {{ $invoice->folio }}</h1>
                        <x-status-badge :status="$invoice->status" :label="\App\Models\PurchaseInvoice::STATUS[$invoice->status] ?? $invoice->status" />
                    </div>
                    <p class="text-[11px] text-slate-400 font-bold uppercase tracking-widest truncate">
                        {{ $invoice->supplier->name ?? 'Sin Proveedor' }} — {{ $invoice->supplier_invoice_number }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                @if(!in_array($invoice->status, ['paid','cancelled']))
                    @can('manage purchases')
                        <button wire:click="$set('showCancelModal', true)"
                            class="hidden sm:inline-flex px-4 py-2 text-xs font-black uppercase tracking-widest text-rose-500 hover:text-rose-700 transition-colors">
                            Cancelar Factura
                        </button>
                    @endcan
                    @if(!$showPaymentForm)
                        <button wire:click="openPaymentForm"
                            class="inline-flex items-center gap-2 bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 text-white text-xs font-black uppercase tracking-widest px-5 py-2.5 rounded-xl transition-all duration-200 shadow-lg shadow-emerald-500/25 hover:shadow-emerald-500/40 hover:scale-[1.02] active:scale-[0.98]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            <span>Registrar Pago</span>
                        </button>
                    @endif
                @endif
            </div>
        </div>
    </div>

    <div class="max-w-full mx-auto p-4 sm:p-6 lg:p-8 space-y-8">
        <x-alert />

        {{-- ── RESUMEN FINANCIERO (KPIs) ───────────────────────────────────── --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white rounded-3xl border border-slate-200/60 p-6 shadow-sm">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Monto Total</p>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-xs font-bold text-slate-400">{{ $invoice->currency }}</span>
                    <span class="text-3xl font-black text-slate-800">${{ number_format($invoice->total, 2) }}</span>
                </div>
            </div>
            <div class="bg-emerald-50/50 rounded-3xl border border-emerald-100 p-6 shadow-sm">
                <p class="text-[10px] font-black text-emerald-600 uppercase tracking-widest mb-1">Total Pagado</p>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-xs font-bold text-emerald-400">$</span>
                    <span class="text-3xl font-black text-emerald-700">${{ number_format($invoice->paid_amount, 2) }}</span>
                </div>
            </div>
            <div class="bg-white rounded-3xl border {{ $balance > 0 ? "border-rose-200 ring-4 ring-rose-50" : "border-slate-200/60" }} p-6 shadow-sm">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Saldo Pendiente</p>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-xs font-bold text-slate-400">$</span>
                    <span class="text-3xl font-black {{ $balance > 0 ? "text-rose-600" : "text-slate-400" }}">${{ number_format($balance, 2) }}</span>
                </div>
            </div>
            <div class="bg-white rounded-3xl border {{ $isOverdue ? "border-amber-200 ring-4 ring-amber-50" : "border-slate-200/60" }} p-6 shadow-sm">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Vencimiento</p>
                <div class="flex items-center gap-2">
                    <span class="text-xl font-black {{ $isOverdue ? "text-amber-600" : "text-slate-700" }}">
                        {{ $invoice->due_at?->format('d/m/Y') ?? '—' }}
                    </span>
                    @if($isOverdue)
                        <span class="px-2 py-0.5 rounded-lg bg-amber-100 text-amber-700 text-[10px] font-black uppercase">{{ $invoice->due_at->diffForHumans() }}</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            {{-- ── COLUMNA PRINCIPAL (CONTENIDO DINÁMICO) ────────────────────── --}}
            <div class="xl:col-span-2 space-y-8">
                
                {{-- Selector de Vista (Tabs as Pills) --}}
                <div class="flex items-center p-1 bg-slate-100 rounded-2xl w-fit border border-slate-200/50 shadow-inner">
                    @foreach(['payments' => 'Pagos & Abonos', 'cfdi' => 'Evidencia Fiscal'] as $tab => $label)
                        <button wire:click="$set('activeTab', '{{ $tab }}')"
                            class="px-6 py-2.5 text-xs font-black uppercase tracking-widest rounded-xl transition-all duration-200
                                {{ $activeTab === $tab ? 'bg-white text-indigo-600 shadow-sm ring-1 ring-black/5' : 'text-slate-500 hover:text-slate-700' }}">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>

                {{-- CONTENIDO: PAGOS --}}
                @if($activeTab === 'payments')
                <div class="space-y-6">
                    {{-- Formulario de Pago --}}
                    @if($showPaymentForm && !in_array($invoice->status, ['paid','cancelled']))
                        <div class="bg-indigo-50/50 rounded-[2rem] border border-indigo-100 p-8 animate-in fade-in slide-in-from-top-4 overflow-hidden relative group">
                            <div class="absolute -right-20 -top-20 w-64 h-64 bg-indigo-500/5 rounded-full blur-3xl transition-transform group-hover:scale-110"></div>
                            
                            <div class="relative z-10 space-y-8">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white shadow-lg">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    <h3 class="text-base font-black text-indigo-900 uppercase tracking-widest">Nuevo Registro de Pago</h3>
                                </div>

                                @if($paymentError)
                                    <div class="p-4 bg-rose-50 border border-rose-100 rounded-2xl text-[11px] font-bold text-rose-600 flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        {{ $paymentError }}
                                    </div>
                                @endif

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-8">
                                    <div class="space-y-2">
                                        <label class="text-[10px] font-black text-indigo-400 uppercase tracking-widest">Monto a Pagar *</label>
                                        <input wire:model.live="paymentAmount" type="number" step="0.01"
                                            class="w-full bg-white border-none rounded-2xl px-5 py-4 text-xl font-black text-indigo-700 focus:ring-4 focus:ring-indigo-500/10 transition-all">
                                        @error('paymentAmount') <p class="text-[10px] text-rose-500 font-bold">{{ $message }}</p> @enderror
                                    </div>

                                    <div class="space-y-2">
                                        <label class="text-[10px] font-black text-indigo-400 uppercase tracking-widest">Método *</label>
                                        <select wire:model="paymentMethod"
                                            class="w-full bg-white border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 appearance-none cursor-pointer">
                                            @foreach(\App\Models\SupplierPayment::PAYMENT_METHODS as $val => $lbl)
                                                <option value="{{ $val }}">{{ $lbl }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="space-y-2">
                                        <label class="text-[10px] font-black text-indigo-400 uppercase tracking-widest">Cuenta de Egreso *</label>
                                        <select wire:model="paymentAccountId"
                                            class="w-full bg-white border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 appearance-none cursor-pointer">
                                            <option value="">— Seleccionar —</option>
                                            @foreach($financeAccounts as $acc)
                                                <option value="{{ $acc['id'] }}">{{ $acc['name'] }} ({{ $acc['currency'] }})</option>
                                            @endforeach
                                        </select>
                                        @error('paymentAccountId') <p class="text-[10px] text-rose-500 font-bold">{{ $message }}</p> @enderror
                                    </div>

                                    <div class="space-y-2">
                                        <label class="text-[10px] font-black text-indigo-400 uppercase tracking-widest">Referencia / Comprobante</label>
                                        <input wire:model="paymentReference" type="text" placeholder="N° Rastreo, Cheque..."
                                            class="w-full bg-white border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10">
                                    </div>
                                </div>

                                <div class="flex items-center gap-3 pt-4">
                                    <button wire:click="savePayment" wire:loading.attr="disabled"
                                        class="px-8 py-4 bg-indigo-600 text-white rounded-2xl text-xs font-black uppercase tracking-[0.2em] shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 hover:scale-[1.02] active:scale-[0.98] transition-all">
                                        <span wire:loading.remove wire:target="savePayment">Efectuar Pago</span>
                                        <span wire:loading wire:target="savePayment">Procesando...</span>
                                    </button>
                                    <button wire:click="$set('showPaymentForm', false)"
                                        class="px-6 py-4 text-xs font-black uppercase tracking-widest text-slate-400 hover:text-slate-600 transition-colors">
                                        Cancelar
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Historial de Pagos --}}
                    <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                        <div class="p-6 lg:p-8 space-y-6">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-5">
                                <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest">Historial de Transacciones</h3>
                                @if(!$showPaymentForm && !in_array($invoice->status, ['paid','cancelled']))
                                    <button wire:click="openPaymentForm"
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-slate-50 text-indigo-600 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-50 transition-all">
                                        + Agregar Pago
                                    </button>
                                @endif
                            </div>

                            @if($invoice->payments->isEmpty())
                                <div class="py-12 text-center">
                                    <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center text-slate-200 mx-auto mb-4">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    </div>
                                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">No se han registrado abonos a esta factura</p>
                                </div>
                            @else
                                <div class="divide-y divide-slate-100 bg-slate-50/50 rounded-2xl border border-slate-100 overflow-hidden">
                                    @foreach($invoice->payments as $pmt)
                                        <div class="p-5 flex items-center justify-between group hover:bg-white transition-all">
                                            <div class="flex items-center gap-4">
                                                <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center text-emerald-500 shadow-sm border border-slate-100 group-hover:border-emerald-100 group-hover:bg-emerald-50 transition-colors">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                </div>
                                                <div>
                                                    <p class="text-xs font-black text-slate-800 font-mono tracking-tight group-hover:text-emerald-700 transition-colors">{{ $pmt->folio }}</p>
                                                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">
                                                        {{ $pmt->paid_at?->format('d/m/Y') }} • {{ \App\Models\SupplierPayment::PAYMENT_METHODS[$pmt->payment_method] ?? $pmt->payment_method }}
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-lg font-black text-slate-900 tracking-tighter">${{ number_format($pmt->amount, 2) }}</p>
                                                <p class="text-[9px] text-slate-400 font-bold uppercase">{{ $pmt->createdBy->name ?? 'Sistema' }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif

                {{-- CONTENIDO: CFDI --}}
                @if($activeTab === 'cfdi')
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm p-8 space-y-8 animate-in fade-in zoom-in-95">
                    <div class="flex items-center gap-3 border-b border-slate-100 pb-5">
                        <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest">Archivos Digitales (CFDI)</h3>
                    </div>

                    @if($invoice->xml_path || $invoice->pdf_path)
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            @if($invoice->xml_path)
                                <div class="group p-6 bg-slate-50 rounded-[2rem] border border-slate-200/60 hover:bg-white hover:border-indigo-200 transition-all flex flex-col items-center text-center gap-4">
                                    <div class="w-14 h-14 rounded-2xl bg-white flex items-center justify-center text-indigo-500 shadow-sm border border-slate-100 group-hover:shadow-indigo-100 transition-all">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-black text-slate-800 uppercase tracking-widest">Comprobante XML</p>
                                        <p class="text-[9px] text-slate-400 font-bold mt-1 uppercase">Datos Estructurados SAT</p>
                                    </div>
                                    <a href="{{ route('purchases.invoices.download', ['invoice' => $invoice->id, 'type' => 'xml']) }}"
                                        class="w-full py-2.5 bg-white border border-slate-200 rounded-xl text-[10px] font-black uppercase tracking-widest text-indigo-600 hover:bg-indigo-600 hover:text-white hover:border-indigo-600 transition-all">
                                        Descargar
                                    </a>
                                </div>
                            @endif

                            @if($invoice->pdf_path)
                                <div class="group p-6 bg-slate-50 rounded-[2rem] border border-slate-200/60 hover:bg-white hover:border-rose-200 transition-all flex flex-col items-center text-center gap-4">
                                    <div class="w-14 h-14 rounded-2xl bg-white flex items-center justify-center text-rose-500 shadow-sm border border-slate-100 group-hover:shadow-rose-100 transition-all">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-black text-slate-800 uppercase tracking-widest">Representación PDF</p>
                                        <p class="text-[9px] text-slate-400 font-bold mt-1 uppercase">Visualización de Factura</p>
                                    </div>
                                    <a href="{{ route('purchases.invoices.download', ['invoice' => $invoice->id, 'type' => 'pdf']) }}"
                                        class="w-full py-2.5 bg-white border border-slate-200 rounded-xl text-[10px] font-black uppercase tracking-widest text-rose-600 hover:bg-rose-600 hover:text-white hover:border-rose-600 transition-all">
                                        Descargar
                                    </a>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="py-12 text-center bg-slate-50 rounded-[2rem] border border-dashed border-slate-200">
                            <p class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em]">Sin archivos adjuntos</p>
                        </div>
                    @endif
                </div>
                @endif
            </div>

            {{-- ── PANEL LATERAL (DATOS MAESTROS) ────────────────────────────── --}}
            <div class="space-y-8">
                {{-- Bloque de Datos --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="p-6 lg:p-8 space-y-6">
                        <h3 class="text-xs font-black text-slate-800 uppercase tracking-widest border-b border-slate-100 pb-4">Detalles del Documento</h3>
                        
                        <div class="space-y-4">
                            <div class="flex flex-col gap-1">
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Folio Sistema</span>
                                <span class="text-sm font-black text-indigo-600 font-mono tracking-tight">{{ $invoice->folio }}</span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">N° Factura Proveedor</span>
                                <span class="text-sm font-bold text-slate-800">{{ $invoice->supplier_invoice_number }}</span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Proveedor</span>
                                <span class="text-sm font-bold text-slate-700">{{ $invoice->supplier->name ?? '—' }}</span>
                            </div>
                            @if($invoice->order)
                            <div class="flex flex-col gap-1">
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Orden Asociada</span>
                                <a wire:navigate href="{{ route('purchases.orders.show', $invoice->order) }}"
                                   class="text-xs font-black text-indigo-500 hover:underline flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.828a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                    {{ $invoice->order->folio }}
                                </a>
                            </div>
                            @endif
                            <div class="grid grid-cols-2 gap-4">
                                <div class="flex flex-col gap-1">
                                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Emisión</span>
                                    <span class="text-xs font-bold text-slate-700">{{ $invoice->issued_at?->format('d/m/Y') ?? '—' }}</span>
                                </div>
                                <div class="flex flex-col gap-1 text-right">
                                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Recepción</span>
                                    <span class="text-xs font-bold text-slate-700">{{ $invoice->received_at?->format('d/m/Y') ?? 'Manual' }}</span>
                                </div>
                            </div>
                        </div>

                        @if($invoice->notes)
                            <div class="pt-6 border-t border-slate-100">
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-2">Notas de Auditoría</span>
                                <p class="text-[11px] text-slate-500 leading-relaxed font-medium bg-slate-50 p-4 rounded-2xl border border-slate-100">{{ $invoice->notes }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Action Cards --}}
                @if(!in_array($invoice->status, ['paid','cancelled']))
                <div class="space-y-3">
                    @can('create purchases')
                        <a wire:navigate href="{{ route('purchases.credit-notes.create') }}?invoice={{ $invoice->id }}"
                           class="group w-full flex items-center justify-between p-5 bg-white rounded-[2rem] border border-slate-200/60 shadow-sm hover:border-amber-200 hover:shadow-amber-100/50 transition-all">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-500 group-hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                </div>
                                <span class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-600 group-hover:text-amber-700">Nota de Crédito</span>
                            </div>
                            <svg class="w-4 h-4 text-slate-300 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    @endcan
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ── MODAL CANCELAR (ESTILO INVENTARIOS) ───────────────────────────── --}}
    @if($showCancelModal)
    <div class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" wire:click="$set('showCancelModal', false)"></div>
        <div class="relative bg-white rounded-[2.5rem] shadow-2xl p-10 w-full max-w-md mx-auto border border-slate-200 animate-in zoom-in-95 duration-200">
            <div class="w-16 h-16 rounded-3xl bg-rose-50 flex items-center justify-center text-rose-500 mb-6 mx-auto">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <h3 class="text-2xl font-black text-slate-800 text-center tracking-tight mb-2">¿Cancelar Factura?</h3>
            <p class="text-sm text-slate-500 text-center font-medium leading-relaxed mb-8">
                Confirmas la cancelación de la factura <strong class="text-slate-800">{{ $invoice->folio }}</strong>. El saldo se revertirá y el historial quedará marcado. Esta acción es irreversible.
            </p>
            <div class="grid grid-cols-2 gap-4">
                <button wire:click="$set('showCancelModal', false)"
                        class="py-4 px-6 bg-slate-50 text-slate-500 rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-slate-100 transition-all">
                    Volver
                </button>
                <button wire:click="cancel"
                        class="py-4 px-6 bg-rose-600 text-white rounded-2xl text-xs font-black uppercase tracking-widest shadow-lg shadow-rose-500/25 hover:bg-rose-700 hover:scale-[1.02] active:scale-[0.98] transition-all">
                    Confirmar
                </button>
            </div>
        </div>
    </div>
    @endif
</div>

<div class="min-h-screen bg-slate-50/50 -m-4 lg:-m-6">
    {{-- ── STICKY HEADER ────────────────────────────────────────────────── --}}
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 max-w-full mx-auto">
            <div class="flex items-center gap-3 min-w-0">
                <a wire:navigate href="{{ route('finance.transactions.index') }}" 
                   class="group flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-100 hover:shadow-sm transition-all duration-200">
                    <svg class="w-5 h-5 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl font-bold text-slate-800 truncate">
                        {{ $transaction?->exists ? 'Editar Transacción' : 'Nueva Transacción Financiera' }}
                    </h1>
                    <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">
                        {{ $transaction?->exists ? 'Folio: ' . $transaction->folio : 'Registro manual de ingresos, egresos o traspasos' }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <a wire:navigate href="{{ route('finance.transactions.index') }}"
                    class="hidden sm:inline-flex px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 transition-colors">
                    Cancelar
                </a>
                <button type="button" wire:click="save"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:scale-[1.02] active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>{{ $transaction?->exists ? 'Guardar cambios' : 'Registrar transacción' }}</span>
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto p-4 sm:p-6 lg:p-8">
        <form wire:submit="save" class="grid grid-cols-1 md:grid-cols-12 gap-6 lg:gap-8">

            {{-- ── COLUMNA IZQUIERDA: Detalle y Cuentas (7 cols) ───────────── --}}
            <div class="md:col-span-7 space-y-6 lg:space-y-8">
                
                {{-- Card: Clasificación --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/30">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Naturaleza de la Operación</h3>
                    </div>
                    <div class="p-6 lg:p-8 space-y-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Tipo de movimiento *</label>
                                <select wire:model.live="type"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-bold text-slate-700">
                                    <option value="ingreso">💰 Ingreso (Entrada)</option>
                                    <option value="egreso">💸 Egreso (Salida)</option>
                                    <option value="transferencia">🔁 Transferencia (Traspaso)</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Categoría *</label>
                                <select wire:model="category"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 text-sm">
                                    <option value="venta">Venta / Servicio</option>
                                    <option value="compra">Compra / Proveedor</option>
                                    <option value="nomina">Nómina / Salarios</option>
                                    <option value="impuesto">Impuestos / Derechos</option>
                                    <option value="prestamo">Préstamo / Crédito</option>
                                    <option value="inversion">Inversión / Activos</option>
                                    <option value="proyecto">Gasto de Proyecto</option>
                                    <option value="otro">Otro concepto</option>
                                </select>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Concepto o Glosa *</label>
                            <input wire:model="concept" type="text" placeholder="Ej. Pago de factura A-102 o Traspaso a Caja Chica"
                                class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-medium">
                            @error('concept') <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider ml-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-4 border-t border-slate-100">
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">
                                    {{ $type === 'transferencia' ? 'Cuenta origen *' : 'Cuenta financiera *' }}
                                </label>
                                <select wire:model="account_id"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-bold text-slate-600 text-sm">
                                    <option value="">— Seleccionar cuenta —</option>
                                    @foreach($accounts as $account)
                                        <option value="{{ $account->id }}">{{ $account->name }} ({{ $account->currency }})</option>
                                    @endforeach
                                </select>
                                @error('account_id') <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider ml-1">{{ $message }}</p> @enderror
                            </div>

                            @if($type === 'transferencia')
                                <div class="space-y-2 animate-in fade-in slide-in-from-top-2 duration-300">
                                    <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Cuenta destino *</label>
                                    <select wire:model="transfer_to_account_id"
                                        class="w-full px-4 py-3 rounded-2xl border-indigo-200 bg-indigo-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-bold text-indigo-700 text-sm">
                                        <option value="">— Seleccionar destino —</option>
                                        @foreach($accounts as $account)
                                            <option value="{{ $account->id }}">{{ $account->name }} ({{ $account->currency }})</option>
                                        @endforeach
                                    </select>
                                    @error('transfer_to_account_id') <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider ml-1">{{ $message }}</p> @enderror
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Card: Referencias --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/30">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Referencias y Vinculación</h3>
                    </div>
                    <div class="p-6 lg:p-8 space-y-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Referencia externa</label>
                                <input wire:model="reference" type="text" placeholder="Folio, factura, cheque..."
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Proyecto relacionado</label>
                                <select wire:model="project_id"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200">
                                    <option value="">— Sin proyecto relacionado —</option>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->id }}">{{ $project->code }} – {{ $project->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Licitación relacionada</label>
                                <select wire:model="tender_id"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200">
                                    <option value="">— Sin licitación —</option>
                                    @foreach($tenders as $t)
                                        <option value="{{ $t->id }}">{{ $t->folio }} — {{ $t->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ── COLUMNA DERECHA: Importes y Fechas (5 cols) ────────────── --}}
            <div class="md:col-span-5 space-y-6 lg:space-y-8">
                
                {{-- Card: Importe --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="p-6 lg:p-8 space-y-6">
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Monto de la transacción *</label>
                            <div class="relative group">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold group-focus-within:text-indigo-500">$</span>
                                <input wire:model="amount" type="number" step="0.01" min="0.01" placeholder="0.00"
                                    class="w-full pl-8 pr-4 py-4 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-black text-xl text-slate-700">
                            </div>
                            @error('amount') <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider ml-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Moneda</label>
                                <select wire:model="currency"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-bold text-slate-600">
                                    <option value="MXN">MXN</option>
                                    <option value="USD">USD</option>
                                    <option value="EUR">EUR</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Tipo cambio</label>
                                <input wire:model="exchange_rate" type="number" step="0.000001" min="0"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-mono text-xs">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card: Fecha y Estatus --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="p-6 lg:p-8 space-y-6">
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Fecha de operación *</label>
                            <input wire:model="transaction_date" type="date"
                                class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-bold">
                            @error('transaction_date') <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider ml-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Estado actual</label>
                            <select wire:model="status"
                                class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-black uppercase text-[10px] tracking-widest {{ $status === 'confirmado' ? 'text-emerald-600' : ($status === 'pendiente' ? 'text-amber-600' : 'text-red-600') }}">
                                <option value="confirmado">✅ Confirmado</option>
                                <option value="pendiente">⏳ Pendiente</option>
                                <option value="cancelado">❌ Cancelado</option>
                            </select>
                        </div>

                        <div class="space-y-2 pt-4 border-t border-slate-100">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Notas contables</label>
                            <textarea wire:model="notes" rows="4" placeholder="Detalles internos..."
                                class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 resize-none text-sm"></textarea>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>

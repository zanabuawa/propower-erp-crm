<div class="min-h-screen bg-slate-50/50 -m-4 lg:-m-6">
    {{-- ── STICKY HEADER ────────────────────────────────────────────────── --}}
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 max-w-full mx-auto">
            <div class="flex items-center gap-3 min-w-0">
                <a wire:navigate href="{{ route('assets.transfers.index') }}" 
                   class="group flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-100 hover:shadow-sm transition-all duration-200">
                    <svg class="w-5 h-5 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl font-bold text-slate-800 truncate">Nueva Transferencia</h1>
                    <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">Reubicación y cambio de custodia de activos</p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <a wire:navigate href="{{ route('assets.transfers.index') }}"
                    class="hidden sm:inline-flex px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 transition-colors">
                    Cancelar
                </a>
                <button type="button" wire:click="save"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:scale-[1.02] active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Registrar transferencia</span>
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto p-4 sm:p-6 lg:p-8">
        <form wire:submit="save" class="grid grid-cols-1 md:grid-cols-12 gap-6 lg:gap-8">

            {{-- ── COLUMNA IZQUIERDA: Origen y Destino (7 cols) ────────────── --}}
            <div class="md:col-span-7 space-y-6 lg:space-y-8">
                
                {{-- Card: Selección de Activo --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/30 flex items-center justify-between">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Activo a Transferir</h3>
                        @if($selectedAsset)
                            <span class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-600 text-[10px] font-bold uppercase tracking-wider">Seleccionado</span>
                        @endif
                    </div>
                    <div class="p-6 lg:p-8">
                        @if(!$selectedAsset)
                            <div class="relative group">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-indigo-500 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                </span>
                                <input wire:model.live.debounce.300ms="assetSearch" type="text"
                                    placeholder="Buscar por nombre, folio o S/N..."
                                    class="w-full pl-12 pr-4 py-4 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-medium">
                                
                                @if(count($assetResults) > 0)
                                    <div class="absolute top-full left-0 right-0 bg-white border border-slate-200 rounded-[2rem] shadow-2xl z-50 mt-2 overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                                        @foreach($assetResults as $result)
                                            <button type="button" wire:click="selectAsset({{ $result['id'] }})"
                                                class="w-full text-left px-6 py-4 hover:bg-indigo-50 transition-colors flex items-center justify-between border-b border-slate-50 last:border-0 group/item">
                                                <div class="min-w-0">
                                                    <p class="text-sm font-bold text-slate-800 group-hover/item:text-indigo-600 truncate">{{ $result['name'] }}</p>
                                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ $result['folio'] }} • {{ $result['serial_number'] ?? 'SIN S/N' }}</p>
                                                </div>
                                                <div class="text-right shrink-0">
                                                    <span class="text-[10px] font-black text-slate-400 group-hover/item:text-indigo-400">{{ $result['branch']['name'] ?? 'ALMACÉN CENTRAL' }}</span>
                                                </div>
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            @error('asset_id') <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider mt-2 ml-2">{{ $message }}</p> @enderror
                        @else
                            <div class="bg-indigo-50/50 border border-indigo-100 rounded-[2rem] p-6 flex items-start justify-between gap-6 animate-in slide-in-from-top-4 duration-300">
                                <div class="space-y-4 min-w-0">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 rounded-2xl bg-white shadow-sm flex items-center justify-center text-indigo-600 shrink-0">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                        </div>
                                        <div class="min-w-0">
                                            <h4 class="text-base font-black text-slate-800 truncate">{{ $selectedAsset->name }}</h4>
                                            <p class="text-[11px] font-bold text-indigo-500 uppercase tracking-widest">{{ $selectedAsset->folio }}</p>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4 pt-2 border-t border-indigo-100/50">
                                        <div>
                                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-tighter">Ubicación actual</p>
                                            <p class="text-xs font-bold text-slate-600 truncate">{{ $selectedAsset->branch?->name ?? 'Sin asignar' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-tighter">Responsable actual</p>
                                            <p class="text-xs font-bold text-slate-600 truncate">{{ $selectedAsset->assignedUser?->name ?? 'Sin custodio' }}</p>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" wire:click="clearAsset" class="w-10 h-10 rounded-2xl bg-white border border-indigo-100 flex items-center justify-center text-slate-400 hover:text-red-500 hover:border-red-100 transition-all shadow-sm shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Card: Destino --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/30">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Destino de la Transferencia</h3>
                    </div>
                    <div class="p-6 lg:p-8 space-y-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Sucursal destino</label>
                                <select wire:model.live="to_branch_id"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-bold text-slate-700">
                                    <option value="">— Sin sucursal (Almacén Central) —</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Almacén / Área destino</label>
                                <select wire:model="to_warehouse_id"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-bold text-slate-700"
                                    @if(!$to_branch_id) disabled @endif>
                                    <option value="">— Sin almacén específico —</option>
                                    @foreach($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Nuevo custodio (Usuario responsable)</label>
                            <select wire:model="to_user_id"
                                class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-bold text-slate-700">
                                <option value="">— Seguirá sin asignar o mismo usuario —</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ── COLUMNA DERECHA: Datos del Traspaso (5 cols) ───────────── --}}
            <div class="md:col-span-5 space-y-6 lg:space-y-8">
                
                {{-- Card: Cronograma --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="p-6 lg:p-8 space-y-6">
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Fecha y Hora de entrega *</label>
                            <input wire:model="transferred_at" type="datetime-local"
                                class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-black text-slate-700">
                            @error('transferred_at') <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider mt-1 ml-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- Card: Justificación --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/30">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Justificación</h3>
                    </div>
                    <div class="p-6 lg:p-8 space-y-6">
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Motivo del movimiento</label>
                            <input wire:model="reason" type="text" placeholder="Ej. Reubicación de oficina, mantenimiento..."
                                class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200">
                        </div>

                        <div class="space-y-2 pt-4 border-t border-slate-100">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Notas adicionales</label>
                            <textarea wire:model="notes" rows="6" placeholder="Detalles sobre el estado del activo al entregar..."
                                class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 resize-none text-sm"></textarea>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>

<div class="min-h-screen bg-slate-50/50 -m-4 lg:-m-6">
    {{-- ── STICKY HEADER ────────────────────────────────────────────────── --}}
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 max-w-full mx-auto">
            <div class="flex items-center gap-3 min-w-0">
                <a wire:navigate href="{{ route('assets.maintenance.index') }}" 
                   class="group flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-100 hover:shadow-sm transition-all duration-200">
                    <svg class="w-5 h-5 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl font-bold text-slate-800 truncate">
                        {{ $maintenance?->exists ? 'Editar Mantenimiento' : 'Nuevo Registro de Mantenimiento' }}
                    </h1>
                    <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">
                        {{ $maintenance?->exists ? 'Folio: ' . $maintenance->folio : 'Seguimiento preventivo y correctivo de activos' }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <a wire:navigate href="{{ route('assets.maintenance.index') }}"
                    class="hidden sm:inline-flex px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 transition-colors">
                    Cancelar
                </a>
                <button type="button" wire:click="save"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:scale-[1.02] active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>{{ $maintenance?->exists ? 'Guardar cambios' : 'Programar mantenimiento' }}</span>
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-full mx-auto p-4 sm:p-6 lg:p-8">
        <form wire:submit="save" class="grid grid-cols-1 xl:grid-cols-12 gap-6 lg:gap-8">

            {{-- ── COLUMNA IZQUIERDA: Detalle y Técnico (8 cols) ────────────── --}}
            <div class="xl:col-span-8 space-y-6 lg:space-y-8">
                
                {{-- Card: Datos Generales --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/30 flex items-center justify-between">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Información General</h3>
                        <span class="px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-600 text-[10px] font-bold uppercase tracking-wider">Orden de Servicio</span>
                    </div>
                    <div class="p-6 lg:p-8 space-y-6">
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Activo Fijo relacionado *</label>
                            <select wire:model="fixed_asset_id"
                                class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-bold text-slate-700">
                                <option value="">— Seleccionar activo —</option>
                                @foreach($assets as $asset)
                                    <option value="{{ $asset->id }}">{{ $asset->folio }} — {{ $asset->name }}</option>
                                @endforeach
                            </select>
                            @error('fixed_asset_id') <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider ml-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Tipo de mantenimiento *</label>
                                <select wire:model="type"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200">
                                    @foreach(\App\Models\AssetMaintenance::TYPES as $k => $v)
                                        <option value="{{ $k }}">{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Estado actual *</label>
                                <select wire:model="status"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-bold {{ $status === 'completed' ? 'text-emerald-600' : 'text-amber-600' }}">
                                    @foreach(\App\Models\AssetMaintenance::STATUSES as $k => $v)
                                        <option value="{{ $k }}">{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card: Técnico Responsable --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/30 flex items-center justify-between">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Responsable del Trabajo</h3>
                        <div class="flex gap-2 p-1 bg-slate-100 rounded-xl">
                            <button type="button" wire:click="$set('technicianType', 'internal')" 
                                class="px-3 py-1 rounded-lg text-[9px] font-black uppercase transition-all {{ $technicianType === 'internal' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-400 hover:text-slate-600' }}">
                                Interno
                            </button>
                            <button type="button" wire:click="$set('technicianType', 'external')" 
                                class="px-3 py-1 rounded-lg text-[9px] font-black uppercase transition-all {{ $technicianType === 'external' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-400 hover:text-slate-600' }}">
                                Externo
                            </button>
                        </div>
                    </div>
                    <div class="p-6 lg:p-8 space-y-6">
                        @if($technicianType === 'internal')
                            <div class="space-y-2 animate-in fade-in duration-300">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Usuario asignado</label>
                                <select wire:model="technician_user_id"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-bold text-slate-700">
                                    <option value="">— Sin asignar —</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 animate-in fade-in duration-300">
                                <div class="space-y-2">
                                    <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Nombre del técnico</label>
                                    <input wire:model="technician_name" type="text" placeholder="Ej. Juan Pérez"
                                        class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Empresa / Proveedor</label>
                                    <input wire:model="provider" type="text" placeholder="Ej. Soporte Industrial ACME"
                                        class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200">
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Card: Detalle del Trabajo --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/30">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Ejecución y Resultados</h3>
                    </div>
                    <div class="p-6 lg:p-8 space-y-6">
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Descripción del trabajo realizado</label>
                            <textarea wire:model="work_performed" rows="4" placeholder="Detalle los servicios ejecutados..."
                                class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 resize-none text-sm"></textarea>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Piezas / Refacciones</label>
                                <textarea wire:model="parts_replaced" rows="3" placeholder="Ej. Filtro de aceite, Bujías..."
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 resize-none text-sm"></textarea>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Observaciones</label>
                                <textarea wire:model="observations" rows="3" placeholder="Recomendaciones futuras..."
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 resize-none text-sm"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ── COLUMNA DERECHA: Fechas y Costos (4 cols) ──────────────── --}}
            <div class="xl:col-span-4 space-y-6 lg:space-y-8">
                
                {{-- Card: Programación --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/30">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Cronograma</h3>
                    </div>
                    <div class="p-6 lg:p-8 space-y-6">
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Fecha programada *</label>
                            <input wire:model="scheduled_date" type="date"
                                class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-bold">
                            @error('scheduled_date') <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider ml-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Fecha de finalización</label>
                            <input wire:model="completed_date" type="date"
                                class="w-full px-4 py-3 rounded-2xl border-emerald-200 bg-emerald-50/30 focus:bg-white focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/5 transition-all duration-200 font-bold text-emerald-700">
                        </div>
                    </div>
                </div>

                {{-- Card: Costos --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="p-6 lg:p-8 space-y-4">
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Costo del servicio</label>
                            <div class="relative group">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold group-focus-within:text-indigo-500">$</span>
                                <input wire:model="cost" type="number" step="0.01" min="0" placeholder="0.00"
                                    class="w-full pl-8 pr-4 py-4 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-black text-xl text-slate-700">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card: Próximo Mantenimiento (Solo preventivo) --}}
                @if($type === 'preventive')
                    <div class="bg-indigo-600 rounded-3xl shadow-lg shadow-indigo-500/20 overflow-hidden animate-in zoom-in-95 duration-500">
                        <div class="px-6 py-5 border-b border-indigo-500 bg-white/5">
                            <h3 class="text-xs font-bold text-indigo-100 uppercase tracking-widest">Recurrencia</h3>
                        </div>
                        <div class="p-6 lg:p-8 space-y-6">
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-indigo-200 uppercase tracking-widest ml-1">Intervalo (meses)</label>
                                <input wire:model="interval_months" type="number" min="1" max="60" placeholder="Ej. 6"
                                    class="w-full px-4 py-3 rounded-2xl border-white/20 bg-white/10 text-white placeholder-indigo-300 focus:bg-white/20 focus:border-white focus:ring-4 focus:ring-white/10 transition-all duration-200 font-bold">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-indigo-200 uppercase tracking-widest ml-1">Siguiente servicio sugerido</label>
                                <input wire:model="next_scheduled_date" type="date"
                                    class="w-full px-4 py-3 rounded-2xl border-white/20 bg-white/10 text-white focus:bg-white/20 focus:border-white focus:ring-4 focus:ring-white/10 transition-all duration-200 font-bold">
                            </div>
                        </div>
                    </div>
                @endif

            </div>
        </form>
    </div>
</div>

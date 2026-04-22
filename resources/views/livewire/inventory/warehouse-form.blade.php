<div class="min-h-screen bg-slate-50/50 -m-4 lg:-m-6">
    {{-- ── STICKY HEADER ────────────────────────────────────────────────── --}}
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 max-w-full mx-auto">
            <div class="flex items-center gap-3 min-w-0">
                <a wire:navigate href="{{ route('inventory.warehouses.index') }}" 
                   class="group flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-100 hover:shadow-sm transition-all duration-200">
                    <svg class="w-5 h-5 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl font-bold text-slate-800 truncate">
                        {{ $warehouse?->exists ? 'Editar almacén' : 'Nuevo almacén' }}
                    </h1>
                    <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">
                        {{ $warehouse?->exists ? 'ID: ' . $warehouse->id : 'Registro de ubicación de stock' }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <a wire:navigate href="{{ route('inventory.warehouses.index') }}"
                    class="hidden sm:inline-flex px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 transition-colors">
                    Cancelar
                </a>
                <button type="button" wire:click="save"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:scale-[1.02] active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>{{ $warehouse?->exists ? 'Guardar cambios' : 'Crear almacén' }}</span>
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-full mx-auto p-4 sm:p-6 lg:p-8">
        <form wire:submit="save" class="w-full space-y-6 lg:space-y-8">
            
            <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                <div class="p-6 lg:p-10 space-y-10">
                    
                    {{-- Nombre (Ocupa todo el ancho superior) --}}
                    <div class="relative group">
                        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-2 group-focus-within:text-indigo-500 transition-colors">Nombre del almacén *</label>
                        <input wire:model="name" type="text"
                            placeholder="Ej. Bodega Central, Almacén de Refacciones, Silo Norte..."
                            class="w-full bg-slate-50 border-none rounded-2xl px-5 py-5 text-2xl font-black text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500/20 transition-all">
                        @error('name') <p class="text-xs text-rose-500 mt-2 font-medium flex items-center gap-1"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                        {{-- Sucursal --}}
                        <div class="space-y-3">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Sucursal de operación *</label>
                            <div class="relative">
                                <select wire:model="branch_id"
                                    class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500/20 cursor-pointer appearance-none pr-10" style="-webkit-appearance: none; -moz-appearance: none;">
                                    <option value="">— Seleccionar sucursal —</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}">
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                            @error('branch_id') <p class="text-[10px] text-rose-500 font-medium">{{ $message }}</p> @enderror
                        </div>

                        {{-- Código --}}
                        <div class="space-y-3">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Código interno</label>
                            <input wire:model="code" type="text" placeholder="Ej. ALM-001"
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-mono font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500/20">
                        </div>

                        {{-- Ubicación --}}
                        <div class="space-y-3">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Ubicación específica</label>
                            <input wire:model="location" type="text" placeholder="Ej. Nave A, Estantería 4, Pasillo 2..."
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500/20">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pt-8 border-t border-slate-50">
                        {{-- Toggle: Activo --}}
                        <div class="flex items-center justify-between p-6 bg-slate-50 rounded-[2rem] border border-slate-100/50">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-white flex items-center justify-center text-emerald-500 shadow-sm border border-slate-100">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-black text-slate-800">Almacén activo</p>
                                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mt-0.5">Habilitado para entrada y salida de stock</p>
                                </div>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input wire:model="is_active" type="checkbox" class="sr-only peer">
                                <div class="w-14 h-7 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-emerald-500 after:content-[''] after:absolute after:top-[4px] after:left-[4px] after:bg-white after:rounded-full after:h-6 after:w-6 after:transition-all"></div>
                            </label>
                        </div>

                        {{-- Toggle: Defectuoso --}}
                        <div class="flex items-center justify-between p-6 bg-slate-50 rounded-[2rem] border border-slate-100/50">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-white flex items-center justify-center text-amber-500 shadow-sm border border-slate-100">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-black text-slate-800">Almacén de mermas</p>
                                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mt-0.5">Destino automático para productos dañados</p>
                                </div>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input wire:model="is_defective" type="checkbox" class="sr-only peer">
                                <div class="w-14 h-7 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-amber-500 after:content-[''] after:absolute after:top-[4px] after:left-[4px] after:bg-white after:rounded-full after:h-6 after:w-6 after:transition-all"></div>
                            </label>
                        </div>
                    </div>

                    @if($is_defective)
                    <div class="bg-amber-50/50 rounded-[2rem] p-6 border border-amber-100 flex items-start gap-4 animate-in fade-in slide-in-from-top-4">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center text-amber-600 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-amber-900 mb-1">Configuración de Flujo Logístico</p>
                            <p class="text-[11px] text-amber-800/80 leading-relaxed font-medium">
                                Al marcar este almacén como de mermas, el sistema habilitará reglas automáticas para que las devoluciones por daño y las recepciones de proveedores con defectos se redirijan aquí sin intervención manual.
                            </p>
                        </div>
                    </div>
                    @endif

                </div>
            </div>
        </form>
    </div>
</div>

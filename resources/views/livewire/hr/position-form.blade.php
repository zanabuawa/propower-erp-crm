<div class="min-h-screen bg-slate-50/50 -m-4 sm:-m-6 lg:-m-8">
    {{-- ── STICKY HEADER ────────────────────────────────────────────────── --}}
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 max-w-full mx-auto">
            <div class="flex items-center gap-3 min-w-0">
                <a wire:navigate href="{{ route('hr.positions.index') }}" 
                   class="group flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-100 hover:shadow-sm transition-all duration-200">
                    <svg class="w-5 h-5 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl font-bold text-slate-800 truncate">
                        {{ $position?->exists ? 'Editar Puesto' : 'Nuevo Puesto Laboral' }}
                    </h1>
                    <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">
                        {{ $position?->exists ? $name : 'Definición de perfil y compensación' }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <a wire:navigate href="{{ route('hr.positions.index') }}"
                    class="hidden sm:inline-flex px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 transition-colors">
                    Cancelar
                </a>
                <button type="button" wire:click="save"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:scale-[1.02] active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>{{ $position?->exists ? 'Guardar cambios' : 'Crear puesto' }}</span>
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8">
        <form wire:submit="save" class="grid grid-cols-1 xl:grid-cols-12 gap-6 lg:gap-8">

            {{-- ── COLUMNA IZQUIERDA: Definición y Responsabilidades (8 cols) ── --}}
            <div class="xl:col-span-8 space-y-6 lg:space-y-8">
                
                {{-- Card: Identificación del Puesto --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/30 flex items-center justify-between">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Identificación</h3>
                        <span class="px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-600 text-[10px] font-bold uppercase tracking-wider">Perfil</span>
                    </div>
                    <div class="p-6 lg:p-8 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="md:col-span-2 space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Nombre del puesto *</label>
                                <input wire:model="name" type="text" placeholder="Ej. Contador Senior, Desarrollador Web..."
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-bold">
                                @error('name') <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider ml-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Código ID</label>
                                <input wire:model="code" type="text" placeholder="Ej. FIN-01"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 uppercase font-mono">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Departamento *</label>
                                <select wire:model="department_id"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-bold text-slate-700">
                                    <option value="">— Seleccionar departamento —</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                                @error('department_id') <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider ml-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Total de plazas autorizadas</label>
                                <div class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-slate-100/60 font-black text-slate-700 text-lg select-none">
                                    {{ $authorized_headcount }}
                                    <span class="text-xs font-medium text-slate-400 ml-1">plazas</span>
                                </div>
                                <p class="text-[10px] text-slate-400 ml-1">Se calcula automáticamente con la distribución por sucursal.</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card: Descripción y Detalles --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/30">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Contenido del Puesto</h3>
                    </div>
                    <div class="p-6 lg:p-8 space-y-6">
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Descripción General</label>
                            <textarea wire:model="description" rows="3" placeholder="Resumen del propósito del puesto..."
                                class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 resize-none text-sm"></textarea>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Responsabilidades</label>
                                <textarea wire:model="responsibilities" rows="6" placeholder="• Tarea 1&#10;• Tarea 2..."
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 resize-none text-sm"></textarea>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Requisitos / Perfil</label>
                                <textarea wire:model="requirements" rows="6" placeholder="• Escolaridad&#10;• Experiencia..."
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 resize-none text-sm"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card: Distribución de plazas por sucursal --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/30 flex items-center justify-between">
                        <div>
                            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Plantilla por sucursal</h3>
                            <p class="text-[10px] text-slate-400 mt-0.5">Define cuántas plazas de este puesto corresponden a cada sucursal. El total se usa para controlar el reclutamiento.</p>
                        </div>
                        <div class="text-right shrink-0 ml-4">
                            <p class="text-2xl font-black text-indigo-600">{{ $authorized_headcount }}</p>
                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Total</p>
                        </div>
                    </div>
                    <div class="divide-y divide-slate-50">
                        @foreach($branches as $branch)
                        @php $filled = \App\Models\HrEmployee::where('position_id', $position?->id ?? 0)->where('branch_id', $branch->id)->whereIn('status', ['active','on_leave'])->count(); @endphp
                        <div class="flex items-center gap-4 px-6 py-4 hover:bg-slate-50/30 transition-colors">
                            <div class="flex items-center gap-3 flex-1 min-w-0">
                                <div class="w-8 h-8 rounded-xl bg-indigo-50 flex items-center justify-center shrink-0">
                                    <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-slate-700 truncate">{{ $branch->name }}</p>
                                    @if($position?->exists && $filled > 0)
                                        <p class="text-[10px] text-slate-400">{{ $filled }} ocupada(s) actualmente</p>
                                    @else
                                        <p class="text-[10px] text-slate-400">Sin empleados asignados</p>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-3 shrink-0">
                                @if($position?->exists && $filled > 0)
                                    <span class="text-xs font-black text-emerald-600 bg-emerald-50 px-2 py-1 rounded-lg">
                                        {{ $filled }}/{{ $branchHeadcounts[$branch->id] ?? 0 }} ocupadas
                                    </span>
                                @endif
                                <div class="flex items-center gap-1">
                                    <button type="button"
                                        wire:click="$set('branchHeadcounts.{{ $branch->id }}', max(0, ($branchHeadcounts[{{ $branch->id }}] ?? 0) - 1))"
                                        class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-600 font-black transition-colors text-lg leading-none">
                                        −
                                    </button>
                                    <input type="number" min="0"
                                        wire:model.live="branchHeadcounts.{{ $branch->id }}"
                                        class="w-16 text-center px-2 py-2 rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/5 transition-all text-sm font-black text-slate-700">
                                    <button type="button"
                                        wire:click="$set('branchHeadcounts.{{ $branch->id }}', ($branchHeadcounts[{{ $branch->id }}] ?? 0) + 1)"
                                        class="w-8 h-8 rounded-xl bg-indigo-50 hover:bg-indigo-100 flex items-center justify-center text-indigo-600 font-black transition-colors text-lg leading-none">
                                        +
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endforeach

                        @if($branches->isEmpty())
                            <div class="px-6 py-8 text-center">
                                <p class="text-sm text-slate-400">No hay sucursales activas configuradas.</p>
                            </div>
                        @endif
                    </div>
                    @if($authorized_headcount > 0)
                    <div class="px-6 py-4 bg-indigo-50/50 border-t border-indigo-100/60 flex items-center justify-between">
                        <p class="text-xs text-indigo-600 font-bold">Total de plazas distribuidas</p>
                        <p class="text-xl font-black text-indigo-700">{{ $authorized_headcount }}</p>
                    </div>
                    @endif
                </div>

            </div>

            {{-- ── COLUMNA DERECHA: Compensación y Estado (4 cols) ─────────── --}}
            <div class="xl:col-span-4 space-y-6 lg:space-y-8">
                
                {{-- Card: Compensación --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/30">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Compensación Sugerida</h3>
                    </div>
                    <div class="p-6 lg:p-8 space-y-6">
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Tipo de pago *</label>
                            <select wire:model="salary_type"
                                class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-bold text-slate-700">
                                @foreach(\App\Models\HrPosition::SALARY_TYPES as $k => $v)
                                    <option value="{{ $k }}">{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Rango Salarial (Mínimo)</label>
                            <div class="relative group">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">$</span>
                                <input wire:model="min_salary" type="number" step="0.01" min="0" placeholder="0.00"
                                    class="w-full pl-8 pr-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-mono">
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Rango Salarial (Máximo)</label>
                            <div class="relative group">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">$</span>
                                <input wire:model="max_salary" type="number" step="0.01" min="0" placeholder="0.00"
                                    class="w-full pl-8 pr-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-mono">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card: Estatus --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="p-6 lg:p-8 space-y-6">
                        <div class="flex items-center justify-between p-4 rounded-2xl bg-emerald-50/50 border border-emerald-100/50">
                            <div>
                                <p class="text-xs font-bold text-slate-700">Estado del Puesto</p>
                                <p class="text-[10px] text-emerald-600 uppercase font-bold tracking-wider">¿Está activo?</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input wire:model="is_active" type="checkbox" class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                            </label>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>

<div class="min-h-screen bg-slate-50/50 -m-4 lg:-m-6">
    {{-- ── STICKY HEADER ────────────────────────────────────────────────── --}}
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 max-w-full mx-auto">
            <div class="flex items-center gap-3 min-w-0">
                <a wire:navigate href="{{ route('finance.budgets.index') }}" 
                   class="group flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-100 hover:shadow-sm transition-all duration-200">
                    <svg class="w-5 h-5 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl font-bold text-slate-800 truncate">
                        {{ $budget?->exists ? 'Editar Presupuesto' : 'Nuevo Presupuesto' }}
                    </h1>
                    <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">
                        {{ $budget?->exists ? $budget->name : 'Planificación de ingresos y egresos' }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <a wire:navigate href="{{ route('finance.budgets.index') }}"
                    class="hidden sm:inline-flex px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 transition-colors">
                    Cancelar
                </a>
                <button type="button" wire:click="save"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:scale-[1.02] active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>{{ $budget?->exists ? 'Guardar cambios' : 'Crear presupuesto' }}</span>
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto p-4 sm:p-6 lg:p-8">
        <form wire:submit="save" class="grid grid-cols-1 md:grid-cols-12 gap-6 lg:gap-8">

            {{-- ── COLUMNA IZQUIERDA: Definición y Periodo (7 cols) ────────── --}}
            <div class="md:col-span-7 space-y-6 lg:space-y-8">
                
                {{-- Card: Datos Generales --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/30">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Identificación del Presupuesto</h3>
                    </div>
                    <div class="p-6 lg:p-8 space-y-6">
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Nombre del presupuesto *</label>
                            <input wire:model="name" type="text" placeholder="Ej. Presupuesto Operativo Q3"
                                class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200">
                            @error('name') <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider ml-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Categoría *</label>
                                <select wire:model="category"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-bold text-slate-700">
                                    <option value="ingresos">💰 Ingresos</option>
                                    <option value="egresos">📉 Egresos</option>
                                    <option value="proyecto">🏗️ Proyecto</option>
                                    <option value="departamento">🏢 Departamento</option>
                                    <option value="otro">📁 Otro</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Estado</label>
                                <select wire:model="status"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-bold text-slate-700">
                                    <option value="borrador">📝 Borrador</option>
                                    <option value="aprobado">✅ Aprobado</option>
                                    <option value="cerrado">🔒 Cerrado</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card: Período --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/30">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Temporalidad</h3>
                    </div>
                    <div class="p-6 lg:p-8 space-y-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Tipo de período *</label>
                                <select wire:model.live="period_type"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200">
                                    <option value="mensual">Mensual</option>
                                    <option value="trimestral">Trimestral</option>
                                    <option value="semestral">Semestral</option>
                                    <option value="anual">Anual</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Año *</label>
                                <input wire:model="year" type="number" min="2000" max="2100"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-bold">
                                @error('year') <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider ml-1">{{ $message }}</p> @enderror
                            </div>
                            @if($period_type !== 'anual')
                                <div class="space-y-2 animate-in fade-in slide-in-from-top-2 duration-300">
                                    <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">
                                        @if($period_type === 'mensual') Mes (1-12)
                                        @elseif($period_type === 'trimestral') Trimestre (1-4)
                                        @else Semestre (1-2)
                                        @endif
                                    </label>
                                    <input wire:model="period_number" type="number" min="1"
                                        max="{{ $period_type === 'mensual' ? 12 : ($period_type === 'trimestral' ? 4 : 2) }}"
                                        class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-bold">
                                    @error('period_number') <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider ml-1">{{ $message }}</p> @enderror
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

            </div>

            {{-- ── COLUMNA DERECHA: Montos y Asignación (5 cols) ───────────── --}}
            <div class="md:col-span-5 space-y-6 lg:space-y-8">
                
                {{-- Card: Monto Planeado --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="p-6 lg:p-8 space-y-6">
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Moneda *</label>
                            <select wire:model="currency"
                                class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-black text-indigo-600">
                                <option value="MXN">Pesos Mexicanos (MXN)</option>
                                <option value="USD">Dólares (USD)</option>
                                <option value="EUR">Euros (EUR)</option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Monto Planeado *</label>
                            <div class="relative group">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold group-focus-within:text-indigo-500">$</span>
                                <input wire:model="amount_planned" type="number" step="0.01" min="0" placeholder="0.00"
                                    class="w-full pl-8 pr-4 py-4 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-black text-xl text-slate-700">
                            </div>
                            @error('amount_planned') <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider ml-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- Card: Asignación --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/30">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Asignación Directa</h3>
                    </div>
                    <div class="p-6 lg:p-8 space-y-6">
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Sucursal</label>
                            <select wire:model="branch_id"
                                class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 text-sm font-bold text-slate-600">
                                <option value="">— Sin sucursal específica —</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Proyecto relacionado</label>
                            <select wire:model="project_id"
                                class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 text-sm font-bold text-slate-600">
                                <option value="">— Sin proyecto relacionado —</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}">{{ $project->code }} – {{ $project->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-2 pt-4 border-t border-slate-100">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Notas de planificación</label>
                            <textarea wire:model="notes" rows="3" placeholder="Justificación o detalles..."
                                class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 resize-none text-sm"></textarea>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>

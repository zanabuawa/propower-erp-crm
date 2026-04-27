<div class="min-h-screen bg-slate-50/50 -m-4 sm:-m-6 lg:-m-8">
    {{-- ── STICKY HEADER ────────────────────────────────────────────────── --}}
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 max-w-full mx-auto">
            <div class="flex items-center gap-3 min-w-0">
                <a wire:navigate href="{{ route('hr.evaluations.index') }}" 
                   class="group flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-100 hover:shadow-sm transition-all duration-200">
                    <svg class="w-5 h-5 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl font-bold text-slate-800 truncate">
                        {{ $evaluation?->exists ? 'Editar Evaluación' : 'Nueva Evaluación' }}
                    </h1>
                    <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">
                        {{ $evaluation?->exists ? 'Empleado: ' . $evaluation->employee->full_name : 'Evaluación de desempeño por competencias' }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <a wire:navigate href="{{ route('hr.evaluations.index') }}"
                    class="hidden sm:inline-flex px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 transition-colors">
                    Cancelar
                </a>
                <button type="button" wire:click="save"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:scale-[1.02] active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>{{ $evaluation?->exists ? 'Guardar cambios' : 'Registrar evaluación' }}</span>
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8">
        <form wire:submit="save" class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8">

            {{-- ── COLUMNA IZQUIERDA: Competencias (7 cols) ────────── --}}
            <div class="lg:col-span-7 space-y-6 lg:space-y-8">
                
                {{-- Card: Datos de la Evaluación --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/30">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Identificación</h3>
                    </div>
                    <div class="p-6 lg:p-8 space-y-6">
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Colaborador a evaluar *</label>
                            <select wire:model="employee_id"
                                class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 text-sm font-bold text-slate-700">
                                <option value="">— Seleccionar empleado —</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Periodo *</label>
                                <input wire:model="period" type="text" placeholder="Ej. 2024-Q1, Anual 2023..."
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-bold uppercase">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Fecha de evaluación *</label>
                                <input wire:model="evaluation_date" type="date"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-bold">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card: Competencias --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/30 flex items-center justify-between">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Calificación por Categoría</h3>
                        <div class="px-3 py-1 bg-indigo-50 text-indigo-600 rounded-lg text-[10px] font-black tracking-widest uppercase">
                            Promedio: {{ round(array_sum($categories) / count($categories), 1) }}%
                        </div>
                    </div>
                    <div class="p-6 lg:p-8 space-y-8">
                        @foreach($categories as $key => $value)
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <label class="text-[11px] font-bold text-slate-600 uppercase tracking-widest ml-1">
                                        {{ __('hr.evaluation.' . $key) }}
                                    </label>
                                    <span class="text-sm font-black text-indigo-600 font-mono">{{ $value }}%</span>
                                </div>
                                <div class="relative h-2 w-full bg-slate-100 rounded-full overflow-hidden">
                                    <div class="absolute inset-y-0 left-0 bg-gradient-to-r from-indigo-500 to-indigo-600 transition-all duration-500" style="width: {{ $value }}%"></div>
                                    <input type="range" wire:model.live="categories.{{ $key }}" min="0" max="100" step="5"
                                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                </div>
                                <div class="flex justify-between px-1">
                                    <span class="text-[9px] font-bold text-slate-400">Insuficiente</span>
                                    <span class="text-[9px] font-bold text-slate-400">Excelente</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ── COLUMNA DERECHA: Feedback (5 cols) ───────────── --}}
            <div class="lg:col-span-5 space-y-6 lg:space-y-8">
                
                {{-- Card: Observaciones --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/30">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Feedback Cualitativo</h3>
                    </div>
                    <div class="p-6 lg:p-8 space-y-6">
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Fortalezas</label>
                            <textarea wire:model="strengths" rows="3" placeholder="Puntos destacados del desempeño..."
                                class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 resize-none text-sm"></textarea>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Áreas de mejora</label>
                            <textarea wire:model="areas_for_improvement" rows="3" placeholder="Oportunidades de crecimiento y desarrollo..."
                                class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 resize-none text-sm"></textarea>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Metas próximo periodo</label>
                            <textarea wire:model="goals_next_period" rows="3" placeholder="Objetivos específicos acordados..."
                                class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 resize-none text-sm"></textarea>
                        </div>
                    </div>
                </div>

                {{-- Card: Estado --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="p-6 lg:p-8 space-y-6">
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Estado de la evaluación</label>
                            <select wire:model="status"
                                class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 text-sm font-bold text-slate-700">
                                <option value="draft">Borrador (Solo evaluador)</option>
                                <option value="submitted">Enviada (Visible al empleado)</option>
                                <option value="completed">Completada (Cerrada)</option>
                            </select>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>

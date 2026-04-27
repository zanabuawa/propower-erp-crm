<div class="min-h-screen bg-slate-50/50 -m-4 sm:-m-6 lg:-m-8">
    {{-- ── STICKY HEADER ────────────────────────────────────────────────── --}}
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 max-w-full mx-auto">
            <div class="flex items-center gap-3 min-w-0">
                <a wire:navigate href="{{ route('hr.leaves.index') }}" 
                   class="group flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-100 hover:shadow-sm transition-all duration-200">
                    <svg class="w-5 h-5 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl font-bold text-slate-800 truncate">
                        {{ $leave?->exists ? 'Editar Solicitud' : 'Nueva Solicitud de Permiso' }}
                    </h1>
                    <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">
                        {{ $leave?->exists ? 'Folio: ' . $leave->id : 'Registro de ausencias, vacaciones e incapacidades' }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <a wire:navigate href="{{ route('hr.leaves.index') }}"
                    class="hidden sm:inline-flex px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 transition-colors">
                    Cancelar
                </a>
                <button type="button" wire:click="save"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:scale-[1.02] active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>{{ $leave?->exists ? 'Actualizar registro' : 'Registrar solicitud' }}</span>
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8">
        <form wire:submit="save" class="grid grid-cols-1 md:grid-cols-12 gap-6 lg:gap-8">

            {{-- ── COLUMNA IZQUIERDA: Detalle y Periodo (7 cols) ────────── --}}
            <div class="md:col-span-7 space-y-6 lg:space-y-8">
                
                {{-- Card: Datos Generales --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/30 flex items-center justify-between">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Información de la Solicitud</h3>
                        <span class="px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-600 text-[10px] font-bold uppercase tracking-wider">Ausentismo</span>
                    </div>
                    <div class="p-6 lg:p-8 space-y-6">
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Colaborador *</label>
                            <select wire:model="employee_id"
                                class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-bold text-slate-700">
                                <option value="">— Seleccionar empleado —</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                                @endforeach
                            </select>
                            @error('employee_id') <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider ml-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Tipo de ausencia *</label>
                            <select wire:model.live="type"
                                class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-bold text-slate-700">
                                @foreach(\App\Models\HrLeave::TYPES as $k => $v)
                                    <option value="{{ $k }}">{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-4 border-t border-slate-100">
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Fecha de inicio *</label>
                                <input wire:model="start_date" type="date"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-bold">
                                @error('start_date') <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider ml-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Fecha de término *</label>
                                <input wire:model="end_date" type="date"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-bold">
                                @error('end_date') <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider ml-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        @if(Str::contains($type, 'imss'))
                            <div class="space-y-2 pt-4 border-t border-slate-100 animate-in fade-in duration-300">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Número de Folio / Certificado IMSS</label>
                                <input wire:model="imss_certificate_number" type="text" placeholder="Ej. AC12345678"
                                    class="w-full px-4 py-3 rounded-2xl border-indigo-200 bg-indigo-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-mono">
                            </div>
                        @endif
                    </div>
                </div>

                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/30">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Motivo y Justificación</h3>
                    </div>
                    <div class="p-6 lg:p-8 space-y-6">
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Motivo detallado</label>
                            <textarea wire:model="reason" rows="4" placeholder="Explicación del permiso..."
                                class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 resize-none text-sm"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── COLUMNA DERECHA: Resumen y Notas (5 cols) ───────────── --}}
            <div class="md:col-span-5 space-y-6 lg:space-y-8">
                
                {{-- Card: Cálculo de Días --}}
                <div class="bg-indigo-600 rounded-3xl shadow-xl shadow-indigo-500/20 overflow-hidden">
                    <div class="p-8 text-center text-white space-y-2">
                        @php
                            $s = \Carbon\Carbon::parse($start_date);
                            $e = \Carbon\Carbon::parse($end_date);
                            $days = 0;
                            if($s && $e && $e->gte($s)) {
                                $current = $s->copy();
                                while ($current->lte($e)) {
                                    if (!$current->isWeekend()) $days++;
                                    $current->addDay();
                                }
                            }
                        @endphp
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-200">Total Días Hábiles</p>
                        <h4 class="text-5xl font-black italic">{{ $days }}</h4>
                        <p class="text-xs font-bold text-indigo-100 pt-2 opacity-60">Se excluyen Sábados y Domingos</p>
                    </div>
                </div>

                {{-- Card: Notas --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/30">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Notas Adicionales</h3>
                    </div>
                    <div class="p-6 lg:p-8">
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Observaciones internas</label>
                            <textarea wire:model="notes" rows="4" placeholder="Solo visible para RH..."
                                class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 resize-none text-sm"></textarea>
                        </div>
                    </div>
                </div>

                {{-- Card: Estatus --}}
                @if($leave?->exists)
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden opacity-80">
                    <div class="p-6 lg:p-8">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Estado Actual</span>
                            <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider {{ \App\Models\HrLeave::STATUS_COLORS[$leave->status] }}">
                                {{ $leave->status_label }}
                            </span>
                        </div>
                    </div>
                </div>
                @endif

            </div>
        </form>
    </div>
</div>

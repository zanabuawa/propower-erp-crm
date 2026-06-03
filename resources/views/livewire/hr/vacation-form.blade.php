<div class="min-h-screen bg-slate-50/50 -m-4 sm:-m-6 lg:-m-8">
    {{-- ── STICKY HEADER ────────────────────────────────────────────────── --}}
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 max-w-full mx-auto">
            <div class="flex items-center gap-3 min-w-0">
                <a wire:navigate href="{{ route('hr.vacations.index') }}"
                    class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-500 shrink-0 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <div class="w-9 h-9 rounded-xl bg-teal-600 flex items-center justify-center text-white shrink-0 shadow-lg shadow-teal-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl font-bold text-slate-800 truncate">
                        {{ $leave && $leave->exists ? 'Editar solicitud' : 'Nueva solicitud de vacaciones' }}
                    </h1>
                    <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">Recursos Humanos · Vacaciones</p>
                </div>
            </div>
            <button wire:click="save"
                class="inline-flex items-center gap-2 bg-gradient-to-r from-teal-600 to-teal-700 hover:from-teal-700 hover:to-teal-800 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all duration-200 shadow-lg shadow-teal-500/25 hover:scale-[1.02] active:scale-[0.98]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                <span>{{ $leave && $leave->exists ? 'Guardar cambios' : 'Registrar solicitud' }}</span>
            </button>
        </div>
    </div>

    <div class="max-w-3xl mx-auto p-4 sm:p-6 lg:p-8 space-y-6">

        @if($errors->any())
            <div class="p-4 bg-rose-50 border border-rose-100 rounded-2xl">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li class="text-sm text-rose-700 font-medium">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Empleado --}}
        <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm p-6 space-y-4">
            <h2 class="text-sm font-black text-slate-700 uppercase tracking-widest">Colaborador</h2>

            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Empleado *</label>
                <select wire:model.live="employee_id"
                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-teal-500 focus:ring-4 focus:ring-teal-500/5 transition-all duration-200 text-sm font-bold text-slate-700 @error('employee_id') border-rose-300 bg-rose-50/30 @enderror">
                    <option value="">-- Seleccionar empleado --</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->last_name }} {{ $emp->first_name }}</option>
                    @endforeach
                </select>
                @error('employee_id')
                    <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            {{-- Balance de días --}}
            @if($balance)
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-2">
                    <div class="bg-slate-50 rounded-2xl p-3 text-center">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Ganados</p>
                        <p class="text-2xl font-black text-slate-700">{{ $balance['earned'] }}</p>
                        <p class="text-[10px] text-slate-400">días</p>
                    </div>
                    <div class="bg-amber-50 rounded-2xl p-3 text-center">
                        <p class="text-[10px] font-bold text-amber-500 uppercase tracking-widest mb-1">En proceso</p>
                        <p class="text-2xl font-black text-amber-700">{{ $balance['pending'] }}</p>
                        <p class="text-[10px] text-amber-400">días</p>
                    </div>
                    <div class="bg-rose-50 rounded-2xl p-3 text-center">
                        <p class="text-[10px] font-bold text-rose-500 uppercase tracking-widest mb-1">Usados</p>
                        <p class="text-2xl font-black text-rose-700">{{ $balance['used'] }}</p>
                        <p class="text-[10px] text-rose-400">días</p>
                    </div>
                    <div class="bg-teal-50 rounded-2xl p-3 text-center">
                        <p class="text-[10px] font-bold text-teal-600 uppercase tracking-widest mb-1">Disponibles</p>
                        <p class="text-2xl font-black text-teal-700">{{ $balance['available'] }}</p>
                        <p class="text-[10px] text-teal-400">días</p>
                    </div>
                </div>
            @endif
        </div>

        {{-- Período --}}
        <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-black text-slate-700 uppercase tracking-widest">Período de vacaciones</h2>
                @if($businessDays > 0)
                    <div class="flex items-center gap-2 bg-teal-50 border border-teal-100 rounded-xl px-3 py-1.5">
                        <svg class="w-3.5 h-3.5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span class="text-sm font-black text-teal-700">{{ $businessDays }} días hábiles</span>
                    </div>
                @endif
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Fecha de inicio *</label>
                    <input wire:model.live="start_date" type="date"
                        class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-teal-500 focus:ring-4 focus:ring-teal-500/5 transition-all duration-200 text-sm @error('start_date') border-rose-300 bg-rose-50/30 @enderror">
                    @error('start_date')
                        <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Fecha de fin *</label>
                    <input wire:model.live="end_date" type="date"
                        class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-teal-500 focus:ring-4 focus:ring-teal-500/5 transition-all duration-200 text-sm @error('end_date') border-rose-300 bg-rose-50/30 @enderror">
                    @error('end_date')
                        <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            @if($businessDays > 0 && $balance && $businessDays > $balance['available'])
                <div class="flex items-start gap-3 p-3 bg-amber-50 border border-amber-100 rounded-2xl">
                    <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-xs text-amber-700 font-medium">El empleado solo tiene <strong>{{ $balance['available'] }} días disponibles</strong> pero esta solicitud requiere <strong>{{ $businessDays }} días hábiles</strong>.</p>
                </div>
            @endif
        </div>

        {{-- Motivo y notas --}}
        <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm p-6 space-y-4">
            <h2 class="text-sm font-black text-slate-700 uppercase tracking-widest">Detalles adicionales</h2>

            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Motivo</label>
                <input wire:model="reason" type="text" placeholder="Descripción breve (opcional)"
                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-teal-500 focus:ring-4 focus:ring-teal-500/5 transition-all duration-200 text-sm">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Notas internas</label>
                <textarea wire:model="notes" rows="3" placeholder="Observaciones adicionales para el área de RRHH..."
                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-teal-500 focus:ring-4 focus:ring-teal-500/5 transition-all duration-200 text-sm resize-none"></textarea>
                @error('notes')
                    <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Botón de guardado --}}
        <div class="flex justify-end gap-3">
            <a wire:navigate href="{{ route('hr.vacations.index') }}"
                class="px-5 py-2.5 rounded-xl text-sm font-bold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 transition-colors">
                Cancelar
            </a>
            <button wire:click="save"
                class="inline-flex items-center gap-2 bg-gradient-to-r from-teal-600 to-teal-700 hover:from-teal-700 hover:to-teal-800 text-white text-sm font-bold px-6 py-2.5 rounded-xl transition-all duration-200 shadow-lg shadow-teal-500/25">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                {{ $leave && $leave->exists ? 'Guardar cambios' : 'Registrar solicitud' }}
            </button>
        </div>
    </div>
</div>

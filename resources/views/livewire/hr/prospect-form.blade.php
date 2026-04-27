<div class="min-h-screen bg-slate-50/50 -m-4 lg:-m-6">
    {{-- ── STICKY HEADER ────────────────────────────────────────────────── --}}
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 max-w-full mx-auto">
            <div class="flex items-center gap-3 min-w-0">
                <a wire:navigate href="{{ route('hr.prospects.index') }}" 
                   class="group flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-100 hover:shadow-sm transition-all duration-200">
                    <svg class="w-5 h-5 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl font-bold text-slate-800 truncate">
                        {{ $prospect?->exists ? 'Editar prospecto' : 'Nuevo prospecto' }}
                    </h1>
                    <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">
                        {{ $prospect?->exists ? $prospect->full_name : 'Registro de candidatos para vacantes' }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                @if($prospect?->exists)
                    <a href="{{ route('hr.prospects.show', $prospect) }}" wire:navigate
                       class="hidden md:inline-flex items-center gap-2 px-4 py-2 text-sm font-bold text-indigo-600 hover:text-indigo-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        Ver expediente
                    </a>
                @endif
                <a wire:navigate href="{{ route('hr.prospects.index') }}"
                    class="hidden sm:inline-flex px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 transition-colors">
                    Cancelar
                </a>
                <button type="button" wire:click="save"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:scale-[1.02] active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>{{ $prospect?->exists ? 'Guardar cambios' : 'Registrar prospecto' }}</span>
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-full mx-auto p-4 sm:p-6 lg:p-8">
        @if(session('success'))
            <div class="mb-6 flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl animate-in fade-in slide-in-from-top-4 duration-300">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm font-semibold">{{ session('success') }}</p>
            </div>
        @endif

        <form wire:submit="save" class="grid grid-cols-1 xl:grid-cols-12 gap-6 lg:gap-8">
            
            {{-- ── COLUMNA IZQUIERDA: Principal (8 cols) ────────────────────── --}}
            <div class="xl:col-span-8 space-y-6 lg:space-y-8">
                
                {{-- Card: Información Personal --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/30 flex items-center justify-between">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Información del Candidato</h3>
                        <span class="px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-600 text-[10px] font-bold uppercase tracking-wider">Datos Básicos</span>
                    </div>
                    <div class="p-6 lg:p-8 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Nombre(s) *</label>
                                <input wire:model="first_name" type="text"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 @error('first_name') border-red-300 bg-red-50/30 @else bg-slate-50/30 @enderror focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200">
                                @error('first_name') <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider ml-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Apellido Paterno *</label>
                                <input wire:model="last_name" type="text"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 @error('last_name') border-red-300 bg-red-50/30 @else bg-slate-50/30 @enderror focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200">
                                @error('last_name') <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider ml-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Apellido Materno</label>
                                <input wire:model="second_last_name" type="text"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Correo electrónico</label>
                                <input wire:model="email" type="email" placeholder="ejemplo@correo.com"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 @error('email') border-red-300 bg-red-50/30 @else bg-slate-50/30 @enderror focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200">
                                @error('email') <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider ml-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Teléfono</label>
                                <input wire:model="phone" type="tel" placeholder="55 1234 5678"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card: Detalles de la Aplicación --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/30">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Detalles de la Aplicación</h3>
                    </div>
                    <div class="p-6 lg:p-8 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Vacante específica</label>
                                <select wire:model.live="job_opening_id"
                                    class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200">
                                    <option value="">Sin vacante específica</option>
                                    @foreach($jobOpenings as $jo)
                                        <option value="{{ $jo->id }}">
                                            {{ $jo->title }}
                                            @if($jo->status !== 'open') ({{ \App\Models\HrJobOpening::STATUSES[$jo->status] ?? $jo->status }}) @endif
                                        </option>
                                    @endforeach
                                </select>
                                @if($jobOpenings->isEmpty())
                                    <p class="text-[10px] text-amber-500 font-semibold mt-1 ml-1">No hay vacantes registradas. <a href="{{ route('hr.job-openings.create') }}" wire:navigate class="underline">Crear vacante</a></p>
                                @endif
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Puesto al que aplica</label>
                                <select wire:model="position_id"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200">
                                    <option value="">Seleccionar puesto</option>
                                    @foreach($positions as $pos)
                                        <option value="{{ $pos->id }}">{{ $pos->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-slate-100">
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Fuente de reclutamiento</label>
                                <select wire:model="source"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200">
                                    <option value="">Seleccionar fuente</option>
                                    @foreach(\App\Models\HrProspect::SOURCES as $k => $v)
                                        <option value="{{ $k }}">{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Fecha de entrevista</label>
                                <input wire:model="interview_date" type="datetime-local"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card: Historial de Estados (Solo si existe) --}}
                @if($prospect?->exists && $prospect->statusLogs->count() > 0)
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/30">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Línea de Tiempo / Historial</h3>
                    </div>
                    <div class="p-6 lg:p-8">
                        <div class="space-y-8 relative before:absolute before:inset-0 before:ml-5 before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-transparent before:via-slate-200 before:to-transparent">
                            @foreach($prospect->statusLogs as $log)
                            <div class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
                                {{-- Icon --}}
                                <div class="flex items-center justify-center w-10 h-10 rounded-full border border-white bg-slate-100 group-[.is-active]:bg-indigo-100 text-slate-500 group-[.is-active]:text-indigo-600 shadow shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 transition-colors duration-300">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                {{-- Content --}}
                                <div class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] p-4 rounded-2xl border border-slate-100 bg-slate-50/50 hover:bg-white hover:shadow-md transition-all duration-300">
                                    <div class="flex items-center justify-between space-x-2 mb-1">
                                        <div class="font-bold text-slate-800 text-sm italic">"{{ $log->to_status_label }}"</div>
                                        <time class="font-mono text-[10px] font-bold text-indigo-500 uppercase tracking-wider">{{ $log->created_at->format('d M, Y H:i') }}</time>
                                    </div>
                                    <div class="text-slate-500 text-xs font-medium">
                                        @if($log->reason)
                                            {{ $log->reason }}
                                        @else
                                            Cambio de estado por {{ $log->user?->name ?? 'Sistema' }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

            </div>

            {{-- ── COLUMNA DERECHA: Lateral (4 cols) ───────────────────────── --}}
            <div class="xl:col-span-4 space-y-6 lg:space-y-8">
                
                {{-- Card: Estado --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/30 text-center">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Estado Actual</h3>
                    </div>
                    <div class="p-6 lg:p-8 space-y-6">
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Estatus del prospecto *</label>
                            <select wire:model.live="status"
                                class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-bold {{ \App\Models\HrProspect::STATUS_COLORS[$status] ?? '' }}">
                                @foreach(\App\Models\HrProspect::STATUSES as $k => $v)
                                    <option value="{{ $k }}">{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>

                        @if($status === 'rechazado' || ($prospect?->exists && $status !== $prospect->status))
                        <div class="space-y-2 animate-in fade-in slide-in-from-top-2 duration-300">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Motivo del cambio *</label>
                            <textarea wire:model="status_reason" rows="2" placeholder="Especificar por qué..."
                                class="w-full px-4 py-3 rounded-2xl border-red-200 bg-red-50/30 focus:bg-white focus:border-red-500 focus:ring-4 focus:ring-red-500/5 transition-all duration-200 resize-none text-sm"></textarea>
                            @error('status_reason') <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider ml-1">{{ $message }}</p> @enderror
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Card: CV Upload --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/30">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Documentación</h3>
                    </div>
                    <div class="p-6 lg:p-8 space-y-6">
                        <div class="relative group">
                            <div class="w-full aspect-video rounded-2xl bg-slate-50 border-2 border-dashed border-slate-200 flex flex-col items-center justify-center p-4 group-hover:border-indigo-300 transition-colors">
                                <svg class="w-10 h-10 text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Subir Currículum</p>
                                <p class="text-[10px] text-slate-400">PDF, DOC (Máx 5MB)</p>
                                <input type="file" wire:model="cv_upload" class="absolute inset-0 opacity-0 cursor-pointer" accept=".pdf,.doc,.docx">
                            </div>
                        </div>

                        @if($prospect?->cv_path)
                        <a href="{{ \Illuminate\Support\Facades\Storage::url($prospect->cv_path) }}" target="_blank"
                            class="flex items-center justify-center gap-2 w-full p-3 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold hover:bg-slate-200 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                            Ver archivo actual
                        </a>
                        @endif

                        <div wire:loading wire:target="cv_upload" class="text-center">
                            <span class="text-[10px] font-bold text-indigo-500 uppercase animate-pulse">Subiendo archivo...</span>
                        </div>
                        @error('cv_upload') <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Card: Notas --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/30">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Notas / Comentarios</h3>
                    </div>
                    <div class="p-6 lg:p-8">
                        <textarea wire:model="initial_notes" rows="6" placeholder="Experiencia, expectativas, etc..."
                            class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 resize-none text-sm"></textarea>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>

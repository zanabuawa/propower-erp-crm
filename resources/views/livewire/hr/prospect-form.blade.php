<div>
    <x-page-header
        :title="$prospect?->exists ? 'Editar prospecto: '.$prospect->full_name : 'Nuevo prospecto'"
        description="Registro de candidatos para vacantes">
        <x-slot:actions>
            <div class="flex gap-2">
                @if($prospect?->exists)
                <a href="{{ route('hr.prospects.show', $prospect) }}" wire:navigate
                   class="inline-flex items-center gap-2 px-3 py-2 text-sm text-indigo-600 hover:text-indigo-800 border border-indigo-200 rounded-lg hover:bg-indigo-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    Ver expediente
                </a>
                @endif
                <a href="{{ route('hr.prospects.index') }}" wire:navigate
                   class="inline-flex items-center gap-2 px-3 py-2 text-sm text-slate-600 hover:text-slate-800 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
                    ← Volver
                </a>
            </div>
        </x-slot:actions>
    </x-page-header>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    <form wire:submit="save" class="space-y-6">

        {{-- DATOS PERSONALES --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h3 class="text-sm font-semibold text-slate-700 mb-4 pb-3 border-b border-slate-100">Información del candidato</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Nombre(s) <span class="text-red-500">*</span></label>
                    <input wire:model="first_name" type="text" required
                           class="w-full px-3 py-2 text-sm border @error('first_name') border-red-300 @else border-slate-200 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    @error('first_name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Apellido paterno <span class="text-red-500">*</span></label>
                    <input wire:model="last_name" type="text" required
                           class="w-full px-3 py-2 text-sm border @error('last_name') border-red-300 @else border-slate-200 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    @error('last_name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Apellido materno</label>
                    <input wire:model="second_last_name" type="text"
                           class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Correo electrónico</label>
                    <input wire:model="email" type="email"
                           class="w-full px-3 py-2 text-sm border @error('email') border-red-300 @else border-slate-200 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Teléfono</label>
                    <input wire:model="phone" type="tel"
                           class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                </div>
            </div>
        </div>

        {{-- DATOS DEL PROCESO --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h3 class="text-sm font-semibold text-slate-700 mb-4 pb-3 border-b border-slate-100">Detalles de la aplicación</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Vacante</label>
                    <select wire:model.live="job_opening_id"
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                        <option value="">Sin vacante específica</option>
                        @foreach($jobOpenings as $jo)
                            <option value="{{ $jo->id }}">{{ $jo->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Puesto al que aplica</label>
                    <select wire:model="position_id"
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                        <option value="">Seleccionar puesto</option>
                        @foreach($positions as $pos)
                            <option value="{{ $pos->id }}">{{ $pos->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Fuente</label>
                    <select wire:model="source"
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                        <option value="">Seleccionar fuente</option>
                        @foreach(\App\Models\HrProspect::SOURCES as $k => $v)
                            <option value="{{ $k }}">{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Estado <span class="text-red-500">*</span></label>
                    <select wire:model.live="status" required
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                        @foreach(\App\Models\HrProspect::STATUSES as $k => $v)
                            <option value="{{ $k }}">{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                @if($status === 'rechazado' || ($prospect?->exists && $status !== $prospect->status))
                <div class="sm:col-span-2 lg:col-span-2">
                    <label class="block text-xs font-medium text-slate-600 mb-1">Motivo del cambio / rechazo <span class="text-red-500">*</span></label>
                    <input wire:model="status_reason" type="text" placeholder="Especificar motivo..."
                           class="w-full px-3 py-2 text-sm border @error('status_reason') border-red-300 @else border-slate-200 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    @error('status_reason') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                @endif
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Fecha de entrevista</label>
                    <input wire:model="interview_date" type="datetime-local"
                           class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                </div>
                <div class="sm:col-span-2 lg:col-span-2">
                    <label class="block text-xs font-medium text-slate-600 mb-1">CV / Archivo adjunto (PDF, DOC, DOCX - Máx 5MB)</label>
                    <div class="flex items-center gap-3">
                        <input wire:model="cv_upload" type="file" accept=".pdf,.doc,.docx"
                               class="block w-full text-sm text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-medium file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100">
                        @if($prospect?->cv_path)
                            <a href="{{ \Illuminate\Support\Facades\Storage::url($prospect->cv_path) }}" target="_blank"
                               class="text-xs text-indigo-600 font-medium whitespace-nowrap">Ver actual</a>
                        @endif
                    </div>
                    @error('cv_upload') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- HISTORIAL DE ESTADOS --}}
        @if($prospect?->exists && $prospect->statusLogs->count() > 0)
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h3 class="text-sm font-semibold text-slate-700 mb-4 pb-3 border-b border-slate-100">Historial de estados</h3>
            <div class="relative">
                <div class="absolute left-3 top-0 bottom-0 w-px bg-slate-100"></div>
                <div class="space-y-6 relative">
                    @foreach($prospect->statusLogs as $log)
                    <div class="flex gap-4">
                        <div class="relative z-10 w-6 h-6 rounded-full bg-white border-2 border-slate-200 flex items-center justify-center">
                            <div class="w-2 h-2 rounded-full bg-slate-400"></div>
                        </div>
                        <div class="flex-1 -mt-1">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-medium {{ \App\Models\HrProspect::STATUS_COLORS[$log->to_status] ?? 'bg-slate-100' }}">
                                    {{ $log->to_status_label }}
                                </span>
                                <span class="text-[11px] text-slate-400">{{ $log->created_at->format('d/m/Y H:i') }}</span>
                                <span class="text-[11px] text-slate-400">· Por: {{ $log->user?->name ?? 'Sistema' }}</span>
                            </div>
                            @if($log->reason)
                                <p class="text-xs text-slate-600 italic">"{{ $log->reason }}"</p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- NOTAS --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h3 class="text-sm font-semibold text-slate-700 mb-4 pb-3 border-b border-slate-100">Notas iniciales / Comentarios</h3>
            <textarea wire:model="initial_notes" rows="4"
                      class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30 resize-none"
                      placeholder="Experiencia relevante, expectativas salariales, etc."></textarea>
        </div>

        {{-- Acciones --}}
        <div class="flex justify-end gap-3">
            <a href="{{ route('hr.prospects.index') }}" wire:navigate
               class="px-4 py-2 text-sm text-slate-600 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
                Cancelar
            </a>
            <button type="submit"
                    class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                <span wire:loading.remove wire:target="save">Guardar prospecto</span>
                <span wire:loading wire:target="save">Guardando...</span>
            </button>
        </div>
    </form>
</div>

<div class="min-h-screen bg-slate-50/50 -m-4 sm:-m-6 lg:-m-8">
    {{-- ── STICKY HEADER ────────────────────────────────────────────────── --}}
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 max-w-full mx-auto">
            <div class="flex items-center gap-3 min-w-0">
                <a wire:navigate href="{{ route('hr.test-templates.index') }}" class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-indigo-50 hover:text-indigo-600 transition-all shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl font-bold text-slate-800 truncate">{{ $isEdit ? 'Editar Plantilla' : 'Nueva Plantilla' }}</h1>
                    <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">Diseño de evaluación técnica/psicométrica</p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <button wire:click="save" wire:loading.attr="disabled" wire:target="save"
                    class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold px-6 py-2.5 rounded-xl transition-all shadow-lg shadow-indigo-500/25 disabled:opacity-50">
                    <svg wire:loading.remove wire:target="save" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    <svg wire:loading wire:target="save" class="animate-spin w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span>Guardar Plantilla</span>
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto p-4 sm:p-6 lg:p-8 space-y-6">
        <div wire:loading wire:target="setCorrectOption" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/10 backdrop-blur-[1px]">
             <div class="bg-white p-3 rounded-xl shadow-xl border border-slate-200 flex items-center gap-3">
                 <svg class="animate-spin h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                 <span class="text-xs font-bold text-slate-600 tracking-tight">Actualizando respuesta correcta...</span>
             </div>
        </div>
        @if ($errors->any())
            <div class="p-4 bg-rose-50 border border-rose-100 text-rose-700 rounded-2xl">
                <p class="text-sm font-bold mb-1">Por favor corrige los siguientes errores:</p>
                <ul class="text-xs list-disc list-inside opacity-80">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Información Básica --}}
        <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-50">
                <h2 class="text-sm font-black text-slate-800 uppercase tracking-widest">Información General</h2>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Nombre de la Plantilla</label>
                    <input wire:model="name" type="text" class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all text-sm font-medium">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Descripción</label>
                    <textarea wire:model="description" rows="3" class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all text-sm font-medium"></textarea>
                </div>

                <div>
                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Clasificación / Puesto Objetivo</label>
                    <select wire:model="role_target" class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all text-sm font-bold text-indigo-600">
                        <option value="">General / Otro</option>
                        <option value="segurista">Segurista</option>
                        <option value="supervisor">Supervisor</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Puntaje Mínimo Aprobatorio (%)</label>
                    <input wire:model="passing_score" type="number" class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all text-sm font-medium">
                </div>

                <div>
                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Tiempo Límite (minutos)</label>
                    <input wire:model="duration_minutes" type="number" placeholder="Sin límite" class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all text-sm font-medium">
                    <p class="text-[9px] text-slate-400 mt-1 ml-1">Deja vacío para no poner límite de tiempo.</p>
                </div>

                <div class="flex items-center gap-3">
                    <button type="button" wire:click="$set('is_active', {{ !$is_active ? 'true' : 'false' }})" 
                        class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none {{ $is_active ? 'bg-indigo-600' : 'bg-slate-200' }}">
                        <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $is_active ? 'translate-x-5' : 'translate-x-0' }}"></span>
                    </button>
                    <span class="text-sm font-bold text-slate-600">Plantilla Activa</span>
                </div>
            </div>
        </div>

        {{-- Preguntas --}}
        <div class="space-y-6">
            <div class="flex items-center justify-between px-2">
                <h2 class="text-sm font-black text-slate-800 uppercase tracking-widest">Preguntas del Examen</h2>
                <button wire:click="addQuestion" class="inline-flex items-center gap-2 text-indigo-600 hover:text-indigo-700 text-xs font-black uppercase tracking-widest transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Agregar Pregunta</span>
                </button>
            </div>

            @foreach($questions as $index => $question)
                <div wire:key="question-{{ $index }}" class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden animate-in fade-in slide-in-from-bottom-4 duration-300">
                    <div class="p-6 bg-slate-50/50 border-b border-slate-100 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-xs font-black text-slate-400">#{{ $index + 1 }}</span>
                            <select wire:model.live="questions.{{ $index }}.type" class="text-xs font-black uppercase tracking-widest border-none bg-transparent focus:ring-0 text-indigo-600 cursor-pointer">
                                <option value="multiple_choice">Opción Múltiple</option>
                                <option value="open_ended">Pregunta Abierta</option>
                            </select>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-tighter">Puntos:</span>
                                <input wire:model="questions.{{ $index }}.points" type="number" class="w-16 px-2 py-1 rounded-lg border-slate-200 text-xs font-bold text-center">
                            </div>
                            <button wire:click="removeQuestion({{ $index }})" class="text-slate-300 hover:text-rose-500 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>

                    <div class="p-6 space-y-6">
                        <div>
                            <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Enunciado de la Pregunta</label>
                            <input wire:model="questions.{{ $index }}.question_text" type="text" placeholder="¿Cuál es su experiencia en...?" class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all text-sm font-medium">
                            @error("questions.$index.question_text") <span class="text-rose-500 text-[10px] font-bold mt-1 ml-1">{{ $message }}</span> @enderror
                        </div>

                        @if($question['type'] === 'multiple_choice')
                            <div class="space-y-3">
                                <div class="flex items-center justify-between ml-1">
                                    <div>
                                        <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest">Opciones de Respuesta</label>
                                        <p class="text-[9px] text-slate-400 font-medium italic">Marca el círculo de la respuesta correcta</p>
                                    </div>
                                    <button wire:click="addOption({{ $index }})" class="text-[10px] font-black text-indigo-600 uppercase tracking-tighter hover:underline">+ Añadir Opción</button>
                                </div>
                                
                                @error("questions.$index.options") 
                                    <div class="p-2 bg-rose-50 border border-rose-100 rounded-xl mb-2">
                                        <p class="text-[10px] text-rose-600 font-bold uppercase tracking-wider text-center">{{ $message }}</p>
                                    </div>
                                @enderror

                                <div class="grid grid-cols-1 gap-3">
                                    @foreach($question['options'] as $oIndex => $option)
                                        <div wire:key="question-{{ $index }}-option-{{ $oIndex }}" class="flex items-center gap-3 p-3 rounded-2xl border transition-all duration-200 group {{ $option['is_correct'] ? 'border-emerald-200 bg-emerald-50/30' : 'border-slate-100 bg-slate-50/20' }}">
                                            <div class="flex flex-col items-center gap-1">
                                                <button type="button" wire:click="setCorrectOption({{ $index }}, {{ $oIndex }})" 
                                                    class="w-6 h-6 rounded-full border-2 transition-all flex items-center justify-center shrink-0 {{ $option['is_correct'] ? 'bg-emerald-500 border-emerald-500 text-white' : 'border-slate-200 bg-white hover:border-indigo-300 shadow-sm' }}">
                                                    @if($option['is_correct'])
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                                    @endif
                                                </button>
                                                @if($option['is_correct'])
                                                    <span class="text-[8px] font-black text-emerald-600 uppercase tracking-tighter">Correcta</span>
                                                @endif
                                            </div>

                                            <div class="flex-1">
                                                <input wire:model="questions.{{ $index }}.options.{{ $oIndex }}.option_text" type="text" placeholder="Opción {{ $oIndex + 1 }}" 
                                                    class="w-full bg-transparent border-none focus:ring-0 text-sm font-medium p-0">
                                                @error("questions.$index.options.$oIndex.option_text") <p class="text-rose-500 text-[9px] font-bold mt-0.5">{{ $message }}</p> @enderror
                                            </div>

                                            <button wire:click="removeOption({{ $index }}, {{ $oIndex }})" class="opacity-0 group-hover:opacity-100 text-slate-300 hover:text-rose-500 transition-all">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach

            <div class="pt-4">
                <button wire:click="addQuestion" class="w-full py-4 rounded-3xl border-2 border-dashed border-slate-200 text-slate-400 hover:border-indigo-300 hover:text-indigo-500 transition-all flex flex-col items-center justify-center gap-2 group">
                    <div class="w-10 h-10 rounded-full bg-slate-50 group-hover:bg-indigo-50 flex items-center justify-center transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    </div>
                    <span class="text-xs font-black uppercase tracking-widest">Añadir otra pregunta</span>
                </button>
            </div>
        </div>
    </div>
</div>

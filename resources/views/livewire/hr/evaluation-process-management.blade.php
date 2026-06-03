<div class="min-h-screen bg-slate-50/50 -m-4 sm:-m-6 lg:-m-8">
    {{-- ── STICKY HEADER ────────────────────────────────────────────────── --}}
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 max-w-full mx-auto">
            <div class="flex items-center gap-3 min-w-0">
                <a wire:navigate href="{{ route('hr.evaluations.dashboard') }}" class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-indigo-50 hover:text-indigo-600 transition-all shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl font-bold text-slate-800 truncate">Proceso de Evaluación: {{ $prospect?->full_name ?? $employee?->full_name }}</h1>
                    <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">Gestión de etapas y exámenes técnicos</p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <button wire:click="save"
                    class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold px-6 py-2.5 rounded-xl transition-all shadow-lg shadow-indigo-500/25">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    <span>Guardar Cambios</span>
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-5xl mx-auto p-4 sm:p-6 lg:p-8">
        @if(session('success'))
            <div class="mb-6 flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl animate-in fade-in slide-in-from-top-4 duration-300">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-sm font-semibold">{{ session('success') }}</p>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Resumen del Prospecto --}}
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm p-6 space-y-4">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-20 h-20 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400 mb-4">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <h2 class="text-lg font-bold text-slate-800">{{ $prospect?->full_name ?? $employee?->full_name }}</h2>
                        <p class="text-xs font-bold text-indigo-600 uppercase tracking-widest mt-1">{{ $prospect?->position?->name ?? $employee?->position?->name ?? 'Sin Puesto' }}</p>
                    </div>
                    
                    <div class="pt-4 border-t border-slate-50 space-y-3">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-black text-slate-400 uppercase tracking-tighter">Estado Actual</span>
                            <span class="px-2 py-0.5 rounded-lg {{ $prospect?->status_color ?? 'bg-slate-100 text-slate-600' }} font-bold text-[10px]">{{ $prospect?->status_label ?? $process->status }}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-black text-slate-400 uppercase tracking-tighter">Fecha Aplicación</span>
                            <span class="font-bold text-slate-600">{{ $prospect?->created_at?->format('d/m/Y') ?? $process->created_at->format('d/m/Y') }}</span>
                        </div>
                    </div>
                </div>

                @if($process)
                <div class="bg-indigo-600 rounded-3xl p-6 text-white shadow-xl shadow-indigo-500/20">
                    <h3 class="text-sm font-black uppercase tracking-widest mb-4">Progreso de Evaluación</h3>
                    <div class="space-y-4">
                        <div class="flex items-end justify-between">
                            <span class="text-3xl font-black">{{ count($stages) > 0 ? min($process->current_stage_index + 1, count($stages)) : 0 }} / {{ count($stages) }}</span>
                            <span class="text-[10px] font-black uppercase opacity-60">Etapa Actual</span>
                        </div>
                        <div class="h-2 w-full bg-white/20 rounded-full overflow-hidden">
                            <div class="h-full bg-white rounded-full transition-all duration-500" style="width: {{ count($stages) > 0 ? (($process->current_stage_index + 1) / count($stages)) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            {{-- Etapas del Proceso --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="flex items-center justify-between px-2">
                    <h2 class="text-sm font-black text-slate-800 uppercase tracking-widest">Etapas Definidas</h2>
                    <button wire:click="addStage" class="text-indigo-600 hover:text-indigo-700 text-xs font-black uppercase tracking-widest">
                        + Añadir Etapa
                    </button>
                </div>

                <div class="space-y-4">
                    @if(empty($stages))
                        <div class="bg-white rounded-3xl border border-dashed border-slate-200 p-8 text-center">
                            <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-50 text-slate-400">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6"/>
                                </svg>
                            </div>
                            <h3 class="text-sm font-black uppercase tracking-widest text-slate-700">Sin etapas definidas</h3>
                            <p class="mt-2 text-sm text-slate-500">Agrega solo las etapas necesarias para este proceso.</p>
                        </div>
                    @endif

                    @foreach($stages as $index => $stage)
                        <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden animate-in fade-in slide-in-from-right-4 duration-300">
                            <div class="p-6">
                                <div class="flex items-start justify-between gap-4 mb-6">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-2">
                                            <span class="w-6 h-6 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-[10px] font-black">0{{ $index + 1 }}</span>
                                            <input wire:model="stages.{{ $index }}.name" type="text" class="text-base font-bold text-slate-800 border-none p-0 focus:ring-0 w-full" placeholder="Nombre de la etapa">
                                        </div>
                                        <div class="mt-3 max-w-xs">
                                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Fecha de desbloqueo</label>
                                            <input wire:model="stages.{{ $index }}.scheduled_at" type="datetime-local" class="w-full rounded-xl border-slate-200 bg-slate-50/40 text-xs font-bold text-slate-600 focus:bg-white focus:border-indigo-500">
                                            <p class="mt-1 text-[10px] text-slate-400">Sin fecha, la etapa queda bloqueada para el evaluado.</p>
                                        </div>
                                    </div>
                                    <button wire:click="removeStage({{ $index }})" class="text-slate-300 hover:text-rose-500 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    {{-- Guías de Evaluación --}}
                                    <div>
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Guías de Evaluación (PDF/Doc)</label>
                                        <div class="space-y-2">
                                            @foreach(($stage['guide_paths'] ?? []) as $guideIndex => $guidePath)
                                                <div class="flex items-center justify-between gap-3 p-3 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-700 text-xs font-bold">
                                                    <span class="truncate">Material de apoyo de la etapa {{ $index + 1 }}</span>
                                                    <div class="flex items-center gap-3 shrink-0">
                                                        <a href="{{ Storage::url($guidePath) }}" target="_blank" class="text-emerald-600 hover:underline">Ver</a>
                                                        <button type="button" wire:click="removeGuide({{ $index }}, {{ $guideIndex }})" class="text-emerald-400 hover:text-rose-500 transition-colors">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>
                                            @endforeach

                                            <input type="file" wire:model="newGuides.{{ $index }}" multiple accept="application/pdf,.pdf" class="mt-2 block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-[10px] file:font-black file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100 cursor-pointer">
                                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider ml-1">Puedes seleccionar varios archivos a la vez.</p>
                                            @error("newGuides.$index.*") <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider ml-1">{{ $message }}</p> @enderror
                                        </div>

                                        {{-- Links de Video --}}
                                        <div class="mt-4">
                                            <div class="flex items-center justify-between mb-2">
                                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Links de Video</label>
                                                <button wire:click="addVideoLink({{ $index }})" class="text-indigo-600 hover:text-indigo-700 text-[10px] font-black uppercase">+ Añadir</button>
                                            </div>
                                            <div class="space-y-2">
                                                @foreach($stage['video_links'] as $linkIndex => $link)
                                                    <div class="flex items-center gap-2">
                                                        <input type="text" wire:model="stages.{{ $index }}.video_links.{{ $linkIndex }}" class="flex-1 rounded-xl border-slate-200 bg-slate-50/30 text-xs font-bold text-slate-600 focus:bg-white focus:border-indigo-500" placeholder="URL del video (YouTube/Vimeo)">
                                                        @if($link)
                                                            <button @click="$dispatch('open-video', { url: '{{ $link }}' })" class="text-indigo-600 hover:text-indigo-700 p-1">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                            </button>
                                                        @endif
                                                        <button wire:click="removeVideoLink({{ $index }}, {{ $linkIndex }})" class="text-slate-300 hover:text-rose-500">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                        </button>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Exámenes Asignados --}}
                                    <div>
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Exámenes a Realizar</label>
                                        <div class="space-y-4">
                                            <div class="flex flex-wrap gap-2">
                                                @foreach($testTemplates as $template)
                                                    <button 
                                                        type="button"
                                                        wire:click="$set('stages.{{ $index }}.test_templates.{{ $template->id }}', {{ isset($stage['test_templates'][$template->id]) ? 'null' : '1' }})"
                                                        class="px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-wider transition-all {{ isset($stage['test_templates'][$template->id]) ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' }}">
                                                        {{ $template->name }}
                                                    </button>
                                                @endforeach
                                            </div>

                                            <div class="space-y-2 mt-4 pt-4 border-t border-slate-50">
                                                @foreach($stage['test_templates'] as $templateId => $maxAttempts)
                                                    @if($maxAttempts !== null)
                                                        @php $template = $testTemplates->find($templateId); @endphp
                                                        @if($template)
                                                            <div class="flex items-center justify-between p-3 rounded-2xl bg-slate-50/50 border border-slate-100">
                                                                <div class="min-w-0">
                                                                    <span class="text-xs font-bold text-slate-700">{{ $template->name }}</span>
                                                                    @if(!empty($stage['attempts'][$templateId] ?? []))
                                                                        <div class="mt-2 flex flex-wrap gap-1.5">
                                                                            @foreach($stage['attempts'][$templateId] as $attempt)
                                                                                <a href="{{ route('hr.test-grading', $attempt['id']) }}" wire:navigate
                                                                                    class="px-2 py-1 rounded-lg border border-slate-200 bg-white text-[9px] font-black uppercase tracking-wider text-slate-500 hover:border-indigo-200 hover:text-indigo-600 transition-colors">
                                                                                    Intento #{{ $attempt['attempt_number'] }} &middot; {{ $attempt['score'] }}%
                                                                                </a>
                                                                            @endforeach
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                                <div class="flex items-center gap-2">
                                                                    <span class="text-[9px] font-black text-slate-400 uppercase">Intentos:</span>
                                                                    <input type="number" wire:model="stages.{{ $index }}.test_templates.{{ $templateId }}" class="w-16 rounded-lg border-slate-200 bg-white text-xs font-black text-indigo-600 text-center p-1 focus:ring-0" min="1">
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Video Player Modal --}}
    <div x-data="{ open: false, videoUrl: '' }" 
         x-on:open-video.window="open = true; videoUrl = $event.detail.url"
         x-show="open" 
         class="fixed inset-0 z-50 overflow-y-auto" 
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
            <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-sm" @click="open = false; videoUrl = ''"></div>

            <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative overflow-hidden transition-all transform bg-black rounded-3xl shadow-2xl sm:max-w-4xl sm:w-full aspect-video">
                <button @click="open = false; videoUrl = ''" class="absolute top-4 right-4 z-10 p-2 text-white/50 hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
                
                <template x-if="videoUrl">
                    <iframe class="w-full h-full" :src="videoUrl" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                </template>
            </div>
        </div>
    </div>
</div>


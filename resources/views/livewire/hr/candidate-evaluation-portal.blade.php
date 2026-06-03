<div class="min-h-screen bg-slate-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8 mb-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div>
                    <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">
                        ¡Hola, {{ $prospect->first_name }}!
                    </h1>
                    <p class="mt-2 text-lg text-slate-600">
                        Bienvenido a tu portal de evaluación para la vacante de <span class="font-semibold text-indigo-600">{{ $prospect->position->name ?? 'Candidato' }}</span>.
                    </p>
                </div>
                <div class="flex-shrink-0">
                    <img src="{{ asset('assets/img/logo.png') }}" alt="Logo" class="h-12 w-auto opacity-80">
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="mt-8">
                <div class="flex items-center justify-between text-sm font-medium text-slate-600 mb-2">
                    <span>Progreso del proceso</span>
                    <span>{{ $progress }}%</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden">
                    <div class="bg-indigo-600 h-3 rounded-full transition-all duration-1000 ease-out" style="width: {{ $progress }}%"></div>
                </div>
            </div>
        </div>

        @if(!$process)
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-12 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-amber-100 text-amber-600 mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-slate-900 mb-2">Proceso no iniciado</h2>
                <p class="text-slate-600">Tu proceso de evaluación aún no ha sido configurado. Por favor, contacta al reclutador.</p>
            </div>
        @elseif($currentStage)
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Current Stage Info -->
                <div class="lg:col-span-2 space-y-8">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="bg-indigo-600 px-6 py-4">
                            <h2 class="text-lg font-bold text-white uppercase tracking-wider">Etapa Actual</h2>
                        </div>
                        <div class="p-8">
                            <h3 class="text-2xl font-bold text-slate-900 mb-3">{{ $currentStage->name }}</h3>
                            <p class="mb-6 rounded-xl bg-slate-50 px-4 py-3 text-sm font-medium text-slate-600 border border-slate-100">
                                Puedes consultar el material de esta etapa en cualquier momento.
                                @if($currentStageAvailable)
                                    El examen ya esta disponible.
                                @elseif($currentStage->scheduled_at)
                                    El examen se habilita el {{ $currentStage->scheduled_at->format('d/m/Y H:i') }}.
                                @else
                                    El examen aun no ha sido programado por Recursos Humanos.
                                @endif
                            </p>
                            
                            @php
                                $guidePaths = collect($currentStage->guide_paths ?? [])
                                    ->when($currentStage->guide_path, fn ($paths) => $paths->prepend($currentStage->guide_path))
                                    ->filter()
                                    ->unique()
                                    ->values();
                            @endphp

                            @if($guidePaths->isNotEmpty())
                                @foreach($guidePaths as $guideIndex => $guidePath)
                                <div class="mb-6 p-4 bg-indigo-50 rounded-xl border border-indigo-100 flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <div class="bg-indigo-600 p-2 rounded-lg">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-slate-900">Material de apoyo de la etapa {{ $currentStage->order + 1 }}</p>
                                            <p class="text-xs text-slate-600">PDF Informativo</p>
                                        </div>
                                    </div>
                                    <a href="{{ Storage::url($guidePath) }}" target="_blank" class="px-4 py-2 bg-white text-indigo-600 text-sm font-bold rounded-lg border border-indigo-200 hover:bg-indigo-50 transition-colors">
                                        Descargar
                                    </a>
                                </div>
                                @endforeach
                            @endif

                            @if($currentStage->video_links && count($currentStage->video_links) > 0)
                                <div class="space-y-4">
                                    <h4 class="text-sm font-bold text-slate-900 uppercase tracking-widest">Recursos Multimedia</h4>
                                    <div class="grid grid-cols-1 gap-4">
                                        @foreach($currentStage->video_links as $link)
                                            @php $embedUrl = $this->embedVideoUrl($link); @endphp
                                            @if($embedUrl)
                                                <div class="overflow-hidden rounded-xl bg-slate-900 shadow-lg">
                                                    <iframe src="{{ $embedUrl }}" class="aspect-video w-full" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                                                    <div class="bg-white px-4 py-3 text-right">
                                                        <a href="{{ $link }}" target="_blank" class="text-xs font-black uppercase tracking-wider text-slate-500 hover:text-indigo-600">Abrir video</a>
                                                    </div>
                                                </div>
                                            @else
                                                <a href="{{ $link }}" target="_blank" class="flex items-center justify-between gap-3 rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 transition-colors hover:bg-white">
                                                    <span class="truncate">{{ $link }}</span>
                                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.868v4.264a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                </a>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Tests List -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden sticky top-8">
                        <div class="bg-slate-900 px-6 py-4">
                            <h2 class="text-sm font-bold text-white uppercase tracking-wider">Evaluaciones Pendientes</h2>
                        </div>
                        <div class="p-6 space-y-4">
                            @forelse($tests as $test)
                                <div class="p-4 rounded-xl border {{ in_array($test->status, ['completed', 'graded'], true) ? 'bg-emerald-50 border-emerald-100' : 'bg-slate-50 border-slate-100' }}">
                                    <div class="flex items-start justify-between mb-3">
                                        <div class="flex-1">
                                            <h4 class="text-sm font-bold text-slate-900">{{ $test->template->name }}</h4>
                                            <p class="text-xs text-slate-500 mt-1 line-clamp-2">{{ $test->template->description }}</p>
                                        </div>
                                        <div class="ml-2 flex flex-col items-end gap-1">
                                            @if(in_array($test->status, ['completed', 'graded'], true))
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800">
                                                    Completado
                                                </span>
                                                <span class="text-[10px] font-black text-emerald-600 tracking-tighter">{{ (int)$test->score }}%</span>
                                            @elseif(in_array($test->status, ['pending_review', 'partially_graded'], true))
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800">
                                                    En revision
                                                </span>
                                            @elseif($test->status === 'failed' && $test->attempts_count < $test->max_attempts)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-rose-100 text-rose-800">
                                                    Reprobado ({{ (int)$test->score }}%)
                                                </span>
                                            @elseif($test->attempts_count >= $test->max_attempts)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-rose-100 text-rose-800">
                                                    Sin intentos ({{ (int)$test->score }}%)
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                    Pendiente
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    @if($currentStageAvailable && ! in_array($test->status, ['completed', 'graded', 'pending_review', 'partially_graded'], true) && $test->attempts_count < $test->max_attempts)
                                        <a href="{{ route('hr.take-test', ['prospectTest' => $test->id]) }}" 
                                           class="w-full inline-flex justify-center items-center px-4 py-2 {{ $test->status === 'failed' ? 'bg-rose-600 hover:bg-rose-700 shadow-rose-200' : 'bg-indigo-600 hover:bg-indigo-700 shadow-indigo-200' }} text-white text-sm font-bold rounded-lg transition-colors shadow-sm">
                                            {{ $test->status === 'failed' ? 'Reintentar Prueba' : 'Iniciar Prueba' }}
                                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                            </svg>
                                        </a>
                                        <p class="mt-2 text-[10px] text-center text-slate-400">
                                            Intentos restantes: {{ $test->max_attempts - $test->attempts_count }} de {{ $test->max_attempts }}
                                        </p>
                                    @elseif(! $currentStageAvailable && ! in_array($test->status, ['completed', 'graded', 'pending_review', 'partially_graded'], true) && $test->attempts_count < $test->max_attempts)
                                        <p class="mt-3 rounded-lg bg-slate-100 px-4 py-3 text-center text-xs font-bold text-slate-500">
                                            {{ $currentStage->scheduled_at ? 'Examen disponible el ' . $currentStage->scheduled_at->format('d/m/Y H:i') : 'Examen pendiente de programacion por RH.' }}
                                        </p>
                                    @endif
                                </div>
                            @empty
                                <div class="text-center py-8">
                                    <p class="text-sm text-slate-500 italic">No hay evaluaciones asignadas para esta etapa.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-12 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-slate-900 mb-2">¡Proceso Completado!</h2>
                <p class="text-slate-600">Has completado todas las etapas de evaluación. Nos pondremos en contacto contigo pronto.</p>
            </div>
        @endif
    </div>
</div>

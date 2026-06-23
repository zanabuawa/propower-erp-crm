<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    @if(!$employee)
        <div class="max-w-2xl mx-auto bg-white border border-slate-200 p-8 rounded-3xl shadow-sm">
            <div class="flex flex-col items-center text-center">
                <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mb-4 text-slate-400">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <h2 class="text-xl font-bold text-slate-800">Perfil no encontrado</h2>
                <p class="mt-2 text-slate-500 text-sm">No tienes un registro de empleado vinculado. Contacta a RH para habilitar tu portal.</p>
            </div>
        </div>
    @else
        {{-- Header simple y elegante --}}
        <div class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h1 class="text-3xl font-bold text-slate-900 tracking-tight">
                    ¡Hola, {{ $employee->first_name }}!
                </h1>
                <p class="text-slate-500 text-sm mt-1">{{ now()->translatedFormat('l, d \d\e F \d\e Y') }}</p>
            </div>
            
            <div class="flex items-center gap-4 bg-slate-50 px-5 py-3 rounded-2xl border border-slate-200/60">
                <div class="w-10 h-10 bg-white rounded-xl border border-slate-200 flex items-center justify-center shadow-sm">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider leading-none mb-1">Puesto Actual</p>
                    <p class="text-xs font-bold text-slate-700">{{ $employee->position->name ?? 'Colaborador' }}</p>
                </div>
            </div>
        </div>

        <x-alert />

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            {{-- Columna Izquierda: Checador (Sticky en Desktop) --}}
            <div class="lg:col-span-4 space-y-8 order-2 lg:order-1 lg:sticky lg:top-6">
                <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 p-8 text-center">
                    <div class="mb-8" x-data="{ 
                        time: '{{ now()->format('H:i:s') }}',
                        init() {
                            setInterval(() => {
                                const now = new Date();
                                this.time = now.getHours().toString().padStart(2, '0') + ':' + 
                                            now.getMinutes().toString().padStart(2, '0') + ':' + 
                                            now.getSeconds().toString().padStart(2, '0');
                            }, 1000);
                        }
                    }">
                        <div class="text-5xl font-bold text-slate-800 tracking-tighter tabular-nums" x-text="time">
                            {{ now()->format('H:i:s') }}
                        </div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Hora Actual</p>
                    </div>

                    {{-- Estado GPS --}}
                    <div class="mb-8 h-12 flex items-center justify-center">
                        @if($geoStatus === 'loading')
                            <div class="flex items-center gap-2 text-slate-400 animate-pulse">
                                <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <span class="text-xs font-medium">Ubicando...</span>
                            </div>
                        @elseif($geoStatus === 'located')
                            @if($locationValid)
                                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-50 border border-emerald-100 text-emerald-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    <span class="text-[10px] font-bold uppercase truncate max-w-[150px]">{{ $detectedZone->name }}</span>
                                </div>
                            @else
                                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-rose-50 border border-rose-100 text-rose-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                    <span class="text-[10px] font-bold uppercase">Fuera de Zona</span>
                                </div>
                            @endif
                        @endif
                    </div>

                    {{-- Botones de Acción --}}
                    <div class="space-y-4">
                        @if(!$todayAttendance)
                            <button wire:click="checkIn" wire:loading.attr="disabled"
                                    @if(!$locationValid) disabled @endif
                                    class="w-full py-5 px-6 rounded-2xl font-bold text-lg transition-all active:scale-95 disabled:opacity-50 disabled:grayscale {{ $locationValid ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200 hover:bg-indigo-700' : 'bg-slate-200 text-slate-400' }}">
                                <span class="flex items-center justify-center gap-3">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                                    REGISTRAR ENTRADA
                                </span>
                            </button>
                        @elseif(!$todayAttendance->check_out)
                            <div class="mb-6 p-4 bg-indigo-50/50 rounded-2xl border border-indigo-100">
                                <p class="text-xs text-indigo-900 font-bold uppercase tracking-wider">Entrada: {{ \Carbon\Carbon::parse($todayAttendance->check_in)->format('H:i') }}</p>
                            </div>
                            
                            <button wire:click="checkOut" wire:loading.attr="disabled"
                                    @if(!$locationValid) disabled @endif
                                    class="w-full py-5 px-6 rounded-2xl font-bold text-lg transition-all active:scale-95 disabled:opacity-50 disabled:grayscale {{ $locationValid ? 'bg-rose-500 text-white shadow-lg shadow-rose-200 hover:bg-rose-600' : 'bg-slate-200 text-slate-400' }}">
                                <span class="flex items-center justify-center gap-3">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    REGISTRAR SALIDA
                                </span>
                            </button>
                        @else
                            <div class="py-8 px-4 rounded-3xl bg-emerald-50/50 border border-emerald-100 flex flex-col items-center">
                                <div class="w-12 h-12 bg-white rounded-2xl shadow-sm border border-emerald-100 flex items-center justify-center mb-3 text-emerald-500">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <h3 class="text-xs font-bold text-emerald-800 uppercase tracking-widest">Jornada Completada</h3>
                                <p class="text-xl font-bold text-emerald-600 mt-2">{{ $todayAttendance->worked_hours }} hrs</p>
                            </div>
                        @endif

                        @if(!$locationValid && !($todayAttendance && $todayAttendance->check_out))
                            <button type="button" onclick="requestLocation()"
                                    class="w-full py-3 text-[10px] font-bold uppercase tracking-widest text-slate-400 hover:text-indigo-600 transition-colors">
                                [ Validar mi ubicación ]
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Historial Simple --}}
                <div class="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-sm">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                        <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Registros Recientes</h3>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @forelse($recentAttendances as $att)
                            <div class="px-6 py-3 flex items-center justify-between">
                                <div class="text-xs font-bold text-slate-700">{{ \Carbon\Carbon::parse($att->date)->translatedFormat('D, d M') }}</div>
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">{{ \Carbon\Carbon::parse($att->check_in)->format('H:i') }} - {{ $att->check_out ? \Carbon\Carbon::parse($att->check_out)->format('H:i') : '--' }}</div>
                                <span class="w-2 h-2 rounded-full {{ $att->status === 'present' ? 'bg-emerald-400' : 'bg-rose-400' }}"></span>
                            </div>
                        @empty
                            <div class="p-6 text-center text-xs text-slate-300 italic">No hay registros previos</div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Columna Derecha: Evaluaciones y Pendientes --}}
            <div class="lg:col-span-8 space-y-8 order-1 lg:order-2">
                
                {{-- Evaluaciones --}}
                <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden" x-data="{ openProcess: null }">
                    <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-bold text-slate-800">Mis Evaluaciones</h2>
                            <p class="text-xs text-slate-500 font-medium">Seguimiento de tu crecimiento profesional</p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </div>
                    </div>

                    <div class="divide-y divide-slate-100">
                        @forelse($evaluationSummary as $evaluation)
                            @php
                                $process = $evaluation['process'];
                                $currentStage = $evaluation['stages']->firstWhere('is_current', true);
                                $displayStage = $currentStage ?? $evaluation['stages']->last();
                                $progress = $evaluation['is_completed'] ? 100 : ($process->total_stages > 0 ? round((($process->current_stage_index + 1) / $process->total_stages) * 100) : 0);
                            @endphp

                            <div class="p-8">
                                <div class="flex flex-col gap-6">
                                    <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2 mb-2">
                                                <span class="inline-flex px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider {{ $evaluation['is_completed'] ? 'bg-emerald-100 text-emerald-700' : 'bg-indigo-100 text-indigo-700' }}">
                                                    {{ $evaluation['is_completed'] ? 'Completado' : 'Activo' }}
                                                </span>
                                            </div>
                                            <h3 class="text-xl font-bold text-slate-800">{{ data_get($displayStage, 'model.name', 'Proceso de Evaluación') }}</h3>
                                            <p class="text-xs text-slate-500 mt-1">Etapa {{ $process->current_stage_index + 1 }} de {{ max(1, $process->total_stages) }}</p>
                                        </div>
                                        <button type="button"
                                            x-on:click="openProcess = openProcess === {{ $process->id }} ? null : {{ $process->id }}"
                                            class="inline-flex items-center justify-center gap-2 rounded-xl {{ $evaluation['is_completed'] ? 'bg-slate-800' : 'bg-indigo-600' }} px-5 py-3 text-xs font-bold uppercase tracking-wider text-white shadow-md hover:opacity-90 transition-all active:scale-95 shrink-0">
                                            {{ $evaluation['is_completed'] ? 'Historial' : 'Ver Detalle' }}
                                            <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-90': openProcess === {{ $process->id }} }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                        </button>
                                    </div>

                                    <div class="h-2 rounded-full bg-slate-100 overflow-hidden">
                                        <div class="h-full rounded-full {{ $evaluation['is_completed'] ? 'bg-emerald-500' : 'bg-indigo-600' }} transition-all duration-1000" style="width: {{ $progress }}%"></div>
                                    </div>
                                </div>

                                {{-- Secciones Expandibles --}}
                                <div x-cloak x-show="openProcess === {{ $process->id }}" x-collapse class="mt-8 space-y-4">
                                    @foreach($evaluation['stages'] as $stageData)
                                        @php
                                            $stage = $stageData['model'];
                                            $guidePaths = collect($stage->guide_paths ?? [])
                                                ->when($stage->guide_path, fn ($paths) => $paths->prepend($stage->guide_path))
                                                ->filter()
                                                ->unique()
                                                ->values();
                                            $videoLinks = collect($stage->video_links ?? [])->filter()->values();
                                        @endphp
                                        <div class="rounded-2xl border {{ $stageData['is_current'] ? 'border-indigo-100 bg-indigo-50/20' : 'border-slate-50 bg-slate-50/30' }} p-5"
                                             x-data="{ materialsOpen: false }">
                                            <div class="flex items-center justify-between mb-4">
                                                <div class="flex items-center gap-3">
                                                    <span class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold {{ $stageData['is_completed'] ? 'bg-emerald-100 text-emerald-700' : ($stageData['is_current'] ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-200 text-slate-500') }}">
                                                        {{ $stage->order + 1 }}
                                                    </span>
                                                    <h4 class="text-sm font-bold text-slate-800">{{ $stage->name }}</h4>
                                                </div>
                                            </div>

                                            @if($guidePaths->isNotEmpty() || $videoLinks->isNotEmpty())
                                                <div class="mb-5 space-y-4">
                                                    <button type="button"
                                                            x-on:click="materialsOpen = !materialsOpen"
                                                            class="flex w-full items-center justify-between rounded-xl border border-slate-200 bg-white px-4 py-3 text-left shadow-sm transition hover:border-indigo-100 hover:bg-indigo-50/40">
                                                        <span>
                                                            <span class="block text-xs font-black text-slate-800">Material de apoyo</span>
                                                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                                                {{ $guidePaths->count() }} PDF{{ $guidePaths->count() === 1 ? '' : 's' }} &middot; {{ $videoLinks->count() }} video{{ $videoLinks->count() === 1 ? '' : 's' }}
                                                            </span>
                                                        </span>
                                                        <svg class="h-4 w-4 text-slate-400 transition-transform" :class="{ 'rotate-90 text-indigo-600': materialsOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                                        </svg>
                                                    </button>

                                                    <div x-cloak x-show="materialsOpen" x-collapse class="space-y-4">
                                                        @if($guidePaths->isNotEmpty())
                                                            <div>
                                                                <p class="mb-2 text-[10px] font-black uppercase tracking-widest text-slate-400">PDFs y guias</p>
                                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                                                @foreach($guidePaths as $guidePath)
                                                                    <a href="{{ Storage::url($guidePath) }}" target="_blank"
                                                                       class="flex items-center gap-3 rounded-xl border border-emerald-100 bg-white px-4 py-3 text-left shadow-sm transition hover:border-emerald-200 hover:bg-emerald-50">
                                                                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                                                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                                                        </span>
                                                                        <span class="min-w-0">
                                                                            <span class="block truncate text-xs font-black text-slate-800">Material de apoyo de la etapa {{ $stage->order + 1 }}</span>
                                                                            <span class="block text-[9px] font-bold uppercase tracking-wider text-emerald-600">Abrir PDF</span>
                                                                        </span>
                                                                    </a>
                                                                @endforeach
                                                            </div>
                                                            </div>
                                                        @endif

                                                        @if($videoLinks->isNotEmpty())
                                                            <div>
                                                                <p class="mb-2 text-[10px] font-black uppercase tracking-widest text-slate-400">Videos de apoyo</p>
                                                            <div class="grid grid-cols-1 gap-3">
                                                                @foreach($videoLinks as $link)
                                                                    @php $embedUrl = $this->embedVideoUrl($link); @endphp
                                                                    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                                                                        @if($embedUrl)
                                                                            <iframe src="{{ $embedUrl }}" class="aspect-video w-full" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                                                                        @else
                                                                            <div class="flex items-center justify-between gap-3 px-4 py-3">
                                                                                <p class="truncate text-xs font-bold text-slate-700">{{ $link }}</p>
                                                                                <a href="{{ $link }}" target="_blank" class="shrink-0 text-[10px] font-black uppercase tracking-wider text-indigo-600 hover:text-indigo-800">Abrir video</a>
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif

                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                @forelse($stageData['tests'] as $testData)
                                                    @php $test = $testData['model']; @endphp
                                                    <div class="bg-white border border-slate-200/60 rounded-xl p-4 shadow-sm hover:border-indigo-200 transition-colors">
                                                        <div class="flex items-start justify-between gap-3 mb-3">
                                                            <div class="min-w-0">
                                                                <p class="text-xs font-bold text-slate-800 truncate">{{ $test->template?->name }}</p>
                                                                <p class="text-[9px] font-bold text-slate-400 uppercase mt-1">Puntaje: {{ (int)$test->score }}%</p>
                                                            </div>
                                                            <span class="px-2 py-0.5 rounded text-[8px] font-bold uppercase {{ $testData['completed'] ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-50 text-slate-400' }}">
                                                                {{ $testData['completed'] ? 'Apto' : 'Pendiente' }}
                                                            </span>
                                                        </div>
                                                        
                                                        @if($testData['can_start'])
                                                            <a href="{{ route('hr.take-test', ['prospectTest' => $test->id]) }}"
                                                                class="flex items-center justify-center gap-2 w-full py-2 bg-indigo-50 text-indigo-600 rounded-lg text-[10px] font-bold uppercase tracking-widest hover:bg-indigo-600 hover:text-white transition-all">
                                                                Comenzar
                                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                                            </a>
                                                        @endif
                                                    </div>
                                                @empty
                                                    <p class="md:col-span-2 text-center py-4 text-[10px] font-bold text-slate-300 uppercase tracking-widest">Sin exámenes</p>
                                                @endforelse
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <div class="p-16 text-center">
                                <p class="text-sm font-medium text-slate-400 italic">No tienes procesos activos en este momento.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Calculadora personal de nomina --}}
                @if($payrollEstimate)
                    <div class="bg-white rounded-3xl shadow-sm border border-indigo-100 overflow-hidden">
                        <div class="px-8 py-6 border-b border-slate-100 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h2 class="text-lg font-bold text-slate-800">Calculo estimado de mi nomina</h2>
                                <p class="text-xs text-slate-500 font-medium">
                                    Semana {{ \Carbon\Carbon::parse($payrollEstimatePeriod['start'])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($payrollEstimatePeriod['end'])->format('d/m/Y') }}
                                </p>
                            </div>
                            <span class="rounded-full bg-indigo-50 px-3 py-1 text-[10px] font-black uppercase tracking-widest text-indigo-600">Vista previa</span>
                        </div>

                        <div class="p-6 sm:p-8">
                            <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                                <div class="rounded-2xl bg-slate-50 px-4 py-3">
                                    <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">Horas</p>
                                    <p class="mt-1 text-lg font-black text-slate-700">{{ number_format((float) ($payrollEstimate['worked_hours'] ?? 0), 2) }}</p>
                                    <p class="mt-0.5 text-[9px] font-bold uppercase tracking-tight text-slate-300">
                                        {{ number_format((float) ($payrollEstimate['days_worked'] ?? 0), 2) }} dias eq.
                                    </p>
                                </div>
                                <div class="rounded-2xl bg-emerald-50 px-4 py-3">
                                    <p class="text-[9px] font-black uppercase tracking-widest text-emerald-400">Percepciones</p>
                                    <p class="mt-1 text-lg font-black text-emerald-600">$ {{ number_format((float) $payrollEstimate['gross_salary'], 2) }}</p>
                                </div>
                                <div class="rounded-2xl bg-rose-50 px-4 py-3">
                                    <p class="text-[9px] font-black uppercase tracking-widest text-rose-300">Deducciones</p>
                                    <p class="mt-1 text-lg font-black text-rose-600">$ {{ number_format((float) $payrollEstimate['total_deductions'], 2) }}</p>
                                </div>
                                <div class="rounded-2xl bg-indigo-50 px-4 py-3">
                                    <p class="text-[9px] font-black uppercase tracking-widest text-indigo-300">Estimado neto</p>
                                    <p class="mt-1 text-lg font-black text-indigo-600">$ {{ number_format((float) $payrollEstimate['net_salary'], 2) }}</p>
                                </div>
                            </div>

                            @if(!empty($payrollEstimate['other_perceptions']) || !empty($payrollEstimate['other_deductions']))
                                <div class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div class="rounded-2xl border border-emerald-100 bg-emerald-50/30 p-4">
                                        <p class="mb-2 text-[10px] font-black uppercase tracking-widest text-emerald-600">Complementos a favor</p>
                                        @forelse($payrollEstimate['other_perceptions'] as $row)
                                            <div class="flex justify-between text-xs font-bold text-slate-600">
                                                <span>{{ $row['concept'] }}</span>
                                                <span>$ {{ number_format((float) $row['amount'], 2) }}</span>
                                            </div>
                                        @empty
                                            <p class="text-xs font-bold text-slate-300">Sin complementos</p>
                                        @endforelse
                                    </div>
                                    <div class="rounded-2xl border border-rose-100 bg-rose-50/30 p-4">
                                        <p class="mb-2 text-[10px] font-black uppercase tracking-widest text-rose-500">Descuentos activos</p>
                                        @forelse($payrollEstimate['other_deductions'] as $row)
                                            <div class="flex justify-between text-xs font-bold text-slate-600">
                                                <span>{{ $row['concept'] }}</span>
                                                <span>$ {{ number_format((float) $row['amount'], 2) }}</span>
                                            </div>
                                        @empty
                                            <p class="text-xs font-bold text-slate-300">Sin descuentos</p>
                                        @endforelse
                                    </div>
                                </div>
                            @endif

                            <p class="mt-4 text-[10px] font-semibold uppercase tracking-widest text-slate-400">
                                Este calculo es informativo. La nomina oficial se genera y aprueba por RH.
                            </p>
                        </div>
                    </div>
                @endif

                {{-- Nominas recibidas --}}
                <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden" x-data="{ openPayroll: null }">
                    <div class="px-8 py-6 border-b border-slate-100 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-lg font-bold text-slate-800">Mis Nominas</h2>
                            <p class="text-xs text-slate-500 font-medium">Detalle de pagos recibidos por periodo</p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                    </div>

                    <div class="divide-y divide-slate-100">
                        @forelse($payrollItems as $item)
                            @php
                                $payroll = $item->payroll;
                                $perceptions = collect([
                                    ['label' => 'Sueldo base', 'amount' => $item->base_salary],
                                    ['label' => 'Horas extra', 'amount' => $item->overtime_amount],
                                    ['label' => 'Prima dominical', 'amount' => $item->sunday_premium],
                                    ['label' => 'Festivo trabajado', 'amount' => $item->holiday_worked],
                                    ['label' => 'Prima vacacional', 'amount' => $item->vacation_premium],
                                    ['label' => 'Aguinaldo', 'amount' => $item->christmas_bonus],
                                    ['label' => 'Vales de despensa', 'amount' => $item->food_voucher],
                                ])->merge(collect($item->other_perceptions ?? [])->map(fn ($concept) => [
                                    'label' => $concept['name'] ?? $concept['label'] ?? 'Otra percepcion',
                                    'amount' => $concept['amount'] ?? 0,
                                ]))->filter(fn ($row) => (float) $row['amount'] > 0)->values();

                                $deductions = collect([
                                    ['label' => 'ISPT', 'amount' => $item->ispt],
                                    ['label' => 'IMSS empleado', 'amount' => $item->imss_employee],
                                    ['label' => 'Infonavit', 'amount' => $item->infonavit_payment],
                                    ['label' => 'Prestamo', 'amount' => $item->loan_payment],
                                ])->merge(collect($item->other_deductions ?? [])->map(fn ($concept) => [
                                    'label' => $concept['name'] ?? $concept['label'] ?? 'Otra deduccion',
                                    'amount' => $concept['amount'] ?? 0,
                                ]))->filter(fn ($row) => (float) $row['amount'] > 0)->values();
                            @endphp

                            <div class="p-6 sm:p-8">
                                <div class="flex flex-col gap-5 md:flex-row md:items-center md:justify-between">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2 mb-2">
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider {{ $payroll?->status === 'stamped' ? 'bg-emerald-100 text-emerald-700' : 'bg-green-100 text-green-700' }}">
                                                {{ $payroll?->status_label ?? 'Pagada' }}
                                            </span>
                                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                                {{ \App\Models\HrPayroll::PERIOD_TYPES[$payroll?->period_type] ?? $payroll?->period_type ?? 'Nomina' }}
                                            </span>
                                        </div>
                                        <h3 class="text-base font-bold text-slate-800">{{ $payroll?->period_label ?? 'Periodo no disponible' }}</h3>
                                        <p class="mt-1 text-xs text-slate-400">
                                            {{ $payroll?->folio ?? 'Sin folio' }}
                                            @if($payroll?->paid_at)
                                                &middot; Pagada el {{ $payroll->paid_at->format('d/m/Y') }}
                                            @endif
                                        </p>
                                    </div>

                                    <div class="flex items-center justify-between gap-5 md:justify-end">
                                        <div class="text-right">
                                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Neto recibido</p>
                                            <p class="text-2xl font-black text-emerald-600">$ {{ number_format((float) $item->net_salary, 2) }}</p>
                                        </div>
                                        <button type="button"
                                                x-on:click="openPayroll = openPayroll === {{ $item->id }} ? null : {{ $item->id }}"
                                                class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-400 shadow-sm transition hover:border-emerald-100 hover:bg-emerald-50 hover:text-emerald-600">
                                            <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-90': openPayroll === {{ $item->id }} }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <div class="mt-5 grid grid-cols-3 gap-3">
                                    <div class="rounded-2xl bg-slate-50 px-4 py-3">
                                        <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">Bruto</p>
                                        <p class="mt-1 text-sm font-bold text-slate-700">$ {{ number_format((float) $item->gross_salary, 2) }}</p>
                                    </div>
                                    <div class="rounded-2xl bg-rose-50 px-4 py-3">
                                        <p class="text-[9px] font-black uppercase tracking-widest text-rose-300">Deducciones</p>
                                        <p class="mt-1 text-sm font-bold text-rose-600">$ {{ number_format((float) $item->total_deductions, 2) }}</p>
                                    </div>
                                    <div class="rounded-2xl bg-indigo-50 px-4 py-3">
                                        <p class="text-[9px] font-black uppercase tracking-widest text-indigo-300">Dias</p>
                                        <p class="mt-1 text-sm font-bold text-indigo-600">{{ number_format((float) $item->days_worked, 2) }}</p>
                                    </div>
                                </div>

                                <div x-cloak x-show="openPayroll === {{ $item->id }}" x-collapse class="mt-6 rounded-2xl border border-slate-100 bg-slate-50/50 p-5">
                                    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                                        <div>
                                            <h4 class="mb-3 text-[10px] font-black uppercase tracking-widest text-emerald-600">Percepciones</h4>
                                            <div class="space-y-2">
                                                @forelse($perceptions as $row)
                                                    <div class="flex items-center justify-between rounded-xl bg-white px-4 py-2 text-xs">
                                                        <span class="font-bold text-slate-600">{{ $row['label'] }}</span>
                                                        <span class="font-black text-slate-800">$ {{ number_format((float) $row['amount'], 2) }}</span>
                                                    </div>
                                                @empty
                                                    <p class="rounded-xl bg-white px-4 py-3 text-center text-xs font-bold text-slate-300">Sin percepciones desglosadas</p>
                                                @endforelse
                                            </div>
                                        </div>

                                        <div>
                                            <h4 class="mb-3 text-[10px] font-black uppercase tracking-widest text-rose-500">Deducciones</h4>
                                            <div class="space-y-2">
                                                @forelse($deductions as $row)
                                                    <div class="flex items-center justify-between rounded-xl bg-white px-4 py-2 text-xs">
                                                        <span class="font-bold text-slate-600">{{ $row['label'] }}</span>
                                                        <span class="font-black text-rose-600">$ {{ number_format((float) $row['amount'], 2) }}</span>
                                                    </div>
                                                @empty
                                                    <p class="rounded-xl bg-white px-4 py-3 text-center text-xs font-bold text-slate-300">Sin deducciones</p>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>

                                    @if($item->cfdi_pdf_path || $item->cfdi_xml_path)
                                        <div class="mt-5 flex flex-wrap gap-2 border-t border-slate-200 pt-4">
                                            @if($item->cfdi_pdf_path)
                                                <a href="{{ Storage::url($item->cfdi_pdf_path) }}" target="_blank" class="rounded-xl bg-white px-4 py-2 text-[10px] font-black uppercase tracking-widest text-indigo-600 ring-1 ring-slate-200 transition hover:bg-indigo-50">
                                                    Descargar PDF
                                                </a>
                                            @endif
                                            @if($item->cfdi_xml_path)
                                                <a href="{{ Storage::url($item->cfdi_xml_path) }}" target="_blank" class="rounded-xl bg-white px-4 py-2 text-[10px] font-black uppercase tracking-widest text-slate-500 ring-1 ring-slate-200 transition hover:bg-slate-100">
                                                    Descargar XML
                                                </a>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="p-12 text-center">
                                <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-50 text-slate-300">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 10v-1m0 0c-1.11 0-2.08-.402-2.599-1M12 16c1.11 0 2.08-.402 2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <p class="text-sm font-medium text-slate-400 italic">Aun no tienes nominas pagadas o timbradas.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Sección de Tareas y Recordatorios Simple --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm">
                        <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-4">Tareas Próximas</h3>
                        <div class="flex flex-col items-center justify-center py-6">
                            <svg class="w-8 h-8 text-slate-200 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            <p class="text-[10px] font-bold text-slate-300 uppercase">Sin tareas</p>
                        </div>
                    </div>

                    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm">
                        <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-4">Avisos</h3>
                        <div class="flex flex-col items-center justify-center py-6">
                            <svg class="w-8 h-8 text-slate-200 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            <p class="text-[10px] font-bold text-slate-300 uppercase">Sin avisos</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="mt-16 text-center pb-8 opacity-40">
        <p class="text-[9px] text-slate-400 uppercase tracking-[0.3em] font-bold">
            ProPower ERP-CRM &middot; {{ now()->year }}
        </p>
    </div>

    <style>
        [x-cloak] { display: none !important; }
    </style>

    <script>
    function requestLocation() {
        if (!navigator.geolocation) return;
        @this.set('geoStatus', 'loading');
        navigator.geolocation.getCurrentPosition(
            function(pos) { @this.call('setCoordinates', pos.coords.latitude, pos.coords.longitude, pos.coords.accuracy); },
            function() { @this.call('geoError'); },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );
    }
    function initGeoCheck() {
        const isEmployee = @json((bool)$employee);
        const isFinished = @json((bool)($todayAttendance?->check_in && $todayAttendance?->check_out));
        if (isEmployee && !isFinished) requestLocation();
    }
    document.addEventListener('livewire:navigated', initGeoCheck);
    if (document.readyState === 'complete') initGeoCheck();
    else window.addEventListener('load', initGeoCheck);
    </script>
</div>

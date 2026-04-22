<div class="min-h-screen bg-slate-50/50 -m-4 lg:-m-6">
    <style>
        .gantt-bar { transition: opacity .15s; }
        .gantt-bar:hover { opacity: .85; filter: brightness(1.05); }
        .custom-scrollbar::-webkit-scrollbar { height: 8px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    </style>

    {{-- ── STICKY HEADER ────────────────────────────────────────────────── --}}
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 max-w-full mx-auto">
            <div class="flex items-center gap-3 min-w-0">
                <a wire:navigate href="{{ route('projects.show', $project) }}" 
                   class="group flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-100 hover:shadow-sm transition-all duration-200">
                    <svg class="w-5 h-5 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl font-bold text-slate-800 truncate">Cronograma Gantt — {{ $project->name }}</h1>
                    <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">
                        {{ $timelineStart->translatedFormat('d M Y') }} → {{ $timelineEnd->translatedFormat('d M Y') }} &middot; {{ $totalDays }} días naturales
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <a wire:navigate href="{{ route('projects.board', $project) }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-slate-50 transition-all shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/></svg>
                    <span>Ver Tablero</span>
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-full mx-auto p-4 sm:p-6 lg:p-8">
        <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">

            {{-- Leyenda --}}
            <div class="px-8 py-4 border-b border-slate-100 bg-slate-50/50 flex flex-wrap gap-6 text-[10px] font-black uppercase tracking-widest text-slate-500">
                <span class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-indigo-400 shadow-sm shadow-indigo-100"></span>Pendiente</span>
                <span class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-blue-500 shadow-sm shadow-blue-100"></span>En progreso</span>
                <span class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-amber-400 shadow-sm shadow-amber-100"></span>Revisión</span>
                <span class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-emerald-500 shadow-sm shadow-emerald-100"></span>Completada</span>
                <span class="flex items-center gap-2">
                    <svg class="w-3 h-3 text-rose-500" viewBox="0 0 12 12" fill="currentColor"><path d="M6 0L12 6L6 12L0 6Z"/></svg>
                    Hito Comercial
                </span>
            </div>

            <div class="overflow-x-auto custom-scrollbar">
                <div style="min-width: 1200px;">
                    {{-- Timeline Header --}}
                    <div class="flex border-b border-slate-200 bg-slate-50/80">
                        <div class="w-72 flex-shrink-0 px-8 py-3 border-r border-slate-200">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Tarea / Entregable</p>
                        </div>
                        <div class="flex-1 flex">
                            @foreach($months as $month)
                                <div class="border-r border-slate-100 px-2 py-3 text-center"
                                     style="width: {{ round(($month['days'] / $totalDays) * 100, 4) }}%">
                                    <p class="text-[10px] font-black text-slate-600 uppercase tracking-widest">{{ $month['label'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    @php
                        $todayLeft = max(0, min(100, round(\Carbon\Carbon::now()->diffInDays($timelineStart) / $totalDays * 100, 2)));
                    @endphp

                    {{-- Filas de Tareas --}}
                    @foreach($taskRows as $row)
                        @php
                            $task = $row['task'];
                            $barColor = match($task->status) {
                                'en_progreso' => 'bg-blue-500 shadow-blue-200',
                                'revision'    => 'bg-amber-400 shadow-amber-100',
                                'completada'  => 'bg-emerald-500 shadow-emerald-100',
                                'cancelada'   => 'bg-slate-300 shadow-slate-100',
                                default       => 'bg-indigo-400 shadow-indigo-100',
                            };
                            $isLate = $task->due_date && $task->due_date->isPast() && !in_array($task->status, ['completada','cancelada']);
                        @endphp
                        <div class="flex border-b border-slate-100 hover:bg-slate-50/30 group transition-colors" style="min-height: 56px;">
                            <div class="w-72 flex-shrink-0 px-8 py-3 border-r border-slate-100 flex items-center gap-3 min-w-0">
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-bold text-slate-800 truncate {{ $task->status === 'completada' ? 'line-through text-slate-400 opacity-60' : '' }}">
                                        {{ $task->title }}
                                    </p>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest truncate mt-0.5">
                                        {{ $task->assignedTo?->name ?? 'Sin asignar' }}
                                        @if($task->estimated_hours) &middot; {{ $task->estimated_hours }}h @endif
                                    </p>
                                </div>
                                <button wire:click="openEdit({{ $task->id }})"
                                        class="opacity-0 group-hover:opacity-100 p-1.5 bg-white rounded-lg border border-slate-200 text-slate-400 hover:text-indigo-600 hover:shadow-sm transition-all flex-shrink-0">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536M9 11l6-6 3 3-6 6H9v-3z"/></svg>
                                </button>
                            </div>

                            <div class="flex-1 relative py-4">
                                {{-- Today Marker --}}
                                <div class="absolute top-0 bottom-0 w-0.5 bg-rose-400/30 z-10 pointer-events-none" style="left: {{ $todayLeft }}%"></div>

                                @if($row['left'] !== null)
                                    <div class="absolute top-1/2 -translate-y-1/2 h-6 rounded-lg {{ $barColor }} gantt-bar {{ $isLate ? 'ring-2 ring-rose-500 ring-offset-2' : '' }} flex items-center px-3 overflow-hidden shadow-sm"
                                         style="left: {{ $row['left'] }}%; width: {{ max(0.8, $row['width'] ?? 1) }}%"
                                         title="{{ $task->title }} | {{ $task->start_date?->format('d/m/Y') ?? '?' }} → {{ $task->due_date?->format('d/m/Y') ?? '?' }}">
                                        @if(($row['width'] ?? 0) > 8)
                                            <span class="text-[9px] text-white font-black uppercase tracking-tighter truncate">
                                                {{ $task->due_date?->format('d/m') }}
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <div class="absolute top-1/2 -translate-y-1/2 flex items-center" style="left: {{ $todayLeft }}%">
                                        <span class="text-[9px] font-black text-slate-300 uppercase tracking-widest ml-4 italic">Sin cronograma</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach

                    {{-- Filas de Hitos --}}
                    @foreach($milestoneRows as $row)
                        <div class="flex border-b border-slate-100 hover:bg-slate-50/30 transition-colors" style="min-height: 50px;">
                            <div class="w-72 flex-shrink-0 px-8 py-3 border-r border-slate-100 flex items-center gap-4 min-w-0">
                                <svg class="w-4 h-4 text-rose-500 shrink-0" viewBox="0 0 12 12" fill="currentColor"><path d="M6 0L12 6L6 12L0 6Z"/></svg>
                                <div class="min-w-0">
                                    <p class="text-xs font-black text-slate-700 truncate uppercase tracking-tight">{{ $row['milestone']->name }}</p>
                                    <p class="text-[9px] font-bold text-slate-400 uppercase mt-0.5">{{ $row['milestone']->due_date->format('d M Y') }}</p>
                                </div>
                            </div>
                            <div class="flex-1 relative py-4">
                                <div class="absolute top-0 bottom-0 w-0.5 bg-rose-400/30 z-10 pointer-events-none" style="left: {{ $todayLeft }}%"></div>
                                <div class="absolute top-1/2 -translate-y-1/2 -translate-x-1/2 z-20" style="left: {{ $row['left'] }}%">
                                    <svg class="w-6 h-6 text-rose-600 drop-shadow-sm transition-transform hover:scale-125 cursor-help"
                                         viewBox="0 0 16 16" fill="currentColor" title="{{ $row['milestone']->name }}">
                                        <path d="M8 0L16 8L8 16L0 8Z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- ── MODAL: EDITAR TAREA ────────────────────────────────────────── --}}
    @if($showEditModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" wire:click="$set('showEditModal', false)"></div>
            <div class="relative bg-white rounded-[2.5rem] shadow-2xl p-10 w-full max-w-lg mx-auto border border-slate-200 animate-in zoom-in-95 duration-200">
                <div class="w-16 h-16 rounded-3xl bg-indigo-50 flex items-center justify-center text-indigo-600 mb-6 mx-auto">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536M9 11l6-6 3 3-6 6H9v-3z"/></svg>
                </div>
                <h3 class="text-2xl font-black text-slate-800 text-center tracking-tight mb-2">Reprogramar Tarea</h3>
                <p class="text-sm text-slate-500 text-center font-medium leading-relaxed mb-8">Ajusta los tiempos de ejecución de la actividad.</p>
                
                <div class="space-y-6">
                    <div class="space-y-2">
                        <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest block">Título *</label>
                        <input wire:model="editTitle" type="text"
                               class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 transition-all">
                        @error('editTitle') <p class="text-[10px] text-rose-500 font-bold">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest block">Inicio</label>
                            <input wire:model="editStartDate" type="date"
                                   class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 transition-all">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest block">Entrega</label>
                            <input wire:model="editDueDate" type="date"
                                   class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 transition-all">
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-6">
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest block">Estatus</label>
                            <select wire:model="editStatus" class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10">
                                <option value="pendiente">Pendiente</option>
                                <option value="en_progreso">En Progreso</option>
                                <option value="revision">Revisión</option>
                                <option value="completada">Completada</option>
                                <option value="cancelada">Cancelada</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest block">Prioridad</label>
                            <select wire:model="editPriority" class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10">
                                <option value="baja">Baja</option>
                                <option value="media">Media</option>
                                <option value="alta">Alta</option>
                                <option value="urgente">Urgente</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest block">Hrs Est.</label>
                            <input wire:model="editEstimatedHours" type="number" min="0" step="0.5"
                                   class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-black text-slate-800 text-center">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mt-10">
                    <button wire:click="$set('showEditModal', false)"
                        class="py-4 bg-slate-50 text-slate-500 rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-slate-100 transition-all">Cancelar</button>
                    <button wire:click="saveEdit"
                        class="py-4 bg-indigo-600 text-white rounded-2xl text-xs font-black uppercase tracking-widest shadow-lg shadow-indigo-500/25 hover:bg-indigo-700 hover:scale-[1.02] transition-all">Guardar Cambios</button>
                </div>
            </div>
        </div>
    @endif
</div>


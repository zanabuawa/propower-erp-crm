<div class="min-h-screen bg-slate-50/50 -m-4 lg:-m-6">
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
                    <h1 class="text-lg sm:text-xl font-bold text-slate-800 truncate">Tablero de Operaciones</h1>
                    <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">{{ $project->code }} — {{ $project->name }} &middot; Avance: {{ $project->progress }}%</p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                @can('create projects')
                <button type="button" wire:click="openForm('pendiente')"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:scale-[1.02] active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Nueva Tarea</span>
                </button>
                @endcan
            </div>
        </div>
    </div>

    <div class="max-w-full mx-auto p-4 sm:p-6 lg:p-8 space-y-8">
        <x-alert />

        {{-- ── KANBAN BOARD ────────────────────────────────────────────────── --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6 items-start">
            @foreach(\App\Livewire\Projects\ProjectTaskBoard::$columns as $status => $label)
                @php
                    $colTasks = $tasks[$status] ?? collect();
                    $colConfig = match($status) {
                        'pendiente'   => ['bg-slate-100', 'text-slate-600', 'border-slate-200'],
                        'en_progreso' => ['bg-blue-50',   'text-blue-700',   'border-blue-100'],
                        'revision'    => ['bg-amber-50',  'text-amber-700',  'border-amber-100'],
                        'completada'  => ['bg-emerald-50','text-emerald-700','border-emerald-100'],
                        default       => ['bg-slate-100', 'text-slate-600', 'border-slate-200'],
                    };
                @endphp
                <div class="rounded-3xl border {{ $colConfig[2] }} {{ $colConfig[0] }} flex flex-col min-h-[500px] shadow-sm overflow-hidden">
                    {{-- Header Columna --}}
                    <div class="flex items-center justify-between px-6 py-4 border-b {{ $colConfig[2] }} bg-white/50 backdrop-blur-sm">
                        <div class="flex items-center gap-3">
                            <span class="w-2 h-2 rounded-full {{ str_replace('text','bg', $colConfig[1]) }} shadow-[0_0_8px_rgba(0,0,0,0.1)]"></span>
                            <span class="text-[10px] font-black uppercase tracking-[0.2em] {{ $colConfig[1] }}">{{ $label }}</span>
                        </div>
                        <span class="text-[10px] font-black {{ $colConfig[1] }} bg-white/80 px-2 py-0.5 rounded-lg border {{ $colConfig[2] }}">{{ $colTasks->count() }}</span>
                    </div>

                    {{-- Lista de Tareas --}}
                    <div class="flex-1 p-4 space-y-4 overflow-y-auto custom-scrollbar">
                        @forelse($colTasks as $task)
                            @php
                                $pConfig = match($task->priority) {
                                    'urgente' => 'bg-rose-500 shadow-rose-200',
                                    'alta'    => 'bg-orange-400 shadow-orange-200',
                                    'media'   => 'bg-blue-400 shadow-blue-200',
                                    default   => 'bg-slate-400 shadow-slate-200',
                                };
                            @endphp
                            <div class="bg-white rounded-2xl border border-slate-200/60 p-5 shadow-sm hover:shadow-md hover:border-indigo-200 transition-all duration-300 group relative">
                                <div class="absolute top-4 right-4">
                                    <span class="w-2 h-2 rounded-full block {{ $pConfig }} shadow-lg"></span>
                                </div>
                                
                                <div class="pr-6">
                                    <p class="text-sm font-bold text-slate-800 leading-relaxed">{{ $task->title }}</p>
                                </div>

                                @if($task->assignedTo || $task->due_date)
                                    <div class="mt-4 flex items-center justify-between gap-3 border-t border-slate-50 pt-3">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <div class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center text-[9px] font-black text-slate-500 shrink-0">
                                                {{ strtoupper(substr($task->assignedTo?->name ?? '?', 0, 2)) }}
                                            </div>
                                            <span class="text-[10px] font-bold text-slate-400 truncate">{{ $task->assignedTo?->name ?? 'Por asignar' }}</span>
                                        </div>
                                        @if($task->due_date)
                                            <div class="flex items-center gap-1.5 shrink-0">
                                                <svg class="w-3 h-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                <span class="text-[9px] font-black uppercase tracking-tighter {{ $task->due_date->isPast() && $task->status !== 'completada' ? 'text-rose-500' : 'text-slate-400' }}">
                                                    {{ $task->due_date->format('d M') }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                {{-- Acciones Rápidas (Hover) --}}
                                <div class="mt-4 flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-all duration-200 scale-95 group-hover:scale-100">
                                    @can('edit projects')
                                        <button wire:click="editTask({{ $task->id }})" class="p-2 bg-slate-50 text-slate-400 hover:text-indigo-600 rounded-lg transition-all" title="Editar">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                    @endcan
                                    @if($status !== 'en_progreso')
                                        <button wire:click="moveTask({{ $task->id }}, 'en_progreso')" class="p-2 bg-blue-50 text-blue-500 hover:bg-blue-600 hover:text-white rounded-lg transition-all" title="En Proceso">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7-7 7"/></svg>
                                        </button>
                                    @endif
                                    @if($status !== 'completada')
                                        <button wire:click="moveTask({{ $task->id }}, 'completada')" class="p-2 bg-emerald-50 text-emerald-500 hover:bg-emerald-600 hover:text-white rounded-lg transition-all" title="Finalizar">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        </button>
                                    @endif
                                    @can('delete projects')
                                        <button wire:click="deleteTask({{ $task->id }})" wire:confirm="¿Eliminar definitivamente esta tarea?" class="p-2 bg-rose-50 text-rose-400 hover:bg-rose-600 hover:text-white rounded-lg transition-all ml-2" title="Eliminar">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    @endcan
                                </div>
                            </div>
                        @empty
                            <div class="py-12 text-center">
                                <p class="text-[10px] font-bold text-slate-300 uppercase tracking-widest italic">Vacío</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ── MODAL: TAREA ────────────────────────────────────────────────── --}}
    @if($showForm)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" wire:click="$set('showForm', false)"></div>
            <div class="relative bg-white rounded-[2.5rem] shadow-2xl p-10 w-full max-w-md mx-auto border border-slate-200 animate-in zoom-in-95 duration-200">
                <div class="w-16 h-16 rounded-3xl bg-indigo-50 flex items-center justify-center text-indigo-600 mb-6 mx-auto">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <h3 class="text-2xl font-black text-slate-800 text-center tracking-tight mb-2">{{ $editingId ? 'Editar Tarea' : 'Nueva Tarea' }}</h3>
                <p class="text-sm text-slate-500 text-center font-medium leading-relaxed mb-8">Define los detalles operativos de la actividad.</p>
                
                <div class="space-y-6">
                    <div class="space-y-2">
                        <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest block">Título de la Tarea *</label>
                        <input wire:model="title" type="text" placeholder="¿Qué hay que hacer?" autofocus
                            class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 transition-all">
                        @error('title') <p class="text-[10px] text-rose-500 font-bold">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest block">Prioridad</label>
                            <select wire:model="priority" class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 cursor-pointer">
                                <option value="baja">Baja</option>
                                <option value="media">Media</option>
                                <option value="alta">Alta</option>
                                <option value="urgente">Urgente</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest block">Estado Inicial</label>
                            <select wire:model="newStatus" class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 cursor-pointer">
                                <option value="pendiente">Pendiente</option>
                                <option value="en_progreso">En Progreso</option>
                                <option value="revision">En Revisión</option>
                                <option value="completada">Completada</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest block">Responsable</label>
                        <select wire:model="assigned_to" class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 cursor-pointer">
                            <option value="">— Sin asignar —</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest block">Fecha Límite (Deadline)</label>
                        <input wire:model="due_date" type="date"
                            class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 transition-all">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mt-10">
                    <button wire:click="$set('showForm', false)"
                        class="py-4 bg-slate-50 text-slate-500 rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-slate-100 transition-all">Cancelar</button>
                    <button wire:click="save"
                        class="py-4 bg-indigo-600 text-white rounded-2xl text-xs font-black uppercase tracking-widest shadow-lg shadow-indigo-500/25 hover:bg-indigo-700 hover:scale-[1.02] transition-all">Guardar Tarea</button>
                </div>
            </div>
        </div>
    @endif
</div>


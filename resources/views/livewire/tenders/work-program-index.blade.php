<div class="{{ $embedded ? 'space-y-4' : 'min-h-screen bg-slate-50/50 -m-4 lg:-m-6' }}">
    @if(!$embedded)
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a wire:navigate href="{{ route('projects.show', $project) }}"
                    class="flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <div>
                    <h1 class="text-lg font-bold text-slate-800">Programa de Obra</h1>
                    <p class="text-[11px] text-slate-400 uppercase tracking-wider">{{ $project->name }}</p>
                </div>
            </div>
            @if(!$program)
            <button type="button" wire:click="createProgram"
                class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold px-4 py-2 rounded-xl transition-all shadow-lg shadow-indigo-500/20">
                Crear Programa
            </button>
            @else
            <div class="flex flex-wrap items-center justify-end gap-2">
                <form action="{{ route('works.program.gantt.print', $project) }}" method="GET" target="_blank"
                      class="flex flex-wrap items-center justify-end gap-2 rounded-2xl border border-slate-100 bg-slate-50/70 p-2">
                    <select name="scale" class="h-9 rounded-xl border-slate-200 bg-white text-xs font-bold text-slate-600">
                        <option value="auto" @selected($ganttScale === 'auto')>Escala auto</option>
                        <option value="week" @selected($ganttScale === 'week')>Semanas</option>
                        <option value="month" @selected($ganttScale === 'month')>Meses</option>
                        <option value="quarter" @selected($ganttScale === 'quarter')>Trimestres</option>
                        <option value="day" @selected($ganttScale === 'day')>Dias</option>
                    </select>
                    <select name="detail" class="h-9 rounded-xl border-slate-200 bg-white text-xs font-bold text-slate-600">
                        <option value="all">Todas</option>
                        <option value="parents">Principales</option>
                    </select>
                    <input type="date" name="start_date" value="{{ $ganttStartDate }}" class="h-9 w-36 rounded-xl border-slate-200 bg-white text-xs text-slate-600" title="Inicio del rango">
                    <input type="date" name="end_date" value="{{ $ganttEndDate }}" class="h-9 w-36 rounded-xl border-slate-200 bg-white text-xs text-slate-600" title="Fin del rango">
                    <button type="submit"
                            class="inline-flex items-center gap-2 border border-indigo-100 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 text-xs font-bold px-4 py-2 rounded-xl transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z"/></svg>
                        Exportar PDF
                    </button>
                </form>
                <button type="button" wire:click="openActivityModal()"
                    class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold px-4 py-2 rounded-xl transition-all shadow-lg shadow-indigo-500/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Nueva Actividad
                </button>
            </div>
            @endif
        </div>
    </div>
    @else
    <div class="flex items-center justify-between gap-4">
        <div>
            <h2 class="text-sm font-semibold text-slate-800">Programa de obra</h2>
            <p class="text-xs text-slate-400">Timeline y actividades del proyecto.</p>
        </div>
        @if(!$program)
        <button type="button" wire:click="createProgram"
            class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold px-4 py-2 rounded-xl transition-all">
            Crear programa
        </button>
        @else
        <div class="flex flex-wrap items-center justify-end gap-2">
            <form action="{{ route('works.program.gantt.print', $project) }}" method="GET" target="_blank"
                  class="flex flex-wrap items-center justify-end gap-2 rounded-2xl border border-slate-100 bg-slate-50/70 p-2">
                <select name="scale" class="h-9 rounded-xl border-slate-200 bg-white text-xs font-bold text-slate-600">
                    <option value="auto" @selected($ganttScale === 'auto')>Escala auto</option>
                    <option value="week" @selected($ganttScale === 'week')>Semanas</option>
                    <option value="month" @selected($ganttScale === 'month')>Meses</option>
                    <option value="quarter" @selected($ganttScale === 'quarter')>Trimestres</option>
                    <option value="day" @selected($ganttScale === 'day')>Dias</option>
                </select>
                <select name="detail" class="h-9 rounded-xl border-slate-200 bg-white text-xs font-bold text-slate-600">
                    <option value="all">Todas</option>
                    <option value="parents">Principales</option>
                </select>
                <input type="date" name="start_date" value="{{ $ganttStartDate }}" class="h-9 w-36 rounded-xl border-slate-200 bg-white text-xs text-slate-600" title="Inicio del rango">
                <input type="date" name="end_date" value="{{ $ganttEndDate }}" class="h-9 w-36 rounded-xl border-slate-200 bg-white text-xs text-slate-600" title="Fin del rango">
                <button type="submit"
                        class="inline-flex items-center gap-2 border border-indigo-100 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 text-xs font-bold px-4 py-2 rounded-xl transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z"/></svg>
                    Exportar PDF
                </button>
            </form>
            <button type="button" wire:click="openActivityModal()"
                class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold px-4 py-2 rounded-xl transition-all">
                Nueva actividad
            </button>
        </div>
        @endif
    </div>
    @endif

    <div class="{{ $embedded ? '' : 'max-w-7xl mx-auto p-4 sm:p-6 lg:p-8' }}">
        @if(session('success'))
            <div class="mb-4 p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl text-sm font-bold">{{ session('success') }}</div>
        @endif

        @if(!$program)
            <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center">
                <p class="text-slate-400 text-sm mb-4">No hay un programa de obra vigente para este proyecto.</p>
                <button wire:click="createProgram" class="bg-indigo-600 text-white text-sm font-bold px-6 py-3 rounded-xl">Crear Programa</button>
            </div>
        @else
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-100 bg-slate-50/30 flex items-center justify-between">
                <p class="text-xs font-black text-slate-500 uppercase">{{ $program->name }} — v{{ $program->version }}</p>
                <span class="px-2 py-1 rounded-lg text-[10px] font-black uppercase {{ $program->status === 'vigente' ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-500' }}">{{ $program->status }}</span>
            </div>
            <div class="border-b border-slate-100 bg-white">
                <div class="px-5 py-4">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <h3 class="text-sm font-black text-slate-800">Diagrama de Gantt</h3>
                            <p class="text-xs text-slate-400">
                                Periodo visible: {{ $gantt['start']->format('d/m/Y') }} - {{ $gantt['end']->format('d/m/Y') }} &middot; Escala: {{ $gantt['scale_label'] }}
                            </p>
                        </div>
                        <div class="flex flex-col items-start gap-3 lg:items-end">
                            <div class="flex flex-wrap items-center gap-2">
                                <label class="text-[10px] font-black uppercase tracking-widest text-slate-400">Escala</label>
                                <select wire:model.live="ganttScale" class="h-9 rounded-xl border-slate-200 bg-white text-xs font-bold text-slate-600">
                                    <option value="auto">Auto</option>
                                    <option value="day">Dias</option>
                                    <option value="week">Semanas</option>
                                    <option value="month">Meses</option>
                                    <option value="quarter">Trimestres</option>
                                </select>
                                <label class="text-[10px] font-black uppercase tracking-widest text-slate-400">Desde</label>
                                <input wire:model.live="ganttStartDate" type="date" class="h-9 w-36 rounded-xl border-slate-200 bg-white text-xs text-slate-600">
                                <label class="text-[10px] font-black uppercase tracking-widest text-slate-400">Hasta</label>
                                <input wire:model.live="ganttEndDate" type="date" class="h-9 w-36 rounded-xl border-slate-200 bg-white text-xs text-slate-600">
                                @if($ganttStartDate || $ganttEndDate)
                                    <button type="button" wire:click="clearGanttDates"
                                            class="h-9 rounded-xl border border-slate-200 bg-white px-3 text-[10px] font-black uppercase tracking-widest text-slate-500 hover:bg-slate-50">
                                        Limpiar
                                    </button>
                                @endif
                            </div>
                            <div class="flex flex-wrap items-center gap-3 text-[10px] font-bold text-slate-500">
                                <span class="inline-flex items-center gap-1.5"><span class="w-3 h-2 rounded bg-indigo-500"></span>Programado</span>
                                <span class="inline-flex items-center gap-1.5"><span class="w-3 h-2 rounded bg-emerald-500"></span>Real</span>
                                <span class="inline-flex items-center gap-1.5"><span class="w-3 h-2 rounded bg-red-500"></span>Fuera de tiempo</span>
                                <span class="inline-flex items-center gap-1.5"><span class="w-3 h-1 rounded bg-slate-900"></span>Avance %</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 overflow-x-auto">
                        <div class="min-w-[900px]">
                            <div class="grid grid-cols-[220px_1fr] gap-3 pb-2 border-b border-slate-100">
                                <div class="text-[10px] font-black uppercase text-slate-400">Actividad</div>
                                <div class="relative h-6">
                                    @foreach($gantt['ticks'] as $tick)
                                        <div class="absolute top-0 -translate-x-1/2 text-[10px] font-bold text-slate-400" style="left: {{ $tick['left'] }}%;">
                                            {{ $tick['label'] }}
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="divide-y divide-slate-50">
                                @forelse($gantt['rows'] as $row)
                                    <div class="grid grid-cols-[220px_1fr] gap-3 py-3">
                                        <div class="{{ $row['is_child'] ? 'pl-5' : '' }}">
                                            <p class="text-xs font-bold text-slate-700 truncate">{{ $row['name'] }}</p>
                                            <p class="text-[10px] text-slate-400">Prog. {{ $row['planned_label'] }} &middot; Real {{ $row['actual_label'] }}</p>
                                        </div>
                                        <div class="relative h-12 rounded-xl bg-slate-50 border border-slate-100 overflow-hidden">
                                            @foreach($gantt['ticks'] as $tick)
                                                <span class="absolute top-0 bottom-0 w-px bg-slate-200/70" style="left: {{ $tick['left'] }}%;"></span>
                                            @endforeach

                                            @if($row['planned'])
                                                <div class="absolute top-2 h-3 rounded-full bg-indigo-500" title="Programado: {{ $row['planned_label'] }}" style="left: {{ $row['planned']['left'] }}%; width: {{ $row['planned']['width'] }}%;"></div>
                                            @endif

                                            @if($row['progress_bar'])
                                                <div class="absolute top-6 h-1 rounded-full bg-slate-900" title="Avance: {{ $row['progress'] }}%" style="left: {{ $row['progress_bar']['left'] }}%; width: {{ $row['progress_bar']['width'] }}%;"></div>
                                            @endif

                                            @if($row['actual'])
                                                <div class="absolute bottom-2 h-3 rounded-full {{ $row['is_late'] ? 'bg-red-500' : 'bg-emerald-500' }}" title="Real: {{ $row['actual_label'] }}" style="left: {{ $row['actual']['left'] }}%; width: {{ $row['actual']['width'] }}%;"></div>
                                            @endif

                                            <div class="absolute inset-y-0 right-2 flex items-center">
                                                <span class="rounded-full bg-white/90 px-2 py-0.5 text-[10px] font-black text-slate-500 shadow-sm">{{ $row['progress'] }}%</span>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="py-8 text-center text-sm text-slate-400">
                                        {{ $ganttStartDate || $ganttEndDate ? 'No hay actividades en el rango seleccionado.' : 'Agrega actividades para generar el Gantt.' }}
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="bg-slate-50/50 border-b border-slate-100">
                        <th class="text-left px-4 py-3 text-[10px] font-black text-slate-400 uppercase">Actividad</th>
                        <th class="text-left px-4 py-3 text-[10px] font-black text-slate-400 uppercase w-16">Unidad</th>
                        <th class="text-right px-4 py-3 text-[10px] font-black text-slate-400 uppercase w-20">Cantidad</th>
                        <th class="text-left px-4 py-3 text-[10px] font-black text-slate-400 uppercase w-28">Inicio prog.</th>
                        <th class="text-left px-4 py-3 text-[10px] font-black text-slate-400 uppercase w-28">Fin prog.</th>
                        <th class="text-left px-4 py-3 text-[10px] font-black text-slate-400 uppercase w-28">Inicio real</th>
                        <th class="text-left px-4 py-3 text-[10px] font-black text-slate-400 uppercase w-28">Fin real</th>
                        <th class="text-left px-4 py-3 text-[10px] font-black text-slate-400 uppercase w-40">Avance</th>
                        <th class="px-4 py-3 w-20"></th>
                    </tr></thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($visibleProgramActivities as $act)
                            <tr class="hover:bg-slate-50/30 {{ $act->parent_id ? 'bg-slate-50/20' : '' }}">
                                <td class="px-4 py-2.5 {{ $act->parent_id ? 'pl-10' : '' }}">
                                    <p class="font-{{ $act->parent_id ? 'medium' : 'bold' }} text-slate-800 text-xs">{{ $act->name }}</p>
                                </td>
                                <td class="px-4 py-2.5 text-xs text-slate-500 text-center">{{ $act->unit ?? '—' }}</td>
                                <td class="px-4 py-2.5 text-xs text-slate-600 text-right">{{ $act->quantity ? number_format($act->quantity, 2) : '—' }}</td>
                                <td class="px-4 py-2.5 text-xs text-slate-500">{{ $act->start_date?->format('d/m/Y') ?? '—' }}</td>
                                <td class="px-4 py-2.5 text-xs text-slate-500">{{ $act->end_date?->format('d/m/Y') ?? '—' }}</td>
                                <td class="px-4 py-2.5 text-xs text-slate-500">{{ $act->actual_start_date?->format('d/m/Y') ?? '—' }}</td>
                                <td class="px-4 py-2.5 text-xs text-slate-500">{{ $act->actual_end_date?->format('d/m/Y') ?? '—' }}</td>
                                <td class="px-4 py-2.5">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 bg-slate-100 rounded-full h-2">
                                            <div class="bg-indigo-500 h-2 rounded-full transition-all" style="width: {{ $act->progress_pct }}%"></div>
                                        </div>
                                        <span class="text-[10px] font-black text-slate-600 w-8 text-right">{{ $act->progress_pct }}%</span>
                                    </div>
                                </td>
                                <td class="px-4 py-2.5">
                                    <div class="flex flex-wrap items-center justify-end gap-2">
                                        <button type="button" wire:click="openActivityModal({{ $act->id }})"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-amber-100 bg-amber-50 text-xs font-bold text-amber-700 hover:bg-amber-100 transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 7.125L16.875 4.5"/>
                                            </svg>
                                            Editar
                                        </button>
                                        <button type="button" wire:click="openActivityModal(null, {{ $act->id }})"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-indigo-100 bg-indigo-50 text-xs font-bold text-indigo-700 hover:bg-indigo-100 transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                            Subactividad
                                        </button>
                                        <button type="button" wire:click="deleteActivity({{ $act->id }})" wire:confirm="¿Eliminar actividad?"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-red-100 bg-red-50 text-xs font-bold text-red-700 hover:bg-red-100 transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            Eliminar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-8 text-center text-slate-400 text-sm">
                                    {{ $ganttStartDate || $ganttEndDate ? 'No hay actividades en el rango seleccionado.' : 'Sin actividades. Agrega la primera.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

    @if($showActivityModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md p-6 space-y-4">
            <h3 class="text-base font-black text-slate-800">{{ $editingActivityId ? 'Editar' : 'Nueva' }} Actividad {{ $parentActivityId ? '(Sub-actividad)' : '' }}</h3>
            <div class="space-y-3">
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Nombre *</label>
                    <input wire:model="actName" type="text" class="w-full mt-1 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold">
                    @error('actName') <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Unidad</label>
                        <input wire:model="actUnit" type="text" class="w-full mt-1 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Cantidad</label>
                        <input wire:model="actQuantity" type="number" step="0.01" class="w-full mt-1 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Inicio programado</label>
                        <input wire:model="actStartDate" type="date" class="w-full mt-1 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Fin programado</label>
                        <input wire:model="actEndDate" type="date" class="w-full mt-1 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                    </div>
                </div>
                <div class="rounded-2xl border border-emerald-100 bg-emerald-50/60 p-3">
                    <p class="mb-3 text-[10px] font-black uppercase tracking-widest text-emerald-700">Tiempo real capturado por supervisión</p>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Inicio real</label>
                            <input wire:model="actActualStartDate" type="date" class="w-full mt-1 px-4 py-2.5 bg-white border border-emerald-100 rounded-xl text-sm">
                        </div>
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Fin real</label>
                            <input wire:model="actActualEndDate" type="date" class="w-full mt-1 px-4 py-2.5 bg-white border border-emerald-100 rounded-xl text-sm">
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Notas del tiempo real</label>
                        <textarea wire:model="actActualNotes" rows="2" class="w-full mt-1 px-4 py-2.5 bg-white border border-emerald-100 rounded-xl text-sm resize-none"></textarea>
                    </div>
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Avance % ({{ $actProgress }}%)</label>
                    <input wire:model.live="actProgress" type="range" min="0" max="100" class="w-full mt-2">
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" wire:click="saveActivity" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black py-3 rounded-xl transition-all">Guardar</button>
                <button type="button" wire:click="$set('showActivityModal', false)" class="px-4 py-3 border border-slate-200 text-slate-600 text-xs font-bold rounded-xl hover:bg-slate-50">Cancelar</button>
            </div>
        </div>
    </div>
    @endif
</div>

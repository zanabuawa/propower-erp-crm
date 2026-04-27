<div class="min-h-screen bg-slate-50/50 -m-4 lg:-m-6">
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
            <button type="button" wire:click="openActivityModal()"
                class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold px-4 py-2 rounded-xl transition-all shadow-lg shadow-indigo-500/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nueva Actividad
            </button>
            @endif
        </div>
    </div>

    <div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8">
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
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="bg-slate-50/50 border-b border-slate-100">
                        <th class="text-left px-4 py-3 text-[10px] font-black text-slate-400 uppercase">Actividad</th>
                        <th class="text-left px-4 py-3 text-[10px] font-black text-slate-400 uppercase w-16">Unidad</th>
                        <th class="text-right px-4 py-3 text-[10px] font-black text-slate-400 uppercase w-20">Cantidad</th>
                        <th class="text-left px-4 py-3 text-[10px] font-black text-slate-400 uppercase w-28">Inicio</th>
                        <th class="text-left px-4 py-3 text-[10px] font-black text-slate-400 uppercase w-28">Fin</th>
                        <th class="text-left px-4 py-3 text-[10px] font-black text-slate-400 uppercase w-40">Avance</th>
                        <th class="px-4 py-3 w-20"></th>
                    </tr></thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($program->allActivities as $act)
                            <tr class="hover:bg-slate-50/30 {{ $act->parent_id ? 'bg-slate-50/20' : '' }}">
                                <td class="px-4 py-2.5 {{ $act->parent_id ? 'pl-10' : '' }}">
                                    <p class="font-{{ $act->parent_id ? 'medium' : 'bold' }} text-slate-800 text-xs">{{ $act->name }}</p>
                                </td>
                                <td class="px-4 py-2.5 text-xs text-slate-500 text-center">{{ $act->unit ?? '—' }}</td>
                                <td class="px-4 py-2.5 text-xs text-slate-600 text-right">{{ $act->quantity ? number_format($act->quantity, 2) : '—' }}</td>
                                <td class="px-4 py-2.5 text-xs text-slate-500">{{ $act->start_date?->format('d/m/Y') ?? '—' }}</td>
                                <td class="px-4 py-2.5 text-xs text-slate-500">{{ $act->end_date?->format('d/m/Y') ?? '—' }}</td>
                                <td class="px-4 py-2.5">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 bg-slate-100 rounded-full h-2">
                                            <div class="bg-indigo-500 h-2 rounded-full transition-all" style="width: {{ $act->progress_pct }}%"></div>
                                        </div>
                                        <span class="text-[10px] font-black text-slate-600 w-8 text-right">{{ $act->progress_pct }}%</span>
                                    </div>
                                </td>
                                <td class="px-4 py-2.5">
                                    <div class="flex items-center justify-end gap-1">
                                        <button wire:click="openActivityModal({{ $act->id }})" class="p-1 text-slate-300 hover:text-amber-600">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13l6-6"/></svg>
                                        </button>
                                        <button wire:click="openActivityModal(null, {{ $act->id }})" class="p-1 text-slate-300 hover:text-indigo-600" title="Agregar subactividad">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        </button>
                                        <button wire:click="deleteActivity({{ $act->id }})" wire:confirm="¿Eliminar actividad?" class="p-1 text-slate-300 hover:text-red-500">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-8 text-center text-slate-400 text-sm">Sin actividades. Agrega la primera.</td></tr>
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
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Inicio</label>
                        <input wire:model="actStartDate" type="date" class="w-full mt-1 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Fin</label>
                        <input wire:model="actEndDate" type="date" class="w-full mt-1 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm">
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

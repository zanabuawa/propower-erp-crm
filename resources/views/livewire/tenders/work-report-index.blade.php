<div class="min-h-screen bg-slate-50/50 -m-4 lg:-m-6">
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-lg font-bold text-slate-800">Reportes Semanales de Obra</h1>
                <p class="text-[11px] text-slate-400 uppercase tracking-wider">Control de avance</p>
            </div>
            @can('manage work reports')
            <button type="button" wire:click="openModal()"
                class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold px-4 py-2 rounded-xl transition-all shadow-lg shadow-indigo-500/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nuevo Reporte
            </button>
            @endcan
        </div>
    </div>

    <div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8 space-y-4">
        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl text-sm font-bold">{{ session('success') }}</div>
        @endif

        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar por proyecto..."
                class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-4 focus:ring-indigo-500/5 focus:border-indigo-500">
        </div>

        <div class="space-y-3">
            @forelse($reports as $r)
                <div class="bg-white rounded-2xl border border-slate-200 p-5 hover:border-indigo-200 transition-colors">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <span class="font-black text-slate-800 text-sm">{{ $r->project?->name }}</span>
                                <span class="text-[10px] font-black text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-lg">{{ $r->progress_pct }}% avance</span>
                                @if($r->tender) <span class="text-[10px] text-slate-400">{{ $r->tender->folio }}</span> @endif
                            </div>
                            <p class="text-xs text-slate-500 mb-2">Semana: {{ $r->week_start->format('d/m') }} – {{ $r->week_end->format('d/m/Y') }} &bull; {{ $r->workers_count }} trabajadores {{ $r->weather_conditions ? "· $r->weather_conditions" : '' }}</p>
                            @if($r->activities) <p class="text-xs text-slate-600 line-clamp-2"><span class="font-bold">Actividades:</span> {{ $r->activities }}</p> @endif
                            @if($r->issues) <p class="text-xs text-amber-700 mt-1 line-clamp-1"><span class="font-bold">Problemas:</span> {{ $r->issues }}</p> @endif
                        </div>
                        <div class="flex items-center gap-1 shrink-0">
                            <button wire:click="openModal({{ $r->id }})" class="p-1.5 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13l6-6"/></svg>
                            </button>
                            <button wire:click="delete({{ $r->id }})" wire:confirm="¿Eliminar reporte?" class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>
                    <div class="mt-3 bg-slate-100 rounded-full h-1.5">
                        <div class="bg-indigo-500 h-1.5 rounded-full" style="width: {{ $r->progress_pct }}%"></div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center text-slate-400 text-sm">Sin reportes semanales.</div>
            @endforelse
            @if($reports->hasPages())
                <div>{{ $reports->links() }}</div>
            @endif
        </div>
    </div>

    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-xl p-6 space-y-4 max-h-[90vh] overflow-y-auto">
            <h3 class="text-base font-black text-slate-800">{{ $editingId ? 'Editar' : 'Nuevo' }} Reporte Semanal</h3>
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Proyecto *</label>
                    <select wire:model="project_id" class="w-full mt-1 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                        <option value="">— Seleccionar —</option>
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                    @error('project_id') <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Inicio semana</label>
                    <input wire:model="week_start" type="date" class="w-full mt-1 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Fin semana</label>
                    <input wire:model="week_end" type="date" class="w-full mt-1 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Avance % *</label>
                    <input wire:model="progress_pct" type="number" min="0" max="100" class="w-full mt-1 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-center font-bold">
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Trabajadores</label>
                    <input wire:model="workers_count" type="number" min="0" class="w-full mt-1 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-center">
                </div>
                <div class="col-span-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Condiciones climáticas</label>
                    <input wire:model="weather_conditions" type="text" placeholder="Soleado, lluvia ligera..." class="w-full mt-1 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                </div>
                <div class="col-span-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Actividades realizadas</label>
                    <textarea wire:model="activities" rows="3" class="w-full mt-1 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm resize-none"></textarea>
                </div>
                <div class="col-span-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Problemas / Incidencias</label>
                    <textarea wire:model="issues" rows="2" class="w-full mt-1 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm resize-none"></textarea>
                </div>
                <div class="col-span-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Plan siguiente semana</label>
                    <textarea wire:model="next_week_plan" rows="2" class="w-full mt-1 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm resize-none"></textarea>
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" wire:click="save" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black py-3 rounded-xl transition-all">Guardar</button>
                <button type="button" wire:click="$set('showModal', false)" class="px-4 py-3 border border-slate-200 text-slate-600 text-xs font-bold rounded-xl hover:bg-slate-50">Cancelar</button>
            </div>
        </div>
    </div>
    @endif
</div>

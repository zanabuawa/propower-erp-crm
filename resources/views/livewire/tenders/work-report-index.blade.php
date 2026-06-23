<div class="{{ $embedded ? 'space-y-4' : 'min-h-screen bg-slate-50/50 -m-4 lg:-m-6' }}">
    @if(!$embedded)
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-lg font-bold text-slate-800">Reportes Semanales de Obra</h1>
                <p class="text-[11px] text-slate-400 uppercase tracking-wider">Formato semanal del proyecto</p>
            </div>
            @can('manage work reports')
            <button type="button" wire:click="openModal()"
                class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold px-4 py-2 rounded-xl transition-all shadow-lg shadow-indigo-500/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nuevo reporte
            </button>
            @endcan
        </div>
    </div>
    @else
    <div class="flex items-center justify-between gap-4">
        <div>
            <h2 class="text-sm font-semibold text-slate-800">Reportes semanales</h2>
            <p class="text-xs text-slate-400">Captura el contenido semanal del reporte del proyecto.</p>
        </div>
        @can('manage work reports')
        <button type="button" wire:click="openModal()"
            class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold px-4 py-2 rounded-xl transition-all">
            Nuevo reporte
        </button>
        @endcan
    </div>
    @endif

    <div class="{{ $embedded ? 'space-y-4' : 'max-w-7xl mx-auto p-4 sm:p-6 lg:p-8 space-y-4' }}">
        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl text-sm font-bold">{{ session('success') }}</div>
        @endif

        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar por proyecto..."
                class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-4 focus:ring-indigo-500/5 focus:border-indigo-500">
        </div>

        <div class="space-y-3">
            @forelse($reports as $report)
                <div class="bg-white rounded-2xl border border-slate-200 p-5 hover:border-indigo-200 transition-colors">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-3 mb-2">
                                <span class="font-black text-slate-800 text-sm truncate">{{ $report->project?->name }}</span>
                                @if($report->tender)
                                    <span class="text-[10px] text-slate-400">{{ $report->tender->folio }}</span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-500 mb-2">
                                Semana: {{ $report->week_start?->format('d/m') }} - {{ $report->week_end?->format('d/m/Y') }}
                            </p>
                            @if($report->custom_body)
                                <p class="text-xs text-emerald-600 line-clamp-2">
                                    Formato libre capturado.
                                </p>
                            @elseif($report->activities)
                                <p class="text-xs text-slate-600 line-clamp-2">
                                    <span class="font-bold">Actividades:</span> {{ $report->activities }}
                                </p>
                            @else
                                <p class="text-xs text-slate-400">Sin contenido capturado. Usa el formato libre para editar el reporte.</p>
                            @endif
                        </div>
                        <div class="flex flex-wrap items-center justify-end gap-2 shrink-0">
                            <a wire:navigate href="{{ route('works.reports.format', $report) }}"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-violet-100 bg-violet-50 text-xs font-bold text-violet-700 hover:bg-violet-100 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6M7 4h7l3 3v13H7V4z"/></svg>
                                Formato
                            </a>
                            <a href="{{ route('works.reports.print', $report) }}" target="_blank"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-indigo-100 bg-indigo-50 text-xs font-bold text-indigo-700 hover:bg-indigo-100 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z"/></svg>
                                Imprimir
                            </a>
                            <button type="button" wire:click="openModal({{ $report->id }})"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-amber-100 bg-amber-50 text-xs font-bold text-amber-700 hover:bg-amber-100 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 7.125L16.875 4.5"/>
                                </svg>
                                Editar
                            </button>
                            <button type="button" wire:click="delete({{ $report->id }})" wire:confirm="Eliminar reporte?"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-red-100 bg-red-50 text-xs font-bold text-red-700 hover:bg-red-100 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Eliminar
                            </button>
                        </div>
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
            <h3 class="text-base font-black text-slate-800">{{ $editingId ? 'Editar' : 'Nuevo' }} reporte semanal</h3>
            <div class="grid grid-cols-2 gap-4">
                @if(!$contextProjectId)
                <div class="col-span-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Proyecto *</label>
                    <select wire:model="project_id" class="w-full mt-1 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                        <option value="">Seleccionar proyecto</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}">{{ $project->name }}</option>
                        @endforeach
                    </select>
                    @error('project_id') <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                @endif

                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Inicio semana *</label>
                    <input wire:model="week_start" type="date" class="w-full mt-1 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                    @error('week_start') <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Fin semana *</label>
                    <input wire:model="week_end" type="date" class="w-full mt-1 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                    @error('week_end') <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p> @enderror
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

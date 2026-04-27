<div class="min-h-screen bg-slate-50/50 -m-4 sm:-m-6 lg:-m-8">
    {{-- ── STICKY HEADER ────────────────────────────────────────────────── --}}
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 max-w-full mx-auto">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-9 h-9 rounded-xl bg-indigo-600 flex items-center justify-center text-white shrink-0 shadow-lg shadow-indigo-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H5a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl font-bold text-slate-800 truncate">Vacantes</h1>
                    <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">Gestión de reclutamiento y selección</p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                @can('create hr')
                <a wire:navigate href="{{ route('hr.job-openings.create') }}"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:scale-[1.02] active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Publicar vacante</span>
                </a>
                @endcan
            </div>
        </div>
    </div>

    <div class="max-w-full mx-auto p-4 sm:p-6 lg:p-8 space-y-8">
        @if(session('success'))
            <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl animate-in fade-in slide-in-from-top-4 duration-300">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-sm font-semibold">{{ session('success') }}</p>
            </div>
        @endif

        {{-- Stats / Quick Filters --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
            @foreach(['open' => 'Abiertas', 'paused' => 'Pausadas', 'closed' => 'Cerradas'] as $k => $v)
                <button wire:click="$set('filterStatus', '{{ $k }}')" 
                    class="bg-white p-5 rounded-3xl border {{ $filterStatus === $k ? 'border-indigo-500 ring-4 ring-indigo-500/5' : 'border-slate-200/60 shadow-sm' }} text-left transition-all duration-200 group">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 group-hover:text-indigo-500 transition-colors">{{ $v }}</p>
                    <p class="text-2xl font-black text-slate-800">{{ $stats[$k] ?? 0 }}</p>
                </button>
            @endforeach
            <button wire:click="$set('filterStatus', '')" 
                class="bg-white p-5 rounded-3xl border {{ $filterStatus === '' ? 'border-indigo-500 ring-4 ring-indigo-500/5' : 'border-slate-200/60 shadow-sm' }} text-left transition-all duration-200 group">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 group-hover:text-indigo-500 transition-colors">Total</p>
                <p class="text-2xl font-black text-slate-800">{{ array_sum($stats->toArray()) }}</p>
            </button>
        </div>

        {{-- Filtros --}}
        <div class="bg-white p-4 rounded-3xl border border-slate-200/60 shadow-sm flex flex-wrap gap-4 items-center">
            <div class="flex-1 min-w-[280px] relative group">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-indigo-500 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar vacante por título..."
                    class="w-full pl-11 pr-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 text-sm">
            </div>
            
            <select wire:model.live="filterType"
                class="px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 text-sm font-bold text-slate-600">
                <option value="">Todos los tipos</option>
                @foreach(\App\Models\HrJobOpening::TYPES as $k => $v)
                    <option value="{{ $k }}">{{ $v }}</option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 animate-in fade-in duration-500">
            @forelse($openings as $opening)
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm hover:shadow-xl hover:shadow-indigo-500/5 transition-all duration-300 group overflow-hidden flex flex-col h-full">
                    <div class="p-6 space-y-4 flex-1">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <h3 class="text-base font-bold text-slate-800 group-hover:text-indigo-600 transition-colors truncate">{{ $opening->title }}</h3>
                                <p class="text-[10px] font-black text-indigo-500 uppercase tracking-widest mt-1">{{ $opening->position->name }}</p>
                            </div>
                            <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest border border-current
                                {{ $opening->status === 'open' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : ($opening->status === 'paused' ? 'bg-amber-50 text-amber-600 border-amber-100' : 'bg-slate-50 text-slate-400 border-slate-100') }}">
                                {{ $opening->status_label }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between py-3 border-y border-slate-50">
                            <div class="flex flex-col">
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-tighter">Candidatos</span>
                                <span class="text-sm font-black text-slate-700">{{ $opening->prospects_count }}</span>
                            </div>
                            <div class="flex flex-col text-right">
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-tighter">Plazas</span>
                                <span class="text-sm font-black text-slate-700">{{ $opening->quantity }}</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 text-[10px] font-bold text-slate-500">
                            <svg class="w-3 h-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>{{ $opening->branch?->name ?? 'Remoto / General' }}</span>
                        </div>

                        @if($opening->salary_range)
                            <div class="flex items-center gap-2 text-[10px] font-black text-emerald-600 uppercase tracking-wider">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>{{ $opening->salary_range }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100 flex items-center justify-between gap-4">
                        <span class="text-[10px] font-bold text-slate-400">{{ $opening->published_at?->diffForHumans() ?? 'No publicada' }}</span>
                        <div class="flex items-center gap-1">
                            @can('edit hr')
                                <button wire:click="toggleStatus({{ $opening->id }})" 
                                        class="p-2 rounded-xl text-slate-400 hover:text-amber-600 hover:bg-white hover:shadow-sm transition-all"
                                        title="{{ $opening->status === 'open' ? 'Pausar' : 'Activar' }}">
                                    @if($opening->status === 'open')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    @else
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    @endif
                                </button>
                                <a wire:navigate href="{{ route('hr.job-openings.edit', $opening) }}" 
                                   class="p-2 rounded-xl text-slate-400 hover:text-indigo-600 hover:bg-white hover:shadow-sm transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </a>
                                <button wire:click="delete({{ $opening->id }})" wire:confirm="¿Seguro que desea eliminar esta vacante?"
                                        class="p-2 rounded-xl text-slate-400 hover:text-red-600 hover:bg-white hover:shadow-sm transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            @endcan
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white rounded-3xl border border-slate-200/60 p-12 text-center space-y-4">
                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto text-slate-300">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H5a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">No hay vacantes</h3>
                        <p class="text-sm text-slate-400 max-w-xs mx-auto">No se encontraron publicaciones que coincidan con los filtros aplicados.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>

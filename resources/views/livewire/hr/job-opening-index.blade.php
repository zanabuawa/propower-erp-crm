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
                    <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">Gestión de plantilla y reclutamiento</p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                @can('edit hr')
                <a wire:navigate href="{{ route('hr.positions.index') }}"
                    class="inline-flex items-center gap-2 bg-white border border-slate-200 hover:border-indigo-300 hover:bg-indigo-50 text-slate-600 hover:text-indigo-700 text-sm font-bold px-4 py-2.5 rounded-xl transition-all duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/></svg>
                    <span class="hidden sm:inline">Gestionar puestos</span>
                </a>
                @endcan
                @can('create hr')
                <a wire:navigate href="{{ route('hr.job-openings.create') }}"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/25 hover:scale-[1.02] active:scale-[0.98]">
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

        {{-- ── PANEL DE CAPACIDAD POR SUCURSAL ──────────────────────────── --}}
        @if($headcounts->count())
        <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-black text-slate-700 uppercase tracking-widest">Plantilla autorizada por sucursal</h2>
                    <p class="text-[10px] text-slate-400 mt-0.5">Plazas autorizadas vs. ocupadas vs. disponibles para reclutamiento</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100">
                            <th class="px-6 py-3 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Sucursal</th>
                            <th class="px-6 py-3 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Puesto</th>
                            <th class="px-6 py-3 text-center text-[10px] font-bold text-slate-400 uppercase tracking-widest">Autorizadas</th>
                            <th class="px-6 py-3 text-center text-[10px] font-bold text-slate-400 uppercase tracking-widest">Ocupadas</th>
                            <th class="px-6 py-3 text-center text-[10px] font-bold text-slate-400 uppercase tracking-widest">En reclutamiento</th>
                            <th class="px-6 py-3 text-center text-[10px] font-bold text-slate-400 uppercase tracking-widest">Disponibles</th>
                            <th class="px-6 py-3 text-center text-[10px] font-bold text-slate-400 uppercase tracking-widest"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($headcounts as $hc)
                        @php $pct = $hc['headcount'] > 0 ? round(($hc['filled'] / $hc['headcount']) * 100) : 0; @endphp
                        <tr class="hover:bg-slate-50/30 transition-colors">
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-lg bg-indigo-50 flex items-center justify-center shrink-0">
                                        <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </div>
                                    <span class="text-sm font-bold text-slate-700">{{ $hc['branch'] }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-3">
                                <span class="text-sm text-slate-600">{{ $hc['position'] }}</span>
                            </td>
                            <td class="px-6 py-3 text-center">
                                <span class="text-lg font-black text-slate-700">{{ $hc['headcount'] }}</span>
                            </td>
                            <td class="px-6 py-3 text-center">
                                <div class="flex flex-col items-center gap-1">
                                    <span class="text-lg font-black {{ $hc['filled'] >= $hc['headcount'] ? 'text-rose-600' : 'text-emerald-600' }}">{{ $hc['filled'] }}</span>
                                    <div class="w-16 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full rounded-full {{ $pct >= 100 ? 'bg-rose-500' : ($pct >= 80 ? 'bg-amber-400' : 'bg-emerald-500') }}"
                                            style="width: {{ min(100, $pct) }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-3 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold
                                    {{ $hc['recruiting'] > 0 ? 'bg-amber-50 text-amber-700' : 'bg-slate-50 text-slate-400' }}">
                                    {{ $hc['recruiting'] }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-center">
                                @php $avail = $hc['available'] - $hc['recruiting']; @endphp
                                <span class="text-lg font-black {{ $avail > 0 ? 'text-indigo-600' : 'text-slate-300' }}">
                                    {{ max(0, $avail) }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-center">
                                @can('edit hr')
                                <a wire:navigate href="{{ route('hr.positions.edit', $hc['position_id']) }}"
                                    class="inline-flex p-1.5 rounded-lg text-slate-300 hover:text-indigo-600 hover:bg-indigo-50 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </a>
                                @endcan
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @else
        <div class="bg-indigo-50/50 border border-indigo-100 rounded-3xl p-6 flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center shrink-0 text-indigo-500">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-sm font-bold text-indigo-700">Sin plantilla definida</p>
                <p class="text-xs text-indigo-500 mt-1">Configura la distribución de plazas por sucursal desde cada puesto laboral para habilitar el control de reclutamiento.</p>
                @can('edit hr')
                <a wire:navigate href="{{ route('hr.positions.index') }}" class="mt-3 inline-block text-xs font-bold text-indigo-600 hover:text-indigo-800 underline underline-offset-2">
                    Ir a puestos laborales →
                </a>
                @endcan
            </div>
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
            <select wire:model.live="filterBranch"
                class="px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 text-sm font-bold text-slate-600">
                <option value="">Todas las sucursales</option>
                @foreach($branches as $b)
                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="filterType"
                class="px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 text-sm font-bold text-slate-600">
                <option value="">Todos los tipos</option>
                @foreach(\App\Models\HrJobOpening::TYPES as $k => $v)
                    <option value="{{ $k }}">{{ $v }}</option>
                @endforeach
            </select>
        </div>

        {{-- Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 animate-in fade-in duration-500">
            @forelse($openings as $opening)
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm hover:shadow-xl hover:shadow-indigo-500/5 transition-all duration-300 group overflow-hidden flex flex-col h-full">
                    <div class="p-6 space-y-4 flex-1">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <h3 class="text-base font-bold text-slate-800 group-hover:text-indigo-600 transition-colors truncate">{{ $opening->title }}</h3>
                                <p class="text-[10px] font-black text-indigo-500 uppercase tracking-widest mt-1">{{ $opening->position->name }}</p>
                            </div>
                            <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest border border-current shrink-0
                                {{ $opening->status === 'open' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : ($opening->status === 'paused' ? 'bg-amber-50 text-amber-600 border-amber-100' : 'bg-slate-50 text-slate-400 border-slate-100') }}">
                                {{ $opening->statusLabel }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between py-3 border-y border-slate-50">
                            <div class="flex flex-col">
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-tighter">Candidatos</span>
                                <span class="text-sm font-black text-slate-700">{{ $opening->prospects_count }}</span>
                            </div>
                            <div class="flex flex-col text-center">
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-tighter">Activos</span>
                                <span class="text-sm font-black text-indigo-600">{{ $opening->active_prospects_count }}</span>
                            </div>
                            <div class="flex flex-col text-right">
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-tighter">Plazas</span>
                                <span class="text-sm font-black {{ $opening->hired_prospects_count >= $opening->quantity ? 'text-emerald-600' : 'text-slate-700' }}">{{ $opening->hired_prospects_count }} / {{ $opening->quantity }}</span>
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
                            <a wire:navigate href="{{ route('hr.prospects.index', ['job_opening_id' => $opening->id]) }}"
                               class="p-2 rounded-xl text-slate-400 hover:text-indigo-600 hover:bg-white hover:shadow-sm transition-all"
                               title="Ver candidatos">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M9 20H4v-2a3 3 0 015.356-1.857M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </a>
                            @can('create hr')
                                @if($opening->status === 'open')
                                    <a wire:navigate href="{{ route('hr.prospects.create', ['job_opening_id' => $opening->id]) }}"
                                       class="p-2 rounded-xl text-slate-400 hover:text-emerald-600 hover:bg-white hover:shadow-sm transition-all"
                                       title="Agregar prospecto">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                    </a>
                                @endif
                            @endcan
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

        @if($openings->hasPages())
            <div class="bg-white rounded-2xl border border-slate-200/60 px-6 py-4">
                {{ $openings->links() }}
            </div>
        @endif
    </div>

</div>

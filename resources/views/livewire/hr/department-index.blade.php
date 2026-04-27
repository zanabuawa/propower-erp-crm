<div class="min-h-screen bg-slate-50/50 -m-4 sm:-m-6 lg:-m-8">
    {{-- ── STICKY HEADER ────────────────────────────────────────────────── --}}
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 max-w-full mx-auto">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-9 h-9 rounded-xl bg-indigo-600 flex items-center justify-center text-white shrink-0 shadow-lg shadow-indigo-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H5a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl font-bold text-slate-800 truncate">Departamentos</h1>
                    <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">Estructura organizacional de la empresa</p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                @can('create hr')
                <a wire:navigate href="{{ route('hr.departments.create') }}"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:scale-[1.02] active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Nuevo departamento</span>
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

        <div class="bg-white p-4 rounded-3xl border border-slate-200/60 shadow-sm flex flex-wrap gap-4 items-center">
            <div class="flex-1 min-w-[280px] relative group">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-indigo-500 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar departamento por nombre o código..."
                    class="w-full pl-11 pr-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 text-sm">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 animate-in fade-in duration-500">
            @forelse($departments as $dept)
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm hover:shadow-xl hover:shadow-indigo-500/5 transition-all duration-300 group overflow-hidden flex flex-col h-full {{ !$dept->is_active ? 'opacity-75' : '' }}">
                    <div class="p-6 space-y-4 flex-1">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <h3 class="text-base font-bold text-slate-800 group-hover:text-indigo-600 transition-colors truncate">{{ $dept->name }}</h3>
                                @if($dept->code)
                                    <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest mt-1 font-mono">{{ $dept->code }}</p>
                                @endif
                            </div>
                            <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider {{ $dept->is_active ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-slate-50 text-slate-400 border-slate-100' }} border shrink-0">
                                {{ $dept->is_active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </div>

                        @if($dept->description)
                            <p class="text-xs text-slate-500 font-medium leading-relaxed line-clamp-2 italic">"{{ $dept->description }}"</p>
                        @endif

                        <div class="pt-4 border-t border-slate-50 flex items-center justify-between">
                            <div class="flex flex-col">
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-tighter">Personal</span>
                                <span class="text-sm font-black text-slate-700">{{ $dept->employees_count }} emp.</span>
                            </div>
                            @if($dept->manager)
                                <div class="flex flex-col text-right">
                                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-tighter">Responsable</span>
                                    <span class="text-xs font-bold text-indigo-600 truncate max-w-[120px]" title="{{ $dept->manager->full_name }}">{{ $dept->manager->full_name }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            @can('edit hr')
                                <a wire:navigate href="{{ route('hr.departments.edit', $dept) }}"
                                   class="p-2 rounded-xl text-slate-400 hover:text-indigo-600 hover:bg-white hover:shadow-sm transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </a>
                                <button wire:click="toggleActive({{ $dept->id }})" 
                                        class="p-2 rounded-xl text-slate-400 hover:{{ $dept->is_active ? 'text-amber-600' : 'text-emerald-600' }} hover:bg-white hover:shadow-sm transition-all">
                                    @if($dept->is_active)
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                    @else
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    @endif
                                </button>
                            @endcan
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white rounded-3xl border border-slate-200/60 p-12 text-center space-y-4">
                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto text-slate-300">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H5a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">No hay departamentos</h3>
                        <p class="text-sm text-slate-400 max-w-xs mx-auto">No se encontraron departamentos registrados o que coincidan con la búsqueda.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>

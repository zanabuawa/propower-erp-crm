<div class="min-h-screen bg-slate-50/50 -m-4 sm:-m-6 lg:-m-8">
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-9 h-9 rounded-xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0120 8.414V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl font-bold text-slate-800 truncate">Plantillas de contrato</h1>
                    <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">Formatos reutilizables para contratos laborales</p>
                </div>
            </div>
            <a wire:navigate href="{{ route('hr.contract-templates.create') }}"
                class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white text-sm font-bold px-5 py-2.5 rounded-xl shadow-lg shadow-indigo-500/25">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                <span>Nueva plantilla</span>
            </a>
        </div>
    </div>

    <div class="p-4 sm:p-6 lg:p-8 space-y-6">
        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl text-sm font-semibold">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white p-4 rounded-3xl border border-slate-200/60 shadow-sm">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar plantilla..."
                class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 text-sm">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            @forelse($templates as $template)
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm p-6 space-y-5">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <h3 class="text-base font-black text-slate-800 truncate">{{ $template->name }}</h3>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-indigo-500 mt-1">{{ $template->code ?: 'Sin clave' }}</p>
                        </div>
                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider {{ $template->is_active ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-slate-100 text-slate-400 border border-slate-200' }}">
                            {{ $template->is_active ? 'Activa' : 'Inactiva' }}
                        </span>
                    </div>

                    <p class="text-sm text-slate-500 min-h-[2.5rem] line-clamp-2">{{ $template->description ?: 'Sin descripcion adicional.' }}</p>

                    <div class="grid grid-cols-3 gap-3">
                        <div class="rounded-2xl bg-slate-50 border border-slate-100 p-3">
                            <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">Tipo</p>
                            <p class="text-xs font-bold text-slate-700 mt-1">{{ \App\Models\HrContract::TYPES[$template->contract_type] ?? $template->contract_type }}</p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 border border-slate-100 p-3">
                            <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">Duracion</p>
                            <p class="text-xs font-bold text-slate-700 mt-1">{{ $template->duration_label }}</p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 border border-slate-100 p-3">
                            <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">Jornada</p>
                            <p class="text-xs font-bold text-slate-700 mt-1">{{ ucfirst($template->work_shift) }}</p>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="toggleActive({{ $template->id }})"
                            class="px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-500 hover:text-indigo-600 hover:border-indigo-100">
                            {{ $template->is_active ? 'Desactivar' : 'Activar' }}
                        </button>
                        <a wire:navigate href="{{ route('hr.contract-templates.edit', $template) }}"
                            class="px-3 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold">
                            Editar
                        </a>
                    </div>
                </div>
            @empty
                <div class="md:col-span-2 xl:col-span-3 bg-white rounded-3xl border border-dashed border-slate-200 p-10 text-center">
                    <p class="text-sm font-bold text-slate-500">Todavia no hay plantillas de contrato.</p>
                </div>
            @endforelse
        </div>

        @if($templates->hasPages())
            {{ $templates->links('vendor.pagination.tailwind') }}
        @endif
    </div>
</div>

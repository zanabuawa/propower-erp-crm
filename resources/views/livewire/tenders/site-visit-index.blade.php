<div class="min-h-screen bg-slate-50/50 -m-4 lg:-m-6">
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-lg font-bold text-slate-800">Visitas de Campo</h1>
                <p class="text-[11px] text-slate-400 uppercase tracking-wider">Control de visitas técnicas</p>
            </div>
            @can('create tenders')
            <a wire:navigate href="{{ route('tenders.visits.create') }}"
                class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold px-4 py-2 rounded-xl transition-all shadow-lg shadow-indigo-500/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nueva Visita
            </a>
            @endcan
        </div>
    </div>

    <div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8 space-y-4">
        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl text-sm font-bold">{{ session('success') }}</div>
        @endif

        <div class="flex flex-wrap gap-3">
            <div class="relative flex-1 min-w-48">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar por propósito o dirección..."
                    class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-4 focus:ring-indigo-500/5 focus:border-indigo-500">
            </div>
            <select wire:model.live="filterStatus" class="px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm">
                <option value="">Todos los estados</option>
                @foreach($statuses as $k => $v)
                    <option value="{{ $k }}">{{ $v }}</option>
                @endforeach
            </select>
            <select wire:model.live="filterType" class="px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm">
                <option value="">Todos los tipos</option>
                @foreach($types as $k => $v)
                    <option value="{{ $k }}">{{ $v }}</option>
                @endforeach
            </select>
        </div>

        <div class="space-y-3">
            @forelse($visits as $v)
                <div class="bg-white rounded-2xl border border-slate-200 p-5 hover:border-indigo-200 transition-colors">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-1">
                                <span class="font-black text-slate-800 text-sm">{{ $v->visit_date->format('d/m/Y') }} — {{ $types[$v->visit_type] ?? $v->visit_type }}</span>
                                @php $colorMap = ['programada'=>'blue','realizada'=>'emerald','cancelada'=>'red']; $c = $colorMap[$v->status] ?? 'slate'; @endphp
                                <span class="px-2 py-0.5 rounded-lg text-[10px] font-black uppercase bg-{{ $c }}-50 text-{{ $c }}-600">{{ $statuses[$v->status] ?? $v->status }}</span>
                            </div>
                            <p class="text-xs font-bold text-slate-600">{{ $v->purpose }}</p>
                            @if($v->address) <p class="text-[10px] text-slate-400 mt-1">📍 {{ $v->address }}</p> @endif
                            <div class="flex items-center gap-3 mt-1.5">
                                @if($v->project) <span class="text-[10px] text-indigo-600 font-bold">{{ $v->project->name }}</span> @endif
                                @if($v->tender) <span class="text-[10px] text-slate-400">{{ $v->tender->folio }}</span> @endif
                                @if(count($v->attendees ?? [])) <span class="text-[10px] text-slate-400">{{ count($v->attendees) }} asistente(s)</span> @endif
                                @if(count($v->photos ?? [])) <span class="text-[10px] text-slate-400">📷 {{ count($v->photos) }} foto(s)</span> @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-1 shrink-0">
                            @can('edit tenders')
                            <a wire:navigate href="{{ route('tenders.visits.edit', $v) }}"
                                class="p-1.5 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13l6-6"/></svg>
                            </a>
                            @endcan
                            @can('delete tenders')
                            <button wire:click="delete({{ $v->id }})" wire:confirm="¿Eliminar visita?"
                                class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                            @endcan
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center text-slate-400 text-sm">Sin visitas registradas.</div>
            @endforelse
            @if($visits->hasPages())
                <div>{{ $visits->links() }}</div>
            @endif
        </div>
    </div>
</div>

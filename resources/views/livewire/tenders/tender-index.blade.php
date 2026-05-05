<div class="min-h-screen bg-slate-50/50 -m-4 lg:-m-6">
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-lg font-bold text-slate-800">Licitaciones</h1>
                <p class="text-[11px] text-slate-400 uppercase tracking-wider">Control de procesos licitatorios</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('tenders.report.print', request()->query()) }}" target="_blank"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-white hover:bg-slate-50 text-slate-700 text-xs font-medium rounded-xl border border-slate-200 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Imprimir
                </a>
                @can('create tenders')
                <a wire:navigate href="{{ route('tenders.create') }}"
                    class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold px-4 py-2 rounded-xl transition-all shadow-lg shadow-indigo-500/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Nueva Licitación
                </a>
                @endcan
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8 space-y-4">
        @if(session('success'))
            <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl text-sm font-bold">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- Filtros --}}
        <div class="flex flex-wrap gap-3">
            <div class="relative flex-1 min-w-48">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar por folio o nombre..."
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

        {{-- Tabla --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-100">
                        <th class="text-left px-4 py-3 text-[10px] font-black text-slate-400 uppercase tracking-wider">Folio</th>
                        <th class="text-left px-4 py-3 text-[10px] font-black text-slate-400 uppercase tracking-wider">Nombre</th>
                        <th class="text-left px-4 py-3 text-[10px] font-black text-slate-400 uppercase tracking-wider">Tipo</th>
                        <th class="text-left px-4 py-3 text-[10px] font-black text-slate-400 uppercase tracking-wider">Estado</th>
                        <th class="text-left px-4 py-3 text-[10px] font-black text-slate-400 uppercase tracking-wider">Cliente</th>
                        <th class="text-right px-4 py-3 text-[10px] font-black text-slate-400 uppercase tracking-wider">Presup.</th>
                        <th class="text-left px-4 py-3 text-[10px] font-black text-slate-400 uppercase tracking-wider">Entrega</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($tenders as $tender)
                        @php
                            $colorMap = ['gray'=>'slate','blue'=>'blue','yellow'=>'amber','green'=>'emerald','orange'=>'orange','red'=>'red'];
                            $c = $colorMap[$tender->statusColor] ?? 'slate';
                        @endphp
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-4 py-3">
                                <span class="font-mono text-xs font-bold text-slate-600">{{ $tender->folio }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <a wire:navigate href="{{ route('tenders.show', $tender) }}" class="font-bold text-slate-800 hover:text-indigo-600 transition-colors text-xs">
                                    {{ $tender->name }}
                                </a>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-xs text-slate-500">{{ $types[$tender->type] ?? $tender->type }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-2 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-{{ $c }}-50 text-{{ $c }}-600">
                                    {{ $statuses[$tender->status] ?? $tender->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-600">{{ $tender->customer?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-right">
                                <span class="text-xs font-bold text-slate-700">
                                    {{ $tender->estimated_budget ? '$' . number_format($tender->estimated_budget, 0) : '—' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-500">
                                {{ $tender->submission_date?->format('d/m/Y') ?? '—' }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <a wire:navigate href="{{ route('tenders.show', $tender) }}"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    @can('edit tenders')
                                    <a wire:navigate href="{{ route('tenders.edit', $tender) }}"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13l6-6m0 0l3.536 3.536M9 13H6v3h3"/></svg>
                                    </a>
                                    @endcan
                                    @can('delete tenders')
                                    <button wire:click="delete({{ $tender->id }})" wire:confirm="¿Eliminar esta licitación?"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-4 py-12 text-center text-slate-400 text-sm">Sin licitaciones registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($tenders->hasPages())
                <div class="px-4 py-3 border-t border-slate-100">{{ $tenders->links() }}</div>
            @endif
        </div>
    </div>
</div>

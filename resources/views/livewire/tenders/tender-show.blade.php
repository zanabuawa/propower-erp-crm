<div class="min-h-screen bg-slate-50/50 -m-4 lg:-m-6">
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a wire:navigate href="{{ route('tenders.index') }}"
                    class="flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <div>
                    <h1 class="text-lg font-bold text-slate-800">{{ $tender->name }}</h1>
                    <p class="text-[11px] text-slate-400 uppercase tracking-wider font-mono">{{ $tender->folio }}</p>
                </div>
            </div>
            <div class="flex gap-2">
                @can('create tenders')
                <a wire:navigate href="{{ route('tenders.quotations.create', $tender) }}"
                    class="inline-flex items-center gap-2 px-3 py-2 bg-white border border-slate-200 text-slate-600 text-xs font-bold rounded-xl hover:border-indigo-200 hover:text-indigo-600 transition-all">
                    Nueva Cotización
                </a>
                @endcan
                @can('edit tenders')
                <a wire:navigate href="{{ route('tenders.edit', $tender) }}"
                    class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold px-4 py-2 rounded-xl transition-all shadow-lg shadow-indigo-500/20">
                    Editar
                </a>
                @endcan
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8 space-y-6">
        {{-- Stats Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            @php
                $colorMap = ['gray'=>'slate','blue'=>'blue','yellow'=>'amber','green'=>'emerald','orange'=>'orange','red'=>'red'];
                $c = $colorMap[$tender->statusColor] ?? 'slate';
            @endphp
            <div class="bg-white rounded-2xl border border-slate-200 p-4">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Estado</p>
                <p class="mt-1 text-sm font-black text-{{ $c }}-600">{{ $statuses[$tender->status] ?? $tender->status }}</p>
                <p class="text-[10px] text-slate-400 mt-1">{{ $types[$tender->type] ?? $tender->type }}</p>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 p-4">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Partidas</p>
                <p class="mt-1 text-2xl font-black text-slate-800">{{ $tender->items->count() }}</p>
                <p class="text-[10px] text-slate-400 mt-1">Total: ${{ number_format($tender->total, 2) }}</p>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 p-4">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Presupuesto</p>
                <p class="mt-1 text-lg font-black text-slate-800">${{ $tender->estimated_budget ? number_format($tender->estimated_budget, 0) : '—' }}</p>
                <p class="text-[10px] text-slate-400 mt-1">Entrega: {{ $tender->submission_date?->format('d/m/Y') ?? '—' }}</p>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 p-4">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Cotizaciones</p>
                <p class="mt-1 text-2xl font-black text-slate-800">{{ $tender->quotations->count() }}</p>
                <p class="text-[10px] text-slate-400 mt-1">{{ $tender->customer?->name ?? 'Sin cliente' }}</p>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="flex overflow-x-auto border-b border-slate-100 px-4 gap-1 pt-2">
                @foreach(['partidas' => 'Partidas', 'cotizaciones' => 'Cotizaciones', 'permisos' => 'Permisos', 'reportes' => 'Reportes', 'libranzas' => 'Libranzas', 'visitas' => 'Visitas'] as $tab => $label)
                    <button wire:click="$set('activeTab', '{{ $tab }}')"
                        class="shrink-0 px-4 py-2.5 text-xs font-black uppercase tracking-wider border-b-2 transition-colors {{ $activeTab === $tab ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-400 hover:text-slate-600' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            <div class="p-6">
                @if($activeTab === 'partidas')
                    <table class="w-full text-sm">
                        <thead><tr class="border-b border-slate-100">
                            <th class="text-left pb-2 text-[10px] font-black text-slate-400 uppercase">Código</th>
                            <th class="text-left pb-2 text-[10px] font-black text-slate-400 uppercase">Descripción</th>
                            <th class="text-left pb-2 text-[10px] font-black text-slate-400 uppercase">Unidad</th>
                            <th class="text-right pb-2 text-[10px] font-black text-slate-400 uppercase">Cantidad</th>
                            <th class="text-right pb-2 text-[10px] font-black text-slate-400 uppercase">P. Unit.</th>
                            <th class="text-right pb-2 text-[10px] font-black text-slate-400 uppercase">Total</th>
                        </tr></thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($tender->items as $item)
                                <tr>
                                    <td class="py-2 text-xs font-mono text-slate-500">{{ $item->code ?? '—' }}</td>
                                    <td class="py-2 text-xs font-bold text-slate-800">{{ $item->description }}</td>
                                    <td class="py-2 text-xs text-slate-500">{{ $item->unit ?? '—' }}</td>
                                    <td class="py-2 text-right text-xs font-mono">{{ number_format($item->quantity, 2) }}</td>
                                    <td class="py-2 text-right text-xs font-mono">${{ number_format($item->unit_price, 2) }}</td>
                                    <td class="py-2 text-right text-xs font-bold">${{ number_format($item->total, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="py-8 text-center text-slate-400 text-xs">Sin partidas</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot class="border-t-2 border-slate-200"><tr>
                            <td colspan="5" class="pt-3 text-right text-xs font-black text-slate-600 uppercase">Total licitación</td>
                            <td class="pt-3 text-right text-sm font-black text-indigo-600">${{ number_format($tender->total, 2) }}</td>
                        </tr></tfoot>
                    </table>

                @elseif($activeTab === 'cotizaciones')
                    <div class="space-y-3">
                        @forelse($tender->quotations as $q)
                            <div class="flex items-center justify-between p-4 rounded-2xl border border-slate-100 bg-slate-50/30">
                                <div>
                                    <p class="font-black text-slate-800 text-sm">{{ $q->folio }} — {{ $q->issuingCompany?->name }}</p>
                                    <p class="text-xs text-slate-400 mt-0.5">
                                        Total: ${{ number_format($q->total, 2) }} &bull; Válida hasta: {{ $q->valid_until?->format('d/m/Y') ?? '—' }}
                                    </p>
                                </div>
                                <span class="px-2 py-1 rounded-lg text-[10px] font-black uppercase {{ match($q->status) { 'aceptada'=>'bg-emerald-50 text-emerald-600', 'rechazada'=>'bg-red-50 text-red-600', 'enviada'=>'bg-blue-50 text-blue-600', default=>'bg-slate-100 text-slate-500' } }}">
                                    {{ $q->status }}
                                </span>
                            </div>
                        @empty
                            <p class="text-center text-slate-400 text-sm py-8">Sin cotizaciones. <a wire:navigate href="{{ route('tenders.quotations.create', $tender) }}" class="text-indigo-600 font-bold hover:underline">Crear una</a></p>
                        @endforelse
                    </div>

                @elseif($activeTab === 'permisos')
                    <div class="space-y-2">
                        @forelse($tender->workPermits as $p)
                            <div class="flex items-center justify-between p-3 rounded-xl border border-slate-100">
                                <div>
                                    <p class="font-bold text-slate-800 text-xs">{{ \App\Models\WorkPermit::TYPES[$p->type] ?? $p->type }}</p>
                                    <p class="text-[10px] text-slate-400">{{ $p->description }} &bull; {{ $p->valid_from->format('d/m/Y') }} – {{ $p->valid_until->format('d/m/Y') }}</p>
                                </div>
                                <span class="px-2 py-1 rounded-lg text-[10px] font-black uppercase {{ $p->status === 'activo' ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-500' }}">{{ $p->status }}</span>
                            </div>
                        @empty
                            <p class="text-center text-slate-400 text-sm py-6">Sin permisos de trabajo registrados</p>
                        @endforelse
                    </div>

                @elseif($activeTab === 'reportes')
                    <div class="space-y-2">
                        @forelse($tender->workReports as $r)
                            <div class="p-3 rounded-xl border border-slate-100">
                                <div class="flex items-center justify-between">
                                    <p class="font-bold text-slate-800 text-xs">Semana {{ $r->week_start->format('d/m') }} – {{ $r->week_end->format('d/m/Y') }}</p>
                                    <span class="text-xs font-black text-indigo-600">{{ $r->progress_pct }}%</span>
                                </div>
                                @if($r->activities)<p class="text-[10px] text-slate-500 mt-1 line-clamp-2">{{ $r->activities }}</p>@endif
                            </div>
                        @empty
                            <p class="text-center text-slate-400 text-sm py-6">Sin reportes semanales</p>
                        @endforelse
                    </div>

                @elseif($activeTab === 'libranzas')
                    <div class="space-y-2">
                        @forelse($tender->workLibranzas as $l)
                            <div class="flex items-center justify-between p-3 rounded-xl border border-slate-100">
                                <div>
                                    <p class="font-bold text-slate-800 text-xs">Estimación #{{ $l->number }} — {{ $l->concept }}</p>
                                    <p class="text-[10px] text-slate-400">{{ $l->period_start->format('d/m') }} – {{ $l->period_end->format('d/m/Y') }} &bull; ${{ number_format($l->amount, 2) }}</p>
                                </div>
                                <span class="px-2 py-1 rounded-lg text-[10px] font-black uppercase {{ $l->status === 'aprobada' ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-500' }}">{{ $l->status }}</span>
                            </div>
                        @empty
                            <p class="text-center text-slate-400 text-sm py-6">Sin libranzas / estimaciones</p>
                        @endforelse
                    </div>

                @elseif($activeTab === 'visitas')
                    <div class="space-y-2">
                        @forelse($tender->siteVisits as $v)
                            <div class="flex items-center justify-between p-3 rounded-xl border border-slate-100">
                                <div>
                                    <p class="font-bold text-slate-800 text-xs">{{ $v->visit_date->format('d/m/Y') }} — {{ \App\Models\SiteVisit::TYPES[$v->visit_type] ?? $v->visit_type }}</p>
                                    <p class="text-[10px] text-slate-400">{{ $v->purpose }}</p>
                                </div>
                                <span class="px-2 py-1 rounded-lg text-[10px] font-black uppercase {{ $v->status === 'realizada' ? 'bg-emerald-50 text-emerald-600' : 'bg-blue-50 text-blue-600' }}">{{ $v->status }}</span>
                            </div>
                        @empty
                            <p class="text-center text-slate-400 text-sm py-6">Sin visitas de campo</p>
                        @endforelse
                    </div>
                @endif
            </div>
        </div>

        {{-- Retroalimentación --}}
        @if($tender->feedback)
        <div class="bg-amber-50 border border-amber-100 rounded-2xl p-5">
            <p class="text-[10px] font-black text-amber-600 uppercase tracking-widest mb-2">Retroalimentación</p>
            <p class="text-sm text-amber-800">{{ $tender->feedback }}</p>
        </div>
        @endif
    </div>
</div>

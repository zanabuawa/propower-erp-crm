<div class="min-h-screen bg-slate-50/50 -m-4 lg:-m-6">
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-lg font-bold text-slate-800">Libranzas / Estimaciones</h1>
                <p class="text-[11px] text-slate-400 uppercase tracking-wider">Certificados de pago por avance</p>
            </div>
            <button type="button" wire:click="openModal()"
                class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold px-4 py-2 rounded-xl transition-all shadow-lg shadow-indigo-500/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nueva Libranza
            </button>
        </div>
    </div>

    <div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8 space-y-4">
        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl text-sm font-bold">{{ session('success') }}</div>
        @endif

        <div class="flex flex-wrap gap-3">
            <div class="relative flex-1 min-w-48">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar por concepto..."
                    class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-4 focus:ring-indigo-500/5 focus:border-indigo-500">
            </div>
            <select wire:model.live="filterStatus" class="px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm">
                <option value="">Todos los estados</option>
                @foreach($statuses as $k => $v)
                    <option value="{{ $k }}">{{ $v }}</option>
                @endforeach
            </select>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead><tr class="bg-slate-50/50 border-b border-slate-100">
                    <th class="text-left px-4 py-3 text-[10px] font-black text-slate-400 uppercase">#</th>
                    <th class="text-left px-4 py-3 text-[10px] font-black text-slate-400 uppercase">Concepto</th>
                    <th class="text-left px-4 py-3 text-[10px] font-black text-slate-400 uppercase">Proyecto</th>
                    <th class="text-left px-4 py-3 text-[10px] font-black text-slate-400 uppercase">Período</th>
                    <th class="text-right px-4 py-3 text-[10px] font-black text-slate-400 uppercase">Monto</th>
                    <th class="text-right px-4 py-3 text-[10px] font-black text-slate-400 uppercase">Avance %</th>
                    <th class="text-left px-4 py-3 text-[10px] font-black text-slate-400 uppercase">Estado</th>
                    <th class="px-4 py-3"></th>
                </tr></thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($libranzas as $l)
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-4 py-3 text-xs font-black text-slate-500">#{{ $l->number }}</td>
                            <td class="px-4 py-3 text-xs font-bold text-slate-800">{{ $l->concept }}</td>
                            <td class="px-4 py-3 text-xs text-slate-500">{{ $l->project?->name }}</td>
                            <td class="px-4 py-3 text-xs text-slate-500">{{ $l->period_start->format('d/m') }} – {{ $l->period_end->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-right text-xs font-black text-slate-800">${{ number_format($l->amount, 2) }}</td>
                            <td class="px-4 py-3 text-right text-xs font-bold text-indigo-600">{{ $l->advance_pct }}%</td>
                            <td class="px-4 py-3">
                                @php $colors = ['borrador'=>'slate','enviada'=>'blue','aprobada'=>'emerald','pagada'=>'green']; $c = $colors[$l->status] ?? 'slate'; @endphp
                                <span class="inline-flex px-2 py-1 rounded-lg text-[10px] font-black uppercase bg-{{ $c }}-50 text-{{ $c }}-600">{{ $statuses[$l->status] ?? $l->status }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    @if($l->status === 'enviada')
                                    @can('approve libranzas')
                                    <button wire:click="approve({{ $l->id }})" wire:confirm="¿Aprobar libranza? Se generará una entrada proyectada en el flujo de caja."
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 transition-colors" title="Aprobar y proyectar flujo">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    </button>
                                    @endcan
                                    @endif
                                    @if($l->status === 'aprobada')
                                    @can('approve libranzas')
                                    <button wire:click="markPaid({{ $l->id }})" wire:confirm="¿Marcar como pagada? Se registrará el ingreso en finanzas."
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition-colors" title="Marcar pagada y registrar ingreso">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </button>
                                    @endcan
                                    @endif
                                    @if($l->status === 'pagada')
                                    <span class="p-1.5 text-emerald-500" title="Registrada en finanzas">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </span>
                                    @endif
                                    <button wire:click="openModal({{ $l->id }})" class="p-1.5 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13l6-6"/></svg>
                                    </button>
                                    <button wire:click="delete({{ $l->id }})" wire:confirm="¿Eliminar libranza?" class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-4 py-12 text-center text-slate-400 text-sm">Sin libranzas registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($libranzas->hasPages())
                <div class="px-4 py-3 border-t border-slate-100">{{ $libranzas->links() }}</div>
            @endif
        </div>
    </div>

    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg p-6 space-y-4 max-h-[90vh] overflow-y-auto">
            <h3 class="text-base font-black text-slate-800">{{ $editingId ? 'Editar' : 'Nueva' }} Libranza</h3>
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
                <div class="col-span-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Concepto *</label>
                    <input wire:model="concept" type="text" class="w-full mt-1 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                    @error('concept') <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Período inicio</label>
                    <input wire:model="period_start" type="date" class="w-full mt-1 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Período fin</label>
                    <input wire:model="period_end" type="date" class="w-full mt-1 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Monto *</label>
                    <input wire:model="amount" type="number" step="0.01" min="0" class="w-full mt-1 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-right font-mono">
                    @error('amount') <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Avance %</label>
                    <input wire:model="advance_pct" type="number" step="0.01" min="0" max="100" class="w-full mt-1 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-center">
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Estado</label>
                    <select wire:model="status" class="w-full mt-1 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                        @foreach($statuses as $k => $v)
                            <option value="{{ $k }}">{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-span-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Notas</label>
                    <textarea wire:model="notes" rows="2" class="w-full mt-1 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm resize-none"></textarea>
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

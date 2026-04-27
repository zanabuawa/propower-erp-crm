<div class="min-h-screen bg-slate-50/50 -m-4 lg:-m-6">
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-lg font-bold text-slate-800">Permisos de Trabajo</h1>
                <p class="text-[11px] text-slate-400 uppercase tracking-wider">Control de obra</p>
            </div>
            @can('manage work permits')
            <button type="button" wire:click="openModal()"
                class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold px-4 py-2 rounded-xl transition-all shadow-lg shadow-indigo-500/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nuevo Permiso
            </button>
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
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar..."
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

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead><tr class="bg-slate-50/50 border-b border-slate-100">
                    <th class="text-left px-4 py-3 text-[10px] font-black text-slate-400 uppercase">Tipo</th>
                    <th class="text-left px-4 py-3 text-[10px] font-black text-slate-400 uppercase">Descripción</th>
                    <th class="text-left px-4 py-3 text-[10px] font-black text-slate-400 uppercase">Proyecto</th>
                    <th class="text-left px-4 py-3 text-[10px] font-black text-slate-400 uppercase">Vigencia</th>
                    <th class="text-left px-4 py-3 text-[10px] font-black text-slate-400 uppercase">Estado</th>
                    <th class="px-4 py-3"></th>
                </tr></thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($permits as $p)
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-4 py-3 text-xs font-bold text-slate-700">{{ $types[$p->type] ?? $p->type }}</td>
                            <td class="px-4 py-3 text-xs text-slate-600 max-w-xs truncate">{{ $p->description }}</td>
                            <td class="px-4 py-3 text-xs text-slate-500">{{ $p->project?->name }}</td>
                            <td class="px-4 py-3 text-xs text-slate-500">{{ $p->valid_from->format('d/m/Y') }} – {{ $p->valid_until->format('d/m/Y') }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-2 py-1 rounded-lg text-[10px] font-black uppercase {{ $p->status === 'activo' ? 'bg-emerald-50 text-emerald-600' : ($p->status === 'vencido' ? 'bg-amber-50 text-amber-600' : 'bg-red-50 text-red-600') }}">
                                    {{ $statuses[$p->status] ?? $p->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <button wire:click="openModal({{ $p->id }})" class="p-1.5 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13l6-6"/></svg>
                                    </button>
                                    <button wire:click="delete({{ $p->id }})" wire:confirm="¿Eliminar permiso?" class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-12 text-center text-slate-400 text-sm">Sin permisos de trabajo.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($permits->hasPages())
                <div class="px-4 py-3 border-t border-slate-100">{{ $permits->links() }}</div>
            @endif
        </div>
    </div>

    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg p-6 space-y-4 max-h-[90vh] overflow-y-auto">
            <h3 class="text-base font-black text-slate-800">{{ $editingId ? 'Editar' : 'Nuevo' }} Permiso de Trabajo</h3>
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
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Tipo *</label>
                    <select wire:model="type" class="w-full mt-1 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                        @foreach($types as $k => $v)
                            <option value="{{ $k }}">{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Estado</label>
                    <select wire:model="status" class="w-full mt-1 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                        @foreach($statuses as $k => $v)
                            <option value="{{ $k }}">{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Desde *</label>
                    <input wire:model="valid_from" type="date" class="w-full mt-1 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Hasta *</label>
                    <input wire:model="valid_until" type="date" class="w-full mt-1 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                    @error('valid_until') <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="col-span-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Descripción *</label>
                    <textarea wire:model="description" rows="2" class="w-full mt-1 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm resize-none"></textarea>
                    @error('description') <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p> @enderror
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

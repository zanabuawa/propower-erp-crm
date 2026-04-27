<div class="min-h-screen bg-slate-50/50 -m-4 lg:-m-6">
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-lg font-bold text-slate-800">Reportes Fotográficos</h1>
                <p class="text-[11px] text-slate-400 uppercase tracking-wider">Evidencia de obra</p>
            </div>
            @can('manage work reports')
            <button type="button" wire:click="openModal()"
                class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold px-4 py-2 rounded-xl transition-all shadow-lg shadow-indigo-500/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nuevo Reporte
            </button>
            @endcan
        </div>
    </div>

    <div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8 space-y-4">
        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl text-sm font-bold">{{ session('success') }}</div>
        @endif

        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar por título..."
                class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-4 focus:ring-indigo-500/5 focus:border-indigo-500">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($reports as $r)
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden hover:border-indigo-200 transition-colors">
                    @php $photos = $r->photoUrls; @endphp
                    @if(count($photos) > 0)
                        <div class="grid grid-cols-2 gap-0.5 h-36 overflow-hidden">
                            @foreach(array_slice($photos, 0, 4) as $pi => $url)
                                <img src="{{ $url }}" class="w-full h-full object-cover {{ count($photos) === 1 ? 'col-span-2' : '' }}">
                            @endforeach
                        </div>
                    @else
                        <div class="h-36 bg-slate-100 flex items-center justify-center">
                            <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                    @endif
                    <div class="p-4">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex-1 min-w-0">
                                <p class="font-black text-slate-800 text-xs truncate">{{ $r->title }}</p>
                                <p class="text-[10px] text-slate-400 mt-0.5">{{ $r->project?->name }} &bull; {{ $r->report_date->format('d/m/Y') }}</p>
                                @if($r->location) <p class="text-[10px] text-slate-400 mt-0.5">📍 {{ $r->location }}</p> @endif
                                <p class="text-[10px] text-indigo-600 mt-1">{{ count($photos) }} foto(s)</p>
                            </div>
                            <div class="flex items-center gap-1 shrink-0">
                                <button wire:click="openModal({{ $r->id }})" class="p-1.5 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13l6-6"/></svg>
                                </button>
                                <button wire:click="delete({{ $r->id }})" wire:confirm="¿Eliminar reporte?" class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-3 bg-white rounded-2xl border border-slate-200 p-12 text-center text-slate-400 text-sm">Sin reportes fotográficos.</div>
            @endforelse
        </div>
        @if($reports->hasPages())
            <div>{{ $reports->links() }}</div>
        @endif
    </div>

    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg p-6 space-y-4 max-h-[90vh] overflow-y-auto">
            <h3 class="text-base font-black text-slate-800">{{ $editingId ? 'Editar' : 'Nuevo' }} Reporte Fotográfico</h3>
            <div class="space-y-3">
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Proyecto *</label>
                    <select wire:model="project_id" class="w-full mt-1 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                        <option value="">— Seleccionar —</option>
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                    @error('project_id') <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Fecha *</label>
                        <input wire:model="report_date" type="date" class="w-full mt-1 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Ubicación</label>
                        <input wire:model="location" type="text" placeholder="Área / zona..." class="w-full mt-1 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                    </div>
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Título *</label>
                    <input wire:model="title" type="text" class="w-full mt-1 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold">
                    @error('title') <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Descripción</label>
                    <textarea wire:model="description" rows="2" class="w-full mt-1 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm resize-none"></textarea>
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Fotos</label>
                    <input wire:model="newPhotos" type="file" multiple accept="image/*"
                        class="w-full mt-1 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-600 file:text-xs file:font-bold">
                    @error('newPhotos.*') <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p> @enderror
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

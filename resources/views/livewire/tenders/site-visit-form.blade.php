<div class="min-h-screen bg-slate-50/50 -m-4 lg:-m-6">
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a wire:navigate href="{{ route('tenders.visits.index') }}"
                    class="flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <div>
                    <h1 class="text-lg font-bold text-slate-800">{{ $siteVisit?->exists ? 'Editar Visita' : 'Nueva Visita de Campo' }}</h1>
                    <p class="text-[11px] text-slate-400 uppercase tracking-wider">Registro técnico</p>
                </div>
            </div>
            <button type="button" wire:click="save"
                class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all shadow-lg shadow-indigo-500/25">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                Guardar
            </button>
        </div>
    </div>

    <div class="max-w-4xl mx-auto p-4 sm:p-6 lg:p-8 space-y-6">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 lg:p-8 space-y-5">
            <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest">Información de la Visita</h3>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Fecha *</label>
                    <input wire:model="visit_date" type="date" class="w-full mt-1 px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Tipo *</label>
                    <select wire:model="visit_type" class="w-full mt-1 px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold">
                        @foreach($types as $k => $v)
                            <option value="{{ $k }}">{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Estado</label>
                    <select wire:model="status" class="w-full mt-1 px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                        @foreach($statuses as $k => $v)
                            <option value="{{ $k }}">{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Propósito / Motivo *</label>
                <input wire:model="purpose" type="text" placeholder="Motivo de la visita..."
                    class="w-full mt-1 px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold focus:ring-4 focus:ring-indigo-500/5 focus:border-indigo-500">
                @error('purpose') <p class="text-[10px] text-red-500 mt-1 ml-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Proyecto</label>
                    <select wire:model="project_id" class="w-full mt-1 px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                        <option value="">— Sin proyecto —</option>
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Licitación</label>
                    <select wire:model="tender_id" class="w-full mt-1 px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                        <option value="">— Sin licitación —</option>
                        @foreach($tenders as $t)
                            <option value="{{ $t->id }}">{{ $t->folio }} — {{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Dirección</label>
                    <input wire:model="address" type="text" placeholder="Calle, número, colonia..."
                        class="w-full mt-1 px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-4 focus:ring-indigo-500/5 focus:border-indigo-500">
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Notas de ubicación</label>
                    <input wire:model="location_notes" type="text" placeholder="Referencias, área específica..."
                        class="w-full mt-1 px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                </div>
            </div>

            <div>
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Asistentes (uno por línea)</label>
                <textarea wire:model="attendeesText" rows="3" placeholder="Nombre y empresa de cada asistente..."
                    class="w-full mt-1 px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm resize-none focus:ring-4 focus:ring-indigo-500/5 focus:border-indigo-500"></textarea>
            </div>

            <div>
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Reporte / Observaciones</label>
                <textarea wire:model="report" rows="4" placeholder="Hallazgos, compromisos, acuerdos..."
                    class="w-full mt-1 px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm resize-none focus:ring-4 focus:ring-indigo-500/5 focus:border-indigo-500"></textarea>
            </div>

            <div>
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Fotografías</label>
                <input wire:model="newPhotos" type="file" multiple accept="image/*"
                    class="w-full mt-1 px-4 py-3 bg-slate-50 border border-dashed border-slate-300 rounded-2xl text-sm file:mr-3 file:py-1.5 file:px-4 file:rounded-xl file:border-0 file:bg-indigo-50 file:text-indigo-600 file:text-xs file:font-bold hover:border-indigo-300 transition-colors">
                @error('newPhotos.*') <p class="text-[10px] text-red-500 mt-1 ml-1">{{ $message }}</p> @enderror

                @if($siteVisit && count($siteVisit->photoUrls))
                    <div class="mt-3 grid grid-cols-4 gap-2">
                        @foreach($siteVisit->photoUrls as $url)
                            <img src="{{ $url }}" class="w-full h-20 object-cover rounded-xl border border-slate-200">
                        @endforeach
                    </div>
                    <p class="text-[9px] text-slate-400 mt-2 ml-1">Las fotos nuevas se agregan a las existentes.</p>
                @endif
            </div>
        </div>
    </div>
</div>

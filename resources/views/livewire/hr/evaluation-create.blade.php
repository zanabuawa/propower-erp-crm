<div class="min-h-screen bg-slate-50/50 -m-4 sm:-m-6 lg:-m-8">
    {{-- ── STICKY HEADER ────────────────────────────────────────────────── --}}
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 max-w-full mx-auto">
            <div class="flex items-center gap-3 min-w-0">
                <a wire:navigate href="{{ route('hr.evaluations.dashboard') }}" class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-indigo-50 hover:text-indigo-600 transition-all shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl font-bold text-slate-800 truncate">Nueva Evaluación de Permiso</h1>
                    <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">Autorización para Seguristas y Supervisores</p>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-3xl mx-auto p-4 sm:p-6 lg:p-8">
        <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
            <div class="p-6 lg:p-8 space-y-8">
                
                {{-- Paso 1: Tipo de Persona --}}
                <div class="space-y-4">
                    <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">1. Seleccionar tipo de evaluado</label>
                    <div class="grid grid-cols-2 gap-4">
                        <button type="button" wire:click="$set('type', 'prospect')" 
                            class="p-4 rounded-2xl border-2 transition-all flex flex-col items-center gap-2 {{ $type === 'prospect' ? 'border-indigo-600 bg-indigo-50 text-indigo-600' : 'border-slate-100 bg-slate-50 text-slate-400 hover:border-slate-200' }}">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span class="text-sm font-bold">Prospecto / Candidato</span>
                        </button>
                        <button type="button" wire:click="$set('type', 'employee')" 
                            class="p-4 rounded-2xl border-2 transition-all flex flex-col items-center gap-2 {{ $type === 'employee' ? 'border-indigo-600 bg-indigo-50 text-indigo-600' : 'border-slate-100 bg-slate-50 text-slate-400 hover:border-slate-200' }}">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <span class="text-sm font-bold">Empleado Interno</span>
                        </button>
                    </div>
                </div>

                {{-- Paso 2: Búsqueda y Selección --}}
                <div class="space-y-4">
                    <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">2. Buscar {{ $type === 'prospect' ? 'prospecto' : 'empleado' }}</label>
                    <div class="relative">
                        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Escribir nombre..."
                            class="w-full px-4 py-3 pl-11 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all text-sm font-medium">
                        <svg class="absolute left-4 top-3.5 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>

                    <div class="grid grid-cols-1 gap-2 max-h-60 overflow-y-auto pr-2">
                        @foreach($items as $item)
                            <button type="button" wire:click="selectItem({{ $item->id }})"
                                class="flex items-center justify-between p-3 rounded-xl border transition-all {{ $selected_id == $item->id ? 'border-indigo-600 bg-indigo-50' : 'border-slate-100 hover:bg-slate-50' }}">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-[10px] font-bold text-slate-500 uppercase">
                                        {{ substr($item->first_name, 0, 1) }}{{ substr($item->last_name, 0, 1) }}
                                    </div>
                                    <div class="text-left">
                                        <p class="text-sm font-bold text-slate-800">{{ $item->full_name }}</p>
                                        <p class="text-[10px] text-slate-400 font-medium uppercase tracking-wider">{{ $item->position->name ?? 'Sin puesto asignado' }}</p>
                                    </div>
                                </div>
                                @if($selected_id == $item->id)
                                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                @endif
                            </button>
                        @endforeach
                    </div>
                    @error('selected_id') <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider ml-1">Debes seleccionar a alguien</p> @enderror
                </div>

                {{-- Paso 3: Clasificación del Examen --}}
                <div class="space-y-4">
                    <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">3. Tipo de evaluación / permiso</label>
                    <select wire:model="test_type"
                        class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all font-bold text-indigo-600">
                        <option value="">Seleccionar clasificación...</option>
                        <option value="segurista">Segurista</option>
                        <option value="supervisor">Supervisor</option>
                        <option value="otro">Otro</option>
                    </select>
                    @error('test_type') <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider ml-1">{{ $message }}</p> @enderror
                </div>

                <div class="pt-6 border-t border-slate-100">
                    <button type="button" wire:click="startProcess"
                        class="w-full flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 rounded-2xl transition-all shadow-lg shadow-indigo-500/25">
                        <span>Iniciar Proceso de Evaluación</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>

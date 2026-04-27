<div class="min-h-screen bg-slate-50/50 -m-4 sm:-m-6 lg:-m-8">
    {{-- ── STICKY HEADER ────────────────────────────────────────────────── --}}
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 max-w-full mx-auto">
            <div class="flex items-center gap-3 min-w-0">
                <a wire:navigate href="{{ route('hr.payroll.concepts') }}" 
                   class="group flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-100 hover:shadow-sm transition-all duration-200">
                    <svg class="w-5 h-5 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl font-bold text-slate-800 truncate">
                        {{ $payrollConcept?->exists ? 'Editar Concepto' : 'Nuevo Concepto' }}
                    </h1>
                    <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">
                        {{ $payrollConcept?->exists ? $name : 'Definición de percepciones y deducciones' }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <a wire:navigate href="{{ route('hr.payroll.concepts') }}"
                    class="hidden sm:inline-flex px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 transition-colors">
                    Cancelar
                </a>
                <button type="button" wire:click="save"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:scale-[1.02] active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>{{ $payrollConcept?->exists ? 'Guardar cambios' : 'Crear concepto' }}</span>
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8">
        <form wire:submit="save" class="space-y-6 lg:space-y-8">
            
            <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/30">
                    <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Detalles del Concepto</h3>
                </div>
                <div class="p-6 lg:p-8 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                        <div class="md:col-span-8 space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Nombre del concepto *</label>
                            <input wire:model="name" type="text" placeholder="Ej. Sueldo Base, Bono de Asistencia, ISR..."
                                class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-bold">
                            @error('name') <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider ml-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="md:col-span-4 space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Código interno</label>
                            <input wire:model="code" type="text" placeholder="Ej. SUELDO"
                                class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-black uppercase">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4">
                        <div class="space-y-3">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1 text-center block">Tipo de Concepto</label>
                            <div class="grid grid-cols-2 gap-2">
                                <button type="button" wire:click="$set('type', 'perception')"
                                    class="px-4 py-3 rounded-2xl border-2 transition-all duration-200 text-[10px] font-black uppercase tracking-widest
                                    {{ $type === 'perception' ? 'bg-emerald-50 border-emerald-500 text-emerald-700 shadow-sm shadow-emerald-500/10' : 'bg-slate-50 border-slate-100 text-slate-400 hover:border-slate-200' }}">
                                    Percepción
                                </button>
                                <button type="button" wire:click="$set('type', 'deduction')"
                                    class="px-4 py-3 rounded-2xl border-2 transition-all duration-200 text-[10px] font-black uppercase tracking-widest
                                    {{ $type === 'deduction' ? 'bg-red-50 border-red-500 text-red-700 shadow-sm shadow-red-500/10' : 'bg-slate-50 border-slate-100 text-slate-400 hover:border-slate-200' }}">
                                    Deducción
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="flex flex-col items-center justify-center p-4 rounded-2xl {{ $is_taxable ? 'bg-indigo-50/50 border-indigo-100' : 'bg-slate-50 border-slate-100' }} border transition-colors">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Gravable</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input wire:model="is_taxable" type="checkbox" class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                                </label>
                            </div>
                            <div class="flex flex-col items-center justify-center p-4 rounded-2xl {{ $is_active ? 'bg-indigo-50/50 border-indigo-100' : 'bg-slate-50 border-slate-100' }} border transition-colors">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Activo</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input wire:model="is_active" type="checkbox" class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

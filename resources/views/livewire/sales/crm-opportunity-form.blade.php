<div class="min-h-screen bg-slate-50/50 -m-4 lg:-m-6">
    {{-- ── STICKY HEADER ────────────────────────────────────────────────── --}}
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 max-w-full mx-auto">
            <div class="flex items-center gap-3 min-w-0">
                <a wire:navigate href="{{ route('sales.crm.pipeline') }}" 
                   class="group flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-100 hover:shadow-sm transition-all duration-200">
                    <svg class="w-5 h-5 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl font-bold text-slate-800 truncate">
                        {{ $opportunity ? 'Editar Oportunidad' : 'Nueva Oportunidad' }}
                    </h1>
                    <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">Embudo de Ventas / Pipeline</p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <a wire:navigate href="{{ route('sales.crm.pipeline') }}"
                    class="hidden sm:inline-flex px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 transition-colors">
                    Cancelar
                </a>
                <button type="button" wire:click="save"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:scale-[1.02] active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ $opportunity ? 'Guardar Cambios' : 'Abrir Oportunidad' }}</span>
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-full mx-auto p-4 sm:p-6 lg:p-8 space-y-8">
        <x-alert />

        <form wire:submit="save" class="space-y-8">
            {{-- ── SECCIÓN: INFORMACIÓN DE LA OPORTUNIDAD ──────────────────────── --}}
            <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                <div class="p-6 lg:p-8 space-y-8">
                    <div class="flex items-center gap-3 border-b border-slate-100 pb-5">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                        </div>
                        <h2 class="text-base font-bold text-slate-800">Detalles del Negocio</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                        <div class="md:col-span-4 space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Título del Proyecto / Negocio *</label>
                            <input wire:model="title" type="text" placeholder="Ej. Instalación Eléctrica Nave Industrial"
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-lg font-bold text-slate-800 focus:ring-4 focus:ring-indigo-500/10 transition-all">
                            @error('title') <p class="text-[10px] text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Vinculación --}}
                        <div class="md:col-span-2 space-y-4">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Vinculado a:</label>
                            <div class="flex p-1 bg-slate-100 rounded-xl w-fit mb-2">
                                <button type="button" wire:click="$set('linked_type', 'prospect')" class="px-4 py-1.5 text-[10px] font-black uppercase tracking-widest rounded-lg transition-all {{ $linked_type === 'prospect' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500' }}">Prospecto</button>
                                <button type="button" wire:click="$set('linked_type', 'customer')" class="px-4 py-1.5 text-[10px] font-black uppercase tracking-widest rounded-lg transition-all {{ $linked_type === 'customer' ? 'bg-white text-emerald-600 shadow-sm' : 'text-slate-500' }}">Cliente</button>
                            </div>

                            <div class="relative">
                                @if($linked_type === 'prospect')
                                    <select wire:model="prospect_id" class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 appearance-none cursor-pointer transition-all">
                                        <option value="">— Seleccionar Prospecto —</option>
                                        @foreach($prospects as $p)
                                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <select wire:model="customer_id" class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-emerald-500/10 appearance-none cursor-pointer transition-all">
                                        <option value="">— Seleccionar Cliente —</option>
                                        @foreach($customers as $c)
                                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                @endif
                                <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Etapa de Venta</label>
                            <select wire:model.live="stage" class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 appearance-none cursor-pointer transition-all">
                                @foreach(\App\Models\SalesOpportunity::STAGES as $k => $v)
                                    <option value="{{ $k }}">{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Probabilidad (%)</label>
                            <div class="relative">
                                <input wire:model="probability" type="number" min="0" max="100"
                                    class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-black text-indigo-600 focus:ring-4 focus:ring-indigo-500/10 text-right">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-indigo-300 font-bold">%</span>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Valor Estimado ($)</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">$</span>
                                <input wire:model="estimated_value" type="number" step="0.01" min="0" placeholder="0.00"
                                    class="w-full bg-slate-50 border-none rounded-2xl pl-8 pr-4 py-4 text-sm font-black text-slate-800 focus:ring-4 focus:ring-teal-500/10">
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Fecha Est. de Cierre</label>
                            <input wire:model="expected_close_date" type="date"
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10">
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Ejecutivo Asignado</label>
                            <select wire:model="assigned_to" class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 appearance-none cursor-pointer">
                                <option value="">— Sin asignar —</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        @if($stage === 'lost')
                            <div class="space-y-2 animate-in slide-in-from-left-4 duration-300">
                                <label class="text-[11px] font-bold text-rose-500 uppercase tracking-widest">Motivo de Pérdida</label>
                                <select wire:model="lost_reason" class="w-full bg-rose-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-rose-700 focus:ring-4 focus:ring-rose-500/10 appearance-none cursor-pointer">
                                    <option value="">— Seleccionar —</option>
                                    @foreach(\App\Models\SalesOpportunity::LOST_REASONS as $k => $v)
                                        <option value="{{ $k }}">{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="md:col-span-4 space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Descripción de la Oportunidad</label>
                            <textarea wire:model="description" rows="3" placeholder="Detalles comerciales, alcances, competencia..."
                                class="w-full bg-slate-50 border-none rounded-[2rem] px-6 py-5 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-indigo-500/10 transition-all resize-none"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>


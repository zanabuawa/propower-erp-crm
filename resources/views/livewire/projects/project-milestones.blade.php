<div class="min-h-screen bg-slate-50/50 -m-4 lg:-m-6">
    {{-- ── STICKY HEADER ────────────────────────────────────────────────── --}}
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 max-w-full mx-auto">
            <div class="flex items-center gap-3 min-w-0">
                <a wire:navigate href="{{ route('projects.show', $project) }}" 
                   class="group flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-100 hover:shadow-sm transition-all duration-200">
                    <svg class="w-5 h-5 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl font-bold text-slate-800 truncate">Hitos del Proyecto</h1>
                    <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">{{ $project->code }} — {{ $project->name }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                @can('edit projects')
                <button type="button" wire:click="create"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:scale-[1.02] active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Nuevo Hito</span>
                </button>
                @endcan
            </div>
        </div>
    </div>

    <div class="max-w-full mx-auto p-4 sm:p-6 lg:p-8 space-y-8">
        <x-alert />

        {{-- ── KPIs DE HITOS ──────────────────────────────────────────────── --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <div class="bg-white rounded-3xl border border-slate-200/60 p-6 shadow-sm">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Hitos</p>
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-black text-slate-800">{{ $milestones->count() }}</span>
                    <span class="text-[10px] font-bold text-slate-400 uppercase">Definidos</span>
                </div>
            </div>
            <div class="bg-white rounded-3xl border border-slate-200/60 p-6 shadow-sm">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Monto a Cobrar</p>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-xs font-bold text-slate-400">{{ $project->currency }}</span>
                    <span class="text-3xl font-black text-slate-800">${{ number_format($totalPago, 2) }}</span>
                </div>
            </div>
            <div class="bg-emerald-50/50 rounded-3xl border border-emerald-100 p-6 shadow-sm">
                <p class="text-[10px] font-black text-emerald-600 uppercase tracking-widest mb-1">Cobrado / Avanzado</p>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-xs font-bold text-emerald-400">$</span>
                    <span class="text-3xl font-black text-emerald-700">${{ number_format($totalCompletado, 2) }}</span>
                </div>
            </div>
        </div>

        {{-- ── FORMULARIO INLINE (REDISEÑADO) ────────────────────────────── --}}
        @if($showForm)
            <div class="bg-white rounded-[2.5rem] border-2 border-indigo-100 p-8 lg:p-10 shadow-xl shadow-indigo-50/50 animate-in fade-in slide-in-from-top-4 duration-300 relative overflow-hidden group">
                <div class="absolute -right-20 -top-20 w-64 h-64 bg-indigo-500/5 rounded-full blur-3xl transition-transform group-hover:scale-110"></div>
                
                <div class="relative z-10 space-y-8">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white shadow-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        </div>
                        <h2 class="text-lg font-black text-indigo-900 uppercase tracking-widest">{{ $editingId ? 'Editar Hito Comercial' : 'Nuevo Hito Comercial' }}</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                        <div class="md:col-span-3 space-y-2">
                            <label class="text-[10px] font-black text-indigo-400 uppercase tracking-widest">Nombre del Hito *</label>
                            <input wire:model="name" type="text" placeholder="Ej. Anticipo, Entrega Final, Cierre de Fase..."
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-800 focus:ring-4 focus:ring-indigo-500/10 transition-all">
                            @error('name') <p class="text-[10px] text-rose-500 font-bold">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-indigo-400 uppercase tracking-widest">Estado</label>
                            <select wire:model="status" class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 appearance-none cursor-pointer transition-all">
                                <option value="pendiente">Pendiente</option>
                                <option value="en_progreso">En Progreso</option>
                                <option value="completado">Completado</option>
                                <option value="cancelado">Cancelado</option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-indigo-400 uppercase tracking-widest">Fecha Límite</label>
                            <input wire:model="due_date" type="date"
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 transition-all">
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-indigo-400 uppercase tracking-widest">Monto de Cobro ({{ $project->currency }})</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">$</span>
                                <input wire:model="payment_amount" type="number" step="0.01" min="0"
                                    class="w-full bg-slate-50 border-none rounded-2xl pl-8 pr-4 py-4 text-sm font-black text-indigo-700 focus:ring-4 focus:ring-indigo-500/10 transition-all">
                            </div>
                            @error('payment_amount') <p class="text-[10px] text-rose-500 font-bold">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-indigo-400 uppercase tracking-widest">Orden Visual</label>
                            <input wire:model="sort_order" type="number" min="0"
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 transition-all">
                        </div>

                        <div class="md:col-span-4 space-y-2">
                            <label class="text-[10px] font-black text-indigo-400 uppercase tracking-widest">Descripción / Condiciones</label>
                            <textarea wire:model="description" rows="2" placeholder="Detalles sobre entregables o requisitos de pago..."
                                class="w-full bg-slate-50 border-none rounded-2xl px-6 py-5 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-indigo-500/10 transition-all resize-none"></textarea>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 pt-4 border-t border-indigo-50">
                        <button wire:click="save"
                            class="px-8 py-4 bg-indigo-600 text-white rounded-2xl text-xs font-black uppercase tracking-[0.2em] shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 hover:scale-[1.02] active:scale-[0.98] transition-all">
                            {{ $editingId ? 'Guardar Cambios' : 'Registrar Hito' }}
                        </button>
                        <button wire:click="resetForm"
                            class="px-6 py-4 text-xs font-black uppercase tracking-widest text-slate-400 hover:text-slate-600 transition-colors">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- ── LISTA DE HITOS ────────────────────────────────────────────── --}}
        <div class="space-y-6">
            @forelse($milestones as $milestone)
                @php
                    $isOverdue = $milestone->due_date && $milestone->status !== 'completado' && $milestone->due_date->isPast();
                    $mStatus = match($milestone->status) {
                        'pendiente'   => ['bg-slate-100 text-slate-600', 'Pendiente'],
                        'en_progreso' => ['bg-blue-100 text-blue-700',   'En Progreso'],
                        'completado'  => ['bg-emerald-100 text-emerald-700', 'Completado'],
                        'cancelado'   => ['bg-rose-100 text-rose-700',   'Cancelado'],
                    };
                @endphp
                <div class="bg-white rounded-3xl border border-slate-200/60 p-6 shadow-sm group hover:border-indigo-200 transition-all {{ $milestone->status === 'completado' ? 'opacity-70 grayscale-[50%]' : '' }}">
                    <div class="flex flex-col lg:flex-row lg:items-center gap-6">
                        {{-- Icono & Info Principal --}}
                        <div class="flex items-center gap-5 flex-1 min-w-0">
                            <div class="w-14 h-14 rounded-2xl flex items-center justify-center shrink-0 border border-slate-100 {{ $milestone->status === 'completado' ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-50 text-slate-400' }}">
                                @if($milestone->status === 'completado')
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                @else
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h11a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-3 mb-1">
                                    <h4 class="text-base font-black text-slate-800 truncate {{ $milestone->status === 'completado' ? 'line-through' : '' }}">{{ $milestone->name }}</h4>
                                    <span class="inline-flex px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-wider {{ $mStatus[0] }}">{{ $mStatus[1] }}</span>
                                    @if($isOverdue)
                                        <span class="inline-flex px-2 py-0.5 rounded-lg bg-rose-100 text-rose-700 text-[9px] font-black uppercase tracking-wider animate-pulse">Vencido</span>
                                    @endif
                                </div>
                                <p class="text-xs font-medium text-slate-500 line-clamp-1">{{ $milestone->description ?: 'Sin descripción detallada' }}</p>
                            </div>
                        </div>

                        {{-- Datos de Pago & Fechas --}}
                        <div class="flex flex-wrap items-center gap-8 lg:gap-12 lg:px-6">
                            @if($milestone->payment_amount > 0)
                                <div class="flex flex-col">
                                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Compromiso</span>
                                    <span class="text-sm font-black text-indigo-600">${{ number_format($milestone->payment_amount, 2) }}</span>
                                </div>
                            @endif
                            <div class="flex flex-col">
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Límite</span>
                                <span class="text-xs font-bold text-slate-700">{{ $milestone->due_date?->format('d/m/Y') ?? 'Sin fecha' }}</span>
                            </div>
                            @if($milestone->completed_at)
                                <div class="flex flex-col">
                                    <span class="text-[9px] font-black text-emerald-400 uppercase tracking-widest mb-1">Realizado</span>
                                    <span class="text-xs font-bold text-emerald-600">{{ $milestone->completed_at->format('d/m/Y') }}</span>
                                </div>
                            @endif
                        </div>

                        {{-- Acciones --}}
                        <div class="flex items-center justify-end gap-3 pt-4 lg:pt-0 border-t lg:border-t-0 border-slate-50">
                            @if($milestone->status !== 'completado' && $milestone->status !== 'cancelado')
                                @can('edit projects')
                                    <button wire:click="complete({{ $milestone->id }})" wire:confirm="¿Confirmas que este hito se ha cumplido?"
                                        class="px-4 py-2 bg-emerald-50 text-emerald-700 text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-emerald-600 hover:text-white transition-all">Completar</button>
                                @endcan
                            @endif
                            <div class="flex gap-1">
                                @can('edit projects')
                                    <button wire:click="edit({{ $milestone->id }})" class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                @endcan
                                @can('delete projects')
                                    <button wire:click="delete({{ $milestone->id }})" wire:confirm="¿Eliminar definitivamente este hito comercial?" class="p-2 text-slate-300 hover:text-rose-500 hover:bg-rose-50 rounded-lg transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-[2.5rem] border border-slate-200 py-24 text-center">
                    <div class="w-20 h-20 rounded-full bg-slate-50 flex items-center justify-center text-slate-200 mx-auto mb-6 border-2 border-dashed border-slate-200">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                    <h3 class="text-xl font-black text-slate-800 tracking-tight">Sin Hitos Registrados</h3>
                    <p class="text-sm font-medium text-slate-400 mt-2 max-w-sm mx-auto">Agrega los hitos comerciales del proyecto para estructurar las entregas y compromisos de cobro.</p>
                </div>
            @endforelse
        </div>

        @if($milestones->count() > 0)
            <div class="bg-indigo-50 rounded-[2rem] p-6 border border-indigo-100 flex items-start gap-4 shadow-sm animate-in slide-in-from-bottom-4 duration-700">
                <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center text-indigo-600 shadow-sm shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-xs font-black text-indigo-900 uppercase tracking-widest">Automatización Comercial</p>
                    <p class="text-[11px] text-indigo-800/80 leading-relaxed font-medium mt-1">
                        Al registrar un hito con monto se genera un <strong>flujo de caja proyectado</strong>. Al completarlo, el registro se marca como realizado en el módulo de finanzas automáticamente.
                    </p>
                </div>
            </div>
        @endif
    </div>
</div>


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
                    <h1 class="text-lg sm:text-xl font-bold text-slate-800 truncate">Gastos del Proyecto</h1>
                    <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">{{ $project->code }} — {{ $project->name }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                @can('create projects')
                <button type="button" wire:click="$set('showForm', true)"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-rose-600 to-rose-700 hover:from-rose-700 hover:to-rose-800 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all duration-200 shadow-lg shadow-rose-500/25 hover:shadow-rose-500/40 hover:scale-[1.02] active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Registrar Gasto</span>
                </button>
                @endcan
            </div>
        </div>
    </div>

    <div class="max-w-full mx-auto p-4 sm:p-6 lg:p-8 space-y-8">
        <x-alert />

        {{-- ── KPIs DE GASTOS ──────────────────────────────────────────────── --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
            @php
                $statusLabels = [
                    'pendiente' => ['label' => 'Por Aprobar', 'color' => 'amber'],
                    'aprobado'  => ['label' => 'Aprobados',   'color' => 'indigo'],
                    'rechazado' => ['label' => 'Rechazados',  'color' => 'rose'],
                    'pagado'    => ['label' => 'Pagados',     'color' => 'emerald']
                ];
            @endphp
            @foreach($statusLabels as $key => $s)
                <div class="bg-white rounded-3xl border border-slate-200/60 p-6 shadow-sm">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ $s['label'] }}</p>
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-xs font-bold text-slate-400">$</span>
                        <span class="text-2xl font-black text-{{ $s['color'] }}-600 tracking-tighter">{{ number_format($totalByStatus[$key] ?? 0, 2) }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ── FORMULARIO INLINE ────────────────────────────────────────── --}}
        @if($showForm)
            <div class="bg-white rounded-[2.5rem] border-2 border-rose-100 p-8 lg:p-10 shadow-xl shadow-rose-50/50 animate-in fade-in slide-in-from-top-4 duration-300 relative overflow-hidden group">
                <div class="absolute -right-20 -top-20 w-64 h-64 bg-rose-500/5 rounded-full blur-3xl transition-transform group-hover:scale-110"></div>
                
                <div class="relative z-10 space-y-8">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-rose-600 flex items-center justify-center text-white shadow-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                        <h3 class="text-lg font-black text-rose-900 uppercase tracking-widest">{{ $editingId ? 'Editar Comprobante' : 'Nuevo Registro de Gasto' }}</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                        <div class="md:col-span-2 space-y-2">
                            <label class="text-[10px] font-black text-rose-400 uppercase tracking-widest">Concepto del Gasto *</label>
                            <input wire:model="concept" type="text" placeholder="Ej. Compra de consumibles menores, Viáticos..."
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-800 focus:ring-4 focus:ring-rose-500/10 transition-all">
                            @error('concept') <p class="text-[10px] text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-rose-400 uppercase tracking-widest">Categoría *</label>
                            <select wire:model="category" class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-rose-500/10 appearance-none cursor-pointer transition-all">
                                <option value="material">Material</option>
                                <option value="mano_obra">Mano de Obra</option>
                                <option value="subcontrato">Subcontrato</option>
                                <option value="transporte">Transporte</option>
                                <option value="viaje">Viaje / Viáticos</option>
                                <option value="otro">Otro Gasto</option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-rose-400 uppercase tracking-widest">Importe Total *</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-rose-300 font-bold">$</span>
                                <input wire:model="amount" type="number" step="0.01" min="0.01"
                                    class="w-full bg-slate-50 border-none rounded-2xl pl-8 pr-4 py-4 text-sm font-black text-rose-700 focus:ring-4 focus:ring-rose-500/10 transition-all">
                            </div>
                            @error('amount') <p class="text-[10px] text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-rose-400 uppercase tracking-widest">Moneda</label>
                            <select wire:model="currency" class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-rose-500/10 appearance-none cursor-pointer transition-all">
                                <option value="MXN">MXN — Pesos</option>
                                <option value="USD">USD — Dólares</option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-rose-400 uppercase tracking-widest">Fecha del Gasto *</label>
                            <input wire:model="expense_date" type="date"
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-rose-500/10 transition-all">
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-rose-400 uppercase tracking-widest">Referencia / Folio</label>
                            <input wire:model="reference" type="text" placeholder="N° Factura, Ticket..."
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-rose-500/10 transition-all uppercase">
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-rose-400 uppercase tracking-widest">Estatus Registro</label>
                            <select wire:model="status" class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-rose-500/10 appearance-none cursor-pointer transition-all">
                                <option value="pendiente">Pendiente</option>
                                <option value="aprobado">Aprobado</option>
                                <option value="pagado">Pagado</option>
                                <option value="rechazado">Rechazado</option>
                            </select>
                        </div>

                        <div class="md:col-span-4 space-y-2">
                            <label class="text-[10px] font-black text-rose-400 uppercase tracking-widest">Notas Adicionales</label>
                            <textarea wire:model="notes" rows="1" placeholder="Detalles extra sobre el origen del gasto..."
                                class="w-full bg-slate-50 border-none rounded-2xl px-6 py-5 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-rose-500/10 transition-all resize-none"></textarea>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 pt-4 border-t border-rose-50">
                        <button wire:click="save" wire:loading.attr="disabled"
                            class="px-8 py-4 bg-rose-600 text-white rounded-2xl text-xs font-black uppercase tracking-[0.2em] shadow-lg shadow-rose-500/30 hover:bg-rose-700 hover:scale-[1.02] active:scale-[0.98] transition-all">
                            <span wire:loading.remove wire:target="save">{{ $editingId ? 'Guardar Cambios' : 'Registrar Gasto' }}</span>
                            <span wire:loading wire:target="save" class="animate-pulse">Guardando...</span>
                        </button>
                        <button wire:click="resetForm"
                            class="px-6 py-4 text-xs font-black uppercase tracking-widest text-slate-400 hover:text-slate-600 transition-colors">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- ── FILTROS & TABLA ────────────────────────────────────────────── --}}
        <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100 bg-slate-50/30 flex flex-wrap gap-4 items-center">
                <div class="relative flex-1 min-w-[200px]">
                    <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar concepto o referencia..."
                        class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 transition-all">
                </div>
                <div class="relative">
                    <select wire:model.live="filterCategory" class="pl-4 pr-10 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 appearance-none cursor-pointer">
                        <option value="">Todas las Categorías</option>
                        <option value="material">Material</option>
                        <option value="mano_obra">Mano de Obra</option>
                        <option value="subcontrato">Subcontrato</option>
                        <option value="transporte">Transporte</option>
                        <option value="viaje">Viaje</option>
                        <option value="otro">Otro</option>
                    </select>
                </div>
                <div class="relative">
                    <select wire:model.live="filterStatus" class="pl-4 pr-10 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 appearance-none cursor-pointer">
                        <option value="">Cualquier Estatus</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="aprobado">Aprobado</option>
                        <option value="rechazado">Rechazado</option>
                        <option value="pagado">Pagado</option>
                    </select>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left border-b border-slate-100 bg-slate-50/10">
                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Fecha / Referencia</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Concepto / Categoría</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Monto</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Estado</th>
                            <th class="px-8 py-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($expenses as $expense)
                            @php
                                $sc = match($expense->status) {
                                    'pendiente' => ['bg-amber-100 text-amber-700', 'Reloj'],
                                    'aprobado'  => ['bg-indigo-100 text-indigo-700',   'OK'],
                                    'rechazado' => ['bg-rose-100 text-rose-700',     'X'],
                                    'pagado'    => ['bg-emerald-100 text-emerald-700', '$'],
                                    default     => ['bg-gray-100 text-gray-500',   '—'],
                                };
                            @endphp
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="px-8 py-5">
                                    <p class="text-sm font-black text-slate-700">{{ $expense->expense_date->format('d/m/Y') }}</p>
                                    @if($expense->reference)
                                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-tighter">Ref: {{ $expense->reference }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-5">
                                    <p class="font-bold text-slate-800">{{ $expense->concept }}</p>
                                    <p class="text-[10px] font-black text-indigo-500 uppercase tracking-widest">{{ str_replace('_', ' ', $expense->category) }}</p>
                                </td>
                                <td class="px-6 py-5 text-right">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase mr-1">{{ $expense->currency }}</span>
                                    <span class="text-sm font-black text-slate-900">${{ number_format($expense->amount, 2) }}</span>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <span class="inline-flex px-3 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest {{ $sc[0] }}">
                                        {{ $expense->status }}
                                    </span>
                                </td>
                                <td class="px-8 py-5 text-right w-32">
                                    <div class="flex items-center justify-end gap-2 transition-all">
                                        @can('edit projects')
                                            <button wire:click="edit({{ $expense->id }})" class="p-2 bg-white rounded-lg text-slate-400 hover:text-indigo-600 border border-slate-100 shadow-sm transition-all">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </button>
                                        @endcan
                                        @can('delete projects')
                                            <button wire:click="delete({{ $expense->id }})" wire:confirm="¿Eliminar definitivamente este registro de gasto?" class="p-2 bg-white rounded-lg text-slate-300 hover:text-rose-600 border border-slate-100 shadow-sm transition-all">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-8 py-24 text-center text-[11px] font-bold text-slate-400 uppercase tracking-widest italic">No se han registrado gastos para este proyecto</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($expenses->hasPages())
                <div class="px-8 py-4 border-t border-slate-100 bg-slate-50/30">{{ $expenses->links('vendor.pagination.tailwind') }}</div>
            @endif
        </div>
    </div>
</div>


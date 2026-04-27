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
                    <h1 class="text-lg sm:text-xl font-bold text-slate-800 truncate">Materiales & Equipos — {{ $project->name }}</h1>
                    <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">{{ $project->code }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <button type="button" wire:click="openCreate"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-teal-600 to-teal-700 hover:from-teal-700 hover:to-teal-800 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all duration-200 shadow-lg shadow-teal-500/25 hover:shadow-teal-500/40 hover:scale-[1.02] active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Agregar Recurso</span>
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-full mx-auto p-4 sm:p-6 lg:p-8 space-y-8">
        {{-- ── KPIs DE SUMINISTROS ────────────────────────────────────────── --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white rounded-3xl border border-slate-200/60 p-6 shadow-sm">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Costo Planificado</p>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-xs font-bold text-slate-400">$</span>
                    <span class="text-2xl font-black text-slate-800">{{ number_format($summary['total_cost'], 2) }}</span>
                </div>
            </div>
            <div class="bg-white rounded-3xl border border-slate-200/60 p-6 shadow-sm">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Costo Ejecutado</p>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-xs font-bold text-slate-400">$</span>
                    <span class="text-2xl font-black text-indigo-600">{{ number_format($summary['total_used'], 2) }}</span>
                </div>
            </div>
            <div class="bg-amber-50/50 rounded-3xl border border-amber-100 p-6 shadow-sm">
                <p class="text-[10px] font-black text-amber-600 uppercase tracking-widest mb-1">Pendientes</p>
                <div class="flex items-center gap-2">
                    <span class="text-2xl font-black text-amber-700">{{ $summary['pending'] }}</span>
                    <span class="text-[10px] font-bold text-amber-500 uppercase">Artículos</span>
                </div>
            </div>
            <div class="bg-emerald-50/50 rounded-3xl border border-emerald-100 p-6 shadow-sm">
                <p class="text-[10px] font-black text-emerald-600 uppercase tracking-widest mb-1">Adquiridos</p>
                <div class="flex items-center gap-2">
                    <span class="text-2xl font-black text-emerald-700">{{ $summary['acquired'] }}</span>
                    <span class="text-[10px] font-bold text-emerald-500 uppercase">Surtidos</span>
                </div>
            </div>
        </div>

        {{-- ── FILTROS & TABLA ────────────────────────────────────────────── --}}
        <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100 bg-slate-50/30 flex flex-wrap gap-4 items-center">
                <div class="relative">
                    <select wire:model.live="filterType"
                        class="pl-4 pr-10 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 appearance-none cursor-pointer">
                        <option value="">Todos los Tipos</option>
                        @foreach(\App\Models\ProjectMaterial::$typeLabels as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="relative">
                    <select wire:model.live="filterStatus"
                        class="pl-4 pr-10 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 appearance-none cursor-pointer">
                        <option value="">Cualquier Estado</option>
                        @foreach(\App\Models\ProjectMaterial::$statusLabels as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left border-b border-slate-100">
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Recurso / Especificación</th>
                            <th class="px-4 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest hidden md:table-cell">Tarea Asoc.</th>
                            <th class="px-4 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Tipo</th>
                            <th class="px-4 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Cant. Requerida</th>
                            <th class="px-4 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Cant. Usada</th>
                            <th class="px-4 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Costo Total</th>
                            <th class="px-4 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Estado</th>
                            <th class="px-6 py-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($materials as $mat)
                            @php
                                $statusConf = match($mat->status) {
                                    'pendiente'  => ['bg-slate-100 text-slate-600', 'Reloj'],
                                    'solicitado' => ['bg-blue-100 text-blue-700',   'OC'],
                                    'adquirido'  => ['bg-amber-100 text-amber-700', 'En Stock'],
                                    'utilizado'  => ['bg-emerald-100 text-emerald-700', 'OK'],
                                    default      => ['bg-gray-100 text-gray-500',   $mat->status],
                                };
                            @endphp
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-slate-800">{{ $mat->name }}</span>
                                        @if($mat->product)
                                            <span class="text-[10px] font-medium text-indigo-500 uppercase">{{ $mat->product->name }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-4 hidden md:table-cell">
                                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-tighter">{{ $mat->task?->title ?? 'Sin vincular' }}</span>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <span class="inline-flex px-2 py-0.5 rounded bg-slate-100 text-slate-500 text-[9px] font-black uppercase tracking-widest">{{ $mat->resource_type }}</span>
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <span class="text-xs font-black text-slate-700">{{ number_format($mat->quantity_needed, 2) }}</span>
                                    <span class="text-[9px] font-bold text-slate-400 uppercase ml-0.5">{{ $mat->unit }}</span>
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <span class="text-xs font-black {{ $mat->quantity_used > $mat->quantity_needed ? 'text-rose-600' : 'text-slate-500' }}">{{ number_format($mat->quantity_used, 2) }}</span>
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <span class="text-xs font-black text-slate-900">${{ number_format($mat->quantity_needed * $mat->unit_cost, 2) }}</span>
                                </td>
                                <td class="px-4 py-4">
                                    <select wire:change="updateStatus({{ $mat->id }}, $event.target.value)"
                                        class="text-[10px] font-black uppercase tracking-widest rounded-lg border-none {{ $statusConf[0] }} cursor-pointer focus:ring-4 focus:ring-indigo-500/10 py-1 pl-2 pr-8">
                                        @foreach(\App\Models\ProjectMaterial::$statusLabels as $key => $label)
                                            <option value="{{ $key }}" {{ $mat->status === $key ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2 transition-all">
                                        @if(!$mat->purchase_requisition_id && in_array($mat->status, ['pendiente', 'solicitado']))
                                            <button wire:click="requestPurchase({{ $mat->id }})" title="Solicitar Compra" class="p-2 bg-indigo-50 text-indigo-600 rounded-lg hover:bg-indigo-600 hover:text-white transition-all">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                            </button>
                                        @endif
                                        <button wire:click="openEdit({{ $mat->id }})" class="p-2 bg-slate-50 text-slate-400 hover:text-teal-600 rounded-lg transition-all">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-6 py-24 text-center text-[11px] font-bold text-slate-400 uppercase tracking-widest">Sin recursos registrados en este proyecto</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ── MODAL: RECURSO ──────────────────────────────────────────────── --}}
    @if($showModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" wire:click="$set('showModal', false)"></div>
            <div class="relative bg-white rounded-[2.5rem] shadow-2xl p-10 w-full max-w-xl mx-auto border border-slate-200 animate-in zoom-in-95 duration-200">
                <div class="w-16 h-16 rounded-3xl bg-teal-50 flex items-center justify-center text-teal-600 mb-6 mx-auto">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <h3 class="text-2xl font-black text-slate-800 text-center tracking-tight mb-2">{{ $editingId ? 'Editar Recurso' : 'Agregar Recurso' }}</h3>
                <p class="text-sm text-slate-500 text-center font-medium leading-relaxed mb-8">Especifica materiales, herramientas o equipos para el proyecto.</p>
                
                <div class="space-y-6">
                    <div class="grid grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest block">Tipo de Recurso *</label>
                            <select wire:model="resource_type" class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-teal-500/10 cursor-pointer">
                                @foreach(\App\Models\ProjectMaterial::$typeLabels as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest block">Catálogo de Productos</label>
                            <select wire:model.live="product_id" class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-teal-500/10 cursor-pointer">
                                <option value="">— Selección libre —</option>
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest block">Nombre / Descripción *</label>
                        <input wire:model="name" type="text" placeholder="Ej. Cable THW Calibre 12"
                               class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-teal-500/10 transition-all">
                        @error('name') <p class="text-[10px] text-rose-500 font-bold">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-3 gap-6">
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest block">Unidad</label>
                            <input wire:model="unit" type="text" placeholder="m, pza..."
                                   class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 text-center uppercase">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest block">Cant. Necesaria</label>
                            <input wire:model="quantity_needed" type="number" step="0.01"
                                   class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-black text-slate-800 text-center">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest block">Costo Unitario ($)</label>
                            <input wire:model="unit_cost" type="number" step="0.01"
                                   class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-black text-indigo-600 text-center">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest block">Notas Adicionales</label>
                        <textarea wire:model="notes" rows="2" class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-teal-500/10 resize-none"></textarea>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mt-10">
                    <button wire:click="$set('showModal', false)"
                        class="py-4 bg-slate-50 text-slate-500 rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-slate-100 transition-all">Cancelar</button>
                    <button wire:click="save"
                        class="py-4 bg-teal-600 text-white rounded-2xl text-xs font-black uppercase tracking-widest shadow-lg shadow-teal-500/25 hover:bg-teal-700 hover:scale-[1.02] transition-all">Guardar Recurso</button>
                </div>
            </div>
        </div>
    @endif
</div>

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

        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium px-5 py-3 rounded-2xl">{{ session('success') }}</div>
        @endif
        @if(session('info'))
            <div class="bg-blue-50 border border-blue-200 text-blue-800 text-sm font-medium px-5 py-3 rounded-2xl">{{ session('info') }}</div>
        @endif

        {{-- ── KPIs ──────────────────────────────────────────────────────────── --}}
        <div class="grid grid-cols-2 lg:grid-cols-6 gap-4">
            <div class="col-span-2 bg-white rounded-3xl border border-slate-200/60 p-5 shadow-sm">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Costo Planificado</p>
                <div class="flex items-baseline gap-1">
                    <span class="text-xs font-bold text-slate-400">$</span>
                    <span class="text-2xl font-black text-slate-800">{{ number_format($summary['total_cost'], 2) }}</span>
                </div>
            </div>
            <div class="col-span-2 bg-white rounded-3xl border border-slate-200/60 p-5 shadow-sm">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Costo Ejecutado</p>
                <div class="flex items-baseline gap-1">
                    <span class="text-xs font-bold text-slate-400">$</span>
                    <span class="text-2xl font-black text-indigo-600">{{ number_format($summary['total_used'], 2) }}</span>
                </div>
            </div>
            <div class="bg-gray-50 rounded-3xl border border-gray-100 p-5 text-center">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Pendiente</p>
                <span class="text-2xl font-black text-gray-600">{{ $summary['pendiente'] }}</span>
            </div>
            <div class="bg-blue-50/60 rounded-3xl border border-blue-100 p-5 text-center">
                <p class="text-[10px] font-black text-blue-500 uppercase tracking-widest mb-1">Reservado</p>
                <span class="text-2xl font-black text-blue-700">{{ $summary['reservado'] }}</span>
            </div>
            <div class="bg-amber-50/60 rounded-3xl border border-amber-100 p-5 text-center">
                <p class="text-[10px] font-black text-amber-500 uppercase tracking-widest mb-1">En Compra</p>
                <span class="text-2xl font-black text-amber-700">{{ $summary['solicitado'] }}</span>
            </div>
            <div class="bg-emerald-50/60 rounded-3xl border border-emerald-100 p-5 text-center">
                <p class="text-[10px] font-black text-emerald-500 uppercase tracking-widest mb-1">Adquirido</p>
                <span class="text-2xl font-black text-emerald-700">{{ $summary['adquirido'] }}</span>
            </div>
        </div>

        {{-- ── TABLA ──────────────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 bg-slate-50/30 flex flex-wrap gap-3 items-center">
                <select wire:model.live="filterType"
                    class="pl-4 pr-10 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-700 appearance-none cursor-pointer">
                    <option value="">Todos los tipos</option>
                    @foreach(\App\Models\ProjectMaterial::$typeLabels as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                <select wire:model.live="filterStatus"
                    class="pl-4 pr-10 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-700 appearance-none cursor-pointer">
                    <option value="">Cualquier estado</option>
                    @foreach(\App\Models\ProjectMaterial::$statusLabels as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left border-b border-slate-100">
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Recurso</th>
                            <th class="px-4 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest hidden lg:table-cell">Almacén</th>
                            <th class="px-4 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Tipo</th>
                            <th class="px-4 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Cant. Necesaria</th>
                            <th class="px-4 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Reservado</th>
                            <th class="px-4 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Costo Total</th>
                            <th class="px-4 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Estado</th>
                            <th class="px-6 py-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($materials as $mat)
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-slate-800">{{ $mat->name }}</span>
                                        @if($mat->product)
                                            <span class="text-[10px] font-medium text-indigo-500">{{ $mat->product->sku ?? $mat->product->name }}</span>
                                        @endif
                                        @if($mat->purchaseRequisition)
                                            <span class="inline-flex items-center gap-1 text-[9px] font-bold text-amber-600 uppercase mt-0.5">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                                REQ: {{ $mat->purchaseRequisition->folio }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-4 hidden lg:table-cell">
                                    <span class="text-xs text-slate-500">{{ $mat->warehouse?->name ?? '—' }}</span>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <span class="inline-flex px-2 py-0.5 rounded bg-slate-100 text-slate-500 text-[9px] font-black uppercase tracking-widest">
                                        {{ \App\Models\ProjectMaterial::$typeLabels[$mat->resource_type] ?? $mat->resource_type }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <span class="text-xs font-black text-slate-700">{{ number_format($mat->quantity_needed, 2) }}</span>
                                    <span class="text-[9px] font-bold text-slate-400 uppercase ml-0.5">{{ $mat->unit }}</span>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    @php $reserved = (float) $mat->quantity_reserved; $needed = (float) $mat->quantity_needed; @endphp
                                    @if($reserved >= $needed)
                                        <span class="text-xs font-black text-blue-600">{{ number_format($reserved, 2) }}</span>
                                        <span class="text-[9px] text-blue-400 ml-0.5">{{ $mat->unit }}</span>
                                    @elseif($reserved > 0)
                                        <span class="text-xs font-black text-amber-600">{{ number_format($reserved, 2) }}</span>
                                        <span class="text-[9px] text-slate-400">/{{ number_format($needed, 2) }}</span>
                                    @else
                                        <span class="text-xs text-slate-300">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <span class="text-xs font-black text-slate-900">${{ number_format($mat->quantity_needed * $mat->unit_cost, 2) }}</span>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="inline-flex px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest
                                        {{ \App\Models\ProjectMaterial::$statusColors[$mat->status] ?? 'bg-gray-100 text-gray-500' }}">
                                        {{ \App\Models\ProjectMaterial::$statusLabels[$mat->status] ?? $mat->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @if(!$mat->purchase_requisition_id && in_array($mat->status, ['pendiente', 'reservado']))
                                            <button wire:click="requestPurchase({{ $mat->id }})" title="Levantar requisición de compra"
                                                class="p-2 bg-amber-50 text-amber-600 rounded-lg hover:bg-amber-600 hover:text-white transition-all">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                            </button>
                                        @endif
                                        <button wire:click="openEdit({{ $mat->id }})"
                                            class="p-2 bg-slate-50 text-slate-400 hover:text-teal-600 rounded-lg transition-all">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <button wire:click="delete({{ $mat->id }})"
                                            wire:confirm="¿Eliminar este recurso? Si tenía inventario reservado, se liberará."
                                            class="p-2 bg-slate-50 text-slate-300 hover:text-rose-500 rounded-lg transition-all">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
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

    {{-- ── MODAL: RECURSO ──────────────────────────────────────────────────── --}}
    @if($showModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="$set('showModal', false)"></div>
            <div class="relative bg-white rounded-[2.5rem] shadow-2xl p-8 w-full max-w-2xl mx-auto border border-slate-200 overflow-y-auto max-h-[90vh]">
                <div class="w-14 h-14 rounded-3xl bg-teal-50 flex items-center justify-center text-teal-600 mb-5 mx-auto">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <h3 class="text-xl font-black text-slate-800 text-center tracking-tight mb-1">{{ $editingId ? 'Editar Recurso' : 'Agregar Recurso' }}</h3>
                <p class="text-xs text-slate-400 text-center font-medium mb-7">El sistema verificará el stock y reservará automáticamente.</p>

                <div class="space-y-5">
                    {{-- Tipo + Producto --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest block">Tipo *</label>
                            <select wire:model="resource_type" class="w-full bg-slate-50 border-none rounded-2xl px-4 py-3.5 text-sm font-bold text-slate-700 cursor-pointer">
                                @foreach(\App\Models\ProjectMaterial::$typeLabels as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest block">Producto del catálogo</label>
                            <select wire:model.live="product_id" class="w-full bg-slate-50 border-none rounded-2xl px-4 py-3.5 text-sm font-bold text-slate-700 cursor-pointer">
                                <option value="">— Selección libre —</option>
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Nombre --}}
                    <div class="space-y-2">
                        <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest block">Nombre / Descripción *</label>
                        <input wire:model="name" type="text" placeholder="Ej. Cable THW Calibre 12"
                               class="w-full bg-slate-50 border-none rounded-2xl px-4 py-3.5 text-sm font-bold text-slate-700">
                        @error('name') <p class="text-[10px] text-rose-500 font-bold">{{ $message }}</p> @enderror
                    </div>

                    {{-- Almacén + stock disponible --}}
                    <div class="space-y-2">
                        <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest block">Almacén de reserva</label>
                        <select wire:model.live="warehouse_id" class="w-full bg-slate-50 border-none rounded-2xl px-4 py-3.5 text-sm font-bold text-slate-700 cursor-pointer">
                            <option value="">— Sin almacén (compra directa) —</option>
                            @foreach($warehouses as $w)
                                <option value="{{ $w->id }}">{{ $w->name }}</option>
                            @endforeach
                        </select>
                        @if($product_id && $warehouse_id)
                            <div class="flex items-center gap-2 mt-1.5 px-1">
                                @if($stockAvailable > 0)
                                    <span class="inline-flex items-center gap-1.5 text-[11px] font-bold text-emerald-700 bg-emerald-50 px-3 py-1.5 rounded-xl border border-emerald-100">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        {{ number_format($stockAvailable, 2) }} disponibles en almacén
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-[11px] font-bold text-amber-700 bg-amber-50 px-3 py-1.5 rounded-xl border border-amber-100">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.834-1.964-.834-2.732 0L3.07 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                                        Sin stock — se levantará requisición automática
                                    </span>
                                @endif
                            </div>
                        @endif
                        @error('warehouse_id') <p class="text-[10px] text-rose-500 font-bold">{{ $message }}</p> @enderror
                    </div>

                    {{-- Cantidad, unidad y costo --}}
                    <div class="grid grid-cols-3 gap-4">
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest block">Unidad</label>
                            <input wire:model="unit" type="text" placeholder="pza, m, kg..."
                                   class="w-full bg-slate-50 border-none rounded-2xl px-4 py-3.5 text-sm font-bold text-slate-700 text-center uppercase">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest block">Cantidad *</label>
                            <input wire:model.live="quantity_needed" type="number" step="0.01" min="0.01"
                                   class="w-full bg-slate-50 border-none rounded-2xl px-4 py-3.5 text-sm font-black text-slate-800 text-center">
                            @error('quantity_needed') <p class="text-[10px] text-rose-500 font-bold">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest block">Costo unitario ($)</label>
                            <input wire:model="unit_cost" type="number" step="0.01"
                                   class="w-full bg-slate-50 border-none rounded-2xl px-4 py-3.5 text-sm font-black text-indigo-600 text-center">
                        </div>
                    </div>

                    {{-- Aviso de faltante --}}
                    @if($product_id && $warehouse_id && $stockAvailable !== null && $quantity_needed)
                        @php $needed = (float) $quantity_needed; $avail = (float) $stockAvailable; @endphp
                        @if($avail > 0 && $avail < $needed)
                            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 text-xs text-amber-800 font-medium">
                                <strong>Stock parcial:</strong> hay {{ number_format($avail, 2) }} disponibles.
                                Se reservarán {{ number_format($avail, 2) }} y se levantará una requisición automática por
                                <strong>{{ number_format($needed - $avail, 2) }} {{ $unit }}</strong> faltantes.
                            </div>
                        @elseif($avail == 0 && $needed > 0)
                            <div class="bg-rose-50 border border-rose-200 rounded-2xl p-4 text-xs text-rose-800 font-medium">
                                <strong>Sin stock.</strong> Al guardar se creará automáticamente una requisición de compra por
                                <strong>{{ number_format($needed, 2) }} {{ $unit }}</strong> vinculada a este proyecto.
                            </div>
                        @endif
                    @endif

                    {{-- Notas --}}
                    <div class="space-y-2">
                        <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest block">Notas</label>
                        <textarea wire:model="notes" rows="2"
                            class="w-full bg-slate-50 border-none rounded-2xl px-4 py-3.5 text-sm font-medium text-slate-700 resize-none"></textarea>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mt-8">
                    <button wire:click="$set('showModal', false)"
                        class="py-4 bg-slate-50 text-slate-500 rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-slate-100 transition-all">Cancelar</button>
                    <button wire:click="save"
                        class="py-4 bg-teal-600 text-white rounded-2xl text-xs font-black uppercase tracking-widest shadow-lg shadow-teal-500/25 hover:bg-teal-700 transition-all">
                        {{ $editingId ? 'Guardar cambios' : 'Agregar recurso' }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

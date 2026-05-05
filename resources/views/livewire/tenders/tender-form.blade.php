<div class="min-h-screen bg-slate-50/50 -m-4 lg:-m-6">
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a wire:navigate href="{{ route('tenders.index') }}"
                    class="flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-100 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <div>
                    <h1 class="text-lg font-bold text-slate-800">{{ $tender?->exists ? 'Editar Licitación' : 'Nueva Licitación' }}</h1>
                    <p class="text-[11px] text-slate-400 uppercase tracking-wider">{{ $tender?->folio ?? 'Nuevo registro' }}</p>
                </div>
            </div>
            <button type="button" wire:click="save"
                class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all shadow-lg shadow-indigo-500/25">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                Guardar
            </button>
        </div>
    </div>

    <div class="max-w-6xl mx-auto p-4 sm:p-6 lg:p-8 space-y-6">
        {{-- Datos generales --}}
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 lg:p-8 space-y-5">
            <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest">Información General</h3>
            <div>
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Nombre de la licitación *</label>
                <input wire:model="name" type="text" placeholder="Ej. Instalación eléctrica edificio PEMEX..."
                    class="w-full mt-1 px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold focus:ring-4 focus:ring-indigo-500/5 focus:border-indigo-500">
                @error('name') <p class="text-[10px] text-red-500 mt-1 ml-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Descripción general</label>
                <textarea wire:model="description" rows="2" placeholder="Alcance general de la licitación..."
                    class="w-full mt-1 px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm resize-none focus:ring-4 focus:ring-indigo-500/5 focus:border-indigo-500"></textarea>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Tipo *</label>
                    <select wire:model="type" class="w-full mt-1 px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold">
                        @foreach($types as $k => $v)
                            <option value="{{ $k }}">{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Estado</label>
                    <select wire:model="status" class="w-full mt-1 px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold">
                        @foreach($statuses as $k => $v)
                            <option value="{{ $k }}">{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Sucursal</label>
                    <select wire:model="branch_id" class="w-full mt-1 px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                        <option value="">— Sin sucursal —</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Responsable</label>
                    <select wire:model="responsible_user_id" class="w-full mt-1 px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                        <option value="">— Sin asignar —</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Cliente / Convocante</label>
                    <select wire:model="customer_id" class="w-full mt-1 px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                        <option value="">— Sin cliente —</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Proyecto vinculado</label>
                    <select wire:model="project_id" class="w-full mt-1 px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                        <option value="">— Sin proyecto —</option>
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Fecha de entrega</label>
                    <input wire:model="submission_date" type="date" class="w-full mt-1 px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Fecha de apertura</label>
                    <input wire:model="opening_date" type="date" class="w-full mt-1 px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Fecha de adjudicación</label>
                    <input wire:model="award_date" type="date" class="w-full mt-1 px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Presupuesto estimado</label>
                    <input wire:model="estimated_budget" type="number" step="0.01" min="0" placeholder="0.00"
                        class="w-full mt-1 px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-mono">
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Monto adjudicado</label>
                    <input wire:model="awarded_amount" type="number" step="0.01" min="0" placeholder="0.00"
                        class="w-full mt-1 px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-mono">
                </div>
            </div>
            <div>
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Retroalimentación / Resultado</label>
                <textarea wire:model="feedback" rows="2" placeholder="Notas sobre el resultado de la licitación..."
                    class="w-full mt-1 px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm resize-none focus:ring-4 focus:ring-indigo-500/5 focus:border-indigo-500"></textarea>
            </div>
            <div>
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Notas internas</label>
                <textarea wire:model="notes" rows="2"
                    class="w-full mt-1 px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm resize-none focus:ring-4 focus:ring-indigo-500/5 focus:border-indigo-500"></textarea>
            </div>
        </div>

        {{-- Partidas --}}
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/30 flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest">Partidas / Conceptos</h3>
                    <p class="text-[10px] text-slate-400 mt-0.5">Selecciona del catálogo o escribe manualmente</p>
                </div>
                <button type="button" wire:click="addItem"
                    class="inline-flex items-center gap-1.5 text-[10px] font-black text-indigo-600 hover:text-indigo-800 uppercase tracking-wider">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Agregar partida
                </button>
            </div>

            {{-- Selector rápido de producto --}}
            <div class="px-4 py-3 bg-indigo-50/50 border-b border-indigo-100">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
                    <select id="productPicker" onchange="addProductRow(this)"
                        class="flex-1 px-3 py-1.5 bg-white border border-indigo-200 rounded-xl text-xs text-slate-700 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500/10">
                        <option value="">— Buscar en catálogo de productos/servicios —</option>
                        @foreach($products->groupBy(fn($p) => $p->category?->name ?? 'Sin categoría') as $cat => $prods)
                            <optgroup label="{{ $cat }}">
                                @foreach($prods as $p)
                                    <option value="{{ $p->id }}"
                                        data-name="{{ $p->name }}"
                                        data-sku="{{ $p->sku ?? '' }}"
                                        data-price="{{ $p->sale_price }}"
                                        data-unit="{{ $p->unitOfMeasure?->abbreviation ?? '' }}"
                                        data-category="{{ $p->category?->name ?? '' }}">
                                        {{ $p->name }}
                                        @if($p->sku) [{{ $p->sku }}] @endif
                                        — ${{ number_format($p->sale_price, 2) }}
                                        {{ $p->unitOfMeasure?->abbreviation ? '/ '.$p->unitOfMeasure->abbreviation : '' }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100">
                            <th class="text-left px-3 py-2.5 text-[10px] font-black text-slate-400 uppercase w-24">SKU</th>
                            <th class="text-left px-3 py-2.5 text-[10px] font-black text-slate-400 uppercase w-28">Categoría</th>
                            <th class="text-left px-3 py-2.5 text-[10px] font-black text-slate-400 uppercase">Descripción</th>
                            <th class="text-left px-3 py-2.5 text-[10px] font-black text-slate-400 uppercase w-16">Unidad</th>
                            <th class="text-right px-3 py-2.5 text-[10px] font-black text-slate-400 uppercase w-24">Cantidad</th>
                            <th class="text-right px-3 py-2.5 text-[10px] font-black text-slate-400 uppercase w-28">P. Unit.</th>
                            <th class="text-right px-3 py-2.5 text-[10px] font-black text-slate-400 uppercase w-28">Total</th>
                            <th class="w-10"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($items as $i => $row)
                            <tr class="hover:bg-slate-50/30 {{ $row['product_id'] ? 'bg-indigo-50/20' : '' }}">
                                <td class="px-2 py-2">
                                    <input wire:model="items.{{ $i }}.code" type="text"
                                        class="w-full px-2 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-mono"
                                        placeholder="SKU">
                                </td>
                                <td class="px-2 py-2">
                                    <input wire:model="items.{{ $i }}.category" type="text"
                                        class="w-full px-2 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs">
                                </td>
                                <td class="px-2 py-2">
                                    <div class="space-y-1">
                                        <input wire:model="items.{{ $i }}.description" type="text" placeholder="Descripción del concepto..."
                                            class="w-full px-2 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs">
                                        @if($row['product_id'])
                                            <span class="inline-flex items-center gap-1 text-[9px] text-indigo-500 font-bold">
                                                <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a8 8 0 100 16A8 8 0 0010 2zm1 11H9v-2h2v2zm0-4H9V7h2v2z"/></svg>
                                                Del catálogo
                                                <button type="button" wire:click="loadProduct({{ $i }}, null)"
                                                    class="text-slate-400 hover:text-red-500 ml-1">✕</button>
                                            </span>
                                        @endif
                                        @error("items.$i.description") <p class="text-[9px] text-red-500">{{ $message }}</p> @enderror
                                    </div>
                                </td>
                                <td class="px-2 py-2">
                                    <input wire:model="items.{{ $i }}.unit" type="text"
                                        class="w-full px-2 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs text-center">
                                </td>
                                <td class="px-2 py-2">
                                    <input wire:model.live="items.{{ $i }}.quantity" type="number" step="0.0001" min="0"
                                        wire:change="recalcItem({{ $i }})"
                                        class="w-full px-2 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs text-right font-mono">
                                </td>
                                <td class="px-2 py-2">
                                    <input wire:model.live="items.{{ $i }}.unit_price" type="number" step="0.0001" min="0"
                                        wire:change="recalcItem({{ $i }})"
                                        class="w-full px-2 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs text-right font-mono">
                                </td>
                                <td class="px-3 py-2 text-right">
                                    <span class="text-xs font-bold text-slate-700">${{ number_format($row['total'] ?? 0, 2) }}</span>
                                </td>
                                <td class="px-2 py-2 text-center">
                                    <button wire:click="removeItem({{ $i }})" class="p-1 text-slate-300 hover:text-red-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-slate-50/50 border-t-2 border-slate-200">
                        <tr>
                            <td colspan="6" class="px-4 py-3 text-right text-xs font-black text-slate-600 uppercase tracking-wider">Total licitación</td>
                            <td class="px-3 py-3 text-right text-base font-black text-indigo-600">${{ number_format($this->total, 2) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function addProductRow(select) {
    const opt = select.options[select.selectedIndex];
    if (!opt.value) return;
    const productId = parseInt(opt.value);
    const currentCount = @this.items.length;
    @this.call('addItem').then(() => {
        @this.call('loadProduct', currentCount, productId);
    });
    select.value = '';
}
</script>
@endpush

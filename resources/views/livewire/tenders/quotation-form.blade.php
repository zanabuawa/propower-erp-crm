<div class="min-h-screen bg-slate-50/50 -m-4 lg:-m-6">
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a wire:navigate href="{{ route('tenders.show', $tender) }}"
                    class="flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <div>
                    <h1 class="text-lg font-bold text-slate-800">Cotización — {{ $tender->name }}</h1>
                    <p class="text-[11px] text-slate-400 uppercase tracking-wider">Multi-empresa</p>
                </div>
            </div>
            <button type="button" wire:click="save"
                class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all shadow-lg shadow-indigo-500/25">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                Guardar
            </button>
        </div>
    </div>

    <div class="max-w-5xl mx-auto p-4 sm:p-6 lg:p-8 space-y-6">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 space-y-5">
            <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest">Empresa Emisora y Condiciones</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Empresa que emite la cotización *</label>
                    <select wire:model="issuing_company_id" class="w-full mt-1 px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold focus:ring-4 focus:ring-indigo-500/5 focus:border-indigo-500">
                        <option value="">— Seleccionar empresa —</option>
                        @foreach($companies as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->rfc }})</option>
                        @endforeach
                    </select>
                    @error('issuing_company_id') <p class="text-[10px] text-red-500 mt-1 ml-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Estado</label>
                    <select wire:model="status" class="w-full mt-1 px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                        @foreach($statuses as $k => $v)
                            <option value="{{ $k }}">{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Válida hasta</label>
                    <input wire:model="valid_until" type="date" class="w-full mt-1 px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm">
                </div>
                <div class="sm:col-span-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Notas</label>
                    <textarea wire:model="notes" rows="2" class="w-full mt-1 px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm resize-none"></textarea>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/30 flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest">Conceptos Cotizados</h3>
                    <p class="text-[10px] text-slate-400 mt-0.5">Pre-cargados desde la licitación. Ajusta precios por empresa emisora.</p>
                </div>
                <button type="button" wire:click="addItem"
                    class="inline-flex items-center gap-1.5 text-[10px] font-black text-indigo-600 uppercase tracking-wider">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Agregar
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="bg-slate-50/50 border-b border-slate-100">
                        <th class="text-left px-3 py-2.5 text-[10px] font-black text-slate-400 uppercase">Producto / Descripción</th>
                        <th class="text-left px-3 py-2.5 text-[10px] font-black text-slate-400 uppercase w-16">Unidad</th>
                        <th class="text-right px-3 py-2.5 text-[10px] font-black text-slate-400 uppercase w-24">Cantidad</th>
                        <th class="text-right px-3 py-2.5 text-[10px] font-black text-slate-400 uppercase w-28">P. Unit.</th>
                        <th class="text-right px-3 py-2.5 text-[10px] font-black text-slate-400 uppercase w-28">Total</th>
                        <th class="w-10"></th>
                    </tr></thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($items as $i => $row)
                            <tr>
                                <td class="px-2 py-2 space-y-1">
                                    @php $grouped = $products->groupBy(fn($p) => $p->category?->name ?? 'Sin categoría'); @endphp
                                    <select onchange="fillQuotationRow({{ $i }}, this.value)"
                                        class="w-full px-2 py-1.5 bg-indigo-50 border border-indigo-100 rounded-lg text-[10px] text-indigo-700 font-bold">
                                        <option value="">— Buscar producto del catálogo —</option>
                                        @foreach($grouped as $cat => $prods)
                                            <optgroup label="{{ $cat }}">
                                                @foreach($prods as $prod)
                                                    <option value="{{ $prod->id }}"
                                                        data-name="{{ $prod->name }}"
                                                        data-unit="{{ $prod->unitOfMeasure?->abbreviation ?? '' }}"
                                                        data-price="{{ $prod->sale_price }}">
                                                        {{ $prod->name }} — ${{ number_format($prod->sale_price, 2) }}
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                    <input wire:model="items.{{ $i }}.description" type="text" placeholder="Descripción del concepto"
                                        class="w-full px-2 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs">
                                </td>
                                <td class="px-2 py-2">
                                    <input wire:model="items.{{ $i }}.unit" type="text" id="quot-unit-{{ $i }}"
                                        class="w-full px-2 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs text-center">
                                </td>
                                <td class="px-2 py-2">
                                    <input wire:model.live="items.{{ $i }}.quantity" type="number" step="0.01" min="0"
                                        wire:change="recalcItem({{ $i }})"
                                        class="w-full px-2 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs text-right font-mono">
                                </td>
                                <td class="px-2 py-2">
                                    <input wire:model.live="items.{{ $i }}.unit_price" type="number" step="0.01" min="0"
                                        id="quot-price-{{ $i }}"
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
                    <tfoot class="bg-slate-50/50 border-t-2 border-slate-200"><tr>
                        <td colspan="4" class="px-4 py-3 text-right text-xs font-black text-slate-600 uppercase">Total Cotización</td>
                        <td class="px-3 py-3 text-right text-base font-black text-indigo-600">${{ number_format($this->total, 2) }}</td>
                        <td></td>
                    </tr></tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function fillQuotationRow(index, productId) {
    if (!productId) return;
    const sel = event.target;
    const opt = sel.options[sel.selectedIndex];
    const name  = opt.dataset.name  || '';
    const unit  = opt.dataset.unit  || '';
    const price = opt.dataset.price || '0';

    @this.call('loadProduct', index, parseInt(productId));

    const unitEl  = document.getElementById('quot-unit-' + index);
    const priceEl = document.getElementById('quot-price-' + index);
    if (unitEl)  { unitEl.value  = unit;  unitEl.dispatchEvent(new Event('input')); }
    if (priceEl) { priceEl.value = price; priceEl.dispatchEvent(new Event('input')); }
}
</script>
@endpush

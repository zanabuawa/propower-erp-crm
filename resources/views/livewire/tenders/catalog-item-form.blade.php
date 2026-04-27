<div class="min-h-screen bg-slate-50/50 -m-4 lg:-m-6">
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a wire:navigate href="{{ route('tenders.catalog.index') }}"
                    class="group flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-100 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <div>
                    <h1 class="text-lg font-bold text-slate-800">{{ $item?->exists ? 'Editar Concepto APU' : 'Nuevo Concepto APU' }}</h1>
                    <p class="text-[11px] text-slate-400 uppercase tracking-wider">Análisis de Precio Unitario</p>
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
        {{-- Datos del concepto --}}
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 lg:p-8 space-y-5">
            <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest">Datos del Concepto</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Categoría *</label>
                    <select wire:model="category_id" class="w-full mt-1 px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-4 focus:ring-indigo-500/5 focus:border-indigo-500">
                        <option value="">— Seleccionar —</option>
                        @foreach($categories as $c)
                            <option value="{{ $c->id }}">{{ $c->code ? "[$c->code] " : '' }}{{ $c->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id') <p class="text-[10px] text-red-500 mt-1 ml-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Código</label>
                    <input wire:model="code" type="text" placeholder="Ej. 01.02.03"
                        class="w-full mt-1 px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-mono focus:ring-4 focus:ring-indigo-500/5 focus:border-indigo-500">
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Unidad</label>
                    <input wire:model="unit" type="text" placeholder="m², ml, pza, hr..."
                        class="w-full mt-1 px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-4 focus:ring-indigo-500/5 focus:border-indigo-500">
                </div>
            </div>
            <div>
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Nombre / Descripción del concepto *</label>
                <input wire:model="name" type="text" placeholder="Ej. Instalación de tubo conduit 1 pulgada..."
                    class="w-full mt-1 px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold focus:ring-4 focus:ring-indigo-500/5 focus:border-indigo-500">
                @error('name') <p class="text-[10px] text-red-500 mt-1 ml-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Especificaciones técnicas</label>
                <textarea wire:model="description" rows="2" placeholder="Detalles técnicos adicionales..."
                    class="w-full mt-1 px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:ring-4 focus:ring-indigo-500/5 focus:border-indigo-500 resize-none"></textarea>
            </div>

            {{-- Porcentajes APU --}}
            <div class="pt-4 border-t border-slate-100">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Factores de Costo Indirecto</p>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="text-[10px] font-bold text-slate-500 ml-1">Indirectos %</label>
                        <input wire:model.live="indirect_pct" type="number" step="0.01" min="0" max="100"
                            class="w-full mt-1 px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm text-center font-bold focus:ring-4 focus:ring-indigo-500/5 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-500 ml-1">Overhead %</label>
                        <input wire:model.live="overhead_pct" type="number" step="0.01" min="0" max="100"
                            class="w-full mt-1 px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm text-center font-bold focus:ring-4 focus:ring-indigo-500/5 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-500 ml-1">Utilidad %</label>
                        <input wire:model.live="utility_pct" type="number" step="0.01" min="0" max="100"
                            class="w-full mt-1 px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm text-center font-bold focus:ring-4 focus:ring-indigo-500/5 focus:border-indigo-500">
                    </div>
                </div>
            </div>
        </div>

        {{-- Insumos APU --}}
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/30 flex items-center justify-between">
                <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest">Insumos (APU)</h3>
                <button type="button" wire:click="addResource"
                    class="inline-flex items-center gap-1.5 text-[10px] font-black text-indigo-600 hover:text-indigo-800 uppercase tracking-wider">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Agregar insumo
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100">
                            <th class="text-left px-3 py-2.5 text-[10px] font-black text-slate-400 uppercase tracking-wider w-28">Tipo</th>
                            <th class="text-left px-3 py-2.5 text-[10px] font-black text-slate-400 uppercase tracking-wider">Descripción</th>
                            <th class="text-left px-3 py-2.5 text-[10px] font-black text-slate-400 uppercase tracking-wider w-20">Unidad</th>
                            <th class="text-right px-3 py-2.5 text-[10px] font-black text-slate-400 uppercase tracking-wider w-24">Cantidad</th>
                            <th class="text-right px-3 py-2.5 text-[10px] font-black text-slate-400 uppercase tracking-wider w-28">Costo Unit.</th>
                            <th class="text-right px-3 py-2.5 text-[10px] font-black text-slate-400 uppercase tracking-wider w-28">Subtotal</th>
                            <th class="w-10"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($resources as $i => $r)
                            <tr class="hover:bg-slate-50/30">
                                <td class="px-2 py-2">
                                    <select wire:model="resources.{{ $i }}.type"
                                        class="w-full px-2 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold {{ $r['type'] === 'material' ? 'text-blue-600' : ($r['type'] === 'labor' ? 'text-green-600' : 'text-orange-600') }}">
                                        <option value="material">Material</option>
                                        <option value="labor">Mano obra</option>
                                        <option value="equipment">Equipo</option>
                                    </select>
                                </td>
                                <td class="px-2 py-2">
                                    <input wire:model="resources.{{ $i }}.description" type="text" placeholder="Descripción..."
                                        class="w-full px-2 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-400">
                                    @error("resources.$i.description") <p class="text-[9px] text-red-500 mt-0.5">{{ $message }}</p> @enderror
                                </td>
                                <td class="px-2 py-2">
                                    <input wire:model="resources.{{ $i }}.unit" type="text" placeholder="m², hr..."
                                        class="w-full px-2 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs text-center">
                                </td>
                                <td class="px-2 py-2">
                                    <input wire:model.live="resources.{{ $i }}.quantity" type="number" step="0.0001" min="0"
                                        class="w-full px-2 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs text-right font-mono">
                                </td>
                                <td class="px-2 py-2">
                                    <input wire:model.live="resources.{{ $i }}.unit_cost" type="number" step="0.0001" min="0"
                                        class="w-full px-2 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs text-right font-mono">
                                </td>
                                <td class="px-3 py-2 text-right">
                                    <span class="text-xs font-bold text-slate-700">${{ number_format(($r['quantity'] ?? 0) * ($r['unit_cost'] ?? 0), 2) }}</span>
                                </td>
                                <td class="px-2 py-2 text-center">
                                    <button wire:click="removeResource({{ $i }})" class="p-1 text-slate-300 hover:text-red-500 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-slate-50/50 border-t-2 border-slate-200">
                        <tr>
                            <td colspan="5" class="px-4 py-3 text-right text-xs font-bold text-slate-500 uppercase">Costo Directo</td>
                            <td class="px-3 py-3 text-right text-sm font-black text-slate-800">${{ number_format($this->directCost, 2) }}</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan="5" class="px-4 py-2 text-right text-[10px] font-bold text-slate-400 uppercase">Indirectos + Overhead + Utilidad ({{ $indirect_pct + $overhead_pct + $utility_pct }}%)</td>
                            <td class="px-3 py-2 text-right text-xs font-bold text-slate-600">${{ number_format($this->directCost * ($indirect_pct + $overhead_pct + $utility_pct) / 100, 2) }}</td>
                            <td></td>
                        </tr>
                        <tr class="border-t border-slate-200">
                            <td colspan="5" class="px-4 py-3 text-right text-xs font-black text-indigo-600 uppercase tracking-wider">Precio Unitario Final</td>
                            <td class="px-3 py-3 text-right text-base font-black text-indigo-600">${{ number_format($this->unitPrice, 2) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

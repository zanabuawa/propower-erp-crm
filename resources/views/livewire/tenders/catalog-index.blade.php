<div class="min-h-screen bg-slate-50/50 -m-4 lg:-m-6">
    {{-- HEADER --}}
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-lg font-bold text-slate-800">Catálogo APU</h1>
                <p class="text-[11px] text-slate-400 uppercase tracking-wider">Análisis de Precios Unitarios</p>
            </div>
            <div class="flex gap-2">
                <button type="button" wire:click="openCategoryModal(false)"
                    class="inline-flex items-center gap-2 px-3 py-2 bg-white border border-slate-200 text-slate-600 text-xs font-bold rounded-xl hover:border-indigo-200 hover:text-indigo-600 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Categoría
                </button>
                <a wire:navigate href="{{ route('tenders.catalog.create') }}"
                    class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold px-4 py-2 rounded-xl transition-all shadow-lg shadow-indigo-500/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Nuevo Concepto
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8 grid grid-cols-1 lg:grid-cols-4 gap-6">

        {{-- Árbol de categorías --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/30">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Categorías</p>
                </div>
                <div class="p-2 space-y-1">
                    <button wire:click="$set('category_id', null)"
                        class="w-full text-left px-3 py-2 rounded-xl text-xs font-bold transition-colors {{ is_null($category_id) ? 'bg-indigo-50 text-indigo-600' : 'text-slate-600 hover:bg-slate-50' }}">
                        Todos los conceptos
                    </button>
                    @foreach($categories as $cat)
                        <div>
                            <div class="flex items-center gap-1">
                                <button wire:click="$set('category_id', {{ $cat->id }})"
                                    class="flex-1 text-left px-3 py-2 rounded-xl text-xs font-bold transition-colors {{ $category_id == $cat->id ? 'bg-indigo-50 text-indigo-600' : 'text-slate-600 hover:bg-slate-50' }}">
                                    {{ $cat->code ? "[$cat->code] " : '' }}{{ $cat->name }}
                                </button>
                                <button wire:click="openCategoryModal(true, {{ $cat->id }})" class="p-1 text-slate-300 hover:text-indigo-500">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13l6-6m0 0l3.536 3.536M9 13H6v3h3"/></svg>
                                </button>
                                <button wire:click="deleteCategory({{ $cat->id }})" wire:confirm="¿Eliminar categoría?" class="p-1 text-slate-300 hover:text-red-500">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                            @foreach($cat->children as $child)
                                <button wire:click="$set('category_id', {{ $child->id }})"
                                    class="w-full text-left pl-7 pr-3 py-1.5 rounded-xl text-[11px] font-medium transition-colors {{ $category_id == $child->id ? 'bg-indigo-50 text-indigo-600' : 'text-slate-500 hover:bg-slate-50' }}">
                                    {{ $child->code ? "[$child->code] " : '' }}{{ $child->name }}
                                </button>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Lista de conceptos --}}
        <div class="lg:col-span-3 space-y-4">
            @if(session('success'))
                <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl text-sm font-bold">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="flex items-center gap-3 p-4 bg-red-50 border border-red-100 text-red-700 rounded-2xl text-sm font-bold">{{ session('error') }}</div>
            @endif

            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar concepto o código..."
                    class="w-full pl-10 pr-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm focus:ring-4 focus:ring-indigo-500/5 focus:border-indigo-500">
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100">
                            <th class="text-left px-4 py-3 text-[10px] font-black text-slate-400 uppercase tracking-wider">Código</th>
                            <th class="text-left px-4 py-3 text-[10px] font-black text-slate-400 uppercase tracking-wider">Concepto</th>
                            <th class="text-left px-4 py-3 text-[10px] font-black text-slate-400 uppercase tracking-wider">Unidad</th>
                            <th class="text-right px-4 py-3 text-[10px] font-black text-slate-400 uppercase tracking-wider">Precio Unit.</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($items as $item)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-4 py-3 text-xs font-mono text-slate-500">{{ $item->code ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <p class="font-bold text-slate-800 text-xs">{{ $item->name }}</p>
                                    <p class="text-[10px] text-slate-400">{{ $item->category?->name }}</p>
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-600">{{ $item->unit ?? '—' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <span class="font-black text-slate-800 text-xs">${{ number_format($item->unitPrice, 2) }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <a wire:navigate href="{{ route('tenders.catalog.edit', $item) }}"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13l6-6m0 0l3.536 3.536M9 13H6v3h3"/></svg>
                                        </a>
                                        <button wire:click="deleteItem({{ $item->id }})" wire:confirm="¿Eliminar concepto?"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-12 text-center text-slate-400 text-sm">Sin conceptos. Crea el primero.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                @if($items->hasPages())
                    <div class="px-4 py-3 border-t border-slate-100">{{ $items->links() }}</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Modal Categoría --}}
    @if($showCategoryModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md p-6 space-y-4">
            <h3 class="text-base font-black text-slate-800">{{ $editingCategory ? 'Editar' : 'Nueva' }} Categoría</h3>
            <div class="space-y-3">
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Código (opcional)</label>
                    <input wire:model="catCode" type="text" placeholder="Ej. 01.02"
                        class="w-full mt-1 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-4 focus:ring-indigo-500/5 focus:border-indigo-500">
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Nombre *</label>
                    <input wire:model="catName" type="text" placeholder="Nombre de la categoría"
                        class="w-full mt-1 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-4 focus:ring-indigo-500/5 focus:border-indigo-500">
                    @error('catName') <p class="text-[10px] text-red-500 mt-1 ml-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Categoría padre (opcional)</label>
                    <select wire:model="catParentId" class="w-full mt-1 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm">
                        <option value="">— Sin padre —</option>
                        @foreach($allCategories as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" wire:click="saveCategory"
                    class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black py-3 rounded-xl transition-all">
                    Guardar
                </button>
                <button type="button" wire:click="$set('showCategoryModal', false)"
                    class="px-4 py-3 border border-slate-200 text-slate-600 text-xs font-bold rounded-xl hover:bg-slate-50 transition-all">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
    @endif
</div>

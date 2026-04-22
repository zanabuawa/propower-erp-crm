<div class="min-h-screen bg-slate-50/50 -m-4 lg:-m-6">
    {{-- ── STICKY HEADER ────────────────────────────────────────────────── --}}
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 max-w-full mx-auto">
            <div class="flex items-center gap-3 min-w-0">
                <a wire:navigate href="{{ route('inventory.categories.index') }}" 
                   class="group flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-100 hover:shadow-sm transition-all duration-200">
                    <svg class="w-5 h-5 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl font-bold text-slate-800 truncate">
                        {{ $category?->exists ? 'Editar categoría' : 'Nueva categoría' }}
                    </h1>
                    <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">
                        {{ $category?->exists ? 'ID: ' . $category->id : 'Registro de clasificación' }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <a wire:navigate href="{{ route('inventory.categories.index') }}"
                    class="hidden sm:inline-flex px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 transition-colors">
                    Cancelar
                </a>
                <button type="button" wire:click="save"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:scale-[1.02] active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>{{ $category?->exists ? 'Guardar cambios' : 'Crear categoría' }}</span>
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-full mx-auto p-4 sm:p-6 lg:p-8">
        <form wire:submit="save" class="w-full space-y-6 lg:space-y-8">
            
            <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                <div class="p-6 lg:p-10">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
                        
                        {{-- Columna 1: Datos principales --}}
                        <div class="space-y-8">
                            {{-- Nombre --}}
                            <div class="relative group">
                                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-2 group-focus-within:text-indigo-500 transition-colors">Nombre de la categoría *</label>
                                <input wire:model="name" type="text"
                                    placeholder="Ej. Electrónica, Materiales Eléctricos, Oficina..."
                                    class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-lg font-semibold text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500/20 transition-all">
                                @error('name') <p class="text-xs text-rose-500 mt-2 font-medium flex items-center gap-1"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>{{ $message }}</p> @enderror
                            </div>

                            {{-- Categoría Padre --}}
                            <div class="space-y-2">
                                <label class="text-xs font-bold text-slate-600">Categoría superior (Opcional)</label>
                                <div class="relative">
                                    <select wire:model="parent_id"
                                        class="w-full bg-slate-50 border-none rounded-xl px-4 py-3 text-sm text-slate-700 focus:ring-2 focus:ring-indigo-500/20 cursor-pointer appearance-none pr-10" style="-webkit-appearance: none; -moz-appearance: none;">
                                        <option value="">— Sin categoría padre —</option>
                                        @foreach($parents as $parent)
                                            <option value="{{ $parent->id }}">
                                                {{ $parent->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </div>
                                </div>
                                <p class="text-[10px] text-slate-400 font-medium">Define una jerarquía si esta categoría es una sub-división</p>
                            </div>
                        </div>

                        {{-- Columna 2: Identidad y Estado --}}
                        <div class="space-y-8">
                            {{-- Color --}}
                            <div class="space-y-4">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Identificador Visual</label>
                                <div class="flex items-center gap-6 p-4 bg-slate-50 rounded-2xl">
                                    <div class="relative group">
                                        <input wire:model="color" type="color"
                                            class="h-14 w-24 rounded-xl border-none cursor-pointer bg-transparent">
                                        <div class="absolute inset-0 rounded-xl pointer-events-none border-2 border-white/20 shadow-inner"></div>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-xs font-bold text-slate-800">Color distintivo</p>
                                        <div class="flex items-center gap-2 mt-1">
                                            <p class="text-xs font-mono text-slate-500">{{ strtoupper($color) }}</p>
                                            <div class="w-3 h-3 rounded-full" style="background-color: {{ $color }}"></div>
                                        </div>
                                    </div>
                                    <div class="hidden sm:flex items-center gap-3 bg-white px-4 py-2 rounded-xl shadow-sm border border-slate-100">
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white font-bold text-xs" style="background-color: {{ $color }}">
                                            {{ strtoupper(substr($name ?: 'C', 0, 1)) }}
                                        </div>
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Previsualización</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Estado --}}
                            <div class="p-6 bg-slate-50 rounded-2xl flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-bold text-slate-800">Estado de la categoría</p>
                                    <p class="text-[10px] text-slate-400 uppercase tracking-wider mt-0.5">{{ $is_active ? 'Activa y disponible para nuevos productos' : 'Inactiva (oculta en selecciones)' }}</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input wire:model="is_active" type="checkbox" class="sr-only peer">
                                    <div class="w-14 h-7 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-emerald-500 after:content-[''] after:absolute after:top-[4px] after:left-[4px] after:bg-white after:rounded-full after:h-6 after:w-6 after:transition-all"></div>
                                </label>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Sección de Ayuda / Info --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-indigo-600 rounded-[2.5rem] p-8 text-white shadow-xl shadow-indigo-200 relative overflow-hidden group">
                    <div class="relative z-10">
                        <div class="w-12 h-12 bg-white/10 rounded-2xl flex items-center justify-center mb-4 backdrop-blur-md">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <h4 class="text-lg font-bold mb-2">Clasificación Inteligente</h4>
                        <p class="text-xs text-indigo-100 leading-relaxed">Organizar tus productos en categorías y subcategorías permite obtener reportes de inventario más precisos y facilita la búsqueda en ventas.</p>
                    </div>
                    <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-white/5 rounded-full blur-3xl group-hover:bg-white/10 transition-colors duration-500"></div>
                </div>
                
                <div class="md:col-span-2 bg-white rounded-[2.5rem] p-8 border border-slate-200/60 shadow-sm flex items-center gap-8">
                    <div class="hidden sm:flex shrink-0 w-24 h-24 bg-slate-50 rounded-3xl items-center justify-center border border-slate-100 shadow-inner">
                        <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                    </div>
                    <div>
                        <h4 class="text-lg font-bold text-slate-800 mb-2">Etiquetas Visuales</h4>
                        <p class="text-sm text-slate-500 leading-relaxed">El color seleccionado ayudará a identificar rápidamente los productos pertenecientes a esta categoría en el Dashboard y en el punto de venta, mejorando la agilidad visual de los operadores.</p>
                    </div>
                </div>
            </div>

        </form>
    </div>
</div>

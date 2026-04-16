<div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center gap-3 mb-6 lg:mb-8">
        <a wire:navigate href="{{ route('inventory.categories.index') }}" 
           class="text-gray-400 hover:text-gray-600 transition-colors duration-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="text-xl sm:text-2xl font-semibold bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent">
            {{ $category?->exists ? 'Editar categoría' : 'Nueva categoría' }}
        </h1>
    </div>

    <form wire:submit="save" class="space-y-5 lg:space-y-6">
        <div class="bg-white rounded-xl lg:rounded-2xl border border-gray-200 p-4 lg:p-6 shadow-sm hover:shadow-md transition-shadow duration-300 space-y-4 lg:space-y-5">
            
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Nombre *</label>
                <input wire:model="name" type="text" value="{{ $name }}"
                    class="w-full border border-gray-200 rounded-lg lg:rounded-xl px-3 lg:px-4 py-2 lg:py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                    placeholder="Ej. Electrónica, Ferretería, Oficina...">
                @error('name') <p class="text-xs text-red-500 mt-1.5 flex items-center gap-1"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Categoría padre</label>
                <select wire:model="parent_id"
                    class="w-full border border-gray-200 rounded-lg lg:rounded-xl px-3 lg:px-4 py-2 lg:py-2.5 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all cursor-pointer hover:border-indigo-300">
                    <option value="">— Sin categoría padre —</option>
                    @foreach($parents as $parent)
                        <option value="{{ $parent->id }}" {{ $parent_id == $parent->id ? 'selected' : '' }}>
                            {{ $parent->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Color</label>
                <div class="flex items-center gap-3">
                    <input wire:model="color" type="color" value="{{ $color }}"
                        class="h-9 lg:h-10 w-14 lg:w-16 rounded-lg border border-gray-200 cursor-pointer p-1">
                    <span class="text-sm text-gray-600 font-mono">{{ $color }}</span>
                </div>
            </div>

            <div class="pt-2">
                <label class="flex items-center gap-3 cursor-pointer group">
                    <input wire:model="is_active" type="checkbox" class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                        {{ $is_active ? 'checked' : '' }}>
                    <span class="text-sm font-medium text-gray-700 group-hover:text-indigo-600 transition-colors">Categoría activa</span>
                </label>
                <p class="text-xs text-gray-400 mt-1.5 ml-7">Las categorías inactivas no estarán disponibles para nuevos productos</p>
            </div>
        </div>

        {{-- ── Botones de acción con contenedor transparente ─────────────────── --}}
        <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pb-6 lg:pb-8 mt-4 lg:mt-6">
            <a wire:navigate href="{{ route('inventory.categories.index') }}"
                class="px-4 lg:px-6 py-2 lg:py-2.5 text-sm font-medium border border-gray-300 hover:border-gray-400 rounded-lg lg:rounded-xl transition-all text-center text-gray-700 hover:bg-gray-50">
                Cancelar
            </a>
            <button type="submit"
                class="px-5 lg:px-7 py-2 lg:py-2.5 text-sm font-semibold bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white rounded-lg lg:rounded-xl transition-all duration-200 shadow-md shadow-indigo-500/20 hover:shadow-indigo-500/30">
                {{ $category?->exists ? 'Guardar cambios' : 'Crear categoría' }}
            </button>
        </div>
    </form>
</div>
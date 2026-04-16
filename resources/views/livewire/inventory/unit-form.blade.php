<div class="max-w-lg mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center gap-3 mb-6 lg:mb-8">
        <a wire:navigate href="{{ route('inventory.units.index') }}"
           class="text-gray-400 hover:text-gray-600 transition-colors duration-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="text-xl sm:text-2xl font-semibold bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent">
            {{ $unitOfMeasure?->exists ? 'Editar unidad' : 'Nueva unidad de medida' }}
        </h1>
    </div>

    <form wire:submit="save" class="space-y-5 lg:space-y-6">
        <div class="bg-white rounded-xl lg:rounded-2xl border border-gray-200 p-4 lg:p-6 shadow-sm space-y-4 lg:space-y-5">

            <h2 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M12 7h.01M3 5h18a2 2 0 012 2v10a2 2 0 01-2 2H3a2 2 0 01-2-2V7a2 2 0 012-2z"/>
                </svg>
                Datos de la unidad
            </h2>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Nombre *</label>
                <input wire:model="name" type="text" value="{{ $name }}"
                    placeholder="Ej. Kilogramo, Litro, Pieza…"
                    class="w-full border border-gray-200 rounded-lg lg:rounded-xl px-3 lg:px-4 py-2 lg:py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                @error('name') <p class="text-xs text-red-500 mt-1.5 flex items-center gap-1"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Abreviación *</label>
                <input wire:model="abbreviation" type="text" value="{{ $abbreviation }}" maxlength="10"
                    placeholder="Ej. kg, lt, pz"
                    class="w-full border border-gray-200 rounded-lg lg:rounded-xl px-3 lg:px-4 py-2 lg:py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all font-mono">
                @error('abbreviation') <p class="text-xs text-red-500 mt-1.5 flex items-center gap-1"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>{{ $message }}</p> @enderror
                <p class="text-xs text-gray-400 mt-1.5">Máximo 10 caracteres. Se mostrará junto al nombre del producto.</p>
            </div>

            <div class="pt-1">
                <label class="flex items-center gap-3 cursor-pointer group">
                    <input wire:model="is_active" type="checkbox"
                        class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                        {{ $is_active ? 'checked' : '' }}>
                    <span class="text-sm font-medium text-gray-700 group-hover:text-indigo-600 transition-colors">Unidad activa</span>
                </label>
                <p class="text-xs text-gray-400 mt-1.5 ml-7">Las unidades inactivas no estarán disponibles para nuevos productos</p>
            </div>
        </div>

        <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pb-6 lg:pb-8">
            <a wire:navigate href="{{ route('inventory.units.index') }}"
                class="px-4 lg:px-6 py-2 lg:py-2.5 text-sm font-medium border border-gray-300 hover:border-gray-400 rounded-lg lg:rounded-xl transition-all text-center text-gray-700 hover:bg-gray-50">
                Cancelar
            </a>
            <button type="submit"
                class="px-5 lg:px-7 py-2 lg:py-2.5 text-sm font-semibold bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white rounded-lg lg:rounded-xl transition-all duration-200 shadow-md shadow-indigo-500/20 hover:shadow-indigo-500/30">
                {{ $unitOfMeasure?->exists ? 'Guardar cambios' : 'Crear unidad' }}
            </button>
        </div>
    </form>
</div>

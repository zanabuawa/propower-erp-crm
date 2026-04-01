<div class="max-w-lg">
    <div class="flex items-center gap-3 mb-6">
        <a wire:navigate href="{{ route('inventory.units.index') }}" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="text-xl font-medium text-gray-900">
            {{ $unitOfMeasure?->exists ? 'Editar unidad' : 'Nueva unidad de medida' }}
        </h1>
    </div>

    <form wire:submit="save" class="space-y-5">
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Nombre *</label>
                <input wire:model="name" type="text" value="{{ $name }}"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Abreviación *</label>
                <input wire:model="abbreviation" type="text" value="{{ $abbreviation }}" maxlength="10"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                    placeholder="ej: pz, kg, lt">
                @error('abbreviation') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input wire:model="is_active" type="checkbox" class="w-4 h-4 rounded text-indigo-600"
                        {{ $is_active ? 'checked' : '' }}>
                    <span class="text-sm text-gray-700">Unidad activa</span>
                </label>
            </div>
        </div>

        <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pb-6">
            <a wire:navigate href="{{ route('inventory.units.index') }}"
                class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition text-center">Cancelar</a>
            <button type="submit"
                class="px-5 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition">
                {{ $unitOfMeasure?->exists ? 'Guardar cambios' : 'Crear unidad' }}
            </button>
        </div>
    </form>
</div>
<div class="max-w-lg mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center gap-3 mb-6 lg:mb-8">
        <a wire:navigate href="{{ route('inventory.warehouses.index') }}"
           class="text-gray-400 hover:text-gray-600 transition-colors duration-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="text-xl sm:text-2xl font-semibold bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent">
            {{ $warehouse?->exists ? 'Editar almacén' : 'Nuevo almacén' }}
        </h1>
    </div>

    <form wire:submit="save" class="space-y-5 lg:space-y-6">
        <div class="bg-white rounded-xl lg:rounded-2xl border border-gray-200 p-4 lg:p-6 shadow-sm space-y-4 lg:space-y-5">

            <h2 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                </svg>
                Datos del almacén
            </h2>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Sucursal *</label>
                <select wire:model="branch_id"
                    class="w-full border border-gray-200 rounded-lg lg:rounded-xl px-3 lg:px-4 py-2 lg:py-2.5 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all cursor-pointer hover:border-indigo-300">
                    <option value="">— Seleccionar sucursal —</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ $branch_id == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
                @error('branch_id') <p class="text-xs text-red-500 mt-1.5 flex items-center gap-1"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Nombre *</label>
                <input wire:model="name" type="text" value="{{ $name }}"
                    placeholder="Ej. Bodega principal, Almacén norte…"
                    class="w-full border border-gray-200 rounded-lg lg:rounded-xl px-3 lg:px-4 py-2 lg:py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                @error('name') <p class="text-xs text-red-500 mt-1.5 flex items-center gap-1"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Código</label>
                    <input wire:model="code" type="text" value="{{ $code }}" maxlength="20"
                        placeholder="Ej. ALM-01"
                        class="w-full border border-gray-200 rounded-lg lg:rounded-xl px-3 lg:px-4 py-2 lg:py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all font-mono">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Ubicación</label>
                    <input wire:model="location" type="text" value="{{ $location }}"
                        placeholder="Ej. Bodega norte, Piso 2"
                        class="w-full border border-gray-200 rounded-lg lg:rounded-xl px-3 lg:px-4 py-2 lg:py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                </div>
            </div>

            <div class="space-y-3 pt-1">
                <label class="flex items-center gap-3 cursor-pointer group">
                    <input wire:model="is_active" type="checkbox"
                        class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                        {{ $is_active ? 'checked' : '' }}>
                    <span class="text-sm font-medium text-gray-700 group-hover:text-indigo-600 transition-colors">Almacén activo</span>
                </label>
                <label class="flex items-start gap-3 cursor-pointer group">
                    <input wire:model="is_defective" type="checkbox"
                        class="w-4 h-4 rounded border-gray-300 text-amber-500 focus:ring-amber-400 mt-0.5"
                        {{ $is_defective ? 'checked' : '' }}>
                    <div>
                        <span class="text-sm font-medium text-gray-700 group-hover:text-amber-600 transition-colors">Almacén de productos defectuosos</span>
                        <p class="text-xs text-gray-400 mt-0.5">Las recepciones de tipo "Defectuoso" se dirigen automáticamente a este almacén por sucursal.</p>
                    </div>
                </label>
            </div>
        </div>

        <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pb-6 lg:pb-8">
            <a wire:navigate href="{{ route('inventory.warehouses.index') }}"
                class="px-4 lg:px-6 py-2 lg:py-2.5 text-sm font-medium border border-gray-300 hover:border-gray-400 rounded-lg lg:rounded-xl transition-all text-center text-gray-700 hover:bg-gray-50">
                Cancelar
            </a>
            <button type="submit"
                class="px-5 lg:px-7 py-2 lg:py-2.5 text-sm font-semibold bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white rounded-lg lg:rounded-xl transition-all duration-200 shadow-md shadow-indigo-500/20 hover:shadow-indigo-500/30">
                {{ $warehouse?->exists ? 'Guardar cambios' : 'Crear almacén' }}
            </button>
        </div>
    </form>
</div>

<div class="max-w-3xl">
    <div class="flex items-center gap-3 mb-6">
        <a wire:navigate href="{{ route('assets.index') }}" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="text-xl font-medium text-gray-900">
            {{ $asset && $asset->exists ? 'Editar activo: ' . $asset->folio : 'Nuevo activo fijo' }}
        </h1>
    </div>

    <form wire:submit="save" class="space-y-5">

        {{-- Identificación --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">Identificación del activo</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">Nombre / descripción corta *</label>
                    <input wire:model="name" type="text"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                        placeholder="Ej: Laptop Dell Inspiron 15, Camioneta Ford F-150 2022">
                    @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Categoría</label>
                    <select wire:model="category"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        <option value="">— Sin categoría —</option>
                        @foreach(\App\Models\FixedAsset::CATEGORIES as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Estado</label>
                    <select wire:model="status"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        @foreach(\App\Models\FixedAsset::STATUSES as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Marca</label>
                    <input wire:model="brand" type="text"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                        placeholder="Ej: Dell, Ford, HP">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Modelo</label>
                    <input wire:model="model" type="text"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                        placeholder="Ej: Inspiron 15, F-150">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Número de serie</label>
                    <input wire:model="serial_number" type="text"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                        placeholder="S/N o placa">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Fecha de adquisición</label>
                    <input wire:model="acquisition_date" type="date"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Costo de adquisición</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-xs text-gray-400">$</span>
                        <input wire:model="acquisition_cost" type="number" step="0.01" min="0"
                            class="w-full border border-gray-200 rounded-lg pl-6 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    </div>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">Descripción adicional</label>
                    <textarea wire:model="description" rows="2"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 resize-none"
                        placeholder="Notas o detalles adicionales del activo"></textarea>
                </div>
            </div>
        </div>

        {{-- Ubicación y asignación --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">Ubicación y asignación</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Sucursal</label>
                    <select wire:model.live="branch_id"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        <option value="">— Sin sucursal —</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Almacén / área</label>
                    <select wire:model="warehouse_id"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                        @if(!$branch_id) disabled @endif>
                        <option value="">— Sin almacén —</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">Asignado a (usuario)</label>
                    <select wire:model="assigned_to"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        <option value="">— Sin asignar —</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">Notas internas</label>
                    <input wire:model="notes" type="text"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>
            </div>
        </div>

        {{-- Depreciación --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">
                Depreciación
                <span class="text-xs text-gray-400 font-normal ml-1">— opcional, para cálculo automático mensual</span>
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Método contable</label>
                    <select wire:model="depreciation_method"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        <option value="">— Sin depreciación —</option>
                        <option value="linea_recta">Línea recta</option>
                        <option value="doble_saldo">Doble saldo decreciente</option>
                        <option value="suma_digitos">Suma de dígitos</option>
                    </select>
                    @error('depreciation_method') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Vida útil (años)</label>
                    <input wire:model="useful_life_years" type="number" min="1" max="50"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                        placeholder="Ej: 5">
                    @error('useful_life_years') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Valor de rescate</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-xs text-gray-400">$</span>
                        <input wire:model="salvage_value" type="number" step="0.01" min="0"
                            class="w-full border border-gray-200 rounded-lg pl-6 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                            placeholder="0">
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">
                        Tasa fiscal SAT (%)
                        @if($category)
                            <span class="text-indigo-500">
                                — sugerida: {{ number_format((\App\Models\FixedAsset::FISCAL_RATES[$category] ?? 0.10) * 100, 0) }}%
                            </span>
                        @endif
                    </label>
                    <div class="relative">
                        <input wire:model="fiscal_rate" type="number" step="0.01" min="0" max="100"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 pr-8 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                            placeholder="Ej: 25">
                        <span class="absolute right-3 top-2 text-xs text-gray-400">%</span>
                    </div>
                    @error('fiscal_rate') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pb-6">
            <a wire:navigate href="{{ route('assets.index') }}"
                class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition text-center">
                Cancelar
            </a>
            <button type="submit"
                class="px-5 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition">
                {{ $asset && $asset->exists ? 'Guardar cambios' : 'Crear activo' }}
            </button>
        </div>
    </form>
</div>

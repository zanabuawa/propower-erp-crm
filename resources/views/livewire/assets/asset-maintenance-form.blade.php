<div class="max-w-2xl">
    <div class="flex items-center gap-3 mb-6">
        <a wire:navigate href="{{ route('assets.maintenance.index') }}" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="text-xl font-medium text-gray-900">
            {{ $maintenance && $maintenance->exists ? 'Editar mantenimiento: '.$maintenance->folio : 'Nuevo mantenimiento' }}
        </h1>
    </div>

    <form wire:submit="save" class="space-y-5">

        {{-- Activo y tipo --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">Datos generales</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">Activo *</label>
                    <select wire:model="fixed_asset_id"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        <option value="">— Selecciona un activo —</option>
                        @foreach($assets as $asset)
                            <option value="{{ $asset->id }}">{{ $asset->folio }} — {{ $asset->name }}</option>
                        @endforeach
                    </select>
                    @error('fixed_asset_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Tipo *</label>
                    <select wire:model="type"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        @foreach(\App\Models\AssetMaintenance::TYPES as $k => $v)
                            <option value="{{ $k }}">{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Estado *</label>
                    <select wire:model="status"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        @foreach(\App\Models\AssetMaintenance::STATUSES as $k => $v)
                            <option value="{{ $k }}">{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Fecha programada *</label>
                    <input wire:model="scheduled_date" type="date"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    @error('scheduled_date') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Fecha de finalización</label>
                    <input wire:model="completed_date" type="date"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>
            </div>
        </div>

        {{-- Técnico --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">Técnico responsable</h2>
            <div class="flex gap-4 text-sm mb-3">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input wire:model.live="technicianType" type="radio" value="internal" class="text-indigo-600">
                    <span>Usuario interno</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input wire:model.live="technicianType" type="radio" value="external" class="text-indigo-600">
                    <span>Técnico externo</span>
                </label>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @if($technicianType === 'internal')
                    <div class="sm:col-span-2">
                        <label class="block text-xs text-gray-500 mb-1">Usuario</label>
                        <select wire:model="technician_user_id"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                            <option value="">— Sin asignar —</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Nombre del técnico</label>
                        <input wire:model="technician_name" type="text"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                            placeholder="Nombre completo">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Empresa / Proveedor</label>
                        <input wire:model="provider" type="text"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                            placeholder="Ej: Servicios Técnicos SA">
                    </div>
                @endif
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Costo estimado / real</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-xs text-gray-400">$</span>
                        <input wire:model="cost" type="number" step="0.01" min="0"
                            class="w-full border border-gray-200 rounded-lg pl-6 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    </div>
                </div>
            </div>
        </div>

        {{-- Programación preventiva --}}
        @if($type === 'preventive')
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">
                Programación preventiva
                <span class="text-xs text-gray-400 font-normal ml-1">— se programa el siguiente automáticamente al completar</span>
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Intervalo (meses)</label>
                    <input wire:model="interval_months" type="number" min="1" max="60"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                        placeholder="Ej: 6 para semestral">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Próxima fecha programada</label>
                    <input wire:model="next_scheduled_date" type="date"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>
            </div>
        </div>
        @endif

        {{-- Trabajo realizado --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">Detalle del trabajo</h2>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Trabajo realizado</label>
                <textarea wire:model="work_performed" rows="3"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 resize-none"
                    placeholder="Describe el trabajo realizado o a realizar..."></textarea>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Piezas o refacciones reemplazadas</label>
                <textarea wire:model="parts_replaced" rows="2"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 resize-none"
                    placeholder="Ej: Filtro de aceite, correa de distribución..."></textarea>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Observaciones</label>
                <textarea wire:model="observations" rows="2"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 resize-none"></textarea>
            </div>
        </div>

        <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pb-6">
            <a wire:navigate href="{{ route('assets.maintenance.index') }}"
                class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition text-center">Cancelar</a>
            <button type="submit"
                class="px-5 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition">
                {{ $maintenance && $maintenance->exists ? 'Guardar cambios' : 'Crear mantenimiento' }}
            </button>
        </div>
    </form>
</div>

<div class="max-w-3xl">
    <div class="flex items-center gap-3 mb-6">
        <a wire:navigate href="{{ route('assets.transfers.index') }}" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="text-xl font-medium text-gray-900">Nueva transferencia de activo</h1>
    </div>

    <form wire:submit="save" class="space-y-5">

        {{-- Selección de activo --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">Activo a transferir</h2>

            @if(!$selectedAsset)
                <div class="relative">
                    <input wire:model.live.debounce.300ms="assetSearch" type="text"
                        placeholder="Buscar activo por nombre, folio o número de serie..."
                        class="w-full border border-gray-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    @if(count($assetResults) > 0)
                        <div class="absolute top-full left-0 right-0 bg-white border border-gray-200 rounded-lg shadow-lg z-10 mt-1">
                            @foreach($assetResults as $result)
                                <button type="button" wire:click="selectAsset({{ $result['id'] }})"
                                    class="w-full text-left px-4 py-3 hover:bg-gray-50 transition flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $result['name'] }}</p>
                                        <p class="text-xs text-gray-400 font-mono">{{ $result['folio'] }}</p>
                                        @if($result['serial_number'])
                                            <p class="text-xs text-gray-400">S/N: {{ $result['serial_number'] }}</p>
                                        @endif
                                    </div>
                                    <div class="text-right flex-shrink-0">
                                        <p class="text-xs text-gray-500">{{ $result['branch']['name'] ?? '—' }}</p>
                                        @if($result['assigned_user'])
                                            <p class="text-xs text-gray-400">{{ $result['assigned_user']['name'] ?? '' }}</p>
                                        @endif
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
                @error('asset_id') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
            @else
                <div class="flex items-start justify-between bg-indigo-50 border border-indigo-100 rounded-lg p-4">
                    <div>
                        <p class="font-medium text-gray-900">{{ $selectedAsset->name }}</p>
                        <p class="text-xs text-gray-500 font-mono">{{ $selectedAsset->folio }}</p>
                        @if($selectedAsset->serial_number)
                            <p class="text-xs text-gray-500">S/N: {{ $selectedAsset->serial_number }}</p>
                        @endif
                        <div class="flex flex-wrap gap-3 mt-2 text-xs text-gray-500">
                            <span>Sucursal actual: <strong>{{ $selectedAsset->branch?->name ?? '—' }}</strong></span>
                            @if($selectedAsset->warehouse)
                                <span>Almacén: <strong>{{ $selectedAsset->warehouse->name }}</strong></span>
                            @endif
                            @if($selectedAsset->assignedUser)
                                <span>Asignado a: <strong>{{ $selectedAsset->assignedUser->name }}</strong></span>
                            @endif
                        </div>
                    </div>
                    <button type="button" wire:click="clearAsset"
                        class="text-gray-400 hover:text-gray-600 transition ml-4 flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            @endif
        </div>

        {{-- Destino --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">Destino de la transferencia</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Sucursal destino</label>
                    <select wire:model.live="to_branch_id"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        <option value="">— Sin sucursal —</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Almacén / área destino</label>
                    <select wire:model="to_warehouse_id"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                        @if(!$to_branch_id) disabled @endif>
                        <option value="">— Sin almacén —</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">Asignar a (usuario destino)</label>
                    <select wire:model="to_user_id"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        <option value="">— Sin asignar —</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Fecha y hora *</label>
                    <input wire:model="transferred_at" type="datetime-local"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    @error('transferred_at') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Motivo</label>
                    <input wire:model="reason" type="text"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                        placeholder="Ej: Reubicación, cambio de personal, mantenimiento">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">Notas</label>
                    <textarea wire:model="notes" rows="2"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 resize-none"></textarea>
                </div>
            </div>
        </div>

        <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pb-6">
            <a wire:navigate href="{{ route('assets.transfers.index') }}"
                class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition text-center">
                Cancelar
            </a>
            <button type="submit"
                class="px-5 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition">
                Registrar transferencia
            </button>
        </div>
    </form>
</div>

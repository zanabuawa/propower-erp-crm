<div class="max-w-2xl">
    <div class="flex items-center gap-3 mb-6">
        <a wire:navigate href="{{ route('assets.loans.index') }}" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="text-xl font-medium text-gray-900">Registrar préstamo de activo / herramienta</h1>
    </div>

    <form wire:submit="save" class="space-y-5">

        {{-- Activo --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">Activo a prestar</h2>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Activo *</label>
                <select wire:model="assetId"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    <option value="">— Selecciona un activo disponible —</option>
                    @foreach($availableAssets as $asset)
                        <option value="{{ $asset->id }}">
                            {{ $asset->folio }} — {{ $asset->name }}
                            @if($asset->category) ({{ \App\Models\FixedAsset::CATEGORIES[$asset->category] ?? $asset->category }}) @endif
                        </option>
                    @endforeach
                </select>
                @error('assetId') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Condición al entregar</label>
                <select wire:model="conditionOnLoan"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    <option value="good">Bueno</option>
                    <option value="fair">Regular</option>
                    <option value="damaged">Dañado</option>
                </select>
            </div>
        </div>

        {{-- Destinatario --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">Destinatario</h2>

            <div class="flex gap-4 text-sm">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input wire:model.live="recipientType" type="radio" value="user" class="text-indigo-600">
                    <span>Usuario del sistema</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input wire:model.live="recipientType" type="radio" value="external" class="text-indigo-600">
                    <span>Cuadrilla / externo</span>
                </label>
            </div>

            @if($recipientType === 'user')
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Usuario *</label>
                    <select wire:model="loanedToUserId"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        <option value="">— Selecciona usuario —</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                    @error('loanedToUserId') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            @else
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Nombre de la cuadrilla / persona *</label>
                    <input wire:model="loanedToName" type="text"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                        placeholder="Ej: Cuadrilla Norte, Juan Pérez (externo)">
                    @error('loanedToName') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            @endif
        </div>

        {{-- Fechas y propósito --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">Fechas y propósito</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Fecha de préstamo *</label>
                    <input wire:model="loanDate" type="date"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    @error('loanDate') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Fecha esperada de devolución</label>
                    <input wire:model="expectedReturnDate" type="date"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    @error('expectedReturnDate') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">Propósito / proyecto</label>
                    <input wire:model="purpose" type="text"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                        placeholder="Ej: Obra Calle Morelos, Mantenimiento planta">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">Notas internas</label>
                    <textarea wire:model="notes" rows="2"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 resize-none"></textarea>
                </div>
            </div>
        </div>

        <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pb-6">
            <a wire:navigate href="{{ route('assets.loans.index') }}"
                class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition text-center">
                Cancelar
            </a>
            <button type="submit"
                class="px-5 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition">
                Registrar préstamo
            </button>
        </div>
    </form>
</div>

<div class="max-w-2xl">
    <div class="flex items-center gap-3 mb-6">
        <a wire:navigate href="{{ route('finance.accounts.index') }}" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="text-xl font-medium text-gray-900">
            {{ $account && $account->exists ? 'Editar cuenta: ' . $account->code : 'Nueva cuenta' }}
        </h1>
    </div>

    <form wire:submit="save" class="space-y-5">

        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">Identificación</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Código *</label>
                    <input wire:model="code" type="text" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    @error('code') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Tipo *</label>
                    <select wire:model="type" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
                        <option value="banco">Banco</option>
                        <option value="caja">Caja</option>
                        <option value="credito">Crédito</option>
                        <option value="inversion">Inversión</option>
                        <option value="otro">Otro</option>
                    </select>
                    @error('type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">Nombre *</label>
                    <input wire:model="name" type="text" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">Datos bancarios</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Banco</label>
                    <input wire:model="bank_name" type="text" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Número de cuenta</label>
                    <input wire:model="account_number" type="text" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">CLABE interbancaria</label>
                    <input wire:model="clabe" type="text" maxlength="18" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    @error('clabe') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Moneda</label>
                    <select wire:model="currency" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
                        <option value="MXN">MXN</option>
                        <option value="USD">USD</option>
                        <option value="EUR">EUR</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Saldo inicial</label>
                    <input wire:model="opening_balance" type="number" step="0.01" min="0" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Sucursal</label>
                    <select wire:model="branch_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
                        <option value="">— Sin sucursal —</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">Notas</label>
                    <textarea wire:model="notes" rows="2" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 resize-none"></textarea>
                </div>
                <div class="sm:col-span-2 flex items-center gap-2">
                    <input wire:model="is_active" type="checkbox" id="is_active" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-300">
                    <label for="is_active" class="text-sm text-gray-700">Cuenta activa</label>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a wire:navigate href="{{ route('finance.accounts.index') }}"
                class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 border border-gray-200 rounded-lg transition">
                Cancelar
            </a>
            <button type="submit"
                class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition shadow-sm">
                {{ $account && $account->exists ? 'Guardar cambios' : 'Crear cuenta' }}
            </button>
        </div>
    </form>
</div>

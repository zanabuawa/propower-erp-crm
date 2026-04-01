<div class="max-w-3xl">
    <div class="flex items-center gap-3 mb-6">
        <a wire:navigate href="{{ route('suppliers.index') }}" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <h1 class="text-xl font-medium text-gray-900">
            {{ $supplier?->exists ? 'Editar proveedor' : 'Nuevo proveedor' }}
        </h1>
    </div>

    <form wire:submit="save" class="space-y-5">

        {{-- Tipo --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex gap-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input wire:model.live="type" type="radio" value="company" class="text-indigo-600">
                    <span class="text-sm font-medium text-gray-700">Empresa</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input wire:model.live="type" type="radio" value="person" class="text-indigo-600">
                    <span class="text-sm font-medium text-gray-700">Persona física</span>
                </label>
            </div>
        </div>

        {{-- Datos generales --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">Datos generales</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">
                        {{ $type === 'company' ? 'Razón social *' : 'Nombre completo *' }}
                    </label>
                    <input wire:model="name" type="text" value="{{ $name }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Código interno (No. proveedor)</label>
                    <input wire:model="internal_code" type="text"
                        placeholder="Ej. PROV-0042"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    <p class="text-xs text-gray-400 mt-1">Número o clave interna para identificar a este proveedor</p>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">RFC</label>
                    <input wire:model="rfc" type="text" value="{{ $rfc }}" maxlength="13"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Régimen fiscal</label>
                    <input wire:model="tax_regime" type="text" value="{{ $tax_regime }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Sitio web</label>
                    <input wire:model="website" type="text" value="{{ $website }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Logo / Imagen</label>
                    <input wire:model="image" type="file" accept="image/*" class="w-full text-sm text-gray-500">
                    @if($supplier?->image)
                        <img src="{{ Storage::url($supplier->image) }}" class="mt-2 h-12 w-12 rounded-full object-cover">
                    @endif
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Estado</label>
                    <select wire:model="status"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        <option value="active">Activo</option>
                        <option value="inactive">Inactivo</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Teléfonos --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-3">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h2 class="text-sm font-medium text-gray-700">Teléfonos</h2>
                <button type="button" wire:click="addPhone"
                    class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">+ Agregar</button>
            </div>
            @foreach($phones as $index => $phone)
                <div class="flex flex-wrap gap-2 items-center">
                    <input wire:model="phones.{{ $index }}.number" type="text" placeholder="Número"
                        class="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    <select wire:model="phones.{{ $index }}.type"
                        class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        <option value="mobile">Móvil</option>
                        <option value="office">Oficina</option>
                        <option value="home">Casa</option>
                        <option value="fax">Fax</option>
                    </select>
                    <label class="flex items-center gap-1 text-xs text-gray-500 whitespace-nowrap">
                        <input wire:model="phones.{{ $index }}.is_primary" type="checkbox" class="rounded">
                        Principal
                    </label>
                    @if(count($phones) > 1)
                        <button type="button" wire:click="removePhone({{ $index }})" class="text-red-400 hover:text-red-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Correos --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-3">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h2 class="text-sm font-medium text-gray-700">Correos electrónicos</h2>
                <button type="button" wire:click="addEmail"
                    class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">+ Agregar</button>
            </div>
            @foreach($emails as $index => $email)
                <div class="flex flex-wrap gap-2 items-center">
                    <input wire:model="emails.{{ $index }}.email" type="email" placeholder="correo@ejemplo.com"
                        class="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    <select wire:model="emails.{{ $index }}.type"
                        class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        <option value="work">Trabajo</option>
                        <option value="personal">Personal</option>
                        <option value="billing">Facturación</option>
                    </select>
                    <label class="flex items-center gap-1 text-xs text-gray-500 whitespace-nowrap">
                        <input wire:model="emails.{{ $index }}.is_primary" type="checkbox" class="rounded">
                        Principal
                    </label>
                    @if(count($emails) > 1)
                        <button type="button" wire:click="removeEmail({{ $index }})" class="text-red-400 hover:text-red-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Dirección --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">Dirección fiscal</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">Calle y número</label>
                    <input wire:model="address" type="text" value="{{ $address }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>
                @include('livewire.partials.location-fields')
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Código postal</label>
                    <input wire:model="zip_code" type="text" value="{{ $zip_code }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>
            </div>
        </div>

        {{-- Datos bancarios --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-3">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h2 class="text-sm font-medium text-gray-700">Cuentas bancarias</h2>
                <button type="button" wire:click="addBankAccount"
                    class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">+ Agregar cuenta</button>
            </div>
            @foreach($bankAccounts as $index => $account)
                <div class="border border-gray-100 rounded-lg p-4 space-y-3">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Banco</label>
                            <input wire:model="bankAccounts.{{ $index }}.bank_name" type="text"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Beneficiario</label>
                            <input wire:model="bankAccounts.{{ $index }}.beneficiary" type="text"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Número de cuenta</label>
                            <input wire:model="bankAccounts.{{ $index }}.account_number" type="text"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">CLABE</label>
                            <input wire:model="bankAccounts.{{ $index }}.clabe" type="text" maxlength="18"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 text-xs text-gray-500 cursor-pointer">
                            <input wire:model="bankAccounts.{{ $index }}.is_primary" type="checkbox" class="rounded">
                            Cuenta principal
                        </label>
                        @if(count($bankAccounts) > 1)
                            <button type="button" wire:click="removeBankAccount({{ $index }})"
                                class="text-xs text-red-500 hover:text-red-700">Eliminar</button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Condiciones comerciales --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">Condiciones comerciales</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Crédito otorgado</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-sm text-gray-400">$</span>
                        <input wire:model="credit_limit" type="number" step="0.01" min="0" value="{{ $credit_limit }}"
                            class="w-full border border-gray-200 rounded-lg pl-7 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Días de crédito</label>
                    <input wire:model="payment_terms" type="number" min="0" value="{{ $payment_terms }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                        placeholder="0 = contado">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">Notas / descripción</label>
                    <textarea wire:model="description" rows="3"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">{{ $description }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pb-6">
            <a wire:navigate href="{{ route('suppliers.index') }}"
                class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                Cancelar
            </a>
            <button type="submit"
                class="px-5 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition">
                {{ $supplier?->exists ? 'Guardar cambios' : 'Crear proveedor' }}
            </button>
        </div>
    </form>
</div>
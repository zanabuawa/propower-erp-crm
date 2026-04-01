<div class="max-w-2xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('companies.index') }}" class="text-gray-400 hover:text-gray-600 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="text-xl font-medium text-gray-900">
            {{ $company?->exists ? 'Editar empresa' : 'Nueva empresa' }}
        </h1>
    </div>

    <form wire:submit="save" class="space-y-5">
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">Datos generales</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">Nombre comercial *</label>
                    <input wire:model="name" type="text" value="{{ $name }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">Razón social</label>
                    <input wire:model="legal_name" type="text" value="{{ $legal_name }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">RFC</label>
                    <input wire:model="rfc" type="text" value="{{ $rfc }}" maxlength="13"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    @error('rfc') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Teléfono</label>
                    <input wire:model="phone" type="text" value="{{ $phone }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">Correo electrónico</label>
                    <input wire:model="email" type="email" value="{{ $email }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">Dirección</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">Calle y número</label>
                    <input wire:model="address" type="text" value="{{ $address }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>
                @include('livewire.partials.location-fields')
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">Logos</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Logo horizontal (sidebar expandido)</label>
                    <input wire:model="logo" type="file" accept="image/*" class="w-full text-sm text-gray-500">
                    @error('logo') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    @if($company?->logo)
                        <img src="{{ Storage::url($company->logo) }}" class="mt-2 h-10 object-contain rounded">
                    @endif
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Ícono cuadrado (sidebar contraído)</label>
                    <input wire:model="icon" type="file" accept="image/*" class="w-full text-sm text-gray-500">
                    @error('icon') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    @if($company?->icon)
                        <img src="{{ Storage::url($company->icon) }}" class="mt-2 h-10 w-10 object-contain rounded-lg">
                    @endif
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">Logo para impresiones</label>
                    <input wire:model="print_logo" type="file" accept="image/*" class="w-full text-sm text-gray-500">
                    @error('print_logo') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    @if($company?->print_logo)
                        <div class="mt-2 flex items-center gap-3">
                            <img src="{{ Storage::url($company->print_logo) }}" class="h-12 object-contain rounded border border-gray-200 bg-white p-1">
                            <img src="{{ Storage::url($company->print_logo) }}" class="h-12 object-contain rounded border border-gray-800 bg-gray-900 p-1">
                        </div>
                    @endif
                    <p class="text-xs text-gray-400 mt-1.5 leading-relaxed">
                        Este logo se usa en órdenes de compra, reportes y documentos impresos.
                        <strong>Se recomienda usar una versión con caracteres a color o en blanco y negro</strong>
                        para garantizar su visibilidad sobre fondos claros y oscuros.
                        Formatos sugeridos: PNG con fondo transparente o SVG.
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <label class="flex items-center gap-3 cursor-pointer">
                <input wire:model="is_active" type="checkbox" class="w-4 h-4 rounded text-indigo-600">
                <div>
                    <p class="text-sm font-medium text-gray-800">Empresa activa</p>
                    <p class="text-xs text-gray-400">Las empresas inactivas no pueden acceder al sistema</p>
                </div>
            </label>
        </div>

        <div class="flex items-center justify-end gap-3 pb-6">
            <a href="{{ route('companies.index') }}"
                class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                Cancelar
            </a>
            <button type="submit"
                class="px-5 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition">
                {{ $company?->exists ? 'Guardar cambios' : 'Crear empresa' }}
            </button>
        </div>
    </form>
</div>
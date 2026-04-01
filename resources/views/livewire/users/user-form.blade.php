<div class="max-w-3xl">
    <div class="flex items-center gap-3 mb-6">
        <a wire:navigate href="{{ route('users.index') }}" class="text-gray-400 hover:text-gray-600 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="text-xl font-medium text-gray-900">
            {{ $user?->exists ? 'Editar usuario' : 'Nuevo usuario' }}
        </h1>
    </div>

    <form wire:submit="save" class="space-y-5">
        {{-- Datos personales --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">Datos personales</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">Nombre completo *</label>
                    <input wire:model="name" type="text"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">Correo electrónico *</label>
                    <input wire:model="email" type="email"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">
                        {{ $user?->exists ? 'Nueva contraseña (dejar vacío para no cambiar)' : 'Contraseña *' }}
                    </label>
                    <input wire:model="password" type="password"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    @error('password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Confirmar contraseña</label>
                    <input wire:model="password_confirmation" type="password"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>
            </div>
        </div>

        {{-- Empresa y rol --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">Empresa y acceso</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Empresa</label>
                    <select wire:model.live="company_id"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        <option value="">— Sin empresa —</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}" {{ $company_id == $company->id ? 'selected' : '' }}>
                                {{ $company->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('company_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Sucursal</label>
                    <select wire:model="branch_id"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                        {{ !$company_id ? 'disabled' : '' }}>
                        <option value="">— Sin sucursal —</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ $branch_id == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('branch_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">Rol *</label>
                    <select wire:model.live="role"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        <option value="">— Seleccionar rol —</option>
                        @foreach($roles as $r)
                            <option value="{{ $r->name }}" {{ $role === $r->name ? 'selected' : '' }}>
                                {{ ucfirst($r->name) }}
                            </option>
                        @endforeach
                    </select>
                    @error('role') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Permisos granulares --}}
        @if($role)
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <div>
                    <h2 class="text-sm font-medium text-gray-700">Permisos granulares</h2>
                    <p class="text-xs text-gray-400 mt-0.5">
                        Los permisos en <span class="text-indigo-500 font-medium">azul</span> son del rol.
                        Puedes agregar permisos adicionales marcando las casillas en blanco.
                    </p>
                </div>
                <button type="button" wire:click="loadRolePermissions"
                    class="text-xs px-3 py-1.5 border border-gray-200 rounded-lg hover:bg-gray-50 transition text-gray-600 whitespace-nowrap">
                    Cargar permisos del rol
                </button>
            </div>

            @php $rolePerms = $this->rolePermissions; @endphp

            <div class="space-y-5">
                @foreach($this->groupedPermissions as $group)
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">
                        {{ $group['label'] }}
                    </p>
                    <div class="flex flex-wrap gap-x-4 gap-y-2">
                        @foreach($group['permissions'] as $perm)
                            @php
                                $fromRole   = in_array($perm['name'], $rolePerms);
                                $isSelected = in_array($perm['name'], $selectedPermissions);
                                $checked    = $fromRole || $isSelected;
                            @endphp
                            <label class="flex items-center gap-2 cursor-pointer group
                                {{ $fromRole ? 'opacity-70' : '' }}">
                                <input
                                    type="checkbox"
                                    wire:model="selectedPermissions"
                                    value="{{ $perm['name'] }}"
                                    {{ $fromRole ? 'disabled' : '' }}
                                    {{ $checked ? 'checked' : '' }}
                                    class="w-4 h-4 rounded border-gray-300
                                        {{ $fromRole ? 'text-indigo-400 cursor-not-allowed' : 'text-indigo-600 cursor-pointer' }}">
                                <span class="text-xs
                                    {{ $fromRole ? 'text-indigo-500 font-medium' : 'text-gray-600 group-hover:text-gray-900' }}">
                                    {{ $perm['actionLabel'] }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Estado --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <label class="flex items-center gap-3 cursor-pointer">
                <input wire:model="is_active" type="checkbox" class="w-4 h-4 rounded text-indigo-600"
                    {{ $is_active ? 'checked' : '' }}>
                <div>
                    <p class="text-sm font-medium text-gray-800">Usuario activo</p>
                    <p class="text-xs text-gray-400">Los usuarios inactivos no pueden iniciar sesión</p>
                </div>
            </label>
        </div>

        <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pb-6">
            <a wire:navigate href="{{ route('users.index') }}"
                class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                Cancelar
            </a>
            <button type="submit"
                class="px-5 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition">
                {{ $user?->exists ? 'Guardar cambios' : 'Crear usuario' }}
            </button>
        </div>
    </form>
</div>

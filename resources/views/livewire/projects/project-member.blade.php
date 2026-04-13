<div>
    {{-- Flash --}}
    @if (session()->has('success'))
        <div class="mb-4 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700 border border-green-200">
            {{ session('success') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Equipo del proyecto</h2>
            <p class="text-sm text-gray-500">{{ $members->count() }} miembro(s) registrado(s)</p>
        </div>
        <button wire:click="openForm"
                class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Agregar miembro
        </button>
    </div>

    {{-- Formulario inline --}}
    @if ($showForm)
        <div class="mb-6 rounded-xl border border-indigo-100 bg-indigo-50 p-5">
            <h3 class="text-sm font-semibold text-indigo-800 mb-4">
                {{ $editingId ? 'Editar miembro' : 'Agregar miembro' }}
            </h3>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">

                {{-- Usuario --}}
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Usuario <span class="text-red-500">*</span></label>
                    <select wire:model="user_id"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            {{ $editingId ? 'disabled' : '' }}>
                        <option value="">— Seleccionar —</option>
                        @foreach ($availableUsers as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                    @error('user_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Rol --}}
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Rol <span class="text-red-500">*</span></label>
                    <select wire:model="role"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="lider">Líder</option>
                        <option value="desarrollador">Desarrollador</option>
                        <option value="diseñador">Diseñador</option>
                        <option value="qa">QA</option>
                        <option value="observador">Observador</option>
                        <option value="otro">Otro</option>
                    </select>
                    @error('role') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Fecha ingreso --}}
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Fecha ingreso</label>
                    <input type="date" wire:model="joined_at"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"/>
                    @error('joined_at') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Fecha salida --}}
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Fecha salida</label>
                    <input type="date" wire:model="left_at"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"/>
                    @error('left_at') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Activo --}}
                <div class="flex items-center gap-3 pt-5">
                    <input type="checkbox" wire:model="is_active" id="is_active"
                           class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"/>
                    <label for="is_active" class="text-sm text-gray-700">Miembro activo</label>
                </div>

                {{-- Notas --}}
                <div class="sm:col-span-2 lg:col-span-3">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Notas</label>
                    <textarea wire:model="notes" rows="2"
                              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                              placeholder="Observaciones opcionales..."></textarea>
                    @error('notes') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Botones --}}
            <div class="mt-4 flex justify-end gap-3">
                <button wire:click="resetForm"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                    Cancelar
                </button>
                <button wire:click="save" wire:loading.attr="disabled"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition disabled:opacity-60">
                    <span wire:loading.remove wire:target="save">
                        {{ $editingId ? 'Actualizar' : 'Agregar' }}
                    </span>
                    <span wire:loading wire:target="save">Guardando...</span>
                </button>
            </div>
        </div>
    @endif

    {{-- Tabla de miembros --}}
    @if ($members->isEmpty())
        <div class="rounded-xl border-2 border-dashed border-gray-200 py-12 text-center">
            <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M17 20h5v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2h5M12 12a4 4 0 100-8 4 4 0 000 8z"/>
            </svg>
            <p class="mt-3 text-sm text-gray-500">No hay miembros registrados en este proyecto.</p>
            <button wire:click="openForm"
                    class="mt-3 text-sm font-medium text-indigo-600 hover:text-indigo-700">
                Agregar el primero
            </button>
        </div>
    @else
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-left">Usuario</th>
                        <th class="px-4 py-3 text-left">Rol</th>
                        <th class="px-4 py-3 text-left">Ingreso</th>
                        <th class="px-4 py-3 text-left">Salida</th>
                        <th class="px-4 py-3 text-center">Estado</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($members as $member)
                        <tr class="{{ $member->is_active ? '' : 'bg-gray-50 opacity-70' }} hover:bg-gray-50 transition">

                            {{-- Usuario --}}
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100 text-xs font-semibold text-indigo-700">
                                        {{ strtoupper(substr($member->user->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $member->user->name }}</p>
                                        <p class="text-xs text-gray-400">{{ $member->user->email }}</p>
                                    </div>
                                </div>
                            </td>

                            {{-- Rol --}}
                            <td class="px-4 py-3">
                                @php
                                    $roleColors = [
                                        'lider'        => 'bg-purple-100 text-purple-700',
                                        'desarrollador'=> 'bg-blue-100 text-blue-700',
                                        'diseñador'    => 'bg-pink-100 text-pink-700',
                                        'qa'           => 'bg-yellow-100 text-yellow-700',
                                        'observador'   => 'bg-gray-100 text-gray-600',
                                        'otro'         => 'bg-gray-100 text-gray-600',
                                    ];
                                    $color = $roleColors[$member->role] ?? 'bg-gray-100 text-gray-600';
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $color }}">
                                    {{ ucfirst($member->role) }}
                                </span>
                            </td>

                            {{-- Fechas --}}
                            <td class="px-4 py-3 text-gray-600">
                                {{ $member->joined_at?->format('d/m/Y') ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ $member->left_at?->format('d/m/Y') ?? '—' }}
                            </td>

                            {{-- Estado --}}
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                    {{ $member->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                                    {{ $member->is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>

                            {{-- Acciones --}}
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    {{-- Toggle activo --}}
                                    <button wire:click="toggleActive({{ $member->id }})"
                                            title="{{ $member->is_active ? 'Desactivar' : 'Activar' }}"
                                            class="rounded p-1 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 transition">
                                        @if ($member->is_active)
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                            </svg>
                                        @else
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        @endif
                                    </button>

                                    {{-- Editar --}}
                                    <button wire:click="edit({{ $member->id }})"
                                            class="rounded p-1 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>

                                    {{-- Eliminar --}}
                                    <button wire:click="delete({{ $member->id }})"
                                            wire:confirm="¿Eliminar este miembro del proyecto?"
                                            class="rounded p-1 text-gray-400 hover:text-red-600 hover:bg-red-50 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
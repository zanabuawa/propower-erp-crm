<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center gap-3 mb-6">
        <a wire:navigate href="{{ route('projects.index') }}" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="text-xl font-medium text-gray-900">
            {{ $project && $project->exists ? 'Editar proyecto: ' . $project->code : 'Nuevo proyecto' }}
        </h1>
    </div>

    <form wire:submit="save" class="space-y-5 lg:space-y-6">

        {{-- Identificación --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 lg:p-6 space-y-4 shadow-sm">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">Identificación</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 lg:gap-5">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Código *</label>
                    <input wire:model="code" type="text" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 transition-all">
                    @error('code') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Tipo *</label>
                    <select wire:model="type" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white transition-all cursor-pointer">
                        <option value="externo">Externo</option>
                        <option value="interno">Interno</option>
                        <option value="licitacion">Licitación</option>
                    </select>
                    @error('type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2 lg:col-span-1 xl:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">Nombre del proyecto *</label>
                    <input wire:model="name" type="text" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 transition-all">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2 lg:col-span-3 xl:col-span-4">
                    <label class="block text-xs text-gray-500 mb-1">Descripción</label>
                    <textarea wire:model="description" rows="3" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 resize-none transition-all"></textarea>
                    @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Asignación --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 lg:p-6 space-y-4 shadow-sm">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">Asignación</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 lg:gap-5">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Cliente</label>
                    <select wire:model="customer_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white transition-all cursor-pointer">
                        <option value="">— Sin cliente —</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Sucursal</label>
                    <select wire:model="branch_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white transition-all cursor-pointer">
                        <option value="">— Sin sucursal —</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Responsable</label>
                    <select wire:model="responsible_user_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white transition-all cursor-pointer">
                        <option value="">— Sin asignar —</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Estado</label>
                    <select wire:model="status" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white transition-all cursor-pointer">
                        <option value="borrador">Borrador</option>
                        <option value="activo">Activo</option>
                        <option value="pausado">Pausado</option>
                        <option value="completado">Completado</option>
                        <option value="cancelado">Cancelado</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Fechas y Presupuesto --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 lg:p-6 space-y-4 shadow-sm">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">Fechas y presupuesto</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 lg:gap-5">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Fecha de inicio</label>
                    <input wire:model="start_date" type="date" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 transition-all">
                    @error('start_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Fecha de fin estimada</label>
                    <input wire:model="end_date" type="date" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 transition-all">
                    @error('end_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Presupuesto</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-sm text-gray-400">$</span>
                        <input wire:model="budget" type="number" step="0.01" min="0" class="w-full border border-gray-200 rounded-lg pl-7 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 transition-all">
                    </div>
                    @error('budget') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Moneda</label>
                    <select wire:model="currency" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white transition-all cursor-pointer">
                        <option value="MXN">MXN</option>
                        <option value="USD">USD</option>
                        <option value="EUR">EUR</option>
                    </select>
                </div>
                <div class="sm:col-span-2 lg:col-span-3 xl:col-span-4">
                    <label class="block text-xs text-gray-500 mb-1">Notas</label>
                    <textarea wire:model="notes" rows="3" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 resize-none transition-all"></textarea>
                </div>
            </div>
        </div>

        <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pb-6">
            <a wire:navigate href="{{ route('projects.index') }}"
                class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 border border-gray-200 rounded-lg transition-colors text-center">
                Cancelar
            </a>
            <button type="submit"
                class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-all shadow-sm">
                {{ $project && $project->exists ? 'Guardar cambios' : 'Crear proyecto' }}
            </button>
        </div>

    </form>
</div>

<div>
    <div class="flex items-center gap-3 mb-6">
        <a wire:navigate href="{{ route('projects.show', $project) }}" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div class="flex-1">
            <h1 class="text-xl font-medium text-gray-900">Hitos del proyecto</h1>
            <p class="text-xs text-gray-400">{{ $project->code }} — {{ $project->name }}</p>
        </div>
        @can('edit projects')
        <button wire:click="create"
            class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nuevo hito
        </button>
        @endcan
    </div>

    <x-alert />

    {{-- Resumen de pagos --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-5">
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-400 mb-1">Total hitos</p>
            <p class="text-lg font-semibold text-gray-800">{{ $milestones->count() }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-400 mb-1">Total a cobrar</p>
            <p class="text-lg font-semibold text-gray-800">{{ $project->currency }} {{ number_format($totalPago, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-400 mb-1">Cobrado (completados)</p>
            <p class="text-lg font-semibold text-green-700">{{ $project->currency }} {{ number_format($totalCompletado, 2) }}</p>
        </div>
    </div>

    {{-- Formulario inline --}}
    @if($showForm)
    <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-5 mb-5">
        <h2 class="text-sm font-medium text-gray-700 mb-4">{{ $editingId ? 'Editar hito' : 'Nuevo hito' }}</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="sm:col-span-2">
                <label class="block text-xs text-gray-500 mb-1">Nombre del hito *</label>
                <input wire:model="name" type="text" placeholder="Ej. Entrega de diseños, Instalación fase 1..."
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Estado</label>
                <select wire:model="status" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
                    <option value="pendiente">Pendiente</option>
                    <option value="en_progreso">En progreso</option>
                    <option value="completado">Completado</option>
                    <option value="cancelado">Cancelado</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Fecha límite</label>
                <input wire:model="due_date" type="date"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Monto de cobro ({{ $project->currency }})</label>
                <input wire:model="payment_amount" type="number" step="0.01" min="0"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                <p class="text-xs text-gray-400 mt-1">Al crear, genera cashflow proyectado automáticamente.</p>
                @error('payment_amount') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Orden</label>
                <input wire:model="sort_order" type="number" min="0"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
            </div>
            <div class="sm:col-span-3">
                <label class="block text-xs text-gray-500 mb-1">Descripción</label>
                <textarea wire:model="description" rows="2"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 resize-none"></textarea>
            </div>
        </div>
        <div class="flex gap-2 mt-4">
            <button wire:click="save"
                class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition">
                {{ $editingId ? 'Guardar cambios' : 'Crear hito' }}
            </button>
            <button wire:click="resetForm"
                class="px-4 py-2 border border-gray-200 text-gray-600 text-sm rounded-lg hover:bg-gray-50 transition">
                Cancelar
            </button>
        </div>
    </div>
    @endif

    {{-- Lista de hitos --}}
    <div class="space-y-3">
        @forelse($milestones as $milestone)
        @php
            $statusColors = [
                'pendiente'   => 'bg-yellow-100 text-yellow-700',
                'en_progreso' => 'bg-blue-100 text-blue-700',
                'completado'  => 'bg-green-100 text-green-700',
                'cancelado'   => 'bg-red-100 text-red-700',
            ];
            $statusLabels = [
                'pendiente'   => 'Pendiente',
                'en_progreso' => 'En progreso',
                'completado'  => 'Completado',
                'cancelado'   => 'Cancelado',
            ];
            $isOverdue = $milestone->due_date && $milestone->status !== 'completado' && $milestone->due_date->isPast();
        @endphp
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm {{ $milestone->status === 'completado' ? 'opacity-75' : '' }}">
            <div class="flex items-start gap-3">
                {{-- Icono estado --}}
                <div class="mt-0.5 flex-shrink-0">
                    @if($milestone->status === 'completado')
                        <span class="w-6 h-6 flex items-center justify-center rounded-full bg-green-100 text-green-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </span>
                    @elseif($milestone->status === 'cancelado')
                        <span class="w-6 h-6 flex items-center justify-center rounded-full bg-red-100 text-red-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </span>
                    @else
                        <span class="w-6 h-6 flex items-center justify-center rounded-full bg-gray-100 text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </span>
                    @endif
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <span class="font-medium text-gray-900 text-sm {{ $milestone->status === 'completado' ? 'line-through text-gray-400' : '' }}">
                            {{ $milestone->name }}
                        </span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$milestone->status] ?? 'bg-gray-100 text-gray-600' }}">
                            {{ $statusLabels[$milestone->status] ?? $milestone->status }}
                        </span>
                        @if($isOverdue)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">
                            Vencido
                        </span>
                        @endif
                    </div>

                    @if($milestone->description)
                    <p class="text-xs text-gray-500 mb-1">{{ $milestone->description }}</p>
                    @endif

                    <div class="flex flex-wrap gap-4 text-xs text-gray-400 mt-1">
                        @if($milestone->due_date)
                        <span>
                            <svg class="w-3 h-3 inline mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Fecha límite: {{ $milestone->due_date->format('d/m/Y') }}
                        </span>
                        @endif
                        @if($milestone->completed_at)
                        <span class="text-green-600">
                            <svg class="w-3 h-3 inline mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Completado: {{ $milestone->completed_at->format('d/m/Y') }}
                        </span>
                        @endif
                        @if($milestone->payment_amount > 0)
                        <span class="{{ $milestone->status === 'completado' ? 'text-green-600 font-medium' : 'text-indigo-600 font-medium' }}">
                            <svg class="w-3 h-3 inline mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/></svg>
                            {{ $project->currency }} {{ number_format($milestone->payment_amount, 2) }}
                        </span>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-shrink-0">
                    @if($milestone->status !== 'completado' && $milestone->status !== 'cancelado')
                    @can('edit projects')
                    <button wire:click="complete({{ $milestone->id }})" wire:confirm="¿Marcar este hito como completado?"
                        class="text-green-600 hover:text-green-800 text-xs font-medium border border-green-200 rounded-lg px-2 py-1 hover:bg-green-50 transition">
                        Completar
                    </button>
                    @endcan
                    @endif
                    @can('edit projects')
                    <button wire:click="edit({{ $milestone->id }})" class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">
                        Editar
                    </button>
                    @endcan
                    @can('delete projects')
                    <button wire:click="delete({{ $milestone->id }})" wire:confirm="¿Eliminar este hito?"
                        class="text-red-400 hover:text-red-600 text-xs">
                        Eliminar
                    </button>
                    @endcan
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl border border-gray-200 p-10 text-center text-gray-400 text-sm">
            <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            Sin hitos definidos. Agrega el primero para estructurar el proyecto.
        </div>
        @endforelse
    </div>

    @if($milestones->count() > 0)
    <div class="mt-4 bg-blue-50 border border-blue-100 rounded-xl p-4 text-xs text-blue-700">
        <strong>Disparadores automáticos activos:</strong>
        Al crear un hito con monto de cobro se genera un <strong>cashflow proyectado</strong> en finanzas.
        Al marcar un hito como <strong>completado</strong>, el cashflow se marca como realizado automáticamente.
    </div>
    @endif
</div>

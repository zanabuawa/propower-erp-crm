<div>
    <div class="flex items-center gap-3 mb-6">
        <a wire:navigate href="{{ route('projects.show', $project) }}" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div class="flex-1">
            <h1 class="text-xl font-medium text-gray-900">Tablero de tareas</h1>
            <p class="text-xs text-gray-400">{{ $project->code }} — {{ $project->name }} &middot; Avance: {{ $project->progress }}%</p>
        </div>
    </div>

    <x-alert />

    {{-- Modal formulario --}}
    @if($showForm)
    <div class="fixed inset-0 bg-black/40 z-40 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6 space-y-4">
            <h2 class="text-base font-medium text-gray-900">{{ $editingId ? 'Editar tarea' : 'Nueva tarea' }}</h2>
            <div class="space-y-3">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Título *</label>
                    <input wire:model="title" type="text" autofocus
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Prioridad</label>
                        <select wire:model="priority" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
                            <option value="baja">Baja</option>
                            <option value="media">Media</option>
                            <option value="alta">Alta</option>
                            <option value="urgente">Urgente</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Estado</label>
                        <select wire:model="newStatus" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
                            <option value="pendiente">Pendiente</option>
                            <option value="en_progreso">En progreso</option>
                            <option value="revision">En revisión</option>
                            <option value="completada">Completada</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Asignar a</label>
                    <select wire:model="assigned_to" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
                        <option value="">— Sin asignar —</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Fecha límite</label>
                    <input wire:model="due_date" type="date" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>
            </div>
            <div class="flex gap-2 pt-2">
                <button wire:click="save" class="flex-1 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                    {{ $editingId ? 'Guardar' : 'Crear tarea' }}
                </button>
                <button wire:click="$set('showForm', false)" class="px-4 py-2 border border-gray-200 text-gray-600 text-sm rounded-lg hover:bg-gray-50 transition">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Kanban --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        @foreach(\App\Livewire\Projects\ProjectTaskBoard::$columns as $status => $label)
        @php
            $colTasks = $tasks[$status] ?? collect();
            $colColors = ['pendiente'=>'gray','en_progreso'=>'blue','revision'=>'yellow','completada'=>'green'];
            $c = $colColors[$status] ?? 'gray';
        @endphp
        <div class="bg-gray-50 rounded-xl border border-gray-200 flex flex-col min-h-[300px]">
            {{-- Header columna --}}
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-{{ $c }}-400"></span>
                    <span class="text-sm font-medium text-gray-700">{{ $label }}</span>
                    <span class="text-xs bg-gray-200 text-gray-600 rounded-full px-1.5">{{ $colTasks->count() }}</span>
                </div>
                @can('create projects')
                <button wire:click="openForm('{{ $status }}')" class="text-gray-400 hover:text-indigo-600 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </button>
                @endcan
            </div>

            {{-- Tareas --}}
            <div class="flex-1 p-3 space-y-2 overflow-y-auto">
                @forelse($colTasks as $task)
                @php
                    $pColors = ['baja'=>'green','media'=>'blue','alta'=>'orange','urgente'=>'red'];
                    $pc = $pColors[$task->priority] ?? 'gray';
                @endphp
                <div class="bg-white rounded-lg border border-gray-200 p-3 shadow-sm group">
                    <div class="flex items-start justify-between gap-2">
                        <p class="text-sm text-gray-800 font-medium leading-snug flex-1">{{ $task->title }}</p>
                        <span class="flex-shrink-0 w-2 h-2 rounded-full mt-1.5 bg-{{ $pc }}-400" title="{{ $task->priority }}"></span>
                    </div>
                    @if($task->assignedTo || $task->due_date)
                    <div class="mt-2 flex items-center justify-between text-xs text-gray-400">
                        <span>{{ $task->assignedTo?->name ?? '—' }}</span>
                        @if($task->due_date)
                        <span class="{{ $task->due_date->isPast() && $task->status !== 'completada' ? 'text-red-500' : '' }}">
                            {{ $task->due_date->format('d/m') }}
                        </span>
                        @endif
                    </div>
                    @endif
                    {{-- Acciones --}}
                    <div class="mt-2 flex items-center gap-2 opacity-0 group-hover:opacity-100 transition">
                        @can('edit projects')
                        <button wire:click="editTask({{ $task->id }})" class="text-xs text-indigo-500 hover:text-indigo-700">Editar</button>
                        @endcan
                        @if($status !== 'en_progreso')
                        <button wire:click="moveTask({{ $task->id }}, 'en_progreso')" class="text-xs text-blue-500 hover:text-blue-700">→ Progreso</button>
                        @endif
                        @if($status !== 'completada')
                        <button wire:click="moveTask({{ $task->id }}, 'completada')" class="text-xs text-green-500 hover:text-green-700">✓</button>
                        @endif
                        @can('delete projects')
                        <button wire:click="deleteTask({{ $task->id }})" wire:confirm="¿Eliminar?" class="text-xs text-red-400 hover:text-red-600 ml-auto">✕</button>
                        @endcan
                    </div>
                </div>
                @empty
                <p class="text-xs text-gray-400 text-center py-6">Sin tareas</p>
                @endforelse
            </div>
        </div>
        @endforeach
    </div>
</div>

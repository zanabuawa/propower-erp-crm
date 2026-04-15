<div>
    <div class="flex items-center gap-3 mb-6">
        <a wire:navigate href="{{ route('projects.index') }}" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div class="flex-1">
            <h1 class="text-xl font-medium text-gray-900">{{ $project->name }}</h1>
            <p class="text-xs text-gray-400">{{ $project->code }} &middot; <span class="capitalize">{{ $project->type }}</span></p>
        </div>
        <a wire:navigate href="{{ route('projects.board', $project) }}"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/></svg>
            Tablero
        </a>
        <a wire:navigate href="{{ route('projects.expenses.index', $project) }}"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
            Gastos
        </a>
        <a wire:navigate href="{{ route('projects.milestones', $project) }}"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            Hitos
        </a>
        @can('edit projects')
        <a wire:navigate href="{{ route('projects.edit', $project) }}"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            Editar
        </a>
        @endcan
    </div>

    <x-alert />

    {{-- Resumen --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-400 mb-1">Estado</p>
            @php $colors = ['borrador'=>'gray','activo'=>'green','pausado'=>'yellow','completado'=>'blue','cancelado'=>'red']; $color = $colors[$project->status] ?? 'gray'; @endphp
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-700 capitalize">{{ $project->status }}</span>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-400 mb-1">Avance</p>
            <div class="flex items-center gap-2">
                <div class="flex-1 bg-gray-200 rounded-full h-2">
                    <div class="bg-indigo-500 h-2 rounded-full transition-all" style="width: {{ $project->progress }}%"></div>
                </div>
                <span class="text-sm font-medium text-gray-700">{{ $project->progress }}%</span>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-400 mb-1">Presupuesto</p>
            <p class="text-sm font-semibold text-gray-800">{{ $project->currency }} {{ number_format($project->budget, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-400 mb-1">Costo real</p>
            <p class="text-sm font-semibold {{ $project->cost_actual > $project->budget ? 'text-red-600' : 'text-gray-800' }}">
                {{ $project->currency }} {{ number_format($project->cost_actual, 2) }}
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Tareas --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100">
                <h2 class="text-sm font-medium text-gray-700">Tareas ({{ $project->tasks->count() }})</h2>
                @can('create projects')
                <button wire:click="$toggle('showTaskForm')" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                    + Agregar
                </button>
                @endcan
            </div>

            @if($showTaskForm)
            <div class="px-5 py-3 bg-indigo-50 border-b border-indigo-100 space-y-3">
                <input wire:model="newTaskTitle" type="text" placeholder="Título de la tarea *"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                @error('newTaskTitle') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
                <div class="grid grid-cols-2 gap-2">
                    <select wire:model="newTaskAssigned" class="border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        <option value="">— Asignar a —</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                    <input wire:model="newTaskDue" type="date" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>
                <div class="flex gap-2">
                    <button wire:click="addTask" class="px-3 py-1.5 bg-indigo-600 text-white text-xs rounded-lg hover:bg-indigo-700 transition">Guardar</button>
                    <button wire:click="$set('showTaskForm', false)" class="px-3 py-1.5 border border-gray-200 text-gray-600 text-xs rounded-lg hover:bg-gray-50 transition">Cancelar</button>
                </div>
            </div>
            @endif

            <ul class="divide-y divide-gray-100">
                @forelse($project->tasks as $task)
                <li class="flex items-center gap-3 px-5 py-3 hover:bg-gray-50 group">
                    <button wire:click="toggleTaskStatus({{ $task->id }})" class="flex-shrink-0">
                        @if($task->status === 'completada')
                            <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        @else
                            <svg class="w-5 h-5 text-gray-300 hover:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="1.5"/></svg>
                        @endif
                    </button>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-800 truncate {{ $task->status === 'completada' ? 'line-through text-gray-400' : '' }}">{{ $task->title }}</p>
                        <p class="text-xs text-gray-400">
                            {{ $task->assignedTo?->name ?? 'Sin asignar' }}
                            @if($task->due_date) &middot; {{ $task->due_date->format('d/m/Y') }} @endif
                        </p>
                    </div>
                    @can('delete projects')
                    <button wire:click="deleteTask({{ $task->id }})" wire:confirm="¿Eliminar esta tarea?"
                        class="opacity-0 group-hover:opacity-100 text-red-400 hover:text-red-600 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                    @endcan
                </li>
                @empty
                <li class="px-5 py-8 text-center text-sm text-gray-400">Sin tareas registradas.</li>
                @endforelse
            </ul>
        </div>

        {{-- Info lateral --}}
        <div class="space-y-4">

            {{-- Detalles --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-3">
                <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-2">Detalles</h2>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Cliente</span>
                        <span class="text-gray-700">{{ $project->customer?->name ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Responsable</span>
                        <span class="text-gray-700">{{ $project->responsible?->name ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Inicio</span>
                        <span class="text-gray-700">{{ $project->start_date?->format('d/m/Y') ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Fin estimado</span>
                        <span class="text-gray-700">{{ $project->end_date?->format('d/m/Y') ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Sucursal</span>
                        <span class="text-gray-700">{{ $project->branch?->name ?? '—' }}</span>
                    </div>
                </div>
            </div>

            {{-- Hitos --}}
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-100">
                    <h2 class="text-sm font-medium text-gray-700">Hitos ({{ $project->milestones->count() }})</h2>
                </div>
                <ul class="divide-y divide-gray-100">
                    @forelse($project->milestones as $milestone)
                    <li class="px-5 py-3">
                        <p class="text-sm text-gray-800">{{ $milestone->name }}</p>
                        <p class="text-xs text-gray-400">{{ $milestone->due_date->format('d/m/Y') }} &middot; <span class="capitalize">{{ $milestone->status }}</span></p>
                    </li>
                    @empty
                    <li class="px-5 py-6 text-center text-sm text-gray-400">Sin hitos.</li>
                    @endforelse
                </ul>
            </div>

            {{-- Gastos --}}
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-100">
                    <h2 class="text-sm font-medium text-gray-700">Gastos ({{ $project->expenses->count() }})</h2>
                </div>
                <ul class="divide-y divide-gray-100">
                    @forelse($project->expenses->take(5) as $expense)
                    <li class="px-5 py-3 flex justify-between">
                        <div>
                            <p class="text-sm text-gray-800">{{ $expense->concept }}</p>
                            <p class="text-xs text-gray-400 capitalize">{{ $expense->category }}</p>
                        </div>
                        <span class="text-sm font-medium text-gray-700">{{ number_format($expense->amount, 2) }}</span>
                    </li>
                    @empty
                    <li class="px-5 py-6 text-center text-sm text-gray-400">Sin gastos.</li>
                    @endforelse
                </ul>
            </div>

        </div>
    </div>
</div>

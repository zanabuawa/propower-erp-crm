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
        <a wire:navigate href="{{ route('projects.gantt', $project) }}"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            Gantt
        </a>
        <a wire:navigate href="{{ route('projects.budget', $project) }}"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            Presupuesto
        </a>
        <a wire:navigate href="{{ route('projects.materials', $project) }}"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            Recursos
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
        <a wire:navigate href="{{ route('projects.time-tracking', $project) }}"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Tiempos
        </a>
        <a wire:navigate href="{{ route('projects.financial', $project) }}"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm border border-emerald-200 rounded-lg text-emerald-700 hover:bg-emerald-50 transition font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            Financiero
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

    {{-- Alertas de presupuesto y plazo --}}
    @php
        $budgetPct    = $project->budget > 0 ? ($project->cost_actual / $project->budget) * 100 : 0;
        $daysLeft     = $project->end_date ? now()->diffInDays($project->end_date, false) : null;
        $showOverBudget = $budgetPct > 90 && !in_array($project->status, ['completado','cancelado']);
        $showOverDue    = $daysLeft !== null && $daysLeft < 0 && !in_array($project->status, ['completado','cancelado']);
        $showNearDue    = $daysLeft !== null && $daysLeft >= 0 && $daysLeft <= 7 && !in_array($project->status, ['completado','cancelado']);
    @endphp
    @if($showOverBudget || $showOverDue || $showNearDue)
    <div class="mb-4 space-y-2">
        @if($showOverBudget)
        <div class="flex items-center gap-2 px-4 py-2.5 rounded-lg {{ $budgetPct >= 100 ? 'bg-red-50 border border-red-200 text-red-700' : 'bg-amber-50 border border-amber-200 text-amber-700' }} text-sm">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <span>
                @if($budgetPct >= 100)
                    <strong>Presupuesto excedido:</strong> {{ number_format($budgetPct, 1) }}% ejercido
                    (${{ number_format($project->cost_actual - $project->budget, 2) }} sobre el presupuesto).
                @else
                    <strong>Alerta presupuestal:</strong> {{ number_format($budgetPct, 1) }}% del presupuesto ejercido.
                @endif
                <a wire:navigate href="{{ route('projects.budget', $project) }}" class="underline ml-1">Ver presupuesto</a>
            </span>
        </div>
        @endif
        @if($showOverDue)
        <div class="flex items-center gap-2 px-4 py-2.5 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span><strong>Proyecto vencido:</strong> La fecha de entrega fue {{ $project->end_date->format('d/m/Y') }} ({{ abs($daysLeft) }} días de retraso).</span>
        </div>
        @elseif($showNearDue)
        <div class="flex items-center gap-2 px-4 py-2.5 rounded-lg bg-amber-50 border border-amber-200 text-amber-700 text-sm">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span><strong>Entrega próxima:</strong> Vence el {{ $project->end_date->format('d/m/Y') }} ({{ $daysLeft }} día{{ $daysLeft != 1 ? 's' : '' }}).</span>
        </div>
        @endif
    </div>
    @endif

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

            {{-- Personal asignado --}}
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100">
                    <div>
                        <h2 class="text-sm font-medium text-gray-700">
                            Personal
                            @php $activeCount = $project->employees->where('pivot.is_active', true)->count(); @endphp
                            ({{ $activeCount }})
                        </h2>
                        @if($labourCost > 0)
                        <p class="text-xs text-gray-400">MO: ${{ number_format($labourCost, 0) }}</p>
                        @endif
                    </div>
                    @can('edit projects')
                    <button wire:click="openEmployeeModal"
                            class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">+ Asignar</button>
                    @endcan
                </div>
                <ul class="divide-y divide-gray-100">
                    @forelse($project->employees->where('pivot.is_active', true) as $emp)
                    <li class="px-5 py-3 flex items-center justify-between gap-2 group">
                        <div class="flex items-center gap-2 min-w-0">
                            <div class="w-7 h-7 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0">
                                <span class="text-xs font-bold text-indigo-600">
                                    {{ strtoupper(substr($emp->first_name,0,1).substr($emp->last_name,0,1)) }}
                                </span>
                            </div>
                            <div class="min-w-0">
                                <a href="{{ route('hr.employees.show', $emp) }}" wire:navigate
                                   class="text-sm text-gray-800 hover:text-indigo-600 truncate block">{{ $emp->full_name }}</a>
                                <p class="text-xs text-gray-400 truncate">
                                    {{ $emp->pivot->role ?: '—' }}
                                    @if($emp->pivot->hours_assigned) · {{ $emp->pivot->hours_assigned }}h @endif
                                    @if($emp->pivot->cost_per_hour) · ${{ number_format($emp->pivot->cost_per_hour,2) }}/h @endif
                                </p>
                            </div>
                        </div>
                        @can('edit projects')
                        <button wire:click="removeEmployee({{ $emp->id }})" wire:confirm="¿Quitar a {{ $emp->first_name }} del proyecto?"
                                class="opacity-0 group-hover:opacity-100 text-gray-300 hover:text-red-500 transition flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                        @endcan
                    </li>
                    @empty
                    <li class="px-5 py-6 text-center text-sm text-gray-400">Sin personal asignado.</li>
                    @endforelse
                </ul>
                @if($project->saleOrder)
                <div class="px-5 py-2 bg-slate-50 border-t border-gray-100">
                    <p class="text-xs text-slate-500">Pedido: <span class="font-medium text-slate-700">{{ $project->saleOrder->folio }}</span></p>
                </div>
                @endif
            </div>

        </div>
    </div>

    {{-- Modal: Asignar empleado --}}
    @if($showEmployeeModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40"
         wire:click.self="$set('showEmployeeModal', false)">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md p-6">
            <h2 class="text-base font-semibold text-gray-800 mb-4">Asignar recurso humano al proyecto</h2>
            <div class="space-y-3">

                {{-- Toggle: interno / externo --}}
                <div class="flex rounded-lg border border-gray-200 overflow-hidden text-sm">
                    <button type="button" wire:click="$set('isExternal', false)"
                            class="flex-1 py-2 font-medium transition {{ !$isExternal ? 'bg-indigo-600 text-white' : 'bg-white text-gray-500 hover:bg-gray-50' }}">
                        Empleado interno
                    </button>
                    <button type="button" wire:click="$set('isExternal', true)"
                            class="flex-1 py-2 font-medium transition {{ $isExternal ? 'bg-indigo-600 text-white' : 'bg-white text-gray-500 hover:bg-gray-50' }}">
                        Contratista externo
                    </button>
                </div>

                @if(!$isExternal)
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Empleado <span class="text-red-500">*</span></label>
                    <select wire:model="addEmployeeId"
                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                        <option value="">Seleccionar empleado</option>
                        @foreach($availableEmployees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->last_name }} {{ $emp->first_name }}</option>
                        @endforeach
                    </select>
                    @error('addEmployeeId') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                @else
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nombre del contratista <span class="text-red-500">*</span></label>
                    <input wire:model="addExternalName" type="text" placeholder="Nombre completo / empresa..."
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    @error('addExternalName') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                @endif

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Rol en el proyecto</label>
                    <input wire:model="addEmployeeRole" type="text" placeholder="Ej: Supervisor, Operario, Técnico..."
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Fecha inicio</label>
                        <input wire:model="addEmployeeStart" type="date"
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Fecha fin</label>
                        <input wire:model="addEmployeeEnd" type="date"
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                        @error('addEmployeeEnd') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Horas asignadas</label>
                        <input wire:model="addEmployeeHours" type="number" min="0" step="0.5" placeholder="0"
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Costo por hora ({{ $project->currency }})</label>
                        <input wire:model="addEmployeeCostPerHour" type="number" min="0" step="0.01" placeholder="0.00"
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                        @error('addEmployeeCostPerHour') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
                @if($addEmployeeHours && $addEmployeeCostPerHour)
                <div class="bg-indigo-50 rounded-lg px-3 py-2 text-xs text-indigo-700 flex justify-between">
                    <span>Costo estimado de mano de obra:</span>
                    <span class="font-bold">{{ $project->currency }} {{ number_format((float)$addEmployeeHours * (float)$addEmployeeCostPerHour, 2) }}</span>
                </div>
                @endif
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Notas</label>
                    <textarea wire:model="addEmployeeNotes" rows="2"
                              class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30 resize-none"></textarea>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-5">
                <button wire:click="$set('showEmployeeModal', false)"
                        class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50">Cancelar</button>
                <button wire:click="assignEmployee"
                        class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">Asignar</button>
            </div>
        </div>
    </div>
    @endif
</div>

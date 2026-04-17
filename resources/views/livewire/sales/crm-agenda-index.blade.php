<div>
    <x-page-header title="Agenda CRM" description="Actividades, recordatorios y seguimientos">
        <x-slot:actions>
            <button wire:click="$set('showForm', true)"
                class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-medium px-5 py-2 rounded-xl transition-all shadow-lg shadow-indigo-500/25">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nueva actividad
            </button>
        </x-slot:actions>
    </x-page-header>

    <x-alert />

    {{-- Contadores rápidos --}}
    <div class="grid grid-cols-3 gap-4 mb-6">
        <button wire:click="showToday"
            class="bg-white rounded-2xl border p-4 text-left hover:border-amber-300 transition {{ $viewMode === 'today' ? 'border-amber-400 ring-2 ring-amber-100' : 'border-gray-200' }} shadow-sm">
            <p class="text-2xl font-bold {{ $summary['today_pending'] > 0 ? 'text-amber-600' : 'text-gray-400' }}">{{ $summary['today_pending'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Hoy pendiente</p>
        </button>
        <div class="bg-white rounded-2xl border p-4 shadow-sm {{ $summary['overdue'] > 0 ? 'border-red-200' : 'border-gray-200' }}">
            <p class="text-2xl font-bold {{ $summary['overdue'] > 0 ? 'text-red-600' : 'text-gray-400' }}">{{ $summary['overdue'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Vencidas</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4 shadow-sm">
            <p class="text-2xl font-bold text-blue-600">{{ $summary['this_week'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Esta semana</p>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-4 mb-5 shadow-sm">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <select wire:model.live="filterStatus" class="px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Todos los estados</option>
                @foreach(\App\Models\CrmActivity::STATUSES as $k => $v)
                    <option value="{{ $k }}">{{ $v }}</option>
                @endforeach
            </select>
            <select wire:model.live="filterType" class="px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Todos los tipos</option>
                @foreach(\App\Models\CrmActivity::TYPES as $k => $v)
                    <option value="{{ $k }}">{{ $v }}</option>
                @endforeach
            </select>
            <select wire:model.live="filterUser" class="px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Todos los usuarios</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                @endforeach
            </select>
            <div class="flex items-center gap-2">
                <label class="text-xs text-gray-500 whitespace-nowrap">Desde</label>
                <input wire:model.live="dateFrom" type="date" class="flex-1 border border-gray-200 rounded-xl px-3 py-2.5 text-sm bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="flex items-center gap-2">
                <label class="text-xs text-gray-500 whitespace-nowrap">Hasta</label>
                <input wire:model.live="dateTo" type="date" class="flex-1 border border-gray-200 rounded-xl px-3 py-2.5 text-sm bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>
        @if($viewMode === 'today')
            <div class="mt-3">
                <button wire:click="showAll" class="text-xs text-indigo-600 hover:underline">← Ver todas las actividades</button>
            </div>
        @endif
    </div>

    {{-- Lista de actividades --}}
    <div class="space-y-3">
        @forelse($activities as $activity)
            @php
                $isOverdue = $activity->isOverdue();
                $rowBg = match(true) {
                    $activity->status === 'completed' => 'bg-white opacity-60',
                    $isOverdue                        => 'bg-amber-50 border border-amber-200',
                    default                           => 'bg-white border border-gray-200',
                };
            @endphp
            <div class="rounded-2xl {{ $rowBg }} p-4 shadow-sm flex gap-4 items-start">
                <div class="text-2xl mt-0.5 shrink-0">{{ \App\Models\CrmActivity::TYPE_ICONS[$activity->type] ?? '📋' }}</div>
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-start gap-2 justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-900 {{ $activity->status === 'completed' ? 'line-through' : '' }}">{{ $activity->title }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">
                                {{ $activity->related_name }} · {{ \App\Models\CrmActivity::TYPES[$activity->type] ?? $activity->type }}
                                @if($activity->assignedTo) · {{ $activity->assignedTo->name }} @endif
                            </p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-medium border {{ \App\Models\CrmActivity::STATUS_COLORS[$activity->status] ?? '' }}">
                                {{ \App\Models\CrmActivity::STATUSES[$activity->status] ?? $activity->status }}
                            </span>
                            @if($activity->status === 'pending')
                                <button wire:click="openOutcomeModal({{ $activity->id }})"
                                    class="text-xs bg-emerald-600 hover:bg-emerald-700 text-white px-2.5 py-1 rounded-lg transition">
                                    ✓ Completar
                                </button>
                                <button wire:click="cancelActivity({{ $activity->id }})"
                                    class="text-xs text-red-400 hover:text-red-600 px-2 py-1 rounded-lg border border-red-100 hover:border-red-200 transition">
                                    Cancelar
                                </button>
                            @endif
                        </div>
                    </div>
                    @if($activity->description)
                        <p class="text-xs text-gray-500 mt-1.5">{{ $activity->description }}</p>
                    @endif
                    @if($activity->outcome)
                        <p class="text-xs text-gray-500 mt-1 italic">Resultado: {{ $activity->outcome }}</p>
                    @endif
                    <div class="flex items-center gap-3 mt-2 text-xs text-gray-400">
                        @if($activity->scheduled_at)
                            <span class="{{ $isOverdue ? 'text-amber-600 font-medium' : '' }}">
                                {{ $isOverdue ? '⚠ ' : '' }}{{ $activity->scheduled_at->format('d/m/Y H:i') }}
                            </span>
                        @endif
                        @if($activity->completed_at)
                            <span class="text-emerald-600">✓ {{ $activity->completed_at->format('d/m/Y H:i') }}</span>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center shadow-sm">
                <x-empty-state message="No hay actividades para el período seleccionado." />
            </div>
        @endforelse
    </div>

    {{-- Modal nueva actividad --}}
    @if($showForm)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-lg">
                <h2 class="text-base font-semibold text-gray-900 mb-4">Nueva actividad</h2>
                <div class="space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Tipo</label>
                            <select wire:model="activityType" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                                @foreach(\App\Models\CrmActivity::TYPES as $k => $v)
                                    <option value="{{ $k }}">{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Fecha programada *</label>
                            <input wire:model="activityScheduled" type="datetime-local"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                            @error('activityScheduled') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Título *</label>
                        <input wire:model="activityTitle" type="text"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        @error('activityTitle') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Descripción</label>
                        <textarea wire:model="activityDesc" rows="2"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Recordatorio</label>
                            <input wire:model="activityReminder" type="datetime-local"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Asignado a</label>
                            <select wire:model="activityAssigned" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Relacionado con</label>
                            <div class="flex gap-2">
                                <label class="flex items-center gap-1 text-xs cursor-pointer"><input type="radio" wire:model.live="linkedType" value="customer"> Cliente</label>
                                <label class="flex items-center gap-1 text-xs cursor-pointer"><input type="radio" wire:model.live="linkedType" value="prospect"> Prospecto</label>
                            </div>
                        </div>
                        <div>
                            @if($linkedType === 'customer')
                                <label class="block text-xs text-gray-500 mb-1">Cliente</label>
                                <select wire:model="linkedId" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                                    <option value="">— Opcional —</option>
                                    @foreach($customers as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                                    @endforeach
                                </select>
                            @else
                                <label class="block text-xs text-gray-500 mb-1">Prospecto</label>
                                <select wire:model="linkedId" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                                    <option value="">— Opcional —</option>
                                    @foreach($prospects as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="flex gap-2 mt-4">
                    <button wire:click="saveActivity" type="button"
                        class="flex-1 px-4 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium">
                        Guardar actividad
                    </button>
                    <button wire:click="$set('showForm', false)" type="button"
                        class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50">Cancelar</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal resultado al completar --}}
    @if($showOutcomeModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-md">
                <h2 class="text-base font-semibold text-gray-900 mb-2">Completar actividad</h2>
                <p class="text-sm text-gray-500 mb-4">Opcionalmente registra el resultado obtenido.</p>
                <textarea wire:model="outcome" rows="3" placeholder="Ej: Cliente interesado, agendaremos demo la próxima semana..."
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 mb-4"></textarea>
                <div class="flex gap-2">
                    <button wire:click="completeActivity" type="button"
                        class="flex-1 px-4 py-2 text-sm bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-medium">
                        ✓ Marcar completada
                    </button>
                    <button wire:click="$set('showOutcomeModal', false)" type="button"
                        class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50">Cancelar</button>
                </div>
            </div>
        </div>
    @endif
</div>

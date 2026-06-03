<div class="space-y-5">

    {{-- ── Header ─────────────────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
        <div class="flex items-center gap-3 flex-1 min-w-0">
            <a wire:navigate href="{{ route('sales.crm.pipeline') }}" class="text-gray-400 hover:text-gray-600 shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div class="min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-xl font-semibold text-gray-900 truncate">{{ $opportunity->title }}</h1>
                    <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-medium border
                        {{ \App\Models\SalesOpportunity::STAGE_COLORS[$opportunity->stage] ?? 'bg-gray-100 text-gray-600' }}">
                        {{ \App\Models\SalesOpportunity::STAGES[$opportunity->stage] ?? $opportunity->stage }}
                    </span>
                </div>
                <p class="text-sm text-gray-400 mt-0.5">{{ $opportunity->linked_name }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <a wire:navigate href="{{ route('sales.crm.opportunities.edit', $opportunity) }}"
                class="px-3 py-2 text-sm border border-gray-200 rounded-xl hover:bg-gray-50 transition text-gray-600">
                Editar
            </a>
            @if($opportunity->stage !== 'won' && $opportunity->stage !== 'lost')
                <button wire:click="convertToQuotation"
                    class="px-4 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl transition font-medium">
                    Crear cotización
                </button>
            @endif
        </div>
    </div>

    <x-alert />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- ── Columna izquierda: datos + etapas ──────────────────────────── --}}
        <div class="space-y-4">

            {{-- Datos clave --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm space-y-4">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Datos de la oportunidad</h3>

                <div>
                    <p class="text-xs text-gray-400 mb-0.5">Valor estimado</p>
                    <p class="text-2xl font-black text-gray-900">${{ number_format($opportunity->estimated_value, 0) }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">
                        Ponderado: <span class="font-semibold text-indigo-600">${{ number_format($opportunity->weightedValue(), 0) }}</span>
                        ({{ $opportunity->probability }}%)
                    </p>
                </div>

                <div class="border-t border-gray-100 pt-3 space-y-2.5">
                    @if($opportunity->expected_close_date)
                        <div class="flex justify-between items-center">
                            <p class="text-xs text-gray-400">Cierre estimado</p>
                            <p class="text-sm font-medium {{ $opportunity->expected_close_date->isPast() && $opportunity->isActive() ? 'text-red-600' : 'text-gray-700' }}">
                                {{ $opportunity->expected_close_date->format('d/m/Y') }}
                                @if($opportunity->expected_close_date->isPast() && $opportunity->isActive())
                                    <span class="text-xs">(vencido)</span>
                                @endif
                            </p>
                        </div>
                    @endif
                    <div class="flex justify-between items-center">
                        <p class="text-xs text-gray-400">Asignado a</p>
                        <p class="text-sm font-medium text-gray-700">{{ $opportunity->assignedTo?->name ?? '—' }}</p>
                    </div>
                    @if($opportunity->customer)
                        <div class="flex justify-between items-center">
                            <p class="text-xs text-gray-400">Cliente</p>
                            <a wire:navigate href="{{ route('customers.show', $opportunity->customer) }}"
                                class="text-sm font-medium text-emerald-600 hover:text-emerald-800 transition">
                                {{ $opportunity->customer->name }}
                            </a>
                        </div>
                    @endif
                    @if($opportunity->description)
                        <div class="pt-2 border-t border-gray-100">
                            <p class="text-xs text-gray-400 mb-1">Descripción</p>
                            <p class="text-sm text-gray-600">{{ $opportunity->description }}</p>
                        </div>
                    @endif
                    @if($opportunity->lost_reason)
                        <div class="flex justify-between items-center">
                            <p class="text-xs text-gray-400">Motivo pérdida</p>
                            <p class="text-sm font-medium text-red-600">
                                {{ \App\Models\SalesOpportunity::LOST_REASONS[$opportunity->lost_reason] ?? $opportunity->lost_reason }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Mover etapa --}}
            @if($opportunity->isActive())
            <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Avanzar etapa</h3>
                <div class="space-y-2">
                    @php
                        $stageKeys = array_keys(\App\Models\SalesOpportunity::STAGES);
                        $currentIdx = array_search($opportunity->stage, $stageKeys);
                    @endphp
                    @foreach(\App\Models\SalesOpportunity::STAGES as $key => $label)
                        @if($key !== 'lost')
                        <button wire:click="moveStage('{{ $key }}')"
                            class="w-full text-left px-3 py-2 rounded-xl text-sm transition flex items-center gap-2
                                {{ $opportunity->stage === $key
                                    ? \App\Models\SalesOpportunity::STAGE_COLORS[$key] . ' font-semibold border'
                                    : 'text-gray-500 hover:bg-gray-50' }}">
                            @php $idx = array_search($key, $stageKeys); @endphp
                            <span class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0
                                {{ $opportunity->stage === $key ? 'border-current' : ($idx < $currentIdx ? 'border-gray-300 bg-gray-200' : 'border-gray-200') }}">
                                @if($idx < $currentIdx)
                                    <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                @elseif($opportunity->stage === $key)
                                    <span class="w-2 h-2 rounded-full bg-current"></span>
                                @endif
                            </span>
                            {{ $label }}
                            <span class="ml-auto text-xs opacity-60">{{ \App\Models\SalesOpportunity::STAGE_PROBABILITY[$key] }}%</span>
                        </button>
                        @endif
                    @endforeach
                </div>
                <div class="mt-3 pt-3 border-t border-gray-100 flex gap-2">
                    <button wire:click="moveStage('won')"
                        class="flex-1 py-2 text-sm font-semibold bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl transition">
                        Ganada ✓
                    </button>
                    <button wire:click="moveStage('lost')"
                        class="flex-1 py-2 text-sm font-semibold bg-red-100 hover:bg-red-200 text-red-700 rounded-xl transition">
                        Perdida ✕
                    </button>
                </div>
            </div>
            @else
            <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm text-center">
                @if($opportunity->stage === 'won')
                    <div class="text-4xl mb-2">🏆</div>
                    <p class="text-sm font-semibold text-emerald-700">Oportunidad Ganada</p>
                    @if($opportunity->won_at)
                        <p class="text-xs text-gray-400 mt-1">{{ $opportunity->won_at->format('d/m/Y') }}</p>
                    @endif
                @else
                    <p class="text-sm font-semibold text-red-600">Oportunidad Perdida</p>
                    @if($opportunity->lost_reason)
                        <p class="text-xs text-gray-500 mt-1">{{ \App\Models\SalesOpportunity::LOST_REASONS[$opportunity->lost_reason] ?? $opportunity->lost_reason }}</p>
                    @endif
                @endif
                <button wire:click="moveStage('qualification')"
                    class="mt-3 text-xs text-gray-500 hover:text-indigo-600 underline transition">
                    Reabrir oportunidad
                </button>
            </div>
            @endif
        </div>

        {{-- ── Columna derecha: actividades ────────────────────────────────── --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- Agregar actividad --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-700">Actividades</h3>
                    <button wire:click="$toggle('showActivityForm')"
                        class="flex items-center gap-1.5 text-xs text-indigo-600 hover:text-indigo-800 font-medium transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $showActivityForm ? 'M6 18L18 6M6 6l12 12' : 'M12 4v16m8-8H4' }}"/>
                        </svg>
                        {{ $showActivityForm ? 'Cancelar' : 'Registrar actividad' }}
                    </button>
                </div>

                @if($showActivityForm)
                <div class="px-5 py-4 border-b border-gray-100 bg-indigo-50/40 space-y-3">
                    {{-- Tipo --}}
                    <div class="flex flex-wrap gap-1.5">
                        @foreach(\App\Models\CrmActivity::TYPES as $t => $label)
                            <button type="button" wire:click="$set('activityType', '{{ $t }}')"
                                class="px-3 py-1.5 text-xs rounded-lg border transition font-medium cursor-pointer
                                    {{ $activityType === $t
                                        ? 'bg-indigo-600 text-white border-indigo-600'
                                        : 'border-gray-200 bg-white text-gray-600 hover:border-indigo-200 hover:text-indigo-600' }}">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="sm:col-span-2">
                            <input wire:model="activityTitle" type="text" placeholder="Título o resumen de la actividad *"
                                class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                            @error('activityTitle') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Fecha / Hora</label>
                            <input wire:model="activityScheduled" type="datetime-local"
                                class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Asignar a</label>
                            <select wire:model="activityAssignedTo"
                                class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <textarea wire:model="activityDesc" rows="2" placeholder="Notas adicionales..."
                                class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 resize-none"></textarea>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button wire:click="saveActivity"
                            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition">
                            Guardar actividad
                        </button>
                    </div>
                </div>
                @endif

                {{-- Lista de actividades --}}
                <div class="divide-y divide-gray-100">
                    @forelse($opportunity->activities as $act)
                        <div class="px-5 py-4 flex gap-4 {{ $act->status === 'completed' ? 'opacity-60' : '' }}">
                            <div class="shrink-0 mt-0.5">
                                @php
                                    $typeColors = [
                                        'call'     => 'bg-blue-100 text-blue-600',
                                        'email'    => 'bg-indigo-100 text-indigo-600',
                                        'meeting'  => 'bg-purple-100 text-purple-600',
                                        'visit'    => 'bg-teal-100 text-teal-600',
                                        'whatsapp' => 'bg-green-100 text-green-600',
                                        'task'     => 'bg-amber-100 text-amber-600',
                                        'note'     => 'bg-gray-100 text-gray-600',
                                    ];
                                    $typeIcons = [
                                        'call'     => 'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z',
                                        'email'    => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
                                        'meeting'  => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
                                        'visit'    => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
                                        'whatsapp' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
                                        'task'     => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
                                        'note'     => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
                                    ];
                                @endphp
                                <div class="w-8 h-8 rounded-xl flex items-center justify-center {{ $typeColors[$act->type] ?? 'bg-gray-100 text-gray-500' }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $typeIcons[$act->type] ?? '' }}"/>
                                    </svg>
                                </div>
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 {{ $act->status === 'completed' ? 'line-through' : '' }}">
                                            {{ $act->title }}
                                        </p>
                                        @if($act->description)
                                            <p class="text-xs text-gray-500 mt-0.5">{{ $act->description }}</p>
                                        @endif
                                        <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                                            <span class="text-xs text-gray-400">{{ $act->user->name }}</span>
                                            @if($act->assignedTo && $act->assigned_to !== $act->user_id)
                                                <span class="text-xs text-gray-300">→</span>
                                                <span class="text-xs text-gray-400">{{ $act->assignedTo->name }}</span>
                                            @endif
                                            @if($act->scheduled_at)
                                                <span class="text-xs {{ $act->isOverdue() ? 'text-red-500 font-medium' : 'text-gray-400' }}">
                                                    {{ $act->scheduled_at->format('d/m/Y H:i') }}
                                                    @if($act->isOverdue()) · Vencida @endif
                                                </span>
                                            @endif
                                            <span class="text-xs text-gray-400">{{ $act->created_at->diffForHumans() }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1 shrink-0">
                                        @if($act->status !== 'completed')
                                            <button wire:click="completeActivity({{ $act->id }})"
                                                class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-300 hover:text-emerald-600 hover:bg-emerald-50 transition cursor-pointer"
                                                title="Marcar completada">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            </button>
                                        @endif
                                        <button wire:click="deleteActivity({{ $act->id }})"
                                            class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-300 hover:text-red-500 hover:bg-red-50 transition cursor-pointer"
                                            title="Eliminar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-10 text-center text-gray-400">
                            <svg class="w-8 h-8 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <p class="text-sm italic">Sin actividades registradas</p>
                            <button wire:click="$set('showActivityForm', true)"
                                class="mt-2 text-xs text-indigo-600 hover:text-indigo-800 font-medium transition cursor-pointer">
                                + Registrar primera actividad
                            </button>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- ── Modal: Motivo de pérdida ────────────────────────────────────────── --}}
    @if($showLostModal)
        <div class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm">
                <div class="p-5 border-b border-gray-100">
                    <h2 class="text-base font-semibold text-gray-900">Marcar como perdida</h2>
                    <p class="text-sm text-gray-500 mt-0.5">Registra el motivo para análisis futuro</p>
                </div>
                <div class="p-5 space-y-3">
                    <label class="block text-xs text-gray-500 mb-1">Motivo (opcional)</label>
                    <select wire:model="lostReason"
                        class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                        <option value="">— Sin especificar —</option>
                        @foreach(\App\Models\SalesOpportunity::LOST_REASONS as $k => $v)
                            <option value="{{ $k }}">{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="p-5 border-t border-gray-100 flex justify-end gap-2">
                    <button wire:click="$set('showLostModal', false)"
                        class="px-4 py-2 text-sm border border-gray-200 rounded-xl hover:bg-gray-50 transition">
                        Cancelar
                    </button>
                    <button wire:click="confirmLost"
                        class="px-4 py-2 text-sm bg-red-600 hover:bg-red-700 text-white rounded-xl font-medium transition">
                        Confirmar pérdida
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>

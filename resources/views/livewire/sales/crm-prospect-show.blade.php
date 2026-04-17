<div class="max-w-5xl">
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <a wire:navigate href="{{ route('sales.crm.prospects.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-xl font-medium text-gray-900">{{ $prospect->name }}</h1>
                <p class="text-sm text-gray-400">{{ $prospect->contact_name }} · {{ $prospect->contact_phone }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex px-3 py-1 rounded-lg text-xs font-medium border {{ \App\Models\SalesProspect::STATUS_COLORS[$prospect->status] ?? '' }}">
                {{ \App\Models\SalesProspect::STATUSES[$prospect->status] ?? $prospect->status }}
            </span>
            <a wire:navigate href="{{ route('sales.crm.prospects.edit', $prospect) }}"
                class="px-3 py-1.5 text-xs border border-gray-200 rounded-lg hover:bg-gray-50 transition">Editar</a>
            @if(!in_array($prospect->status, ['converted', 'disqualified']))
                <button wire:click="$set('showConvertModal', true)"
                    class="px-3 py-1.5 text-xs bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition">
                    Convertir a cliente
                </button>
            @endif
        </div>
    </div>

    <x-alert />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        {{-- Info lateral --}}
        <div class="space-y-4">
            <div class="bg-white rounded-xl border border-gray-200 p-4 space-y-3">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Datos del prospecto</h3>
                @if($prospect->contact_email)
                    <div><p class="text-xs text-gray-400">Correo</p><p class="text-sm text-gray-800">{{ $prospect->contact_email }}</p></div>
                @endif
                @if($prospect->contact_phone)
                    <div><p class="text-xs text-gray-400">Teléfono</p><p class="text-sm text-gray-800">{{ $prospect->contact_phone }}</p></div>
                @endif
                @if($prospect->city || $prospect->state)
                    <div><p class="text-xs text-gray-400">Ubicación</p><p class="text-sm text-gray-800">{{ collect([$prospect->city, $prospect->state])->filter()->join(', ') }}</p></div>
                @endif
                <div><p class="text-xs text-gray-400">Fuente</p><p class="text-sm text-gray-800">{{ \App\Models\SalesProspect::SOURCES[$prospect->source] ?? '—' }}</p></div>
                <div><p class="text-xs text-gray-400">Valor estimado</p><p class="text-sm font-medium text-gray-900">${{ number_format($prospect->estimated_value, 0) }}</p></div>
                <div><p class="text-xs text-gray-400">Asignado a</p><p class="text-sm text-gray-800">{{ $prospect->assignedTo?->name ?? '—' }}</p></div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-4 space-y-3">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Cambiar estado</h3>
                <div class="flex flex-wrap gap-1.5">
                    @foreach(\App\Models\SalesProspect::STATUSES as $k => $v)
                        @if(!in_array($k, ['converted']))
                            <button wire:click="updateStatus('{{ $k }}')"
                                class="px-2.5 py-1 text-xs rounded-lg border transition {{ $prospect->status === $k ? \App\Models\SalesProspect::STATUS_COLORS[$k] : 'border-gray-200 text-gray-500 hover:bg-gray-50' }}">
                                {{ $v }}
                            </button>
                        @endif
                    @endforeach
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-1">Próximo seguimiento</p>
                    <input type="date" wire:change="updateFollowUp($event.target.value)"
                        value="{{ $prospect->next_follow_up?->format('Y-m-d') }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>
            </div>

            {{-- Oportunidades ligadas --}}
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Oportunidades</h3>
                    <button wire:click="$set('showOpportunityForm', true)" class="text-xs text-indigo-600 hover:underline">+ Nueva</button>
                </div>
                @forelse($prospect->opportunities as $opp)
                    <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                        <div>
                            <p class="text-sm font-medium text-gray-800">{{ $opp->title }}</p>
                            <p class="text-xs text-gray-400">${{ number_format($opp->estimated_value, 0) }}</p>
                        </div>
                        <span class="inline-flex px-2 py-0.5 text-xs rounded-lg border {{ \App\Models\SalesOpportunity::STAGE_COLORS[$opp->stage] ?? '' }}">
                            {{ \App\Models\SalesOpportunity::STAGES[$opp->stage] ?? $opp->stage }}
                        </span>
                    </div>
                @empty
                    <p class="text-xs text-gray-400">Sin oportunidades.</p>
                @endforelse
            </div>
        </div>

        {{-- Actividades --}}
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-medium text-gray-700">Actividades y seguimiento</h3>
                    <button wire:click="$set('showActivityForm', true)"
                        class="inline-flex items-center gap-1.5 text-xs bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded-lg transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Nueva actividad
                    </button>
                </div>

                @if($showActivityForm)
                    <div class="bg-gray-50 rounded-xl p-4 mb-4 space-y-3">
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
                                <label class="block text-xs text-gray-500 mb-1">Fecha programada</label>
                                <input wire:model="activityScheduled" type="datetime-local" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Título *</label>
                            <input wire:model="activityTitle" type="text" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                            @error('activityTitle') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Descripción</label>
                            <textarea wire:model="activityDesc" rows="2" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"></textarea>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Asignado a</label>
                            <select wire:model="activityAssignedTo" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button wire:click="saveActivity" type="button" class="px-3 py-1.5 text-xs bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg">Guardar</button>
                            <button wire:click="$set('showActivityForm', false)" type="button" class="px-3 py-1.5 text-xs border border-gray-200 rounded-lg hover:bg-gray-50">Cancelar</button>
                        </div>
                    </div>
                @endif

                <div class="space-y-3">
                    @forelse($prospect->activities as $act)
                        <div class="flex gap-3 p-3 rounded-xl {{ $act->status === 'completed' ? 'bg-gray-50 opacity-70' : ($act->isOverdue() ? 'bg-amber-50 border border-amber-100' : 'border border-gray-100') }}">
                            <div class="text-xl mt-0.5">{{ \App\Models\CrmActivity::TYPE_ICONS[$act->type] ?? '📋' }}</div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <p class="text-sm font-medium text-gray-900 {{ $act->status === 'completed' ? 'line-through' : '' }}">{{ $act->title }}</p>
                                    <div class="flex items-center gap-1 shrink-0">
                                        @if($act->status === 'pending')
                                            <button wire:click="completeActivity({{ $act->id }})" class="text-xs text-emerald-600 hover:underline">✓ Completar</button>
                                            <button wire:click="deleteActivity({{ $act->id }})" class="text-xs text-red-400 hover:text-red-600 ml-1">✕</button>
                                        @endif
                                    </div>
                                </div>
                                @if($act->description) <p class="text-xs text-gray-500 mt-0.5">{{ $act->description }}</p> @endif
                                <p class="text-xs text-gray-400 mt-1">
                                    {{ $act->scheduled_at?->format('d/m/Y H:i') ?? '—' }} · {{ $act->assignedTo?->name ?? $act->user->name }}
                                    @if($act->isOverdue()) <span class="text-amber-600 font-medium"> · Vencida</span> @endif
                                </p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400 text-center py-4">Sin actividades registradas.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Modal nueva oportunidad --}}
    @if($showOpportunityForm)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-md">
                <h2 class="text-base font-semibold text-gray-900 mb-4">Nueva oportunidad</h2>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Título *</label>
                        <input wire:model="oppTitle" type="text" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        @error('oppTitle') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Etapa</label>
                            <select wire:model="oppStage" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                                @foreach(\App\Models\SalesOpportunity::STAGES as $k => $v)
                                    @if(!in_array($k, ['won', 'lost']))
                                        <option value="{{ $k }}">{{ $v }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Valor estimado</label>
                            <input wire:model="oppValue" type="number" min="0" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Cierre estimado</label>
                        <input wire:model="oppCloseDate" type="date" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    </div>
                </div>
                <div class="flex gap-2 mt-4">
                    <button wire:click="saveOpportunity" type="button" class="flex-1 px-4 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium">Crear oportunidad</button>
                    <button wire:click="$set('showOpportunityForm', false)" type="button" class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50">Cancelar</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal convertir a cliente --}}
    @if($showConvertModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-md">
                <h2 class="text-base font-semibold text-gray-900 mb-2">Convertir a cliente</h2>
                <p class="text-sm text-gray-500 mb-4">Elige cómo manejar la conversión.</p>
                <div class="space-y-3">
                    <label class="flex items-start gap-3 cursor-pointer p-3 rounded-xl border {{ $convertAction === 'new' ? 'border-indigo-300 bg-indigo-50' : 'border-gray-200' }}">
                        <input type="radio" wire:model="convertAction" value="new" class="mt-0.5">
                        <div>
                            <p class="text-sm font-medium text-gray-800">Crear cliente nuevo</p>
                            <p class="text-xs text-gray-400">Se creará un perfil de cliente con los datos de este prospecto.</p>
                        </div>
                    </label>
                    <label class="flex items-start gap-3 cursor-pointer p-3 rounded-xl border {{ $convertAction === 'existing' ? 'border-indigo-300 bg-indigo-50' : 'border-gray-200' }}">
                        <input type="radio" wire:model="convertAction" value="existing" class="mt-0.5">
                        <div>
                            <p class="text-sm font-medium text-gray-800">Vincular a cliente existente</p>
                            <p class="text-xs text-gray-400">El prospecto ya existe como cliente.</p>
                        </div>
                    </label>
                    @if($convertAction === 'existing')
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Seleccionar cliente *</label>
                            <select wire:model="existingCustomerId" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                                <option value="">— Seleccionar —</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                            @error('existingCustomerId') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                    @endif
                </div>
                <div class="flex gap-2 mt-4">
                    <button wire:click="convertToCustomer" type="button" class="flex-1 px-4 py-2 text-sm bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-medium">Convertir</button>
                    <button wire:click="$set('showConvertModal', false)" type="button" class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50">Cancelar</button>
                </div>
            </div>
        </div>
    @endif
</div>

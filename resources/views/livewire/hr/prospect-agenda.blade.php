<div x-data="{ 
    draggingId: null,
    draggingType: null,
    dropDate: null,
    startDrag(id, type = 'prospect') {
        this.draggingId = id;
        this.draggingType = type;
    },
    handleDrop(date) {
        if (this.draggingId) {
            if (this.draggingType === 'interview') {
                @this.call('moveInterviewDate', this.draggingId, date);
            } else if (this.draggingType === 'event') {
                @this.call('moveEventDate', this.draggingId, date);
            } else {
                @this.call('selectProspect', this.draggingId);
                @this.set('interview_date', date);
            }
            this.draggingId = null;
            this.draggingType = null;
        }
    }
}">
    <x-page-header title="Agenda" description="Calendario general de eventos, entrevistas y actividades importantes">
        <x-slot:actions>
            <div class="flex flex-wrap items-center gap-2">
                <button wire:click="openEventModal" type="button" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Nuevo evento
                </button>
                <div class="flex items-center gap-2 bg-white rounded-lg border border-slate-200 p-1">
                    <button wire:click="prevMonth" class="p-1.5 hover:bg-slate-100 rounded-md transition-colors">
                        <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <span class="text-sm font-semibold px-2 text-slate-700 min-w-[120px] text-center">
                        {{ \Carbon\Carbon::parse($currentDate)->translatedFormat('F Y') }}
                    </span>
                    <button wire:click="nextMonth" class="p-1.5 hover:bg-slate-100 rounded-md transition-colors">
                        <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>
        </x-slot:actions>
    </x-page-header>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        {{-- Listado lateral: Sin fecha --}}
        <div class="lg:col-span-1 space-y-4">
            <div class="bg-white rounded-xl border border-slate-200 flex flex-col h-[calc(100vh-200px)] shadow-sm overflow-hidden">
                <div class="p-4 border-b border-indigo-100 bg-gradient-to-r from-indigo-50 via-sky-50 to-white">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-700">Pendientes de agendar</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Arrastra al calendario para citar</p>
                        </div>
                        <div class="h-9 w-9 rounded-lg bg-white/80 border border-indigo-100 flex items-center justify-center text-indigo-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                    </div>
                </div>
                <div class="flex-1 overflow-y-auto p-3 space-y-2 bg-slate-50/40">
                    <p class="px-1 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Entrevistas</p>
                    @forelse($unscheduledProspects as $prospect)
                        <div draggable="true" 
                             @dragstart="startDrag({{ $prospect->id }})"
                             class="p-3 bg-white border border-slate-200 rounded-lg shadow-sm cursor-grab active:cursor-grabbing hover:border-indigo-300 hover:shadow-md hover:-translate-y-0.5 transition-all group">
                            <div class="flex items-center gap-2">
                                <span class="h-2.5 w-2.5 rounded-full shrink-0" style="background-color: {{ $prospect->calendar_color ?: '#7c3aed' }};"></span>
                                <p class="text-sm font-medium text-slate-800 truncate">{{ $prospect->full_name }}</p>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">{{ $prospect->position?->name ?? 'Puesto no especificado' }}</p>
                            <div class="mt-2 flex items-center justify-between">
                                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-medium {{ $prospect->status_color }}">
                                    {{ $prospect->status_label }}
                                </span>
                                <button wire:click="selectProspect({{ $prospect->id }})" class="text-indigo-600 transition-opacity">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                </button>
                            </div>
                        </div>
                    @empty
                        <p class="px-2 py-3 text-xs text-slate-400">No hay entrevistas pendientes.</p>
                    @endforelse

                    <p class="px-1 pt-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Eventos</p>
                    @forelse($unscheduledEvents as $pendingEvent)
                        <div draggable="true"
                             @dragstart="startDrag({{ $pendingEvent->id }}, 'event')"
                             wire:click="selectEvent({{ $pendingEvent->id }})"
                             class="p-3 bg-white border border-slate-200 rounded-lg shadow-sm cursor-grab active:cursor-grabbing hover:border-sky-300 hover:shadow-md hover:-translate-y-0.5 transition-all group">
                            <div class="flex items-center gap-2">
                                <span class="h-2.5 w-2.5 rounded-full shrink-0" style="background-color: {{ $pendingEvent->color }};"></span>
                                <p class="text-sm font-medium text-slate-800 truncate">{{ $pendingEvent->title }}</p>
                            </div>
                            <div class="mt-2 flex items-center justify-between">
                                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-medium bg-sky-50 text-sky-700">
                                    {{ $pendingEvent->type_label }}
                                </span>
                                <span class="text-[10px] text-slate-400">Sin fecha</span>
                            </div>
                        </div>
                    @empty
                        <p class="px-2 py-3 text-xs text-slate-400">No hay eventos pendientes.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Calendario --}}
        <div class="lg:col-span-3">
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
                <div class="px-4 py-3 border-b border-slate-100 bg-gradient-to-r from-slate-50 via-indigo-50/70 to-cyan-50/60 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[10px] font-bold text-indigo-500 uppercase tracking-widest">Calendario</p>
                        <p class="text-sm font-semibold text-slate-700">{{ \Carbon\Carbon::parse($currentDate)->translatedFormat('F Y') }}</p>
                    </div>
                    <div class="hidden sm:flex items-center gap-2 text-[11px] text-slate-500">
                        <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-indigo-500"></span>Virtual</span>
                        <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>Presencial</span>
                    </div>
                </div>
                {{-- Días de la semana --}}
                <div class="grid grid-cols-7 border-b border-slate-100 bg-white">
                    @foreach(['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'] as $dayName)
                        <div class="py-2.5 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">
                            {{ $dayName }}
                        </div>
                    @endforeach
                </div>

                {{-- Rejilla de días --}}
                <div class="grid grid-cols-7 auto-rows-[120px]">
                    @foreach($days as $day)
                        <div 
                            @dragover.prevent=""
                            @drop.prevent="handleDrop('{{ $day['date']->format('Y-m-d') }}')"
                            class="border-r border-b border-slate-100 p-2 transition-all
                                {{ $day['isCurrentMonth'] ? 'bg-white' : 'bg-slate-50/60' }}
                                {{ $day['isToday'] ? 'bg-indigo-50/70 ring-1 ring-inset ring-indigo-300' : '' }}
                                hover:bg-cyan-50/50 hover:ring-1 hover:ring-inset hover:ring-cyan-200"
                        >
                            <div class="flex justify-between items-center mb-1">
                                <span class="inline-flex h-5 min-w-5 items-center justify-center rounded-full px-1.5 text-xs font-bold {{ $day['isToday'] ? 'bg-indigo-600 text-white' : ($day['isCurrentMonth'] ? 'text-slate-700' : 'text-slate-300') }}">
                                    {{ $day['date']->day }}
                                </span>
                                <div class="flex items-center gap-1">
                                    @if($day['isToday'])
                                        <span class="text-[10px] font-bold text-indigo-600 uppercase">Hoy</span>
                                    @elseif(($day['interviews']->count() + $day['events']->count()) > 0)
                                        <span class="text-[10px] font-bold text-slate-400">{{ $day['interviews']->count() + $day['events']->count() }}</span>
                                    @endif
                                    <button type="button" wire:click.stop="openEventModal('{{ $day['date']->format('Y-m-d') }}')"
                                            class="h-5 w-5 rounded-full text-slate-300 hover:bg-white hover:text-indigo-600 hover:shadow-sm transition-colors"
                                            title="Agregar evento">
                                        +
                                    </button>
                                </div>
                            </div>
                            
                            <div class="space-y-1 overflow-y-auto max-h-[85px] sb-nav">
                                @foreach($day['interviews'] as $interview)
                                    @php
                                        $eventColor = $interview->calendar_color ?: ($interview->interview_type === 'virtual' ? '#7c3aed' : '#059669');
                                        $typeColor = $interview->interview_type === 'virtual' ? '#7c3aed' : '#059669';
                                    @endphp
                                    <div draggable="true"
                                        @dragstart.stop="startDrag({{ $interview->id }}, 'interview')"
                                        class="px-1.5 py-1 rounded text-[10px] leading-tight border transition-all cursor-grab active:cursor-grabbing hover:scale-[1.02]"
                                        style="background-color: {{ $eventColor }}14; border-color: {{ $eventColor }}40; color: {{ $eventColor }};"
                                        wire:click="selectProspect({{ $interview->id }})"
                                        title="{{ $interview->full_name }} - {{ $interview->interview_date->format('H:i') }}">
                                        <div class="flex justify-between font-bold">
                                            <span>{{ $interview->interview_date->format('H:i') }}</span>
                                            <span class="h-2 w-2 rounded-full" style="background-color: {{ $typeColor }};"></span>
                                        </div>
                                        <div class="truncate">{{ $interview->first_name }} {{ $interview->last_name }}</div>
                                    </div>
                                @endforeach
                                @foreach($day['events'] as $agendaEvent)
                                    <div draggable="true"
                                        @dragstart.stop="startDrag({{ $agendaEvent->id }}, 'event')"
                                        class="px-1.5 py-1 rounded text-[10px] leading-tight border transition-all cursor-grab active:cursor-grabbing hover:scale-[1.02]"
                                        style="background-color: {{ $agendaEvent->color }}14; border-color: {{ $agendaEvent->color }}40; color: {{ $agendaEvent->color }};"
                                        wire:click="selectEvent({{ $agendaEvent->id }})"
                                        title="{{ $agendaEvent->title }} - {{ $agendaEvent->starts_at->format('H:i') }}">
                                        <div class="flex justify-between font-bold">
                                            <span>{{ $agendaEvent->starts_at->format('H:i') }}</span>
                                            <span class="h-2 w-2 rounded-full" style="background-color: {{ $agendaEvent->color }};"></span>
                                        </div>
                                        <div class="truncate">{{ $agendaEvent->title }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Modal de Agendar --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="$set('showModal', false)"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            @php $selP = \App\Models\HrProspect::find($selectedProspectId); @endphp
                            <h3 class="text-lg leading-6 font-medium text-slate-900" id="modal-title">
                                {{ $selP?->interview_date ? 'Editar Entrevista' : 'Agendar Entrevista' }}
                            </h3>
                            <div class="mt-4 space-y-4">
                                <div class="p-3 bg-slate-50 rounded-lg border border-slate-100">
                                    <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider">Candidato</p>
                                    <p class="text-sm font-medium text-slate-800">{{ $selP?->full_name }}</p>
                                    <p class="text-xs text-slate-500">{{ $selP?->position?->name }}</p>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-medium text-slate-600 mb-1">Fecha</label>
                                        <input wire:model="interview_date" type="date"
                                               class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                                        @error('interview_date') <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-slate-600 mb-1">Hora</label>
                                        <input wire:model="interview_time" type="time"
                                               class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                                        @error('interview_time') <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p> @enderror
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Tipo de entrevista</label>
                                    <select wire:model="interview_type"
                                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                                        <option value="virtual">Virtual (Zoom, Meet, Teams)</option>
                                        <option value="presencial">Presencial (En oficina)</option>
                                    </select>
                                </div>

                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <label class="block text-xs font-medium text-slate-600">Color en calendario</label>
                                        <input wire:model.live="calendar_color" type="color"
                                               class="h-8 w-12 cursor-pointer rounded-md border border-slate-200 bg-white p-1">
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach(['#7c3aed', '#2563eb', '#0891b2', '#059669', '#d97706', '#dc2626', '#db2777', '#475569'] as $color)
                                            <button type="button"
                                                    wire:click="$set('calendar_color', '{{ $color }}')"
                                                    class="h-7 w-7 rounded-full border transition-all {{ strtolower($calendar_color) === strtolower($color) ? 'ring-2 ring-offset-2 ring-slate-500 border-white' : 'border-slate-200 hover:scale-105' }}"
                                                    style="background-color: {{ $color }};"
                                                    aria-label="Seleccionar color {{ $color }}">
                                            </button>
                                        @endforeach
                                    </div>
                                    @error('calendar_color') <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Entrevistador responsable</label>
                                    <select wire:model="interviewer_id"
                                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-slate-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                    <button wire:click="scheduleInterview" type="button" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:w-auto sm:text-sm transition-colors">
                        {{ $selP?->interview_date ? 'Guardar Cambios' : 'Confirmar Cita' }}
                    </button>
                    <button wire:click="$set('showModal', false)" type="button" class="mt-3 w-full inline-flex justify-center rounded-lg border border-slate-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-slate-700 hover:bg-slate-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm transition-colors">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($showEventModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="event-modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="$set('showEventModal', false)"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                    <div class="flex items-start gap-4">
                        <div class="h-10 w-10 rounded-full bg-sky-100 flex items-center justify-center text-sky-600 shrink-0">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <div class="w-full">
                            <h3 class="text-lg leading-6 font-medium text-slate-900" id="event-modal-title">
                                {{ $editingEventId ? 'Editar evento' : 'Nuevo evento' }}
                            </h3>

                            <div class="mt-4 space-y-4">
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Titulo</label>
                                    <input wire:model="event_title" type="text"
                                           class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                                    @error('event_title') <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-medium text-slate-600 mb-1">Fecha opcional</label>
                                        <input wire:model="event_date" type="date"
                                               class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                                        @error('event_date') <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-slate-600 mb-1">Hora opcional</label>
                                        <input wire:model="event_time" type="time"
                                               class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                                        @error('event_time') <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p> @enderror
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Tipo de evento</label>
                                    <div class="flex gap-2">
                                        <select wire:model.live="event_type"
                                                class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                                            @foreach($eventTypes as $typeKey => $typeLabel)
                                                <option value="{{ $typeKey }}">{{ $typeLabel }}</option>
                                            @endforeach
                                            <option value="custom">Otro tipo</option>
                                        </select>
                                        <button type="button"
                                                wire:click="openEventTypeCreator"
                                                class="h-9 w-10 shrink-0 rounded-lg border border-slate-200 bg-white text-indigo-600 hover:bg-indigo-50 hover:border-indigo-200 transition-colors"
                                                title="Agregar categoria">
                                            +
                                        </button>
                                        <button type="button"
                                                wire:click="openEventTypeEditor"
                                                class="h-9 w-10 shrink-0 rounded-lg border border-slate-200 bg-white text-slate-500 hover:bg-indigo-50 hover:text-indigo-600 hover:border-indigo-200 transition-colors"
                                                title="Editar categoria">
                                            <svg class="mx-auto h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </button>
                                        <button type="button"
                                                wire:click="openEventTypeDeleter"
                                                wire:confirm="¿Está seguro de que desea eliminar esta categoría? Los eventos que la usen pasarán a General."
                                                class="h-9 w-10 shrink-0 rounded-lg border border-slate-200 bg-white text-slate-500 hover:bg-red-50 hover:text-red-500 hover:border-red-200 transition-colors"
                                                title="Eliminar categoria">
                                            <svg class="mx-auto h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                    @if($show_event_type_creator)
                                        <div class="mt-2 flex gap-2">
                                            <input wire:model="event_custom_type" type="text" placeholder="Ej. Evaluacion, visita, induccion..."
                                                   class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                                            @if($editing_event_type_id || $editing_event_type_slug)
                                                <button type="button"
                                                        wire:click="saveEventType"
                                                        wire:confirm="¿Está seguro de que desea modificar esta categoría?"
                                                        class="h-9 w-10 shrink-0 rounded-lg border border-emerald-100 bg-emerald-50 text-emerald-600 hover:bg-emerald-100 hover:border-emerald-200 transition-colors"
                                                        title="Actualizar tipo">
                                                    ✓
                                                </button>
                                            @else
                                                <button type="button"
                                                        wire:click="saveEventType"
                                                        class="h-9 w-10 shrink-0 rounded-lg border border-emerald-100 bg-emerald-50 text-emerald-600 hover:bg-emerald-100 hover:border-emerald-200 transition-colors"
                                                        title="Guardar tipo">
                                                    ✓
                                                </button>
                                            @endif
                                        </div>
                                        @error('event_custom_type') <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p> @enderror
                                    @endif
                                    @if($event_type_feedback)
                                        <p class="mt-2 rounded-lg border border-emerald-100 bg-emerald-50 px-3 py-2 text-xs font-medium text-emerald-700">
                                            {{ $event_type_feedback }}
                                        </p>
                                    @endif
                                    @if($show_event_type_editor && $customEventTypes->isNotEmpty())
                                        <div class="mt-3 space-y-1">
                                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Editar categoria</p>
                                            @foreach($customEventTypes as $customType)
                                                <div class="flex items-center justify-between gap-2 rounded-lg border border-slate-100 bg-slate-50/60 px-2 py-1.5">
                                                    <span class="text-xs font-medium text-slate-600 truncate">{{ $customType->name }}</span>
                                                    <button type="button"
                                                            wire:click="editEventType({{ $customType->id }})"
                                                            class="h-7 w-7 rounded-md text-slate-400 hover:bg-white hover:text-indigo-600 transition-colors"
                                                            title="Editar categoria">
                                                        <svg class="mx-auto h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                    </button>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                    @if($show_event_type_deleter && $customEventTypes->isNotEmpty())
                                        <div class="mt-3 space-y-1">
                                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Eliminar categoria</p>
                                            @foreach($customEventTypes as $customType)
                                                <div class="flex items-center justify-between gap-2 rounded-lg border border-slate-100 bg-slate-50/60 px-2 py-1.5">
                                                    <span class="text-xs font-medium text-slate-600 truncate">{{ $customType->name }}</span>
                                                    <button type="button"
                                                            wire:click="deleteEventType({{ $customType->id }})"
                                                            wire:confirm="¿Está seguro de que desea eliminar esta categoría? Los eventos que la usen pasarán a General."
                                                            class="h-7 w-7 rounded-md text-slate-400 hover:bg-red-50 hover:text-red-500 transition-colors"
                                                            title="Eliminar categoria">
                                                        <svg class="mx-auto h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    </button>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <label class="block text-xs font-medium text-slate-600">Color del evento</label>
                                        <input wire:model.live="event_color" type="color"
                                               class="h-8 w-12 cursor-pointer rounded-md border border-slate-200 bg-white p-1">
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach(['#2563eb', '#0891b2', '#059669', '#7c3aed', '#d97706', '#dc2626', '#db2777', '#475569'] as $color)
                                            <button type="button"
                                                    wire:click="$set('event_color', '{{ $color }}')"
                                                    class="h-7 w-7 rounded-full border transition-all {{ strtolower($event_color) === strtolower($color) ? 'ring-2 ring-offset-2 ring-slate-500 border-white' : 'border-slate-200 hover:scale-105' }}"
                                                    style="background-color: {{ $color }};"
                                                    aria-label="Seleccionar color {{ $color }}">
                                            </button>
                                        @endforeach
                                    </div>
                                    @error('event_color') <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Descripcion</label>
                                    <textarea wire:model="event_description" rows="3"
                                              class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30 resize-none"></textarea>
                                    @error('event_description') <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-slate-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse sm:justify-between gap-2">
                    <div class="sm:flex sm:flex-row-reverse gap-2">
                        <button wire:click="saveEvent" type="button" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:w-auto sm:text-sm transition-colors">
                            Guardar evento
                        </button>
                        <button wire:click="$set('showEventModal', false)" type="button" class="mt-3 w-full inline-flex justify-center rounded-lg border border-slate-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-slate-700 hover:bg-slate-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm transition-colors">
                            Cancelar
                        </button>
                    </div>
                    @if($editingEventId)
                        <button wire:click="deleteEvent" type="button" class="mt-3 sm:mt-0 w-full sm:w-auto inline-flex justify-center rounded-lg border border-red-200 px-4 py-2 bg-white text-base font-medium text-red-600 hover:bg-red-50 focus:outline-none sm:text-sm transition-colors">
                            Eliminar
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<div x-data="{ 
    draggingId: null,
    dropDate: null,
    startDrag(id) {
        this.draggingId = id;
    },
    handleDrop(date) {
        if (this.draggingId) {
            @this.call('selectProspect', this.draggingId);
            @this.set('interview_date', date);
        }
    }
}">
    <x-page-header title="Agenda de Reclutamiento" description="Calendario de entrevistas y gestión de citas">
        <x-slot:actions>
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
        </x-slot:actions>
    </x-page-header>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        {{-- Listado lateral: Sin fecha --}}
        <div class="lg:col-span-1 space-y-4">
            <div class="bg-white rounded-xl border border-slate-200 flex flex-col h-[calc(100vh-200px)]">
                <div class="p-4 border-b border-slate-100 bg-slate-50/50 rounded-t-xl">
                    <h3 class="text-sm font-semibold text-slate-700">Pendientes de agendar</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Arrastra al calendario para citar</p>
                </div>
                <div class="flex-1 overflow-y-auto p-3 space-y-2">
                    @forelse($unscheduledProspects as $prospect)
                        <div draggable="true" 
                             @dragstart="startDrag({{ $prospect->id }})"
                             class="p-3 bg-white border border-slate-200 rounded-lg shadow-sm cursor-grab active:cursor-grabbing hover:border-indigo-300 hover:shadow-md transition-all group">
                            <p class="text-sm font-medium text-slate-800">{{ $prospect->full_name }}</p>
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
                        <div class="text-center py-10 px-4">
                            <div class="w-12 h-12 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <p class="text-xs text-slate-400">Todos los prospectos tienen cita agendada.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Calendario --}}
        <div class="lg:col-span-3">
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
                {{-- Días de la semana --}}
                <div class="grid grid-cols-7 border-b border-slate-100 bg-slate-50/50">
                    @foreach(['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'] as $dayName)
                        <div class="py-2 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">
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
                            class="border-r border-b border-slate-100 p-2 transition-colors
                                {{ $day['isCurrentMonth'] ? 'bg-white' : 'bg-slate-50/30' }}
                                {{ $day['isToday'] ? 'ring-1 ring-inset ring-indigo-500' : '' }}
                                hover:bg-slate-50/80"
                        >
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-xs font-medium {{ $day['isCurrentMonth'] ? 'text-slate-700' : 'text-slate-300' }}">
                                    {{ $day['date']->day }}
                                </span>
                                @if($day['isToday'])
                                    <span class="text-[10px] font-bold text-indigo-600 uppercase">Hoy</span>
                                @endif
                            </div>
                            
                            <div class="space-y-1 overflow-y-auto max-h-[85px] sb-nav">
                                @foreach($day['interviews'] as $interview)
                                    <div class="px-1.5 py-1 rounded text-[10px] leading-tight border transition-all cursor-pointer hover:scale-[1.02]
                                        {{ $interview->interview_type === 'virtual' ? 'bg-purple-50 border-purple-100 text-purple-700' : 'bg-emerald-50 border-emerald-100 text-emerald-700' }}"
                                        title="{{ $interview->full_name }} - {{ $interview->interview_date->format('H:i') }}">
                                        <div class="flex justify-between font-bold">
                                            <span>{{ $interview->interview_date->format('H:i') }}</span>
                                            <span>{{ $interview->interview_type === 'virtual' ? '💻' : '🏢' }}</span>
                                        </div>
                                        <div class="truncate">{{ $interview->first_name }} {{ $interview->last_name }}</div>
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
                            <h3 class="text-lg leading-6 font-medium text-slate-900" id="modal-title">
                                Agendar Entrevista
                            </h3>
                            <div class="mt-4 space-y-4">
                                @php $selP = \App\Models\HrProspect::find($selectedProspectId); @endphp
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
                        Confirmar Cita
                    </button>
                    <button wire:click="$set('showModal', false)" type="button" class="mt-3 w-full inline-flex justify-center rounded-lg border border-slate-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-slate-700 hover:bg-slate-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm transition-colors">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

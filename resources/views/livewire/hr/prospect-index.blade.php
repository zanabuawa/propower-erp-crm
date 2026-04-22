<div>
    <x-page-header title="Reclutamiento - Prospectos" description="Gestión de candidatos y procesos de selección">
        <x-slot:actions>
            <div class="flex gap-2">
                <a href="{{ route('hr.prospects.agenda') }}" wire:navigate
                   class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Ver Agenda
                </a>
                @can('create hr')
                <a href="{{ route('hr.prospects.create') }}" wire:navigate
                   class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nuevo prospecto
                </a>
                @endcan
            </div>
        </x-slot:actions>
    </x-page-header>

    {{-- Estadísticas Rápidas --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Prospectos</p>
            <p class="text-2xl font-bold text-slate-800">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm border-l-4 border-l-blue-500">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Nuevos</p>
            <p class="text-2xl font-bold text-slate-800">{{ $stats['new'] }}</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm border-l-4 border-l-yellow-500">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">En Proceso</p>
            <p class="text-2xl font-bold text-slate-800">{{ $stats['interviewing'] }}</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm border-l-4 border-l-green-500">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Contratados</p>
            <p class="text-2xl font-bold text-slate-800">{{ $stats['hired'] }}</p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    {{-- Filtros --}}
    <div class="bg-white rounded-xl border border-slate-200 p-4 mb-5">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-3">
            <div class="xl:col-span-2">
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Búsqueda</label>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Nombre, email..."
                       class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Estado</label>
                <select wire:model.live="filterStatus" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    <option value="">Todos</option>
                    @foreach(\App\Models\HrProspect::STATUSES as $k => $v)
                        <option value="{{ $k }}">{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Vacante</label>
                <select wire:model.live="filterJobOpening" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    <option value="">Todas</option>
                    @foreach($jobOpenings as $jo)
                        <option value="{{ $jo->id }}">{{ $jo->title }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Puesto</label>
                <select wire:model.live="filterPosition" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    <option value="">Todos</option>
                    @foreach($positions as $pos)
                        <option value="{{ $pos->id }}">{{ $pos->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Reclutador</label>
                <select wire:model.live="filterRecruiter" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    <option value="">Todos</option>
                    @foreach($recruiters as $r)
                        <option value="{{ $r->id }}">{{ $r->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Desde (Entrevista)</label>
                <input wire:model.live="filterDateStart" type="date" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
            </div>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50/60">
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Candidato</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide hidden md:table-cell">Puesto / Fuente</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide hidden lg:table-cell">Entrevista / Reclutador</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Estado</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($prospects as $prospect)
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-slate-100 flex items-center justify-center flex-shrink-0">
                                <span class="text-xs font-bold text-slate-600">
                                    {{ strtoupper(substr($prospect->first_name,0,1) . substr($prospect->last_name,0,1)) }}
                                </span>
                            </div>
                            <div>
                                <a href="{{ route('hr.prospects.show', $prospect) }}" wire:navigate class="font-medium text-indigo-600 hover:text-indigo-800">{{ $prospect->full_name }}</a>
                                <p class="text-xs text-slate-400">
                                    {{ $prospect->email ?? '' }}{{ $prospect->phone ? ' · '.$prospect->phone : '' }}
                                </p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 hidden md:table-cell">
                        <p class="text-slate-700 font-medium">{{ $prospect->position?->name ?? '—' }}</p>
                        <p class="text-[11px] text-slate-400">{{ $prospect->source_label ?? '—' }}</p>
                    </td>
                    <td class="px-4 py-3 hidden lg:table-cell">
                        @if($prospect->interview_date)
                            <p class="text-slate-700">{{ $prospect->interview_date->format('d/m/Y H:i') }}</p>
                            <p class="text-[11px] text-indigo-500 font-medium">{{ $prospect->interviewer?->name ?? 'Sin asignar' }}</p>
                        @else
                            <span class="text-slate-400 italic">Pendiente</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-medium {{ $prospect->status_color }}">
                            {{ $prospect->status_label }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right space-x-2">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('hr.prospects.show', $prospect) }}" wire:navigate class="p-1.5 text-slate-400 hover:text-indigo-600" title="Ver detalles">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>

                            @if(in_array($prospect->status, ['entrevista_agendada', 'entrevistado']))
                                <button wire:click="openEvaluation({{ $prospect->id }})" 
                                        class="p-1.5 text-emerald-600 hover:bg-emerald-50 rounded-md transition-colors" title="Evaluar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                                </button>
                            @endif

                            @if($prospect->status === 'en_revision')
                                <button wire:click="approve({{ $prospect->id }})" class="p-1.5 text-indigo-600 hover:bg-indigo-50 rounded-md" title="Aprobar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </button>
                            @endif

                            @if(in_array($prospect->status, ['en_revision', 'aprobado']))
                                <button wire:click="openReject({{ $prospect->id }})" class="p-1.5 text-red-500 hover:bg-red-50 rounded-md" title="Rechazar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            @endif

                            @if($prospect->status === 'aprobado')
                                <button wire:click="hire({{ $prospect->id }})" class="px-2 py-1 bg-indigo-600 text-white text-[10px] font-bold rounded uppercase hover:bg-indigo-700" title="Contratar">
                                    Contratar
                                </button>
                            @endif

                            @if($prospect->status === 'contratado' && $prospect->employee_id)
                                <a href="{{ route('hr.employees.show', $prospect->employee_id) }}" wire:navigate
                                   class="p-1.5 text-slate-400 hover:text-slate-600" title="Ver empleado">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </a>
                            @endif

                            @if($prospect->cv_path)
                                <a href="{{ \Illuminate\Support\Facades\Storage::url($prospect->cv_path) }}" target="_blank"
                                   class="p-1.5 text-slate-400 hover:text-indigo-600" title="Ver CV">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.586-.897L13.828 5.686A1 1 0 0013.103 5.5H7a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </a>
                            @endif

                            @can('edit hr')
                            <a href="{{ route('hr.prospects.edit', $prospect) }}" wire:navigate
                               class="p-1.5 text-slate-400 hover:text-indigo-600" title="Editar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-5 5l.586-.586 3 3L19 11l-3-3z"/></svg>
                            </a>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-10 text-center text-slate-400 text-sm">
                        No se encontraron prospectos con los filtros seleccionados.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($prospects->hasPages())
        <div class="px-4 py-3 border-t border-slate-100">
            {{ $prospects->links() }}
        </div>
        @endif
    </div>

    {{-- MODAL DE EVALUACIÓN --}}
    @if($showEvalModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="$set('showEvalModal', false)"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border border-slate-200">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-slate-900 mb-4 pb-2 border-b border-slate-100">
                        Evaluación del Candidato
                    </h3>
                    
                    @php $p = \App\Models\HrProspect::find($evaluatingId); @endphp
                    <div class="mb-5 p-3 bg-slate-50 rounded-lg">
                        <p class="text-sm font-semibold text-slate-800">{{ $p?->full_name }}</p>
                        <p class="text-xs text-slate-500">{{ $p?->position?->name }}</p>
                    </div>

                    <div class="space-y-4">
                        @foreach(\App\Models\HrProspectEvaluation::CRITERIA as $key => $label)
                        <div class="flex items-center justify-between gap-4">
                            <label class="text-xs font-medium text-slate-600 flex-1">{{ $label }}</label>
                            <div class="flex items-center gap-2">
                                <input type="range" wire:model.live="criteria_scores.{{ $key }}" min="0" max="100" step="5"
                                       class="w-32 h-1.5 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-indigo-600">
                                <span class="text-xs font-bold text-slate-700 min-w-[30px]">{{ $criteria_scores[$key] }}</span>
                            </div>
                        </div>
                        @endforeach

                        <div class="pt-2">
                            <label class="block text-xs font-medium text-slate-600 mb-1">Comentarios y observaciones <span class="text-red-500">*</span></label>
                            <textarea wire:model="eval_comments" rows="3" required
                                      class="w-full px-3 py-2 text-sm border @error('eval_comments') border-red-300 @else border-slate-200 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30 resize-none"></textarea>
                            @error('eval_comments') <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Resultado sugerido <span class="text-red-500">*</span></label>
                            <div class="flex gap-4 mt-2">
                                <label class="flex items-center gap-2 cursor-pointer group">
                                    <input type="radio" wire:model="eval_result" value="aprobado" class="text-indigo-600 focus:ring-indigo-500/30">
                                    <span class="text-sm text-slate-700 group-hover:text-indigo-600 transition-colors">Aprobado / Avanza</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer group">
                                    <input type="radio" wire:model="eval_result" value="rechazado" class="text-indigo-600 focus:ring-indigo-500/30">
                                    <span class="text-sm text-slate-700 group-hover:text-red-600 transition-colors">Rechazado</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-slate-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                    <button wire:click="saveEvaluation" type="button" 
                            class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:w-auto sm:text-sm transition-colors">
                        Guardar Evaluación
                    </button>
                    <button wire:click="$set('showEvalModal', false)" type="button" 
                            class="mt-3 w-full inline-flex justify-center rounded-lg border border-slate-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-slate-700 hover:bg-slate-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm transition-colors">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- MODAL DE RECHAZO --}}
    @if($showRejectModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="$set('showRejectModal', false)"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-slate-900 mb-4">Rechazar Candidato</h3>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Motivo del rechazo <span class="text-red-500">*</span></label>
                        <textarea wire:model="reject_reason" rows="3" required
                                  class="w-full px-3 py-2 text-sm border @error('reject_reason') border-red-300 @else border-slate-200 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500/30 resize-none"
                                  placeholder="Especificar por qué no continúa el proceso..."></textarea>
                        @error('reject_reason') <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="bg-slate-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                    <button wire:click="reject" type="button" 
                            class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none sm:w-auto sm:text-sm transition-colors">
                        Confirmar Rechazo
                    </button>
                    <button wire:click="$set('showRejectModal', false)" type="button" 
                            class="mt-3 w-full inline-flex justify-center rounded-lg border border-slate-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-slate-700 hover:bg-slate-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm transition-colors">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

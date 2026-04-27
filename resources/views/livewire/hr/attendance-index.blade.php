<div class="min-h-screen bg-slate-50/50 -m-4 sm:-m-6 lg:-m-8">
    {{-- ── STICKY HEADER ────────────────────────────────────────────────── --}}
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 max-w-full mx-auto">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-9 h-9 rounded-xl bg-indigo-600 flex items-center justify-center text-white shrink-0 shadow-lg shadow-indigo-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl font-bold text-slate-800 truncate">Control de Asistencias</h1>
                    <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">Gestión de puntualidad y horas laboradas</p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                @can('create hr')
                <button wire:click="registerAllPresent" wire:confirm="¿Registrar asistencia como 'Presente' para todo el personal activo en la fecha seleccionada?"
                    class="hidden md:inline-flex items-center gap-2 px-4 py-2 text-sm font-bold text-indigo-600 hover:text-indigo-700 transition-colors">
                    Marcar todos presentes
                </button>
                <a wire:navigate href="{{ route('hr.attendances.create', ['defaultDate' => $filterDate]) }}"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:scale-[1.02] active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Registrar asistencia</span>
                </a>
                @endcan
            </div>
        </div>
    </div>

    <div class="max-w-full mx-auto p-4 sm:p-6 lg:p-8 space-y-8">
        @if(session('success'))
            <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl animate-in fade-in slide-in-from-top-4 duration-300">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-sm font-semibold">{{ session('success') }}</p>
            </div>
        @endif

        {{-- Summary Cards --}}
        @if($filterDate)
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
                @foreach(\App\Models\HrAttendance::STATUSES as $k => $v)
                    <div class="bg-white p-4 rounded-3xl border border-slate-200/60 shadow-sm">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ $v }}</p>
                        <p class="text-xl font-black text-slate-800">{{ $summary[$k] ?? 0 }}</p>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Filtros --}}
        <div class="bg-white p-4 rounded-3xl border border-slate-200/60 shadow-sm flex flex-wrap gap-4 items-center">
            <div class="flex-1 min-w-[200px]">
                <input wire:model.live="filterDate" type="date"
                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 text-sm font-bold">
            </div>

            <select wire:model.live="filterEmployee"
                class="flex-1 min-w-[200px] px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 text-sm font-bold text-slate-600">
                <option value="">Todos los empleados</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                @endforeach
            </select>

            <select wire:model.live="filterProject"
                class="flex-1 min-w-[200px] px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 text-sm font-bold text-slate-600">
                <option value="">Cualquier proyecto</option>
                @foreach($projects as $proj)
                    <option value="{{ $proj->id }}">{{ $proj->code }} - {{ $proj->name }}</option>
                @endforeach
            </select>
            
            <select wire:model.live="filterStatus"
                class="px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 text-sm font-bold text-slate-600">
                <option value="">Todos los estatus</option>
                @foreach(\App\Models\HrAttendance::STATUSES as $k => $v)
                    <option value="{{ $k }}">{{ $v }}</option>
                @endforeach
            </select>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100">
                            <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Colaborador</th>
                            <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Fecha</th>
                            <th class="px-6 py-4 text-center text-[10px] font-bold text-slate-400 uppercase tracking-widest">Entrada / Salida</th>
                            <th class="px-6 py-4 text-center text-[10px] font-bold text-slate-400 uppercase tracking-widest">Horas</th>
                            <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Proyecto</th>
                            <th class="px-6 py-4 text-center text-[10px] font-bold text-slate-400 uppercase tracking-widest">Estado</th>
                            <th class="px-6 py-4 text-right text-[10px] font-bold text-slate-400 uppercase tracking-widest">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($attendances as $att)
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400 font-bold text-xs">
                                            {{ substr($att->employee->first_name, 0, 1) }}{{ substr($att->employee->last_name, 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-slate-700">{{ $att->employee->full_name }}</p>
                                            <p class="text-[10px] font-medium text-slate-400 uppercase tracking-tight">{{ $att->employee->department?->name }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm font-bold text-slate-600">{{ $att->date->format('d/m/Y') }}</p>
                                    <p class="text-[10px] font-medium text-slate-400 uppercase">{{ $att->date->translatedFormat('l') }}</p>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <span class="text-xs font-black text-emerald-600 font-mono">{{ $att->check_in ? substr($att->check_in, 0, 5) : '--:--' }}</span>
                                        <span class="text-slate-300">|</span>
                                        <span class="text-xs font-black text-indigo-600 font-mono">{{ $att->check_out ? substr($att->check_out, 0, 5) : '--:--' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="text-sm font-black text-slate-700">{{ $att->worked_hours ?? 0 }}h</span>
                                </td>
                                <td class="px-6 py-4">
                                    @if($att->project)
                                        <span class="text-xs font-bold text-indigo-600">{{ $att->project->code }}</span>
                                    @else
                                        <span class="text-xs text-slate-400">General</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-center">
                                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider {{ \App\Models\HrAttendance::STATUS_COLORS[$att->status] }} border border-current opacity-80">
                                            {{ \App\Models\HrAttendance::STATUSES[$att->status] }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @can('edit hr')
                                        <a wire:navigate href="{{ route('hr.attendances.edit', $att) }}" 
                                           class="p-2 rounded-xl text-slate-400 hover:text-indigo-600 hover:bg-white hover:shadow-sm transition-all">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        </a>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto text-slate-300 mb-4">
                                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    <h3 class="text-lg font-bold text-slate-800">No hay asistencias</h3>
                                    <p class="text-sm text-slate-400 max-w-xs mx-auto">No se encontraron registros para los filtros seleccionados.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($attendances->hasPages())
                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/30">
                    {{ $attendances->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

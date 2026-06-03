<div class="p-6">
    <x-page-header title="Sistema de Evaluación" description="Centro de control unificado para el reclutamiento y capacitación técnica">
        <x-slot name="actions">
            <a href="{{ route('hr.test-templates.index') }}" wire:navigate class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 text-slate-700 rounded-xl hover:bg-slate-50 transition-colors font-bold text-xs shadow-sm">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                Banco de Exámenes
            </a>
            <a href="{{ route('hr.evaluations.create_permit') }}" wire:navigate class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition-colors font-bold text-xs shadow-lg shadow-indigo-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                Nueva Evaluación
            </a>
        </x-slot>
    </x-page-header>

    {{-- Estadísticas Rápidas --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm transition-all hover:shadow-md">
            <div class="flex items-center justify-between mb-4">
                <div class="p-2 bg-blue-50 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>
            <p class="text-xs font-black text-slate-400 uppercase tracking-widest">En Curso</p>
            <p class="text-3xl font-black text-slate-800 tracking-tight">{{ $stats['active_processes'] }}</p>
            <p class="mt-4 text-[10px] text-slate-400 font-bold uppercase">Procesos activos ahora</p>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm transition-all hover:shadow-md border-l-4 border-l-amber-500">
            <div class="flex items-center justify-between mb-4">
                <div class="p-2 bg-amber-50 rounded-lg">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </div>
            </div>
            <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Por Calificar</p>
            <p class="text-3xl font-black text-slate-800 tracking-tight">{{ $stats['pending_grades'] }}</p>
            <a href="{{ route('hr.evaluations.pending-grades') }}" wire:navigate class="mt-4 text-[10px] text-amber-600 font-black uppercase tracking-tighter hover:underline flex items-center gap-1">
                Revisión Manual Necesaria
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm transition-all hover:shadow-md border-l-4 border-l-emerald-500">
            <div class="flex items-center justify-between mb-4">
                <div class="p-2 bg-emerald-50 rounded-lg">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Éxitos Hoy</p>
            <p class="text-3xl font-black text-slate-800 tracking-tight">{{ $stats['completed_today'] }}</p>
            <p class="mt-4 text-[10px] text-emerald-600 font-bold uppercase">Evaluaciones finalizadas</p>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm transition-all hover:shadow-md">
            <div class="flex items-center justify-between mb-4">
                <div class="p-2 bg-slate-50 rounded-lg">
                    <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
            </div>
            <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Base de Datos</p>
            <p class="text-3xl font-black text-slate-800 tracking-tight">{{ $stats['total_prospects'] }}</p>
            <p class="mt-4 text-[10px] text-slate-400 font-bold uppercase">Prospectos totales</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-8">
        {{-- Listado Principal de Procesos --}}
        <div class="lg:col-span-8">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-8 py-5 border-b border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <h3 class="font-black text-slate-800 uppercase tracking-widest text-xs">Gestión de Procesos</h3>
                    
                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <div class="relative flex-1 sm:w-64">
                            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar evaluado..."
                                   class="w-full pl-9 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold focus:ring-4 focus:ring-indigo-500/5 transition-all">
                            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <select wire:model.live="filterStatus" class="px-4 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold focus:ring-4 focus:ring-indigo-500/5 transition-all">
                            <option value="">Estados</option>
                            <option value="active">Activos</option>
                            <option value="completed">Completados</option>
                            <option value="failed">Fallidos</option>
                            <option value="canceled">Cancelados</option>
                        </select>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead>
                            <tr class="bg-white border-b border-slate-100">
                                <th class="px-8 py-4 font-black text-slate-400 uppercase tracking-widest text-[10px]">Evaluado</th>
                                <th class="px-8 py-4 font-black text-slate-400 uppercase tracking-widest text-[10px]">Progreso</th>
                                <th class="px-8 py-4 font-black text-slate-400 uppercase tracking-widest text-[10px]">Estado</th>
                                <th class="px-8 py-4 text-right"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($processes as $process)
                            <tr class="hover:bg-slate-50/80 transition-colors group">
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 font-black text-xs">
                                            {{ substr($process->prospect?->first_name ?? $process->employee?->first_name ?? 'E', 0, 1) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-black text-slate-800 truncate leading-none mb-1">{{ $process->prospect?->full_name ?? $process->employee?->full_name }}</p>
                                            <p class="text-[10px] font-bold text-indigo-500 uppercase tracking-tight">{{ $process->prospect?->position?->name ?? $process->employee?->position?->name ?? 'Puesto no asignado' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-3">
                                        <div class="flex-1 min-w-[100px] h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                            @php 
                                                $completedCount = $process->stages->where('status', 'completed')->count();
                                                $total = max(1, $process->total_stages);
                                                $percent = ($completedCount / $total) * 100;
                                            @endphp
                                            <div class="h-full bg-indigo-500 transition-all duration-700" style="width: {{ $percent }}%"></div>
                                        </div>
                                        <span class="text-[10px] font-black text-slate-400">{{ $completedCount }}/{{ $total }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-5">
                                    @php $currentStage = $process->stages->where('order', $process->current_stage_index)->first(); @endphp
                                    <div class="flex flex-col gap-1">
                                        <span class="inline-flex px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-widest
                                            {{ $process->status === 'active' ? 'bg-blue-50 text-blue-600' : ($process->status === 'completed' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600') }}">
                                            {{ $process->status === 'active' ? 'En Curso' : ($process->status === 'completed' ? 'Finalizado' : 'Inactivo') }}
                                        </span>
                                        @if($currentStage && $process->status === 'active')
                                            <p class="text-[9px] font-bold text-slate-400 truncate max-w-[120px]">{{ $currentStage->name }}</p>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <a href="{{ $process->hr_prospect_id ? route('hr.prospects.evaluation', $process->prospect) : route('hr.evaluations.manage', $process) }}" wire:navigate 
                                       class="inline-flex items-center justify-center w-8 h-8 bg-white border border-slate-200 text-slate-400 rounded-xl hover:bg-indigo-600 hover:text-white hover:border-indigo-600 transition-all group/btn shadow-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-8 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-slate-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">No se encontraron procesos</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($processes->hasPages())
                <div class="p-6 border-t border-slate-100 bg-slate-50/30">
                    {{ $processes->links() }}
                </div>
                @endif
            </div>
        </div>

        {{-- Barra Lateral: Actividad Reciente --}}
        <div class="lg:col-span-4 space-y-8">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="font-black text-slate-800 uppercase tracking-widest text-[10px]">Últimos Exámenes</h3>
                </div>
                <div class="divide-y divide-slate-50">
                    @forelse($recentAttempts as $attempt)
                    <div class="px-6 py-4 hover:bg-slate-50 transition-colors">
                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 rounded-full {{ $attempt->score >= 80 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }} flex flex-col items-center justify-center shrink-0 border border-current/10">
                                <span class="text-xs font-black leading-none">{{ (int)$attempt->score }}%</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-[11px] font-black text-slate-800 truncate">
                                    {{ $attempt->prospectTest->stage->evaluationProcess->prospect?->full_name ?? $attempt->prospectTest->stage->evaluationProcess->employee?->full_name }}
                                </p>
                                <p class="text-[9px] font-bold text-slate-400 truncate mb-2">
                                    {{ $attempt->prospectTest->testTemplate->name }}
                                </p>
                                <div class="flex items-center justify-between">
                                    <span class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-500 text-[8px] font-black uppercase tracking-tighter">
                                        Intento #{{ $attempt->attempt_number }}
                                    </span>
                                    <span class="text-[8px] font-bold text-slate-400 uppercase tracking-tighter">{{ $attempt->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="p-8 text-center text-slate-400 text-xs italic">
                        Sin actividad reciente.
                    </div>
                    @endforelse
                </div>
                <div class="p-4 bg-slate-50/50 border-t border-slate-100 text-center">
                    <a href="{{ route('hr.evaluations.pending-grades') }}" wire:navigate class="text-[9px] font-black text-indigo-600 uppercase tracking-widest hover:underline">
                        Ver revisiones pendientes
                    </a>
                </div>
            </div>

            {{-- Guía Rápida --}}
            <div class="bg-indigo-900 rounded-[2rem] p-8 text-white shadow-xl shadow-indigo-200">
                <h3 class="font-black text-xs uppercase tracking-widest mb-6">Centro de Ayuda</h3>
                <ul class="space-y-6">
                    <li class="flex gap-4">
                        <div class="w-6 h-6 rounded-lg bg-indigo-500/30 flex-shrink-0 flex items-center justify-center text-[10px] font-black">1</div>
                        <p class="text-[11px] text-indigo-100 font-medium leading-relaxed">Configura tus plantillas en el <strong>Banco de Exámenes</strong>.</p>
                    </li>
                    <li class="flex gap-4">
                        <div class="w-6 h-6 rounded-lg bg-indigo-500/30 flex-shrink-0 flex items-center justify-center text-[10px] font-black">2</div>
                        <p class="text-[11px] text-indigo-100 font-medium leading-relaxed">Asigna rutas de evaluación a tus candidatos desde su perfil.</p>
                    </li>
                    <li class="flex gap-4">
                        <div class="w-6 h-6 rounded-lg bg-indigo-500/30 flex-shrink-0 flex items-center justify-center text-[10px] font-black">3</div>
                        <p class="text-[11px] text-indigo-100 font-medium leading-relaxed">Supervisa el progreso real y califica exámenes abiertos aquí.</p>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

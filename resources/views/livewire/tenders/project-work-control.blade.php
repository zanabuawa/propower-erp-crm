<div>
    <div class="flex flex-col gap-3 mb-6 lg:flex-row lg:items-center">
        <a wire:navigate href="{{ route('works.projects.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
            </svg>
            Volver a proyectos en obra
        </a>
        <div class="flex-1">
            <h1 class="text-xl font-medium text-gray-900">Control de obra</h1>
            <p class="text-sm text-gray-500">{{ $project->code }} &middot; {{ $project->name }}</p>
        </div>
        <a wire:navigate href="{{ route('works.permits.index', ['project_id' => $project->id]) }}"
           class="inline-flex items-center justify-center px-3 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
            Administrar permisos
        </a>
    </div>

    <x-alert />

    <div class="grid grid-cols-2 gap-3 mb-5 lg:grid-cols-6">
        <div class="p-4 bg-white border border-gray-200 rounded-xl">
            <p class="mb-1 text-xs text-gray-400">Avance por hitos</p>
            <p class="text-lg font-semibold text-gray-900">{{ $milestoneProgress }}%</p>
        </div>
        <div class="p-4 bg-white border border-gray-200 rounded-xl">
            <p class="mb-1 text-xs text-gray-400">Hitos cumplidos</p>
            <p class="text-lg font-semibold text-gray-900">{{ $completedMilestones }}/{{ $milestones->count() }}</p>
        </div>
        <div class="p-4 bg-white border border-gray-200 rounded-xl">
            <p class="mb-1 text-xs text-gray-400">Permisos activos</p>
            <p class="text-lg font-semibold text-gray-900">{{ $activePermitsCount }}</p>
        </div>
        <div class="p-4 bg-white border border-gray-200 rounded-xl">
            <p class="mb-1 text-xs text-gray-400">Reportes semanales</p>
            <p class="text-lg font-semibold text-gray-900">{{ $weeklyReportsCount }}</p>
        </div>
        <div class="p-4 bg-white border border-gray-200 rounded-xl">
            <p class="mb-1 text-xs text-gray-400">Fotograficos</p>
            <p class="text-lg font-semibold text-gray-900">{{ $photoReportsCount }}</p>
        </div>
        <div class="p-4 bg-white border border-gray-200 rounded-xl">
            <p class="mb-1 text-xs text-gray-400">Incidencias</p>
            <p class="text-lg font-semibold text-gray-900">{{ $incidentReportsCount }}</p>
        </div>
    </div>

    <div class="mb-5 overflow-x-auto border-b border-gray-200">
        <nav class="flex min-w-max gap-1">
            @foreach([
                'overview' => 'Avance',
                'permits' => 'Permisos de obra',
                'program' => 'Programa de obra',
                'weekly' => 'Reportes semanales',
                'photos' => 'Reportes fotograficos',
                'logbook' => 'Incidencias de obra',
            ] as $key => $label)
                <button type="button"
                        wire:click="setTab('{{ $key }}')"
                        class="px-4 py-2 text-sm font-medium border-b-2 transition {{ $tab === $key ? 'border-indigo-600 text-indigo-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    {{ $label }}
                </button>
            @endforeach
        </nav>
    </div>

    @if($tab === 'overview')
        <div class="grid gap-5 lg:grid-cols-3">
            <div class="p-5 bg-white border border-gray-200 rounded-xl lg:col-span-2">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-sm font-semibold text-gray-800">Avance por hitos</h2>
                        <p class="text-xs text-gray-400">Se calcula con los hitos marcados como completados.</p>
                    </div>
                    <span class="text-sm font-semibold text-indigo-700">{{ $milestoneProgress }}%</span>
                </div>
                <div class="w-full h-3 mb-5 overflow-hidden bg-gray-100 rounded-full">
                    <div class="h-3 bg-indigo-600 rounded-full" style="width: {{ $milestoneProgress }}%"></div>
                </div>
                <div class="space-y-3">
                    @forelse($milestones as $milestone)
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center w-7 h-7 rounded-full {{ $milestone->status === 'completado' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-400' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-800 truncate">{{ $milestone->name }}</p>
                                <p class="text-xs text-gray-400">{{ $milestone->due_date?->format('d/m/Y') ?? 'Sin fecha' }} &middot; {{ ucfirst($milestone->status) }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="py-8 text-sm text-center text-gray-400">Sin hitos registrados para este proyecto.</p>
                    @endforelse
                </div>
            </div>

            <div class="overflow-hidden bg-white border border-gray-200 rounded-xl">
                <div class="px-5 py-3 border-b border-gray-100">
                    <h2 class="text-sm font-semibold text-gray-800">Trabajadores asignados</h2>
                    <p class="text-xs text-gray-400">Personal activo del proyecto.</p>
                </div>
                <ul class="divide-y divide-gray-100">
                    @forelse($project->employees->where('pivot.is_active', true) as $employee)
                        <li class="px-5 py-3">
                            <p class="text-sm font-medium text-gray-800">{{ $employee->full_name }}</p>
                            <p class="text-xs text-gray-400">{{ $employee->pivot->role ?: 'Sin rol' }}</p>
                        </li>
                    @empty
                        <li class="px-5 py-8 text-sm text-center text-gray-400">Sin trabajadores asignados.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    @elseif($tab === 'permits')
        <div class="p-5 bg-white border border-gray-200 rounded-xl">
            @livewire('tenders.work-permit-index', ['project' => $project, 'embedded' => true], key('work-permits-'.$project->id))
        </div>
    @elseif($tab === 'program')
        <div class="p-5 bg-white border border-gray-200 rounded-xl">
            @livewire('tenders.work-program-index', ['project' => $project, 'embedded' => true], key('work-program-'.$project->id))
        </div>
    @elseif($tab === 'weekly')
        <div class="p-5 bg-white border border-gray-200 rounded-xl">
            @livewire('tenders.work-report-index', ['project' => $project, 'embedded' => true], key('work-reports-'.$project->id))
        </div>
    @elseif($tab === 'photos')
        <div class="p-5 bg-white border border-gray-200 rounded-xl">
            @livewire('tenders.work-photo-report-index', ['project' => $project, 'embedded' => true], key('work-photos-'.$project->id))
        </div>
    @else
        <div class="p-5 bg-white border border-gray-200 rounded-xl">
            @livewire('tenders.work-incident-report-index', ['project' => $project, 'embedded' => true], key('work-incidents-'.$project->id))
        </div>
    @endif
</div>

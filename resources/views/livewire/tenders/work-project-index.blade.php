<div>
    <div class="flex flex-col gap-3 mb-6 lg:flex-row lg:items-center">
        <div class="flex-1">
            <p class="text-xs font-semibold tracking-widest text-indigo-500 uppercase">Licitaciones / Control de obra</p>
            <h1 class="text-xl font-semibold text-gray-900">Proyectos en obra</h1>
            <p class="text-sm text-gray-500">Selecciona un proyecto para administrar permisos, programa, reportes e incidencias desde una sola vista.</p>
        </div>
        <a wire:navigate href="{{ route('projects.create') }}"
           class="inline-flex items-center justify-center px-3 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
            Nuevo proyecto
        </a>
    </div>

    <div class="p-4 mb-5 bg-white border border-gray-200 rounded-xl">
        <div class="grid gap-3 md:grid-cols-3">
            <div class="md:col-span-2">
                <label class="block mb-1 text-xs font-medium text-gray-500">Buscar proyecto</label>
                <input wire:model.live.debounce.300ms="search"
                       type="search"
                       placeholder="Nombre, codigo o cliente..."
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
            </div>
            <div>
                <label class="block mb-1 text-xs font-medium text-gray-500">Estado</label>
                <select wire:model.live="status"
                        class="w-full px-3 py-2 text-sm bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse($projects as $project)
            @php
                $progress = (int) ($project->progress ?? 0);
                $statusClasses = [
                    'activo' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                    'pausado' => 'bg-amber-50 text-amber-700 border-amber-100',
                    'borrador' => 'bg-gray-50 text-gray-700 border-gray-100',
                    'completado' => 'bg-blue-50 text-blue-700 border-blue-100',
                    'cancelado' => 'bg-red-50 text-red-700 border-red-100',
                ][$project->status] ?? 'bg-gray-50 text-gray-700 border-gray-100';
            @endphp
            <a wire:navigate href="{{ route('works.projects.show', $project) }}"
               class="block p-5 transition bg-white border border-gray-200 rounded-xl hover:border-indigo-200 hover:shadow-sm">
                <div class="flex items-start justify-between gap-3 mb-4">
                    <div class="min-w-0">
                        <p class="text-xs font-medium text-gray-400">{{ $project->code }}</p>
                        <h2 class="text-base font-semibold text-gray-900 truncate">{{ $project->name }}</h2>
                        <p class="mt-1 text-sm text-gray-500 truncate">{{ $project->customer?->name ?? 'Sin cliente asignado' }}</p>
                    </div>
                    <span class="px-2 py-1 text-xs font-medium border rounded-full {{ $statusClasses }}">{{ ucfirst($project->status) }}</span>
                </div>

                <div class="mb-4">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs text-gray-400">Avance general</span>
                        <span class="text-xs font-semibold text-gray-700">{{ $progress }}%</span>
                    </div>
                    <div class="w-full h-2 overflow-hidden bg-gray-100 rounded-full">
                        <div class="h-2 bg-indigo-600 rounded-full" style="width: {{ $progress }}%"></div>
                    </div>
                </div>

                <div class="grid grid-cols-5 gap-2 text-center">
                    <div class="p-2 rounded-lg bg-gray-50">
                        <p class="text-sm font-semibold text-gray-900">{{ $project->active_workers_count }}</p>
                        <p class="text-[10px] text-gray-400 uppercase">Personal</p>
                    </div>
                    <div class="p-2 rounded-lg bg-gray-50">
                        <p class="text-sm font-semibold text-gray-900">{{ $project->active_permits_count }}</p>
                        <p class="text-[10px] text-gray-400 uppercase">Permisos</p>
                    </div>
                    <div class="p-2 rounded-lg bg-gray-50">
                        <p class="text-sm font-semibold text-gray-900">{{ $project->work_reports_count }}</p>
                        <p class="text-[10px] text-gray-400 uppercase">Reportes</p>
                    </div>
                    <div class="p-2 rounded-lg bg-gray-50">
                        <p class="text-sm font-semibold text-gray-900">{{ $project->work_photo_reports_count }}</p>
                        <p class="text-[10px] text-gray-400 uppercase">Fotos</p>
                    </div>
                    <div class="p-2 rounded-lg bg-gray-50">
                        <p class="text-sm font-semibold text-gray-900">{{ $project->work_incident_reports_count }}</p>
                        <p class="text-[10px] text-gray-400 uppercase">Incid.</p>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-4 mt-4 border-t border-gray-100">
                    <span class="text-xs text-gray-400">{{ $project->responsible?->name ?? 'Sin responsable' }}</span>
                    <span class="text-sm font-medium text-indigo-600">Abrir control</span>
                </div>
            </a>
        @empty
            <div class="p-10 text-center bg-white border border-gray-200 rounded-xl md:col-span-2 xl:col-span-3">
                <p class="text-sm font-medium text-gray-700">No hay proyectos con los filtros seleccionados.</p>
                <p class="mt-1 text-sm text-gray-400">Cambia el estado o crea un proyecto para comenzar el control de obra.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-5">
        {{ $projects->links() }}
    </div>
</div>

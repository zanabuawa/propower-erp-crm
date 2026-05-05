<div>
    <x-page-header title="Empleados" description="Directorio y expedientes del personal">
        <x-slot:actions>
            <a href="{{ route('hr.employees.print', request()->query()) }}" target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2 bg-white hover:bg-slate-50 text-slate-700 text-sm font-medium rounded-lg border border-slate-200 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Imprimir
            </a>
            @can('create hr')
            <a href="{{ route('hr.employees.create') }}" wire:navigate
               class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nuevo empleado
            </a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    {{-- Filtros --}}
    <div class="bg-white rounded-xl border border-slate-200 p-4 mb-5">
        <div class="flex flex-wrap gap-3">
            <input wire:model.live.debounce.300ms="search"
                   type="text" placeholder="Buscar por nombre, RFC, número..."
                   class="flex-1 min-w-[200px] px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
            <select wire:model.live="filterStatus"
                    class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                <option value="">Todos los estados</option>
                @foreach(\App\Models\HrEmployee::STATUSES as $k => $v)
                    <option value="{{ $k }}">{{ $v }}</option>
                @endforeach
            </select>
            <select wire:model.live="filterDepartment"
                    class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                <option value="">Todos los departamentos</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50/60">
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Empleado</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide hidden md:table-cell">Departamento / Puesto</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide hidden lg:table-cell">Antigüedad</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide hidden sm:table-cell">Contrato</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Estado</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($employees as $employee)
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            @if($employee->photo_url)
                                <img src="{{ $employee->photo_url }}" alt="" class="w-9 h-9 rounded-full object-cover flex-shrink-0">
                            @else
                                <div class="w-9 h-9 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0">
                                    <span class="text-xs font-bold text-indigo-600">
                                        {{ strtoupper(substr($employee->first_name,0,1) . substr($employee->last_name,0,1)) }}
                                    </span>
                                </div>
                            @endif
                            <div>
                                <p class="font-medium text-slate-800">{{ $employee->full_name }}</p>
                                <p class="text-xs text-slate-400">
                                    {{ $employee->employee_number ? '#'.$employee->employee_number.' · ' : '' }}{{ $employee->email ?? '' }}
                                </p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 hidden md:table-cell">
                        <p class="text-slate-700">{{ $employee->department?->name ?? '—' }}</p>
                        <p class="text-xs text-slate-400">{{ $employee->position?->name ?? '—' }}</p>
                    </td>
                    <td class="px-4 py-3 hidden lg:table-cell">
                        <p class="text-slate-700">{{ $employee->hire_date->format('d/m/Y') }}</p>
                        <p class="text-xs text-slate-400">{{ $employee->antiquity_years }} años</p>
                    </td>
                    <td class="px-4 py-3 hidden sm:table-cell text-slate-600">
                        {{ \App\Models\HrEmployee::CONTRACT_TYPES[$employee->contract_type] ?? $employee->contract_type }}
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $employee->status_color }}">
                            {{ $employee->status_label }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('hr.employees.show', $employee) }}" wire:navigate
                           class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Ver</a>
                        @can('edit hr')
                        <a href="{{ route('hr.employees.edit', $employee) }}" wire:navigate
                           class="ml-3 text-xs text-slate-500 hover:text-slate-700">Editar</a>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-10 text-center text-slate-400 text-sm">
                        No se encontraron empleados con los filtros seleccionados.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($employees->hasPages())
        <div class="px-4 py-3 border-t border-slate-100">
            {{ $employees->links('vendor.pagination.tailwind') }}
        </div>
        @endif
    </div>
</div>

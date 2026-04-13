<div>
    <x-page-header title="Presupuestos" description="Control presupuestal por período">
        <x-slot:actions>
            @can('create finance')
            <a wire:navigate href="{{ route('finance.budgets.create') }}"
                class="inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nuevo presupuesto
            </a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <x-alert />

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
        <div class="relative sm:col-span-2">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input wire:model.live.debounce.300ms="search" type="text"
                placeholder="Buscar por nombre..."
                class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
        </div>
        <select wire:model.live="filterPeriodType" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
            <option value="">Todos los períodos</option>
            <option value="mensual">Mensual</option>
            <option value="trimestral">Trimestral</option>
            <option value="semestral">Semestral</option>
            <option value="anual">Anual</option>
        </select>
        <select wire:model.live="filterYear" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
            <option value="">Todos los años</option>
            @foreach($years as $year)
                <option value="{{ $year }}">{{ $year }}</option>
            @endforeach
        </select>
        <select wire:model.live="filterStatus" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
            <option value="">Todos los estados</option>
            <option value="borrador">Borrador</option>
            <option value="aprobado">Aprobado</option>
            <option value="cerrado">Cerrado</option>
        </select>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[720px]">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Nombre</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden md:table-cell">Período</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden md:table-cell">Proyecto</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Planeado</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Real</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Ejecución</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Estado</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($budgets as $budget)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-5 py-3 font-medium text-gray-900">{{ $budget->name }}</td>
                        <td class="px-5 py-3 hidden md:table-cell text-gray-600 capitalize">
                            {{ $budget->period_type }}
                            @if($budget->period_number) #{{ $budget->period_number }}@endif
                            {{ $budget->year }}
                        </td>
                        <td class="px-5 py-3 hidden md:table-cell text-gray-600">{{ $budget->project?->name ?? '—' }}</td>
                        <td class="px-5 py-3 text-right text-gray-700">{{ $budget->currency }} {{ number_format($budget->amount_planned, 2) }}</td>
                        <td class="px-5 py-3 text-right {{ $budget->amount_actual > $budget->amount_planned ? 'text-red-600 font-semibold' : 'text-gray-700' }}">
                            {{ $budget->currency }} {{ number_format($budget->amount_actual, 2) }}
                        </td>
                        <td class="px-5 py-3">
                            @php $pct = $budget->execution_percent_attribute ?? 0; @endphp
                            <div class="flex items-center gap-2">
                                <div class="flex-1 bg-gray-200 rounded-full h-1.5 min-w-[60px]">
                                    <div class="h-1.5 rounded-full {{ $pct > 100 ? 'bg-red-500' : 'bg-indigo-500' }}" style="width: {{ min($pct, 100) }}%"></div>
                                </div>
                                <span class="text-xs text-gray-500 w-10 text-right">{{ $pct }}%</span>
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            @php $sc = ['borrador'=>'gray','aprobado'=>'green','cerrado'=>'blue'][$budget->status] ?? 'gray'; @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $sc }}-100 text-{{ $sc }}-700 capitalize">
                                {{ $budget->status }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            @can('edit finance')
                            <a wire:navigate href="{{ route('finance.budgets.edit', $budget) }}"
                                class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">Editar</a>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-10 text-center text-gray-400 text-sm">No se encontraron presupuestos.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($budgets->hasPages())
        <div class="px-5 py-3 border-t border-gray-100">{{ $budgets->links() }}</div>
        @endif
    </div>
</div>

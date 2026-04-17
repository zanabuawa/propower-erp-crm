<div>
    <x-page-header title="Prospectos" description="Gestión de leads y prospectos de ventas">
        <x-slot:actions>
            <a wire:navigate href="{{ route('sales.crm.prospects.create') }}"
                class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-medium px-5 py-2 rounded-xl transition-all shadow-lg shadow-indigo-500/25">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nuevo prospecto
            </a>
        </x-slot:actions>
    </x-page-header>

    <x-alert />

    {{-- Resumen --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-gray-200 p-4 shadow-sm">
            <p class="text-2xl font-bold text-gray-700">{{ $summary['new'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Nuevos</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4 shadow-sm">
            <p class="text-2xl font-bold text-blue-600">{{ $summary['contacted'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Contactados</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4 shadow-sm">
            <p class="text-2xl font-bold text-emerald-600">{{ $summary['qualified'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Calificados</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4 shadow-sm {{ $summary['overdue'] > 0 ? 'border-amber-200' : '' }}">
            <p class="text-2xl font-bold {{ $summary['overdue'] > 0 ? 'text-amber-600' : 'text-gray-400' }}">{{ $summary['overdue'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Seguimiento vencido</p>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-4 mb-6 shadow-sm">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="relative sm:col-span-2">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar por nombre o correo..."
                    class="w-full pl-9 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <select wire:model.live="filterStatus" class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Todos los estados</option>
                @foreach(\App\Models\SalesProspect::STATUSES as $k => $v)
                    <option value="{{ $k }}">{{ $v }}</option>
                @endforeach
            </select>
            <select wire:model.live="filterUser" class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Todos los vendedores</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[640px]">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="text-left px-5 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Prospecto</th>
                        <th class="text-left px-5 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider hidden sm:table-cell">Fuente</th>
                        <th class="text-left px-5 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider hidden md:table-cell">Valor estimado</th>
                        <th class="text-left px-5 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider hidden md:table-cell">Seguimiento</th>
                        <th class="text-left px-5 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Estado</th>
                        <th class="w-10"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($prospects as $prospect)
                        <tr class="hover:bg-gray-50/70 transition-colors group">
                            <td class="px-5 py-4">
                                <p class="font-medium text-gray-900">{{ $prospect->name }}</p>
                                @if($prospect->contact_name)
                                    <p class="text-xs text-gray-400">{{ $prospect->contact_name }}{{ $prospect->contact_phone ? ' · ' . $prospect->contact_phone : '' }}</p>
                                @endif
                                <p class="text-xs text-gray-400">{{ $prospect->assignedTo?->name ?? '—' }}</p>
                            </td>
                            <td class="px-5 py-4 text-gray-500 text-xs hidden sm:table-cell">
                                {{ \App\Models\SalesProspect::SOURCES[$prospect->source] ?? $prospect->source ?? '—' }}
                            </td>
                            <td class="px-5 py-4 text-gray-700 hidden md:table-cell">
                                {{ $prospect->estimated_value > 0 ? '$' . number_format($prospect->estimated_value, 0) : '—' }}
                            </td>
                            <td class="px-5 py-4 hidden md:table-cell">
                                @if($prospect->next_follow_up)
                                    <span class="{{ $prospect->isOverdue() ? 'text-amber-600 font-medium' : 'text-gray-500' }} text-xs">
                                        {{ $prospect->isOverdue() ? '⚠ ' : '' }}{{ $prospect->next_follow_up->format('d/m/Y') }}
                                    </span>
                                @else
                                    <span class="text-gray-400 text-xs">Sin agendar</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-medium border {{ \App\Models\SalesProspect::STATUS_COLORS[$prospect->status] ?? 'bg-gray-100 text-gray-500 border-gray-200' }}">
                                    {{ \App\Models\SalesProspect::STATUSES[$prospect->status] ?? $prospect->status }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a wire:navigate href="{{ route('sales.crm.prospects.show', $prospect) }}"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-indigo-500 hover:text-white hover:bg-indigo-600 bg-indigo-50 opacity-0 group-hover:opacity-100 transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-12 text-center"><x-empty-state message="No hay prospectos registrados." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($prospects->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">{{ $prospects->links() }}</div>
        @endif
    </div>
</div>

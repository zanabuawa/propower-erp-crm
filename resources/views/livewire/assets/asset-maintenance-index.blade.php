<div>
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <a wire:navigate href="{{ route('assets.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-medium text-gray-900">Mantenimiento de activos</h1>
                @if($overdueCount > 0)
                    <p class="text-xs text-red-500 mt-0.5">{{ $overdueCount }} mantenimiento(s) vencido(s)</p>
                @endif
            </div>
        </div>
        <a wire:navigate href="{{ route('assets.maintenance.create') }}"
            class="px-4 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition">
            + Nuevo mantenimiento
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ session('success') }}</div>
    @endif

    {{-- Filtros --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-5 flex flex-wrap gap-3">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar folio, activo, técnico..."
            class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 min-w-56">

        <select wire:model.live="filterType"
            class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
            <option value="">— Todos los tipos —</option>
            @foreach(\App\Models\AssetMaintenance::TYPES as $k => $v)
                <option value="{{ $k }}">{{ $v }}</option>
            @endforeach
        </select>

        <select wire:model.live="filterStatus"
            class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
            <option value="">— Todos los estados —</option>
            @foreach(\App\Models\AssetMaintenance::STATUSES as $k => $v)
                <option value="{{ $k }}">{{ $v }}</option>
            @endforeach
        </select>

        <select wire:model.live="filterAsset"
            class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 min-w-48">
            <option value="">— Todos los activos —</option>
            @foreach($assets as $asset)
                <option value="{{ $asset->id }}">{{ $asset->folio }} — {{ $asset->name }}</option>
            @endforeach
        </select>
    </div>

    {{-- Tabla --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        @if($maintenances->isEmpty())
            <div class="p-10 text-center text-gray-400 text-sm">No hay mantenimientos registrados.</div>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                        <th class="px-4 py-3 text-left">Folio</th>
                        <th class="px-4 py-3 text-left">Activo</th>
                        <th class="px-4 py-3 text-left">Tipo</th>
                        <th class="px-4 py-3 text-left">Fecha programada</th>
                        <th class="px-4 py-3 text-left">Técnico / Proveedor</th>
                        <th class="px-4 py-3 text-right">Costo</th>
                        <th class="px-4 py-3 text-left">Estado</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($maintenances as $m)
                        @php $overdue = $m->isOverdue(); $color = \App\Models\AssetMaintenance::STATUS_COLORS[$m->status] ?? 'gray'; @endphp
                        <tr class="hover:bg-gray-50 transition {{ $overdue ? 'bg-red-50' : '' }}">
                            <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $m->folio }}</td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-800">{{ $m->asset->name }}</div>
                                <div class="text-xs text-gray-400">{{ $m->asset->folio }}</div>
                            </td>
                            <td class="px-4 py-3 text-gray-700">{{ \App\Models\AssetMaintenance::TYPES[$m->type] ?? $m->type }}</td>
                            <td class="px-4 py-3">
                                <span class="{{ $overdue ? 'text-red-600 font-medium' : 'text-gray-600' }}">
                                    {{ $m->scheduled_date->format('d/m/Y') }}
                                    @if($overdue)<span class="text-xs ml-1">(vencido)</span>@endif
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ $m->technician_label }}
                                @if($m->provider)<span class="text-xs text-gray-400 ml-1">({{ $m->provider }})</span>@endif
                            </td>
                            <td class="px-4 py-3 text-right text-gray-700">
                                {{ $m->cost ? '$'.number_format($m->cost, 2) : '—' }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-700">
                                    {{ \App\Models\AssetMaintenance::STATUSES[$m->status] }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right flex justify-end gap-3">
                                @if(in_array($m->status, ['scheduled', 'in_progress']))
                                    <button wire:click="complete({{ $m->id }})" wire:confirm="¿Marcar mantenimiento como completado?"
                                        class="text-xs text-green-600 hover:text-green-800 font-medium">Completar</button>
                                    <button wire:click="cancel({{ $m->id }})" wire:confirm="¿Cancelar este mantenimiento?"
                                        class="text-xs text-red-500 hover:text-red-700">Cancelar</button>
                                @endif
                                <a wire:navigate href="{{ route('assets.maintenance.edit', $m->id) }}"
                                    class="text-xs text-indigo-600 hover:text-indigo-800">Editar</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-4 py-3 border-t border-gray-100">{{ $maintenances->links() }}</div>
        @endif
    </div>
</div>

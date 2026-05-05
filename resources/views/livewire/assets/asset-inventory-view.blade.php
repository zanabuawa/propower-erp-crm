<div>
    <x-page-header title="Inventario de activos fijos" description="Resumen y ubicación actual de todos los activos">
        <x-slot:actions>
            @can('create assets')
            <a wire:navigate href="{{ route('assets.create') }}"
                class="inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nuevo activo
            </a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <x-alert />

    {{-- Summary cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
        <div class="bg-white border border-gray-200 rounded-xl p-4">
            <p class="text-xs text-gray-500 mb-1">Total de activos</p>
            <p class="text-2xl font-semibold text-gray-900">{{ $totalAssets }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4">
            <p class="text-xs text-gray-500 mb-1">Activos</p>
            <p class="text-2xl font-semibold text-emerald-600">{{ $activeAssets }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4">
            <p class="text-xs text-gray-500 mb-1">En mantenimiento</p>
            <p class="text-2xl font-semibold text-amber-600">{{ $inMaintenance }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4">
            <p class="text-xs text-gray-500 mb-1">Dados de baja</p>
            <p class="text-2xl font-semibold text-gray-500">{{ $retired }}</p>
        </div>
    </div>

    {{-- By branch & by category --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
        {{-- By branch --}}
        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="px-5 py-3.5 border-b border-gray-100">
                <h3 class="text-sm font-medium text-gray-700">Por sucursal</h3>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($byBranch as $row)
                    <div class="px-5 py-3 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $row->branch?->name ?? 'Sin sucursal' }}</p>
                            <p class="text-xs text-gray-400">
                                {{ $row->active_count }} activos · {{ $row->maintenance_count }} en mant.
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold text-gray-900">{{ $row->total }}</p>
                            @if($row->total_cost)
                                <p class="text-xs text-gray-400">$ {{ number_format($row->total_cost, 0) }}</p>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="px-5 py-6 text-sm text-gray-400 text-center">Sin datos.</p>
                @endforelse
            </div>
        </div>

        {{-- By category --}}
        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="px-5 py-3.5 border-b border-gray-100">
                <h3 class="text-sm font-medium text-gray-700">Por categoría</h3>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($byCategory as $row)
                    <div class="px-5 py-3 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-900">
                                {{ \App\Models\FixedAsset::CATEGORIES[$row->category] ?? $row->category ?? 'Sin categoría' }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold text-gray-900">{{ $row->total }}</p>
                            @if($row->total_cost)
                                <p class="text-xs text-gray-400">$ {{ number_format($row->total_cost, 0) }}</p>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="px-5 py-6 text-sm text-gray-400 text-center">Sin datos.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
        <div class="relative sm:col-span-2">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input wire:model.live.debounce.300ms="search" type="text"
                placeholder="Buscar por folio, nombre, serie..."
                aria-label="Buscar activos"
                class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-transparent transition">
        </div>
        <select wire:model.live="filterStatus" aria-label="Filtrar por estado"
            class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
            <option value="">Todos los estados</option>
            @foreach(\App\Models\FixedAsset::STATUSES as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </select>
        <select wire:model.live="filterCategory" aria-label="Filtrar por categoría"
            class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
            <option value="">Todas las categorías</option>
            @foreach(\App\Models\FixedAsset::CATEGORIES as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </select>
        <select wire:model.live="filterBranch" aria-label="Filtrar por sucursal"
            class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
            <option value="">Todas las sucursales</option>
            @foreach($branches as $branch)
                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
            @endforeach
        </select>
    </div>

    {{-- Detailed table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[720px]">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Folio</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Activo</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Sucursal / Área</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden md:table-cell">Asignado a</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden lg:table-cell">Costo adq.</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Estado</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($assets as $asset)
                        @php
                            $statusColors = [
                                'active'         => 'bg-emerald-50 text-emerald-700',
                                'in_maintenance' => 'bg-amber-50 text-amber-700',
                                'transferred'    => 'bg-blue-50 text-blue-700',
                                'retired'        => 'bg-gray-100 text-gray-500',
                            ];
                        @endphp
                        <tr class="hover:bg-gray-50 transition group">
                            <td class="px-5 py-3">
                                <span class="font-mono text-xs text-gray-700">{{ $asset->folio }}</span>
                            </td>
                            <td class="px-5 py-3">
                                <p class="font-medium text-gray-900">{{ $asset->name }}</p>
                                <p class="text-xs text-gray-400">
                                    {{ \App\Models\FixedAsset::CATEGORIES[$asset->category] ?? $asset->category ?? '' }}
                                    @if($asset->brand || $asset->model)
                                        · {{ implode(' ', array_filter([$asset->brand, $asset->model])) }}
                                    @endif
                                </p>
                            </td>
                            <td class="px-5 py-3 text-gray-600">
                                <p>{{ $asset->branch?->name ?? '—' }}</p>
                                @if($asset->warehouse)
                                    <p class="text-xs text-gray-400">{{ $asset->warehouse->name }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-gray-600 hidden md:table-cell">{{ $asset->assignedUser?->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-gray-600 hidden lg:table-cell">
                                {{ $asset->acquisition_cost ? '$ ' . number_format($asset->acquisition_cost, 2) : '—' }}
                            </td>
                            <td class="px-5 py-3">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$asset->status] ?? '' }}">
                                    {{ \App\Models\FixedAsset::STATUSES[$asset->status] ?? $asset->status }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <div class="flex items-center justify-end gap-2 transition">
                                    @can('transfer assets')
                                    <a wire:navigate href="{{ route('assets.transfers.create', ['asset' => $asset->id]) }}"
                                        class="text-xs text-blue-600 hover:text-blue-800 font-medium px-2 py-1 rounded hover:bg-blue-50 transition">Transferir</a>
                                    @endcan
                                    @can('edit assets')
                                    <a wire:navigate href="{{ route('assets.edit', $asset) }}"
                                        class="text-xs text-indigo-600 hover:text-indigo-800 font-medium px-2 py-1 rounded hover:bg-indigo-50 transition">Editar</a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7"><x-empty-state message="No se encontraron activos." /></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($assets->hasPages())
            <div class="px-5 py-3 border-t border-gray-100">{{ $assets->links('vendor.pagination.tailwind') }}</div>
        @endif
    </div>
</div>

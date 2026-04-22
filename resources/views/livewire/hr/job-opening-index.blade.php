<div>
    <x-page-header title="Vacantes" description="Gestión de plazas abiertas para reclutamiento">
        <x-slot:actions>
            @can('create hr')
            <button wire:click="openCreate"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                + Nueva vacante
            </button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <x-alert />

    {{-- KPIs --}}
    @php
        $statuses = \App\Models\HrJobOpening::STATUSES;
        $statusColors = ['open'=>'text-green-700 bg-green-50','paused'=>'text-yellow-700 bg-yellow-50','closed'=>'text-slate-600 bg-slate-100','cancelled'=>'text-red-600 bg-red-50'];
    @endphp
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
        @foreach($statuses as $k => $v)
        <div class="bg-white rounded-xl border border-slate-200 p-4 text-center">
            <p class="text-2xl font-bold {{ explode(' ',$statusColors[$k])[0] }}">{{ $stats[$k] ?? 0 }}</p>
            <p class="text-xs text-slate-500 mt-0.5">{{ $v }}</p>
        </div>
        @endforeach
    </div>

    {{-- Filtros --}}
    <div class="flex flex-wrap gap-3 mb-5">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar vacante..."
               class="flex-1 min-w-[200px] px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
        <select wire:model.live="filterStatus"
                class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
            <option value="">Todos los estados</option>
            @foreach(\App\Models\HrJobOpening::STATUSES as $k => $v)
                <option value="{{ $k }}">{{ $v }}</option>
            @endforeach
        </select>
        <select wire:model.live="filterType"
                class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
            <option value="">Todos los tipos</option>
            @foreach(\App\Models\HrJobOpening::TYPES as $k => $v)
                <option value="{{ $k }}">{{ $v }}</option>
            @endforeach
        </select>
    </div>

    {{-- Grid de vacantes --}}
    @if($openings->isEmpty())
    <div class="bg-white rounded-xl border border-slate-200 p-12 text-center">
        <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
        </div>
        <p class="text-slate-500 mb-3">No hay vacantes registradas.</p>
        @can('create hr')
        <button wire:click="openCreate"
                class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
            Crear primera vacante
        </button>
        @endcan
    </div>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @foreach($openings as $opening)
        @php
            $statusBadge = match($opening->status) {
                'open'      => 'bg-green-100 text-green-700',
                'paused'    => 'bg-yellow-100 text-yellow-700',
                'closed'    => 'bg-slate-100 text-slate-600',
                'cancelled' => 'bg-red-100 text-red-600',
                default     => 'bg-gray-100 text-gray-600',
            };
            $typeBadge = match($opening->type) {
                'internal' => 'bg-blue-100 text-blue-700',
                'external' => 'bg-purple-100 text-purple-700',
                default    => 'bg-indigo-100 text-indigo-700',
            };
        @endphp
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden {{ $opening->status === 'closed' || $opening->status === 'cancelled' ? 'opacity-70' : '' }}">
            <div class="p-4">
                <div class="flex items-start justify-between gap-2 mb-2">
                    <div class="flex-1 min-w-0">
                        <h3 class="font-semibold text-slate-800 truncate">{{ $opening->title }}</h3>
                        <p class="text-xs text-slate-400 mt-0.5">{{ $opening->position?->name }}</p>
                        @if($opening->branch)
                        <p class="text-xs text-slate-400">{{ $opening->branch->name }}</p>
                        @endif
                    </div>
                    <div class="flex flex-col gap-1 items-end flex-shrink-0">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $statusBadge }}">
                            {{ \App\Models\HrJobOpening::STATUSES[$opening->status] }}
                        </span>
                        <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $typeBadge }}">
                            {{ \App\Models\HrJobOpening::TYPES[$opening->type] }}
                        </span>
                    </div>
                </div>

                {{-- Stats --}}
                <div class="grid grid-cols-3 gap-2 text-center text-xs mt-3 mb-3">
                    <div class="bg-slate-50 rounded-lg py-2">
                        <p class="text-lg font-bold text-slate-700">{{ $opening->quantity }}</p>
                        <p class="text-slate-400">Plazas</p>
                    </div>
                    <div class="bg-indigo-50 rounded-lg py-2">
                        <p class="text-lg font-bold text-indigo-600">{{ $opening->prospects_count }}</p>
                        <p class="text-slate-400">Candidatos</p>
                    </div>
                    <div class="bg-slate-50 rounded-lg py-2">
                        <p class="text-sm font-semibold text-slate-700 mt-1">
                            {{ $opening->closing_date ? $opening->closing_date->format('d/m') : '—' }}
                        </p>
                        <p class="text-slate-400">Cierre</p>
                    </div>
                </div>

                @if($opening->salary_range)
                <p class="text-xs text-slate-500 mb-2">
                    <span class="font-medium">Rango salarial:</span> {{ $opening->salary_range }}
                </p>
                @endif

                {{-- Ver candidatos --}}
                <a href="{{ route('hr.prospects.index', ['job_opening_id' => $opening->id]) }}"
                   class="block w-full text-center text-xs text-indigo-600 hover:text-indigo-800 border border-indigo-100 hover:bg-indigo-50 rounded-lg py-1.5 transition">
                    Ver candidatos
                </a>
            </div>

            <div class="border-t border-slate-100 px-4 py-2.5 flex items-center justify-between gap-2 bg-slate-50">
                @if($opening->status === 'open')
                <button wire:click="toggleStatus({{ $opening->id }})"
                        class="text-xs text-yellow-600 hover:text-yellow-800 font-medium">Pausar</button>
                @elseif($opening->status === 'paused')
                <button wire:click="toggleStatus({{ $opening->id }})"
                        class="text-xs text-green-600 hover:text-green-800 font-medium">Reactivar</button>
                @else
                <span></span>
                @endif

                <div class="flex gap-3">
                    @can('edit hr')
                    <button wire:click="openEdit({{ $opening->id }})"
                            class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Editar</button>
                    @if($opening->status !== 'closed')
                    <button wire:click="close({{ $opening->id }})"
                            wire:confirm="¿Cerrar esta vacante?"
                            class="text-xs text-slate-500 hover:text-slate-700 font-medium">Cerrar</button>
                    @endif
                    <button wire:click="delete({{ $opening->id }})"
                            wire:confirm="¿Eliminar esta vacante permanentemente?"
                            class="text-xs text-red-500 hover:text-red-700 font-medium">Eliminar</button>
                    @endcan
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @if($openings->hasPages())
    <div class="mt-4">{{ $openings->links() }}</div>
    @endif
    @endif

    {{-- Modal --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <h3 class="text-base font-bold text-slate-800 mb-5">
                {{ $editingId ? 'Editar vacante' : 'Nueva vacante' }}
            </h3>

            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-xs font-medium text-slate-600 mb-1">Título <span class="text-red-400">*</span></label>
                    <input wire:model="title" type="text"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                           placeholder="Ej. Ingeniero Civil Senior">
                    @error('title') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Puesto <span class="text-red-400">*</span></label>
                    <select wire:model="position_id"
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
                        <option value="">Seleccionar puesto</option>
                        @foreach($positionOptions as $pos)
                        <option value="{{ $pos['id'] }}">{{ $pos['name'] }}</option>
                        @endforeach
                    </select>
                    @error('position_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Sucursal</label>
                    <select wire:model="branch_id"
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
                        <option value="">Sin sucursal específica</option>
                        @foreach($branchOptions as $b)
                        <option value="{{ $b['id'] }}">{{ $b['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Tipo</label>
                    <select wire:model="type"
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
                        @foreach(\App\Models\HrJobOpening::TYPES as $k => $v)
                        <option value="{{ $k }}">{{ $v }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Número de plazas</label>
                    <input wire:model="quantity" type="number" min="1"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>

                <div class="col-span-2">
                    <label class="block text-xs font-medium text-slate-600 mb-1">Rango salarial</label>
                    <input wire:model="salary_range" type="text"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                           placeholder="Ej. $18,000 – $22,000 mensuales">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Fecha publicación</label>
                    <input wire:model="published_at" type="date"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Fecha cierre</label>
                    <input wire:model="closing_date" type="date"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>

                <div class="col-span-2">
                    <label class="block text-xs font-medium text-slate-600 mb-1">Descripción del puesto</label>
                    <textarea wire:model="description" rows="3"
                              class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 resize-none"
                              placeholder="Responsabilidades principales..."></textarea>
                </div>

                <div class="col-span-2">
                    <label class="block text-xs font-medium text-slate-600 mb-1">Requisitos</label>
                    <textarea wire:model="requirements" rows="3"
                              class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 resize-none"
                              placeholder="Experiencia, escolaridad, habilidades..."></textarea>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Estado</label>
                    <select wire:model="status"
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
                        @foreach(\App\Models\HrJobOpening::STATUSES as $k => $v)
                        <option value="{{ $k }}">{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex gap-3 mt-6">
                <button wire:click="save" wire:loading.attr="disabled"
                        class="flex-1 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                    <span wire:loading.remove wire:target="save">Guardar vacante</span>
                    <span wire:loading wire:target="save">Guardando...</span>
                </button>
                <button wire:click="$set('showModal', false)"
                        class="flex-1 text-slate-600 text-sm px-4 py-2 rounded-lg border border-slate-200 hover:bg-slate-50 transition">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
    @endif
</div>

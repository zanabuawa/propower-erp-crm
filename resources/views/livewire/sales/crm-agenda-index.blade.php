{{--
    AGENDA CRM — planificador diario tipo notebook
    ════════════════════════════════════════════════════════════════
    Diseño: Enterprise SaaS + planner. Las actividades se agrupan
    por fecha (groupBy en blade). La estética de cuaderno viene de
    la distribución (hora en margen, barras de color, días agrupados).

    Sidebar izquierdo (escritorio):
      - Búsqueda rápida por título
      - Atajos: hoy pendientes, vencidas, esta semana, completadas hoy
      - Filtros: estado, tipo, usuario, rango de fechas
      - Decoración espiral (visual)

    Tarjetas de actividad:
      - Hora en margen, barra de color por tipo, icono
      - Chips de cliente / prospecto / oportunidad (links)
      - Indicador de recordatorio (campana)
      - Acciones en hover: completar, editar, cancelar

    Modales:
      - Nueva / Editar actividad (activityId == 0 → crear, > 0 → editar)
      - Completar actividad con campo de resultado

    Lógica en CrmAgendaIndex:
      saveActivity()        → crea o actualiza según activityId
      editActivity(id)      → carga actividad en el formulario
      completeActivity()    → marca completada + guarda outcome
      cancelActivity(id)    → cambia status a 'cancelled'
      openOutcomeModal(id)  → abre modal de resultado
      showToday()           → filtra solo el día de hoy
      showAll()             → quita ese filtro
--}}
<div class="-m-4 lg:-m-6 bg-slate-50 flex flex-col min-h-screen">

    {{-- Header propio de la vista --}}
    <div class="bg-white border-b border-gray-200 shadow-sm px-6 py-3 flex items-center justify-between gap-4 shrink-0">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-indigo-100 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-base font-bold text-gray-900 tracking-tight leading-tight">Agenda CRM</h1>
                <p class="text-xs text-gray-500">Actividades · Recordatorios · Seguimientos</p>
            </div>
        </div>
        <button wire:click="$set('showForm', true)"
            class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-all shadow-md shadow-indigo-500/25 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
            </svg>
            Nueva actividad
        </button>
    </div>

    <x-alert />

    {{-- Layout de dos columnas: sidebar + contenido --}}
    <div class="flex flex-1">

        {{-- ══ SIDEBAR ════════════════════════════════════════════════════════ --}}
        <aside class="hidden lg:flex flex-col w-64 shrink-0 bg-white border-r border-gray-200">

            {{-- Búsqueda --}}
            <div class="p-4 border-b border-gray-100">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input wire:model.live.debounce.300ms="search"
                           type="text" placeholder="Buscar actividad…"
                           class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-gray-200 bg-gray-50
                                  focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400
                                  focus:bg-white transition-all">
                </div>
            </div>

            {{-- Stats rápidas --}}
            <div class="p-4 space-y-2">
                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-3">Vista rápida</p>

                {{-- Hoy pendientes (botón filtro) --}}
                <button wire:click="showToday"
                    class="w-full text-left px-3 py-2.5 rounded-xl border transition-all cursor-pointer group
                           {{ $viewMode === 'today'
                               ? 'border-amber-300 bg-amber-50 ring-1 ring-amber-200'
                               : 'border-gray-200 hover:border-amber-200 hover:bg-amber-50/50' }}">
                    <div class="flex items-center justify-between">
                        <p class="text-xl font-black {{ $summary['today_pending'] > 0 ? 'text-amber-500' : 'text-gray-300' }}">
                            {{ $summary['today_pending'] }}
                        </p>
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center
                                    {{ $summary['today_pending'] > 0 ? 'bg-amber-100' : 'bg-gray-100' }}">
                            <svg class="w-3.5 h-3.5 {{ $summary['today_pending'] > 0 ? 'text-amber-500' : 'text-gray-300' }}"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <p class="text-[11px] text-gray-500 font-medium mt-0.5">Hoy pendientes</p>
                </button>

                {{-- Vencidas --}}
                <div class="w-full px-3 py-2.5 rounded-xl border transition-all
                            {{ $summary['overdue'] > 0 ? 'border-red-200 bg-red-50' : 'border-gray-200 bg-gray-50' }}">
                    <div class="flex items-center justify-between">
                        <p class="text-xl font-black {{ $summary['overdue'] > 0 ? 'text-red-500' : 'text-gray-300' }}">
                            {{ $summary['overdue'] }}
                        </p>
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center
                                    {{ $summary['overdue'] > 0 ? 'bg-red-100' : 'bg-gray-100' }}">
                            <svg class="w-3.5 h-3.5 {{ $summary['overdue'] > 0 ? 'text-red-500' : 'text-gray-300' }}"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                    </div>
                    <p class="text-[11px] text-gray-500 font-medium mt-0.5">Vencidas</p>
                </div>

                {{-- Esta semana --}}
                <div class="w-full px-3 py-2.5 rounded-xl border border-gray-200 bg-gray-50">
                    <div class="flex items-center justify-between">
                        <p class="text-xl font-black text-indigo-500">{{ $summary['this_week'] }}</p>
                        <div class="w-7 h-7 rounded-lg bg-indigo-50 flex items-center justify-center">
                            <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>
                    <p class="text-[11px] text-gray-500 font-medium mt-0.5">Esta semana</p>
                </div>

                {{-- Completadas hoy --}}
                <div class="w-full px-3 py-2.5 rounded-xl border transition-all
                            {{ $summary['completed_today'] > 0 ? 'border-emerald-200 bg-emerald-50' : 'border-gray-200 bg-gray-50' }}">
                    <div class="flex items-center justify-between">
                        <p class="text-xl font-black {{ $summary['completed_today'] > 0 ? 'text-emerald-600' : 'text-gray-300' }}">
                            {{ $summary['completed_today'] }}
                        </p>
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center
                                    {{ $summary['completed_today'] > 0 ? 'bg-emerald-100' : 'bg-gray-100' }}">
                            <svg class="w-3.5 h-3.5 {{ $summary['completed_today'] > 0 ? 'text-emerald-500' : 'text-gray-300' }}"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <p class="text-[11px] text-gray-500 font-medium mt-0.5">Completadas hoy</p>
                </div>

                @if($viewMode === 'today')
                <button wire:click="showAll"
                    class="w-full text-left text-xs text-indigo-600 hover:text-indigo-800 font-medium px-1 py-1 cursor-pointer transition-colors">
                    ← Ver todas
                </button>
                @endif
            </div>

            <div class="mx-4 border-t border-dashed border-gray-200"></div>

            {{-- Filtros --}}
            <div class="p-4 space-y-3">
                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Filtros</p>

                <select wire:model.live="filterStatus"
                    class="w-full text-xs px-3 py-2 rounded-xl border border-gray-200 bg-white text-gray-700
                           focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400">
                    <option value="">Todos los estados</option>
                    @foreach(\App\Models\CrmActivity::STATUSES as $k => $v)
                        <option value="{{ $k }}">{{ $v }}</option>
                    @endforeach
                </select>

                <select wire:model.live="filterType"
                    class="w-full text-xs px-3 py-2 rounded-xl border border-gray-200 bg-white text-gray-700
                           focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400">
                    <option value="">Todos los tipos</option>
                    @foreach(\App\Models\CrmActivity::TYPES as $k => $v)
                        <option value="{{ $k }}">{{ $v }}</option>
                    @endforeach
                </select>

                <select wire:model.live="filterUser"
                    class="w-full text-xs px-3 py-2 rounded-xl border border-gray-200 bg-white text-gray-700
                           focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400">
                    <option value="">Todos los usuarios</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                    @endforeach
                </select>

                <div>
                    <label class="text-[10px] text-gray-400 font-semibold uppercase tracking-wide block mb-1">Desde</label>
                    <input wire:model.live="dateFrom" type="date"
                        class="w-full text-xs px-3 py-2 rounded-xl border border-gray-200 bg-white text-gray-700
                               focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400">
                </div>
                <div>
                    <label class="text-[10px] text-gray-400 font-semibold uppercase tracking-wide block mb-1">Hasta</label>
                    <input wire:model.live="dateTo" type="date"
                        class="w-full text-xs px-3 py-2 rounded-xl border border-gray-200 bg-white text-gray-700
                               focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400">
                </div>
            </div>

            {{-- Decoración espiral en la parte inferior --}}
            <div class="mt-auto flex flex-col items-center gap-3 py-6 border-t border-gray-100">
                @foreach(range(1, 6) as $_)
                <div class="w-4 h-4 rounded-full border-2 border-gray-200 bg-gray-50 shadow-sm"></div>
                @endforeach
            </div>
        </aside>

        {{-- ══ CONTENIDO PRINCIPAL ════════════════════════════════════════════ --}}
        <div class="flex-1 min-w-0 px-4 lg:px-8 py-6">

            {{-- Filtros móvil --}}
            <div class="lg:hidden space-y-2 mb-5">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar actividad…"
                        class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-gray-200 bg-white focus:outline-none">
                </div>
                <div class="flex flex-wrap gap-2">
                    <select wire:model.live="filterStatus"
                        class="flex-1 min-w-0 text-xs px-2 py-2 rounded-lg border border-gray-200 bg-white text-gray-700">
                        <option value="">Todos los estados</option>
                        @foreach(\App\Models\CrmActivity::STATUSES as $k => $v)
                            <option value="{{ $k }}">{{ $v }}</option>
                        @endforeach
                    </select>
                    <select wire:model.live="filterType"
                        class="flex-1 min-w-0 text-xs px-2 py-2 rounded-lg border border-gray-200 bg-white text-gray-700">
                        <option value="">Todos los tipos</option>
                        @foreach(\App\Models\CrmActivity::TYPES as $k => $v)
                            <option value="{{ $k }}">{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            @php
                // Paleta y SVG por tipo de actividad
                $typeBar = [
                    'call'     => '#3b82f6',
                    'email'    => '#8b5cf6',
                    'meeting'  => '#10b981',
                    'visit'    => '#f97316',
                    'whatsapp' => '#22c55e',
                    'task'     => '#f59e0b',
                    'note'     => '#94a3b8',
                ];
                $typeIcon = [
                    'call'     => 'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z',
                    'email'    => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
                    'meeting'  => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
                    'visit'    => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z',
                    'whatsapp' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
                    'task'     => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
                    'note'     => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                ];
                $grouped = $activities->groupBy(fn($a) => $a->scheduled_at->format('Y-m-d'));
            @endphp

            @if($activities->isEmpty())
                <div class="max-w-sm mx-auto mt-20 text-center">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-gray-100 flex items-center justify-center">
                        <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <p class="text-gray-600 font-semibold">Sin actividades</p>
                    <p class="text-gray-400 text-sm mt-1">No hay actividades para el período o filtros seleccionados.</p>
                    <button wire:click="$set('showForm', true)"
                        class="mt-4 inline-flex items-center gap-2 text-sm text-indigo-600 hover:text-indigo-800 font-medium cursor-pointer transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Crear primera actividad
                    </button>
                </div>
            @else

            <div class="space-y-8 max-w-3xl">
                @foreach($grouped as $dateKey => $dayActivities)
                @php
                    $date    = \Carbon\Carbon::parse($dateKey);
                    $isToday = $date->isToday();
                    $isPast  = $date->isPast() && !$isToday;
                    $hasOverdue = $dayActivities->contains(fn($a) => $a->isOverdue());
                @endphp

                <div wire:key="day-{{ $dateKey }}">
                    {{-- Cabecera de día --}}
                    <div class="flex items-center gap-3 mb-3">
                        {{-- Cuadro de fecha estilo calendario --}}
                        <div class="shrink-0 w-12 rounded-xl overflow-hidden border shadow-sm
                                    {{ $isToday ? 'border-indigo-400 shadow-indigo-100' : 'border-gray-200' }}">
                            <div class="text-[9px] font-bold uppercase tracking-wider text-center py-0.5
                                        {{ $isToday ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-500' }}">
                                {{ $date->translatedFormat('M') }}
                            </div>
                            <div class="text-center py-1 font-black text-xl leading-none pb-1.5
                                        {{ $isToday ? 'bg-indigo-50 text-indigo-700' : ($isPast ? 'bg-white text-gray-300' : 'bg-white text-gray-800') }}">
                                {{ $date->format('d') }}
                            </div>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="font-semibold text-gray-800 capitalize text-sm">
                                    {{ $date->translatedFormat('l') }}
                                </p>
                                @if($isToday)
                                    <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-indigo-600 text-white">
                                        Hoy
                                    </span>
                                @elseif($isPast)
                                    <span class="text-[10px] font-semibold text-gray-400">Pasado</span>
                                @endif
                                @if($hasOverdue)
                                    <span class="text-[10px] font-semibold text-red-500 flex items-center gap-0.5">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        Vencidas
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-400">{{ $date->translatedFormat('d \d\e F \d\e Y') }}</p>
                        </div>

                        <div class="flex-1 hidden sm:block border-t border-dashed border-gray-200"></div>
                        <span class="text-xs text-gray-400 shrink-0 font-medium">
                            {{ $dayActivities->count() }} {{ $dayActivities->count() === 1 ? 'actividad' : 'actividades' }}
                        </span>
                    </div>

                    {{-- Tarjeta del día --}}
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                        <div class="relative">
                            {{-- Línea roja de margen (estética cuaderno) --}}
                            <div class="absolute left-[4.5rem] top-0 bottom-0 w-px bg-red-200 opacity-50 pointer-events-none"></div>

                            @foreach($dayActivities as $i => $activity)
                            @php
                                $isOverdue   = $activity->isOverdue();
                                $isDone      = $activity->status === 'completed';
                                $isCancelled = $activity->status === 'cancelled';
                                $bar         = $typeBar[$activity->type] ?? '#94a3b8';
                                $icon        = $typeIcon[$activity->type] ?? $typeIcon['note'];
                            @endphp

                            @if($i > 0)
                            <div class="border-t border-gray-100 ml-[4.5rem]"></div>
                            @endif

                            <div wire:key="activity-{{ $activity->id }}"
                                 class="flex items-stretch group {{ $isDone || $isCancelled ? 'opacity-55' : '' }}
                                        hover:bg-indigo-50/30 transition-colors duration-150">

                                {{-- Hora en margen --}}
                                <div class="w-[4.5rem] shrink-0 py-4 px-2 text-right flex flex-col items-end justify-start pt-4">
                                    <span class="text-[11px] font-mono font-bold leading-tight
                                                 {{ $isOverdue ? 'text-red-500' : 'text-gray-400' }}">
                                        {{ $activity->scheduled_at->format('H:i') }}
                                    </span>
                                    @if($activity->reminder_at)
                                    <svg class="w-2.5 h-2.5 text-amber-400 mt-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zm0 16a2 2 0 01-2-2h4a2 2 0 01-2 2z"/>
                                    </svg>
                                    @endif
                                </div>

                                {{-- Barra de color por tipo --}}
                                <div class="w-1 shrink-0 my-3 rounded-full self-stretch"
                                     style="background:{{ $bar }};"></div>

                                {{-- Contenido principal --}}
                                <div class="flex-1 min-w-0 py-3 px-3">

                                    {{-- Fila 1: tipo badge + título --}}
                                    <div class="flex items-start gap-2 mb-1.5">
                                        <div class="w-6 h-6 rounded-md shrink-0 flex items-center justify-center mt-0.5"
                                             style="background:{{ $bar }}18;">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                 stroke="{{ $bar }}" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/>
                                            </svg>
                                        </div>
                                        <p class="text-sm font-semibold text-gray-900 leading-snug
                                                   {{ $isDone || $isCancelled ? 'line-through text-gray-400' : '' }}">
                                            {{ $activity->title }}
                                        </p>
                                    </div>

                                    {{-- Fila 2: chips de entidad + tipo + asignado --}}
                                    <div class="flex flex-wrap items-center gap-1.5 ml-8">
                                        {{-- Chip cliente --}}
                                        @if($activity->customer)
                                        <a wire:navigate href="{{ route('contacts.show', $activity->customer) }}"
                                           class="inline-flex items-center gap-1 text-[11px] font-medium px-2 py-0.5 rounded-md
                                                  bg-blue-50 text-blue-700 border border-blue-100 hover:bg-blue-100 transition-colors cursor-pointer">
                                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                            </svg>
                                            {{ $activity->customer->name }}
                                        </a>
                                        @endif

                                        {{-- Chip oportunidad --}}
                                        @if($activity->opportunity)
                                        <span class="inline-flex items-center gap-1 text-[11px] font-medium px-2 py-0.5 rounded-md
                                                     bg-emerald-50 text-emerald-700 border border-emerald-100">
                                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                            </svg>
                                            {{ $activity->opportunity->title }}
                                        </span>
                                        @endif

                                        {{-- Tipo --}}
                                        <span class="text-[11px] text-gray-400">
                                            {{ \App\Models\CrmActivity::TYPES[$activity->type] ?? $activity->type }}
                                        </span>

                                        {{-- Asignado --}}
                                        @if($activity->assignedTo)
                                        <span class="inline-flex items-center gap-1 text-[11px] text-gray-400">
                                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                            {{ $activity->assignedTo->name }}
                                        </span>
                                        @endif
                                    </div>

                                    {{-- Fila 3: descripción --}}
                                    @if($activity->description)
                                    <p class="text-xs text-gray-400 mt-1.5 ml-8 italic line-clamp-2">
                                        {{ $activity->description }}
                                    </p>
                                    @endif

                                    {{-- Resultado (si completada) --}}
                                    @if($activity->outcome)
                                    <div class="mt-2 ml-8 inline-flex items-start gap-1.5 px-2.5 py-1.5 rounded-lg bg-emerald-50 border border-emerald-100">
                                        <svg class="w-3 h-3 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <p class="text-xs text-emerald-700 font-medium">{{ $activity->outcome }}</p>
                                    </div>
                                    @endif

                                    {{-- Alerta vencida --}}
                                    @if($isOverdue)
                                    <p class="text-[11px] text-red-500 font-semibold mt-1 ml-8 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                        </svg>
                                        Vencida
                                    </p>
                                    @endif
                                </div>

                                {{-- Acciones (aparecen en hover del row) --}}
                                <div class="flex flex-col items-center justify-center gap-1.5 px-3 shrink-0">
                                    {{-- Badge de estado --}}
                                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full border whitespace-nowrap
                                                 {{ \App\Models\CrmActivity::STATUS_COLORS[$activity->status] ?? 'bg-gray-100 text-gray-500 border-gray-200' }}">
                                        {{ \App\Models\CrmActivity::STATUSES[$activity->status] ?? $activity->status }}
                                    </span>

                                    {{-- Botones de acción --}}
                                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity duration-150">
                                        @if($activity->status === 'pending')
                                            {{-- Completar --}}
                                            <button wire:click="openOutcomeModal({{ $activity->id }})"
                                                title="Marcar completada"
                                                class="w-7 h-7 rounded-full flex items-center justify-center bg-emerald-50 hover:bg-emerald-100 text-emerald-600 transition-colors cursor-pointer">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </button>
                                        @endif
                                        {{-- Editar (siempre visible en hover) --}}
                                        <button wire:click="editActivity({{ $activity->id }})"
                                            title="Editar actividad"
                                            class="w-7 h-7 rounded-full flex items-center justify-center bg-indigo-50 hover:bg-indigo-100 text-indigo-500 transition-colors cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.172-8.172z"/>
                                            </svg>
                                        </button>
                                        @if($activity->status === 'pending')
                                            {{-- Cancelar --}}
                                            <button wire:click="cancelActivity({{ $activity->id }})"
                                                title="Cancelar actividad"
                                                class="w-7 h-7 rounded-full flex items-center justify-center bg-red-50 hover:bg-red-100 text-red-500 transition-colors cursor-pointer">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>


    {{-- ══ MODAL: nueva / editar actividad ════════════════════════════════════ --}}
    @if($showForm)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm">
        <div class="w-full max-w-lg bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden">

            <div class="px-6 py-4 bg-gradient-to-r from-indigo-600 to-indigo-700 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        @if($activityId > 0)
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.172-8.172z"/>
                        @else
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        @endif
                    </svg>
                    <h2 class="text-sm font-bold text-white">
                        {{ $activityId > 0 ? 'Editar actividad' : 'Nueva actividad' }}
                    </h2>
                </div>
                <button wire:click="$set('showForm', false)"
                    class="text-white/70 hover:text-white transition-colors cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="px-6 pt-5 pb-5 space-y-4 max-h-[80vh] overflow-y-auto">

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tipo</label>
                        <select wire:model="activityType"
                            class="w-full text-sm px-3 py-2 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400">
                            @foreach(\App\Models\CrmActivity::TYPES as $k => $v)
                                <option value="{{ $k }}">{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Fecha y hora <span class="text-red-500">*</span></label>
                        <input wire:model="activityScheduled" type="datetime-local"
                            class="w-full text-sm px-3 py-2 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 @error('activityScheduled') border-red-400 @enderror">
                        @error('activityScheduled') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Título <span class="text-red-500">*</span></label>
                    <input wire:model="activityTitle" type="text" placeholder="¿Qué se va a hacer?"
                        class="w-full text-sm px-3 py-2 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 @error('activityTitle') border-red-400 @enderror">
                    @error('activityTitle') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Notas</label>
                    <textarea wire:model="activityDesc" rows="2" placeholder="Contexto adicional…"
                        class="w-full text-sm px-3 py-2 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 resize-none"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Recordatorio</label>
                        <input wire:model="activityReminder" type="datetime-local"
                            class="w-full text-sm px-3 py-2 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Asignado a</label>
                        <select wire:model="activityAssigned"
                            class="w-full text-sm px-3 py-2 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400">
                            @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-xl p-3 space-y-3">
                    <label class="block text-xs font-medium text-gray-600">Cliente relacionado</label>
                    <select wire:model="linkedId"
                        class="w-full text-sm px-3 py-2 rounded-xl border border-gray-200 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400">
                        <option value="">— Sin cliente —</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex gap-2 pt-1 border-t border-gray-100">
                    <button wire:click="saveActivity" type="button"
                        class="flex-1 py-2.5 text-sm font-semibold rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700
                               hover:from-indigo-700 hover:to-indigo-800 text-white transition-all shadow-sm shadow-indigo-500/20 cursor-pointer">
                        {{ $activityId > 0 ? 'Guardar cambios' : 'Guardar actividad' }}
                    </button>
                    <button wire:click="$set('showForm', false)" type="button"
                        class="px-4 py-2.5 text-sm font-medium rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors cursor-pointer">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif


    {{-- ══ MODAL: resultado al completar ══════════════════════════════════════ --}}
    @if($showOutcomeModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm">
        <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 bg-emerald-50 border-b border-emerald-100 flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-emerald-800">Completar actividad</h2>
                    <p class="text-xs text-emerald-600">Registra el resultado obtenido (opcional)</p>
                </div>
            </div>
            <div class="p-5 space-y-4">
                <textarea wire:model="outcome" rows="3"
                    placeholder="Ej: Cliente interesado, agendaremos demo la próxima semana…"
                    class="w-full text-sm px-3 py-2.5 rounded-xl border border-gray-200 resize-none
                           focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-400"></textarea>
                <div class="flex gap-2">
                    <button wire:click="completeActivity" type="button"
                        class="flex-1 py-2.5 text-sm font-semibold rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white transition-colors cursor-pointer">
                        Marcar completada
                    </button>
                    <button wire:click="$set('showOutcomeModal', false)" type="button"
                        class="px-4 py-2.5 text-sm font-medium rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors cursor-pointer">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>

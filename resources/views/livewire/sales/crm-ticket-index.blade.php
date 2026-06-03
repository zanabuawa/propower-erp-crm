{{--
    LISTA DE TICKETS  (CrmTicketIndex)
    ════════════════════════════════════════════════════════════════
    Muestra todos los tickets de soporte de la empresa en una tabla
    paginada (25 por página). Los tickets se ordenan por prioridad
    (urgente → alta → media → baja) y luego por estado activo primero.

    ┌─ SECCIÓN: TARJETAS DE RESUMEN ─────────────────────────────┐
    │  Cuatro contadores en tiempo real:                          │
    │  • Abiertos      — recién creados, sin respuesta            │
    │  • En progreso   — ya tienen respuesta o están en atención  │
    │  • En espera     — esperando respuesta del cliente          │
    │  • Vencidos      — superaron su fecha límite (due_at)       │
    └─────────────────────────────────────────────────────────────┘

    ┌─ SECCIÓN: FILTROS ──────────────────────────────────────────┐
    │  Todos reactivos (wire:model.live), filtran sin recargar:   │
    │  • Búsqueda de texto  — por folio (TKT-00001) o asunto      │
    │  • Estado             — abierto / en progreso / etc.        │
    │  • Prioridad          — baja / media / alta / urgente       │
    │  • Tipo               — soporte / garantía / queja / etc.   │
    │  • Agente asignado    — filtra por usuario responsable      │
    └─────────────────────────────────────────────────────────────┘

    ┌─ SECCIÓN: TABLA ────────────────────────────────────────────┐
    │  Cada fila es clickeable y lleva al detalle del ticket.     │
    │  Columna "Msgs" muestra cuántos mensajes tiene el ticket.   │
    │  Si el ticket está vencido, el asunto muestra "Vencido"     │
    │  en rojo y la fecha límite se resalta en rojo.              │
    └─────────────────────────────────────────────────────────────┘

    Componente: App\Livewire\Sales\CrmTicketIndex
    Modelo:     App\Models\CrmTicket
--}}
<div>
    <x-page-header title="Tickets de soporte" description="Gestión de solicitudes, garantías y quejas">
        <x-slot:actions>
            <a wire:navigate href="{{ route('sales.crm.tickets.create') }}"
                class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-medium px-5 py-2 rounded-xl transition-all shadow-lg shadow-indigo-500/25">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nuevo ticket
            </a>
        </x-slot:actions>
    </x-page-header>

    <x-alert />

    {{-- ── Tarjetas de resumen ──────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-gray-200 p-4 shadow-sm">
            <p class="text-2xl font-bold text-blue-600">{{ $stats['open'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Abiertos</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4 shadow-sm">
            <p class="text-2xl font-bold text-indigo-600">{{ $stats['in_progress'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5">En progreso</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4 shadow-sm">
            <p class="text-2xl font-bold text-amber-600">{{ $stats['waiting'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5">En espera</p>
        </div>
        {{-- Vencidos: tickets abiertos/en progreso/en espera cuya fecha límite ya pasó --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-4 shadow-sm">
            <p class="text-2xl font-bold text-red-600">{{ $stats['overdue'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Vencidos</p>
        </div>
    </div>

    {{-- ── Filtros reactivos ────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4 mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
            <div class="lg:col-span-2">
                {{-- Busca en folio (TKT-00001) y en el asunto del ticket --}}
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar por folio o asunto…"
                    class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 outline-none">
            </div>
            <select wire:model.live="status" class="border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 outline-none">
                <option value="">Todos los estados</option>
                @foreach(\App\Models\CrmTicket::STATUSES as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
            <select wire:model.live="priority" class="border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 outline-none">
                <option value="">Todas las prioridades</option>
                @foreach(\App\Models\CrmTicket::PRIORITIES as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
            {{-- Tipo: clasifica la naturaleza del ticket --}}
            <select wire:model.live="type" class="border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 outline-none">
                <option value="">Todos los tipos</option>
                @foreach(\App\Models\CrmTicket::TYPES as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
            <select wire:model.live="assignedTo" class="border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 outline-none">
                <option value="">Todos los agentes</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- ── Tabla de tickets ─────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        @if($tickets->isEmpty())
            <div class="text-center py-16 text-gray-400">
                <svg class="w-10 h-10 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p class="text-sm font-medium">Sin tickets</p>
                <p class="text-xs mt-1">Ajusta los filtros o crea un nuevo ticket.</p>
            </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/60">
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wide">Folio</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wide">Asunto</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wide">Cliente</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wide">Tipo</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wide">Prioridad</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wide">Estado</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wide">Asignado a</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wide">Vence</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wide w-8">Msgs</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($tickets as $ticket)
                    {{-- Cada fila lleva al detalle del ticket (crm-ticket-show) --}}
                    <tr wire:navigate href="{{ route('sales.crm.tickets.show', $ticket) }}"
                        class="hover:bg-indigo-50/40 cursor-pointer transition-colors"
                        onclick="window.location='{{ route('sales.crm.tickets.show', $ticket) }}'">
                        <td class="px-4 py-3 font-mono text-xs text-gray-500 whitespace-nowrap">
                            {{ $ticket->folio }}
                        </td>
                        <td class="px-4 py-3 max-w-xs">
                            <p class="font-medium text-gray-900 truncate">{{ $ticket->subject }}</p>
                            {{-- Aviso visual si ya pasó la fecha límite y el ticket sigue abierto --}}
                            @if($ticket->isOverdue())
                                <span class="text-xs text-red-500 font-medium">Vencido</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                            {{ $ticket->customer?->name ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            {{-- Colores definidos en CrmTicket::TYPE_COLORS --}}
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium border {{ \App\Models\CrmTicket::TYPE_COLORS[$ticket->type] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ \App\Models\CrmTicket::TYPES[$ticket->type] ?? $ticket->type }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            {{-- Colores definidos en CrmTicket::PRIORITY_COLORS --}}
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium {{ \App\Models\CrmTicket::PRIORITY_COLORS[$ticket->priority] ?? 'bg-gray-100 text-gray-500' }}">
                                {{ \App\Models\CrmTicket::PRIORITIES[$ticket->priority] ?? $ticket->priority }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            {{-- Colores definidos en CrmTicket::STATUS_COLORS --}}
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium border {{ \App\Models\CrmTicket::STATUS_COLORS[$ticket->status] ?? 'bg-gray-100 text-gray-500' }}">
                                {{ \App\Models\CrmTicket::STATUSES[$ticket->status] ?? $ticket->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-600 text-xs whitespace-nowrap">
                            {{ $ticket->assignedTo?->name ?? '—' }}
                        </td>
                        {{-- Fecha límite en rojo si está vencida --}}
                        <td class="px-4 py-3 text-xs whitespace-nowrap {{ $ticket->isOverdue() ? 'text-red-600 font-semibold' : 'text-gray-500' }}">
                            {{ $ticket->due_at ? $ticket->due_at->format('d/m/Y') : '—' }}
                        </td>
                        {{-- Total de mensajes (cargado con withCount en el componente) --}}
                        <td class="px-4 py-3 text-center text-xs text-gray-400">
                            {{ $ticket->messages_count }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $tickets->links() }}
        </div>
        @endif
    </div>
</div>

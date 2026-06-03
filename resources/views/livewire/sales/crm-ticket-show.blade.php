{{--
    DETALLE DE TICKET  (CrmTicketShow)
    ════════════════════════════════════════════════════════════════
    Vista principal para trabajar un ticket. Tiene dos columnas:

    COLUMNA IZQUIERDA (2/3 del ancho):
    ┌─ Tarjeta de información ───────────────────────────────────┐
    │  Badges de estado / tipo / prioridad y descripción.        │
    │  Metadatos: cliente, creador, fecha límite, fecha creación.│
    │  Si tiene orden de venta o factura ligada, muestra links.  │
    │  Si está resuelto/cerrado muestra las fechas de esos hitos.│
    └─────────────────────────────────────────────────────────────┘
    ┌─ Conversación (hilo de mensajes) ──────────────────────────┐
    │  Todos los mensajes del ticket ordenados por fecha.        │
    │  Mensajes con fondo ámbar = notas internas (solo agentes). │
    │  El avatar muestra la inicial del nombre del usuario.      │
    │                                                            │
    │  Caja de respuesta (solo si el ticket está abierto):       │
    │  • Textarea para escribir la respuesta                     │
    │  • Checkbox "Nota interna" — si se marca, el mensaje       │
    │    queda en fondo ámbar y no debería enviarse al cliente    │
    │  • Al enviar: si el ticket estaba en "abierto", cambia     │
    │    automáticamente a "en progreso" (lógica en sendMessage) │
    └─────────────────────────────────────────────────────────────┘

    COLUMNA DERECHA (1/3 del ancho):
    ┌─ Panel de estado ───────────────────────────────────────────┐
    │  Select + botón para cambiar el estado del ticket.         │
    │  Al pasar a "resuelto" guarda resolved_at automáticamente. │
    │  Al pasar a "cerrado" guarda closed_at.                    │
    │  Al reabrir (open/in_progress/waiting) limpia esas fechas. │
    └─────────────────────────────────────────────────────────────┘
    ┌─ Panel de asignación ───────────────────────────────────────┐
    │  Select para reasignar el ticket a otro agente.            │
    └─────────────────────────────────────────────────────────────┘
    ┌─ Línea de tiempo ───────────────────────────────────────────┐
    │  Muestra hitos: creación, resolución y cierre con fecha.   │
    └─────────────────────────────────────────────────────────────┘

    Componente: App\Livewire\Sales\CrmTicketShow
    Modelo:     App\Models\CrmTicket + CrmTicketMessage
--}}
<div>
    <x-page-header :title="$ticket->folio . ' — ' . $ticket->subject" description="">
        <x-slot:actions>
            <a wire:navigate href="{{ route('sales.crm.tickets.index') }}"
                class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Volver
            </a>
            @can('create sales')
            <a wire:navigate href="{{ route('sales.crm.tickets.edit', $ticket) }}"
                class="inline-flex items-center gap-2 text-sm bg-white border border-gray-200 rounded-xl px-4 py-2 text-gray-700 hover:bg-gray-50 transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.172-8.172z"/></svg>
                Editar
            </a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <x-alert />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ══ COLUMNA IZQUIERDA: info + conversación ════════════════════════ --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- ── Tarjeta de información general del ticket ─────────────── --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                {{-- Badges: estado, tipo, prioridad y alerta de vencido --}}
                <div class="flex flex-wrap gap-2 mb-4">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium border {{ \App\Models\CrmTicket::STATUS_COLORS[$ticket->status] ?? 'bg-gray-100 text-gray-500' }}">
                        {{ \App\Models\CrmTicket::STATUSES[$ticket->status] ?? $ticket->status }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium border {{ \App\Models\CrmTicket::TYPE_COLORS[$ticket->type] ?? 'bg-gray-100 text-gray-600' }}">
                        {{ \App\Models\CrmTicket::TYPES[$ticket->type] ?? $ticket->type }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium {{ \App\Models\CrmTicket::PRIORITY_COLORS[$ticket->priority] ?? 'bg-gray-100 text-gray-500' }}">
                        {{ \App\Models\CrmTicket::PRIORITIES[$ticket->priority] ?? $ticket->priority }}
                    </span>
                    @if($ticket->isOverdue())
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-red-100 text-red-700">Vencido</span>
                    @endif
                </div>

                {{-- Descripción larga del ticket --}}
                @if($ticket->description)
                    <p class="text-sm text-gray-700 whitespace-pre-line">{{ $ticket->description }}</p>
                @endif

                {{-- Metadatos en cuadrícula: cliente, creador, fechas, referencias --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-5 pt-4 border-t border-gray-100 text-xs text-gray-500">
                    <div>
                        <p class="font-semibold text-gray-700 text-xs uppercase tracking-wide mb-0.5">Cliente</p>
                        @if($ticket->customer)
                            <p class="text-gray-800">{{ $ticket->customer->name }}</p>
                        @else
                            <p>—</p>
                        @endif
                    </div>
                    <div>
                        <p class="font-semibold text-gray-700 text-xs uppercase tracking-wide mb-0.5">Creado por</p>
                        <p class="text-gray-800">{{ $ticket->createdBy?->name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-700 text-xs uppercase tracking-wide mb-0.5">Fecha límite</p>
                        <p class="{{ $ticket->isOverdue() ? 'text-red-600 font-semibold' : 'text-gray-800' }}">
                            {{ $ticket->due_at ? $ticket->due_at->format('d/m/Y') : '—' }}
                        </p>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-700 text-xs uppercase tracking-wide mb-0.5">Creado</p>
                        <p class="text-gray-800">{{ $ticket->created_at->format('d/m/Y') }}</p>
                    </div>
                    {{-- Orden y factura solo se muestran si el ticket tiene esas referencias --}}
                    @if($ticket->saleOrder)
                    <div>
                        <p class="font-semibold text-gray-700 text-xs uppercase tracking-wide mb-0.5">Orden</p>
                        <a wire:navigate href="{{ route('sales.orders.show', $ticket->saleOrder) }}" class="text-indigo-600 hover:underline">{{ $ticket->saleOrder->folio }}</a>
                    </div>
                    @endif
                    @if($ticket->saleInvoice)
                    <div>
                        <p class="font-semibold text-gray-700 text-xs uppercase tracking-wide mb-0.5">Factura</p>
                        <a wire:navigate href="{{ route('sales.invoices.show', $ticket->saleInvoice) }}" class="text-indigo-600 hover:underline">{{ $ticket->saleInvoice->folio }}</a>
                    </div>
                    @endif
                    @if($ticket->resolved_at)
                    <div>
                        <p class="font-semibold text-gray-700 text-xs uppercase tracking-wide mb-0.5">Resuelto</p>
                        <p class="text-gray-800">{{ $ticket->resolved_at->format('d/m/Y H:i') }}</p>
                    </div>
                    @endif
                    @if($ticket->closed_at)
                    <div>
                        <p class="font-semibold text-gray-700 text-xs uppercase tracking-wide mb-0.5">Cerrado</p>
                        <p class="text-gray-800">{{ $ticket->closed_at->format('d/m/Y H:i') }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- ── Hilo de conversación ──────────────────────────────────── --}}
            {{-- Tabla crm_ticket_messages. Cada mensaje tiene: usuario, body, is_internal --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/60">
                    <h3 class="text-sm font-semibold text-gray-700">Conversación</h3>
                </div>

                @if($ticket->messages->isEmpty())
                    <div class="text-center py-10 text-gray-400 text-sm">Sin mensajes aún.</div>
                @else
                <div class="divide-y divide-gray-100">
                    @foreach($ticket->messages as $msg)
                    {{-- Fondo ámbar = nota interna (solo visible para el equipo) --}}
                    <div class="px-6 py-4 {{ $msg->is_internal ? 'bg-amber-50/50' : '' }}">
                        <div class="flex items-start gap-3">
                            {{-- Avatar con inicial del nombre --}}
                            <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold flex items-center justify-center shrink-0">
                                {{ strtoupper(substr($msg->user?->name ?? '?', 0, 1)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap mb-1">
                                    <span class="text-sm font-semibold text-gray-800">{{ $msg->user?->name ?? 'Desconocido' }}</span>
                                    <span class="text-xs text-gray-400">{{ $msg->created_at->format('d/m/Y H:i') }}</span>
                                    @if($msg->is_internal)
                                        <span class="text-xs bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded font-medium">Nota interna</span>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-700 whitespace-pre-line">{{ $msg->body }}</p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif

                {{-- ── Caja de respuesta (solo si el ticket está activo) ─── --}}
                {{-- isOpen() = status en open | in_progress | waiting        --}}
                @if($ticket->isOpen())
                <div class="px-6 py-5 border-t border-gray-100 space-y-3">
                    <textarea wire:model="messageBody" rows="4" placeholder="Escribe una respuesta…"
                        class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 outline-none resize-none @error('messageBody') border-red-400 @enderror"></textarea>
                    @error('messageBody') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                    <div class="flex items-center justify-between gap-3 flex-wrap">
                        {{-- Si se marca, el mensaje queda guardado como nota interna --}}
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input wire:model="isInternal" type="checkbox" class="rounded border-gray-300 text-amber-500 focus:ring-amber-500">
                            <span class="text-xs text-gray-600">Nota interna (no visible para el cliente)</span>
                        </label>
                        {{-- sendMessage() crea el mensaje y si el ticket era "open" lo pasa a "in_progress" --}}
                        <button wire:click="sendMessage" type="button"
                            class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-5 py-2 rounded-xl transition-colors cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                            Enviar
                        </button>
                    </div>
                </div>
                @else
                {{-- Si el ticket está cerrado, no se puede responder --}}
                <div class="px-6 py-4 border-t border-gray-100 text-center text-sm text-gray-400">
                    El ticket está cerrado. Reabre el ticket para continuar la conversación.
                </div>
                @endif
            </div>
        </div>

        {{-- ══ COLUMNA DERECHA: acciones rápidas ════════════════════════════ --}}
        <div class="space-y-6">

            {{-- ── Panel de estado ──────────────────────────────────────── --}}
            {{-- updateStatus() guarda resolved_at / closed_at según el estado elegido --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 space-y-3">
                <h3 class="text-sm font-semibold text-gray-700">Estado</h3>
                <select wire:model="newStatus" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 outline-none">
                    @foreach(\App\Models\CrmTicket::STATUSES as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                <button wire:click="updateStatus" type="button"
                    class="w-full bg-gray-800 hover:bg-gray-900 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors cursor-pointer">
                    Actualizar estado
                </button>
            </div>

            {{-- ── Panel de asignación ──────────────────────────────────── --}}
            {{-- updateAssigned() actualiza assigned_to sin recargar la página --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 space-y-3">
                <h3 class="text-sm font-semibold text-gray-700">Asignado a</h3>
                <select wire:model="newAssignedTo" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 outline-none">
                    <option value="">Sin asignar</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                    @endforeach
                </select>
                <button wire:click="updateAssigned" type="button"
                    class="w-full bg-gray-800 hover:bg-gray-900 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors cursor-pointer">
                    Reasignar
                </button>
            </div>

            {{-- ── Línea de tiempo ──────────────────────────────────────── --}}
            {{-- Muestra los hitos clave: cuándo se creó, resolvió y cerró  --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Línea de tiempo</h3>
                <ol class="relative border-l border-gray-200 ml-2 space-y-4">
                    <li class="ml-4">
                        <div class="absolute -left-1.5 mt-1 w-3 h-3 rounded-full bg-blue-400 border-2 border-white"></div>
                        <p class="text-xs text-gray-500">Creado por <span class="font-medium text-gray-700">{{ $ticket->createdBy?->name ?? '—' }}</span></p>
                        <p class="text-xs text-gray-400">{{ $ticket->created_at->format('d/m/Y H:i') }}</p>
                    </li>
                    @if($ticket->resolved_at)
                    <li class="ml-4">
                        <div class="absolute -left-1.5 mt-1 w-3 h-3 rounded-full bg-emerald-400 border-2 border-white"></div>
                        <p class="text-xs text-gray-500">Resuelto</p>
                        <p class="text-xs text-gray-400">{{ $ticket->resolved_at->format('d/m/Y H:i') }}</p>
                    </li>
                    @endif
                    @if($ticket->closed_at)
                    <li class="ml-4">
                        <div class="absolute -left-1.5 mt-1 w-3 h-3 rounded-full bg-gray-400 border-2 border-white"></div>
                        <p class="text-xs text-gray-500">Cerrado</p>
                        <p class="text-xs text-gray-400">{{ $ticket->closed_at->format('d/m/Y H:i') }}</p>
                    </li>
                    @endif
                </ol>
            </div>
        </div>

    </div>
</div>

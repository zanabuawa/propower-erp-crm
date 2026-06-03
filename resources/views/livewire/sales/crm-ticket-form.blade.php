{{--
    FORMULARIO DE TICKET  (CrmTicketForm)
    ════════════════════════════════════════════════════════════════
    Sirve tanto para CREAR un ticket nuevo como para EDITARLO.
    Se sabe en qué modo está por la propiedad $ticket->exists.

    Al crear: genera folio automático TKT-00001, TKT-00002, etc.
    Al editar: pre-llena todos los campos con los valores actuales.

    COLUMNA PRINCIPAL (2/3):
    ┌─ Información del ticket ───────────────────────────────────┐
    │  • Asunto    — título corto del problema (requerido)       │
    │  • Tipo      — Soporte / Garantía / Queja / Consulta /     │
    │                Devolución                                   │
    │  • Prioridad — Baja / Media / Alta / Urgente               │
    │  • Descripción — detalle completo del problema             │
    └─────────────────────────────────────────────────────────────┘
    ┌─ Referencias (opcionales) ──────────────────────────────────┐
    │  • Cliente       — si se selecciona, aparecen los selects  │
    │                    de Orden de venta y Factura filtrados    │
    │                    solo para ese cliente                    │
    │  • Orden / Factura — el ticket queda ligado a un documento │
    │    de venta específico, útil para garantías y devoluciones  │
    │    (updatedCustomerId() limpia estos campos si el cliente   │
    │     cambia)                                                 │
    └─────────────────────────────────────────────────────────────┘

    COLUMNA LATERAL (1/3):
    ┌─ Asignación ────────────────────────────────────────────────┐
    │  • Asignado a  — usuario responsable del ticket            │
    │  • Fecha límite — cuándo debe resolverse (due_at)          │
    └─────────────────────────────────────────────────────────────┘

    Componente: App\Livewire\Sales\CrmTicketForm
    Rutas:
      GET /ventas/crm/tickets/crear          → nuevo
      GET /ventas/crm/tickets/{id}/editar    → editar
--}}
<div>
    <x-page-header
        :title="$ticket?->exists ? 'Editar ticket ' . $ticket->folio : 'Nuevo ticket'"
        description="{{ $ticket?->exists ? 'Modifica los datos del ticket' : 'Registra una nueva solicitud de soporte' }}">
        <x-slot:actions>
            <a wire:navigate href="{{ route('sales.crm.tickets.index') }}"
                class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Volver
            </a>
        </x-slot:actions>
    </x-page-header>

    <x-alert />

    <form wire:submit="save" class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Main column --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Basic info --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-4">
                <h3 class="text-sm font-semibold text-gray-700">Información del ticket</h3>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Asunto <span class="text-red-500">*</span></label>
                    <input wire:model="subject" type="text" placeholder="Describe brevemente el problema…"
                        class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 outline-none @error('subject') border-red-400 @enderror">
                    @error('subject') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tipo <span class="text-red-500">*</span></label>
                        <select wire:model="type" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 outline-none">
                            @foreach(\App\Models\CrmTicket::TYPES as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('type') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Prioridad <span class="text-red-500">*</span></label>
                        <select wire:model="priority" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 outline-none">
                            @foreach(\App\Models\CrmTicket::PRIORITIES as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('priority') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Descripción</label>
                    <textarea wire:model="description" rows="5" placeholder="Detalla el problema, pasos para reproducirlo, impacto…"
                        class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 outline-none resize-none"></textarea>
                    @error('description') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- References --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-4">
                <h3 class="text-sm font-semibold text-gray-700">Referencias (opcional)</h3>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Cliente</label>
                    <select wire:model.live="customer_id" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 outline-none">
                        <option value="">Sin cliente</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                @if($customer_id)
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Orden de venta</label>
                        <select wire:model="sale_order_id" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 outline-none">
                            <option value="">Ninguna</option>
                            @foreach($orders as $o)
                                <option value="{{ $o->id }}">{{ $o->folio }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Factura</label>
                        <select wire:model="sale_invoice_id" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 outline-none">
                            <option value="">Ninguna</option>
                            @foreach($invoices as $i)
                                <option value="{{ $i->id }}">{{ $i->folio }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Side column --}}
        <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-4">
                <h3 class="text-sm font-semibold text-gray-700">Asignación</h3>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Asignado a</label>
                    <select wire:model="assigned_to" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 outline-none">
                        <option value="">Sin asignar</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Fecha límite</label>
                    <input wire:model="due_at" type="date"
                        class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 outline-none @error('due_at') border-red-400 @enderror">
                    @error('due_at') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <button type="submit"
                class="w-full bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-semibold px-6 py-3 rounded-xl transition-all shadow-lg shadow-indigo-500/25 cursor-pointer">
                {{ $ticket?->exists ? 'Guardar cambios' : 'Crear ticket' }}
            </button>
        </div>

    </form>
</div>

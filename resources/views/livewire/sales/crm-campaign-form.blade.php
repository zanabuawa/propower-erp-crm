{{--
    FORMULARIO DE CAMPAÑA (crear / editar)
    ─────────────────────────────────────────────────────────────────
    Una "campaña" es una acción de marketing o comunicación dirigida
    a un segmento de clientes o prospectos con un objetivo concreto
    (generar leads, recuperar clientes inactivos, anunciar un producto, etc.)

    Campos principales:
      • Nombre               — título interno de la campaña
      • Canal                — medio de comunicación utilizado:
                               Email, WhatsApp, SMS, Redes sociales,
                               Evento presencial, Llamada, Otro
      • Estado               — Borrador → Activa → Pausada → Completada / Cancelada
      • Descripción          — objetivo de la campaña
      • Audiencia objetivo   — descripción del segmento al que va dirigida

    Fechas y presupuesto:
      • Fecha de inicio / fin — vigencia de la campaña
      • Presupuesto ($)       — inversión autorizada

    Las métricas (leads generados, conversiones, gasto real, ingresos
    atribuidos y ROI) se registran DESPUÉS de crear la campaña,
    desde la vista de detalle → panel "Actualizar métricas".

    Componente Livewire: App\Livewire\Sales\CrmCampaignForm
    Rutas:
      GET /ventas/crm/campanas/crear          → crear nueva
      GET /ventas/crm/campanas/{id}/editar    → editar existente
--}}
<div>
    <x-page-header
        :title="$campaign?->exists ? 'Editar ' . $campaign->folio : 'Nueva campaña'"
        description="{{ $campaign?->exists ? 'Modifica los datos de la campaña' : 'Crea una nueva campaña de marketing' }}">
        <x-slot:actions>
            <a wire:navigate href="{{ route('sales.crm.campaigns.index') }}"
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

            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-4">
                <h3 class="text-sm font-semibold text-gray-700">Información general</h3>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nombre <span class="text-red-500">*</span></label>
                    <input wire:model="name" type="text" placeholder="Ej. Campaña verano 2026 — email"
                        class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 outline-none @error('name') border-red-400 @enderror">
                    @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Canal <span class="text-red-500">*</span></label>
                        <select wire:model="type" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 outline-none">
                            @foreach(\App\Models\CrmCampaign::TYPES as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Estado <span class="text-red-500">*</span></label>
                        <select wire:model="status" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 outline-none">
                            @foreach(\App\Models\CrmCampaign::STATUSES as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Descripción / objetivo</label>
                    <textarea wire:model="description" rows="3" placeholder="Describe el objetivo de la campaña…"
                        class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 outline-none resize-none"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Audiencia objetivo</label>
                    <textarea wire:model="target_audience" rows="3" placeholder="Describe el segmento de clientes al que va dirigida…"
                        class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 outline-none resize-none"></textarea>
                </div>
            </div>

        </div>

        {{-- Side column --}}
        <div class="space-y-6">

            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-4">
                <h3 class="text-sm font-semibold text-gray-700">Fechas y presupuesto</h3>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Fecha de inicio</label>
                    <input wire:model="start_at" type="date"
                        class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 outline-none @error('start_at') border-red-400 @enderror">
                    @error('start_at') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Fecha de fin</label>
                    <input wire:model="end_at" type="date"
                        class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 outline-none @error('end_at') border-red-400 @enderror">
                    @error('end_at') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Presupuesto ($)</label>
                    <input wire:model="budget" type="number" step="0.01" min="0" placeholder="0.00"
                        class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 outline-none @error('budget') border-red-400 @enderror">
                    @error('budget') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <button type="submit"
                class="w-full bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-semibold px-6 py-3 rounded-xl transition-all shadow-lg shadow-indigo-500/25 cursor-pointer">
                {{ $campaign?->exists ? 'Guardar cambios' : 'Crear campaña' }}
            </button>
        </div>

    </form>
</div>

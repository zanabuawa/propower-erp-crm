{{--
    DETALLE DE CAMPAÑA  (CrmCampaignShow)
    ════════════════════════════════════════════════════════════════
    Vista de seguimiento de una campaña individual. Dos columnas:

    COLUMNA IZQUIERDA (2/3):
    ┌─ Tarjeta de información ───────────────────────────────────┐
    │  Badges de estado y canal. Descripción y audiencia objetivo│
    │  (cuadro azul). Metadatos: creador, fecha, inicio, fin.    │
    │  Si la fecha de fin ya pasó y la campaña sigue activa,     │
    │  la fecha se muestra en rojo.                              │
    └─────────────────────────────────────────────────────────────┘
    ┌─ KPIs (4 tarjetas) ─────────────────────────────────────────┐
    │  • Leads generados  — contactos captados por la campaña    │
    │  • Conversiones     — leads que se convirtieron en clientes │
    │  • Tasa conversión  — conversiones / leads × 100           │
    │  • ROI              — (ingresos − gasto) / presupuesto × % │
    │    Verde si positivo, rojo si negativo, "—" sin datos      │
    └─────────────────────────────────────────────────────────────┘
    ┌─ Barra de presupuesto ─────────────────────────────────────┐
    │  Solo se muestra si la campaña tiene presupuesto definido. │
    │  Barra de progreso: % gastado sobre el presupuesto.        │
    │  Cambia de color: azul < 80% · ámbar 80-99% · rojo ≥ 100% │
    │  Muestra gasto real, presupuesto, % usado, ingresos        │
    │  atribuidos y costo por lead (gasto / leads).              │
    └─────────────────────────────────────────────────────────────┘

    COLUMNA DERECHA (1/3):
    ┌─ Panel de estado ───────────────────────────────────────────┐
    │  Cambia el estado sin entrar al formulario de edición.     │
    │  Flujo: Borrador → Activa → Pausada / Completada           │
    └─────────────────────────────────────────────────────────────┘
    ┌─ Panel de métricas ─────────────────────────────────────────┐
    │  Actualiza los resultados de la campaña en tiempo real:    │
    │  • Leads generados                                         │
    │  • Conversiones                                            │
    │  • Gasto real ($) — lo que se gastó realmente              │
    │  • Ingresos atribuidos ($) — ventas que se le atribuyen    │
    │  Al guardar, el ROI y la barra de presupuesto se           │
    │  recalculan automáticamente.                               │
    └─────────────────────────────────────────────────────────────┘

    Componente: App\Livewire\Sales\CrmCampaignShow
    Modelo:     App\Models\CrmCampaign
    Métodos clave del modelo:
      roi()            → (revenue - spent) / budget * 100
      conversionRate() → conversions / leads * 100
      costPerLead()    → spent / leads
--}}
<div>
    <x-page-header :title="$campaign->folio . ' — ' . $campaign->name" description="">
        <x-slot:actions>
            <a wire:navigate href="{{ route('sales.crm.campaigns.index') }}"
                class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Volver
            </a>
            @can('create sales')
            <a wire:navigate href="{{ route('sales.crm.campaigns.edit', $campaign) }}"
                class="inline-flex items-center gap-2 text-sm bg-white border border-gray-200 rounded-xl px-4 py-2 text-gray-700 hover:bg-gray-50 transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.172-8.172z"/></svg>
                Editar
            </a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <x-alert />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left: info + metrics --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Info card --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                <div class="flex flex-wrap gap-2 mb-4">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium border {{ \App\Models\CrmCampaign::STATUS_COLORS[$campaign->status] ?? 'bg-gray-100 text-gray-500' }}">
                        {{ \App\Models\CrmCampaign::STATUSES[$campaign->status] ?? $campaign->status }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium border {{ \App\Models\CrmCampaign::TYPE_COLORS[$campaign->type] ?? 'bg-gray-100 text-gray-600' }}">
                        {{ \App\Models\CrmCampaign::TYPES[$campaign->type] ?? $campaign->type }}
                    </span>
                </div>

                @if($campaign->description)
                    <p class="text-sm text-gray-700 whitespace-pre-line mb-4">{{ $campaign->description }}</p>
                @endif

                @if($campaign->target_audience)
                    <div class="bg-indigo-50/60 rounded-xl p-4 mb-4">
                        <p class="text-xs font-semibold text-indigo-700 mb-1 uppercase tracking-wide">Audiencia objetivo</p>
                        <p class="text-sm text-indigo-900 whitespace-pre-line">{{ $campaign->target_audience }}</p>
                    </div>
                @endif

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs text-gray-500 pt-4 border-t border-gray-100">
                    <div>
                        <p class="font-semibold text-gray-700 uppercase tracking-wide mb-0.5">Creado por</p>
                        <p class="text-gray-800">{{ $campaign->createdBy?->name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-700 uppercase tracking-wide mb-0.5">Creado</p>
                        <p class="text-gray-800">{{ $campaign->created_at->format('d/m/Y') }}</p>
                    </div>
                    @if($campaign->start_at)
                    <div>
                        <p class="font-semibold text-gray-700 uppercase tracking-wide mb-0.5">Inicio</p>
                        <p class="text-gray-800">{{ $campaign->start_at->format('d/m/Y') }}</p>
                    </div>
                    @endif
                    @if($campaign->end_at)
                    <div>
                        <p class="font-semibold text-gray-700 uppercase tracking-wide mb-0.5">Fin</p>
                        <p class="text-gray-800 {{ $campaign->end_at->isPast() && !in_array($campaign->status, ['completed','cancelled']) ? 'text-red-600 font-semibold' : '' }}">
                            {{ $campaign->end_at->format('d/m/Y') }}
                        </p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- KPI cards --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4 text-center">
                    <p class="text-2xl font-bold text-indigo-600">{{ number_format($campaign->leads_generated) }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Leads generados</p>
                </div>
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4 text-center">
                    <p class="text-2xl font-bold text-emerald-600">{{ number_format($campaign->conversions) }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Conversiones</p>
                </div>
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4 text-center">
                    @php $cr = $campaign->conversionRate(); @endphp
                    <p class="text-2xl font-bold text-blue-600">{{ number_format($cr, 1) }}%</p>
                    <p class="text-xs text-gray-500 mt-0.5">Tasa conversión</p>
                </div>
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4 text-center">
                    @php $roi = $campaign->roi(); @endphp
                    <p class="text-2xl font-bold {{ $roi !== null ? ($roi >= 0 ? 'text-emerald-600' : 'text-red-500') : 'text-gray-400' }}">
                        {{ $roi !== null ? number_format($roi, 1) . '%' : '—' }}
                    </p>
                    <p class="text-xs text-gray-500 mt-0.5">ROI</p>
                </div>
            </div>

            {{-- Budget progress --}}
            @if($campaign->budget)
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Presupuesto</h3>
                @php
                    $spentPct = $campaign->budget > 0 ? min(($campaign->spent ?? 0) / $campaign->budget * 100, 100) : 0;
                @endphp
                <div class="flex justify-between text-xs text-gray-500 mb-1.5">
                    <span>Gastado: ${{ number_format($campaign->spent ?? 0, 2) }}</span>
                    <span>Presupuesto: ${{ number_format($campaign->budget, 2) }}</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2.5">
                    <div class="h-2.5 rounded-full {{ $spentPct >= 100 ? 'bg-red-500' : ($spentPct >= 80 ? 'bg-amber-400' : 'bg-indigo-500') }} transition-all"
                        style="width: {{ $spentPct }}%"></div>
                </div>
                <div class="flex justify-between text-xs mt-1.5">
                    <span class="text-gray-400">{{ number_format($spentPct, 1) }}% utilizado</span>
                    @if($campaign->revenue_generated)
                        <span class="text-emerald-600 font-medium">Ingresos: ${{ number_format($campaign->revenue_generated, 2) }}</span>
                    @endif
                </div>
                @if($campaign->costPerLead() !== null)
                <p class="text-xs text-gray-400 mt-2">Costo por lead: ${{ number_format($campaign->costPerLead(), 2) }}</p>
                @endif
            </div>
            @endif

        </div>

        {{-- Right panel --}}
        <div class="space-y-6">

            {{-- Status --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 space-y-3">
                <h3 class="text-sm font-semibold text-gray-700">Estado</h3>
                <select wire:model="newStatus" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 outline-none">
                    @foreach(\App\Models\CrmCampaign::STATUSES as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                <button wire:click="updateStatus" type="button"
                    class="w-full bg-gray-800 hover:bg-gray-900 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors cursor-pointer">
                    Actualizar estado
                </button>
            </div>

            {{-- Metrics updater --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 space-y-4">
                <h3 class="text-sm font-semibold text-gray-700">Actualizar métricas</h3>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Leads generados</label>
                    <input wire:model="leads_generated" type="number" min="0"
                        class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Conversiones</label>
                    <input wire:model="conversions" type="number" min="0"
                        class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Gasto real ($)</label>
                    <input wire:model="spent" type="number" step="0.01" min="0"
                        class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Ingresos atribuidos ($)</label>
                    <input wire:model="revenue_generated" type="number" step="0.01" min="0"
                        class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 outline-none">
                </div>

                <button wire:click="updateMetrics" type="button"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors cursor-pointer">
                    Guardar métricas
                </button>
            </div>
        </div>

    </div>
</div>

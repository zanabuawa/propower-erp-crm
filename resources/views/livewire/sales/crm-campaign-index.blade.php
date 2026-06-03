{{--
    LISTA DE CAMPAÑAS  (CrmCampaignIndex)
    ════════════════════════════════════════════════════════════════
    Muestra todas las campañas de marketing de la empresa en formato
    de tarjetas (grid). Se ordenan por estado (Activa primero) y
    luego por fecha de creación descendente.

    ┌─ TARJETAS DE RESUMEN ───────────────────────────────────────┐
    │  • Campañas activas   — cuántas están corriendo ahora       │
    │  • Leads generados    — suma total de leads de todas         │
    │  • Presupuesto total  — suma de campañas activas/completadas │
    │  • Ingresos atribuidos — suma de revenue_generated          │
    └─────────────────────────────────────────────────────────────┘

    ┌─ FILTROS ───────────────────────────────────────────────────┐
    │  • Búsqueda — por folio (CAM-00001) o nombre                │
    │  • Estado   — Borrador / Activa / Pausada / Completada /    │
    │               Cancelada                                      │
    │  • Canal    — Email / WhatsApp / SMS / Redes / Evento / etc.│
    └─────────────────────────────────────────────────────────────┘

    ┌─ TARJETAS DE CAMPAÑA ───────────────────────────────────────┐
    │  Cada tarjeta muestra:                                      │
    │  • Folio, nombre, estado y canal                            │
    │  • Descripción (truncada a 2 líneas)                        │
    │  • 3 métricas: Leads / Conversiones / ROI                   │
    │    ROI = (ingresos - gasto) / presupuesto × 100             │
    │    Verde si ROI ≥ 0, rojo si es negativo                    │
    │  • Fechas de inicio y fin (si están definidas)              │
    │  Clic en la tarjeta → detalle de la campaña                 │
    └─────────────────────────────────────────────────────────────┘

    Componente: App\Livewire\Sales\CrmCampaignIndex
    Modelo:     App\Models\CrmCampaign
--}}
<div>
    <x-page-header title="Campañas" description="Gestión de campañas de marketing y comunicación">
        <x-slot:actions>
            @can('create sales')
            <a wire:navigate href="{{ route('sales.crm.campaigns.create') }}"
                class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-medium px-5 py-2 rounded-xl transition-all shadow-lg shadow-indigo-500/25">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nueva campaña
            </a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <x-alert />

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-gray-200 p-4 shadow-sm">
            <p class="text-2xl font-bold text-emerald-600">{{ $stats['active'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Campañas activas</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4 shadow-sm">
            <p class="text-2xl font-bold text-indigo-600">{{ number_format($stats['leads']) }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Leads generados</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4 shadow-sm">
            <p class="text-2xl font-bold text-gray-700">${{ number_format($stats['budget'], 0) }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Presupuesto total</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4 shadow-sm">
            <p class="text-2xl font-bold text-blue-600">${{ number_format($stats['revenue'], 0) }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Ingresos atribuidos</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4 mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar por folio o nombre…"
                    class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 outline-none">
            </div>
            <select wire:model.live="status" class="border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 outline-none">
                <option value="">Todos los estados</option>
                @foreach(\App\Models\CrmCampaign::STATUSES as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
            <select wire:model.live="type" class="border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-400 outline-none">
                <option value="">Todos los canales</option>
                @foreach(\App\Models\CrmCampaign::TYPES as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Cards grid --}}
    @if($campaigns->isEmpty())
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm text-center py-16 text-gray-400">
            <svg class="w-10 h-10 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
            </svg>
            <p class="text-sm font-medium">Sin campañas</p>
            <p class="text-xs mt-1">Crea tu primera campaña para comenzar.</p>
        </div>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($campaigns as $campaign)
        <a wire:navigate href="{{ route('sales.crm.campaigns.show', $campaign) }}"
            class="bg-white rounded-2xl border border-gray-200 shadow-sm hover:shadow-md hover:border-indigo-200 transition-all cursor-pointer block p-5 group">

            <div class="flex items-start justify-between gap-2 mb-3">
                <div class="min-w-0">
                    <p class="font-mono text-xs text-gray-400 mb-0.5">{{ $campaign->folio }}</p>
                    <h3 class="text-sm font-semibold text-gray-900 truncate group-hover:text-indigo-700 transition-colors">{{ $campaign->name }}</h3>
                </div>
                <span class="inline-flex items-center shrink-0 px-2 py-0.5 rounded-md text-xs font-medium border {{ \App\Models\CrmCampaign::STATUS_COLORS[$campaign->status] ?? 'bg-gray-100 text-gray-500' }}">
                    {{ \App\Models\CrmCampaign::STATUSES[$campaign->status] ?? $campaign->status }}
                </span>
            </div>

            <div class="mb-4">
                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium border {{ \App\Models\CrmCampaign::TYPE_COLORS[$campaign->type] ?? 'bg-gray-100 text-gray-600' }}">
                    {{ \App\Models\CrmCampaign::TYPES[$campaign->type] ?? $campaign->type }}
                </span>
            </div>

            @if($campaign->description)
                <p class="text-xs text-gray-500 mb-4 line-clamp-2">{{ $campaign->description }}</p>
            @endif

            <div class="grid grid-cols-3 gap-2 text-center border-t border-gray-100 pt-3 mt-auto">
                <div>
                    <p class="text-sm font-bold text-gray-800">{{ number_format($campaign->leads_generated) }}</p>
                    <p class="text-xs text-gray-400">Leads</p>
                </div>
                <div>
                    <p class="text-sm font-bold text-gray-800">{{ number_format($campaign->conversions) }}</p>
                    <p class="text-xs text-gray-400">Conversiones</p>
                </div>
                <div>
                    @php $roi = $campaign->roi(); @endphp
                    <p class="text-sm font-bold {{ $roi !== null ? ($roi >= 0 ? 'text-emerald-600' : 'text-red-500') : 'text-gray-800' }}">
                        {{ $roi !== null ? number_format($roi, 1) . '%' : '—' }}
                    </p>
                    <p class="text-xs text-gray-400">ROI</p>
                </div>
            </div>

            @if($campaign->start_at || $campaign->end_at)
            <div class="text-xs text-gray-400 mt-3 pt-2 border-t border-gray-100 flex gap-3">
                @if($campaign->start_at)
                    <span>Inicio: {{ $campaign->start_at->format('d/m/Y') }}</span>
                @endif
                @if($campaign->end_at)
                    <span>Fin: {{ $campaign->end_at->format('d/m/Y') }}</span>
                @endif
            </div>
            @endif
        </a>
        @endforeach
    </div>

    <div class="mt-6">
        {{ $campaigns->links() }}
    </div>
    @endif
</div>

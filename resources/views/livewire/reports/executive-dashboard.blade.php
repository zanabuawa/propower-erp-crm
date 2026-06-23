<div>
    <x-page-header title="Dashboard Ejecutivo" description="Vista consolidada de todos los módulos">
        <x-slot:actions>
            <div class="flex gap-2">
                @foreach(['month' => 'Mes', 'quarter' => 'Trimestre', 'year' => 'Año'] as $val => $lbl)
                <button wire:click="$set('period','{{ $val }}')"
                        class="px-3 py-1.5 text-xs rounded-lg border transition {{ $period === $val ? 'bg-indigo-600 text-white border-indigo-600' : 'border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                    {{ $lbl }}
                </button>
                @endforeach
            </div>
        </x-slot:actions>
    </x-page-header>

    <x-alert />

    {{-- Alertas ────────────────────────────────────────────────────────────── --}}
    @if($alerts->isNotEmpty())
    <div class="space-y-2 mb-5">
        @foreach($alerts as $alert)
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl border
            {{ $alert['type'] === 'red' ? 'bg-red-50 border-red-200 text-red-700' : ($alert['type'] === 'amber' ? 'bg-amber-50 border-amber-200 text-amber-700' : 'bg-slate-50 border-slate-200 text-slate-600') }}
            text-sm">
            <span class="text-base">{{ $alert['type'] === 'red' ? '⚠' : ($alert['type'] === 'amber' ? '△' : 'ℹ') }}</span>
            {{ $alert['msg'] }}
        </div>
        @endforeach
    </div>
    @endif

    {{-- KPIs ───────────────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">

        <div class="bg-white rounded-xl border border-slate-200 p-4 sm:col-span-2">
            <p class="text-[10px] text-slate-400 uppercase tracking-wider mb-1">Ingresos facturados</p>
            <p class="text-2xl font-bold text-emerald-600">${{ number_format($revenueNow, 0) }}</p>
            @if($revenueGrowth !== null)
            <p class="text-xs mt-1 {{ $revenueGrowth >= 0 ? 'text-emerald-600' : 'text-red-500' }}">
                {{ $revenueGrowth >= 0 ? '▲' : '▼' }} {{ number_format(abs($revenueGrowth), 1) }}% vs período anterior
            </p>
            @endif
        </div>

        <div class="bg-white rounded-xl border {{ $overdueAmount > 0 ? 'border-red-200 bg-red-50' : 'border-slate-200' }} p-4">
            <p class="text-[10px] text-slate-400 uppercase tracking-wider mb-1">CxC Vencida</p>
            <p class="text-2xl font-bold {{ $overdueAmount > 0 ? 'text-red-600' : 'text-slate-300' }}">
                ${{ number_format($overdueAmount, 0) }}
            </p>
            <p class="text-xs text-slate-400 mt-1">{{ $overdueCount }} factura(s)</p>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <p class="text-[10px] text-slate-400 uppercase tracking-wider mb-1">Por Pagar</p>
            <p class="text-2xl font-bold text-amber-600">${{ number_format($pendingPayables, 0) }}</p>
            <p class="text-xs text-slate-400 mt-1">Compras pendientes</p>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <p class="text-[10px] text-slate-400 uppercase tracking-wider mb-1">Headcount</p>
            <p class="text-2xl font-bold text-indigo-600">{{ $headcount }}</p>
            <p class="text-xs text-slate-400 mt-1">${{ number_format($monthlySalaryCost, 0) }}/mes</p>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <p class="text-[10px] text-slate-400 uppercase tracking-wider mb-1">Proyectos</p>
            <p class="text-2xl font-bold text-slate-800">{{ $activeProjects }}</p>
            @if($projectsOverBudget > 0)
            <p class="text-xs text-red-500 mt-1">{{ $projectsOverBudget }} sobre presupuesto</p>
            @else
            <p class="text-xs text-slate-400 mt-1">activos</p>
            @endif
        </div>

    </div>

    {{-- Pipeline + Top clientes + Tendencia ──────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

        {{-- Pipeline CRM --}}
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Pipeline CRM</h3>
            </div>
            <div class="p-5 space-y-3">
                <div class="flex justify-between text-xs text-slate-500 mb-3">
                    <span>Valor bruto: <strong class="text-slate-800">${{ number_format($pipelineValue, 0) }}</strong></span>
                    <span>Ponderado: <strong class="text-indigo-600">${{ number_format($pipelineWeighted, 0) }}</strong></span>
                </div>
                @foreach(['qualification' => ['Calificación', 'bg-slate-300'], 'proposal' => ['Propuesta', 'bg-blue-400'], 'negotiation' => ['Negociación', 'bg-amber-400'], 'won' => ['Ganada', 'bg-emerald-500'], 'lost' => ['Perdida', 'bg-red-400']] as $key => [$label, $color])
                @php
                    $count = \App\Models\SalesOpportunity::where('company_id', auth()->user()->company_id)->where('stage', $key)->count();
                    $val = \App\Models\SalesOpportunity::where('company_id', auth()->user()->company_id)->where('stage', $key)->sum('estimated_value');
                @endphp
                <div class="flex items-center gap-2 text-xs">
                    <div class="w-2 h-2 rounded-full {{ $color }} flex-shrink-0"></div>
                    <span class="w-24 text-slate-600 flex-shrink-0">{{ $label }}</span>
                    <span class="text-slate-500 flex-shrink-0">{{ $count }}</span>
                    <span class="ml-auto font-bold text-slate-700">${{ number_format($val, 0) }}</span>
                </div>
                @endforeach
                <div class="pt-3 border-t border-slate-100">
                    <p class="text-xs text-slate-500">Ganado este período: <strong class="text-emerald-600">${{ number_format($wonThisPeriod, 0) }}</strong></p>
                    <a wire:navigate href="{{ route('sales.crm.analytics') }}" class="text-xs text-indigo-500 hover:underline mt-1 inline-block">Ver analytics CRM →</a>
                </div>
            </div>
        </div>

        {{-- Tendencia ingresos --}}
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Ingresos — últimos 6 meses</h3>
            </div>
            <div class="p-5">
                <div class="flex items-end gap-1.5 h-28">
                    @foreach($revenueTrend as $m)
                    <div class="flex-1 flex flex-col items-center gap-1">
                        <div class="w-full bg-emerald-500 rounded-t transition-all"
                             style="height: {{ $maxRevenue > 0 ? round($m['value']/$maxRevenue*100) : 0 }}%">
                        </div>
                        <span class="text-[9px] text-slate-400 truncate w-full text-center">{{ $m['label'] }}</span>
                    </div>
                    @endforeach
                </div>
                <div class="mt-3 flex justify-between text-xs">
                    <span class="text-slate-400">Min: ${{ number_format($revenueTrend->min('value'), 0) }}</span>
                    <span class="text-slate-400">Max: ${{ number_format($revenueTrend->max('value'), 0) }}</span>
                </div>
            </div>
        </div>

        {{-- Top clientes --}}
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Top 5 clientes</h3>
            </div>
            <div class="p-5 space-y-3">
                @forelse($topCustomers as $c)
                <div>
                    <div class="flex justify-between text-xs mb-1">
                        <span class="text-slate-700 font-medium truncate pr-2">{{ $c->name }}</span>
                        <span class="font-bold text-slate-800 flex-shrink-0">${{ number_format($c->total, 0) }}</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-1.5">
                        <div class="bg-indigo-400 h-1.5 rounded-full"
                             style="width: {{ round($c->total / $maxCustomer * 100) }}%"></div>
                    </div>
                </div>
                @empty
                <p class="text-sm text-slate-400 text-center py-4">Sin facturas en el período</p>
                @endforelse
                <a wire:navigate href="{{ route('sales.customers.analytics') }}" class="text-xs text-indigo-500 hover:underline mt-2 inline-block">Ver analytics clientes →</a>
            </div>
        </div>

    </div>

    {{-- Links rápidos a módulos ──────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        @foreach([
            ['route' => 'sales.report',         'label' => 'Reporte Ventas',     'icon' => '📈', 'color' => 'emerald'],
            ['route' => 'finance.dashboard',     'label' => 'Finanzas',           'icon' => '🏦', 'color' => 'indigo'],
            ['route' => 'hr.analytics',          'label' => 'RRHH Analytics',     'icon' => '👥', 'color' => 'violet'],
            ['route' => 'projects.analytics',    'label' => 'Proyectos',          'icon' => '📐', 'color' => 'amber'],
            ['route' => 'purchases.analytics',   'label' => 'Compras',            'icon' => '🛒', 'color' => 'rose'],
            ['route' => 'inventory.turnover',    'label' => 'Rotación Inventario','icon' => '📦', 'color' => 'cyan'],
            ['route' => 'finance.collections',   'label' => 'Cobranza',           'icon' => '💰', 'color' => 'teal'],
            ['route' => 'sales.crm.analytics',   'label' => 'CRM Pipeline',       'icon' => '🎯', 'color' => 'blue'],
        ] as $link)
        @if(\Route::has($link['route']))
        <a wire:navigate href="{{ route($link['route']) }}"
           class="bg-white rounded-xl border border-slate-200 p-4 flex items-center gap-3 hover:border-{{ $link['color'] }}-300 hover:bg-{{ $link['color'] }}-50 transition group">
            <span class="text-2xl">{{ $link['icon'] }}</span>
            <span class="text-sm font-medium text-slate-700 group-hover:text-{{ $link['color'] }}-700">{{ $link['label'] }}</span>
        </a>
        @endif
        @endforeach
    </div>
</div>

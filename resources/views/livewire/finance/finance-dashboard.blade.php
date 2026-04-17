<div class="space-y-6" x-data="financeDashboard()" x-init="init()">

    {{-- ── HEADER ─────────────────────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Gestión Financiera</h1>
            <p class="text-sm text-slate-500 mt-0.5">Resumen ejecutivo · {{ now()->isoFormat('MMMM YYYY') }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('finance.transactions.create') }}" wire:navigate
               class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nueva transacción
            </a>
            <a href="{{ route('finance.reports.index') }}" wire:navigate
               class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-lg transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z"/></svg>
                Reportes
            </a>
        </div>
    </div>

    {{-- ── ALERTAS ──────────────────────────────────────────────────────────── --}}
    @php
        $alerts = [];
        if ($overdueInvoices > 0)        $alerts[] = ['color'=>'red',    'icon'=>'!', 'text'=>"{$overdueInvoices} factura(s) de venta vencida(s)", 'route'=>route('finance.aging.index')];
        if ($overduePayables > 0)        $alerts[] = ['color'=>'orange', 'icon'=>'!', 'text'=>"{$overduePayables} factura(s) de proveedor vencida(s)", 'route'=>route('finance.ap-aging.index')];
        if ($scheduledToday > 0)         $alerts[] = ['color'=>'amber',  'icon'=>'$', 'text'=>"{$scheduledToday} pago(s) programado(s) para hoy", 'route'=>route('finance.scheduled-payments.index')];
        if ($pendingReconciliations > 0) $alerts[] = ['color'=>'blue',   'icon'=>'~', 'text'=>"{$pendingReconciliations} conciliación(es) bancaria(s) pendiente(s)", 'route'=>route('finance.bank-reconciliation.index')];
        if (!$periodClosed)              $alerts[] = ['color'=>'violet', 'icon'=>'⏱', 'text'=>"Período actual sin cerrar", 'route'=>route('finance.period-close.index')];
    @endphp

    @if(count($alerts))
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
        @foreach($alerts as $a)
        @php
            $colors = [
                'red'    => 'bg-red-50 border-red-200 text-red-700',
                'orange' => 'bg-orange-50 border-orange-200 text-orange-700',
                'amber'  => 'bg-amber-50 border-amber-200 text-amber-700',
                'blue'   => 'bg-blue-50 border-blue-200 text-blue-700',
                'violet' => 'bg-violet-50 border-violet-200 text-violet-700',
            ];
        @endphp
        <a href="{{ $a['route'] }}" wire:navigate
           class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg border text-xs font-medium transition-opacity hover:opacity-80 {{ $colors[$a['color']] }}">
            <span class="w-5 h-5 flex items-center justify-center rounded-full bg-white/70 font-bold text-[11px] flex-shrink-0">{{ $a['icon'] }}</span>
            {{ $a['text'] }}
        </a>
        @endforeach
    </div>
    @endif

    {{-- ── KPIs PRINCIPALES ─────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- Saldo total --}}
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Saldo en cuentas</p>
            <p class="text-2xl font-bold text-slate-800 mt-1">${{ number_format($totalBalance, 2) }}</p>
            <div class="mt-3 flex flex-wrap gap-1.5">
                @foreach($accounts->take(3) as $acc)
                <span class="text-[10px] bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded">
                    {{ $acc['name'] }}: ${{ number_format($acc['current_balance'], 0) }}
                </span>
                @endforeach
                @if($accounts->count() > 3)
                <span class="text-[10px] text-slate-400">+{{ $accounts->count()-3 }} más</span>
                @endif
            </div>
        </div>

        {{-- CxC --}}
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">CxC pendiente</p>
            <p class="text-2xl font-bold text-slate-800 mt-1">${{ number_format($cxcPending, 2) }}</p>
            @if($cxcOverdue > 0)
            <p class="text-xs text-red-600 font-medium mt-1">
                <span class="inline-block w-1.5 h-1.5 rounded-full bg-red-500 mr-1"></span>
                ${{ number_format($cxcOverdue, 2) }} vencido
            </p>
            @else
            <p class="text-xs text-emerald-600 mt-1">Sin vencidos</p>
            @endif
            <a href="{{ route('finance.aging.index') }}" wire:navigate class="text-[11px] text-indigo-500 hover:underline mt-1 block">Ver antigüedad →</a>
        </div>

        {{-- CxP --}}
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">CxP pendiente</p>
            <p class="text-2xl font-bold text-slate-800 mt-1">${{ number_format($cxpPending, 2) }}</p>
            @if($cxpOverdue > 0)
            <p class="text-xs text-red-600 font-medium mt-1">
                <span class="inline-block w-1.5 h-1.5 rounded-full bg-red-500 mr-1"></span>
                ${{ number_format($cxpOverdue, 2) }} vencido
            </p>
            @else
            <p class="text-xs text-emerald-600 mt-1">Sin vencidos</p>
            @endif
            <a href="{{ route('finance.ap-aging.index') }}" wire:navigate class="text-[11px] text-indigo-500 hover:underline mt-1 block">Ver antigüedad →</a>
        </div>

        {{-- Resultado del mes --}}
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Resultado del mes</p>
            <p class="text-2xl font-bold mt-1 {{ $monthResult >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                {{ $monthResult >= 0 ? '+' : '' }}${{ number_format($monthResult, 2) }}
            </p>
            <div class="flex gap-3 mt-2">
                <span class="text-[11px] text-emerald-600">↑ ${{ number_format($monthIncome, 0) }}</span>
                <span class="text-[11px] text-red-500">↓ ${{ number_format($monthExpense, 0) }}</span>
            </div>
            <a href="{{ route('finance.reports.index') }}" wire:navigate class="text-[11px] text-indigo-500 hover:underline mt-1 block">Ver reportes →</a>
        </div>
    </div>

    {{-- ── GRÁFICAS ──────────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        {{-- Ingresos vs Egresos --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <h3 class="text-sm font-semibold text-slate-700 mb-4">Ingresos vs Egresos — últimos 6 meses</h3>
            <canvas id="incomeExpenseChart" height="200"></canvas>
        </div>

        {{-- Flujo proyectado --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <h3 class="text-sm font-semibold text-slate-700 mb-1">Flujo proyectado — próximos 30 días</h3>
            <p class="text-xs text-slate-400 mb-4">Basado en pagos programados y vencimientos CxC</p>
            <canvas id="cashflowChart" height="200"></canvas>
        </div>
    </div>

    {{-- ── TABLAS INFERIORES ────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- Top deudores --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100">
                <h3 class="text-sm font-semibold text-slate-700">Top deudores (CxC)</h3>
                <a href="{{ route('finance.aging.index') }}" wire:navigate class="text-xs text-indigo-500 hover:underline">Ver todo</a>
            </div>
            <div class="divide-y divide-slate-50">
                @forelse($topDebtors as $d)
                <div class="flex items-center justify-between px-4 py-2.5">
                    <span class="text-xs text-slate-700 truncate max-w-[140px]">{{ $d->customer?->name ?? 'N/A' }}</span>
                    <span class="text-xs font-semibold text-slate-800">${{ number_format($d->balance, 0) }}</span>
                </div>
                @empty
                <div class="px-4 py-6 text-center text-xs text-slate-400">Sin deudores pendientes</div>
                @endforelse
            </div>
        </div>

        {{-- Top acreedores --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100">
                <h3 class="text-sm font-semibold text-slate-700">Top acreedores (CxP)</h3>
                <a href="{{ route('finance.ap-aging.index') }}" wire:navigate class="text-xs text-indigo-500 hover:underline">Ver todo</a>
            </div>
            <div class="divide-y divide-slate-50">
                @forelse($topCreditors as $c)
                <div class="flex items-center justify-between px-4 py-2.5">
                    <span class="text-xs text-slate-700 truncate max-w-[140px]">{{ $c->supplier?->name ?? 'N/A' }}</span>
                    <span class="text-xs font-semibold text-slate-800">${{ number_format($c->balance, 0) }}</span>
                </div>
                @empty
                <div class="px-4 py-6 text-center text-xs text-slate-400">Sin proveedores pendientes</div>
                @endforelse
            </div>
        </div>

        {{-- Próximos pagos programados --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100">
                <h3 class="text-sm font-semibold text-slate-700">Pagos programados</h3>
                <a href="{{ route('finance.scheduled-payments.index') }}" wire:navigate class="text-xs text-indigo-500 hover:underline">Ver todo</a>
            </div>
            <div class="divide-y divide-slate-50">
                @forelse($upcomingPayments as $p)
                @php
                    $isToday    = $p->scheduled_date->isToday();
                    $isOverdue  = $p->status === 'overdue';
                    $rowColor   = $isOverdue ? 'bg-red-50' : ($isToday ? 'bg-amber-50' : '');
                @endphp
                <div class="flex items-center justify-between px-4 py-2.5 {{ $rowColor }}">
                    <div class="min-w-0">
                        <p class="text-xs font-medium text-slate-700 truncate">{{ $p->concept }}</p>
                        <p class="text-[10px] text-slate-400">
                            {{ $p->scheduled_date->format('d/m/Y') }}
                            @if($isOverdue) <span class="text-red-500 font-semibold">• Vencido</span>
                            @elseif($isToday) <span class="text-amber-600 font-semibold">• Hoy</span>
                            @endif
                        </p>
                    </div>
                    <span class="text-xs font-semibold text-slate-800 ml-2 flex-shrink-0">${{ number_format($p->amount, 0) }}</span>
                </div>
                @empty
                <div class="px-4 py-6 text-center text-xs text-slate-400">Sin pagos próximos</div>
                @endforelse
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
function financeDashboard() {
    return {
        chartData: @json($chartData),
        projectionDays: @json($projectionDays),

        init() {
            this.$nextTick(() => {
                this.renderIncomeExpenseChart();
                this.renderCashflowChart();
            });
        },

        renderIncomeExpenseChart() {
            const ctx = document.getElementById('incomeExpenseChart');
            if (!ctx) return;
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: this.chartData.map(d => d.label),
                    datasets: [
                        {
                            label: 'Ingresos',
                            data: this.chartData.map(d => d.income),
                            backgroundColor: 'rgba(16,185,129,0.75)',
                            borderRadius: 4,
                        },
                        {
                            label: 'Egresos',
                            data: this.chartData.map(d => d.expense),
                            backgroundColor: 'rgba(239,68,68,0.65)',
                            borderRadius: 4,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    plugins: { legend: { position: 'bottom', labels: { font: { size: 11 } } } },
                    scales: {
                        x: { grid: { display: false }, ticks: { font: { size: 11 } } },
                        y: {
                            ticks: {
                                font: { size: 10 },
                                callback: v => '$' + Intl.NumberFormat('es-MX',{notation:'compact'}).format(v),
                            },
                            grid: { color: 'rgba(0,0,0,0.04)' },
                        },
                    },
                },
            });
        },

        renderCashflowChart() {
            const ctx = document.getElementById('cashflowChart');
            if (!ctx) return;
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: this.projectionDays.map(d => d.label),
                    datasets: [{
                        label: 'Saldo proyectado',
                        data: this.projectionDays.map(d => d.balance),
                        borderColor: 'rgb(99,102,241)',
                        backgroundColor: 'rgba(99,102,241,0.08)',
                        fill: true,
                        tension: 0.35,
                        pointRadius: 3,
                        pointBackgroundColor: 'rgb(99,102,241)',
                    }],
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                        y: {
                            ticks: {
                                font: { size: 10 },
                                callback: v => '$' + Intl.NumberFormat('es-MX',{notation:'compact'}).format(v),
                            },
                            grid: { color: 'rgba(0,0,0,0.04)' },
                        },
                    },
                },
            });
        },
    };
}
</script>
@endpush
</div>

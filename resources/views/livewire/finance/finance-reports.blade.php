<div class="space-y-5" x-data="financeReports()" x-init="init()">

    {{-- ── HEADER ─────────────────────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Reportes y Análisis</h1>
            <p class="text-sm text-slate-500 mt-0.5">Análisis detallado de movimientos financieros</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('finance.dashboard') }}" wire:navigate
               class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-lg transition-colors">
                ← Dashboard
            </a>
            @if($activeTab === 'transactions')
            <button wire:click="exportCsv"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Exportar CSV
            </button>
            @endif
        </div>
    </div>

    {{-- ── FILTROS ──────────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Desde</label>
                <input type="date" wire:model.live="dateFrom"
                       class="w-full text-xs border border-slate-200 rounded-lg px-2.5 py-1.5 focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 outline-none">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Hasta</label>
                <input type="date" wire:model.live="dateTo"
                       class="w-full text-xs border border-slate-200 rounded-lg px-2.5 py-1.5 focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 outline-none">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Cuenta</label>
                <select wire:model.live="accountId"
                        class="w-full text-xs border border-slate-200 rounded-lg px-2.5 py-1.5 focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 outline-none">
                    <option value="">Todas las cuentas</option>
                    @foreach($accounts as $acc)
                    <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Tipo</label>
                <select wire:model.live="type"
                        class="w-full text-xs border border-slate-200 rounded-lg px-2.5 py-1.5 focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 outline-none">
                    <option value="">Todos</option>
                    <option value="ingreso">Ingreso</option>
                    <option value="egreso">Egreso</option>
                    <option value="transferencia">Transferencia</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Categoría</label>
                <select wire:model.live="category"
                        class="w-full text-xs border border-slate-200 rounded-lg px-2.5 py-1.5 focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 outline-none">
                    <option value="">Todas</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat }}">{{ ucfirst($cat) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                {{-- Accesos rápidos de período --}}
                <div class="flex flex-wrap gap-1">
                    <button wire:click="$set('dateFrom', '{{ now()->startOfMonth()->toDateString() }}')"
                            onclick="document.querySelector('[wire\\:model\\.live=dateTo]').dispatchEvent(new Event('change'))"
                            class="text-[10px] px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded font-medium transition-colors">
                        Este mes
                    </button>
                    <button
                        x-on:click="
                            $wire.set('dateFrom', '{{ now()->subMonth()->startOfMonth()->toDateString() }}');
                            $wire.set('dateTo',   '{{ now()->subMonth()->endOfMonth()->toDateString() }}');
                        "
                        class="text-[10px] px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded font-medium transition-colors">
                        Mes ant.
                    </button>
                    <button
                        x-on:click="
                            $wire.set('dateFrom', '{{ now()->startOfYear()->toDateString() }}');
                            $wire.set('dateTo',   '{{ now()->toDateString() }}');
                        "
                        class="text-[10px] px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded font-medium transition-colors">
                        Este año
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ── TABS ─────────────────────────────────────────────────────────────── --}}
    <div class="border-b border-slate-200">
        <nav class="flex gap-1 -mb-px">
            @foreach([
                ['transactions', 'Transacciones'],
                ['monthly',      'Por mes'],
                ['categories',   'Por categoría'],
                ['budget',       'Presupuesto vs Real'],
            ] as [$tab, $label])
            <button wire:click="$set('activeTab', '{{ $tab }}')"
                    class="px-4 py-2.5 text-xs font-semibold border-b-2 transition-colors whitespace-nowrap
                           {{ $activeTab === $tab
                               ? 'border-indigo-500 text-indigo-600'
                               : 'border-transparent text-slate-500 hover:text-slate-700' }}">
                {{ $label }}
            </button>
            @endforeach
        </nav>
    </div>

    {{-- ── TAB: TRANSACCIONES ───────────────────────────────────────────────── --}}
    @if($activeTab === 'transactions')
    <div class="space-y-4">

        {{-- Totales --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm text-center">
                <p class="text-xs text-slate-500">Movimientos</p>
                <p class="text-xl font-bold text-slate-800">{{ number_format($totals['count']) }}</p>
            </div>
            <div class="bg-white rounded-xl border border-emerald-200 p-4 shadow-sm text-center">
                <p class="text-xs text-emerald-600">Ingresos</p>
                <p class="text-xl font-bold text-emerald-700">${{ number_format($totals['income'], 2) }}</p>
            </div>
            <div class="bg-white rounded-xl border border-red-200 p-4 shadow-sm text-center">
                <p class="text-xs text-red-500">Egresos</p>
                <p class="text-xl font-bold text-red-600">${{ number_format($totals['expense'], 2) }}</p>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm text-center">
                <p class="text-xs text-slate-500">Neto</p>
                <p class="text-xl font-bold {{ $totals['net'] >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                    {{ $totals['net'] >= 0 ? '+' : '' }}${{ number_format($totals['net'], 2) }}
                </p>
            </div>
        </div>

        {{-- Tabla --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="text-left px-4 py-2.5 font-semibold text-slate-600">Fecha</th>
                            <th class="text-left px-4 py-2.5 font-semibold text-slate-600">Folio</th>
                            <th class="text-left px-4 py-2.5 font-semibold text-slate-600">Concepto</th>
                            <th class="text-left px-4 py-2.5 font-semibold text-slate-600">Categoría</th>
                            <th class="text-left px-4 py-2.5 font-semibold text-slate-600">Cuenta</th>
                            <th class="text-left px-4 py-2.5 font-semibold text-slate-600">Tipo</th>
                            <th class="text-right px-4 py-2.5 font-semibold text-slate-600">Monto</th>
                            <th class="text-center px-4 py-2.5 font-semibold text-slate-600">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($transactions as $tx)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-4 py-2.5 text-slate-500 whitespace-nowrap">{{ $tx->transaction_date->format('d/m/Y') }}</td>
                            <td class="px-4 py-2.5 font-mono text-slate-600">{{ $tx->folio }}</td>
                            <td class="px-4 py-2.5 text-slate-700 max-w-[200px] truncate">{{ $tx->concept }}</td>
                            <td class="px-4 py-2.5 text-slate-500">{{ $tx->category ? ucfirst($tx->category) : '—' }}</td>
                            <td class="px-4 py-2.5 text-slate-500 whitespace-nowrap">{{ $tx->account?->name ?? '—' }}</td>
                            <td class="px-4 py-2.5">
                                @if($tx->type === 'ingreso')
                                <span class="inline-flex items-center gap-1 text-emerald-700 font-medium">↑ Ingreso</span>
                                @elseif($tx->type === 'egreso')
                                <span class="inline-flex items-center gap-1 text-red-600 font-medium">↓ Egreso</span>
                                @else
                                <span class="text-slate-500">⇄ Transfer.</span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5 text-right font-semibold whitespace-nowrap
                                       {{ $tx->type === 'ingreso' ? 'text-emerald-700' : ($tx->type === 'egreso' ? 'text-red-600' : 'text-slate-700') }}">
                                ${{ number_format($tx->amount, 2) }}
                            </td>
                            <td class="px-4 py-2.5 text-center">
                                @php
                                    $sc = ['confirmado'=>'bg-green-100 text-green-700','pendiente'=>'bg-amber-100 text-amber-700','cancelado'=>'bg-red-100 text-red-600'];
                                @endphp
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $sc[$tx->status] ?? 'bg-slate-100 text-slate-600' }}">
                                    {{ ucfirst($tx->status) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="px-4 py-10 text-center text-slate-400">Sin movimientos en el período seleccionado</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- ── TAB: POR MES ─────────────────────────────────────────────────────── --}}
    @if($activeTab === 'monthly')
    <div class="space-y-4">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
            <div class="p-4 border-b border-slate-100">
                <canvas id="monthlyChart" height="120"></canvas>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="text-left px-4 py-2.5 font-semibold text-slate-600">Período</th>
                            <th class="text-right px-4 py-2.5 font-semibold text-emerald-600">Ingresos</th>
                            <th class="text-right px-4 py-2.5 font-semibold text-red-500">Egresos</th>
                            <th class="text-right px-4 py-2.5 font-semibold text-slate-600">Neto</th>
                            <th class="text-right px-4 py-2.5 font-semibold text-slate-600">Margen</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($monthlyBreakdown as $row)
                        @php $margin = $row['income'] > 0 ? round(($row['net'] / $row['income']) * 100, 1) : null; @endphp
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-4 py-2.5 font-medium text-slate-700">{{ $row['label'] }}</td>
                            <td class="px-4 py-2.5 text-right text-emerald-700 font-semibold">${{ number_format($row['income'], 2) }}</td>
                            <td class="px-4 py-2.5 text-right text-red-600 font-semibold">${{ number_format($row['expense'], 2) }}</td>
                            <td class="px-4 py-2.5 text-right font-bold {{ $row['net'] >= 0 ? 'text-emerald-700' : 'text-red-600' }}">
                                {{ $row['net'] >= 0 ? '+' : '' }}${{ number_format($row['net'], 2) }}
                            </td>
                            <td class="px-4 py-2.5 text-right text-slate-500">
                                {{ $margin !== null ? $margin.'%' : '—' }}
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-4 py-10 text-center text-slate-400">Sin datos en el período</td></tr>
                        @endforelse
                    </tbody>
                    @if(count($monthlyBreakdown) > 1)
                    @php
                        $totInc = collect($monthlyBreakdown)->sum('income');
                        $totExp = collect($monthlyBreakdown)->sum('expense');
                        $totNet = $totInc - $totExp;
                    @endphp
                    <tfoot class="bg-slate-50 border-t-2 border-slate-200 font-bold text-xs">
                        <tr>
                            <td class="px-4 py-2.5 text-slate-700">Total</td>
                            <td class="px-4 py-2.5 text-right text-emerald-700">${{ number_format($totInc, 2) }}</td>
                            <td class="px-4 py-2.5 text-right text-red-600">${{ number_format($totExp, 2) }}</td>
                            <td class="px-4 py-2.5 text-right {{ $totNet >= 0 ? 'text-emerald-700' : 'text-red-600' }}">
                                {{ $totNet >= 0 ? '+' : '' }}${{ number_format($totNet, 2) }}
                            </td>
                            <td class="px-4 py-2.5 text-right text-slate-500">
                                {{ $totInc > 0 ? round(($totNet/$totInc)*100,1).'%' : '—' }}
                            </td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- ── TAB: POR CATEGORÍA ───────────────────────────────────────────────── --}}
    @if($activeTab === 'categories')
    <div class="space-y-4">
        @php
            $incRows = collect($categoryBreakdown)->where('type', 'ingreso')->sortByDesc('total');
            $expRows = collect($categoryBreakdown)->where('type', 'egreso')->sortByDesc('total');
            $totalInc = $incRows->sum('total');
            $totalExp = $expRows->sum('total');
        @endphp

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

            {{-- Ingresos por categoría --}}
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-emerald-700">Ingresos por categoría</h3>
                    <span class="text-sm font-bold text-emerald-700">${{ number_format($totalInc, 2) }}</span>
                </div>
                <div class="divide-y divide-slate-50">
                    @forelse($incRows as $row)
                    @php $pct = $totalInc > 0 ? round($row['total']/$totalInc*100,1) : 0; @endphp
                    <div class="px-4 py-2.5">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-xs text-slate-700 font-medium">{{ ucfirst($row['category'] ?: 'Sin categoría') }}</span>
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] text-slate-400">{{ $row['count'] }} mvtos</span>
                                <span class="text-xs font-semibold text-emerald-700">${{ number_format($row['total'], 2) }}</span>
                                <span class="text-[10px] text-slate-400 w-8 text-right">{{ $pct }}%</span>
                            </div>
                        </div>
                        <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full bg-emerald-500 rounded-full" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                    @empty
                    <div class="px-4 py-6 text-center text-xs text-slate-400">Sin ingresos en el período</div>
                    @endforelse
                </div>
            </div>

            {{-- Egresos por categoría --}}
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-red-600">Egresos por categoría</h3>
                    <span class="text-sm font-bold text-red-600">${{ number_format($totalExp, 2) }}</span>
                </div>
                <div class="divide-y divide-slate-50">
                    @forelse($expRows as $row)
                    @php $pct = $totalExp > 0 ? round($row['total']/$totalExp*100,1) : 0; @endphp
                    <div class="px-4 py-2.5">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-xs text-slate-700 font-medium">{{ ucfirst($row['category'] ?: 'Sin categoría') }}</span>
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] text-slate-400">{{ $row['count'] }} mvtos</span>
                                <span class="text-xs font-semibold text-red-600">${{ number_format($row['total'], 2) }}</span>
                                <span class="text-[10px] text-slate-400 w-8 text-right">{{ $pct }}%</span>
                            </div>
                        </div>
                        <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full bg-red-400 rounded-full" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                    @empty
                    <div class="px-4 py-6 text-center text-xs text-slate-400">Sin egresos en el período</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ── TAB: PRESUPUESTO VS REAL ─────────────────────────────────────────── --}}
    @if($activeTab === 'budget')
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        @if(count($budgetComparison))
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-left px-4 py-2.5 font-semibold text-slate-600">Presupuesto</th>
                        <th class="text-left px-4 py-2.5 font-semibold text-slate-600">Categoría</th>
                        <th class="text-right px-4 py-2.5 font-semibold text-slate-600">Planeado</th>
                        <th class="text-right px-4 py-2.5 font-semibold text-slate-600">Ejecutado</th>
                        <th class="text-right px-4 py-2.5 font-semibold text-slate-600">Variación</th>
                        <th class="text-center px-4 py-2.5 font-semibold text-slate-600">Ejecución</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($budgetComparison as $row)
                    @php
                        $over = $row['variance'] > 0;
                        $pct  = $row['progress_pct'];
                        $barColor = $pct > 110 ? 'bg-red-500' : ($pct > 90 ? 'bg-amber-400' : 'bg-indigo-500');
                    @endphp
                    <tr class="hover:bg-slate-50/50">
                        <td class="px-4 py-2.5 font-medium text-slate-700">{{ $row['name'] }}</td>
                        <td class="px-4 py-2.5 text-slate-500">{{ ucfirst($row['category'] ?: '—') }}</td>
                        <td class="px-4 py-2.5 text-right text-slate-700">${{ number_format($row['planned'], 2) }}</td>
                        <td class="px-4 py-2.5 text-right font-semibold text-slate-800">${{ number_format($row['actual'], 2) }}</td>
                        <td class="px-4 py-2.5 text-right font-semibold {{ $over ? 'text-red-600' : 'text-emerald-600' }}">
                            {{ $over ? '+' : '' }}${{ number_format($row['variance'], 2) }}
                            @if($row['variance_pct'] !== null)
                            <span class="text-[10px] ml-1">({{ $row['variance_pct'] > 0 ? '+' : '' }}{{ $row['variance_pct'] }}%)</span>
                            @endif
                        </td>
                        <td class="px-4 py-2.5">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 h-2 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full {{ $barColor }} rounded-full transition-all" style="width: {{ min(100, $pct) }}%"></div>
                                </div>
                                <span class="text-[10px] w-7 text-right text-slate-500">{{ $pct }}%</span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="py-16 text-center">
            <p class="text-slate-400 text-sm">No hay presupuestos definidos para el año seleccionado.</p>
            <a href="{{ route('finance.budgets.create') }}" wire:navigate
               class="inline-flex items-center gap-1.5 mt-3 px-4 py-2 bg-indigo-600 text-white text-xs font-semibold rounded-lg hover:bg-indigo-700">
                Crear presupuesto
            </a>
        </div>
        @endif
    </div>
    @endif

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
function financeReports() {
    return {
        monthlyData: @json($monthlyBreakdown),
        activeTab: @json($activeTab),

        init() {
            if (this.activeTab === 'monthly') {
                this.$nextTick(() => this.renderMonthlyChart());
            }
        },

        renderMonthlyChart() {
            const ctx = document.getElementById('monthlyChart');
            if (!ctx || !this.monthlyData.length) return;
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: this.monthlyData.map(d => d.label),
                    datasets: [
                        {
                            label: 'Ingresos',
                            data: this.monthlyData.map(d => d.income),
                            backgroundColor: 'rgba(16,185,129,0.75)',
                            borderRadius: 4,
                        },
                        {
                            label: 'Egresos',
                            data: this.monthlyData.map(d => d.expense),
                            backgroundColor: 'rgba(239,68,68,0.65)',
                            borderRadius: 4,
                        },
                        {
                            label: 'Neto',
                            data: this.monthlyData.map(d => d.net),
                            type: 'line',
                            borderColor: 'rgb(99,102,241)',
                            backgroundColor: 'rgba(99,102,241,0.06)',
                            fill: false,
                            tension: 0.3,
                            pointRadius: 4,
                            yAxisID: 'y',
                        },
                    ],
                },
                options: {
                    responsive: true,
                    plugins: { legend: { position: 'bottom', labels: { font: { size: 11 } } } },
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

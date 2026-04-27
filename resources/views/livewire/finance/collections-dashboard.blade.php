<div>
    {{-- ── Header ─────────────────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-6">
        <div class="flex-1">
            <h1 class="text-xl font-medium text-gray-900">Dashboard de cobranza</h1>
            <p class="text-sm text-gray-500">KPIs y métricas de cuentas por cobrar</p>
        </div>
        {{-- Selector de período --}}
        <div class="flex items-center gap-2 flex-wrap">
            <div class="flex rounded-lg border border-gray-200 overflow-hidden text-sm">
                @foreach(['month' => 'Mes', 'quarter' => 'Trimestre', 'year' => 'Año'] as $key => $label)
                <button wire:click="$set('period','{{ $key }}')"
                    class="px-3 py-1.5 transition {{ $period === $key ? 'bg-indigo-600 text-white font-medium' : 'bg-white text-gray-600 hover:bg-gray-50' }}">
                    {{ $label }}
                </button>
                @endforeach
                <button wire:click="$set('period','custom')"
                    class="px-3 py-1.5 transition {{ $period === 'custom' ? 'bg-indigo-600 text-white font-medium' : 'bg-white text-gray-600 hover:bg-gray-50' }}">
                    Personalizado
                </button>
            </div>
            @if($period === 'custom')
            <div class="flex items-center gap-1 text-sm">
                <input wire:model.live="dateFrom" type="date"
                    class="border border-gray-200 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                <span class="text-gray-400">—</span>
                <input wire:model.live="dateTo" type="date"
                    class="border border-gray-200 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
            </div>
            @else
            <span class="text-xs text-gray-400">
                {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}
            </span>
            @endif
        </div>
    </div>

    <x-alert />

    {{-- ── KPI Cards ────────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-3 mb-6">

        {{-- Total por cobrar --}}
        <div class="col-span-2 xl:col-span-1 bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <div class="flex items-start justify-between mb-2">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Total por cobrar</p>
                <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900">${{ number_format($totalReceivable, 0) }}</p>
            <p class="text-xs text-gray-400 mt-1">saldo total pendiente</p>
        </div>

        {{-- Vencido --}}
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm {{ $overdueAmount > 0 ? 'border-l-4 border-l-red-400' : '' }}">
            <div class="flex items-start justify-between mb-2">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Vencido</p>
                <div class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold {{ $overdueAmount > 0 ? 'text-red-600' : 'text-gray-900' }}">${{ number_format($overdueAmount, 0) }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $overduePercent }}% del total</p>
        </div>

        {{-- DSO --}}
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <div class="flex items-start justify-between mb-2">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">DSO</p>
                <div class="w-8 h-8 rounded-lg bg-purple-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
            @if($dso !== null)
            <p class="text-2xl font-bold {{ $dso > 60 ? 'text-red-600' : ($dso > 30 ? 'text-yellow-600' : 'text-gray-900') }}">
                {{ $dso }} <span class="text-sm font-normal text-gray-400">días</span>
            </p>
            @else
            <p class="text-2xl font-bold text-gray-300">—</p>
            @endif
            <p class="text-xs text-gray-400 mt-1">días promedio de cobro</p>
        </div>

        {{-- Cobrado en el período --}}
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <div class="flex items-start justify-between mb-2">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Cobrado</p>
                <div class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-green-700">${{ number_format($collectedPeriod, 0) }}</p>
            <p class="text-xs text-gray-400 mt-1">en el período</p>
        </div>

        {{-- Facturado en el período --}}
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <div class="flex items-start justify-between mb-2">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Facturado</p>
                <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900">${{ number_format($invoicedPeriod, 0) }}</p>
            <p class="text-xs text-gray-400 mt-1">en el período</p>
        </div>

        {{-- Tasa de cobro --}}
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <div class="flex items-start justify-between mb-2">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Tasa cobro</p>
                <div class="w-8 h-8 rounded-lg bg-teal-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
            </div>
            @if($collectionRate !== null)
            <p class="text-2xl font-bold {{ $collectionRate >= 80 ? 'text-teal-600' : ($collectionRate >= 50 ? 'text-yellow-600' : 'text-red-600') }}">
                {{ $collectionRate }}<span class="text-sm font-normal text-gray-400">%</span>
            </p>
            @else
            <p class="text-2xl font-bold text-gray-300">—</p>
            @endif
            <p class="text-xs text-gray-400 mt-1">cobrado vs facturado</p>
        </div>

    </div>

    {{-- ── Fila central: Aging + Gráfica de cobros ──────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">

        {{-- Antigüedad de saldos (barras) --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-sm font-semibold text-gray-700">Antigüedad de saldos</h2>
                <a wire:navigate href="{{ route('finance.aging.index') }}"
                   class="text-xs text-indigo-600 hover:underline">Ver detalle →</a>
            </div>
            @php $agingTotal = collect($agingBuckets)->sum('amount') ?: 1; @endphp
            <div class="space-y-3">
                @foreach($agingBuckets as $key => $bucket)
                @php
                    $pct = round($bucket['amount'] / $agingTotal * 100, 1);
                @endphp
                <div>
                    <div class="flex justify-between text-xs text-gray-600 mb-1">
                        <span class="font-medium">{{ $bucket['label'] }}</span>
                        <span class="font-semibold text-gray-900">${{ number_format($bucket['amount'], 0) }}
                            <span class="text-gray-400 font-normal">({{ $pct }}%)</span>
                        </span>
                    </div>
                    <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                        <div class="{{ $bucket['color'] }} h-2 rounded-full transition-all duration-500"
                             style="width: {{ $pct }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Cobros últimos 6 meses --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-sm font-semibold text-gray-700">Cobros últimos 6 meses</h2>
            </div>
            <div class="flex items-end gap-2 h-32">
                @foreach($monthlyCollections as $m)
                @php
                    $barHeight = $maxMonthly > 0 ? max(4, round($m['amount'] / $maxMonthly * 100)) : 4;
                @endphp
                <div class="flex-1 flex flex-col items-center gap-1">
                    <div class="w-full bg-indigo-100 hover:bg-indigo-200 rounded-t transition-all duration-300 relative group"
                         style="height: {{ $barHeight }}%">
                        <div class="absolute -top-6 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-xs px-1.5 py-0.5 rounded whitespace-nowrap transition pointer-events-none z-10">
                            ${{ number_format($m['amount'], 0) }}
                        </div>
                    </div>
                    <span class="text-xs text-gray-400 capitalize">{{ $m['label'] }}</span>
                </div>
                @endforeach
            </div>
            @php $totalMonthly = collect($monthlyCollections)->sum('amount'); @endphp
            <div class="mt-3 pt-3 border-t border-gray-100 flex justify-between text-xs text-gray-500">
                <span>Total 6 meses</span>
                <span class="font-semibold text-gray-900">${{ number_format($totalMonthly, 0) }}</span>
            </div>
        </div>

    </div>

    {{-- ── Fila inferior: Top deudores + Cobros recientes ───────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        {{-- Top deudores --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-700">Top deudores</h2>
                <a wire:navigate href="{{ route('finance.aging.index') }}"
                   class="text-xs text-indigo-600 hover:underline">Ver todos →</a>
            </div>
            @if($topDebtors->isEmpty())
            <div class="px-5 py-8 text-center text-gray-400 text-sm">Sin saldos pendientes.</div>
            @else
            @php $maxDebt = $topDebtors->max('balance') ?: 1; @endphp
            <ul class="divide-y divide-gray-50">
                @foreach($topDebtors as $debtor)
                @php $pct = round($debtor->balance / $maxDebt * 100); @endphp
                <li class="px-5 py-3">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm font-medium text-gray-800 truncate max-w-[200px]">
                            {{ $debtor->customer->name ?? '—' }}
                        </span>
                        <div class="text-right flex-shrink-0 ml-3">
                            <span class="text-sm font-bold text-gray-900">${{ number_format($debtor->balance, 0) }}</span>
                            <span class="text-xs text-gray-400 ml-1">{{ $debtor->invoice_count }} fact.</span>
                        </div>
                    </div>
                    <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-1.5 bg-indigo-400 rounded-full" style="width: {{ $pct }}%"></div>
                    </div>
                </li>
                @endforeach
            </ul>
            @endif
        </div>

        {{-- Cobros recientes --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-700">Cobros recientes</h2>
            </div>
            @if($recentPayments->isEmpty())
            <div class="px-5 py-8 text-center text-gray-400 text-sm">No hay cobros registrados.</div>
            @else
            <ul class="divide-y divide-gray-50">
                @foreach($recentPayments as $payment)
                <li class="px-5 py-3 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium text-gray-800 truncate">{{ $payment->customer->name ?? '—' }}</div>
                        <div class="flex items-center gap-2 text-xs text-gray-400">
                            <span class="font-mono">{{ $payment->invoice->folio ?? '—' }}</span>
                            <span>·</span>
                            <span>{{ $payment->paid_at->format('d/m/Y') }}</span>
                            <span>·</span>
                            <span class="capitalize">{{ \App\Models\SalePayment::PAYMENT_METHODS[$payment->payment_method] ?? $payment->payment_method }}</span>
                        </div>
                    </div>
                    <span class="text-sm font-bold text-green-700 flex-shrink-0">
                        +${{ number_format($payment->amount, 0) }}
                    </span>
                </li>
                @endforeach
            </ul>
            @endif
        </div>

    </div>
</div>

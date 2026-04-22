<div>
    <x-page-header title="CRM Analytics" description="Pipeline, conversión y rendimiento de ventas">
        <x-slot:actions>
            <a wire:navigate href="{{ route('sales.crm.pipeline') }}"
               class="px-3 py-2 text-sm border border-slate-200 text-slate-600 rounded-lg hover:bg-slate-50 transition">
                ← Pipeline
            </a>
        </x-slot:actions>
    </x-page-header>

    <x-alert />

    {{-- Filtros ────────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-slate-200 p-4 mb-5 flex flex-wrap gap-3">
        <div>
            <label class="block text-[10px] text-slate-400 mb-0.5 uppercase">Desde</label>
            <input wire:model.live="filterFrom" type="date"
                   class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
        </div>
        <div>
            <label class="block text-[10px] text-slate-400 mb-0.5 uppercase">Hasta</label>
            <input wire:model.live="filterTo" type="date"
                   class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
        </div>
        <div>
            <label class="block text-[10px] text-slate-400 mb-0.5 uppercase">Ejecutivo</label>
            <select wire:model.live="filterUser"
                    class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30 bg-white">
                <option value="">Todos</option>
                @foreach($users as $u)
                <option value="{{ $u->id }}">{{ $u->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- KPIs ───────────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-6">
        <div class="bg-white rounded-xl border border-slate-200 p-4 text-center">
            <p class="text-2xl font-bold text-slate-800">{{ $total }}</p>
            <p class="text-[10px] text-slate-400 mt-0.5 uppercase">Oportunidades</p>
        </div>
        <div class="bg-white rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-center">
            <p class="text-2xl font-bold text-emerald-700">{{ $winRate !== null ? number_format($winRate, 1).'%' : '—' }}</p>
            <p class="text-[10px] text-slate-400 mt-0.5 uppercase">Tasa de cierre</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 text-center">
            <p class="text-2xl font-bold text-indigo-600">${{ number_format($pipelineValue, 0) }}</p>
            <p class="text-[10px] text-slate-400 mt-0.5 uppercase">Pipeline activo</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 text-center">
            <p class="text-2xl font-bold text-emerald-600">${{ number_format($wonValue, 0) }}</p>
            <p class="text-[10px] text-slate-400 mt-0.5 uppercase">Ganado</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 text-center">
            <p class="text-2xl font-bold text-slate-600">{{ $avgDaysToClose !== null ? round($avgDaysToClose).' días' : '—' }}</p>
            <p class="text-[10px] text-slate-400 mt-0.5 uppercase">Días a cerrar</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

        {{-- Embudo de conversión ─────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Embudo de pipeline</h3>
            </div>
            @php
                $activeTotal = $activeStages->sum('count') ?: 1;
                $stageColors = ['qualification' => 'bg-slate-400', 'proposal' => 'bg-blue-400', 'negotiation' => 'bg-amber-400', 'won' => 'bg-emerald-500', 'lost' => 'bg-red-400'];
            @endphp
            <div class="p-5 space-y-3">
                @foreach($stageData as $s)
                <div>
                    <div class="flex justify-between text-xs mb-1">
                        <span class="text-slate-600 font-medium">{{ $s['label'] }}</span>
                        <div class="flex gap-3 text-right">
                            <span class="text-slate-500">{{ $s['count'] }}</span>
                            <span class="font-bold text-slate-800">${{ number_format($s['value'], 0) }}</span>
                        </div>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-3">
                        <div class="{{ $stageColors[$s['key']] ?? 'bg-slate-400' }} h-3 rounded-full transition-all"
                             style="width: {{ $maxPipelineValue > 0 && $s['value'] > 0 ? max(5, round($s['value']/$maxPipelineValue*100)) : ($s['count'] > 0 ? 10 : 0) }}%"></div>
                    </div>
                    @if($s['weighted'] > 0)
                    <p class="text-[10px] text-slate-400 mt-0.5">Ponderado: ${{ number_format($s['weighted'], 0) }}</p>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        {{-- Razones de pérdida ─────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Razones de pérdida</h3>
                <p class="text-xs text-slate-400 mt-0.5">Valor perdido: <strong class="text-red-500">${{ number_format($lostValue, 0) }}</strong></p>
            </div>
            <div class="p-5 space-y-3">
                @php $maxLost = $lostReasons->max('count') ?: 1; @endphp
                @forelse($lostReasons as $r)
                <div>
                    <div class="flex justify-between text-xs mb-1">
                        <span class="text-slate-600 truncate pr-2">{{ $r['reason'] }}</span>
                        <span class="font-bold text-slate-800 flex-shrink-0">{{ $r['count'] }} · ${{ number_format($r['value'], 0) }}</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2">
                        <div class="bg-red-400 h-2 rounded-full" style="width: {{ round($r['count']/$maxLost*100) }}%"></div>
                    </div>
                </div>
                @empty
                <p class="text-sm text-slate-400 text-center py-4">Sin oportunidades perdidas</p>
                @endforelse
            </div>
        </div>

        {{-- Por ejecutivo ──────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Por ejecutivo</h3>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($byUser as $u)
                <div class="px-5 py-3">
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-sm font-medium text-slate-800 truncate pr-2">{{ $u['name'] }}</span>
                        <span class="text-xs font-bold {{ ($u['win_rate'] ?? 0) >= 50 ? 'text-emerald-600' : 'text-amber-600' }}">
                            {{ $u['win_rate'] !== null ? number_format($u['win_rate'], 0).'%' : '—' }}
                        </span>
                    </div>
                    <div class="flex gap-3 text-[10px] text-slate-400">
                        <span class="text-emerald-600">{{ $u['won'] }} ganadas</span>
                        <span class="text-red-400">{{ $u['lost'] }} perdidas</span>
                        <span class="text-indigo-500">{{ $u['active'] }} activas</span>
                        <span class="ml-auto font-bold text-slate-700">${{ number_format($u['value'], 0) }}</span>
                    </div>
                </div>
                @empty
                <p class="px-5 py-8 text-sm text-slate-400 text-center">Sin datos</p>
                @endforelse
            </div>
        </div>

    </div>

    {{-- Tendencia mensual ─────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Valor ganado por mes (12 meses)</h3>
            </div>
            <div class="p-5">
                <div class="flex items-end gap-1 h-28">
                    @foreach($monthlyTrend as $m)
                    <div class="flex-1 flex flex-col items-center gap-1">
                        <div class="w-full bg-emerald-500 rounded-t"
                             style="height: {{ $maxMonthVal > 0 ? round($m['won_val']/$maxMonthVal*100) : 0 }}%">
                        </div>
                        <span class="text-[8px] text-slate-400">{{ $m['label'] }}</span>
                    </div>
                    @endforeach
                </div>
                <div class="mt-2 flex gap-4 text-xs text-slate-500">
                    <span>Total ganado: <strong class="text-emerald-700">${{ number_format($monthlyTrend->sum('won_val'), 0) }}</strong></span>
                    <span>Nuevas: <strong class="text-slate-700">{{ $monthlyTrend->sum('new') }}</strong></span>
                </div>
            </div>
        </div>

        {{-- Próximas a cerrar --}}
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Cierran en los próximos 30 días</h3>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($closingSoon as $opp)
                @php
                    $days = now()->diffInDays($opp->expected_close_date, false);
                    $urgentColor = $days <= 7 ? 'text-red-500' : ($days <= 15 ? 'text-amber-600' : 'text-slate-500');
                @endphp
                <div class="px-5 py-3 flex items-center justify-between">
                    <div class="min-w-0 pr-2">
                        <p class="text-sm font-medium text-slate-800 truncate">{{ $opp->title }}</p>
                        <p class="text-xs text-slate-400">{{ $opp->linked_name }} · {{ $opp->assignedTo?->name ?? '—' }}</p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-sm font-bold text-indigo-700">${{ number_format($opp->estimated_value, 0) }}</p>
                        <p class="text-xs {{ $urgentColor }}">{{ abs($days) }}d {{ $days >= 0 ? '' : 'vencida' }}</p>
                    </div>
                </div>
                @empty
                <p class="px-5 py-8 text-sm text-slate-400 text-center">Sin oportunidades próximas a cerrar</p>
                @endforelse
            </div>
        </div>

    </div>
</div>

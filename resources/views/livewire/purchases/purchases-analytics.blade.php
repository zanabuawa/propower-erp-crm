<div>
    <x-page-header title="Analytics de Compras" description="Gasto por proveedor, categoría y evaluación">
        <x-slot:actions>
            <div class="flex gap-2">
                @foreach(['month' => 'Mes', 'quarter' => 'Trimestre', 'year' => 'Año'] as $val => $lbl)
                <button wire:click="$set('period','{{ $val }}')"
                        class="px-3 py-1.5 text-xs rounded-lg border transition {{ $period === $val ? 'bg-indigo-600 text-white border-indigo-600' : 'border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                    {{ $lbl }}
                </button>
                @endforeach
            </div>
            <a wire:navigate href="{{ route('purchases.report') }}"
               class="px-3 py-2 text-sm border border-slate-200 text-slate-600 rounded-lg hover:bg-slate-50 transition">
                Reporte
            </a>
        </x-slot:actions>
    </x-page-header>

    <x-alert />

    {{-- Filtros fecha --}}
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
    </div>

    {{-- KPIs ──────────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
        <div class="bg-white rounded-xl border border-slate-200 p-4 sm:col-span-2">
            <p class="text-[10px] text-slate-400 uppercase tracking-wider mb-1">Gasto total</p>
            <p class="text-2xl font-bold text-slate-800">${{ number_format($totalSpend, 0) }}</p>
            @if($spendGrowth !== null)
            <p class="text-xs mt-1 {{ $spendGrowth <= 0 ? 'text-emerald-600' : 'text-red-500' }}">
                {{ $spendGrowth >= 0 ? '▲' : '▼' }} {{ number_format(abs($spendGrowth), 1) }}% vs período anterior
            </p>
            @endif
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 text-center">
            <p class="text-[10px] text-slate-400 uppercase tracking-wider mb-1">Órdenes</p>
            <p class="text-2xl font-bold text-indigo-600">{{ $orderCount }}</p>
            <p class="text-xs text-slate-400 mt-0.5">Prom. ${{ number_format($avgOrder, 0) }}</p>
        </div>
        <div class="bg-white rounded-xl border {{ $pendingPay > 0 ? 'border-amber-200 bg-amber-50' : 'border-slate-200' }} p-4 text-center">
            <p class="text-[10px] text-slate-400 uppercase tracking-wider mb-1">Pendiente pagar</p>
            <p class="text-2xl font-bold text-amber-600">${{ number_format($pendingPay, 0) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

        {{-- Por proveedor ─────────────────────────────────────────────── --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Gasto por proveedor (top 10)</h3>
            </div>
            <div class="p-5 space-y-3">
                @forelse($bySupplier as $s)
                <div>
                    <div class="flex justify-between text-xs mb-1">
                        <span class="text-slate-700 font-medium truncate pr-2">{{ $s->name }}</span>
                        <div class="flex gap-3 flex-shrink-0">
                            <span class="text-slate-400">{{ $s->orders }} OC</span>
                            <span class="font-bold text-slate-800">${{ number_format($s->total, 0) }}</span>
                        </div>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2">
                        <div class="bg-indigo-400 h-2 rounded-full" style="width: {{ round($s->total/$maxSupplier*100) }}%"></div>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-0.5">Prom. por orden: ${{ number_format($s->avg_order, 0) }}</p>
                </div>
                @empty
                <p class="text-sm text-slate-400 text-center py-6">Sin órdenes en el período</p>
                @endforelse
            </div>
        </div>

        {{-- Por categoría ─────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Por categoría</h3>
            </div>
            <div class="p-5 space-y-3">
                @forelse($byCategory as $c)
                <div>
                    <div class="flex justify-between text-xs mb-1">
                        <span class="text-slate-600 truncate pr-2">{{ $c->name }}</span>
                        <span class="font-bold text-slate-800">${{ number_format($c->total, 0) }}</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2">
                        <div class="bg-amber-400 h-2 rounded-full" style="width: {{ round($c->total/$maxCategory*100) }}%"></div>
                    </div>
                </div>
                @empty
                <p class="text-sm text-slate-400 text-center py-4">Sin datos de categorías</p>
                @endforelse
            </div>
        </div>

    </div>

    {{-- Tendencia mensual + Evaluación proveedores ────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Tendencia gasto --}}
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Tendencia de gasto (12 meses)</h3>
            </div>
            <div class="p-5">
                <div class="flex items-end gap-1 h-28">
                    @foreach($spendTrend as $m)
                    <div class="flex-1 flex flex-col items-center gap-1">
                        <div class="w-full bg-indigo-400 rounded-t"
                             style="height: {{ $maxTrend > 0 ? round($m['value']/$maxTrend*100) : 0 }}%"></div>
                        <span class="text-[8px] text-slate-400 truncate w-full text-center">{{ $m['label'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Evaluación de proveedores --}}
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Evaluación de proveedores</h3>
            </div>
            @forelse($supplierScores as $s)
            <div class="px-5 py-3 border-b border-slate-100 last:border-0 hover:bg-slate-50/50">
                <div class="flex justify-between items-center mb-1.5">
                    <span class="text-sm font-medium text-slate-800 truncate pr-2">{{ $s->name }}</span>
                    <span class="text-sm font-bold {{ $s->overall >= 4 ? 'text-emerald-600' : ($s->overall >= 2.5 ? 'text-amber-600' : 'text-red-500') }}">
                        {{ $s->overall }}/5
                    </span>
                </div>
                <div class="grid grid-cols-4 gap-1">
                    @foreach(['price' => 'Precio', 'quality' => 'Calidad', 'delivery' => 'Entrega', 'compliance' => 'Cumpl.'] as $key => $lbl)
                    <div class="text-center">
                        <div class="w-full bg-slate-100 rounded-full h-1.5 mb-0.5">
                            <div class="h-1.5 rounded-full {{ $s->$key >= 4 ? 'bg-emerald-400' : ($s->$key >= 2.5 ? 'bg-amber-400' : 'bg-red-400') }}"
                                 style="width: {{ $s->$key * 20 }}%"></div>
                        </div>
                        <span class="text-[9px] text-slate-400">{{ $lbl }} {{ $s->$key }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @empty
            <p class="px-5 py-8 text-sm text-slate-400 text-center">Sin evaluaciones registradas</p>
            @endforelse
        </div>

    </div>
</div>

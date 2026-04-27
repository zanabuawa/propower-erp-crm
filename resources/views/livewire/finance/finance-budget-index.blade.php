<div class="min-h-screen bg-slate-50/50 -m-4 sm:-m-6 lg:-m-8">
    {{-- STICKY HEADER --}}
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 max-w-full mx-auto">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-9 h-9 rounded-xl bg-emerald-600 flex items-center justify-center text-white shrink-0 shadow-lg shadow-emerald-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl font-bold text-slate-800 truncate">Presupuestos</h1>
                    <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">Control presupuestal por período</p>
                </div>
            </div>
            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                @can('create finance')
                <a wire:navigate href="{{ route('finance.budgets.create') }}"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:scale-[1.02] active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Nuevo presupuesto</span>
                </a>
                @endcan
            </div>
        </div>
    </div>

    <div class="max-w-full mx-auto p-4 sm:p-6 lg:p-8 space-y-6">
        @if(session('success'))
            <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl animate-in fade-in slide-in-from-top-4 duration-300">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-sm font-semibold">{{ session('success') }}</p>
            </div>
        @endif

        {{-- Filters --}}
        <div class="bg-white p-4 rounded-3xl border border-slate-200/60 shadow-sm flex flex-wrap gap-4 items-center">
            <div class="flex-1 min-w-[280px] relative group">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-indigo-500 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar por nombre..."
                    class="w-full pl-11 pr-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 text-sm">
            </div>
            <select wire:model.live="filterPeriodType"
                class="px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 text-sm font-bold text-slate-600">
                <option value="">Todos los períodos</option>
                <option value="mensual">Mensual</option>
                <option value="trimestral">Trimestral</option>
                <option value="semestral">Semestral</option>
                <option value="anual">Anual</option>
            </select>
            <select wire:model.live="filterYear"
                class="px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 text-sm font-bold text-slate-600">
                <option value="">Todos los años</option>
                @foreach($years as $year)
                    <option value="{{ $year }}">{{ $year }}</option>
                @endforeach
            </select>
            <select wire:model.live="filterStatus"
                class="px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 text-sm font-bold text-slate-600">
                <option value="">Todos los estados</option>
                <option value="borrador">Borrador</option>
                <option value="aprobado">Aprobado</option>
                <option value="cerrado">Cerrado</option>
            </select>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100">
                            <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Nombre</th>
                            <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest hidden md:table-cell">Período</th>
                            <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest hidden md:table-cell">Proyecto</th>
                            <th class="px-6 py-4 text-right text-[10px] font-bold text-slate-400 uppercase tracking-widest">Planeado</th>
                            <th class="px-6 py-4 text-right text-[10px] font-bold text-slate-400 uppercase tracking-widest">Real</th>
                            <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Ejecución</th>
                            <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Estado</th>
                            <th class="px-6 py-4 text-right text-[10px] font-bold text-slate-400 uppercase tracking-widest">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($budgets as $budget)
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-slate-700">{{ $budget->name }}</p>
                            </td>
                            <td class="px-6 py-4 hidden md:table-cell">
                                <p class="text-sm text-slate-500 capitalize">
                                    {{ $budget->period_type }}
                                    @if($budget->period_number) #{{ $budget->period_number }}@endif
                                    {{ $budget->year }}
                                </p>
                            </td>
                            <td class="px-6 py-4 hidden md:table-cell">
                                <p class="text-sm text-slate-500">{{ $budget->project?->name ?? '—' }}</p>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <p class="text-sm font-mono text-slate-700">{{ $budget->currency }} {{ number_format($budget->amount_planned, 2) }}</p>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <p class="text-sm font-mono {{ $budget->amount_actual > $budget->amount_planned ? 'text-red-600 font-bold' : 'text-slate-700' }}">
                                    {{ $budget->currency }} {{ number_format($budget->amount_actual, 2) }}
                                </p>
                            </td>
                            <td class="px-6 py-4">
                                @php $pct = $budget->execution_percent_attribute ?? 0; @endphp
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 bg-slate-100 rounded-full h-1.5 min-w-[60px]">
                                        <div class="h-1.5 rounded-full {{ $pct > 100 ? 'bg-red-500' : 'bg-emerald-500' }}" style="width: {{ min($pct, 100) }}%"></div>
                                    </div>
                                    <span class="text-[10px] font-bold text-slate-500 w-10 text-right">{{ $pct }}%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $sc = match($budget->status) {
                                        'aprobado' => 'bg-emerald-50 text-emerald-600 border border-emerald-200',
                                        'cerrado' => 'bg-blue-50 text-blue-600 border border-blue-200',
                                        default => 'bg-slate-100 text-slate-400 border border-slate-200',
                                    };
                                @endphp
                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider {{ $sc }}">
                                    {{ $budget->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    @can('edit finance')
                                    <a wire:navigate href="{{ route('finance.budgets.edit', $budget) }}"
                                        class="p-2 rounded-xl text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 hover:shadow-sm transition-all" title="Editar">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="w-10 h-10 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                    <p class="text-slate-400 text-sm font-medium">No se encontraron presupuestos.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($budgets->hasPages())
                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/30">
                    {{ $budgets->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<div class="min-h-screen bg-slate-50/50 -m-4 lg:-m-6">
    {{-- ── STICKY HEADER ────────────────────────────────────────────────── --}}
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 max-w-full mx-auto">
            <div class="flex items-center gap-3 min-w-0">
                <a wire:navigate href="{{ route('projects.show', $project) }}" 
                   class="group flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-100 hover:shadow-sm transition-all duration-200">
                    <svg class="w-5 h-5 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl font-bold text-slate-800 truncate">Presupuesto — {{ $project->name }}</h1>
                    <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">{{ $project->code }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <button type="button" wire:click="openVersionCreate"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:scale-[1.02] active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Nueva Versión</span>
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-full mx-auto p-4 sm:p-6 lg:p-8 space-y-8">
        <x-alert />

        <div class="grid grid-cols-1 xl:grid-cols-4 gap-8">
            {{-- ── COLUMNA LATERAL: VERSIONES ────────────────────────────────── --}}
            <div class="xl:col-span-1 space-y-6">
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                        <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Versiones del Proyecto</h3>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @forelse($versions as $v)
                            <div class="p-5 cursor-pointer transition-all duration-200 {{ $activeVersionId == $v->id ? 'bg-indigo-50/50 border-l-4 border-indigo-600' : 'hover:bg-slate-50 border-l-4 border-transparent' }}"
                                wire:click="$set('activeVersionId', {{ $v->id }})">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-sm font-black text-slate-800 truncate">v{{ $v->version }} · {{ $v->name }}</p>
                                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mt-0.5">{{ $v->lines_count }} partidas registradas</p>
                                    </div>
                                    @php
                                        $vStatus = match($v->status) {
                                            'vigente'   => ['bg-emerald-100 text-emerald-700', 'Vigente'],
                                            'aprobado'  => ['bg-indigo-100 text-indigo-700',   'Aprobado'],
                                            'historico' => ['bg-slate-100 text-slate-500',     'Histórico'],
                                            default     => ['bg-amber-100 text-amber-700',     'Borrador'],
                                        };
                                    @endphp
                                    <span class="inline-flex px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-wider {{ $vStatus[0] }}">
                                        {{ $vStatus[1] }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-3 mt-4">
                                    @if($v->status !== 'vigente')
                                        <button wire:click.stop="activateVersion({{ $v->id }})"
                                            class="text-[9px] font-black uppercase tracking-widest text-indigo-600 hover:text-indigo-800">Activar</button>
                                    @endif
                                    <button wire:click.stop="duplicateVersion({{ $v->id }})"
                                        class="text-[9px] font-black uppercase tracking-widest text-slate-400 hover:text-slate-600">Duplicar</button>
                                    <button wire:click.stop="openVersionEdit({{ $v->id }})"
                                        class="text-[9px] font-black uppercase tracking-widest text-slate-400 hover:text-slate-600">Editar</button>
                                </div>
                            </div>
                        @empty
                            <div class="p-10 text-center">
                                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest leading-relaxed">Sin versiones.<br>Crea la primera para comenzar.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="bg-indigo-600 rounded-[2.5rem] p-8 text-white shadow-xl shadow-indigo-100 relative overflow-hidden group">
                    <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-white/10 rounded-full blur-3xl transition-transform group-hover:scale-150 duration-700"></div>
                    <div class="relative z-10 space-y-4">
                        <h4 class="text-sm font-black uppercase tracking-[0.2em] opacity-80 text-indigo-100">Control de Versiones</h4>
                        <p class="text-xs font-medium leading-relaxed">
                            Manten historial de cambios. Solo una versión puede estar <strong>Vigente</strong> para el cálculo de rentabilidad del proyecto.
                        </p>
                    </div>
                </div>
            </div>

            {{-- ── COLUMNA PRINCIPAL: DETALLE DE VERSIÓN ─────────────────────── --}}
            <div class="xl:col-span-3 space-y-8">
                @if($activeVersion)
                    <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden animate-in fade-in slide-in-from-right-4 duration-300">
                        <div class="px-6 py-5 lg:px-8 lg:py-6 border-b border-slate-100 bg-white flex flex-col sm:flex-row sm:items-center justify-between gap-6">
                            <div>
                                <div class="flex items-center gap-3">
                                    <h3 class="text-lg font-black text-slate-800">v{{ $activeVersion->version }} — {{ $activeVersion->name }}</h3>
                                    <span class="px-2.5 py-1 rounded-xl bg-slate-100 text-slate-600 text-[10px] font-black uppercase tracking-wider">Detalle de Inversión</span>
                                </div>
                                @if($activeVersion->description)
                                    <p class="text-xs font-medium text-slate-400 mt-1">{{ $activeVersion->description }}</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-6">
                                <div class="text-right">
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Presupuestado</p>
                                    <p class="text-2xl font-black text-slate-900 tracking-tighter">{{ $project->currency }} ${{ number_format($activeVersion->lines->sum('budgeted_amount'), 2) }}</p>
                                </div>
                                <button wire:click="openLineCreate"
                                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-900 text-white rounded-xl text-xs font-black uppercase tracking-widest hover:bg-slate-800 transition-all shadow-md">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                    Línea
                                </button>
                            </div>
                        </div>

                        @php
                            $linesByCategory = $activeVersion->lines->groupBy('category');
                            $categories = \App\Models\ProjectBudgetLine::$categoryLabels;
                        @endphp

                        <div class="divide-y divide-slate-100">
                            @foreach($categories as $catKey => $catLabel)
                                @if($linesByCategory->has($catKey))
                                    <div>
                                        <div class="px-6 py-3 bg-slate-50/50 flex items-center gap-3">
                                            <div class="w-1.5 h-4 rounded-full bg-indigo-500"></div>
                                            <p class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">{{ $catLabel }}</p>
                                        </div>
                                        <div class="overflow-x-auto">
                                            <table class="w-full text-sm">
                                                <tbody class="divide-y divide-slate-100/60">
                                                    @foreach($linesByCategory[$catKey] as $line)
                                                        <tr class="hover:bg-slate-50/30 transition-colors group">
                                                            <td class="px-8 py-4">
                                                                <p class="font-bold text-slate-800">{{ $line->concept }}</p>
                                                                @if($line->description)
                                                                    <p class="text-[10px] font-medium text-slate-400 mt-0.5 line-clamp-1 italic">{{ $line->description }}</p>
                                                                @endif
                                                            </td>
                                                            <td class="px-4 py-4 text-center">
                                                                <span class="inline-flex px-2 py-1 rounded-lg bg-slate-100 text-slate-600 text-[10px] font-black uppercase tracking-tighter">
                                                                    {{ $line->quantity }} {{ $line->unit }}
                                                                </span>
                                                            </td>
                                                            <td class="px-4 py-4 text-right">
                                                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block">Unitario</span>
                                                                <span class="text-xs font-black text-slate-600">${{ number_format($line->unit_cost, 2) }}</span>
                                                            </td>
                                                            <td class="px-4 py-4 text-right">
                                                                <span class="text-[10px] font-bold text-indigo-400 uppercase tracking-widest block">Importe</span>
                                                                <span class="text-sm font-black text-slate-900">${{ number_format($line->budgeted_amount, 2) }}</span>
                                                            </td>
                                                            <td class="px-8 py-4 text-right w-24">
                                                                <div class="flex items-center justify-end gap-2 transition-all duration-200">
                                                                    <button wire:click="openLineEdit({{ $line->id }})" class="p-2 bg-white rounded-lg text-slate-400 hover:text-indigo-600 hover:shadow-sm border border-slate-100 transition-all">
                                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                                    </button>
                                                                    <button wire:click="deleteLine({{ $line->id }})" wire:confirm="¿Eliminar esta línea de presupuesto?" class="p-2 bg-white rounded-lg text-slate-400 hover:text-rose-600 hover:shadow-sm border border-slate-100 transition-all">
                                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    {{-- Comparativo --}}
                    @if($comparison)
                        <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden animate-in fade-in zoom-in-95 duration-500">
                            <div class="px-8 py-5 border-b border-slate-100 bg-slate-900 text-white flex items-center justify-between">
                                <div>
                                    <h3 class="text-sm font-black uppercase tracking-[0.2em] text-indigo-300">Análisis Presupuesto vs. Ejecución</h3>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase mt-0.5">Control financiero en tiempo real</p>
                                </div>
                                <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="border-b border-slate-100 bg-slate-50/50">
                                            <th class="text-left px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Categoría / Concepto</th>
                                            <th class="text-right px-4 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Planificado</th>
                                            <th class="text-right px-4 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Ejercido</th>
                                            <th class="text-right px-4 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Variación</th>
                                            <th class="text-left px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest hidden sm:table-cell">Consumo %</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach($comparison as $row)
                                            @php
                                                $over = $row['variance'] < 0;
                                                $pct  = $row['pct'];
                                            @endphp
                                            <tr class="{{ $over ? 'bg-rose-50/30' : '' }} hover:bg-slate-50/50 transition-colors">
                                                <td class="px-8 py-4 font-bold text-slate-700 uppercase text-[11px] tracking-wider">{{ $row['label'] }}</td>
                                                <td class="px-4 py-4 text-right text-slate-500 font-mono">${{ number_format($row['budgeted'], 2) }}</td>
                                                <td class="px-4 py-4 text-right font-black {{ $over ? 'text-rose-600' : 'text-slate-800' }}">${{ number_format($row['executed'], 2) }}</td>
                                                <td class="px-4 py-4 text-right font-black {{ $over ? 'text-rose-600' : 'text-emerald-600' }}">
                                                    {{ $over ? '-' : '+' }}${{ number_format(abs($row['variance']), 2) }}
                                                </td>
                                                <td class="px-8 py-4 hidden sm:table-cell">
                                                    @if($pct !== null)
                                                        <div class="flex items-center gap-3">
                                                            <div class="flex-1 bg-slate-100 rounded-full h-2 shadow-inner">
                                                                <div class="{{ $pct > 100 ? 'bg-rose-500 animate-pulse' : ($pct > 80 ? 'bg-amber-400' : 'bg-emerald-400') }} h-2 rounded-full transition-all duration-1000"
                                                                     style="width:{{ min(100, $pct) }}%"></div>
                                                            </div>
                                                            <span class="text-[10px] font-black {{ $pct > 100 ? 'text-rose-600' : 'text-slate-600' }}">{{ $pct }}%</span>
                                                        </div>
                                                    @else
                                                        <span class="text-slate-300 font-bold italic text-[10px] uppercase">N/A</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach

                                        @php
                                            $totalBudgeted = collect($comparison)->sum('budgeted');
                                            $totalExecuted = collect($comparison)->sum('executed');
                                            $totalVariance = $totalBudgeted - $totalExecuted;
                                            $totalPct      = $totalBudgeted > 0 ? round(($totalExecuted / $totalBudgeted) * 100, 1) : null;
                                        @endphp
                                        <tr class="bg-slate-900 border-t-2 border-slate-700">
                                            <td class="px-8 py-6 text-[10px] font-black text-indigo-400 uppercase tracking-[0.2em]">Resumen Global</td>
                                            <td class="px-4 py-6 text-right text-white font-black text-sm">${{ number_format($totalBudgeted, 2) }}</td>
                                            <td class="px-4 py-6 text-right font-black text-lg {{ $totalVariance < 0 ? 'text-rose-400' : 'text-emerald-400' }}">${{ number_format($totalExecuted, 2) }}</td>
                                            <td class="px-4 py-6 text-right font-black text-sm {{ $totalVariance < 0 ? 'text-rose-400' : 'text-emerald-400' }}">
                                                {{ $totalVariance < 0 ? '-' : '+' }}${{ number_format(abs($totalVariance), 2) }}
                                            </td>
                                            <td class="px-8 py-6 hidden sm:table-cell text-xs font-black {{ $totalPct > 100 ? 'text-rose-400' : 'text-slate-400' }} uppercase tracking-widest">
                                                Ejecución: {{ $totalPct !== null ? $totalPct.'%' : '—' }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="bg-white rounded-[2.5rem] border border-slate-200 py-24 text-center">
                        <div class="w-20 h-20 rounded-full bg-slate-50 flex items-center justify-center text-slate-200 mx-auto mb-6 border-2 border-dashed border-slate-200">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        </div>
                        <h3 class="text-xl font-black text-slate-800 tracking-tight">Sin Versión Seleccionada</h3>
                        <p class="text-sm font-medium text-slate-400 mt-2 max-w-sm mx-auto">Selecciona una versión del presupuesto en el panel izquierdo para visualizar el desglose de costos o crear comparativos.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ── MODAL: VERSIÓN ─────────────────────────────────────────────── --}}
    @if($showVersionModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" wire:click="$set('showVersionModal', false)"></div>
            <div class="relative bg-white rounded-[2.5rem] shadow-2xl p-10 w-full max-w-md mx-auto border border-slate-200 animate-in zoom-in-95 duration-200">
                <div class="w-16 h-16 rounded-3xl bg-indigo-50 flex items-center justify-center text-indigo-600 mb-6 mx-auto">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
                <h3 class="text-2xl font-black text-slate-800 text-center tracking-tight mb-2">{{ $editingVersionId ? 'Editar Versión' : 'Nueva Versión' }}</h3>
                <p class="text-sm text-slate-500 text-center font-medium leading-relaxed mb-8">Define una nueva iteración para el presupuesto del proyecto.</p>
                
                <div class="space-y-6">
                    <div class="space-y-2">
                        <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest block">Nombre de Referencia *</label>
                        <input wire:model="versionName" type="text" placeholder="Ej: Presupuesto Base, Revisión A..."
                               class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 transition-all">
                        @error('versionName') <p class="text-[10px] text-rose-500 font-bold">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-2">
                        <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest block">Descripción</label>
                        <textarea wire:model="versionDescription" rows="2"
                                  class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-indigo-500/10 resize-none"></textarea>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mt-10">
                    <button wire:click="$set('showVersionModal', false)"
                        class="py-4 bg-slate-50 text-slate-500 rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-slate-100 transition-all">Cancelar</button>
                    <button wire:click="saveVersion"
                        class="py-4 bg-indigo-600 text-white rounded-2xl text-xs font-black uppercase tracking-widest shadow-lg shadow-indigo-500/25 hover:bg-indigo-700 hover:scale-[1.02] transition-all">Guardar</button>
                </div>
            </div>
        </div>
    @endif

    {{-- ── MODAL: LÍNEA DE PRESUPUESTO ────────────────────────────────── --}}
    @if($showLineModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" wire:click="$set('showLineModal', false)"></div>
            <div class="relative bg-white rounded-[2.5rem] shadow-2xl p-10 w-full max-w-lg mx-auto border border-slate-200 animate-in zoom-in-95 duration-200">
                <div class="w-16 h-16 rounded-3xl bg-teal-50 flex items-center justify-center text-teal-600 mb-6 mx-auto">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                </div>
                <h3 class="text-2xl font-black text-slate-800 text-center tracking-tight mb-2">{{ $editingLineId ? 'Editar Línea' : 'Nueva Línea' }}</h3>
                <p class="text-sm text-slate-500 text-center font-medium leading-relaxed mb-8">Asigna costos por concepto a la versión activa.</p>
                
                <div class="space-y-6">
                    <div class="grid grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest block">Categoría *</label>
                            <select wire:model.live="lineCategory"
                                    class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-teal-500/10 appearance-none cursor-pointer">
                                @foreach(\App\Models\ProjectBudgetLine::$categoryLabels as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest block">Unidad de Medida</label>
                            <input wire:model="lineUnit" type="text" placeholder="m², pza, hr..."
                                   class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-teal-500/10 uppercase">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest block">Concepto / Partida *</label>
                        <input wire:model="lineConcept" type="text" placeholder="¿Qué se está presupuestando?"
                               class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-teal-500/10">
                        @error('lineConcept') <p class="text-[10px] text-rose-500 font-bold">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest block">Cantidad</label>
                            <input wire:model.live="lineQuantity" type="number" step="0.0001" min="0"
                                   class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-black text-slate-800 focus:ring-4 focus:ring-teal-500/10 text-center">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest block">Costo Unitario ($)</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">$</span>
                                <input wire:model.live="lineUnitCost" type="number" step="0.01" min="0"
                                       class="w-full bg-slate-50 border-none rounded-2xl pl-8 pr-4 py-4 text-sm font-black text-teal-600 focus:ring-4 focus:ring-teal-500/10 text-right">
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-900 rounded-3xl p-6 flex justify-between items-center shadow-lg shadow-slate-200">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Total de Partida:</span>
                        <span class="text-xl font-black text-white tracking-tighter">
                            ${{ number_format((float)$lineQuantity * (float)$lineUnitCost, 2) }}
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mt-10">
                    <button wire:click="$set('showLineModal', false)"
                        class="py-4 bg-slate-50 text-slate-500 rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-slate-100 transition-all">Cancelar</button>
                    <button wire:click="saveLine"
                        class="py-4 bg-teal-600 text-white rounded-2xl text-xs font-black uppercase tracking-widest shadow-lg shadow-teal-500/25 hover:bg-teal-700 hover:scale-[1.02] transition-all">Guardar Línea</button>
                </div>
            </div>
        </div>
    @endif
</div>

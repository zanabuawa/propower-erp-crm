<div class="min-h-screen bg-slate-50/50 -m-4 lg:-m-6">
    {{-- ── STICKY HEADER ────────────────────────────────────────────────── --}}
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 max-w-full mx-auto">
            <div class="flex items-center gap-3 min-w-0">
                <a wire:navigate href="{{ route('hr.payrolls.index') }}" 
                   class="group flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-100 hover:shadow-sm transition-all duration-200">
                    <svg class="w-5 h-5 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl font-bold text-slate-800 truncate">Nueva Nómina</h1>
                    <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">
                        @if($period_start && $period_end)
                            Periodo: {{ \Carbon\Carbon::parse($period_start)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($period_end)->format('d/m/Y') }}
                        @else
                            Configuración de cálculo y dispersión
                        @endif
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <a wire:navigate href="{{ route('hr.payrolls.index') }}"
                    class="hidden sm:inline-flex px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 transition-colors">
                    Cancelar
                </a>
                
                @if($calculated && !empty($items))
                    <button type="button" wire:click="calculate"
                        class="hidden md:inline-flex items-center gap-2 px-4 py-2 text-sm font-bold text-indigo-600 hover:text-indigo-700 transition-colors">
                        <svg wire:loading.remove wire:target="calculate" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span wire:loading wire:target="calculate" class="w-4 h-4 border-2 border-indigo-600/30 border-t-indigo-600 rounded-full animate-spin"></span>
                        Recalcular
                    </button>
                    <button type="button" wire:click="save"
                        class="inline-flex items-center gap-2 bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all duration-200 shadow-lg shadow-emerald-500/25 hover:shadow-emerald-500/40 hover:scale-[1.02] active:scale-[0.98]">
                        <svg wire:loading.remove wire:target="save" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <span wire:loading wire:target="save" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                        <span>Guardar Nómina</span>
                    </button>
                @else
                    <button type="button" wire:click="calculate"
                        class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:scale-[1.02] active:scale-[0.98]">
                        <svg wire:loading.remove wire:target="calculate" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 7l-.75 3m0 0l.75 3m-.75-3h3.5m-8.5 8.5l.75-3m0 0l-.75-3m.75 3h-3.5"/></svg>
                        <span wire:loading wire:target="calculate" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                        <span>Calcular Nómina</span>
                    </button>
                @endif
            </div>
        </div>
    </div>

    <div class="max-w-full mx-auto p-4 sm:p-6 lg:p-8 space-y-8">
        @if(session('success'))
            <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl animate-in fade-in slide-in-from-top-4 duration-300">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm font-semibold">{{ session('success') }}</p>
            </div>
        @endif

        @error('items')
            <div class="p-4 bg-red-50 border border-red-100 text-red-700 rounded-2xl flex items-center gap-3">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-sm font-bold uppercase tracking-wide">{{ $message }}</p>
            </div>
        @enderror

        {{-- ── CONFIGURACIÓN DEL PERIODO ────────────────────────────────────── --}}
        <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/30 flex items-center justify-between">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Configuración del Periodo</h3>
                <span class="px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-600 text-[10px] font-bold uppercase tracking-wider">Parámetros de Cálculo</span>
            </div>
            <div class="p-6 lg:p-8">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
                    <div class="space-y-2">
                        <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Tipo de nómina</label>
                        <select wire:model.live="period_type"
                            class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-bold text-slate-700">
                            @foreach(\App\Models\HrPayroll::PERIOD_TYPES as $k => $v)
                                <option value="{{ $k }}">{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Empleado</label>
                        <select wire:model.live="employee_id"
                            class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-bold text-slate-700">
                            <option value="">Todos los empleados</option>
                            @foreach($employeeOptions as $employee)
                                <option value="{{ $employee->id }}">
                                    {{ $employee->employee_number ? '#'.$employee->employee_number.' · ' : '' }}{{ $employee->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Fecha de inicio *</label>
                        <input wire:model="period_start" type="date"
                            class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Fecha de fin *</label>
                        <input wire:model="period_end" type="date"
                            class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Notas / Observaciones</label>
                        <input wire:model="notes" type="text" placeholder="Ej: Bonos de puntualidad incl."
                            class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200">
                    </div>
                </div>
            </div>
        </div>

        @if($calculated && !empty($items))
            {{-- ── RESUMEN DE CÁLCULO ────────────────────────────────────────── --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 animate-in fade-in slide-in-from-top-4 duration-500">
                <div class="bg-white p-6 rounded-3xl border border-slate-200/60 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Personal</p>
                        <p class="text-xl font-black text-slate-800">{{ $totals['employees'] }} emp.</p>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-3xl border border-slate-200/60 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600 shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Bruto</p>
                        <p class="text-xl font-black text-slate-800 font-mono">${{ number_format($totals['gross'], 2) }}</p>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-3xl border border-slate-200/60 shadow-sm flex items-center gap-4 border-l-4 border-l-red-500">
                    <div class="w-12 h-12 rounded-2xl bg-red-50 flex items-center justify-center text-red-600 shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Deducciones</p>
                        <p class="text-xl font-black text-red-600 font-mono">${{ number_format($totals['deductions'], 2) }}</p>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-3xl border border-slate-200/60 shadow-sm flex items-center gap-4 border-l-4 border-l-emerald-500">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600 shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Neto a Pagar</p>
                        <p class="text-xl font-black text-emerald-600 font-mono">${{ number_format($totals['net'], 2) }}</p>
                    </div>
                </div>
            </div>

            {{-- ── TABLA DE EMPLEADOS ────────────────────────────────────────── --}}
            <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden animate-in fade-in slide-in-from-bottom-4 duration-700">
                <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/30">
                    <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Detalle Individual</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-slate-50/50 border-b border-slate-100">
                                <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest sticky left-0 bg-slate-50">Empleado</th>
                                <th class="px-4 py-4 text-center text-[10px] font-bold text-slate-400 uppercase tracking-widest">Horas</th>
                                <th class="px-4 py-4 text-right text-[10px] font-bold text-slate-400 uppercase tracking-widest">Sal. Diario</th>
                                <th class="px-4 py-4 text-center text-[10px] font-bold text-slate-400 uppercase tracking-widest">Hrs Extra</th>
                                <th class="px-4 py-4 text-right text-[10px] font-bold text-slate-400 uppercase tracking-widest">Percepciones</th>
                                <th class="px-4 py-4 text-right text-[10px] font-bold text-indigo-500 uppercase tracking-widest">Bruto</th>
                                <th class="px-4 py-4 text-right text-[10px] font-bold text-red-500 uppercase tracking-widest">ISR</th>
                                <th class="px-4 py-4 text-right text-[10px] font-bold text-red-500 uppercase tracking-widest">IMSS</th>
                                <th class="px-4 py-4 text-right text-[10px] font-bold text-red-500 uppercase tracking-widest">Préstamos</th>
                                <th class="px-6 py-4 text-right text-[10px] font-bold text-emerald-600 uppercase tracking-widest">Neto</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($items as $empId => $item)
                                <tr class="hover:bg-slate-50/50 transition-colors group">
                                    <td class="px-6 py-4 sticky left-0 bg-white group-hover:bg-slate-50 transition-colors">
                                        <p class="text-sm font-bold text-slate-700">{{ $item['full_name'] }}</p>
                                        <p class="text-[10px] font-medium text-slate-400 uppercase tracking-tight">{{ $item['department'] }} · {{ $item['position'] }}</p>
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <input wire:change="updateItemField({{ $empId }}, 'worked_hours', $event.target.value)"
                                            type="number" step="0.5" min="0" value="{{ $item['worked_hours'] ?? round(($item['days_worked'] ?? 0) * 8, 2) }}"
                                            class="w-16 text-center text-xs font-bold px-2 py-1.5 rounded-xl border-slate-200 bg-slate-50/50 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all">
                                        <p class="mt-1 text-[9px] font-bold uppercase tracking-tight text-slate-300">
                                            {{ number_format((float) ($item['days_worked'] ?? 0), 2) }} dias eq.
                                        </p>
                                    </td>
                                    <td class="px-4 py-4 text-right text-xs font-mono font-bold text-slate-600">
                                        ${{ number_format($item['daily_salary'], 2) }}
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <input wire:change="updateItemField({{ $empId }}, 'overtime_hours', $event.target.value)"
                                            type="number" step="0.5" min="0" value="{{ $item['overtime_hours'] }}"
                                            class="w-16 text-center text-xs font-bold px-2 py-1.5 rounded-xl border-slate-200 bg-slate-50/50 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all">
                                    </td>
                                    <td class="px-4 py-4 text-right">
                                        <input wire:change="updateItemField({{ $empId }}, 'bonus_amount', $event.target.value)"
                                            type="number" step="0.01" min="0" value="{{ $item['bonus_amount'] }}"
                                            class="w-20 text-right text-xs font-bold px-2 py-1.5 rounded-xl border-slate-200 bg-slate-50/50 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all">
                                    </td>
                                    <td class="px-4 py-4 text-right text-sm font-black text-indigo-600 font-mono">
                                        ${{ number_format($item['gross_salary'], 2) }}
                                    </td>
                                    <td class="px-4 py-4 text-right">
                                        <input wire:change="updateItemField({{ $empId }}, 'ispt', $event.target.value)"
                                            type="number" step="0.01" min="0" value="{{ $item['ispt'] }}"
                                            class="w-20 text-right text-xs font-bold px-2 py-1.5 rounded-xl border-red-200 bg-red-50/30 focus:bg-white focus:border-red-500 focus:ring-4 focus:ring-red-500/5 transition-all">
                                    </td>
                                    <td class="px-4 py-4 text-right">
                                        <input wire:change="updateItemField({{ $empId }}, 'imss_employee', $event.target.value)"
                                            type="number" step="0.01" min="0" value="{{ $item['imss_employee'] }}"
                                            class="w-20 text-right text-xs font-bold px-2 py-1.5 rounded-xl border-red-200 bg-red-50/30 focus:bg-white focus:border-red-500 focus:ring-4 focus:ring-red-500/5 transition-all">
                                    </td>
                                    <td class="px-4 py-4 text-right">
                                        <input wire:change="updateItemField({{ $empId }}, 'loan_payment', $event.target.value)"
                                            type="number" step="0.01" min="0" value="{{ $item['loan_payment'] }}"
                                            class="w-20 text-right text-xs font-bold px-2 py-1.5 rounded-xl border-slate-200 bg-slate-50/50 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all">
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm font-black text-emerald-600 font-mono">
                                        ${{ number_format($item['net_salary'], 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @elseif($calculated)
            <div class="bg-white rounded-3xl border border-amber-200 p-8 text-center space-y-4">
                <div class="w-16 h-16 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center mx-auto">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-800">No se encontraron empleados</h3>
                    <p class="text-sm text-slate-500">No hay empleados activos bajo los criterios seleccionados para este periodo.</p>
                </div>
            </div>
        @else
            <div class="bg-indigo-600 rounded-3xl p-8 text-center text-white space-y-6 shadow-xl shadow-indigo-200">
                <div class="w-20 h-20 rounded-3xl bg-white/20 flex items-center justify-center mx-auto backdrop-blur-md">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 7l-.75 3m0 0l.75 3m-.75-3h3.5m-8.5 8.5l.75-3m0 0l-.75-3m.75 3h-3.5"/></svg>
                </div>
                <div class="max-w-md mx-auto">
                    <h3 class="text-2xl font-black italic">Calculadora de Nómina</h3>
                    <p class="text-indigo-100 mt-2">Configure el periodo de pago y haga clic en el botón superior para realizar el cálculo automático de percepciones y deducciones.</p>
                </div>
            </div>
        @endif
    </div>
</div>

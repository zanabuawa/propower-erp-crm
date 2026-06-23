<div class="min-h-screen bg-slate-50/50 -m-4 sm:-m-6 lg:-m-8">
    {{-- ── STICKY HEADER ────────────────────────────────────────────────── --}}
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 max-w-full mx-auto">
            <div class="flex items-center gap-3 min-w-0">
                <a wire:navigate href="{{ route('hr.contracts.index') }}" 
                   class="group flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-100 hover:shadow-sm transition-all duration-200">
                    <svg class="w-5 h-5 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl font-bold text-slate-800 truncate">
                        {{ $contract?->exists ? 'Editar Contrato' : 'Nuevo Contrato Laboral' }}
                    </h1>
                    <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">
                        {{ $contract?->exists ? ($contract->contract_number ?? 'Folio: '.$contract->id) : 'Formalización de relación laboral' }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <a wire:navigate href="{{ route('hr.contracts.index') }}"
                    class="hidden sm:inline-flex px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 transition-colors">
                    Cancelar
                </a>
                <button type="button" wire:click="save"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:scale-[1.02] active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>{{ $contract?->exists ? 'Guardar cambios' : 'Registrar contrato' }}</span>
                </button>
                <button type="button" wire:click="saveAndPrint"
                    class="inline-flex items-center gap-2 bg-white border border-slate-200 text-slate-600 hover:text-indigo-600 hover:border-indigo-100 text-sm font-bold px-5 py-2.5 rounded-xl transition-all duration-200 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2m-12 0h12v4H6v-4z"/>
                    </svg>
                    <span>Guardar e imprimir</span>
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8">
        <form wire:submit="save" class="grid grid-cols-1 xl:grid-cols-12 gap-6 lg:gap-8">

            {{-- ── COLUMNA IZQUIERDA: Principal (8 cols) ────────────────────── --}}
            <div class="xl:col-span-8 space-y-6 lg:space-y-8">
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/30 flex items-center justify-between gap-4">
                        <div>
                            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Plantilla del contrato</h3>
                            <p class="text-xs text-slate-400 mt-1">Selecciona una base como contrato inicial o renovacion.</p>
                        </div>
                        <a wire:navigate href="{{ route('hr.contract-templates.index') }}"
                            class="text-xs font-bold text-indigo-600 hover:text-indigo-700">
                            Administrar plantillas
                        </a>
                    </div>
                    <div class="p-6 lg:p-8">
                        <div class="grid grid-cols-1 md:grid-cols-[1fr_auto] gap-4">
                            <select wire:model.live="hr_contract_template_id"
                                class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-bold text-slate-700">
                                <option value="">Sin plantilla especifica</option>
                                @foreach($templates as $template)
                                    <option value="{{ $template->id }}">
                                        {{ $template->name }}{{ $template->duration_months ? ' - '.$template->duration_months.' meses' : '' }}{{ $template->code ? ' ('.$template->code.')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="button" wire:click="applyTemplate"
                                class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-slate-900 text-white text-sm font-bold hover:bg-slate-800 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v6h6M20 20v-6h-6M5 19A9 9 0 0019 5M19 5h-5m5 0v5"/></svg>
                                <span>Aplicar</span>
                            </button>
                        </div>
                        @error('hr_contract_template_id') <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider mt-2">{{ $message }}</p> @enderror
                    </div>
                </div>
                
                {{-- Card: Datos del Contrato --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/30 flex items-center justify-between">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Información Contractual</h3>
                        <span class="px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-600 text-[10px] font-bold uppercase tracking-wider">Acuerdo Legal</span>
                    </div>
                    <div class="p-6 lg:p-8 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Colaborador *</label>
                                <select wire:model="employee_id"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-bold text-slate-700">
                                    <option value="">— Seleccionar empleado —</option>
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                                    @endforeach
                                </select>
                                @error('employee_id') <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider ml-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Numero de contrato / Folio</label>
                                <input wire:model="contract_number" type="text" readonly
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-100 text-slate-500 transition-all duration-200 uppercase font-mono font-bold">
                                <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-300 ml-1">Generado automaticamente</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Tipo de contrato *</label>
                                <select wire:model="type"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200">
                                    @foreach(\App\Models\HrContract::TYPES as $k => $v)
                                        <option value="{{ $k }}">{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Fecha de inicio *</label>
                                <input wire:model="start_date" type="date"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-bold">
                                @error('start_date') <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider ml-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Fecha de término</label>
                                <input wire:model="end_date" type="date"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card: Jornada y Horario --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/30">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Jornada y Horarios</h3>
                    </div>
                    <div class="p-6 lg:p-8 space-y-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Tipo de jornada</label>
                                <select wire:model.live="work_shift"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-bold text-slate-700">
                                    <option value="oficina">Oficina (lunes a viernes, sin horas extra)</option>
                                    <option value="campo">Campo (horario y sabado segun contrato)</option>
                                </select>
                            </div>
                            <div class="rounded-2xl border border-slate-100 bg-slate-50/50 px-4 py-3">
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Regla operativa</p>
                                <p class="mt-1 text-xs font-semibold text-slate-500">
                                    {{ $work_shift === 'oficina'
                                        ? 'Oficina no trabaja sabados y no genera horas extra en el calculo ordinario.'
                                        : 'Campo puede trabajar sabado y su jornada queda definida en este contrato.' }}
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-4">
                                <div class="space-y-2">
                                    <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Horario de entrada</label>
                                    <input wire:model="entry_time" type="time"
                                        class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-bold text-indigo-600">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Horario de salida</label>
                                    <input wire:model="exit_time" type="time"
                                        class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-bold text-indigo-600">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Tolerancia (minutos)</label>
                                    <input wire:model="tolerance_minutes" type="number" min="0" max="60"
                                        class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200">
                                </div>
                            </div>

                            <div class="space-y-4">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Días laborales</label>
                                <div class="grid grid-cols-2 gap-3">
                                    @php
                                        $days = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'];
                                    @endphp
                                    @foreach($days as $num => $label)
                                        @php $disabledDay = $work_shift === 'oficina' && in_array($num, [6, 7], true); @endphp
                                        <label class="flex items-center gap-3 p-3 rounded-2xl border transition-all {{ $disabledDay ? 'cursor-not-allowed bg-slate-100 border-slate-100 text-slate-300' : 'cursor-pointer '.(in_array($num, $work_days) ? 'bg-indigo-50 border-indigo-200 text-indigo-700' : 'bg-slate-50 border-slate-100 text-slate-500 hover:border-slate-200') }}">
                                            <input type="checkbox" wire:model="work_days" value="{{ $num }}" class="sr-only" @disabled($disabledDay)>
                                            <span class="text-xs font-bold">{{ $label }}</span>
                                            @if(!$disabledDay && in_array($num, $work_days))
                                                <svg class="w-4 h-4 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            @elseif($disabledDay)
                                                <span class="ml-auto text-[9px] font-black uppercase tracking-wider">No aplica</span>
                                            @endif
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-6 border-t border-slate-100">
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Horas por semana *</label>
                                <input wire:model="work_hours_per_week" type="number"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-bold">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Horas jornada Sábado (si aplica)</label>
                                <input wire:model="saturday_hours" type="number" step="0.5"
                                    @disabled($work_shift === 'oficina')
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 disabled:bg-slate-100 disabled:text-slate-300">
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ── COLUMNA DERECHA: Lateral (4 cols) ───────────────────────── --}}
            <div class="xl:col-span-4 space-y-6 lg:space-y-8">
                
                {{-- Card: Compensación --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/30">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Compensación</h3>
                    </div>
                    <div class="p-6 lg:p-8 space-y-6">
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Salario base *</label>
                            <div class="relative group">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">$</span>
                                <input wire:model="salary" type="number" step="0.01" min="0" placeholder="0.00"
                                    class="w-full pl-8 pr-4 py-4 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-black text-xl text-slate-700">
                            </div>
                            @error('salary') <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider ml-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Periodicidad de pago</label>
                            <select wire:model="salary_period"
                                class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-bold text-slate-700">
                                <option value="weekly">Semanal</option>
                                <option value="biweekly">Quincenal</option>
                                <option value="monthly">Mensual</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Card: Prestaciones --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/30">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Prestaciones</h3>
                    </div>
                    <div class="p-6 lg:p-8 space-y-4">
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Días Aguinaldo</label>
                            <input wire:model="aguinaldo_days" type="number"
                                class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-bold">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Días Vacaciones (1er año)</label>
                            <input wire:model="vacation_days" type="number"
                                class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-bold">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Prima Vacacional (%)</label>
                            <div class="relative">
                                <input wire:model="vacation_premium_pct" type="number"
                                    class="w-full pr-8 pl-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-bold">
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">%</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card: Estatus --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="p-6 lg:p-8 space-y-6">
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Estatus del Contrato</label>
                            <select wire:model="status"
                                class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-black uppercase text-[10px] tracking-widest {{ $status === 'active' ? 'text-emerald-600' : 'text-amber-600' }}">
                                @foreach(\App\Models\HrContract::STATUSES as $k => $v)
                                    <option value="{{ $k }}">{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-2 pt-4 border-t border-slate-100">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Notas internas</label>
                            <textarea wire:model="notes" rows="3" placeholder="Observaciones adicionales..."
                                class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 resize-none text-sm"></textarea>
                        </div>
                    </div>
                </div>

            </div>

            <div class="xl:col-span-12">
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/30">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Contrato editable para impresion</h3>
                    </div>
                    <div class="p-6 lg:p-8 space-y-8 bg-slate-100/70">
                        <div class="rounded-2xl border border-indigo-100 bg-indigo-50 px-4 py-3">
                            <p class="text-xs font-semibold text-indigo-700">
                                Este texto se guarda como copia del contrato actual. Puedes corregir redaccion o condiciones antes de imprimir sin modificar la plantilla original.
                            </p>
                        </div>

                        @foreach($print_pages as $index => $page)
                            <div class="mx-auto w-full max-w-[760px]">
                                <div class="mb-2 flex items-center justify-between">
                                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Hoja {{ $index + 1 }} de 5</span>
                                    <span class="text-[10px] font-bold text-slate-400">Vista tipo Word</span>
                                </div>
                                <div class="bg-white shadow-xl shadow-slate-200/70 border border-slate-200 p-6 sm:p-8 aspect-[210/297]">
                                    <textarea wire:model="print_pages.{{ $index }}"
                                        class="w-full h-full resize-none border-0 p-0 focus:ring-0 text-[10pt] leading-[1.15] text-slate-900 font-sans bg-transparent"
                                        spellcheck="true"></textarea>
                                </div>
                                @error('print_pages.'.$index) <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider mt-2">{{ $message }}</p> @enderror
                            </div>
                        @endforeach

                        <div class="pt-4 border-t border-slate-200 space-y-3">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Clausulas adicionales opcionales</label>
                            <textarea wire:model="print_custom_clauses" rows="5"
                                placeholder="Se agregara como clausula especial en caso de necesitar una nota adicional fuera del cuerpo principal."
                                class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 resize-y text-sm leading-relaxed"></textarea>
                            @error('print_custom_clauses') <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider ml-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

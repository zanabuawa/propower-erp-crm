<div class="min-h-screen bg-slate-50/50 -m-4 sm:-m-6 lg:-m-8">
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3 min-w-0">
                <a wire:navigate href="{{ route('hr.contract-templates.index') }}"
                    class="w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl font-bold text-slate-800 truncate">{{ $template?->exists ? 'Editar plantilla' : 'Nueva plantilla' }}</h1>
                    <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">Configuracion base para contratos</p>
                </div>
            </div>
            <button type="button" wire:click="save"
                class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white text-sm font-bold px-5 py-2.5 rounded-xl shadow-lg shadow-indigo-500/25">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                <span>Guardar plantilla</span>
            </button>
        </div>
    </div>

    <div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8">
        <form wire:submit="save" class="grid grid-cols-1 xl:grid-cols-12 gap-6">
            <div class="xl:col-span-8 space-y-6">
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/30">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Datos de plantilla</h3>
                    </div>
                    <div class="p-6 lg:p-8 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Nombre *</label>
                                <input wire:model="name" type="text" placeholder="Contrato inicial 3 meses"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 font-bold text-slate-700">
                                @error('name') <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider">{{ $message }}</p> @enderror
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Clave interna</label>
                                <input wire:model="code" type="text" placeholder="INICIAL-3M"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 font-mono font-bold uppercase">
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Descripcion</label>
                            <textarea wire:model="description" rows="3" placeholder="Uso recomendado, condiciones o notas de RH..."
                                class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 resize-none text-sm"></textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Tipo</label>
                                <select wire:model="contract_type"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5">
                                    @foreach(\App\Models\HrContract::TYPES as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Duracion meses</label>
                                <input wire:model="duration_months" type="number" min="1" max="120"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 font-bold">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Estado</label>
                                <label class="flex items-center gap-3 h-[48px] px-4 rounded-2xl border border-slate-200 bg-slate-50/30 cursor-pointer">
                                    <input wire:model="is_active" type="checkbox" class="rounded border-slate-300 text-indigo-600">
                                    <span class="text-sm font-bold text-slate-600">Plantilla activa</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/30">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Contrato editable por hojas</h3>
                    </div>
                    <div class="p-6 lg:p-8 space-y-8 bg-slate-100/70">
                        <div class="rounded-2xl border border-indigo-100 bg-indigo-50 px-4 py-3">
                            <p class="text-xs font-semibold text-indigo-700">
                                Puedes usar variables como <span class="font-mono">&#123;&#123;employee_name&#125;&#125;</span>,
                                <span class="font-mono">&#123;&#123;start_date_long&#125;&#125;</span>,
                                <span class="font-mono">&#123;&#123;end_date_clause&#125;&#125;</span> y
                                <span class="font-mono">&#123;&#123;daily_salary&#125;&#125;</span>. Al imprimir se remplazan con los datos del contrato.
                            </p>
                        </div>

                        @foreach($print_pages as $index => $page)
                            <div class="mx-auto w-full max-w-[760px]">
                                <div class="mb-2 flex items-center justify-between">
                                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Hoja {{ $index + 1 }} de 5</span>
                                    <span class="text-[10px] font-bold text-slate-400">Formato de impresion</span>
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
                                placeholder="Texto adicional opcional. Si llenas esto, se agregara como clausula especial en contratos que usen esta plantilla."
                                class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 resize-y text-sm leading-relaxed"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="xl:col-span-4 space-y-6">
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/30">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Jornada base</h3>
                    </div>
                    <div class="p-6 lg:p-8 space-y-5">
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Tipo de jornada</label>
                            <select wire:model.live="work_shift"
                                class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 font-bold">
                                <option value="oficina">Oficina</option>
                                <option value="campo">Campo</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Entrada</label>
                                <input wire:model="entry_time" type="time" class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 font-bold text-indigo-600">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Salida</label>
                                <input wire:model="exit_time" type="time" class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 font-bold text-indigo-600">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Horas semana</label>
                                <input wire:model="work_hours_per_week" type="number" class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 font-bold">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Sabado</label>
                                <input wire:model="saturday_hours" type="number" step="0.5" @disabled($work_shift === 'oficina') class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 font-bold disabled:bg-slate-100 disabled:text-slate-300">
                            </div>
                        </div>
                        <div class="space-y-3">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Dias laborales</label>
                            <div class="grid grid-cols-2 gap-2">
                                @php($days = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miercoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sabado', 7 => 'Domingo'])
                                @foreach($days as $num => $label)
                                    @php($disabledDay = $work_shift === 'oficina' && in_array($num, [6, 7], true))
                                    <label class="flex items-center gap-2 px-3 py-2 rounded-xl border text-xs font-bold {{ $disabledDay ? 'bg-slate-100 border-slate-100 text-slate-300 cursor-not-allowed' : (in_array($num, $work_days) ? 'bg-indigo-50 border-indigo-200 text-indigo-700' : 'bg-slate-50 border-slate-100 text-slate-500 cursor-pointer') }}">
                                        <input type="checkbox" wire:model="work_days" value="{{ $num }}" class="rounded border-slate-300 text-indigo-600" @disabled($disabledDay)>
                                        <span>{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('work_days') <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/30">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Prestaciones base</h3>
                    </div>
                    <div class="p-6 lg:p-8 space-y-4">
                        <input wire:model="aguinaldo_days" type="number" placeholder="Dias aguinaldo" class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 font-bold">
                        <input wire:model="vacation_days" type="number" placeholder="Dias vacaciones" class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 font-bold">
                        <input wire:model="vacation_premium_pct" type="number" placeholder="Prima vacacional %" class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 font-bold">
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

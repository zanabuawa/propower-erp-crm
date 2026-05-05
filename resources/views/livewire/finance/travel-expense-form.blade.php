<div class="min-h-screen bg-slate-50/50 -m-4 lg:-m-6">
    {{-- STICKY HEADER --}}
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 max-w-full mx-auto">
            <div class="flex items-center gap-3 min-w-0">
                <a wire:navigate href="{{ route('finance.travel-expenses.index') }}"
                   class="group flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-100 hover:shadow-sm transition-all duration-200">
                    <svg class="w-5 h-5 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl font-bold text-slate-800 truncate">
                        {{ $travel?->exists ? 'Editar Viático ' . $travel->folio : 'Nuevo Viático' }}
                    </h1>
                    <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">
                        Gestión de viáticos y gastos de viaje
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <a wire:navigate href="{{ route('finance.travel-expenses.index') }}"
                   class="hidden sm:inline-flex px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 transition-colors">
                    Cancelar
                </a>
                <button type="button" wire:click="save" wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:scale-[1.02] active:scale-[0.98] disabled:opacity-60 disabled:cursor-not-allowed disabled:hover:scale-100">
                    <svg wire:loading.remove wire:target="save" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    <svg wire:loading wire:target="save" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    <span>{{ $travel?->exists ? 'Guardar cambios' : 'Registrar viático' }}</span>
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-5xl mx-auto p-4 sm:p-6 lg:p-8">
        <form wire:submit="save" class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            {{-- LEFT COLUMN: 8 cols --}}
            <div class="lg:col-span-8 space-y-6">

                {{-- Empleado y Destino --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/30 flex items-center justify-between">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Datos del viaje</h3>
                        <span class="px-2.5 py-1 rounded-lg bg-amber-50 text-amber-600 text-[10px] font-bold uppercase tracking-wider">Viáticos</span>
                    </div>
                    <div class="p-6 space-y-5">
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Empleado *</label>
                            <select wire:model="employee_id"
                                class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 text-sm font-medium text-slate-700">
                                <option value="">— Selecciona empleado —</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">
                                        {{ $emp->employee_number }} — {{ $emp->first_name }} {{ $emp->last_name }} {{ $emp->second_last_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('employee_id') <p class="text-xs text-red-500 mt-1 ml-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Destino *</label>
                                <input wire:model="destination" type="text" placeholder="Ej. Monterrey, N.L."
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 text-sm font-medium">
                                @error('destination') <p class="text-xs text-red-500 mt-1 ml-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Propósito *</label>
                                <input wire:model="purpose" type="text" placeholder="Ej. Instalación en planta cliente"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 text-sm font-medium">
                                @error('purpose') <p class="text-xs text-red-500 mt-1 ml-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Fecha de salida *</label>
                                <input wire:model="departure_date" type="date"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 text-sm font-medium">
                                @error('departure_date') <p class="text-xs text-red-500 mt-1 ml-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Fecha de retorno *</label>
                                <input wire:model="return_date" type="date"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 text-sm font-medium">
                                @error('return_date') <p class="text-xs text-red-500 mt-1 ml-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Notas / Instrucciones</label>
                            <textarea wire:model="notes" rows="2" placeholder="Observaciones adicionales..."
                                class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 text-sm resize-none"></textarea>
                        </div>
                    </div>
                </div>

                {{-- Partidas de gasto --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/30 flex items-center justify-between">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Partidas de gasto</h3>
                        <button type="button" wire:click="addItem"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 text-xs font-bold rounded-xl transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                            </svg>
                            Agregar partida
                        </button>
                    </div>

                    <div class="divide-y divide-slate-100">
                        @foreach($items as $idx => $item)
                            <div class="p-5 space-y-4" wire:key="item-{{ $idx }}">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Partida #{{ $idx + 1 }}</span>
                                    @if(count($items) > 1)
                                        <button type="button" wire:click="removeItem({{ $idx }})"
                                            class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-300 hover:text-red-400 hover:bg-red-50 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    @endif
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                                    <div class="sm:col-span-3 space-y-1.5">
                                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Categoría</label>
                                        <select wire:model="items.{{ $idx }}.category"
                                            class="w-full px-3 py-2.5 rounded-xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 text-sm font-medium">
                                            @foreach($itemCategories as $key => $label)
                                                <option value="{{ $key }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        @error("items.{$idx}.category") <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                                    </div>

                                    <div class="sm:col-span-4 space-y-1.5">
                                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Concepto *</label>
                                        <input wire:model="items.{{ $idx }}.concept" type="text" placeholder="Descripción del gasto"
                                            class="w-full px-3 py-2.5 rounded-xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 text-sm font-medium">
                                        @error("items.{$idx}.concept") <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                                    </div>

                                    <div class="sm:col-span-2 space-y-1.5">
                                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Monto *</label>
                                        <input wire:model="items.{{ $idx }}.amount" type="number" step="0.01" min="0" placeholder="0.00"
                                            class="w-full px-3 py-2.5 rounded-xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 text-sm font-medium text-right">
                                        @error("items.{$idx}.amount") <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                                    </div>

                                    <div class="sm:col-span-3 space-y-1.5">
                                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">No. recibo</label>
                                        <input wire:model="items.{{ $idx }}.receipt_number" type="text" placeholder="Opcional"
                                            class="w-full px-3 py-2.5 rounded-xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 text-sm font-medium">
                                    </div>
                                </div>

                                <div class="space-y-1.5">
                                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Nota de partida</label>
                                    <input wire:model="items.{{ $idx }}.notes" type="text" placeholder="Observaciones (opcional)"
                                        class="w-full px-3 py-2.5 rounded-xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 text-sm">
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Total de partidas --}}
                    <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Total de partidas</span>
                        <span class="text-lg font-black text-slate-800">
                            ${{ number_format($this->totalItems, 2) }} {{ $currency }}
                        </span>
                    </div>
                </div>

            </div>

            {{-- RIGHT COLUMN: 4 cols --}}
            <div class="lg:col-span-4 space-y-6">

                {{-- Monto aprobado y moneda --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/30">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Monto aprobado</h3>
                    </div>
                    <div class="p-6 space-y-5">
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Moneda</label>
                            <div class="grid grid-cols-3 gap-2 p-1 bg-slate-100 rounded-2xl">
                                @foreach(['MXN', 'USD', 'EUR'] as $cur)
                                    <button type="button" wire:click="$set('currency', '{{ $cur }}')"
                                        class="py-2 rounded-xl text-xs font-bold transition-all {{ $currency === $cur ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-400 hover:text-slate-600' }}">
                                        {{ $cur }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Monto aprobado *</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-sm">$</span>
                                <input wire:model="amount_approved" type="number" step="0.01" min="0" placeholder="0.00"
                                    class="w-full pl-8 pr-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 text-right text-lg font-black text-slate-800">
                            </div>
                            @error('amount_approved') <p class="text-xs text-red-500 mt-1 ml-1">{{ $message }}</p> @enderror
                        </div>

                        @if($this->totalItems > 0 && $amount_approved)
                            <div class="p-3 rounded-2xl {{ $this->totalItems <= (float) $amount_approved ? 'bg-emerald-50 border border-emerald-100' : 'bg-amber-50 border border-amber-100' }}">
                                <p class="text-[10px] font-bold uppercase tracking-widest {{ $this->totalItems <= (float) $amount_approved ? 'text-emerald-600' : 'text-amber-600' }}">
                                    {{ $this->totalItems <= (float) $amount_approved ? 'Partidas dentro del límite' : 'Partidas exceden el monto' }}
                                </p>
                                <p class="text-sm font-black {{ $this->totalItems <= (float) $amount_approved ? 'text-emerald-700' : 'text-amber-700' }} mt-0.5">
                                    ${{ number_format($this->totalItems, 2) }} / ${{ number_format((float) $amount_approved, 2) }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Vinculación --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/30">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Vinculación</h3>
                    </div>
                    <div class="p-6 space-y-5">
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Proyecto</label>
                            <select wire:model="project_id"
                                class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 text-sm font-medium text-slate-700">
                                <option value="">— Sin proyecto —</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}">{{ $project->code ? '[' . $project->code . '] ' : '' }}{{ $project->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Sucursal</label>
                            <select wire:model="branch_id"
                                class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 text-sm font-medium text-slate-700">
                                <option value="">— Sin sucursal —</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Info card --}}
                <div class="bg-amber-50 border border-amber-100 rounded-2xl p-4">
                    <div class="flex gap-3">
                        <div class="w-8 h-8 rounded-xl bg-amber-100 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-amber-700">Flujo de aprobación</p>
                            <p class="text-[11px] text-amber-600 mt-0.5 leading-relaxed">
                                El viático se registra en estado <strong>Borrador</strong>. Debe ser aprobado para proceder al pago. El pago genera una transacción financiera automáticamente.
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>

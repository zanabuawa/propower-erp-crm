<div class="min-h-screen bg-slate-50/50 -m-4 lg:-m-6">
    {{-- ── STICKY HEADER ────────────────────────────────────────────────── --}}
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 max-w-full mx-auto">
            <div class="flex items-center gap-3 min-w-0">
                <a wire:navigate href="{{ route('hr.employees.index') }}" 
                   class="group flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-100 hover:shadow-sm transition-all duration-200">
                    <svg class="w-5 h-5 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl font-bold text-slate-800 truncate">
                        {{ $employee?->exists ? 'Editar empleado' : 'Nuevo registro de empleado' }}
                    </h1>
                    <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">
                        {{ $employee?->exists ? $employee->full_name : 'Alta de personal en el sistema' }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <a wire:navigate href="{{ route('hr.employees.index') }}"
                    class="hidden sm:inline-flex px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 transition-colors">
                    Cancelar
                </a>
                <button type="button" wire:click="save"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:scale-[1.02] active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>{{ $employee?->exists ? 'Guardar cambios' : 'Crear empleado' }}</span>
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-full mx-auto p-4 sm:p-6 lg:p-8">
        @if(session('success'))
            <div class="mb-6 flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl animate-in fade-in slide-in-from-top-4 duration-300">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm font-semibold">{{ session('success') }}</p>
            </div>
        @endif

        <form wire:submit="save" class="grid grid-cols-1 xl:grid-cols-12 gap-6 lg:gap-8">
            
            {{-- ── COLUMNA IZQUIERDA: Principal (8 cols) ────────────────────── --}}
            <div class="xl:col-span-8 space-y-6 lg:space-y-8">
                
                {{-- Card: Identificación --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/30">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Identificación Personal</h3>
                    </div>
                    <div class="p-6 lg:p-8 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Número de empleado</label>
                                <input wire:model="employee_number" type="text" placeholder="EMP-001"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Género</label>
                                <select wire:model="gender"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200">
                                    <option value="">Seleccionar</option>
                                    @foreach(\App\Models\HrEmployee::GENDERS as $k => $v)
                                        <option value="{{ $k }}">{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Nombre(s) *</label>
                                <input wire:model="first_name" type="text"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 @error('first_name') border-red-300 bg-red-50/30 @else bg-slate-50/30 @enderror focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200">
                                @error('first_name') <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider ml-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Apellido Paterno *</label>
                                <input wire:model="last_name" type="text"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 @error('last_name') border-red-300 bg-red-50/30 @else bg-slate-50/30 @enderror focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200">
                                @error('last_name') <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider ml-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Apellido Materno</label>
                                <input wire:model="second_last_name" type="text"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">CURP</label>
                                <input wire:model="curp" type="text" maxlength="18" placeholder="XEXX010101HNEXXXA4"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 uppercase">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">RFC</label>
                                <input wire:model="rfc" type="text" maxlength="13" placeholder="XEXX010101ABC"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 @error('rfc') border-red-300 bg-red-50/30 @else bg-slate-50/30 @enderror focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 uppercase">
                                @error('rfc') <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider ml-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">NSS (IMSS)</label>
                                <input wire:model="nss" type="text" maxlength="11" placeholder="00000000000"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card: Datos Laborales --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/30 flex items-center justify-between">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Información Laboral</h3>
                        <span class="px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-600 text-[10px] font-bold uppercase tracking-wider">Contrato y Puesto</span>
                    </div>
                    <div class="p-6 lg:p-8 space-y-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Departamento</label>
                                <select wire:model.live="department_id"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200">
                                    <option value="">Seleccionar</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Puesto</label>
                                <select wire:model="position_id"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200">
                                    <option value="">Seleccionar</option>
                                    @foreach($positions as $pos)
                                        <option value="{{ $pos['id'] }}">{{ $pos['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Sucursal</label>
                                <select wire:model="branch_id"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200">
                                    <option value="">Seleccionar</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Jefe Directo</label>
                                <select wire:model="supervisor_id"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200">
                                    <option value="">Ninguno (Es jefe)</option>
                                    @foreach($supervisors as $sup)
                                        <option value="{{ $sup->id }}">{{ $sup->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4 border-t border-slate-100">
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Fecha de ingreso *</label>
                                <input wire:model="hire_date" type="date"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 @error('hire_date') border-red-300 bg-red-50/30 @else bg-slate-50/30 @enderror focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200">
                                @error('hire_date') <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider ml-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Tipo de contrato *</label>
                                <select wire:model="contract_type"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200">
                                    @foreach(\App\Models\HrEmployee::CONTRACT_TYPES as $k => $v)
                                        <option value="{{ $k }}">{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Estado *</label>
                                <select wire:model="status"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200">
                                    @foreach(\App\Models\HrEmployee::STATUSES as $k => $v)
                                        <option value="{{ $k }}">{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Salario base *</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">$</span>
                                    <input wire:model="salary" type="number" step="0.01" min="0"
                                        class="w-full pl-8 pr-4 py-3 rounded-2xl border-slate-200 @error('salary') border-red-300 bg-red-50/30 @else bg-slate-50/30 @enderror focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-mono">
                                </div>
                                @error('salary') <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider ml-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Periodicidad *</label>
                                <select wire:model="salary_period"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200">
                                    @foreach(\App\Models\HrEmployee::SALARY_PERIODS as $k => $v)
                                        <option value="{{ $k }}">{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card: Contacto y Dirección --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/30">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Contacto y Ubicación</h3>
                    </div>
                    <div class="p-6 lg:p-8 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Correo electrónico</label>
                                <input wire:model="email" type="email" placeholder="ejemplo@propower.mx"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 @error('email') border-red-300 bg-red-50/30 @else bg-slate-50/30 @enderror focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200">
                                @error('email') <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider ml-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Teléfono</label>
                                <input wire:model="phone" type="tel" placeholder="55 1234 5678"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200">
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Domicilio completo</label>
                            <input wire:model="address" type="text" placeholder="Calle, número exterior/interior, colonia..."
                                class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Ciudad</label>
                                <input wire:model="city" type="text"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Estado</label>
                                <input wire:model="state" type="text"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Código Postal</label>
                                <input wire:model="postal_code" type="text" maxlength="10"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200">
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ── COLUMNA DERECHA: Lateral (4 cols) ───────────────────────── --}}
            <div class="xl:col-span-4 space-y-6 lg:space-y-8">
                
                {{-- Card: Foto --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="p-6 lg:p-8">
                        <div class="flex flex-col items-center text-center space-y-4">
                            <div class="relative group">
                                <div class="w-32 h-32 rounded-3xl bg-slate-100 border-2 border-dashed border-slate-300 flex items-center justify-center overflow-hidden group-hover:border-indigo-300 transition-colors">
                                    @if ($photo_upload)
                                        <img src="{{ $photo_upload->temporaryUrl() }}" class="w-full h-full object-cover">
                                    @elseif ($employee?->photo)
                                        <img src="{{ asset('storage/' . $employee->photo) }}" class="w-full h-full object-cover">
                                    @else
                                        <svg class="w-12 h-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    @endif
                                </div>
                                <label class="absolute -bottom-2 -right-2 w-10 h-10 rounded-xl bg-white border border-slate-200 shadow-sm flex items-center justify-center text-slate-600 cursor-pointer hover:bg-slate-50 hover:text-indigo-600 transition-all">
                                    <input type="file" wire:model="photo_upload" class="sr-only" accept="image/*">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </label>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-slate-800 uppercase tracking-tight">Fotografía del empleado</h4>
                                <p class="text-[11px] text-slate-400 font-medium">PNG, JPG hasta 2MB</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card: Datos Bancarios --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/30">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Pago y Prestaciones</h3>
                    </div>
                    <div class="p-6 lg:p-8 space-y-6">
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Forma de pago</label>
                            <select wire:model="payment_method"
                                class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200">
                                @foreach(\App\Models\HrEmployee::PAYMENT_METHODS as $k => $v)
                                    <option value="{{ $k }}">{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Banco</label>
                            <input wire:model="bank" type="text" placeholder="Ej: BBVA"
                                class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">CLABE Interbancaria</label>
                            <input wire:model="clabe" type="text" maxlength="18"
                                class="w-full px-4 py-3 rounded-2xl border-slate-200 @error('clabe') border-red-300 bg-red-50/30 @else bg-slate-50/30 @enderror focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-mono">
                            @error('clabe') <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider ml-1">{{ $message }}</p> @enderror
                        </div>
                        
                        <div class="pt-4 border-t border-slate-100 space-y-4">
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">SDI (IMSS)</label>
                                <input wire:model="daily_salary_imss" type="number" step="0.01"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-mono">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card: Emergencia --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/30">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Contacto de Emergencia</h3>
                    </div>
                    <div class="p-6 lg:p-8 space-y-4">
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Nombre</label>
                            <input wire:model="emergency_contact_name" type="text"
                                class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Teléfono</label>
                            <input wire:model="emergency_contact_phone" type="tel"
                                class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200">
                        </div>
                    </div>
                </div>

                {{-- Card: Notas --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="p-6 lg:p-8">
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Notas internas</label>
                            <textarea wire:model="notes" rows="3"
                                class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 resize-none text-sm"></textarea>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>

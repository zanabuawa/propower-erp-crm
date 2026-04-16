<div>
    <x-page-header
        :title="$employee?->exists ? 'Editar empleado: '.$employee->full_name : 'Nuevo empleado'"
        description="Datos personales, laborales y de pago">
        <x-slot:actions>
            <a href="{{ route('hr.employees.index') }}" wire:navigate
               class="inline-flex items-center gap-2 px-3 py-2 text-sm text-slate-600 hover:text-slate-800 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
                ← Volver
            </a>
        </x-slot:actions>
    </x-page-header>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    <form wire:submit="save" class="space-y-6">

        {{-- IDENTIFICACIÓN --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h3 class="text-sm font-semibold text-slate-700 mb-4 pb-3 border-b border-slate-100">Identificación personal</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Número de empleado</label>
                    <input wire:model="employee_number" type="text" placeholder="EMP-001"
                           class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Nombre(s) <span class="text-red-500">*</span></label>
                    <input wire:model="first_name" type="text" required
                           class="w-full px-3 py-2 text-sm border @error('first_name') border-red-300 @else border-slate-200 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    @error('first_name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Apellido paterno <span class="text-red-500">*</span></label>
                    <input wire:model="last_name" type="text" required
                           class="w-full px-3 py-2 text-sm border @error('last_name') border-red-300 @else border-slate-200 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    @error('last_name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Apellido materno</label>
                    <input wire:model="second_last_name" type="text"
                           class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Género</label>
                    <select wire:model="gender"
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                        <option value="">Seleccionar</option>
                        @foreach(\App\Models\HrEmployee::GENDERS as $k => $v)
                            <option value="{{ $k }}">{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Fecha de nacimiento</label>
                    <input wire:model="birth_date" type="date"
                           class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">CURP</label>
                    <input wire:model="curp" type="text" maxlength="18" placeholder="XEXX010101HNEXXXA4"
                           class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg uppercase focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">RFC</label>
                    <input wire:model="rfc" type="text" maxlength="13" placeholder="XEXX010101ABC"
                           class="w-full px-3 py-2 text-sm border @error('rfc') border-red-300 @else border-slate-200 @enderror rounded-lg uppercase focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    @error('rfc') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">NSS (IMSS)</label>
                    <input wire:model="nss" type="text" maxlength="11" placeholder="00000000000"
                           class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                </div>
            </div>
        </div>

        {{-- CONTACTO Y DIRECCIÓN --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h3 class="text-sm font-semibold text-slate-700 mb-4 pb-3 border-b border-slate-100">Contacto y dirección</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Correo electrónico</label>
                    <input wire:model="email" type="email"
                           class="w-full px-3 py-2 text-sm border @error('email') border-red-300 @else border-slate-200 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Teléfono</label>
                    <input wire:model="phone" type="tel"
                           class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                </div>
                <div class="sm:col-span-2 lg:col-span-3">
                    <label class="block text-xs font-medium text-slate-600 mb-1">Domicilio</label>
                    <input wire:model="address" type="text" placeholder="Calle, número, colonia"
                           class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Ciudad</label>
                    <input wire:model="city" type="text"
                           class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Estado</label>
                    <input wire:model="state" type="text"
                           class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Código postal</label>
                    <input wire:model="postal_code" type="text" maxlength="10"
                           class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                </div>
            </div>
        </div>

        {{-- DATOS LABORALES --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h3 class="text-sm font-semibold text-slate-700 mb-4 pb-3 border-b border-slate-100">Datos laborales</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Departamento</label>
                    <select wire:model.live="department_id"
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                        <option value="">Seleccionar</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Puesto</label>
                    <select wire:model="position_id"
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                        <option value="">Seleccionar</option>
                        @foreach($positions as $pos)
                            <option value="{{ $pos['id'] }}">{{ $pos['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Sucursal</label>
                    <select wire:model="branch_id"
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                        <option value="">Seleccionar</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Fecha de ingreso <span class="text-red-500">*</span></label>
                    <input wire:model="hire_date" type="date" required
                           class="w-full px-3 py-2 text-sm border @error('hire_date') border-red-300 @else border-slate-200 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    @error('hire_date') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Tipo de contrato <span class="text-red-500">*</span></label>
                    <select wire:model="contract_type" required
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                        @foreach(\App\Models\HrEmployee::CONTRACT_TYPES as $k => $v)
                            <option value="{{ $k }}">{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Turno</label>
                    <select wire:model="work_shift"
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                        <option value="">Seleccionar</option>
                        @foreach(\App\Models\HrEmployee::WORK_SHIFTS as $k => $v)
                            <option value="{{ $k }}">{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Salario <span class="text-red-500">*</span></label>
                    <input wire:model="salary" type="number" step="0.01" min="0" required
                           class="w-full px-3 py-2 text-sm border @error('salary') border-red-300 @else border-slate-200 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    @error('salary') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Periodicidad de pago <span class="text-red-500">*</span></label>
                    <select wire:model="salary_period" required
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                        @foreach(\App\Models\HrEmployee::SALARY_PERIODS as $k => $v)
                            <option value="{{ $k }}">{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Estado <span class="text-red-500">*</span></label>
                    <select wire:model="status" required
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                        @foreach(\App\Models\HrEmployee::STATUSES as $k => $v)
                            <option value="{{ $k }}">{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">SDI (Salario Diario Integrado IMSS)</label>
                    <input wire:model="daily_salary_imss" type="number" step="0.01" min="0"
                           class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Crédito INFONAVIT</label>
                    <input wire:model="infonavit_credit" type="text"
                           class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                </div>
            </div>
        </div>

        {{-- DATOS DE PAGO --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h3 class="text-sm font-semibold text-slate-700 mb-4 pb-3 border-b border-slate-100">Datos bancarios y pago</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Forma de pago</label>
                    <select wire:model="payment_method"
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                        @foreach(\App\Models\HrEmployee::PAYMENT_METHODS as $k => $v)
                            <option value="{{ $k }}">{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Banco</label>
                    <input wire:model="bank" type="text" placeholder="BBVA, HSBC, Banamex..."
                           class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Número de cuenta</label>
                    <input wire:model="bank_account" type="text"
                           class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-slate-600 mb-1">CLABE interbancaria (18 dígitos)</label>
                    <input wire:model="clabe" type="text" maxlength="18"
                           class="w-full px-3 py-2 text-sm border @error('clabe') border-red-300 @else border-slate-200 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    @error('clabe') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- CONTACTO DE EMERGENCIA --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h3 class="text-sm font-semibold text-slate-700 mb-4 pb-3 border-b border-slate-100">Contacto de emergencia</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Nombre completo</label>
                    <input wire:model="emergency_contact_name" type="text"
                           class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Teléfono</label>
                    <input wire:model="emergency_contact_phone" type="tel"
                           class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Parentesco</label>
                    <input wire:model="emergency_contact_relationship" type="text" placeholder="Cónyuge, padre, hijo..."
                           class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                </div>
            </div>
        </div>

        {{-- FOTO Y NOTAS --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h3 class="text-sm font-semibold text-slate-700 mb-4 pb-3 border-b border-slate-100">Foto y notas</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Foto (máx 2MB)</label>
                    <input wire:model="photo_upload" type="file" accept="image/*"
                           class="block w-full text-sm text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-medium file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100">
                    @error('photo_upload') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Notas internas</label>
                    <textarea wire:model="notes" rows="3"
                              class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30 resize-none"></textarea>
                </div>
            </div>
        </div>

        {{-- Acciones --}}
        <div class="flex justify-end gap-3">
            <a href="{{ route('hr.employees.index') }}" wire:navigate
               class="px-4 py-2 text-sm text-slate-600 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
                Cancelar
            </a>
            <button type="submit"
                    class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                <span wire:loading.remove wire:target="save">Guardar empleado</span>
                <span wire:loading wire:target="save">Guardando...</span>
            </button>
        </div>
    </form>
</div>

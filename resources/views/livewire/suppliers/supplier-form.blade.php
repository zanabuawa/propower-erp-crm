<div class="min-h-screen bg-slate-50/50 -m-4 lg:-m-6">
    {{-- ── STICKY HEADER ────────────────────────────────────────────────── --}}
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 max-w-full mx-auto">
            <div class="flex items-center gap-3 min-w-0">
                <a wire:navigate href="{{ route('suppliers.index') }}" 
                   class="group flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-100 hover:shadow-sm transition-all duration-200">
                    <svg class="w-5 h-5 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl font-bold text-slate-800 truncate">
                        {{ $supplier?->exists ? 'Editar Proveedor' : 'Nuevo Proveedor' }}
                    </h1>
                    <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">Gestión de cadena de suministro</p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <a wire:navigate href="{{ route('suppliers.index') }}"
                    class="hidden sm:inline-flex px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 transition-colors">
                    Cancelar
                </a>
                <button type="button" wire:click="save"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:scale-[1.02] active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ $supplier?->exists ? 'Guardar Cambios' : 'Registrar Proveedor' }}</span>
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-full mx-auto p-4 sm:p-6 lg:p-8 space-y-8">
        <form wire:submit="save" class="space-y-8">

            {{-- ── TIPO DE PROVEEDOR ────────────────────────────────────────── --}}
            <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm p-6 lg:p-8">
                <div class="flex items-center gap-6">
                    <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Tipo de Entidad:</label>
                    <div class="flex p-1 bg-slate-100 rounded-2xl w-fit border border-slate-200/50 shadow-inner">
                        <button type="button" wire:click="$set('type', 'company')"
                            class="px-6 py-2.5 text-xs font-black uppercase tracking-widest rounded-xl transition-all duration-200
                                {{ $type === 'company' ? 'bg-white text-indigo-600 shadow-sm ring-1 ring-black/5' : 'text-slate-500 hover:text-slate-700' }}">
                            Empresa
                        </button>
                        <button type="button" wire:click="$set('type', 'person')"
                            class="px-6 py-2.5 text-xs font-black uppercase tracking-widest rounded-xl transition-all duration-200
                                {{ $type === 'person' ? 'bg-white text-indigo-600 shadow-sm ring-1 ring-black/5' : 'text-slate-500 hover:text-slate-700' }}">
                            Persona Física
                        </button>
                    </div>
                </div>
            </div>

            {{-- ── SECCIÓN: DATOS GENERALES ────────────────────────────────────── --}}
            <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                <div class="p-6 lg:p-8 space-y-8">
                    <div class="flex items-center gap-3 border-b border-slate-100 pb-5">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        <h2 class="text-base font-bold text-slate-800">Perfil del Proveedor</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                        <div class="md:col-span-2 space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">
                                {{ $type === 'company' ? 'Razón Social *' : 'Nombre Completo *' }}
                            </label>
                            <input wire:model="name" type="text" placeholder="Ej. Aceros del Norte S.A."
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 transition-all">
                            @error('name') <p class="text-[10px] text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Código Interno</label>
                            <input wire:model="internal_code" type="text" placeholder="Ej. PROV-001"
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-mono font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 transition-all">
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Estado</label>
                            <select wire:model="status"
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 appearance-none cursor-pointer">
                                <option value="active">Activo</option>
                                <option value="inactive">Inactivo</option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">RFC / ID Fiscal</label>
                            <input wire:model="rfc" type="text" maxlength="13" placeholder="ABCD010101XXX"
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-mono font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 uppercase transition-all">
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Régimen Fiscal</label>
                            <input wire:model="tax_regime" type="text" placeholder="Ej. 601"
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 transition-all">
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Tipo de Servicio</label>
                            <select wire:model="service_type"
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 appearance-none cursor-pointer">
                                <option value="">— Sin especificar —</option>
                                @foreach(\App\Models\Supplier::SERVICE_TYPES as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Rubro / Giro</label>
                            <select wire:model="supplier_category"
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 appearance-none cursor-pointer">
                                <option value="">— Sin especificar —</option>
                                @foreach(\App\Models\Supplier::CATEGORIES as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="md:col-span-2 space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Sitio Web</label>
                            <input wire:model="website" type="text" placeholder="https://..."
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 transition-all">
                        </div>

                        <div class="md:col-span-2 space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Logo / Marca</label>
                            <div class="flex items-center gap-4">
                                <input wire:model="image" type="file" accept="image/*"
                                    class="flex-1 text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-black file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100 transition-all">
                                @if($supplier?->image || $image)
                                    <div class="w-12 h-12 rounded-xl overflow-hidden border border-slate-200 shadow-sm shrink-0">
                                        <img src="{{ $image ? $image->temporaryUrl() : Storage::url($supplier->image) }}" class="w-full h-full object-cover">
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── SECCIÓN: CONTACTO DIRECTO ──────────────────────────────────── --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                {{-- Teléfonos --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="p-6 lg:p-8 space-y-6">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-5">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-teal-50 flex items-center justify-center text-teal-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                </div>
                                <h2 class="text-base font-bold text-slate-800">Teléfonos</h2>
                            </div>
                            <button type="button" wire:click="addPhone"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-teal-50 text-teal-600 rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-teal-100 transition-all">+ Agregar</button>
                        </div>

                        <div class="space-y-4">
                            @foreach($phones as $index => $phone)
                                <div class="flex items-center gap-3 bg-slate-50 p-3 rounded-2xl border border-slate-100 group transition-all hover:bg-white hover:border-teal-200 hover:shadow-sm">
                                    <input wire:model="phones.{{ $index }}.number" type="text" placeholder="Número"
                                        class="flex-1 bg-transparent border-none focus:ring-0 p-0 text-sm font-bold text-slate-700">
                                    <select wire:model="phones.{{ $index }}.type"
                                        class="bg-transparent border-none focus:ring-0 p-0 text-[10px] font-black uppercase text-slate-500 cursor-pointer w-24">
                                        <option value="mobile">Móvil</option>
                                        <option value="office">Oficina</option>
                                        <option value="home">Casa</option>
                                        <option value="fax">Fax</option>
                                    </select>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input wire:model="phones.{{ $index }}.is_primary" type="checkbox" class="sr-only peer">
                                        <div class="w-8 h-4 bg-slate-200 rounded-full peer peer-checked:bg-teal-500 relative transition-all after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:after:translate-x-4"></div>
                                        <span class="text-[9px] font-black uppercase text-slate-400 peer-checked:text-teal-600">Fav</span>
                                    </label>
                                    @if(count($phones) > 1)
                                        <button type="button" wire:click="removePhone({{ $index }})" class="p-1.5 text-slate-300 hover:text-rose-500 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Correos --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="p-6 lg:p-8 space-y-6">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-5">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                </div>
                                <h2 class="text-base font-bold text-slate-800">Correos Electrónicos</h2>
                            </div>
                            <button type="button" wire:click="addEmail"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 text-indigo-600 rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-indigo-100 transition-all">+ Agregar</button>
                        </div>

                        <div class="space-y-4">
                            @foreach($emails as $index => $email)
                                <div class="flex items-center gap-3 bg-slate-50 p-3 rounded-2xl border border-slate-100 group transition-all hover:bg-white hover:border-indigo-200 hover:shadow-sm">
                                    <input wire:model="emails.{{ $index }}.email" type="email" placeholder="correo@ejemplo.com"
                                        class="flex-1 bg-transparent border-none focus:ring-0 p-0 text-sm font-bold text-slate-700">
                                    <select wire:model="emails.{{ $index }}.type"
                                        class="bg-transparent border-none focus:ring-0 p-0 text-[10px] font-black uppercase text-slate-500 cursor-pointer w-24">
                                        <option value="work">Trabajo</option>
                                        <option value="personal">Personal</option>
                                        <option value="billing">Facturación</option>
                                    </select>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input wire:model="emails.{{ $index }}.is_primary" type="checkbox" class="sr-only peer">
                                        <div class="w-8 h-4 bg-slate-200 rounded-full peer peer-checked:bg-indigo-600 relative transition-all after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:after:translate-x-4"></div>
                                        <span class="text-[9px] font-black uppercase text-slate-400 peer-checked:text-indigo-600">Fav</span>
                                    </label>
                                    @if(count($emails) > 1)
                                        <button type="button" wire:click="removeEmail({{ $index }})" class="p-1.5 text-slate-300 hover:text-rose-500 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── SECCIÓN: DIRECCIÓN & CRÉDITO ──────────────────────────────── --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                {{-- Dirección --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="p-6 lg:p-8 space-y-8">
                        <div class="flex items-center gap-3 border-b border-slate-100 pb-5">
                            <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </div>
                            <h2 class="text-base font-bold text-slate-800">Dirección Fiscal</h2>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="md:col-span-2 space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Calle y Número</label>
                                <input wire:model="address" type="text"
                                    class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 transition-all">
                            </div>
                            @include('livewire.partials.location-fields')
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Código Postal</label>
                                <input wire:model="zip_code" type="text" maxlength="5"
                                    class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-mono font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 transition-all">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Condiciones --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="p-6 lg:p-8 space-y-8">
                        <div class="flex items-center gap-3 border-b border-slate-100 pb-5">
                            <div class="w-10 h-10 rounded-xl bg-rose-50 flex items-center justify-center text-rose-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <h2 class="text-base font-bold text-slate-800">Condiciones Comerciales</h2>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Límite de Crédito Otorgado ($)</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">$</span>
                                    <input wire:model="credit_limit" type="number" step="0.01" min="0" placeholder="0.00"
                                        class="w-full bg-slate-50 border-none rounded-2xl pl-8 pr-4 py-4 text-sm font-black text-slate-800 focus:ring-4 focus:ring-rose-500/10">
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Días de Crédito Otorgados</label>
                                <input wire:model="payment_terms" type="number" min="0" placeholder="0 = Contado"
                                    class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-rose-500/10">
                            </div>
                            <div class="md:col-span-2 space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Notas / Perfil del Proveedor</label>
                                <textarea wire:model="description" rows="4" placeholder="Observaciones generales, niveles de cumplimiento..."
                                    class="w-full bg-slate-50 border-none rounded-[2rem] px-6 py-5 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-rose-500/10 transition-all resize-none"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── SECCIÓN: CUENTAS BANCARIAS ────────────────────────────────── --}}
            <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                <div class="p-6 lg:p-8 space-y-6">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-5">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-slate-800">Cuentas Bancarias</h2>
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Para gestión de pagos y tesorería</p>
                            </div>
                        </div>
                        <button type="button" wire:click="addBankAccount"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-amber-50 text-amber-600 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-amber-100 transition-all">+ Nueva Cuenta</button>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        @foreach($bankAccounts as $index => $account)
                            <div class="bg-slate-50/50 rounded-[2rem] border border-slate-100 p-6 space-y-6 group hover:border-amber-200 hover:bg-white transition-all">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Banco / Institución</label>
                                        <input wire:model="bankAccounts.{{ $index }}.bank_name" type="text" placeholder="Ej. BBVA, Santander..."
                                            class="w-full bg-white border-none rounded-xl px-4 py-3 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-amber-500/10">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Titular de la Cuenta</label>
                                        <input wire:model="bankAccounts.{{ $index }}.beneficiary" type="text" placeholder="Nombre beneficiario"
                                            class="w-full bg-white border-none rounded-xl px-4 py-3 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-amber-500/10">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Número de Cuenta</label>
                                        <input wire:model="bankAccounts.{{ $index }}.account_number" type="text"
                                            class="w-full bg-white border-none rounded-xl px-4 py-3 text-sm font-mono font-bold text-slate-700 focus:ring-4 focus:ring-amber-500/10">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">CLABE Interbancaria</label>
                                        <input wire:model="bankAccounts.{{ $index }}.clabe" type="text" maxlength="18"
                                            class="w-full bg-white border-none rounded-xl px-4 py-3 text-sm font-mono font-bold text-slate-700 focus:ring-4 focus:ring-amber-500/10">
                                    </div>
                                </div>
                                <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                                    <label class="flex items-center gap-3 cursor-pointer group/toggle">
                                        <div class="relative inline-flex items-center">
                                            <input type="checkbox" wire:model="bankAccounts.{{ $index }}.is_primary" class="sr-only peer">
                                            <div class="w-10 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:bg-amber-500 relative transition-all after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-5"></div>
                                        </div>
                                        <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 peer-checked:text-amber-600 transition-colors">Cuenta Principal</span>
                                    </label>
                                    <button type="button" wire:click="removeBankAccount({{ $index }})"
                                        class="text-[10px] font-black uppercase tracking-widest text-slate-300 hover:text-rose-500 transition-colors">Eliminar</button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if(empty($bankAccounts))
                        <div class="py-12 text-center bg-slate-50 rounded-[2rem] border-2 border-dashed border-slate-100">
                            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">No hay cuentas bancarias registradas</p>
                        </div>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>
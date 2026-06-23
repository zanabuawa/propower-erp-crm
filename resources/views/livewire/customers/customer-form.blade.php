<div class="min-h-screen bg-slate-50/50 -m-4 lg:-m-6">
    {{-- ── STICKY HEADER ────────────────────────────────────────────────── --}}
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 max-w-full mx-auto">
            <div class="flex items-center gap-3 min-w-0">
                <a wire:navigate href="{{ route('contacts.index') }}" 
                   class="group flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-100 hover:shadow-sm transition-all duration-200">
                    <svg class="w-5 h-5 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl font-bold text-slate-800 truncate">
                        {{ $customer?->exists ? 'Editar Cliente' : 'Nuevo Cliente' }}
                    </h1>
                    <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">Gestión de cartera de clientes</p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <a wire:navigate href="{{ route('contacts.index') }}"
                    class="hidden sm:inline-flex px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 transition-colors">
                    Cancelar
                </a>
                <button type="button" wire:click="save"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:scale-[1.02] active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ $customer?->exists ? 'Guardar Cambios' : 'Registrar Cliente' }}</span>
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-full mx-auto p-4 sm:p-6 lg:p-8 space-y-8">
        <form wire:submit="save" class="space-y-8">

            {{-- ── SECCIÓN: DATOS GENERALES ────────────────────────────────────── --}}
            <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                <div class="p-6 lg:p-8 space-y-8">
                    <div class="flex items-center gap-3 border-b border-slate-100 pb-5">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        <h2 class="text-base font-bold text-slate-800">Identificación Fiscal & Perfil</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                        <div class="md:col-span-2 space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Razón Social / Nombre *</label>
                            <input wire:model="name" type="text" placeholder="Ej. Comercializadora Alfa S.A. de C.V."
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 transition-all">
                            @error('name') <p class="text-[10px] text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">RFC / ID Fiscal</label>
                            <input wire:model="rfc" type="text" maxlength="13" placeholder="ABCD010101XXX"
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-mono font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 uppercase transition-all">
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Estado del Cliente</label>
                            <select wire:model="status"
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 appearance-none cursor-pointer">
                                <option value="prospect">Prospecto</option>
                                <option value="active">Activo</option>
                                <option value="inactive">Inactivo</option>
                            </select>
                        </div>

                        <div class="md:col-span-2 space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Régimen Fiscal</label>
                            <select wire:model="tax_regime"
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 appearance-none cursor-pointer">
                                <option value="">— Seleccionar Régimen —</option>
                                @foreach(\App\Models\Customer::TAX_REGIMES as $code => $label)
                                    <option value="{{ $code }}">{{ $code }} - {{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="md:col-span-2 space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Uso de CFDI (Default)</label>
                            <select wire:model="cfdi_use"
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 appearance-none cursor-pointer">
                                <option value="">— Seleccionar Uso —</option>
                                @foreach(\App\Models\Customer::CFDI_USES as $code => $label)
                                    <option value="{{ $code }}">{{ $code }} - {{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Aniversario como cliente con la empresa</label>
                            <input wire:model="anniversary_date" type="date"
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 transition-all">
                        </div>

                        <div class="md:col-span-2 space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Sitio Web</label>
                            <input wire:model="website" type="text" placeholder="https://..."
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 transition-all">
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Imagen / logo del cliente</label>
                            <div class="flex items-center gap-4">
                                <input wire:model="image" type="file" accept="image/*"
                                    class="flex-1 text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-black file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100 transition-all">
                                @if($customer?->image || $image)
                                    <div class="w-12 h-12 rounded-xl overflow-hidden border border-slate-200 shadow-sm shrink-0">
                                        <img src="{{ $image ? $image->temporaryUrl() : Storage::url($customer->image) }}" class="w-full h-full object-cover">
                                    </div>
                                @endif
                            </div>
                            @error('image') <p class="text-[10px] text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
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

            {{-- ── SECCIÓN: PERSONAS DE CONTACTO ──────────────────────────────── --}}
            <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                <div class="p-6 lg:p-8 space-y-6">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-5">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-slate-800">Personas de Contacto</h2>
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Gestión de relaciones internas</p>
                            </div>
                        </div>
                        <button type="button" wire:click="addContact"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-amber-50 text-amber-600 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-amber-100 transition-all">+ Nuevo Contacto</button>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        @foreach($contacts as $index => $contact)
                            <div class="bg-slate-50/50 rounded-[2rem] border border-slate-100 p-6 space-y-6 group hover:border-amber-200 hover:bg-white transition-all">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                    <div class="sm:col-span-2 space-y-2">
                                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Nombre Completo *</label>
                                        <input wire:model="contacts.{{ $index }}.first_name" type="text" placeholder="Nombre(s)"
                                            class="w-full bg-white border-none rounded-xl px-4 py-3 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-amber-500/10">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Apellido Paterno</label>
                                        <input wire:model="contacts.{{ $index }}.paternal_surname" type="text"
                                            class="w-full bg-white border-none rounded-xl px-4 py-3 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-amber-500/10">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Apellido Materno</label>
                                        <input wire:model="contacts.{{ $index }}.maternal_surname" type="text"
                                            class="w-full bg-white border-none rounded-xl px-4 py-3 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-amber-500/10">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Cargo / Puesto</label>
                                        <input wire:model="contacts.{{ $index }}.position" type="text" placeholder="Ej. TI, Finanzas..."
                                            class="w-full bg-white border-none rounded-xl px-4 py-3 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-amber-500/10">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Teléfono Directo</label>
                                        <input wire:model="contacts.{{ $index }}.phone" type="text"
                                            class="w-full bg-white border-none rounded-xl px-4 py-3 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-amber-500/10">
                                    </div>
                                </div>
                                <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                                    <label class="flex items-center gap-3 cursor-pointer group/toggle">
                                        <div class="relative inline-flex items-center">
                                            <input type="checkbox" wire:model="contacts.{{ $index }}.is_primary" class="sr-only peer">
                                            <div class="w-10 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:bg-amber-500 relative transition-all after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-5"></div>
                                        </div>
                                        <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 peer-checked:text-amber-600 transition-colors">Contacto Principal</span>
                                    </label>
                                    <button type="button" wire:click="removeContact({{ $index }})"
                                        class="text-[10px] font-black uppercase tracking-widest text-slate-300 hover:text-rose-500 transition-colors">Eliminar</button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if(empty($contacts))
                        <div class="py-12 text-center bg-slate-50 rounded-[2rem] border-2 border-dashed border-slate-100">
                            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">No se han registrado personas de contacto</p>
                        </div>
                    @endif
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
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Límite de Crédito ($)</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">$</span>
                                    <input wire:model="credit_limit" type="number" step="0.01" min="0" placeholder="0.00"
                                        class="w-full bg-slate-50 border-none rounded-2xl pl-8 pr-4 py-4 text-sm font-black text-slate-800 focus:ring-4 focus:ring-rose-500/10">
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Días de Crédito Pactados</label>
                                <input wire:model="payment_terms" type="number" min="0" placeholder="0 = Contado"
                                    class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-rose-500/10">
                            </div>
                            <div class="md:col-span-2 space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Notas Internas / Perfilamiento</label>
                                <textarea wire:model="description" rows="4" placeholder="Cualquier dato relevante para el equipo de ventas..."
                                    class="w-full bg-slate-50 border-none rounded-[2rem] px-6 py-5 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-rose-500/10 transition-all resize-none"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

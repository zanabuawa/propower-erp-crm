<div class="min-h-screen bg-slate-50/50 -m-4 lg:-m-6">
    {{-- ── STICKY HEADER ────────────────────────────────────────────────── --}}
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 max-w-full mx-auto">
            <div class="flex items-center gap-3 min-w-0">
                <a wire:navigate href="{{ route('companies.index') }}" 
                   class="group flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-100 hover:shadow-sm transition-all duration-200">
                    <svg class="w-5 h-5 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl font-bold text-slate-800 truncate">
                        {{ $company?->exists ? 'Configuración de Empresa' : 'Registro de Nueva Empresa' }}
                    </h1>
                    <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">
                        {{ $company?->exists ? $name : 'Alta de entidad corporativa principal' }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <a wire:navigate href="{{ route('companies.index') }}"
                    class="hidden sm:inline-flex px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 transition-colors">
                    Cancelar
                </a>
                <button type="button" wire:click="save"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:scale-[1.02] active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>{{ $company?->exists ? 'Guardar cambios' : 'Crear empresa' }}</span>
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-full mx-auto p-4 sm:p-6 lg:p-8">
        <form wire:submit="save" class="grid grid-cols-1 xl:grid-cols-12 gap-6 lg:gap-8">

            {{-- ── COLUMNA IZQUIERDA: Identificación y Fiscal (8 cols) ──────── --}}
            <div class="xl:col-span-8 space-y-6 lg:space-y-8">
                
                {{-- Card: Datos Generales --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/30 flex items-center justify-between">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Información Corporativa</h3>
                        <span class="px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-600 text-[10px] font-bold uppercase tracking-wider">Identidad Comercial</span>
                    </div>
                    <div class="p-6 lg:p-8 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Nombre Comercial *</label>
                                <input wire:model="name" type="text" placeholder="Ej. ProPower Solutions"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-bold">
                                @error('name') <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider ml-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Razón Social</label>
                                <input wire:model="legal_name" type="text" placeholder="Ej. ProPower Soluciones S.A. de C.V."
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">RFC</label>
                                <input wire:model="rfc" type="text" maxlength="13" placeholder="ABC010101XYZ"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 uppercase font-mono">
                                @error('rfc') <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider ml-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="space-y-2 md:col-span-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Régimen Fiscal (SAT)</label>
                                <select wire:model="fiscal_regime"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 text-sm">
                                    <option value="">— Seleccionar Régimen —</option>
                                    @foreach(\App\Models\Company::FISCAL_REGIMES as $code => $label)
                                        <option value="{{ $code }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card: Contacto y Ubicación --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/30">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Contacto y Domicilio Fiscal</h3>
                    </div>
                    <div class="p-6 lg:p-8 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Correo de Contacto</label>
                                <input wire:model="email" type="email" placeholder="administracion@empresa.com"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200">
                                @error('email') <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider ml-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Teléfono Principal</label>
                                <input wire:model="phone" type="text" placeholder="+52 81..."
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200">
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Dirección (Calle, Número, Colonia)</label>
                            <input wire:model="address" type="text" placeholder="Ej. Av. Constitución 123, Col. Centro"
                                class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            @include('livewire.partials.location-fields')
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">C.P. Fiscal</label>
                                <input wire:model="fiscal_postal_code" type="text" maxlength="5" placeholder="64000"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-bold">
                                @error('fiscal_postal_code') <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider ml-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ── COLUMNA DERECHA: Logos y Estatus (4 cols) ──────────────── --}}
            <div class="xl:col-span-4 space-y-6 lg:space-y-8">
                
                {{-- Card: Identidad Visual --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/30">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Branding</h3>
                    </div>
                    <div class="p-6 lg:p-8 space-y-8">
                        {{-- Logo Sidebar --}}
                        <div class="space-y-3">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Logo Principal (Sidebar)</label>
                            <div class="relative group">
                                <div class="w-full aspect-[4/1] rounded-2xl bg-slate-50 border-2 border-dashed border-slate-200 flex items-center justify-center p-4 overflow-hidden group-hover:border-indigo-300 transition-colors">
                                    @if($logo)
                                        <img src="{{ $logo->temporaryUrl() }}" class="max-h-full object-contain">
                                    @elseif($company?->logo)
                                        <img src="{{ Storage::url($company->logo) }}" class="max-h-full object-contain">
                                    @else
                                        <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    @endif
                                    <input type="file" wire:model="logo" class="absolute inset-0 opacity-0 cursor-pointer" accept="image/*">
                                </div>
                            </div>
                        </div>

                        {{-- Icono --}}
                        <div class="space-y-3">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Ícono Cuadrado (Mini)</label>
                            <div class="relative group w-20 h-20">
                                <div class="w-full h-full rounded-2xl bg-slate-50 border-2 border-dashed border-slate-200 flex items-center justify-center p-2 overflow-hidden group-hover:border-indigo-300 transition-colors">
                                    @if($icon)
                                        <img src="{{ $icon->temporaryUrl() }}" class="w-full h-full object-contain">
                                    @elseif($company?->icon)
                                        <img src="{{ Storage::url($company->icon) }}" class="w-full h-full object-contain">
                                    @else
                                        <span class="text-xs font-black text-slate-300">ICO</span>
                                    @endif
                                    <input type="file" wire:model="icon" class="absolute inset-0 opacity-0 cursor-pointer" accept="image/*">
                                </div>
                            </div>
                        </div>

                        {{-- Logo Impresión --}}
                        <div class="space-y-3 pt-4 border-t border-slate-100">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Logo para Reportes / CFDI</label>
                            <div class="relative group">
                                <div class="w-full aspect-[4/1] rounded-2xl bg-slate-900 border-2 border-dashed border-slate-700 flex items-center justify-center p-4 overflow-hidden group-hover:border-indigo-500 transition-colors">
                                    @if($print_logo)
                                        <img src="{{ $print_logo->temporaryUrl() }}" class="max-h-full object-contain">
                                    @elseif($company?->print_logo)
                                        <img src="{{ Storage::url($company->print_logo) }}" class="max-h-full object-contain">
                                    @else
                                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Logo Versión Impresa</span>
                                    @endif
                                    <input type="file" wire:model="print_logo" class="absolute inset-0 opacity-0 cursor-pointer" accept="image/*">
                                </div>
                            </div>
                            <p class="text-[9px] text-slate-400 leading-tight">Use una versión contrastada (blanco/negro) para documentos PDF.</p>
                        </div>
                    </div>
                </div>

                {{-- Card: Estatus --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="p-6 lg:p-8 space-y-6">
                        <div class="flex items-center justify-between p-4 rounded-2xl bg-emerald-50/50 border border-emerald-100/50">
                            <div>
                                <p class="text-xs font-bold text-slate-700">Estado Operativo</p>
                                <p class="text-[10px] text-emerald-600 uppercase font-bold tracking-wider">¿Empresa Activa?</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input wire:model="is_active" type="checkbox" class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                            </label>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>

<div class="min-h-screen bg-slate-50/50 -m-4 lg:-m-6">
    {{-- ── STICKY HEADER ────────────────────────────────────────────────── --}}
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 max-w-full mx-auto">
            <div class="flex items-center gap-3 min-w-0">
                <a wire:navigate href="{{ route('projects.index') }}" 
                   class="group flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-100 hover:shadow-sm transition-all duration-200">
                    <svg class="w-5 h-5 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl font-bold text-slate-800 truncate">
                        {{ $project && $project->exists ? 'Editar Proyecto: ' . $project->code : 'Nuevo Proyecto' }}
                    </h1>
                    <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">Planificación y ejecución de obras</p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <a wire:navigate href="{{ route('projects.index') }}"
                    class="hidden sm:inline-flex px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 transition-colors">
                    Cancelar
                </a>
                <button type="button" wire:click="save"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:scale-[1.02] active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ $project && $project->exists ? 'Guardar Cambios' : 'Crear Proyecto' }}</span>
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-full mx-auto p-4 sm:p-6 lg:p-8 space-y-8">
        <x-alert />

        <form wire:submit="save" class="space-y-8">

            {{-- ── SECCIÓN: IDENTIFICACIÓN ────────────────────────────────────── --}}
            <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                <div class="p-6 lg:p-8 space-y-8">
                    <div class="flex items-center gap-3 border-b border-slate-100 pb-5">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <h2 class="text-base font-bold text-slate-800">Identificación del Proyecto</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Código Interno *</label>
                            <input wire:model="code" type="text" placeholder="Ej. PROJ-001"
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-mono font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 transition-all uppercase">
                            @error('code') <p class="text-[10px] text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Tipo de Proyecto *</label>
                            <div class="relative">
                                <select wire:model="type"
                                    class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 appearance-none cursor-pointer">
                                    <option value="externo">Externo (Cliente)</option>
                                    <option value="interno">Interno</option>
                                    <option value="licitacion">Licitación</option>
                                    <option value="mantenimiento">Mantenimiento</option>
                                    <option value="instalacion">Instalación</option>
                                    <option value="servicio">Servicio</option>
                                </select>
                                <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                        </div>

                        <div class="md:col-span-2 space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Nombre Descriptivo *</label>
                            <input wire:model="name" type="text" placeholder="Ej. Construcción de Nave Industrial B"
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 transition-all">
                            @error('name') <p class="text-[10px] text-rose-500 font-bold mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="md:col-span-4 space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Alcance / Descripción General</label>
                            <textarea wire:model="description" rows="3" placeholder="Describe brevemente los objetivos y el alcance del proyecto..."
                                class="w-full bg-slate-50 border-none rounded-[2rem] px-6 py-5 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-indigo-500/10 transition-all resize-none"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── SECCIÓN: ASIGNACIÓN Y CONTROL ──────────────────────────────── --}}
            <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                <div class="p-6 lg:p-8 space-y-8">
                    <div class="flex items-center gap-3 border-b border-slate-100 pb-5">
                        <div class="w-10 h-10 rounded-xl bg-teal-50 flex items-center justify-center text-teal-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </div>
                        <h2 class="text-base font-bold text-slate-800">Asignación & Vinculación</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                        <div class="md:col-span-2 space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Cliente Receptor</label>
                            <div class="relative">
                                <select wire:model="customer_id" class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-teal-500/10 appearance-none cursor-pointer transition-all">
                                    <option value="">— Sin asignar cliente —</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Venta Asociada</label>
                            <div class="relative">
                                <select wire:model="sale_order_id" class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-teal-500/10 appearance-none cursor-pointer transition-all">
                                    <option value="">— Ninguno —</option>
                                    @foreach($saleOrders as $so)
                                        <option value="{{ $so->id }}">{{ $so->folio }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Estado del Proyecto *</label>
                            <div class="relative">
                                <select wire:model="status" class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-teal-500/10 appearance-none cursor-pointer transition-all">
                                    <option value="borrador">Borrador</option>
                                    <option value="activo">Activo</option>
                                    <option value="pausado">Pausado</option>
                                    <option value="completado">Completado</option>
                                    <option value="cancelado">Cancelado</option>
                                </select>
                                <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Referencia de Contrato</label>
                            <input wire:model="contract_reference" type="text" placeholder="Ej. CONTR-2026-X"
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-teal-500/10 transition-all uppercase">
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Sucursal Responsable</label>
                            <div class="relative">
                                <select wire:model="branch_id" class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-teal-500/10 appearance-none cursor-pointer transition-all">
                                    <option value="">— Sin sucursal —</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                        </div>

                        <div class="md:col-span-2 space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Líder / Responsable</label>
                            <div class="relative">
                                <select wire:model="responsible_user_id" class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-teal-500/10 appearance-none cursor-pointer transition-all">
                                    <option value="">— Sin asignar —</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── SECCIÓN: TIEMPOS Y PRESUPUESTO ─────────────────────────────── --}}
            <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                <div class="p-6 lg:p-8 space-y-8">
                    <div class="flex items-center gap-3 border-b border-slate-100 pb-5">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <h2 class="text-base font-bold text-slate-800">Temporalidad & Inversión</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Fecha de Inicio</label>
                            <input wire:model="start_date" type="date"
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-amber-500/10 transition-all">
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Entrega Estimada</label>
                            <input wire:model="end_date" type="date"
                                class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-amber-500/10 transition-all">
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Presupuesto Asignado ($)</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">$</span>
                                <input wire:model="budget" type="number" step="0.01" min="0" placeholder="0.00"
                                    class="w-full bg-slate-50 border-none rounded-2xl pl-8 pr-4 py-4 text-sm font-black text-slate-800 focus:ring-4 focus:ring-amber-500/10 transition-all">
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Moneda</label>
                            <div class="relative">
                                <select wire:model="currency" class="w-full bg-slate-50 border-none rounded-2xl px-5 py-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-amber-500/10 appearance-none cursor-pointer transition-all">
                                    <option value="MXN">MXN — Pesos</option>
                                    <option value="USD">USD — Dólares</option>
                                    <option value="EUR">EUR — Euros</option>
                                </select>
                                <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                        </div>

                        <div class="md:col-span-4 space-y-2">
                            <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Notas de Planeación</label>
                            <textarea wire:model="notes" rows="3" placeholder="Observaciones sobre hitos, riesgos o condiciones financieras..."
                                class="w-full bg-slate-50 border-none rounded-[2rem] px-6 py-5 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-amber-500/10 transition-all resize-none"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── SECCIÓN: RESERVAR INVENTARIO ────────────────────────────────── --}}
            <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                <div class="p-6 lg:p-8 space-y-5">

                    {{-- Cabecera --}}
                    <div class="flex items-center justify-between border-b border-slate-100 pb-5">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-teal-50 flex items-center justify-center text-teal-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-slate-800">Reservar Inventario</h2>
                                <p class="text-[11px] text-slate-400 font-medium">Selecciona productos del catálogo. El sistema reserva stock o levanta una requisición automáticamente.</p>
                            </div>
                        </div>
                        @if($project && $project->exists)
                            <a wire:navigate href="{{ route('projects.materials', $project) }}"
                               class="inline-flex items-center gap-1.5 text-xs font-bold text-teal-600 hover:text-teal-800 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                Gestión completa
                            </a>
                        @endif
                    </div>

                    {{-- Materiales ya registrados (edición) --}}
                    @if($project && $project->exists && $existingMaterials->count())
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Ya registrados en este proyecto</p>
                            <div class="divide-y divide-slate-100 rounded-2xl border border-slate-100 overflow-hidden">
                                @foreach($existingMaterials as $mat)
                                    <div class="flex items-center gap-3 px-5 py-3 bg-slate-50/40">
                                        <span class="shrink-0 inline-flex px-2 py-0.5 rounded bg-slate-100 text-[9px] font-black uppercase text-slate-500">{{ $mat->resource_type }}</span>
                                        <span class="flex-1 text-sm font-bold text-slate-700 truncate">{{ $mat->name }}</span>
                                        <span class="text-xs text-slate-500 shrink-0">{{ number_format($mat->quantity_needed, 2) }} {{ $mat->unit }}</span>
                                        @if($mat->warehouse)
                                            <span class="text-[10px] text-slate-400 hidden md:block shrink-0">{{ $mat->warehouse->name }}</span>
                                        @endif
                                        <span class="shrink-0 text-[9px] font-black uppercase px-2 py-1 rounded-lg {{ \App\Models\ProjectMaterial::$statusColors[$mat->status] ?? 'bg-gray-100 text-gray-500' }}">
                                            {{ \App\Models\ProjectMaterial::$statusLabels[$mat->status] ?? $mat->status }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Lista de líneas seleccionadas --}}
                    @if(count($materialLines))
                        @if($project && $project->exists)
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Nuevos a agregar</p>
                        @endif
                        <div class="rounded-2xl border border-slate-100 overflow-hidden divide-y divide-slate-100">
                            {{-- Cabecera de columnas --}}
                            <div class="hidden md:grid grid-cols-12 gap-3 px-4 py-2 bg-slate-50 text-[9px] font-black text-slate-400 uppercase tracking-widest">
                                <div class="col-span-3">Producto</div>
                                <div class="col-span-2">Almacén</div>
                                <div class="col-span-1 text-center">Tipo</div>
                                <div class="col-span-1 text-center">Unidad</div>
                                <div class="col-span-1 text-center">Cantidad</div>
                                <div class="col-span-2 text-center">Costo unit.</div>
                                <div class="col-span-2 text-center">Stock / Estado</div>
                            </div>

                            @foreach($materialLines as $i => $line)
                                <div class="grid grid-cols-12 gap-2 items-center px-4 py-3 bg-white hover:bg-slate-50/50 transition-colors">

                                    {{-- Nombre (readonly, viene del catálogo) --}}
                                    <div class="col-span-12 md:col-span-3">
                                        <p class="text-xs font-bold text-slate-800 truncate">{{ $line['name'] ?: '—' }}</p>
                                        @if(!empty($line['product_id']))
                                            <input type="hidden" wire:model="materialLines.{{ $i }}.product_id">
                                        @endif
                                    </div>

                                    {{-- Almacén --}}
                                    <div class="col-span-6 md:col-span-2">
                                        <select wire:model.live="materialLines.{{ $i }}.warehouse_id"
                                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-2 py-2 text-xs font-bold text-slate-700 cursor-pointer">
                                            <option value="">— Almacén —</option>
                                            @foreach($warehouses as $w)
                                                <option value="{{ $w->id }}">{{ $w->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Tipo --}}
                                    <div class="col-span-6 md:col-span-1">
                                        <select wire:model="materialLines.{{ $i }}.resource_type"
                                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-2 py-2 text-xs font-bold text-slate-700 cursor-pointer">
                                            <option value="material">Material</option>
                                            <option value="equipo">Equipo</option>
                                            <option value="herramienta">Herramienta</option>
                                            <option value="otro">Otro</option>
                                        </select>
                                    </div>

                                    {{-- Unidad --}}
                                    <div class="col-span-4 md:col-span-1">
                                        <input wire:model="materialLines.{{ $i }}.unit" type="text" placeholder="pza"
                                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-2 py-2 text-xs font-bold text-center uppercase text-slate-700">
                                    </div>

                                    {{-- Cantidad --}}
                                    <div class="col-span-4 md:col-span-1">
                                        <input wire:model.live="materialLines.{{ $i }}.quantity_needed" type="number" step="0.01" min="0.01"
                                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-2 py-2 text-xs font-black text-center text-slate-800">
                                    </div>

                                    {{-- Costo --}}
                                    <div class="col-span-4 md:col-span-2">
                                        <div class="relative">
                                            <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-[10px] text-slate-400 font-bold">$</span>
                                            <input wire:model="materialLines.{{ $i }}.unit_cost" type="number" step="0.01"
                                                class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-5 pr-2 py-2 text-xs font-black text-indigo-600 text-right">
                                        </div>
                                    </div>

                                    {{-- Indicador de stock --}}
                                    <div class="col-span-11 md:col-span-2 flex items-center">
                                        @if(isset($stockPerLine[$i]))
                                            @php $avail = $stockPerLine[$i]; $qty = (float)($line['quantity_needed'] ?? 0); @endphp
                                            @if($avail >= $qty && $qty > 0)
                                                <span class="inline-flex items-center gap-1 text-[9px] font-bold text-emerald-700 bg-emerald-50 px-2 py-1 rounded-lg border border-emerald-100">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                                    Reservar {{ number_format(min($avail,$qty), 2) }}
                                                </span>
                                            @elseif($avail > 0)
                                                <span class="inline-flex items-center gap-1 text-[9px] font-bold text-amber-700 bg-amber-50 px-2 py-1 rounded-lg border border-amber-100">
                                                    ⚡ Parcial + REQ
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 text-[9px] font-bold text-rose-700 bg-rose-50 px-2 py-1 rounded-lg border border-rose-100">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                                    Sin stock → REQ
                                                </span>
                                            @endif
                                        @else
                                            <span class="text-[9px] text-slate-300 font-bold">Selecciona almacén</span>
                                        @endif
                                    </div>

                                    {{-- Quitar --}}
                                    <div class="col-span-1 flex justify-end">
                                        <button type="button" wire:click="removeMaterialLine({{ $i }})"
                                            class="p-1.5 text-slate-300 hover:text-rose-500 rounded-lg hover:bg-rose-50 transition-all">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>

                                </div>
                            @endforeach
                        </div>
                    @else
                        {{-- Estado vacío --}}
                        <div class="flex flex-col items-center justify-center py-10 rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50/40">
                            <svg class="w-10 h-10 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            <p class="text-sm font-bold text-slate-400">Sin elementos</p>
                            <p class="text-[11px] text-slate-300 font-medium mt-1">Abre el catálogo para seleccionar productos</p>
                        </div>
                    @endif

                    {{-- Botón abrir catálogo --}}
                    <button type="button" wire:click="openCatalog"
                        class="inline-flex items-center gap-2 text-sm font-bold text-white bg-gradient-to-r from-teal-600 to-teal-700 hover:from-teal-700 hover:to-teal-800 px-5 py-3 rounded-xl transition-all shadow-md shadow-teal-500/20 hover:scale-[1.02] active:scale-[0.98]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                        Reservar inventario
                    </button>

                </div>
            </div>

            {{-- ── MODAL: CATÁLOGO DE PRODUCTOS ────────────────────────────────── --}}
            @if($showCatalog)
                <div x-data @keydown.escape.window="$wire.set('showCatalog', false)"
                     class="fixed inset-0 z-[70] flex flex-col" wire:key="catalog-modal">

                    {{-- Backdrop --}}
                    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="$set('showCatalog', false)"></div>

                    {{-- Panel --}}
                    <div class="relative z-10 flex flex-col bg-white w-full h-full
                                sm:h-auto sm:max-h-[90vh] sm:rounded-2xl sm:shadow-2xl
                                sm:max-w-4xl sm:mx-auto sm:my-auto sm:mt-[5vh]">

                        {{-- Header --}}
                        <div class="flex items-center gap-3 px-5 py-4 border-b border-slate-200 flex-shrink-0">
                            <svg class="w-5 h-5 text-teal-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                            <div class="flex-1">
                                <h3 class="text-base font-semibold text-slate-900">Catálogo de productos</h3>
                                <p class="text-xs text-slate-400 mt-0.5">Selecciona uno o varios para agregar al proyecto</p>
                            </div>
                            <button type="button" wire:click="$set('showCatalog', false)"
                                class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        {{-- Barra de búsqueda y filtros --}}
                        <div class="px-5 py-3 border-b border-slate-100 flex-shrink-0 space-y-2">
                            <div class="flex gap-2">
                                <div class="relative flex-1">
                                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    <input wire:model.live.debounce.250ms="catalogSearch" type="text" autofocus
                                        placeholder="Buscar por nombre, SKU o código de barras..."
                                        class="w-full pl-9 pr-4 py-2.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-300">
                                </div>
                                <select wire:model="defaultWarehouse"
                                    class="px-3 py-2.5 border border-slate-200 rounded-lg text-xs bg-white text-slate-700 focus:ring-2 focus:ring-teal-300 shrink-0">
                                    <option value="">Almacén (opcional)</option>
                                    @foreach($warehouses as $w)
                                        <option value="{{ $w->id }}">{{ $w->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex gap-2 flex-wrap items-center">
                                <select wire:model.live="catalogCategoryId"
                                    class="px-3 py-1.5 border border-slate-200 rounded-lg text-xs bg-white text-slate-600 focus:ring-2 focus:ring-teal-300">
                                    <option value="">Todas las categorías</option>
                                    @foreach($catalogCategories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                <select wire:model.live="catalogSupplierId"
                                    class="px-3 py-1.5 border border-slate-200 rounded-lg text-xs bg-white text-slate-600 focus:ring-2 focus:ring-teal-300">
                                    <option value="">Todos los proveedores</option>
                                    @foreach($catalogSuppliers as $sup)
                                        <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                                    @endforeach
                                </select>
                                @if($catalogSearch || $catalogCategoryId || $catalogSupplierId)
                                    <button type="button"
                                        wire:click="$set('catalogSearch',''); $set('catalogCategoryId', null); $set('catalogSupplierId', null)"
                                        class="px-3 py-1.5 text-xs text-slate-500 hover:text-slate-700 border border-slate-200 rounded-lg hover:bg-slate-50 transition">
                                        Limpiar filtros
                                    </button>
                                @endif
                                <span class="ml-auto text-xs text-slate-400 self-center">
                                    {{ $catalogProducts->count() }} resultado{{ $catalogProducts->count() !== 1 ? 's' : '' }}
                                </span>
                            </div>
                        </div>

                        {{-- Grid de productos --}}
                        <div class="flex-1 overflow-y-auto p-4">
                            @if($catalogProducts->isEmpty())
                                <div class="flex flex-col items-center justify-center h-48 text-center">
                                    <svg class="w-10 h-10 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <p class="text-sm text-slate-400">Sin resultados. Prueba con otro término o filtro.</p>
                                </div>
                            @else
                                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                                    @foreach($catalogProducts as $product)
                                        @php
                                            $checked     = in_array($product->id, $selectedIds);
                                            $isService   = $product->type === 'service';
                                        @endphp
                                        <button type="button"
                                            wire:key="cat-{{ $product->id }}"
                                            wire:click="toggleProduct({{ $product->id }})"
                                            class="group relative flex flex-col text-left border rounded-xl overflow-hidden transition focus:outline-none focus:ring-2 focus:ring-teal-400
                                                {{ $checked ? 'border-teal-400 shadow-md shadow-teal-100' : 'border-slate-200 hover:border-teal-300 hover:shadow-md' }} bg-white">

                                            {{-- Imagen --}}
                                            <div class="w-full aspect-square bg-slate-50 overflow-hidden flex-shrink-0 relative">
                                                @if($isService)
                                                    <div class="w-full h-full flex items-center justify-center bg-violet-50">
                                                        <svg class="w-8 h-8 text-violet-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                    </div>
                                                @elseif($product->primaryImage)
                                                    <img src="{{ Storage::url($product->primaryImage->path) }}"
                                                        class="w-full h-full object-cover {{ $checked ? '' : 'group-hover:scale-105' }} transition duration-200" alt="">
                                                @else
                                                    <div class="w-full h-full flex items-center justify-center bg-slate-100">
                                                        <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2" stroke-width="1.5"/><circle cx="8.5" cy="8.5" r="1.5" stroke-width="1.5"/><path stroke-width="1.5" stroke-linecap="round" d="M21 15l-5-5L5 21"/></svg>
                                                    </div>
                                                @endif

                                                {{-- Tipo badge --}}
                                                <span class="absolute top-1.5 left-1.5 text-[9px] font-bold px-1.5 py-0.5 rounded
                                                    {{ $isService ? 'bg-violet-100 text-violet-700' : 'bg-indigo-100 text-indigo-700' }}">
                                                    {{ $isService ? 'SRV' : 'PRD' }}
                                                </span>

                                                {{-- Overlay seleccionado --}}
                                                @if($checked)
                                                    <div class="absolute inset-0 bg-teal-600/20 flex items-center justify-center">
                                                        <div class="w-9 h-9 bg-teal-600 rounded-full flex items-center justify-center shadow-lg">
                                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>

                                            {{-- Info --}}
                                            <div class="p-2.5 flex-1 flex flex-col gap-0.5">
                                                <p class="text-xs font-semibold text-slate-900 leading-tight line-clamp-2">{{ $product->name }}</p>
                                                <p class="text-[10px] text-slate-400 font-mono">{{ $product->sku ?? '—' }}</p>
                                                @if($product->category)
                                                    <div class="flex items-center gap-1 mt-0.5">
                                                        <span class="w-1.5 h-1.5 rounded-full flex-shrink-0"
                                                            style="background-color: {{ $product->category->color ?? '#94a3b8' }}"></span>
                                                        <span class="text-[10px] text-slate-400 truncate">{{ $product->category->name }}</span>
                                                    </div>
                                                @endif
                                                <p class="text-sm font-bold text-indigo-600 mt-auto pt-1">
                                                    ${{ number_format($product->sale_price ?? 0, 2) }}
                                                </p>
                                            </div>
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        {{-- Footer --}}
                        <div class="p-5 border-t border-slate-100 bg-slate-50/60 shrink-0 flex items-center justify-between gap-4">
                            <p class="text-xs text-slate-500 font-medium">
                                @if(count($selectedIds))
                                    <span class="font-black text-teal-700">{{ count($selectedIds) }}</span> producto(s) seleccionado(s)
                                @else
                                    Toca una tarjeta para seleccionar
                                @endif
                            </p>
                            <div class="flex gap-3">
                                <button type="button" wire:click="$set('showCatalog', false)"
                                    class="px-4 py-2.5 text-xs font-bold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-all">
                                    Cancelar
                                </button>
                                <button type="button" wire:click="addSelectedToLines"
                                    @disabled(!count($selectedIds))
                                    class="px-5 py-2.5 text-xs font-black text-white bg-teal-600 hover:bg-teal-700 rounded-xl transition-all disabled:opacity-40 disabled:cursor-not-allowed shadow-sm shadow-teal-500/20">
                                    Agregar {{ count($selectedIds) ?: '' }} al proyecto
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            @endif

        </form>
    </div>
</div>


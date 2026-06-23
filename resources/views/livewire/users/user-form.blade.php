<div class="min-h-screen bg-slate-50/50 -m-4 lg:-m-6">
    {{-- ── STICKY HEADER ────────────────────────────────────────────────── --}}
    <div class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 max-w-full mx-auto">
            <div class="flex items-center gap-3 min-w-0">
                <a wire:navigate href="{{ route('users.index') }}"
                   class="group flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-100 hover:shadow-sm transition-all duration-200">
                    <svg class="w-5 h-5 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl font-bold text-slate-800 truncate">
                        {{ $user?->exists ? 'Editar Perfil de Usuario' : 'Nuevo Usuario de Sistema' }}
                    </h1>
                    <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">
                        {{ $user?->exists ? $name : 'Control de acceso y privilegios' }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <a wire:navigate href="{{ route('users.index') }}"
                    class="hidden sm:inline-flex px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 transition-colors">
                    Cancelar
                </a>
                <button type="button" wire:click="save"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:scale-[1.02] active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>{{ $user?->exists ? 'Guardar cambios' : 'Crear usuario' }}</span>
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8">
        <form wire:submit="save" class="space-y-6 lg:space-y-8">

            {{-- ── FILA SUPERIOR: Datos de cuenta + Acceso y Estatus ──────── --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">

                {{-- COLUMNA IZQUIERDA: Identidad (7 cols) --}}
                <div class="lg:col-span-7 space-y-6 lg:space-y-8">

                    {{-- Card: Datos de Cuenta --}}
                    <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                        <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/30 flex items-center justify-between">
                            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Información de Cuenta</h3>
                            <span class="px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-600 text-[10px] font-bold uppercase tracking-wider">Perfil</span>
                        </div>
                        <div class="p-6 lg:p-8 space-y-6">
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Nombre completo *</label>
                                <input wire:model="name" type="text" placeholder="Ej. Juan Pérez García"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-bold text-slate-700">
                                @error('name') <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider ml-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Correo electrónico *</label>
                                <input wire:model="email" type="email" placeholder="usuario@propower.mx"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200">
                                @error('email') <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider ml-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Fecha de Nacimiento (Edad)</label>
                                    <input wire:model="birth_date" type="date"
                                        class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200">
                                    @error('birth_date') <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider ml-1">{{ $message }}</p> @enderror
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Sexo</label>
                                    <select wire:model="gender"
                                        class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200">
                                        <option value="">Seleccionar...</option>
                                        @foreach(\App\Models\HrEmployee::GENDERS as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('gender') <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider ml-1">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-4 border-t border-slate-100">
                                <div class="space-y-2">
                                    <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">
                                        {{ $user?->exists ? 'Nueva contraseña' : 'Contraseña *' }}
                                    </label>
                                    <input wire:model="password" type="password" placeholder="••••••••"
                                        class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-mono">
                                    @if($user?->exists) <p class="text-[9px] text-slate-400 px-1 italic">Dejar vacío para no cambiar</p> @endif
                                    @error('password') <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider ml-1">{{ $message }}</p> @enderror
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Confirmar contraseña</label>
                                    <input wire:model="password_confirmation" type="password" placeholder="••••••••"
                                        class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-mono">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Card: Firma Digital --}}
                    @if($user?->exists || auth()->id() === $user?->id)
                    <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden"
                        x-data="{
                            drawing: false,
                            initCanvas() {
                                const canvas = this.$refs.sigCanvasProfile;
                                if (!canvas || canvas._initialized) return;
                                canvas._initialized = true;
                                const ctx = canvas.getContext('2d');
                                ctx.strokeStyle = '#312e81';
                                ctx.lineWidth   = 3;
                                ctx.lineCap     = 'round';
                                ctx.lineJoin    = 'round';
                                let points = [];
                                const SMOOTH = 0.35;
                                const pos = (e) => {
                                    const r   = canvas.getBoundingClientRect();
                                    const src = e.touches ? e.touches[0] : e;
                                    return {
                                        x: (src.clientX - r.left) * (canvas.width  / r.width),
                                        y: (src.clientY - r.top)  * (canvas.height / r.height)
                                    };
                                };
                                const lerp = (a, b, t) => a + (b - a) * t;
                                const start = (e) => {
                                    e.preventDefault();
                                    this.drawing = true;
                                    points = [];
                                    const p = pos(e);
                                    points.push(p);
                                    ctx.beginPath();
                                    ctx.moveTo(p.x, p.y);
                                };
                                const move = (e) => {
                                    if (!this.drawing) return;
                                    e.preventDefault();
                                    const p = pos(e);
                                    const prev = points[points.length - 1];
                                    const s = { x: lerp(prev.x, p.x, 1 - SMOOTH), y: lerp(prev.y, p.y, 1 - SMOOTH) };
                                    points.push(s);
                                    if (points.length >= 3) {
                                        const p0 = points[points.length - 3], p1 = points[points.length - 2], p2 = points[points.length - 1];
                                        const mid01 = { x: (p0.x + p1.x) / 2, y: (p0.y + p1.y) / 2 };
                                        const mid12 = { x: (p1.x + p2.x) / 2, y: (p1.y + p2.y) / 2 };
                                        ctx.beginPath(); ctx.moveTo(mid01.x, mid01.y);
                                        ctx.quadraticCurveTo(p1.x, p1.y, mid12.x, mid12.y);
                                        ctx.stroke();
                                    } else {
                                        ctx.beginPath(); ctx.moveTo(prev.x, prev.y); ctx.lineTo(s.x, s.y); ctx.stroke();
                                    }
                                    $wire.set('signatureData', canvas.toDataURL('image/png'));
                                };
                                const stop = () => {
                                    if (this.drawing && points.length >= 2) {
                                        const last = points[points.length - 1];
                                        ctx.lineTo(last.x, last.y); ctx.stroke();
                                        $wire.set('signatureData', canvas.toDataURL('image/png'));
                                    }
                                    this.drawing = false; points = [];
                                };
                                canvas.addEventListener('mousedown', start);
                                canvas.addEventListener('mousemove', move);
                                canvas.addEventListener('mouseup', stop);
                                canvas.addEventListener('mouseleave', stop);
                                canvas.addEventListener('touchstart', start, { passive: false });
                                canvas.addEventListener('touchmove', move, { passive: false });
                                canvas.addEventListener('touchend', stop);
                            },
                            clearCanvas() {
                                const canvas = this.$refs.sigCanvasProfile;
                                if (canvas) canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
                                $wire.set('signatureData', '');
                            }
                        }"
                        x-init="initCanvas()"
                    >
                        <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/30 flex items-center justify-between">
                            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Firma Autógrafa</h3>
                            @if($user?->signature)
                                <span class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-600 text-[10px] font-black uppercase tracking-wider">Registrada</span>
                            @else
                                <span class="px-2.5 py-1 rounded-lg bg-amber-50 text-amber-600 text-[10px] font-black uppercase tracking-wider">Pendiente</span>
                            @endif
                        </div>
                        <div class="p-6 lg:p-8 space-y-6">
                            @if(session('signatureSuccess'))
                                <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <p class="text-sm font-bold">{{ session('signatureSuccess') }}</p>
                                </div>
                            @endif

                            @if($user?->signature)
                                <div class="space-y-2">
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Firma actual en sistema</p>
                                    <div class="bg-slate-50 rounded-2xl border border-slate-200 p-4 flex items-center justify-center min-h-[100px]">
                                        <img src="{{ $user->signature }}" class="max-h-20 object-contain drop-shadow-sm">
                                    </div>
                                    <p class="text-[9px] text-slate-400 italic text-center">Última actualización: {{ $user->signature_updated_at?->format('d/m/Y H:i') ?? 'N/A' }}</p>
                                </div>
                            @endif

                            <div class="space-y-3 pt-4 border-t border-slate-100">
                                <div class="flex items-center justify-between px-1">
                                    <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">
                                        {{ $user?->signature ? 'Actualizar firma' : 'Dibujar nueva firma' }}
                                    </label>
                                    <button type="button" @click="clearCanvas()" class="text-[10px] font-black text-red-400 hover:text-red-600 uppercase tracking-tighter transition-colors">Limpiar Lienzo</button>
                                </div>
                                <div class="bg-slate-50 rounded-3xl border-2 border-dashed border-slate-200 overflow-hidden cursor-crosshair hover:border-indigo-300 transition-colors" style="touch-action: none;">
                                    <canvas x-ref="sigCanvasProfile" width="800" height="180" class="w-full block" style="touch-action: none;"></canvas>
                                </div>
                                @error('signatureData') <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider ml-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="flex items-center gap-3">
                                <button type="button" wire:click="saveSignature"
                                    class="flex-1 bg-slate-800 hover:bg-slate-900 text-white text-[11px] font-black uppercase py-3 rounded-xl transition-all shadow-lg shadow-slate-200">
                                    Guardar Firma
                                </button>
                                @if($user?->signature)
                                    <button type="button" wire:click="clearSignature" wire:confirm="¿Eliminar definitivamente la firma?"
                                        class="px-4 py-3 rounded-xl border border-red-100 text-red-500 hover:bg-red-50 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                </div>

                {{-- COLUMNA DERECHA: Acceso, Rol y Estatus (5 cols) --}}
                <div class="lg:col-span-5 space-y-6 lg:space-y-8">

                    {{-- Card: Empresa y Rol --}}
                    <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                        <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/30">
                            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Acceso y Nivel</h3>
                        </div>
                        <div class="p-6 lg:p-8 space-y-6">
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Empresa</label>
                                <select wire:model.live="company_id"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-bold text-slate-700">
                                    <option value="">— Sin empresa (SaaS Admin) —</option>
                                    @foreach($companies as $company)
                                        <option value="{{ $company->id }}">{{ $company->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Sucursal asignada</label>
                                <select wire:model="branch_id"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 {{ !$company_id ? 'opacity-50 cursor-not-allowed' : 'font-bold text-slate-700' }}"
                                    {{ !$company_id ? 'disabled' : '' }}>
                                    <option value="">— Acceso a todas las sucursales —</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="space-y-2 pt-4 border-t border-slate-100">
                                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-widest ml-1">Rol de usuario *</label>
                                <select wire:model.live="role"
                                    class="w-full px-4 py-3 rounded-2xl border-slate-200 bg-slate-50/30 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all duration-200 font-black text-indigo-600 uppercase tracking-wider text-xs">
                                    <option value="">— Seleccionar Perfil —</option>
                                    @foreach($roles as $r)
                                        <option value="{{ $r->name }}">{{ $r->name }}</option>
                                    @endforeach
                                </select>
                                @error('role') <p class="text-[10px] font-bold text-red-500 uppercase tracking-wider ml-1">{{ $message }}</p> @enderror
                            </div>

                            {{-- Estatus: integrado en el mismo card --}}
                            <div class="pt-4 border-t border-slate-100">
                                <div class="flex items-center justify-between px-4 py-3 rounded-2xl bg-emerald-50/50 border border-emerald-100/60">
                                    <div>
                                        <p class="text-xs font-bold text-slate-700">Estado del usuario</p>
                                        <p class="text-[10px] text-emerald-600 uppercase font-bold tracking-wider">¿Puede acceder?</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input wire:model="is_active" type="checkbox" class="sr-only peer">
                                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                                    </label>
                                </div>
                            </div>

                            @if($role)
                            {{-- Resumen de permisos --}}
                            <div class="pt-1 flex items-center gap-3">
                                @php
                                    $totalPerms = collect($this->groupedPermissions)->sum(fn($g) => count($g['permissions']));
                                    $roleCount  = count($this->rolePermissions);
                                    $extraCount = count($selectedPermissions);
                                @endphp
                                <div class="flex-1 bg-indigo-50/60 rounded-2xl px-3 py-2.5 text-center">
                                    <p class="text-lg font-black text-indigo-600">{{ $roleCount }}</p>
                                    <p class="text-[9px] font-bold text-indigo-400 uppercase tracking-wider">Del rol</p>
                                </div>
                                <div class="flex-1 bg-emerald-50/60 rounded-2xl px-3 py-2.5 text-center">
                                    <p class="text-lg font-black text-emerald-600">{{ $extraCount }}</p>
                                    <p class="text-[9px] font-bold text-emerald-500 uppercase tracking-wider">Extras</p>
                                </div>
                                <div class="flex-1 bg-slate-100/80 rounded-2xl px-3 py-2.5 text-center">
                                    <p class="text-lg font-black text-slate-500">{{ $totalPerms }}</p>
                                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Total</p>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>

            {{-- ── SECCIÓN FULL-WIDTH: Permisos Granulares ─────────────────── --}}
            @if($role)
            <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/30 flex items-center justify-between flex-wrap gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-indigo-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-700">Permisos Granulares</h3>
                            <p class="text-[10px] text-slate-400 font-medium">Los permisos del rol aparecen marcados. Agrega extras individualmente o por módulo.</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" wire:click="loadRolePermissions"
                            class="inline-flex items-center gap-1.5 text-[10px] font-black bg-white border border-slate-200 px-3 py-2 rounded-xl text-slate-500 hover:text-indigo-600 hover:border-indigo-200 hover:bg-indigo-50 transition-all uppercase tracking-wider">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Cargar del rol
                        </button>
                        <button type="button" wire:click="$set('selectedPermissions', [])"
                            class="inline-flex items-center gap-1.5 text-[10px] font-black bg-white border border-slate-200 px-3 py-2 rounded-xl text-slate-400 hover:text-red-500 hover:border-red-200 hover:bg-red-50 transition-all uppercase tracking-wider">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            Limpiar extras
                        </button>
                    </div>
                </div>

                <div class="divide-y divide-slate-100">
                    @php $rolePerms = $this->rolePermissions; @endphp
                    @foreach($this->groupedPermissions as $group)
                        @php
                            $modulePermNames   = collect($group['permissions'])->pluck('name')->toArray();
                            $selectable        = array_values(array_filter($modulePermNames, fn($p) => !in_array($p, $rolePerms)));
                            $allModuleSelected = count($selectable) > 0
                                && count(array_diff($selectable, $selectedPermissions)) === 0;
                            $activeCount       = count(array_intersect($modulePermNames, array_merge($rolePerms, $selectedPermissions)));
                        @endphp
                        <div class="flex items-start hover:bg-slate-50/60 transition-colors">

                            {{-- Etiqueta del módulo (columna fija izquierda) --}}
                            <div class="w-44 shrink-0 flex flex-col justify-between gap-2 px-6 py-4 border-r border-slate-100">
                                <div>
                                    <p class="text-[11px] font-black text-slate-700 uppercase tracking-wide leading-tight">
                                        {{ $group['label'] }}
                                    </p>
                                    <p class="text-[9px] text-slate-400 font-medium mt-0.5">
                                        {{ $activeCount }} / {{ count($group['permissions']) }} activos
                                    </p>
                                </div>
                                @if(count($selectable) > 0)
                                    <button type="button"
                                        wire:click="toggleModule({{ json_encode($modulePermNames) }})"
                                        class="self-start text-[9px] font-black uppercase tracking-wider px-2 py-1 rounded-lg border transition-all
                                            {{ $allModuleSelected
                                                ? 'bg-red-50 border-red-100 text-red-500 hover:bg-red-100'
                                                : 'bg-slate-100 border-slate-200 text-slate-400 hover:bg-indigo-50 hover:border-indigo-200 hover:text-indigo-600' }}">
                                        {{ $allModuleSelected ? '− Quitar' : '+ Todos' }}
                                    </button>
                                @endif
                            </div>

                            {{-- Permisos en fila (flex-wrap) --}}
                            <div class="flex-1 flex flex-wrap gap-2 px-5 py-4">
                                @foreach($group['permissions'] as $perm)
                                    @php
                                        $fromRole   = in_array($perm['name'], $rolePerms);
                                        $isSelected = in_array($perm['name'], $selectedPermissions);
                                        $isSection  = ($perm['type'] ?? null) === 'section';
                                    @endphp
                                    <label class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border text-xs font-semibold transition-all select-none
                                        {{ $fromRole
                                            ? ($isSection
                                                ? 'bg-slate-900/5 border-slate-300 text-slate-700 cursor-default'
                                                : 'bg-indigo-50 border-indigo-200 text-indigo-600 cursor-default')
                                            : ($isSelected
                                                ? ($isSection
                                                    ? 'bg-slate-800 border-slate-800 text-white cursor-pointer hover:bg-slate-700 hover:border-slate-700'
                                                    : 'bg-emerald-50 border-emerald-300 text-emerald-700 cursor-pointer hover:border-emerald-400')
                                                : ($isSection
                                                    ? 'bg-slate-50 border-slate-300 text-slate-600 cursor-pointer hover:border-slate-500 hover:bg-slate-100'
                                                    : 'bg-white border-slate-200 text-slate-500 cursor-pointer hover:border-indigo-300 hover:bg-indigo-50/40 hover:text-indigo-600')) }}">
                                        @if($fromRole)
                                            <svg class="w-3.5 h-3.5 shrink-0 text-indigo-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                        @else
                                            <input type="checkbox"
                                                wire:model.live="selectedPermissions"
                                                value="{{ $perm['name'] }}"
                                                class="w-3.5 h-3.5 shrink-0 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500/20 cursor-pointer">
                                        @endif
                                        {{ $perm['actionLabel'] }}
                                        @if($isSection)
                                            <span class="text-[8px] font-black uppercase tracking-wider opacity-60">Secci&oacute;n</span>
                                        @endif
                                    </label>
                                @endforeach
                            </div>

                        </div>
                    @endforeach
                </div>
            </div>
            @endif

        </form>
    </div>
</div>

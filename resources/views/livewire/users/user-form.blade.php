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

    <div class="max-w-5xl mx-auto p-4 sm:p-6 lg:p-8">
        <form wire:submit="save" class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8">

            {{-- ── COLUMNA IZQUIERDA: Identidad y Acceso (7 cols) ─────────── --}}
            <div class="lg:col-span-7 space-y-6 lg:space-y-8">
                
                {{-- Card: Datos Personales --}}
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
                            ctx.strokeStyle = '#312e81'; // Indigo 900
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
                            <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl animate-in fade-in duration-300">
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
                            <div class="bg-slate-50 rounded-3xl border-2 border-dashed border-slate-200 overflow-hidden cursor-crosshair group hover:border-indigo-300 transition-colors" style="touch-action: none;">
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

            {{-- ── COLUMNA DERECHA: Privilegios y Estatus (5 cols) ─────────── --}}
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
                    </div>
                </div>

                {{-- Card: Permisos Granulares --}}
                @if($role)
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/30 flex items-center justify-between">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Privilegios</h3>
                        <button type="button" wire:click="loadRolePermissions"
                            class="text-[9px] font-black bg-white border border-slate-200 px-2 py-1 rounded-lg text-slate-400 hover:text-indigo-600 hover:border-indigo-100 transition-all uppercase tracking-tighter">Reset</button>
                    </div>
                    <div class="p-6 lg:p-8">
                        <div class="space-y-6 max-h-[600px] overflow-y-auto pr-2 custom-scrollbar">
                            @php $rolePerms = $this->rolePermissions; @endphp
                            @foreach($this->groupedPermissions as $group)
                                @php
                                    $modulePermNames = collect($group['permissions'])->pluck('name')->toArray();
                                    $selectable      = array_values(array_filter($modulePermNames, fn($p) => !in_array($p, $rolePerms)));
                                    $allModuleSelected = count($selectable) > 0
                                        && count(array_diff($selectable, $selectedPermissions)) === 0;
                                @endphp
                                <div class="space-y-2">
                                    {{-- Cabecera del módulo con toggle "Todos" --}}
                                    <div class="flex items-center justify-between pb-1 border-b border-indigo-50">
                                        <h4 class="text-[10px] font-black text-indigo-500 uppercase tracking-[0.2em]">
                                            {{ $group['label'] }}
                                        </h4>
                                        @if(count($selectable) > 0)
                                            <button type="button"
                                                wire:click="toggleModule({{ json_encode($modulePermNames) }})"
                                                class="flex items-center gap-1.5 text-[9px] font-black uppercase tracking-wider transition-colors px-2 py-1 rounded-lg border
                                                    {{ $allModuleSelected
                                                        ? 'bg-indigo-50 border-indigo-100 text-indigo-600 hover:bg-red-50 hover:border-red-100 hover:text-red-500'
                                                        : 'bg-slate-50 border-slate-200 text-slate-400 hover:bg-indigo-50 hover:border-indigo-100 hover:text-indigo-600' }}">
                                                @if($allModuleSelected)
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    Quitar todos
                                                @else
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                    Todos
                                                @endif
                                            </button>
                                        @endif
                                    </div>

                                    {{-- Permisos del módulo --}}
                                    <div class="grid grid-cols-2 gap-2">
                                        @foreach($group['permissions'] as $perm)
                                            @php
                                                $fromRole   = in_array($perm['name'], $rolePerms);
                                                $isSelected = in_array($perm['name'], $selectedPermissions);
                                            @endphp
                                            <label class="flex items-center gap-2 p-2 rounded-xl border transition-all
                                                {{ $fromRole
                                                    ? 'bg-indigo-50/40 border-indigo-100/60 cursor-default'
                                                    : ($isSelected
                                                        ? 'bg-emerald-50/40 border-emerald-200 cursor-pointer hover:border-emerald-300'
                                                        : 'bg-slate-50/30 border-slate-100 cursor-pointer hover:border-indigo-200') }}">
                                                @if($fromRole)
                                                    {{-- Permiso heredado del rol: solo visual, sin wire:model --}}
                                                    <input type="checkbox" checked disabled
                                                        class="w-3.5 h-3.5 rounded-md border-indigo-300 text-indigo-400 cursor-default">
                                                @else
                                                    <input type="checkbox"
                                                        wire:model.live="selectedPermissions"
                                                        value="{{ $perm['name'] }}"
                                                        class="w-3.5 h-3.5 rounded-md border-slate-300 text-indigo-600 focus:ring-indigo-500/20 cursor-pointer">
                                                @endif
                                                <span class="text-[10px] font-bold truncate {{ $fromRole ? 'text-indigo-500' : 'text-slate-600' }}"
                                                    title="{{ $perm['name'] }}">
                                                    {{ $perm['actionLabel'] }}
                                                    @if($fromRole)
                                                        <span class="text-indigo-300 font-normal">(rol)</span>
                                                    @endif
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                {{-- Card: Estatus --}}
                <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
                    <div class="p-6 lg:p-8 space-y-6">
                        <div class="flex items-center justify-between p-4 rounded-2xl bg-emerald-50/50 border border-emerald-100/50">
                            <div>
                                <p class="text-xs font-bold text-slate-700">Estado del Usuario</p>
                                <p class="text-[10px] text-emerald-600 uppercase font-bold tracking-wider">¿Puede Acceder?</p>
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

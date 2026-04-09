<div class="max-w-3xl">
    <div class="flex items-center gap-3 mb-6">
        <a wire:navigate href="{{ route('users.index') }}" class="text-gray-400 hover:text-gray-600 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="text-xl font-medium text-gray-900">
            {{ $user?->exists ? 'Editar usuario' : 'Nuevo usuario' }}
        </h1>
    </div>

    <form wire:submit="save" class="space-y-5">
        {{-- Datos personales --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">Datos personales</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">Nombre completo *</label>
                    <input wire:model="name" type="text"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">Correo electrónico *</label>
                    <input wire:model="email" type="email"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">
                        {{ $user?->exists ? 'Nueva contraseña (dejar vacío para no cambiar)' : 'Contraseña *' }}
                    </label>
                    <input wire:model="password" type="password"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    @error('password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Confirmar contraseña</label>
                    <input wire:model="password_confirmation" type="password"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>
            </div>
        </div>

        {{-- Empresa y rol --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h2 class="text-sm font-medium text-gray-700 border-b border-gray-100 pb-3">Empresa y acceso</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Empresa</label>
                    <select wire:model.live="company_id"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        <option value="">— Sin empresa —</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}" {{ $company_id == $company->id ? 'selected' : '' }}>
                                {{ $company->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('company_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Sucursal</label>
                    <select wire:model="branch_id"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                        {{ !$company_id ? 'disabled' : '' }}>
                        <option value="">— Sin sucursal —</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ $branch_id == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('branch_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">Rol *</label>
                    <select wire:model.live="role"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        <option value="">— Seleccionar rol —</option>
                        @foreach($roles as $r)
                            <option value="{{ $r->name }}" {{ $role === $r->name ? 'selected' : '' }}>
                                {{ ucfirst($r->name) }}
                            </option>
                        @endforeach
                    </select>
                    @error('role') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Permisos granulares --}}
        @if($role)
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <div>
                    <h2 class="text-sm font-medium text-gray-700">Permisos granulares</h2>
                    <p class="text-xs text-gray-400 mt-0.5">
                        Los permisos en <span class="text-indigo-500 font-medium">azul</span> son del rol.
                        Puedes agregar permisos adicionales marcando las casillas en blanco.
                    </p>
                </div>
                <button type="button" wire:click="loadRolePermissions"
                    class="text-xs px-3 py-1.5 border border-gray-200 rounded-lg hover:bg-gray-50 transition text-gray-600 whitespace-nowrap">
                    Cargar permisos del rol
                </button>
            </div>

            @php $rolePerms = $this->rolePermissions; @endphp

            <div class="space-y-5">
                @foreach($this->groupedPermissions as $group)
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">
                        {{ $group['label'] }}
                    </p>
                    <div class="flex flex-wrap gap-x-4 gap-y-2">
                        @foreach($group['permissions'] as $perm)
                            @php
                                $fromRole   = in_array($perm['name'], $rolePerms);
                                $isSelected = in_array($perm['name'], $selectedPermissions);
                                $checked    = $fromRole || $isSelected;
                            @endphp
                            <label class="flex items-center gap-2 cursor-pointer group
                                {{ $fromRole ? 'opacity-70' : '' }}">
                                <input
                                    type="checkbox"
                                    wire:model="selectedPermissions"
                                    value="{{ $perm['name'] }}"
                                    {{ $fromRole ? 'disabled' : '' }}
                                    {{ $checked ? 'checked' : '' }}
                                    class="w-4 h-4 rounded border-gray-300
                                        {{ $fromRole ? 'text-indigo-400 cursor-not-allowed' : 'text-indigo-600 cursor-pointer' }}">
                                <span class="text-xs
                                    {{ $fromRole ? 'text-indigo-500 font-medium' : 'text-gray-600 group-hover:text-gray-900' }}">
                                    {{ $perm['actionLabel'] }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Firma digital registrada --}}
        @if($user?->exists || auth()->id() === $user?->id)
        <div
            x-data="{
                drawing: false,
                initCanvas() {
                    const canvas = this.$refs.sigCanvasProfile;
                    if (!canvas || canvas._initialized) return;
                    canvas._initialized = true;
                    const ctx = canvas.getContext('2d');
                    ctx.strokeStyle = '#1e293b';
                    ctx.lineWidth   = 2.5;
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
                            ctx.beginPath();
                            ctx.moveTo(mid01.x, mid01.y);
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
                    canvas.addEventListener('mousedown',  start);
                    canvas.addEventListener('mousemove',  move);
                    canvas.addEventListener('mouseup',    stop);
                    canvas.addEventListener('mouseleave', stop);
                    canvas.addEventListener('touchstart', start, { passive: false });
                    canvas.addEventListener('touchmove',  move,  { passive: false });
                    canvas.addEventListener('touchend',   stop);
                },
                clearCanvas() {
                    const canvas = this.$refs.sigCanvasProfile;
                    if (canvas) canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
                    $wire.set('signatureData', '');
                }
            }"
            x-init="initCanvas()"
            class="bg-white rounded-xl border border-gray-200 p-5 space-y-4"
        >
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <div>
                    <h2 class="text-sm font-medium text-gray-700">Firma digital</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Se usará al autorizar documentos. Puedes dibujarla o actualizarla cuando quieras.</p>
                </div>
                @if($user?->signature)
                    <span class="text-[10px] text-emerald-600 bg-emerald-50 border border-emerald-100 px-2 py-0.5 rounded-full font-medium">Registrada</span>
                @else
                    <span class="text-[10px] text-amber-600 bg-amber-50 border border-amber-100 px-2 py-0.5 rounded-full font-medium">Sin firma</span>
                @endif
            </div>

            @if(session('signatureSuccess'))
                <div class="text-xs text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg px-3 py-2">
                    {{ session('signatureSuccess') }}
                </div>
            @endif

            {{-- Firma actual guardada --}}
            @if($user?->signature)
            <div>
                <p class="text-xs text-gray-500 mb-2">Firma actual <span class="text-gray-400">(guardada el {{ $user->signature_updated_at?->format('d/m/Y H:i') ?? '—' }})</span>:</p>
                <div class="border border-gray-200 rounded-xl bg-gray-50 p-3 flex items-center justify-center" style="min-height: 72px;">
                    <img src="{{ $user->signature }}" alt="Firma registrada" class="max-h-16 object-contain">
                </div>
            </div>
            @endif

            {{-- Canvas para dibujar nueva firma --}}
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="text-xs font-medium text-gray-600">
                        {{ $user?->signature ? 'Nueva firma (reemplazará la actual)' : 'Dibuja tu firma' }}
                    </label>
                    <button type="button" @click="clearCanvas()"
                        class="text-[10px] text-gray-400 hover:text-red-500 underline transition">Limpiar</button>
                </div>
                <div class="border-2 border-dashed border-gray-300 rounded-xl overflow-hidden bg-gray-50 cursor-crosshair select-none hover:border-indigo-300 transition"
                     style="touch-action: none;">
                    <canvas x-ref="sigCanvasProfile" width="800" height="160"
                        class="w-full block"
                        style="touch-action: none;"></canvas>
                </div>
                @error('signatureData') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                <p class="text-[10px] text-gray-400 mt-1">Traza tu firma con el ratón o dedo.</p>
            </div>

            <div class="flex gap-3">
                <button type="button" wire:click="saveSignature"
                    class="px-4 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition">
                    Guardar firma
                </button>
                @if($user?->signature)
                <button type="button" wire:click="clearSignature"
                    wire:confirm="¿Eliminar la firma registrada?"
                    class="px-4 py-2 text-sm border border-red-200 text-red-600 hover:bg-red-50 rounded-lg font-medium transition">
                    Eliminar firma
                </button>
                @endif
            </div>
        </div>
        @endif

        {{-- Estado --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <label class="flex items-center gap-3 cursor-pointer">
                <input wire:model="is_active" type="checkbox" class="w-4 h-4 rounded text-indigo-600"
                    {{ $is_active ? 'checked' : '' }}>
                <div>
                    <p class="text-sm font-medium text-gray-800">Usuario activo</p>
                    <p class="text-xs text-gray-400">Los usuarios inactivos no pueden iniciar sesión</p>
                </div>
            </label>
        </div>

        <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pb-6">
            <a wire:navigate href="{{ route('users.index') }}"
                class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                Cancelar
            </a>
            <button type="submit"
                class="px-5 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition">
                {{ $user?->exists ? 'Guardar cambios' : 'Crear usuario' }}
            </button>
        </div>
    </form>
</div>

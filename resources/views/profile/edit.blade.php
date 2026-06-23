<x-app-layout>
    @php
        $profileImage = $employee?->photo_url;

        if (! $profileImage && $user->avatar) {
            $profileImage = filter_var($user->avatar, FILTER_VALIDATE_URL)
                ? $user->avatar
                : \Illuminate\Support\Facades\Storage::url($user->avatar);
        }

        $initials = strtoupper(substr($employee?->first_name ?? $user->name, 0, 1) . substr($employee?->last_name ?? '', 0, 1));
        $initials = trim($initials) !== '' ? $initials : strtoupper(substr($user->name, 0, 2));
        $employeeLocation = collect([$employee?->city, $employee?->state, $employee?->country])->filter()->join(', ');
    @endphp

    <div class="min-h-screen bg-slate-50/70 -m-4 lg:-m-6">
        <div class="border-b border-slate-200 bg-white/90 px-4 py-5 backdrop-blur sm:px-6 lg:px-8">
            <div class="mx-auto flex max-w-7xl flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center gap-4">
                    @if ($profileImage)
                        <img src="{{ $profileImage }}" alt="{{ $user->name }}" class="h-16 w-16 rounded-2xl object-cover ring-4 ring-white shadow-sm">
                    @else
                        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-indigo-50 text-lg font-black text-indigo-600 ring-1 ring-indigo-100">
                            {{ $initials }}
                        </div>
                    @endif
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400">Cuenta de usuario</p>
                        <h1 class="text-2xl font-black tracking-tight text-slate-900">Perfil y seguridad</h1>
                        <p class="mt-1 text-sm text-slate-500">
                            {{ $employee?->position?->name ?? 'Usuario del sistema' }}
                            @if ($employee?->department)
                                <span class="mx-1 text-slate-300">/</span>{{ $employee->department->name }}
                            @endif
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 sm:flex sm:items-center">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">Empleado</p>
                        <p class="mt-1 max-w-36 truncate text-xs font-bold text-slate-700">{{ $employee?->employee_number ? '#'.$employee->employee_number : 'Sin numero' }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">Empresa</p>
                        <p class="mt-1 max-w-36 truncate text-xs font-bold text-slate-700">{{ $user->company?->name ?? 'Sin empresa' }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">Sucursal</p>
                        <p class="mt-1 max-w-36 truncate text-xs font-bold text-slate-700">{{ $employee?->branch?->name ?? $user->branch?->name ?? 'Todas' }}</p>
                    </div>
                    <div class="rounded-2xl border {{ $user->hasTwoFactorEnabled() ? 'border-emerald-100 bg-emerald-50 text-emerald-700' : 'border-amber-100 bg-amber-50 text-amber-700' }} px-4 py-3">
                        <p class="text-[9px] font-black uppercase tracking-widest opacity-70">2FA</p>
                        <p class="mt-1 text-xs font-black">{{ $user->hasTwoFactorEnabled() ? 'Activo' : 'Sin activar' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mx-auto grid max-w-7xl grid-cols-1 gap-6 p-4 sm:p-6 lg:grid-cols-12 lg:p-8"
             x-data="{ active: 'profile', menuOpen: true }">
            <aside class="lg:col-span-4 xl:col-span-3">
                <div class="sticky top-6 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <button type="button"
                            x-on:click="menuOpen = !menuOpen"
                            class="flex w-full items-center justify-between border-b border-slate-100 px-5 py-4 text-left">
                        <span>
                            <span class="block text-sm font-black text-slate-800">Ajustes</span>
                            <span class="block text-[10px] font-bold uppercase tracking-widest text-slate-400">Perfil y seguridad</span>
                        </span>
                        <svg class="h-4 w-4 text-slate-400 transition-transform" :class="{ 'rotate-90': menuOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>

                    <div x-cloak x-show="menuOpen" x-collapse class="p-2">
                        <button type="button" x-on:click="active = 'profile'"
                                class="flex w-full items-center gap-3 rounded-2xl px-4 py-3 text-left transition"
                                :class="active === 'profile' ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50'">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/80 ring-1 ring-current/10">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </span>
                            <span>
                                <span class="block text-xs font-black">Informacion personal</span>
                                <span class="block text-[10px] font-semibold opacity-60">Nombre, firma y contexto</span>
                            </span>
                        </button>

                        <button type="button" x-on:click="active = 'security'"
                                class="mt-1 flex w-full items-center gap-3 rounded-2xl px-4 py-3 text-left transition"
                                :class="active === 'security' ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50'">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/80 ring-1 ring-current/10">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0-1.105.895-2 2-2s2 .895 2 2-.895 2-2 2-2-.895-2-2zm-6 8V9a6 6 0 1112 0v10H6z"/></svg>
                            </span>
                            <span>
                                <span class="block text-xs font-black">Seguridad</span>
                                <span class="block text-[10px] font-semibold opacity-60">Contrasena y 2FA</span>
                            </span>
                        </button>

                        @if (session('status') === 'account-delete-disabled')
                            <div class="mt-3 rounded-2xl border border-amber-100 bg-amber-50 px-4 py-3 text-xs font-bold text-amber-700">
                                La cuenta solo puede ser eliminada por administracion.
                            </div>
                        @endif
                    </div>
                </div>
            </aside>

            <main class="space-y-6 lg:col-span-8 xl:col-span-9">
                <section x-show="active === 'profile'" x-cloak class="space-y-6">
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                        @include('profile.partials.update-profile-information-form')
                    </div>

                    @include('profile.partials.signature-form')

                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                        <h2 class="text-lg font-black text-slate-900">Informacion general</h2>
                        <p class="mt-1 text-sm text-slate-500">Datos asignados por administracion. Si algo no coincide, solicita el ajuste al responsable.</p>

                        <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Correo</p>
                                <p class="mt-1 truncate text-sm font-bold text-slate-700">{{ $user->email }}</p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Roles</p>
                                <p class="mt-1 text-sm font-bold text-slate-700">{{ $user->roles->pluck('name')->join(', ') ?: 'Sin rol' }}</p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Estado</p>
                                <p class="mt-1 text-sm font-bold {{ $user->is_active ? 'text-emerald-600' : 'text-rose-600' }}">{{ $user->is_active ? 'Activo' : 'Inactivo' }}</p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Firma</p>
                                <p class="mt-1 text-sm font-bold {{ $user->signature ? 'text-emerald-600' : 'text-slate-500' }}">{{ $user->signature ? 'Registrada' : 'Sin registrar' }}</p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Telefono</p>
                                <p class="mt-1 truncate text-sm font-bold text-slate-700">{{ $employee?->phone ?? 'Sin telefono' }}</p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Edad</p>
                                <p class="mt-1 truncate text-sm font-bold text-slate-700">{{ $employee?->age ?? ($user->birth_date ? floor($user->birth_date->diffInYears(now())) : 'Sin fecha') }}</p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Genero</p>
                                <p class="mt-1 truncate text-sm font-bold text-slate-700">{{ \App\Models\HrEmployee::GENDERS[$employee?->gender ?? $user->gender ?? ''] ?? 'Sin definir' }}</p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Puesto</p>
                                <p class="mt-1 truncate text-sm font-bold text-slate-700">{{ $employee?->position?->name ?? 'Sin puesto' }}</p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Departamento</p>
                                <p class="mt-1 truncate text-sm font-bold text-slate-700">{{ $employee?->department?->name ?? 'Sin departamento' }}</p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Jefe directo</p>
                                <p class="mt-1 truncate text-sm font-bold text-slate-700">{{ $employee?->supervisor?->full_name ?? 'Sin asignar' }}</p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Ingreso</p>
                                <p class="mt-1 text-sm font-bold text-slate-700">
                                    {{ $employee?->hire_date?->format('d/m/Y') ?? 'Sin fecha' }}
                                    @if ($employee?->hire_date)
                                        <span class="text-xs text-slate-400">({{ $employee->antiquity_years }} anos)</span>
                                    @endif
                                </p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Contrato</p>
                                <p class="mt-1 truncate text-sm font-bold text-slate-700">{{ \App\Models\HrEmployee::CONTRACT_TYPES[$employee?->contract_type] ?? $employee?->contract_type ?? 'Sin contrato' }}</p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Turno</p>
                                <p class="mt-1 truncate text-sm font-bold text-slate-700">{{ \App\Models\HrEmployee::WORK_SHIFTS[$employee?->work_shift] ?? $employee?->work_shift ?? 'Sin turno' }}</p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Ubicacion</p>
                                <p class="mt-1 truncate text-sm font-bold text-slate-700">{{ $employeeLocation ?: 'Sin ubicacion' }}</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section x-show="active === 'security'" x-cloak class="space-y-6">
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                        @include('profile.partials.update-password-form')
                    </div>

                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                        @include('profile.partials.two-factor-authentication-form')
                    </div>
                </section>
            </main>
        </div>
    </div>
</x-app-layout>

@php
    $nameParts = preg_split('/\s+/', trim($user->name), 3);
    $firstName = old('first_name', $employee?->first_name ?? ($nameParts[0] ?? $user->name));
    $lastName = old('last_name', $employee?->last_name ?? ($nameParts[1] ?? ''));
    $secondLastName = old('second_last_name', $employee?->second_last_name ?? ($nameParts[2] ?? ''));
    $birthDate = old('birth_date', $employee?->birth_date?->format('Y-m-d') ?? $user->birth_date?->format('Y-m-d') ?? '');
    $gender = old('gender', $employee?->gender ?? $user->gender ?? '');
@endphp

<section>
    <header>
        <h2 class="text-lg font-black text-slate-900">Informacion personal</h2>
        <p class="mt-1 text-sm text-slate-500">Actualiza tu nombre de trabajador. El correo de acceso lo administra la empresa.</p>
    </header>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('patch')

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
                <label for="first_name" class="mb-1.5 ml-1 block text-[10px] font-black uppercase tracking-widest text-slate-400">Nombre</label>
                <input id="first_name" name="first_name" type="text" value="{{ $firstName }}" required autofocus autocomplete="given-name"
                       class="w-full rounded-2xl border-slate-200 bg-slate-50/60 px-4 py-3 text-sm font-bold text-slate-800 transition focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/5">
                <x-input-error class="mt-2" :messages="$errors->get('first_name')" />
            </div>

            <div>
                <label for="last_name" class="mb-1.5 ml-1 block text-[10px] font-black uppercase tracking-widest text-slate-400">Apellido paterno</label>
                <input id="last_name" name="last_name" type="text" value="{{ $lastName }}" required autocomplete="family-name"
                       class="w-full rounded-2xl border-slate-200 bg-slate-50/60 px-4 py-3 text-sm font-bold text-slate-800 transition focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/5">
                <x-input-error class="mt-2" :messages="$errors->get('last_name')" />
            </div>

            <div>
                <label for="second_last_name" class="mb-1.5 ml-1 block text-[10px] font-black uppercase tracking-widest text-slate-400">Apellido materno</label>
                <input id="second_last_name" name="second_last_name" type="text" value="{{ $secondLastName }}" autocomplete="additional-name"
                       class="w-full rounded-2xl border-slate-200 bg-slate-50/60 px-4 py-3 text-sm font-bold text-slate-800 transition focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/5">
                <x-input-error class="mt-2" :messages="$errors->get('second_last_name')" />
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label for="birth_date" class="mb-1.5 ml-1 block text-[10px] font-black uppercase tracking-widest text-slate-400">Fecha de nacimiento</label>
                <input id="birth_date" name="birth_date" type="date" value="{{ $birthDate }}"
                       class="w-full rounded-2xl border-slate-200 bg-slate-50/60 px-4 py-3 text-sm font-bold text-slate-800 transition focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/5">
                <x-input-error class="mt-2" :messages="$errors->get('birth_date')" />
            </div>

            <div>
                <label for="gender" class="mb-1.5 ml-1 block text-[10px] font-black uppercase tracking-widest text-slate-400">Genero</label>
                <select id="gender" name="gender"
                        class="w-full rounded-2xl border-slate-200 bg-slate-50/60 px-4 py-3 text-sm font-bold text-slate-800 transition focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/5">
                    <option value="">Seleccionar</option>
                    @foreach(\App\Models\HrEmployee::GENDERS as $key => $label)
                        <option value="{{ $key }}" @selected($gender === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('gender')" />
            </div>
        </div>

        <div>
            <label class="mb-1.5 ml-1 block text-[10px] font-black uppercase tracking-widest text-slate-400">Correo electronico</label>
            <div class="flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-slate-100/70 px-4 py-3">
                <span class="truncate text-sm font-bold text-slate-600">{{ $user->email }}</span>
                <span class="rounded-full bg-white px-3 py-1 text-[10px] font-black uppercase tracking-widest text-slate-400 ring-1 ring-slate-200">Solo lectura</span>
            </div>
            <p class="mt-2 text-xs font-semibold text-slate-400">Para cambiar el correo, solicita el ajuste a un administrador.</p>
        </div>

        <div class="flex items-center gap-4 pt-2">
            <button type="submit" class="inline-flex items-center rounded-xl bg-indigo-600 px-5 py-3 text-xs font-black uppercase tracking-widest text-white shadow-lg shadow-indigo-500/20 transition hover:bg-indigo-700 active:scale-95">
                Guardar perfil
            </button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="text-sm font-semibold text-emerald-600">
                    Perfil actualizado.
                </p>
            @endif
        </div>
    </form>
</section>

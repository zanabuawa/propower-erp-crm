<section>
    <header>
        <h2 class="text-lg font-black text-slate-900">Contrasena</h2>
        <p class="mt-1 text-sm text-slate-500">Usa una contrasena larga y unica para proteger el acceso al sistema.</p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 grid grid-cols-1 gap-5 lg:grid-cols-3">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password" class="mb-1.5 ml-1 block text-[10px] font-black uppercase tracking-widest text-slate-400">Actual</label>
            <input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password"
                   class="w-full rounded-2xl border-slate-200 bg-slate-50/60 px-4 py-3 text-sm font-bold text-slate-800 transition focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/5">
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <label for="update_password_password" class="mb-1.5 ml-1 block text-[10px] font-black uppercase tracking-widest text-slate-400">Nueva</label>
            <input id="update_password_password" name="password" type="password" autocomplete="new-password"
                   class="w-full rounded-2xl border-slate-200 bg-slate-50/60 px-4 py-3 text-sm font-bold text-slate-800 transition focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/5">
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <label for="update_password_password_confirmation" class="mb-1.5 ml-1 block text-[10px] font-black uppercase tracking-widest text-slate-400">Confirmar</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"
                   class="w-full rounded-2xl border-slate-200 bg-slate-50/60 px-4 py-3 text-sm font-bold text-slate-800 transition focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/5">
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4 lg:col-span-3">
            <button type="submit" class="inline-flex items-center rounded-xl bg-slate-900 px-5 py-3 text-xs font-black uppercase tracking-widest text-white shadow-lg shadow-slate-300 transition hover:bg-slate-700 active:scale-95">
                Actualizar contrasena
            </button>

            @if (session('status') === 'password-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="text-sm font-semibold text-emerald-600">
                    Contrasena actualizada.
                </p>
            @endif
        </div>
    </form>
</section>

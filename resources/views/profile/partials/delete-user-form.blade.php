<section class="space-y-6">
    <header>
        <h2 class="text-lg font-black text-rose-700">Eliminar cuenta</h2>
        <p class="mt-1 text-sm text-slate-500">
            Esta accion elimina el acceso del usuario. En un ERP operativo normalmente es preferible desactivar el usuario desde administracion.
        </p>
    </header>

    <button type="button"
            x-data=""
            x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
            class="inline-flex items-center rounded-xl bg-rose-600 px-5 py-3 text-xs font-black uppercase tracking-widest text-white shadow-lg shadow-rose-500/20 transition hover:bg-rose-700 active:scale-95">
        Eliminar cuenta
    </button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-black text-slate-900">Confirmar eliminacion</h2>

            <p class="mt-2 text-sm text-slate-600">
                Escribe tu contrasena para confirmar. Esta operacion no se recomienda si el usuario tiene registros historicos ligados al sistema.
            </p>

            <div class="mt-6">
                <label for="password" class="sr-only">Contrasena</label>
                <input id="password" name="password" type="password"
                       class="block w-full rounded-2xl border-slate-200 bg-slate-50/60 px-4 py-3 text-sm font-bold text-slate-800 focus:border-rose-500 focus:bg-white focus:ring-4 focus:ring-rose-500/5"
                       placeholder="Contrasena actual">
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" x-on:click="$dispatch('close')" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-black uppercase tracking-widest text-slate-500 hover:bg-slate-50">
                    Cancelar
                </button>

                <button type="submit" class="rounded-xl bg-rose-600 px-4 py-2 text-xs font-black uppercase tracking-widest text-white hover:bg-rose-700">
                    Eliminar
                </button>
            </div>
        </form>
    </x-modal>
</section>

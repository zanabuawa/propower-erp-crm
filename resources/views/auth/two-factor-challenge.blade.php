<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-zinc-950 px-4 py-10">
        <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl p-8">
            <div class="mb-7">
                <p class="text-[10px] font-black text-red-600 uppercase tracking-[0.24em] mb-2">Verificacion requerida</p>
                <h1 class="text-2xl font-black text-zinc-900 uppercase tracking-tight">Autenticacion en dos pasos</h1>
                <p class="text-sm text-zinc-500 mt-3 leading-relaxed">
                    Ingresa el codigo de 6 digitos de tu app autenticadora para continuar.
                </p>
            </div>

            <form method="POST" action="{{ route('two-factor.verify') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="code" class="block text-[10px] font-black text-zinc-400 uppercase tracking-[0.2em] mb-1.5 ml-1">Codigo de autenticacion</label>
                    <input id="code" name="code" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code" autofocus
                        class="w-full px-4 py-3 bg-zinc-100 border border-zinc-200 rounded-xl text-black focus:bg-white focus:ring-4 focus:ring-red-600/5 focus:border-red-600 transition-all duration-200 font-black text-center text-2xl tracking-[0.35em]"
                        placeholder="000000">
                    <x-input-error :messages="$errors->get('code')" class="mt-2" />
                </div>

                <div class="border-t border-zinc-100 pt-5">
                    <label for="recovery_code" class="block text-[10px] font-black text-zinc-400 uppercase tracking-[0.2em] mb-1.5 ml-1">Codigo de recuperacion</label>
                    <input id="recovery_code" name="recovery_code" type="text" autocomplete="off"
                        class="w-full px-4 py-3 bg-zinc-100 border border-zinc-200 rounded-xl text-black focus:bg-white focus:ring-4 focus:ring-red-600/5 focus:border-red-600 transition-all duration-200 font-bold text-sm"
                        placeholder="Usalo solo si perdiste acceso a tu app">
                    <x-input-error :messages="$errors->get('recovery_code')" class="mt-2" />
                </div>

                <button type="submit" class="w-full bg-red-600 hover:bg-black text-white font-black py-4 rounded-xl transition-all duration-300 shadow-xl shadow-red-600/20 hover:shadow-black/20 active:scale-[0.98] uppercase tracking-[0.2em] text-xs">
                    Verificar acceso
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="{{ route('login') }}" class="text-[10px] font-black text-zinc-400 hover:text-red-600 uppercase tracking-widest transition-colors">
                    Volver al inicio de sesion
                </a>
            </div>
        </div>
    </div>
</x-guest-layout>

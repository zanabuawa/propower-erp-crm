<x-guest-layout>
    <div class="fixed inset-0 flex flex-col lg:flex-row overflow-hidden bg-zinc-950">
        {{-- Fondo con imagen para Móvil y Desktop (Lado Izquierdo) --}}
        <div class="absolute inset-0 lg:w-1/2">
            <img src="https://images.unsplash.com/photo-1558449028-b53a39d100fc?q=80&w=1974&auto=format&fit=crop" 
                 class="absolute inset-0 w-full h-full object-cover opacity-50 mix-blend-luminosity" 
                 alt="Ingeniería Eléctrica">
            <div class="absolute inset-0 bg-gradient-to-tr from-black via-black/80 to-red-900/30"></div>
        </div>

        {{-- LADO IZQUIERDO: Branding (Igual al Login para consistencia) --}}
        <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden">
            <div class="relative z-10 flex flex-col justify-center px-12 xl:px-24 w-full">
                <div class="mb-8 flex items-center gap-3">
                    @php $company = \App\Models\Company::first(); @endphp
                    @if($company?->logo)
                        <img src="{{ Storage::url($company->logo) }}" class="h-auto w-auto object-contain" alt="Logo">
                    @else
                        <x-application-logo class="w-16 h-16 fill-current text-red-600" />
                    @endif
                </div>
                <h1 class="text-4xl xl:text-5xl font-black text-white leading-tight mb-6 uppercase tracking-tighter">
                    Soluciones <br>
                    <span class="text-red-600 underline decoration-red-600/30 underline-offset-8">
                        Calidad Y Trabajo
                    </span>
                </h1>
                <p class="text-lg text-gray-400 mb-10 max-w-lg font-medium">
                    Gestión de seguridad y recuperación de accesos.
                </p>
            </div>

            <div class="absolute bottom-8 left-12 xl:left-24 text-gray-500 text-xs font-bold uppercase tracking-widest">
                © {{ date('Y') }} ProPower Systems.
            </div>
        </div>

        {{-- LADO DERECHO: Formulario --}}
        <div class="w-full lg:w-1/2 flex flex-col items-center justify-center p-6 sm:p-12 lg:bg-white overflow-y-auto relative z-10 backdrop-blur-sm lg:backdrop-blur-none">
            {{-- Logo en móvil --}}
            <div class="lg:hidden flex flex-col items-center mb-8">
                @php $company = \App\Models\Company::first(); @endphp
                @if($company?->logo)
                    <img src="{{ Storage::url($company->logo) }}" class="h-24 w-auto object-contain drop-shadow-2xl" alt="Logo">
                @else
                    <x-application-logo class="w-20 h-20 fill-current text-red-600 drop-shadow-2xl" />
                @endif
            </div>

            <div class="w-full max-w-md bg-white lg:bg-transparent p-8 lg:p-0 rounded-3xl shadow-2xl lg:shadow-none border border-white/10 lg:border-none">
                <div class="text-center lg:text-left mb-8">
                    <h2 class="text-2xl font-extrabold text-zinc-900 tracking-tight leading-none uppercase">
                        Recuperar <span class="text-red-600">Acceso</span>
                    </h2>
                    <p class="text-zinc-500 mt-4 font-medium text-sm leading-relaxed">
                        ¿Olvidaste tu contraseña? No hay problema. Ingresa tu correo y te enviaremos un enlace para generar una nueva.
                    </p>
                </div>

                {{-- Session Status --}}
                <x-auth-session-status class="mb-6" :status="session('status')" />

                <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
                    @csrf

                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-[10px] font-black text-zinc-400 uppercase tracking-[0.2em] mb-1.5 ml-1">Correo registrado</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none transition-colors">
                                <svg class="w-4 h-4 text-zinc-400 group-focus-within:text-red-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206"/>
                                </svg>
                            </div>
                            <input id="email" type="email" name="email" :value="old('email')" required autofocus
                                class="w-full pl-14 pr-4 py-3 bg-zinc-100 border border-zinc-200 rounded-xl text-black focus:bg-white focus:ring-4 focus:ring-red-600/5 focus:border-red-600 transition-all duration-200 placeholder-zinc-400 font-bold text-sm"
                                placeholder="tu@empresa.com">
                        </div>
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <button type="submit" class="w-full bg-red-600 hover:bg-black text-white font-black py-4 rounded-xl transition-all duration-300 shadow-xl shadow-red-600/20 hover:shadow-black/20 active:scale-[0.98] flex items-center justify-center gap-2 uppercase tracking-[0.2em] text-xs">
                        <span>Enviar Enlace</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>

                    <div class="text-center pt-4">
                        <a href="{{ route('login') }}" class="text-[10px] font-black text-zinc-400 hover:text-red-600 uppercase tracking-widest transition-colors flex items-center justify-center gap-2">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                            Volver al inicio
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>

<x-guest-layout>
    <div class="fixed inset-0 flex flex-col lg:flex-row overflow-hidden bg-zinc-950">
        {{-- Fondo con imagen --}}
        <div class="absolute inset-0 lg:w-1/2">
            <img src="https://images.unsplash.com/photo-1558449028-b53a39d100fc?q=80&w=1974&auto=format&fit=crop" 
                 class="absolute inset-0 w-full h-full object-cover opacity-50 mix-blend-luminosity" 
                 alt="Ingeniería Eléctrica">
            <div class="absolute inset-0 bg-gradient-to-tr from-black via-black/80 to-red-900/30"></div>
        </div>

        {{-- LADO IZQUIERDO: Branding --}}
        <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden">
            <div class="relative z-10 flex flex-col justify-center px-12 xl:px-24 w-full">
                <div class="mb-8 flex items-center gap-3">
                    @php $company = \App\Models\Company::first(); @endphp
                    @if($company?->logo)
                        <img src="{{ Storage::url($company->logo) }}" class="h-12 w-auto object-contain" alt="Logo">
                    @else
                        <x-application-logo class="w-16 h-16 fill-current text-red-600" />
                    @endif
                </div>
                <h1 class="text-4xl xl:text-5xl font-black text-white leading-tight mb-6 uppercase tracking-tighter">
                    Soporte <br>
                    <span class="text-red-600 underline decoration-red-600/30 underline-offset-8">
                        Técnico
                    </span>
                </h1>
                <p class="text-lg text-gray-400 mb-10 max-w-lg font-medium">
                    Estamos aquí para ayudarte con cualquier problema de acceso o técnico en la plataforma.
                </p>
            </div>

            <div class="absolute bottom-8 left-12 xl:left-24 text-gray-500 text-xs font-bold uppercase tracking-widest">
                © {{ date('Y') }} ProPower Systems.
            </div>
        </div>

        {{-- LADO DERECHO: Información de Contacto --}}
        <div class="w-full lg:w-1/2 flex flex-col items-center justify-center p-6 sm:p-12 lg:bg-white overflow-y-auto relative z-10 backdrop-blur-sm lg:backdrop-blur-none">
            {{-- Logo en móvil --}}
            <div class="lg:hidden flex flex-col items-center mb-8">
                @if($company?->logo)
                    <img src="{{ Storage::url($company->logo) }}" class="h-24 w-auto object-contain drop-shadow-2xl" alt="Logo">
                @else
                    <x-application-logo class="w-20 h-20 fill-current text-red-600 drop-shadow-2xl" />
                @endif
            </div>

            <div class="w-full max-w-md bg-white p-8 lg:p-0 rounded-3xl shadow-2xl lg:shadow-none border border-white/10 lg:border-none">
                <div class="text-center lg:text-left mb-8">
                    <h2 class="text-2xl font-extrabold text-zinc-900 tracking-tight leading-none uppercase">
                        Contactar <span class="text-red-600">Soporte</span>
                    </h2>
                    <p class="text-zinc-500 mt-4 font-medium text-sm leading-relaxed">
                        Elige el canal de comunicación que prefieras para recibir asistencia inmediata.
                    </p>
                </div>

                <div class="space-y-4">
                    {{-- WhatsApp --}}
                    <a href="https://wa.me/521234567890" target="_blank" class="flex items-center gap-4 p-4 bg-zinc-50 hover:bg-zinc-100 rounded-2xl border border-zinc-100 transition-all group">
                        <div class="w-12 h-12 bg-green-100 text-green-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.438 9.889-9.886.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-zinc-900 uppercase">WhatsApp Directo</h3>
                            <p class="text-xs text-zinc-500 font-bold tracking-tight">Atención inmediata vía chat</p>
                        </div>
                    </a>

                    {{-- Email --}}
                    <a href="mailto:soporte@propower.com" class="flex items-center gap-4 p-4 bg-zinc-50 hover:bg-zinc-100 rounded-2xl border border-zinc-100 transition-all group">
                        <div class="w-12 h-12 bg-red-100 text-red-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-zinc-900 uppercase">Correo Electrónico</h3>
                            <p class="text-xs text-zinc-500 font-bold tracking-tight">soporte@propower.com</p>
                        </div>
                    </a>

                    {{-- Teléfono --}}
                    <a href="tel:+521234567890" class="flex items-center gap-4 p-4 bg-zinc-50 hover:bg-zinc-100 rounded-2xl border border-zinc-100 transition-all group">
                        <div class="w-12 h-12 bg-zinc-200 text-zinc-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-zinc-900 uppercase">Línea Telefónica</h3>
                            <p class="text-xs text-zinc-500 font-bold tracking-tight">(123) 456-7890</p>
                        </div>
                    </a>
                </div>

                <div class="mt-12 text-center">
                    <a href="{{ route('login') }}" class="text-[10px] font-black text-zinc-400 hover:text-red-600 uppercase tracking-widest transition-colors flex items-center justify-center gap-2">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        Volver al inicio
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>

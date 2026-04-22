<x-guest-layout>
    <div class="fixed inset-0 flex flex-col lg:flex-row overflow-hidden bg-white">
        {{-- LADO IZQUIERDO: Branding --}}
        <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden bg-black">
            <img src="https://images.unsplash.com/photo-1581092160562-40aa08e78837?q=80&w=2070&auto=format&fit=crop" 
                 class="absolute inset-0 w-full h-full object-cover opacity-40 mix-blend-luminosity" 
                 alt="Soporte Técnico">
            
            <div class="absolute inset-0 bg-gradient-to-tr from-black via-black/80 to-red-900/20"></div>
            
            <div class="relative z-10 flex flex-col justify-center px-12 xl:px-24 w-full">
                <div class="mb-8 flex items-center gap-3">
                    @php $company = \App\Models\Company::first(); @endphp
                    @if($company?->logo)
                        <img src="{{ Storage::url($company->logo) }}" class="h-12 w-auto object-contain" alt="Logo">
                    @else
                        <x-application-logo class="w-16 h-16 fill-current text-red-600" />
                    @endif
                    <span class="text-white text-3xl font-black tracking-tighter uppercase italic">PRO<span class="text-red-600">POWER</span></span>
                </div>
                <h1 class="text-4xl xl:text-5xl font-black text-white leading-tight mb-6 uppercase tracking-tighter">
                    Centro de <br>
                    <span class="text-red-600 underline decoration-red-600/30 underline-offset-8">
                        Soporte <br> Técnico
                    </span>
                </h1>
                <p class="text-lg text-gray-400 mb-10 max-w-lg font-medium">
                    Estamos aquí para ayudarte a mantener tu operación en marcha 24/7.
                </p>
            </div>

            <div class="absolute bottom-8 left-12 xl:left-24 text-gray-500 text-xs font-bold uppercase tracking-widest">
                © {{ date('Y') }} ProPower Systems.
            </div>
        </div>

        {{-- LADO DERECHO: Contacto --}}
        <div class="w-full lg:w-1/2 flex items-center justify-center p-6 sm:p-12 bg-zinc-50 lg:bg-white overflow-y-auto">
            <div class="w-full max-w-md">
                {{-- Logo en móvil --}}
                <div class="lg:hidden flex flex-col items-center mb-10">
                    @if($company?->logo)
                        <img src="{{ Storage::url($company->logo) }}" class="h-16 w-auto object-contain mb-2" alt="Logo">
                    @else
                        <x-application-logo class="w-14 h-14 fill-current text-red-600" />
                    @endif
                    <span class="text-black text-2xl font-black tracking-tighter uppercase italic">PRO<span class="text-red-600">POWER</span></span>
                </div>

                <div class="text-center lg:text-left mb-10">
                    <h2 class="text-2xl font-extrabold text-zinc-900 tracking-tight leading-none uppercase">
                        ¿Cómo podemos <span class="text-red-600">ayudarte?</span>
                    </h2>
                    <p class="text-zinc-500 mt-4 font-semibold text-sm uppercase tracking-wider">Canales de Atención Directa</p>
                </div>

                <div class="space-y-4">
                    {{-- WhatsApp --}}
                    <a href="https://wa.me/521234567890" target="_blank" 
                       class="flex items-center gap-4 p-5 bg-white border border-zinc-200 rounded-2xl hover:border-red-600 transition-all group shadow-sm">
                        <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.353-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.128.571-.074 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.87 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs font-black text-zinc-400 uppercase tracking-widest mb-1">Respuesta Inmediata</p>
                            <p class="text-base font-bold text-black uppercase italic tracking-tighter">WhatsApp Corporativo</p>
                        </div>
                    </a>

                    {{-- Email --}}
                    <a href="mailto:soporte@propower.com" 
                       class="flex items-center gap-4 p-5 bg-white border border-zinc-200 rounded-2xl hover:border-red-600 transition-all group shadow-sm">
                        <div class="w-12 h-12 rounded-xl bg-zinc-900 flex items-center justify-center text-white group-hover:bg-red-600 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs font-black text-zinc-400 uppercase tracking-widest mb-1">Reporte de Fallos</p>
                            <p class="text-base font-bold text-black uppercase italic tracking-tighter">soporte@propower.com</p>
                        </div>
                    </a>

                    {{-- Phone --}}
                    <div class="flex items-center gap-4 p-5 bg-zinc-100/50 border border-transparent rounded-2xl shadow-inner">
                        <div class="w-12 h-12 rounded-xl bg-white flex items-center justify-center text-zinc-400 border border-zinc-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs font-black text-zinc-400 uppercase tracking-widest mb-1">Central Telefónica</p>
                            <p class="text-base font-bold text-zinc-700 tracking-tighter">+52 (81) 1234 5678</p>
                        </div>
                    </div>
                </div>

                <div class="mt-12 text-center">
                    <a href="{{ route('login') }}" class="text-[10px] font-black text-zinc-400 hover:text-red-600 uppercase tracking-widest transition-colors flex items-center justify-center gap-2">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        Volver al panel principal
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>

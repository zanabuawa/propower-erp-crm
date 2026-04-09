@php
    $company = auth()->check() ? auth()->user()->company : \App\Models\Company::where('is_active', true)->first();
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página no encontrada — {{ $company?->name ?? config('app.name') }}</title>
    @if($company?->icon)
        <link rel="icon" href="{{ Storage::url($company->icon) }}">
    @elseif($company?->logo)
        <link rel="icon" href="{{ Storage::url($company->logo) }}">
    @endif
    @vite(['resources/css/app.css'])
</head>
<body class="bg-[#0f1120] text-white antialiased min-h-screen flex flex-col items-center justify-center p-6"
      style="background-color:#0f1120;color:#fff;min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:1.5rem;font-family:sans-serif">

    {{-- Fondo con gradiente sutil --}}
    <div class="absolute inset-0 overflow-hidden pointer-events-none" aria-hidden="true">
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-indigo-600/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-indigo-800/10 rounded-full blur-3xl"></div>
    </div>

    <div class="relative w-full max-w-md text-center">

        {{-- Logo --}}
        <div class="flex justify-center mb-10">
            @if($company?->logo)
                <img src="{{ Storage::url($company->logo) }}"
                    alt="{{ $company->name }}"
                    class="h-20 object-contain brightness-0 invert opacity-90">
            @else
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 bg-indigo-500 rounded-xl flex items-center justify-center text-white font-bold text-lg">
                        {{ strtoupper(substr($company?->name ?? config('app.name'), 0, 1)) }}
                    </div>
                    <span class="text-lg font-semibold text-white/80">{{ $company?->name ?? config('app.name') }}</span>
                </div>
            @endif
        </div>

        {{-- Número --}}
        <p class="text-[8rem] font-extrabold leading-none tracking-tight text-transparent bg-clip-text bg-gradient-to-br from-indigo-400 to-indigo-600 mb-2 select-none">
            404
        </p>

        {{-- Ícono + texto --}}
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-white mb-3">Página no encontrada</h1>
            <p class="text-white/45 text-sm leading-relaxed">
                La página que buscas no existe o ha sido movida.<br>
                Verifica la URL o regresa al inicio.
            </p>
        </div>

        {{-- Separador decorativo --}}
        <div class="flex items-center gap-3 mb-8">
            <div class="flex-1 h-px bg-white/8"></div>
            <svg class="w-4 h-4 text-white/20" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="flex-1 h-px bg-white/8"></div>
        </div>

        {{-- Acciones --}}
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            @auth
                <a href="{{ route('dashboard') }}"
                    class="inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium px-5 py-2.5 rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    Ir al Dashboard
                </a>
                <button onclick="history.back()"
                    class="inline-flex items-center justify-center gap-2 bg-white/6 hover:bg-white/10 border border-white/10 text-white/70 hover:text-white text-sm font-medium px-5 py-2.5 rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Regresar
                </button>
            @else
                <a href="{{ route('login') }}"
                    class="inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium px-5 py-2.5 rounded-lg transition">
                    Iniciar sesión
                </a>
            @endauth
        </div>
    </div>

    {{-- Footer --}}
    <p class="relative mt-16 text-xs text-white/20">
        &copy; {{ date('Y') }} {{ $company?->name ?? config('app.name') }}. Sistema ERP
    </p>

</body>
</html>

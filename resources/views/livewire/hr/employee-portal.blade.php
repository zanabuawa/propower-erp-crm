<div class="max-w-2xl mx-auto py-8 px-4">
    @if(!$employee)
        <div class="bg-amber-50 border-l-4 border-amber-400 p-4 rounded-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-amber-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-amber-700">No tienes un perfil de empleado vinculado. Contacta a Recursos Humanos.</p>
                </div>
            </div>
        </div>
    @else
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-slate-800">¡Hola, {{ $employee->first_name }}!</h1>
            <p class="text-slate-500">{{ now()->translatedFormat('l, d \d\e F \d\e Y') }}</p>
        </div>

        <x-alert />

        {{-- Reloj y Checador --}}
        <div class="bg-white rounded-3xl shadow-xl border border-slate-100 p-8 mb-8 text-center">
            <div class="mb-6" x-data="{ 
                time: '{{ now()->format('H:i:s') }}',
                init() {
                    setInterval(() => {
                        const now = new Date();
                        this.time = now.getHours().toString().padStart(2, '0') + ':' + 
                                    now.getMinutes().toString().padStart(2, '0') + ':' + 
                                    now.getSeconds().toString().padStart(2, '0');
                    }, 1000);
                }
            }">
                <div class="text-5xl font-black text-slate-800 tracking-tighter tabular-nums" x-text="time">
                    {{ now()->format('H:i:s') }}
                </div>
                <p class="text-xs font-bold text-indigo-500 uppercase tracking-widest mt-1">Hora local actual</p>
            </div>

            {{-- Estado de Ubicación --}}
            <div class="mb-6">
                @if($geoStatus === 'loading')
                    <div class="flex flex-col items-center gap-2 py-4 animate-pulse">
                        <div class="w-10 h-10 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin"></div>
                        <p class="text-sm font-medium text-slate-500">Validando tu ubicación GPS...</p>
                    </div>
                @elseif($geoStatus === 'located')
                    @if($locationValid)
                        <div class="bg-green-50 border-2 border-green-500/20 rounded-2xl p-4 flex items-center gap-4 text-left animate-in fade-in zoom-in duration-300">
                            <div class="w-12 h-12 bg-green-500 text-white rounded-full flex items-center justify-center flex-shrink-0 shadow-lg shadow-green-100">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-green-800">Ubicación Confirmada</h4>
                                <p class="text-xs text-green-600">Estás en: <strong>{{ $detectedZone->name }}</strong></p>
                            </div>
                        </div>
                    @else
                        <div class="bg-red-50 border-2 border-red-500/10 rounded-2xl p-4 text-center animate-in fade-in zoom-in duration-300">
                            <p class="text-sm font-bold text-red-800 mb-1">Fuera de Zona Autorizada</p>
                            <p class="text-xs text-red-600">No se detectó ninguna zona de asistencia en tu posición actual.</p>
                            @if($currentDistance)
                                <p class="text-[10px] text-red-400 mt-1 uppercase font-bold">Aprox. {{ number_format($currentDistance) }} metros de la zona más cercana</p>
                            @endif
                        </div>
                    @endif
                @elseif($geoStatus === 'denied')
                    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 text-center">
                        <p class="text-sm font-bold text-amber-800">Permiso Denegado</p>
                        <p class="text-xs text-amber-600">Has bloqueado el acceso al GPS. Por favor, actívalo haciendo clic en el candado de la URL.</p>
                        @if(str_contains(request()->url(), 'http://') && !str_contains(request()->url(), 'localhost'))
                            <p class="text-[10px] text-amber-500 mt-2 font-bold uppercase">Tip local: Usa "localhost" en lugar de la IP para habilitar el GPS en HTTP.</p>
                        @endif
                    </div>
                @elseif($geoStatus === 'error')
                    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 text-center">
                        <p class="text-sm font-bold text-slate-800">Error de Conexión GPS</p>
                        <p class="text-xs text-slate-500">No se pudo obtener la ubicación. Verifica que tu dispositivo tenga el GPS activo.</p>
                    </div>
                @endif
            </div>

            <div class="space-y-4">
                @if(!$todayAttendance)
                    {{-- Botón de Entrada --}}
                    <button wire:click="checkIn" wire:loading.attr="disabled"
                            @if(!$locationValid) disabled @endif
                            class="group relative w-full py-6 px-4 {{ $locationValid ? 'bg-indigo-600 hover:bg-indigo-700 shadow-indigo-100' : 'bg-slate-300 cursor-not-allowed' }} text-white rounded-2xl font-bold text-xl shadow-lg transition-all active:scale-95">
                        <span class="flex items-center justify-center gap-3">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                            REGISTRAR ENTRADA
                        </span>
                    </button>
                @elseif(!$todayAttendance->check_out)
                    {{-- Botón de Salida --}}
                    <div class="mb-4 p-4 bg-indigo-50 rounded-2xl border border-indigo-100 inline-block w-full">
                        <p class="text-sm text-indigo-600 font-medium">Entrada registrada a las: <span class="text-lg font-bold ml-1">{{ \Carbon\Carbon::parse($todayAttendance->check_in)->format('H:i A') }}</span></p>
                    </div>
                    
                    <button wire:click="checkOut" wire:loading.attr="disabled"
                            @if(!$locationValid) disabled @endif
                            class="w-full py-6 px-4 {{ $locationValid ? 'bg-rose-500 hover:bg-rose-600 shadow-rose-100' : 'bg-slate-300 cursor-not-allowed' }} text-white rounded-2xl font-bold text-xl shadow-lg transition-all active:scale-95">
                        <span class="flex items-center justify-center gap-3">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            REGISTRAR SALIDA
                        </span>
                    </button>
                @else
                    {{-- Jornada Finalizada --}}
                    <div class="py-8 px-4 bg-slate-50 rounded-2xl border border-dashed border-slate-300">
                        <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-3">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-800">¡Jornada completada!</h3>
                        <p class="text-sm text-slate-500">Has registrado tu salida el día de hoy.</p>
                        <div class="mt-4 flex justify-center gap-6 text-sm">
                            <div><p class="text-slate-400">Entrada</p><p class="font-bold text-slate-700">{{ \Carbon\Carbon::parse($todayAttendance->check_in)->format('H:i A') }}</p></div>
                            <div><p class="text-slate-400">Salida</p><p class="font-bold text-slate-700">{{ \Carbon\Carbon::parse($todayAttendance->check_out)->format('H:i A') }}</p></div>
                            <div><p class="text-slate-400">Horas</p><p class="font-bold text-indigo-600">{{ $todayAttendance->worked_hours }}</p></div>
                        </div>
                    </div>
                @endif

                {{-- Botón para Solicitar Ubicación --}}
                @if(!$locationValid && !($todayAttendance && $todayAttendance->check_out))
                    <button type="button" onclick="requestLocation()"
                            class="w-full py-4 bg-white border-2 border-indigo-100 text-indigo-600 hover:bg-indigo-50 rounded-2xl font-bold text-sm transition-all flex items-center justify-center gap-2 uppercase tracking-widest">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Validar mi ubicación para checar
                    </button>
                @endif
            </div>
        </div>

        {{-- Historial Reciente --}}
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Mis últimos registros</h3>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($recentAttendances as $att)
                    <div class="px-5 py-3 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-bold text-slate-700">{{ \Carbon\Carbon::parse($att->date)->translatedFormat('D, d M') }}</p>
                            <p class="text-xs text-slate-400">{{ \Carbon\Carbon::parse($att->check_in)->format('H:i A') }} @if($att->check_out) — {{ \Carbon\Carbon::parse($att->check_out)->format('H:i A') }} @endif</p>
                        </div>
                        <div class="text-right">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $att->status_color ?? 'bg-slate-100 text-slate-600' }}">
                                {{ $att->status_label ?? 'Presente' }}
                            </span>
                            @if($att->worked_hours)
                                <p class="text-[10px] text-slate-500 mt-1 font-medium">{{ $att->worked_hours }} hrs trabajadas</p>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-sm text-slate-400">No hay registros previos.</div>
                @endforelse
            </div>
        </div>
    @endif

    {{-- Debug Info (puedes borrarlo después de verificar) --}}
    <div class="mt-8 text-center">
        <p class="text-[10px] text-slate-400 uppercase tracking-widest font-bold">
            Configuración del Sistema: {{ config('app.timezone') }} | Hora Laravel: {{ now()->format('H:i:s') }}
        </p>
    </div>

    {{-- Script de Geolocalización --}}
    <script>
    function requestLocation() {
        console.log('--- Iniciando solicitud de ubicación ---');
        
        if (!navigator.geolocation) {
            alert('Atención: Tu navegador no soporta geolocalización.');
            @this.call('geoError');
            return;
        }

        @this.set('geoStatus', 'loading');

        // Llamada directa sin retardos para que el navegador no bloquee el popup
        navigator.geolocation.getCurrentPosition(
            function(pos) {
                console.log('Ubicación obtenida con éxito');
                @this.call('setCoordinates', pos.coords.latitude, pos.coords.longitude);
            },
            function(err) {
                let errorMsg = '';
                switch(err.code) {
                    case 1: errorMsg = 'Permiso Denegado. Por favor, permite el acceso a la ubicación en el icono del candado de la URL.'; break;
                    case 2: errorMsg = 'Ubicación no disponible. Verifica que tu GPS esté encendido.'; break;
                    case 3: errorMsg = 'Tiempo agotado. Intenta de nuevo.'; break;
                    default: errorMsg = 'Error al obtener ubicación: ' + err.message;
                }
                alert(errorMsg);
                @this.call('geoError');
            },
            { 
                enableHighAccuracy: true, 
                timeout: 15000, 
                maximumAge: 0 
            }
        );
    }

    function simulateLocation() {
        // Usa una ubicación central por defecto o la última conocida
        @this.call('setCoordinates', 19.4326, -99.1332);
    }

    function initGeoCheck() {
        const isEmployee = @json((bool)$employee);
        const isFinished = @json((bool)($todayAttendance?->check_in && $todayAttendance?->check_out));
        
        if (isEmployee && !isFinished) {
            requestLocation();
        }
    }

    document.addEventListener('livewire:navigated', initGeoCheck);
    if (document.readyState === 'complete') initGeoCheck();
    else window.addEventListener('load', initGeoCheck);
    </script>
</div>

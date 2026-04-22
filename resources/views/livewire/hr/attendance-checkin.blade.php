<div>
    <x-page-header title="Registrar Asistencia" description="Usa tu ubicación GPS para registrar tu entrada o salida" />

    <x-alert />

    @if(! $employee)
    <div class="bg-white rounded-xl border border-slate-200 p-12 text-center">
        <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <p class="text-slate-700 font-medium mb-1">No tienes un perfil de empleado activo</p>
        <p class="text-slate-400 text-sm">Contacta a tu administrador para vincular tu cuenta.</p>
    </div>
    @else

    {{-- Employee card --}}
    <div class="max-w-md mx-auto space-y-4">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 flex items-center gap-4">
            <div class="w-14 h-14 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 font-bold text-xl flex-shrink-0">
                {{ strtoupper(substr($employee->first_name, 0, 1)) }}{{ strtoupper(substr($employee->last_name, 0, 1)) }}
            </div>
            <div>
                <p class="font-semibold text-slate-800">{{ $employee->first_name }} {{ $employee->last_name }} {{ $employee->second_last_name }}</p>
                <p class="text-sm text-slate-400">{{ $employee->employee_number ?? '—' }}
                    @if($employee->position) · {{ $employee->position->name }} @endif
                </p>
            </div>
        </div>

        {{-- Schedule summary --}}
        @if($activeContract)
        @php
            $todayIso = now()->isoFormat('E');
            $isRestDay = ! $activeContract->isWorkDay(now());
            $workDayLabels = ['1'=>'Lun','2'=>'Mar','3'=>'Mié','4'=>'Jue','5'=>'Vie','6'=>'Sáb','7'=>'Dom'];
        @endphp
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
            <p class="text-xs font-medium text-slate-400 uppercase mb-2">Horario del contrato</p>
            <div class="flex flex-wrap items-center gap-3 text-sm text-slate-700">
                @if($activeContract->entry_time)
                <span>{{ substr($activeContract->entry_time, 0, 5) }} – {{ substr($activeContract->exit_time ?? '', 0, 5) }}</span>
                <span class="text-slate-300">·</span>
                @endif
                <span class="flex gap-1">
                    @foreach($workDayLabels as $num => $lbl)
                        <span class="px-1.5 py-0.5 rounded text-[11px] font-medium
                            {{ in_array((int)$num, $activeContract->work_days ?? [1,2,3,4,5])
                                ? ($num == $todayIso ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600')
                                : 'text-slate-300' }}">{{ $lbl }}</span>
                    @endforeach
                </span>
                @if(($activeContract->work_days && in_array(6, $activeContract->work_days)) && $activeContract->saturday_hours > 0)
                <span class="text-xs text-orange-600">Sáb {{ $activeContract->saturday_hours }}h</span>
                @endif
                <span class="text-slate-300">·</span>
                <span class="text-xs text-slate-500">Tolerancia {{ $activeContract->tolerance_minutes ?? 10 }} min</span>
            </div>
            @if($isRestDay)
            <div class="mt-3 flex items-center gap-2 text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Hoy es día de descanso según tu contrato.
            </div>
            @endif
        </div>
        @endif

        {{-- Today status --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
            <p class="text-xs font-medium text-slate-400 uppercase mb-3">Hoy · {{ now()->translatedFormat('l d \d\e F') }}</p>

            @if($todayAttendance && $todayAttendance->check_in)
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-9 h-9 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400">Entrada</p>
                        <p class="font-semibold text-slate-800">{{ \Carbon\Carbon::parse($todayAttendance->check_in)->format('H:i') }}</p>
                    </div>
                </div>

                @if($todayAttendance->check_out)
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-9 h-9 bg-blue-100 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400">Salida</p>
                            <p class="font-semibold text-slate-800">{{ \Carbon\Carbon::parse($todayAttendance->check_out)->format('H:i') }}</p>
                        </div>
                    </div>
                    @if($todayAttendance->worked_hours)
                    <div class="mt-3 bg-slate-50 rounded-lg p-3 text-sm text-slate-600">
                        Horas trabajadas: <strong>{{ number_format($todayAttendance->worked_hours, 1) }} h</strong>
                        @if($todayAttendance->overtime_hours)
                            · Horas extra: <strong class="text-orange-600">{{ number_format($todayAttendance->overtime_hours, 1) }} h</strong>
                        @endif
                    </div>
                    @endif
                    <p class="mt-3 text-center text-sm text-slate-500">Tu jornada de hoy ha sido registrada correctamente.</p>
                @else
                    <p class="text-sm text-slate-500">Aún no has registrado tu salida.</p>
                @endif
            @else
                <p class="text-sm text-slate-500">Sin registros para hoy.</p>
            @endif
        </div>

        {{-- Geolocation + action --}}
        @if(! ($todayAttendance && $todayAttendance->check_in && $todayAttendance->check_out))
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 space-y-4">

            {{-- Location status --}}
            <div>
                {{-- MODO DESARROLLO: Botón para simular ubicación si estamos en HTTP local --}}
                @if(app()->environment('local'))
                    <div class="mb-4 p-3 bg-slate-800 rounded-xl text-white">
                        <p class="text-[10px] uppercase font-bold text-slate-400 mb-2 tracking-widest text-center">Herramientas de Desarrollo</p>
                        <button onclick="simulateLocation()" 
                                class="w-full py-2 bg-slate-700 hover:bg-slate-600 rounded-lg text-xs font-bold transition flex items-center justify-center gap-2">
                            <span class="w-2 h-2 bg-green-500 rounded-full animate-ping"></span>
                            SIMULAR UBICACIÓN (BYPASS HTTP)
                        </button>
                        <script>
                            function simulateLocation() {
                                // Coordenadas de prueba (puedes ajustarlas a una zona que ya tengas creada)
                                const testLat = @js($latitude) || 19.4326; 
                                const testLng = @js($longitude) || -99.1332;
                                alert('Simulando ubicación en: ' + testLat + ', ' + testLng);
                                @this.call('setCoordinates', parseFloat(testLat), parseFloat(testLng));
                            }
                        </script>
                    </div>
                @endif

                @if($geoStatus === 'idle')
                    <button onclick="requestLocation()"
                            class="w-full flex items-center justify-center gap-2 text-sm text-indigo-600 hover:text-indigo-800 border border-indigo-200 hover:bg-indigo-50 rounded-lg py-2.5 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Obtener mi ubicación
                    </button>

                @elseif($geoStatus === 'loading')
                    <div class="flex items-center justify-center gap-2 text-sm text-slate-500 py-2">
                        <svg class="animate-spin w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                        </svg>
                        Obteniendo ubicación...
                    </div>

                @elseif($geoStatus === 'located')
                    @if($locationValid)
                        <div class="animate-in fade-in zoom-in duration-500">
                            <div class="bg-green-50 border-2 border-green-500/30 rounded-2xl p-6 text-center shadow-lg shadow-green-100/50 mb-4">
                                <div class="w-16 h-16 bg-green-500 text-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg shadow-green-200 animate-bounce">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-black text-green-800 mb-1">¡UBICACIÓN CORRECTA!</h3>
                                <p class="text-green-600 text-sm">Estás en: <span class="font-bold text-green-700">{{ $detectedZone->name }}</span></p>
                                <p class="text-xs text-green-500 mt-2 italic">Ahora puedes registrar tu asistencia abajo.</p>
                            </div>
                        </div>
                    @else
                        <div class="bg-red-50 border-2 border-red-500/20 rounded-2xl p-6 text-center mb-4">
                            <div class="w-12 h-12 bg-red-100 text-red-500 rounded-full flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-red-800 mb-1">Fuera de Zona</h3>
                            <p class="text-red-600 text-sm mb-4">No se detectó ninguna zona de asistencia autorizada en tu ubicación actual.</p>
                            <button onclick="requestLocation()"
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-red-200 text-red-600 hover:bg-red-50 rounded-lg text-sm font-semibold transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Reintentar detección
                            </button>
                        </div>
                    @endif

                @elseif($geoStatus === 'denied')
                    <div class="text-sm text-red-700 bg-red-50 border border-red-200 rounded-lg px-3 py-2">
                        Permisos de ubicación denegados. Actívalos en la configuración de tu navegador.
                    </div>

                @elseif($geoStatus === 'error')
                    <div class="text-sm text-yellow-700 bg-yellow-50 border border-yellow-200 rounded-lg px-3 py-2">
                        No se pudo obtener la ubicación. Intenta de nuevo.
                    </div>
                    <button onclick="requestLocation()" class="mt-2 w-full text-sm text-indigo-600 border border-indigo-200 rounded-lg py-2 hover:bg-indigo-50 transition">
                        Reintentar
                    </button>
                @endif
            </div>

            {{-- Action buttons --}}
            <div class="space-y-3 pt-2">
                {{-- Botón de Registro (Entrada/Salida) --}}
                <button wire:click="checkin" 
                        wire:loading.attr="disabled"
                        @if(!$locationValid) disabled @endif
                        class="w-full py-4 rounded-2xl font-bold text-white text-base transition-all shadow-lg
                            {{ !$locationValid 
                                ? 'bg-slate-300 cursor-not-allowed shadow-none' 
                                : (($todayAttendance && $todayAttendance->check_in && ! $todayAttendance->check_out) 
                                    ? 'bg-blue-600 hover:bg-blue-700 shadow-blue-100' 
                                    : 'bg-green-600 hover:bg-green-700 shadow-green-100') }}">
                    
                    <span wire:loading.remove wire:target="checkin">
                        @if($todayAttendance && $todayAttendance->check_in && ! $todayAttendance->check_out)
                            <span class="flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                REGISTRAR SALIDA
                            </span>
                        @else
                            <span class="flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                                REGISTRAR ENTRADA
                            </span>
                        @endif
                    </span>
                    
                    <span wire:loading wire:target="checkin" class="flex items-center justify-center gap-2">
                        <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        REGISTRANDO...
                    </span>
                </button>

                {{-- Botón de Solicitar Ubicación (Visible si no hay ubicación válida) --}}
                @if(!$locationValid)
                    <button type="button" onclick="requestLocation()"
                            class="w-full py-3 rounded-xl font-bold text-indigo-600 border-2 border-indigo-100 hover:bg-indigo-50 transition-all flex items-center justify-center gap-2 uppercase tracking-wide text-xs">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Solicitar mi Ubicación
                    </button>
                @endif
            </div>
        </div>
        @endif
    </div>
    @endif

    <script>
    function requestLocation() {
        console.log('--- Iniciando solicitud de ubicación ---');
        
        if (!navigator.geolocation) {
            alert('DEBUG: Tu navegador NO soporta navigator.geolocation');
            @this.call('geoError');
            return;
        }

        @this.set('geoStatus', 'loading');

        // Pequeño retardo para asegurar que el estado "loading" se pinte
        setTimeout(() => {
            navigator.geolocation.getCurrentPosition(
                function(pos) {
                    console.log('DEBUG: Ubicación obtenida con éxito');
                    @this.call('setCoordinates', pos.coords.latitude, pos.coords.longitude);
                },
                function(err) {
                    console.error('DEBUG: Error código ' + err.code + ' - ' + err.message);
                    
                    let errorMsg = '';
                    switch(err.code) {
                        case 1: errorMsg = 'Permiso Denegado. Por favor, haz clic en el icono del candado en la barra de direcciones y permite la Ubicación.'; break;
                        case 2: errorMsg = 'Ubicación no disponible. Verifica que tu GPS esté encendido.'; break;
                        case 3: errorMsg = 'Tiempo de espera agotado al intentar obtener el GPS.'; break;
                        default: errorMsg = 'Error desconocido: ' + err.message;
                    }
                    
                    alert('Atención: ' + errorMsg);
                    @this.call('geoError');

                    // Si falló por tiempo, intentar una vez más con baja precisión (más rápido)
                    if (err.code === 3) {
                        navigator.geolocation.getCurrentPosition(
                            (p) => @this.call('setCoordinates', p.coords.latitude, p.coords.longitude),
                            () => {}, // Silencioso el segundo fallo
                            { enableHighAccuracy: false, timeout: 5000 }
                        );
                    }
                },
                { 
                    enableHighAccuracy: true, 
                    timeout: 12000, 
                    maximumAge: 0 
                }
            );
        }, 500);
    }

    // Asegurar ejecución al cargar
    function initGeoCheck() {
        const isEmployee = @json((bool)$employee);
        const isFinished = @json((bool)($todayAttendance?->check_in && $todayAttendance?->check_out));
        
        if (isEmployee && !isFinished) {
            requestLocation();
        }
    }

    document.addEventListener('livewire:navigated', initGeoCheck);
    
    if (document.readyState === 'complete') {
        initGeoCheck();
    } else {
        window.addEventListener('load', initGeoCheck);
    }
    </script>
</div>

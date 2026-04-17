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

        @if(session('success'))
            <div class="mb-6 p-4 bg-green-100 border border-green-200 text-green-700 rounded-xl flex items-center gap-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
        @endif

        {{-- Reloj y Checador --}}
        <div class="bg-white rounded-3xl shadow-xl border border-slate-100 p-8 mb-8 text-center">
            <div wire:poll.1s class="mb-6">
                <div class="text-5xl font-black text-slate-800 tracking-tighter tabular-nums">
                    {{ now()->format('H:i:s') }}
                </div>
                <p class="text-xs font-bold text-indigo-500 uppercase tracking-widest mt-1">Hora actual del servidor</p>
            </div>

            @if(!$todayAttendance)
                {{-- Botón de Entrada --}}
                <button wire:click="checkIn" wire:loading.attr="disabled"
                        class="group relative w-full py-6 px-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl font-bold text-xl shadow-lg shadow-indigo-200 transition-all active:scale-95">
                    <span class="flex items-center justify-center gap-3">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                        REGISTRAR ENTRADA
                    </span>
                    <div wire:loading class="absolute right-6 top-1/2 -translate-y-1/2">
                        <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </div>
                </button>
            @elseif(!$todayAttendance->check_out)
                {{-- Botón de Salida --}}
                <div class="mb-6 p-4 bg-indigo-50 rounded-2xl border border-indigo-100 inline-block w-full">
                    <p class="text-sm text-indigo-600 font-medium">Entrada registrada a las: <span class="text-lg font-bold ml-1">{{ \Carbon\Carbon::parse($todayAttendance->check_in)->format('H:i A') }}</span></p>
                </div>
                
                <button wire:click="checkOut" wire:loading.attr="disabled"
                        class="w-full py-6 px-4 bg-rose-500 hover:bg-rose-600 text-white rounded-2xl font-bold text-xl shadow-lg shadow-rose-200 transition-all active:scale-95">
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
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $att->status_color }}">
                                {{ $att->status_label }}
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
</div>

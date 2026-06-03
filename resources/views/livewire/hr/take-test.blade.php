<div class="min-h-screen bg-slate-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto">
        @if($isCompleted)
            <div class="bg-white rounded-3xl shadow-xl border border-slate-200 p-8 sm:p-12 text-center animate-in fade-in zoom-in duration-500">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full {{ $testResult['isPassed'] ? 'bg-emerald-100 text-emerald-600' : ($testResult['hasOpenEnded'] ? 'bg-amber-100 text-amber-600' : 'bg-rose-100 text-rose-600') }} mb-6">
                    @if($testResult['isPassed'])
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                    @elseif($testResult['hasOpenEnded'])
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    @else
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                    @endif
                </div>

                <h2 class="text-3xl font-black text-slate-900 mb-2">
                    {{ $testResult['hasOpenEnded'] ? '¡Prueba Recibida!' : ($testResult['isPassed'] ? '¡Prueba Aprobada!' : 'Prueba No Aprobada') }}
                </h2>
                
                <div class="mb-8 mt-6">
                    <div class="inline-block px-6 py-4 rounded-3xl bg-slate-50 border border-slate-100">
                        <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Tu Calificación</span>
                        <span class="text-4xl font-black {{ $testResult['isPassed'] ? 'text-emerald-600' : ($testResult['hasOpenEnded'] ? 'text-amber-600' : 'text-rose-600') }}">
                            {{ $testResult['score'] }}%
                        </span>
                    </div>
                    @if(!$testResult['hasOpenEnded'])
                        <p class="text-xs font-bold text-slate-400 mt-2 uppercase tracking-tighter">Mínimo para aprobar: {{ $testResult['passingScore'] }}%</p>
                    @endif
                </div>

                <div class="max-w-md mx-auto mb-10">
                    @if($testResult['hasOpenEnded'])
                        <p class="text-slate-600 font-medium">Hemos recibido tus respuestas. Debido a que incluiste preguntas abiertas, nuestro equipo de RH las revisará manualmente y te notificaremos pronto.</p>
                    @elseif($testResult['isPassed'])
                        <p class="text-slate-600 font-medium">¡Felicidades! Has superado con éxito esta evaluación. Puedes continuar con las demás etapas en tu portal.</p>
                    @else
                        <p class="text-slate-600 font-medium mb-4">No has alcanzado el puntaje mínimo requerido para esta prueba.</p>
                        @if($testResult['attemptsLeft'] > 0)
                            <div class="p-4 bg-amber-50 rounded-2xl border border-amber-100">
                                <p class="text-sm font-bold text-amber-800">
                                    Aún tienes {{ $testResult['attemptsLeft'] }} {{ $testResult['attemptsLeft'] == 1 ? 'intento disponible' : 'intentos disponibles' }}.
                                </p>
                                <p class="text-xs text-amber-600 mt-1">Te recomendamos revisar el material de apoyo antes de volver a intentarlo.</p>
                            </div>
                        @else
                            <div class="p-4 bg-rose-50 rounded-2xl border border-rose-100">
                                <p class="text-sm font-bold text-rose-800 text-center">Has agotado todos tus intentos para esta prueba.</p>
                            </div>
                        @endif
                    @endif
                </div>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="{{ $this->getPortalUrl() }}" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3 bg-slate-900 text-white font-bold rounded-xl hover:bg-slate-800 transition-colors shadow-lg">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                        Volver al Portal
                    </a>
                </div>
            </div>
        @else
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-8">
                <div class="bg-slate-900 px-8 py-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-white">{{ $prospectTest->template->name }}</h1>
                            <p class="text-slate-400 text-sm mt-1">{{ $prospectTest->template->description }}</p>
                        </div>
                        <div class="flex items-center gap-8">
                            @if($timeLeft !== null)
                                <div class="text-right" 
                                     x-data="{ 
                                        timeLeft: @js($timeLeft),
                                        formatTime(seconds) {
                                            const h = Math.floor(seconds / 3600);
                                            const m = Math.floor((seconds % 3600) / 60);
                                            const s = seconds % 60;
                                            return `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
                                        }
                                     }"
                                     x-init="
                                        const timer = setInterval(() => {
                                            if (timeLeft > 0) {
                                                timeLeft--;
                                                $wire.set('timeLeft', timeLeft);
                                            } else {
                                                clearInterval(timer);
                                                $wire.submit();
                                            }
                                        }, 1000);
                                     ">
                                    <span class="block text-xs font-bold text-slate-500 uppercase tracking-widest">Tiempo Restante</span>
                                    <span class="text-xl font-mono font-bold" :class="timeLeft < 60 ? 'text-rose-400 animate-pulse' : 'text-emerald-400'" x-text="formatTime(timeLeft)"></span>
                                </div>
                            @endif
                            <div class="text-right">
                                <span class="block text-xs font-bold text-slate-500 uppercase tracking-widest">Preguntas</span>
                                <span class="text-xl font-bold text-indigo-400">{{ count($prospectTest->template->questions) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-8">
                    <form wire:submit.prevent="submit" class="space-y-12">
                        @foreach($prospectTest->template->questions as $index => $question)
                            <div class="space-y-4">
                                <div class="flex items-start">
                                    <span class="flex-shrink-0 flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 font-bold text-sm mr-4 mt-0.5">
                                        {{ $index + 1 }}
                                    </span>
                                    <h3 class="text-lg font-bold text-slate-900 leading-tight pt-1">
                                        {{ $question->question_text }}
                                    </h3>
                                </div>

                                <div class="ml-12">
                                    @if($question->type === 'multiple_choice')
                                        <div class="grid grid-cols-1 gap-3">
                                            @foreach($question->options as $option)
                                                <button type="button"
                                                    wire:click="selectAnswer({{ $question->id }}, {{ $option->id }})"
                                                    class="relative flex w-full items-center p-4 rounded-xl border text-left {{ ($answers[$question->id] ?? null) == $option->id ? 'bg-indigo-50 border-indigo-200 ring-2 ring-indigo-100' : 'bg-white border-slate-200 hover:border-slate-300 hover:bg-slate-50' }} cursor-pointer transition-all group">
                                                    <div class="w-5 h-5 rounded-full border-2 {{ ($answers[$question->id] ?? null) == $option->id ? 'border-indigo-600 bg-indigo-600' : 'border-slate-300 group-hover:border-slate-400' }} flex items-center justify-center transition-colors">
                                                        @if(($answers[$question->id] ?? null) == $option->id)
                                                            <div class="w-2 h-2 rounded-full bg-white"></div>
                                                        @endif
                                                    </div>
                                                    <span class="ml-3 text-sm font-medium {{ ($answers[$question->id] ?? null) == $option->id ? 'text-indigo-900' : 'text-slate-700' }}">
                                                        {{ $option->option_text }}
                                                    </span>
                                                </button>
                                            @endforeach
                                        </div>
                                    @else
                                        <textarea wire:model="answers.{{ $question->id }}" rows="4" 
                                                  class="w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                                  placeholder="Escribe tu respuesta aquí..."></textarea>
                                    @endif
                                    
                                    @error('answers.' . $question->id)
                                        <p class="mt-2 text-xs text-rose-600 font-medium">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        @endforeach

                        <div class="pt-8 border-t border-slate-100 flex items-center justify-between">
                            <p class="text-xs text-slate-500 italic">
                                Asegúrate de haber respondido todas las preguntas antes de enviar.
                            </p>
                            <button type="submit" class="inline-flex items-center px-8 py-3 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                Enviar Evaluación
                                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>

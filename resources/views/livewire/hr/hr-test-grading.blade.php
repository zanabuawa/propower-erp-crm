<div class="py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-5xl mx-auto">
        <!-- Header -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8 mb-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div>
                    <nav class="flex mb-4" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 md:space-x-3 text-sm font-medium text-slate-500">
                            <li><a href="{{ route('hr.evaluations.pending-grades') }}" class="hover:text-indigo-600 transition-colors">Pendientes</a></li>
                            <li><svg class="w-4 h-4 mx-1" fill="currentColor" viewBox="0 0 20 20"><path d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"></path></svg></li>
                            <li class="text-slate-900">Calificar Evaluación</li>
                        </ol>
                    </nav>
                    <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">
                        {{ $attempt->prospectTest->template->name }}
                    </h1>
                    <p class="mt-2 text-lg text-slate-600">
                        Evaluado: <span class="font-semibold text-indigo-600">{{ $attempt->prospectTest->stage->process->prospect?->full_name ?? $attempt->prospectTest->stage->process->employee?->full_name }}</span>
                    </p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-right">
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-widest">Intento #</p>
                        <p class="text-2xl font-black text-slate-900">{{ $attempt->attempt_number }}</p>
                    </div>
                    <div class="h-12 w-px bg-slate-200"></div>
                    <div class="text-right">
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-widest">Fecha</p>
                        <p class="text-sm font-bold text-slate-900">{{ ($attempt->completed_at ?? $attempt->submitted_at ?? $attempt->updated_at)->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm mb-8">
            <div class="flex items-center justify-between gap-4 mb-4">
                <div>
                    <h2 class="text-sm font-black text-slate-800 uppercase tracking-widest">Intentos del examen</h2>
                    <p class="text-xs text-slate-500 mt-1">Revisa cada intento de forma independiente.</p>
                </div>
                <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-500 text-[10px] font-black uppercase tracking-widest">
                    {{ $attempts->count() }} intento(s)
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                @foreach($attempts as $testAttempt)
                    <a href="{{ route('hr.test-grading', $testAttempt) }}" wire:navigate
                       class="rounded-xl border px-4 py-3 transition-colors {{ $testAttempt->id === $attempt->id ? 'border-indigo-200 bg-indigo-50 text-indigo-700' : 'border-slate-100 bg-slate-50 text-slate-600 hover:bg-white hover:border-indigo-100' }}">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-sm font-black">Intento #{{ $testAttempt->attempt_number }}</p>
                            <span class="text-[10px] font-black uppercase tracking-wider">
                                {{ $testAttempt->status === 'graded' ? 'Calificado' : ($testAttempt->status === 'partially_graded' ? 'Por revisar' : $testAttempt->status) }}
                            </span>
                        </div>
                        <p class="text-[10px] font-bold uppercase tracking-wider opacity-70 mt-1">
                            {{ $testAttempt->answers_count }} respuestas &middot; {{ $testAttempt->score }}%
                        </p>
                    </a>
                @endforeach
            </div>
        </div>

        <form wire:submit.prevent="saveGrades" class="space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Automatico</p>
                    <p class="mt-2 text-2xl font-black text-slate-900">{{ number_format($automaticPoints, 1) }}</p>
                </div>
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Manual</p>
                    <p class="mt-2 text-2xl font-black text-slate-900">{{ number_format($manualPoints, 1) }}</p>
                </div>
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total puntos</p>
                    <p class="mt-2 text-2xl font-black text-slate-900">{{ number_format($totalPoints, 1) }}</p>
                </div>
                <div class="bg-indigo-600 rounded-2xl p-5 shadow-lg shadow-indigo-100 text-white">
                    <p class="text-[10px] font-black text-indigo-100 uppercase tracking-widest">Calificacion estimada</p>
                    <p class="mt-2 text-2xl font-black">{{ $previewScore }}%</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="bg-slate-900 px-8 py-4">
                    <h2 class="text-sm font-bold text-white uppercase tracking-wider">Revisión de Respuestas</h2>
                </div>
                
                <div class="divide-y divide-slate-100">
                    @foreach($attempt->answers as $index => $answer)
                        <div class="p-8">
                            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                                <!-- Question and Answer -->
                                <div class="lg:col-span-8 space-y-4">
                                    <div class="flex items-start">
                                        <span class="flex-shrink-0 flex items-center justify-center w-8 h-8 rounded-lg bg-slate-100 text-slate-600 font-bold text-sm mr-4 mt-0.5">
                                            {{ $index + 1 }}
                                        </span>
                                        <div>
                                            <h3 class="text-lg font-bold text-slate-900 leading-tight pt-1">
                                                {{ $answer->question->question_text }}
                                            </h3>
                                            <p class="text-xs text-slate-500 mt-1 uppercase font-bold tracking-tighter">
                                                Tipo: {{ $answer->question->type === 'multiple_choice' ? 'Opción Múltiple' : 'Pregunta Abierta' }} | Valor: {{ $answer->question->points }} pts
                                            </p>
                                        </div>
                                    </div>

                                    <div class="ml-12 p-5 rounded-2xl bg-slate-50 border border-slate-100">
                                        <p class="text-xs font-bold text-slate-400 uppercase mb-2 tracking-widest">Respuesta del Candidato:</p>
                                        @if($answer->question->type === 'multiple_choice')
                                            <div class="flex items-center space-x-3">
                                                <div class="p-2 rounded-lg {{ $answer->is_correct ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600' }}">
                                                    @if($answer->is_correct)
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                    @else
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                    @endif
                                                </div>
                                                <span class="font-bold text-slate-900">{{ $answer->option->option_text ?? 'N/A' }}</span>
                                            </div>
                                            @if(!$answer->is_correct)
                                                @php
                                                    $correct = $answer->question->options->where('is_correct', true)->first();
                                                @endphp
                                                @if($correct)
                                                    <p class="mt-3 text-xs text-slate-500">
                                                        <span class="font-bold text-emerald-600">Correcta:</span> {{ $correct->option_text }}
                                                    </p>
                                                @endif
                                            @endif
                                        @else
                                            <p class="text-slate-900 whitespace-pre-wrap leading-relaxed">{{ $answer->answer_text }}</p>
                                        @endif
                                    </div>
                                </div>

                                <!-- Grading -->
                                <div class="lg:col-span-4 bg-slate-50/50 p-6 rounded-2xl border border-slate-100">
                                    <div class="flex flex-col h-full justify-between">
                                        <div>
                                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-3">Puntaje Asignado</label>
                                            @if($answer->question->type === 'open_ended')
                                                <div class="relative">
                                                    <input type="number" step="0.5" max="{{ $answer->question->points }}" min="0" 
                                                           wire:model.live="grades.{{ $answer->id }}"
                                                           class="w-full pl-4 pr-12 py-3 rounded-xl border-slate-200 focus:ring-indigo-500 focus:border-indigo-500 text-lg font-bold text-slate-900">
                                                    <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                                        <span class="text-slate-400 font-bold">/ {{ $answer->question->points }}</span>
                                                    </div>
                                                </div>
                                                <div class="mt-4 flex space-x-2">
                                                    <button type="button" wire:click="$set('grades.{{ $answer->id }}', 0)" class="px-3 py-1 text-[10px] font-bold uppercase bg-white border border-slate-200 rounded-lg hover:bg-slate-50">0%</button>
                                                    <button type="button" wire:click="$set('grades.{{ $answer->id }}', {{ $answer->question->points * 0.5 }})" class="px-3 py-1 text-[10px] font-bold uppercase bg-white border border-slate-200 rounded-lg hover:bg-slate-50">50%</button>
                                                    <button type="button" wire:click="$set('grades.{{ $answer->id }}', {{ $answer->question->points }})" class="px-3 py-1 text-[10px] font-bold uppercase bg-indigo-50 text-indigo-600 border border-indigo-100 rounded-lg hover:bg-indigo-100">100%</button>
                                                </div>
                                                @error('grades.' . $answer->id)
                                                    <p class="mt-3 text-xs font-bold text-rose-600">{{ $message }}</p>
                                                @enderror
                                            @else
                                                <div class="flex items-center justify-between p-4 rounded-xl bg-white border border-slate-200">
                                                    <span class="text-2xl font-black {{ $answer->is_correct ? 'text-emerald-600' : 'text-slate-400' }}">
                                                        {{ $answer->points_earned }}
                                                    </span>
                                                    <span class="text-sm font-bold text-slate-400">de {{ $answer->question->points }} pts</span>
                                                </div>
                                                <p class="mt-2 text-[10px] text-slate-400 uppercase italic">Calificado automáticamente</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Footer Action -->
            <div class="flex items-center justify-between bg-white p-8 rounded-2xl shadow-sm border border-slate-200">
                <div class="flex items-center space-x-6">
                    <div class="text-center px-6 border-r border-slate-100">
                        <p class="text-xs font-bold text-slate-400 uppercase mb-1">Total Respuestas</p>
                        <p class="text-xl font-black text-slate-900">{{ count($attempt->answers) }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase mb-1">Estatus actual de calificación</p>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700 uppercase tracking-widest">
                            {{ $attempt->status === 'partially_graded' ? 'Parcialmente Calificado' : 'Pendiente' }}
                        </span>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('hr.evaluations.pending-grades') }}" class="px-6 py-3 text-sm font-bold text-slate-600 hover:text-slate-900 transition-colors">
                        Cancelar
                    </a>
                    <button type="submit" class="inline-flex items-center px-8 py-3 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-200">
                        Finalizar Calificación
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

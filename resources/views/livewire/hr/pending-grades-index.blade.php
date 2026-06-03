<div class="p-6">
    <x-page-header title="Calificaciones Pendientes" description="Revisión de respuestas abiertas y validación de exámenes">
    </x-page-header>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
        <table class="w-full text-sm text-left">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100">
                    <th class="px-6 py-4 font-bold text-slate-600">Candidato</th>
                    <th class="px-6 py-4 font-bold text-slate-600">Examen / Intento</th>
                    <th class="px-6 py-4 font-bold text-slate-600">Estado de Calificación</th>
                    <th class="px-6 py-4 font-bold text-slate-600">Fecha Envío</th>
                    <th class="px-6 py-4 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($pendingAttempts as $attempt)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-6 py-4">
                        <p class="font-bold text-slate-800">{{ $attempt->prospectTest->stage->process->prospect?->full_name ?? $attempt->prospectTest->stage->process->employee?->full_name }}</p>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-slate-700 font-medium">{{ $attempt->prospectTest->testTemplate->name }}</p>
                        <p class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">
                            Intento #{{ $attempt->attempt_number }} &middot; {{ $attempt->open_answers_count }} respuesta(s) abierta(s)
                        </p>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider
                            {{ $attempt->status === 'partially_graded' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700' }}">
                            {{ $attempt->status === 'partially_graded' ? 'Tiene Resp. Abiertas' : 'Por Calificar' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-slate-500 text-xs">
                        {{ $attempt->submitted_at ? $attempt->submitted_at->format('d/m/Y H:i') : $attempt->updated_at->diffForHumans() }}
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('hr.test-grading', $attempt) }}" wire:navigate
                           class="inline-flex items-center gap-2 px-3 py-1.5 bg-emerald-50 text-emerald-600 rounded-lg hover:bg-emerald-100 transition-colors font-bold text-xs">
                            Revisar Respuestas
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-10 text-center text-slate-400">
                        No hay exámenes pendientes de calificación manual.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($pendingAttempts->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $pendingAttempts->links() }}
        </div>
        @endif
    </div>
</div>

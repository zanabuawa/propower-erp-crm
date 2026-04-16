<div>
    <x-page-header title="Evaluaciones de desempeño" description="Seguimiento del rendimiento y objetivos del personal">
        <x-slot:actions>
            @can('create hr')
            <button wire:click="openCreate"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg">
                + Nueva evaluación
            </button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    <div class="flex flex-wrap gap-3 mb-5">
        <select wire:model.live="filterEmployee"
                class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
            <option value="">Todos los empleados</option>
            @foreach($employees as $emp)
                <option value="{{ $emp->id }}">{{ $emp->last_name }} {{ $emp->first_name }}</option>
            @endforeach
        </select>
        <select wire:model.live="filterStatus"
                class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
            <option value="">Todos los estados</option>
            @foreach(\App\Models\HrPerformanceEvaluation::STATUSES as $k => $v)
                <option value="{{ $k }}">{{ $v }}</option>
            @endforeach
        </select>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50/60">
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Empleado</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase hidden md:table-cell">Periodo</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase hidden lg:table-cell">Evaluador</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Puntaje</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Estado</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($evaluations as $ev)
                <tr class="hover:bg-slate-50/50">
                    <td class="px-4 py-3 font-medium text-slate-800">{{ $ev->employee?->full_name ?? '—' }}</td>
                    <td class="px-4 py-3 hidden md:table-cell text-slate-600">{{ $ev->period }} <span class="text-slate-400 text-xs">({{ $ev->evaluation_date->format('d/m/Y') }})</span></td>
                    <td class="px-4 py-3 hidden lg:table-cell text-slate-500 text-xs">{{ $ev->evaluator?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-center">
                        @if($ev->overall_score)
                        <span class="text-lg font-bold {{ $ev->score_color }}">{{ $ev->overall_score }}</span>
                        <p class="text-xs text-slate-400">{{ $ev->score_label }}</p>
                        @else
                        <span class="text-slate-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $ev->status_color }}">
                            {{ \App\Models\HrPerformanceEvaluation::STATUSES[$ev->status] ?? $ev->status }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        @can('edit hr')
                        <div class="flex justify-end gap-2">
                            @if($ev->status === 'draft')
                            <button wire:click="submit({{ $ev->id }})"
                                    class="text-xs text-blue-600 hover:text-blue-800">Enviar</button>
                            @endif
                            <button wire:click="openEdit({{ $ev->id }})"
                                    class="text-xs text-slate-500 hover:text-slate-700">Editar</button>
                        </div>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-10 text-center text-slate-400 text-sm">No hay evaluaciones registradas.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($evaluations->hasPages())
        <div class="px-4 py-3 border-t border-slate-100">{{ $evaluations->links() }}</div>
        @endif
    </div>

    {{-- Modal --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40" wire:click.self="$set('showModal', false)">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl p-6 max-h-[90vh] overflow-y-auto">
            <h2 class="text-base font-semibold text-slate-800 mb-4">{{ $editingId ? 'Editar' : 'Nueva' }} evaluación</h2>
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-slate-600 mb-1">Empleado <span class="text-red-500">*</span></label>
                        <select wire:model="employee_id"
                                class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                            <option value="">Seleccionar</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->last_name }} {{ $emp->first_name }}</option>
                            @endforeach
                        </select>
                        @error('employee_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Periodo (ej: 2026-Q2)</label>
                        <input wire:model="period" type="text"
                               class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Fecha evaluación</label>
                        <input wire:model="evaluation_date" type="date"
                               class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    </div>
                </div>

                {{-- Categorías --}}
                <div class="border border-slate-200 rounded-xl p-4">
                    <p class="text-xs font-semibold text-slate-600 mb-3">Evaluación por categorías (0 – 100)</p>
                    <div class="space-y-3">
                        @foreach(\App\Models\HrPerformanceEvaluation::CATEGORY_LABELS as $key => $label)
                        @if(isset($categories[$key]))
                        <div class="flex items-center gap-3">
                            <label class="text-xs text-slate-600 w-44 flex-shrink-0">{{ $label }}</label>
                            <input wire:model.live="categories.{{ $key }}" type="range" min="0" max="100" step="5"
                                   class="flex-1">
                            <span class="text-sm font-bold text-indigo-600 w-10 text-right">{{ $categories[$key] ?? 0 }}</span>
                        </div>
                        @endif
                        @endforeach
                    </div>
                    <p class="text-xs text-slate-400 mt-3">
                        Promedio: <strong>{{ count(array_filter($categories)) > 0 ? round(array_sum($categories) / count(array_filter($categories, fn($v) => isset($v))), 1) : 0 }}/100</strong>
                    </p>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Fortalezas</label>
                    <textarea wire:model="strengths" rows="2"
                              class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30 resize-none"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Áreas de mejora</label>
                    <textarea wire:model="areas_for_improvement" rows="2"
                              class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30 resize-none"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Objetivos próximo periodo</label>
                    <textarea wire:model="goals_next_period" rows="2"
                              class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30 resize-none"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Estado</label>
                    <select wire:model="status"
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                        @foreach(\App\Models\HrPerformanceEvaluation::STATUSES as $k => $v)
                            <option value="{{ $k }}">{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button wire:click="$set('showModal', false)"
                        class="px-4 py-2 text-sm text-slate-600 border border-slate-200 rounded-lg hover:bg-slate-50">Cancelar</button>
                <button wire:click="save"
                        class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">Guardar</button>
            </div>
        </div>
    </div>
    @endif
</div>

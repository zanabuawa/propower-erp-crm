<div>
    <x-page-header title="Cierre mensual" description="Control de cierre de período, checklist de validación y bloqueo de mes">
    </x-page-header>

    <x-alert />

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

        {{-- ── Columna izquierda: grilla de meses ──────────────────────── --}}
        <div class="xl:col-span-1">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-700">Períodos (últimos 12 meses)</h3>
                </div>
                <div class="divide-y divide-gray-100">
                    @foreach($months as $m)
                    @php
                        $key    = $m['year'] . '-' . $m['month'];
                        $period = $periods->get($key);
                        $status = $period?->status ?? 'not_created';
                        $isActive = $viewing && $period && $viewing->id === $period->id;
                    @endphp
                    <button wire:click="openPeriod({{ $m['year'] }}, {{ $m['month'] }})"
                            class="w-full flex items-center justify-between px-5 py-3 hover:bg-gray-50 transition text-left
                                {{ $isActive ? 'bg-indigo-50 border-l-4 border-l-indigo-500' : '' }}">
                        <div>
                            <p class="text-sm font-medium {{ $isActive ? 'text-indigo-700' : 'text-gray-800' }} capitalize">
                                {{ $m['label'] }}
                            </p>
                            @if($period)
                            <p class="text-xs text-gray-400">{{ $period->progress }}% completado</p>
                            @else
                            <p class="text-xs text-gray-300">Sin iniciar</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            @if($period)
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                                {{ \App\Models\FinancePeriodClose::STATUS_COLORS[$period->status] ?? 'bg-gray-100 text-gray-500' }}">
                                {{ \App\Models\FinancePeriodClose::STATUS[$period->status] ?? $period->status }}
                            </span>
                            @else
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-gray-50 text-gray-400">
                                Nuevo
                            </span>
                            @endif
                            <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </button>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ── Columna derecha: panel del período activo ───────────────── --}}
        <div class="xl:col-span-2">

            @if(!$viewing)
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm flex flex-col items-center justify-center py-20 text-center">
                <svg class="w-12 h-12 text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <p class="text-sm text-gray-400">Selecciona un mes para ver o iniciar el cierre</p>
            </div>
            @else

            {{-- Header del período --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 mb-4">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 capitalize">{{ $viewing->period_label }}</h3>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                                {{ \App\Models\FinancePeriodClose::STATUS_COLORS[$viewing->status] ?? 'bg-gray-100 text-gray-500' }}">
                                {{ \App\Models\FinancePeriodClose::STATUS[$viewing->status] ?? $viewing->status }}
                            </span>
                            @if($viewing->status === 'closed' && $viewing->closedBy)
                            <span class="text-xs text-gray-400">Cerrado por {{ $viewing->closedBy->name }} el {{ $viewing->closed_at?->format('d/m/Y H:i') }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="flex gap-2">
                        @if($viewing->status !== 'closed')
                            @if($viewing->can_close)
                            <button wire:click="openCloseModal({{ $viewing->id }})"
                                    class="inline-flex items-center gap-1 bg-green-600 hover:bg-green-700 text-white text-xs font-medium px-3 py-2 rounded-lg transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                Cerrar período
                            </button>
                            @else
                            <span class="text-xs text-gray-400 px-3 py-2 border border-dashed border-gray-200 rounded-lg">
                                Completa el checklist para cerrar
                            </span>
                            @endif
                        @else
                        @can('manage finance')
                        <button wire:click="openReopenModal({{ $viewing->id }})"
                                class="text-xs text-orange-500 hover:text-orange-700 font-medium px-3 py-2 rounded-lg border border-orange-100 hover:bg-orange-50 transition">
                            Reabrir período
                        </button>
                        @endcan
                        @endif
                    </div>
                </div>

                {{-- Barra de progreso del checklist --}}
                <div class="mt-4">
                    <div class="flex justify-between text-xs text-gray-500 mb-1">
                        <span>Progreso del checklist</span>
                        <span>{{ $viewing->progress }}%</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        <div class="h-2 rounded-full transition-all {{ $viewing->progress === 100 ? 'bg-green-500' : 'bg-indigo-500' }}"
                             style="width: {{ $viewing->progress }}%"></div>
                    </div>
                </div>
            </div>

            {{-- KPIs del período --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
                <div class="bg-white rounded-xl border border-gray-200 p-3 shadow-sm">
                    <p class="text-xs font-medium text-gray-500 mb-1">Ingresos</p>
                    <p class="text-base font-bold text-green-600">${{ number_format($viewing->total_income, 0) }}</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-3 shadow-sm">
                    <p class="text-xs font-medium text-gray-500 mb-1">Egresos</p>
                    <p class="text-base font-bold text-red-500">${{ number_format($viewing->total_expense, 0) }}</p>
                </div>
                <div class="bg-white rounded-xl border {{ $viewing->net_result >= 0 ? 'border-green-200' : 'border-red-200' }} p-3 shadow-sm">
                    <p class="text-xs font-medium text-gray-500 mb-1">Resultado neto</p>
                    <p class="text-base font-bold {{ $viewing->net_result >= 0 ? 'text-green-700' : 'text-red-600' }}">
                        ${{ number_format(abs($viewing->net_result), 0) }}
                        <span class="text-xs font-normal">{{ $viewing->net_result >= 0 ? 'utilidad' : 'pérdida' }}</span>
                    </p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-3 shadow-sm">
                    <p class="text-xs font-medium text-gray-500 mb-1">Saldo cierre</p>
                    <p class="text-base font-bold text-gray-800">${{ number_format($viewing->closing_cash, 0) }}</p>
                </div>
            </div>

            {{-- Tabs --}}
            <div class="border-b border-gray-200 mb-4">
                <nav class="flex gap-1">
                    @foreach(['checklist' => 'Checklist', 'summary' => 'Resumen'] as $tab => $label)
                    <button wire:click="$set('activeTab', '{{ $tab }}')"
                            class="px-4 py-2.5 text-sm font-medium border-b-2 transition
                                {{ $activeTab === $tab
                                    ? 'border-indigo-600 text-indigo-600'
                                    : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                        {{ $label }}
                    </button>
                    @endforeach
                </nav>
            </div>

            {{-- Tab: Checklist --}}
            @if($activeTab === 'checklist')
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-700">Checklist de cierre</h3>
                    <p class="text-xs text-gray-400">
                        {{ collect($viewing->checklist ?? [])->where('done', true)->count() }}
                        / {{ count($viewing->checklist ?? []) }} completados
                    </p>
                </div>
                <div class="divide-y divide-gray-100">
                    @foreach($viewing->checklist ?? [] as $item)
                    <div class="flex items-center gap-4 px-5 py-3.5 hover:bg-gray-50/50 transition
                        {{ $item['done'] ? 'opacity-75' : '' }}">
                        <button
                            @if($viewing->status !== 'closed')
                                wire:click="toggleChecklistItem({{ $viewing->id }}, '{{ $item['key'] }}')"
                            @endif
                            class="flex-shrink-0 w-5 h-5 rounded border-2 transition flex items-center justify-center
                                {{ $item['done']
                                    ? 'bg-green-500 border-green-500'
                                    : 'border-gray-300 hover:border-indigo-400 bg-white' }}
                                {{ $viewing->status === 'closed' ? 'cursor-not-allowed' : 'cursor-pointer' }}">
                            @if($item['done'])
                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                            @endif
                        </button>
                        <div class="flex-1">
                            <p class="text-sm {{ $item['done'] ? 'line-through text-gray-400' : 'text-gray-800' }}">
                                {{ $item['label'] }}
                            </p>
                            @if($item['done'] && $item['done_at'])
                            <p class="text-xs text-gray-400 mt-0.5">
                                Completado {{ \Carbon\Carbon::parse($item['done_at'])->format('d/m/Y H:i') }}
                            </p>
                            @endif
                        </div>
                        @if($item['done'])
                        <svg class="w-4 h-4 text-green-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Tab: Resumen --}}
            @if($activeTab === 'summary')
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 space-y-4">
                <h3 class="text-sm font-semibold text-gray-700">Resumen del período</h3>

                <div class="space-y-2">
                    <div class="flex justify-between py-2 border-b border-gray-50">
                        <span class="text-sm text-gray-600">Período</span>
                        <span class="text-sm font-medium text-gray-900 capitalize">{{ $viewing->period_label }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-50">
                        <span class="text-sm text-gray-600">Saldo apertura (cuentas)</span>
                        <span class="text-sm font-medium text-gray-900">${{ number_format($viewing->opening_cash, 2) }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-50">
                        <span class="text-sm text-green-600">+ Total ingresos</span>
                        <span class="text-sm font-semibold text-green-600">${{ number_format($viewing->total_income, 2) }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-50">
                        <span class="text-sm text-red-500">− Total egresos</span>
                        <span class="text-sm font-semibold text-red-500">${{ number_format($viewing->total_expense, 2) }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100 bg-gray-50 px-3 rounded-lg">
                        <span class="text-sm font-bold text-gray-700">Resultado neto</span>
                        <span class="text-sm font-bold {{ $viewing->net_result >= 0 ? 'text-green-700' : 'text-red-600' }}">
                            {{ $viewing->net_result >= 0 ? '+' : '-' }}${{ number_format(abs($viewing->net_result), 2) }}
                        </span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-sm font-bold text-gray-700">Saldo al cierre</span>
                        <span class="text-sm font-bold text-gray-900">${{ number_format($viewing->closing_cash, 2) }}</span>
                    </div>
                </div>

                @if($viewing->notes)
                <div class="pt-3 border-t border-gray-100">
                    <p class="text-xs font-medium text-gray-500 mb-1">Notas del cierre</p>
                    <p class="text-sm text-gray-600 whitespace-pre-line">{{ $viewing->notes }}</p>
                </div>
                @endif
            </div>
            @endif

            @endif {{-- fin @if($viewing) --}}
        </div>
    </div>

    {{-- Modal: cerrar período --}}
    @if($showCloseModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4">
            <h3 class="text-base font-bold text-gray-900 mb-2">Cerrar período</h3>
            <p class="text-sm text-gray-500 mb-4">
                Una vez cerrado, el período quedará bloqueado. Solo un usuario con permiso podrá reabrirlo.
            </p>
            <div class="mb-4">
                <label class="block text-xs font-medium text-gray-600 mb-1">Notas de cierre (opcional)</label>
                <textarea wire:model="closeNotes" rows="3"
                          placeholder="Observaciones, ajustes realizados..."
                          class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 resize-none"></textarea>
            </div>
            <div class="flex gap-3">
                <button wire:click="closePeriod"
                        class="flex-1 bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                    Confirmar cierre
                </button>
                <button wire:click="$set('showCloseModal', false)"
                        class="flex-1 text-gray-600 text-sm font-medium px-4 py-2 rounded-lg border border-gray-200 hover:bg-gray-50 transition">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal: reabrir período --}}
    @if($showReopenModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4">
            <h3 class="text-base font-bold text-gray-900 mb-2">Reabrir período</h3>
            <p class="text-sm text-gray-500 mb-4">
                El período quedará en estado "En revisión". Registra el motivo en los logs internos.
            </p>
            <div class="flex gap-3">
                <button wire:click="reopenPeriod"
                        class="flex-1 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                    Sí, reabrir
                </button>
                <button wire:click="$set('showReopenModal', false)"
                        class="flex-1 text-gray-600 text-sm font-medium px-4 py-2 rounded-lg border border-gray-200 hover:bg-gray-50 transition">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
    @endif
</div>

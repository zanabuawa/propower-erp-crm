<div>
    <x-page-header title="Conciliación de ingresos" description="Verifica que los pagos registrados correspondan al estado de cuenta bancario">
        <x-slot:actions>
            @if($tab === 'pending' && count($selectedIds) > 0)
            <button wire:click="openBatchModal"
                class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Conciliar {{ count($selectedIds) }} seleccionados
            </button>
            @endif
        </x-slot:actions>
    </x-page-header>

    <x-alert />

    {{-- ── KPI Cards ─────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 mb-5">

        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Total cobrado</p>
            <p class="text-xl font-bold text-gray-900">${{ number_format($kpis['totalApplied'], 0) }}</p>
            <p class="text-xs text-gray-400 mt-0.5">{{ $kpis['countPending'] + $kpis['countReconciled'] }} pagos</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Conciliado</p>
            <p class="text-xl font-bold text-emerald-600">${{ number_format($kpis['totalReconciled'], 0) }}</p>
            <p class="text-xs text-gray-400 mt-0.5">{{ $kpis['countReconciled'] }} pagos</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm {{ $kpis['totalPending'] > 0 ? 'border-l-4 border-l-amber-400' : '' }}">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Pendiente</p>
            <p class="text-xl font-bold {{ $kpis['totalPending'] > 0 ? 'text-amber-600' : 'text-gray-400' }}">${{ number_format($kpis['totalPending'], 0) }}</p>
            <p class="text-xs text-gray-400 mt-0.5">{{ $kpis['countPending'] }} pagos</p>
        </div>

        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Avance de conciliación</p>
                <span class="text-sm font-bold {{ $kpis['reconciliationRate'] >= 80 ? 'text-emerald-600' : ($kpis['reconciliationRate'] >= 50 ? 'text-amber-500' : 'text-red-500') }}">
                    {{ $kpis['reconciliationRate'] }}%
                </span>
            </div>
            <div class="h-2.5 bg-gray-100 rounded-full overflow-hidden">
                <div class="h-2.5 rounded-full transition-all duration-500
                    {{ $kpis['reconciliationRate'] >= 80 ? 'bg-emerald-500' : ($kpis['reconciliationRate'] >= 50 ? 'bg-amber-400' : 'bg-red-400') }}"
                     style="width: {{ $kpis['reconciliationRate'] }}%"></div>
            </div>
            <p class="text-xs text-gray-400 mt-1">
                ${{ number_format($kpis['totalReconciled'], 0) }} de ${{ number_format($kpis['totalApplied'], 0) }} verificados
            </p>
        </div>

    </div>

    {{-- ── Tabs ──────────────────────────────────────────────────────────── --}}
    <div class="flex gap-1 mb-4 border-b border-gray-200">
        <button wire:click="$set('tab','pending')"
            class="px-4 py-2 text-sm font-medium transition border-b-2 -mb-px
                   {{ $tab === 'pending' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
            Pendientes de conciliar
            @if($kpis['countPending'] > 0)
            <span class="ml-1.5 inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                {{ $kpis['countPending'] }}
            </span>
            @endif
        </button>
        <button wire:click="$set('tab','reconciled')"
            class="px-4 py-2 text-sm font-medium transition border-b-2 -mb-px
                   {{ $tab === 'reconciled' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
            Conciliados
            <span class="ml-1.5 inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                {{ $kpis['countReconciled'] }}
            </span>
        </button>
    </div>

    {{-- ── Filtros ───────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 mb-4">
        <div class="relative col-span-2 lg:col-span-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input wire:model.live.debounce.300ms="search" type="text"
                placeholder="Folio, cliente, referencia…"
                class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
        </div>
        <select wire:model.live="filterAccount" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
            <option value="">Todas las cuentas</option>
            @foreach($accounts as $acc)
                <option value="{{ $acc->id }}">{{ $acc->name }}</option>
            @endforeach
        </select>
        <select wire:model.live="filterCustomer" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
            <option value="">Todos los clientes</option>
            @foreach($customers as $c)
                <option value="{{ $c->id }}">{{ $c->name }}</option>
            @endforeach
        </select>
        <input wire:model.live="filterDateFrom" type="date"
            class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
        <input wire:model.live="filterDateTo" type="date"
            class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
    </div>

    {{-- Selección masiva (solo en pestaña pending) --}}
    @if($tab === 'pending')
    <div class="flex items-center gap-3 mb-3 text-sm">
        <button wire:click="selectAll" class="text-indigo-600 hover:underline">Seleccionar todos</button>
        @if(count($selectedIds) > 0)
        <span class="text-gray-400">·</span>
        <span class="text-gray-600">{{ count($selectedIds) }} seleccionados</span>
        <button wire:click="clearSelection" class="text-gray-400 hover:text-red-500">Limpiar</button>
        @endif
    </div>
    @endif

    {{-- ── Tabla ─────────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[860px]">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        @if($tab === 'pending')
                        <th class="px-4 py-3 w-10">
                            {{-- checkbox cabecera vacío (selectAll via link) --}}
                        </th>
                        @endif
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Folio pago</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Cliente</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden md:table-cell">Factura</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden lg:table-cell">Cuenta</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden md:table-cell">Método</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Fecha cobro</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Monto</th>
                        @if($tab === 'reconciled')
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden lg:table-cell">Conciliado</th>
                        @endif
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($payments as $payment)
                    @php $isSelected = in_array($payment->id, $selectedIds); @endphp
                    <tr class="hover:bg-gray-50 transition {{ $isSelected ? 'bg-indigo-50' : '' }}">

                        @if($tab === 'pending')
                        <td class="px-4 py-3 text-center">
                            <input type="checkbox"
                                wire:click="toggleSelect({{ $payment->id }})"
                                {{ $isSelected ? 'checked' : '' }}
                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-300 cursor-pointer">
                        </td>
                        @endif

                        <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $payment->folio }}</td>

                        <td class="px-4 py-3 font-medium text-gray-800 max-w-[160px] truncate">
                            {{ $payment->customer->name ?? '—' }}
                        </td>

                        <td class="px-4 py-3 hidden md:table-cell">
                            @if($payment->invoice)
                            <a wire:navigate href="{{ route('sales.invoices.show', $payment->invoice) }}"
                               class="font-mono text-xs text-indigo-600 hover:underline">{{ $payment->invoice->folio }}</a>
                            @else
                            <span class="text-gray-300 text-xs">—</span>
                            @endif
                        </td>

                        <td class="px-4 py-3 hidden lg:table-cell text-gray-500 text-xs truncate max-w-[140px]">
                            {{ $payment->financeAccount->name ?? '—' }}
                        </td>

                        <td class="px-4 py-3 hidden md:table-cell">
                            <span class="text-xs text-gray-600">
                                {{ \App\Models\SalePayment::PAYMENT_METHODS[$payment->payment_method] ?? $payment->payment_method }}
                            </span>
                        </td>

                        <td class="px-4 py-3 text-xs text-gray-600">
                            {{ $payment->paid_at?->format('d/m/Y') ?? '—' }}
                        </td>

                        <td class="px-4 py-3 text-right font-semibold text-gray-900">
                            ${{ number_format($payment->amount, 2) }}
                        </td>

                        @if($tab === 'reconciled')
                        <td class="px-4 py-3 hidden lg:table-cell">
                            <div class="text-xs text-emerald-600">{{ $payment->reconciled_at?->format('d/m/Y') }}</div>
                            @if($payment->reconciliation_note)
                            <div class="text-xs text-gray-400 truncate max-w-[160px]" title="{{ $payment->reconciliation_note }}">
                                {{ $payment->reconciliation_note }}
                            </div>
                            @endif
                        </td>
                        @endif

                        <td class="px-4 py-3 text-right">
                            @if($tab === 'pending')
                                @if($reconcilingId === $payment->id)
                                {{-- Formulario inline de conciliación --}}
                                <div class="flex items-center gap-2 justify-end">
                                    <input wire:model="reconcilingNote" type="text"
                                        placeholder="Nota (opcional)"
                                        class="border border-gray-200 rounded px-2 py-1 text-xs w-36 focus:outline-none focus:ring-1 focus:ring-indigo-300">
                                    <button wire:click="confirmReconcile"
                                        class="px-2.5 py-1 text-xs bg-emerald-600 hover:bg-emerald-700 text-white rounded transition font-medium">
                                        Confirmar
                                    </button>
                                    <button wire:click="cancelReconcile"
                                        class="px-2.5 py-1 text-xs border border-gray-200 hover:bg-gray-50 rounded transition text-gray-600">
                                        ✕
                                    </button>
                                </div>
                                @else
                                <button wire:click="openReconcile({{ $payment->id }})"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-lg transition font-medium border border-emerald-200">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Conciliar
                                </button>
                                @endif
                            @else
                                <button wire:click="revert({{ $payment->id }})"
                                    wire:confirm="¿Revertir esta conciliación?"
                                    class="text-xs text-gray-400 hover:text-red-500 transition">
                                    Revertir
                                </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $tab === 'pending' ? 9 : 9 }}" class="px-5 py-12 text-center">
                            @if($tab === 'pending')
                            <div class="text-emerald-500 text-sm font-medium">Todo está conciliado</div>
                            <div class="text-gray-400 text-xs mt-1">No hay pagos pendientes de verificar.</div>
                            @else
                            <div class="text-gray-400 text-sm">Aún no hay pagos conciliados.</div>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($payments->count())
                <tfoot>
                    <tr class="border-t-2 border-gray-200 bg-gray-50">
                        <td colspan="{{ $tab === 'pending' ? 7 : 6 }}"
                            class="px-4 py-3 text-xs text-gray-500 text-right uppercase tracking-wide hidden md:table-cell">
                            Total filtrado
                        </td>
                        <td class="px-4 py-3 text-right font-bold text-gray-900">
                            ${{ number_format($filteredTotal, 2) }}
                        </td>
                        <td colspan="1"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        @if($payments->hasPages())
        <div class="px-5 py-3 border-t border-gray-100">{{ $payments->links('vendor.pagination.tailwind') }}</div>
        @endif
    </div>

    {{-- ── Modal: conciliación en lote ─────────────────────────────────── --}}
    @if($showBatchModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40" wire:click="$set('showBatchModal',false)"></div>
        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md p-6">
            <h3 class="text-base font-semibold text-gray-900 mb-3">Conciliar en lote</h3>
            <p class="text-sm text-gray-600 mb-4">
                Se marcarán como conciliados
                <span class="font-semibold text-gray-900">{{ count($selectedIds) }} pagos</span>
                con la fecha y hora actual.
            </p>
            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nota de conciliación <span class="text-gray-400 font-normal">(opcional)</span>
                </label>
                <input wire:model="batchNote" type="text"
                    placeholder="Ej. Revisado contra edo. cta. BBVA abr-2026"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
            </div>
            <div class="flex justify-end gap-3">
                <button wire:click="$set('showBatchModal',false)"
                    class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                    Cancelar
                </button>
                <button wire:click="confirmBatch" wire:loading.attr="disabled" wire:target="confirmBatch"
                    class="px-5 py-2 text-sm bg-emerald-600 hover:bg-emerald-700 disabled:opacity-60 text-white rounded-lg font-medium transition flex items-center gap-2">
                    <span wire:loading.remove wire:target="confirmBatch">Confirmar conciliación</span>
                    <span wire:loading wire:target="confirmBatch">Procesando…</span>
                </button>
            </div>
        </div>
    </div>
    @endif
</div>

<div>
    <x-page-header title="Conciliación de pagos a proveedores" description="Verifica que los pagos registrados coincidan con movimientos bancarios">
    </x-page-header>

    <x-alert />

    {{-- KPIs --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Total pagado</p>
            <p class="text-xl font-bold text-gray-900">${{ number_format($kpis['total_applied'], 0) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-green-200 p-4 shadow-sm">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Conciliado</p>
            <p class="text-xl font-bold text-green-600">${{ number_format($kpis['total_reconciled'], 0) }}</p>
            <p class="text-xs text-gray-400">{{ $kpis['rate'] }}% del total</p>
        </div>
        <div class="bg-white rounded-xl border {{ $kpis['count_pending'] > 0 ? 'border-l-4 border-l-amber-400' : 'border-gray-200' }} p-4 shadow-sm">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Por conciliar</p>
            <p class="text-xl font-bold {{ $kpis['count_pending'] > 0 ? 'text-amber-600' : 'text-gray-400' }}">${{ number_format($kpis['total_pending'], 0) }}</p>
            <p class="text-xs text-gray-400">{{ $kpis['count_pending'] }} pagos</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm flex flex-col justify-center">
            <div class="flex items-center justify-between mb-1">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Avance</p>
                <p class="text-sm font-bold text-gray-900">{{ $kpis['rate'] }}%</p>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-2">
                <div class="bg-green-500 h-2 rounded-full transition-all"
                     style="width: {{ min(100, $kpis['rate']) }}%"></div>
            </div>
        </div>
    </div>

    {{-- Filtros + acción masiva --}}
    <div class="flex flex-col sm:flex-row gap-3 mb-4">
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input wire:model.live.debounce.300ms="search" type="text"
                   placeholder="Folio, referencia o proveedor..."
                   class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
        </div>
        <select wire:model.live="filterAccount" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
            <option value="">Todas las cuentas</option>
            @foreach($accounts as $acc)
                <option value="{{ $acc->id }}">{{ $acc->name }}</option>
            @endforeach
        </select>
        <input wire:model.live="filterDateFrom" type="date"
               class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
        <input wire:model.live="filterDateTo" type="date"
               class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
        @if($activeTab === 'pending' && count($selected) > 0)
        <button wire:click="openBatchModal"
                class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition whitespace-nowrap">
            Conciliar {{ count($selected) }} seleccionados
        </button>
        @endif
    </div>

    {{-- Tabs --}}
    <div class="border-b border-gray-200 mb-4">
        <nav class="flex gap-1">
            @foreach(['pending' => 'Por conciliar', 'reconciled' => 'Conciliados'] as $tab => $label)
            <button wire:click="$set('activeTab', '{{ $tab }}')"
                    class="px-4 py-2.5 text-sm font-medium border-b-2 transition
                        {{ $activeTab === $tab
                            ? 'border-indigo-600 text-indigo-600'
                            : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                {{ $label }}
                @if($tab === 'pending' && $kpis['count_pending'] > 0)
                    <span class="ml-1 inline-flex items-center justify-center w-5 h-5 rounded-full bg-amber-100 text-amber-700 text-[10px] font-bold">
                        {{ $kpis['count_pending'] }}
                    </span>
                @endif
            </button>
            @endforeach
        </nav>
    </div>

    {{-- Tabla --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[850px]">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        @if($activeTab === 'pending')
                        <th class="px-4 py-3 w-10">
                            <input type="checkbox" class="rounded border-gray-300"
                                   onchange="
                                    document.querySelectorAll('.row-check').forEach(cb => {
                                        if(this.checked) {
                                            @this.call('toggleSelect', parseInt(cb.dataset.id))
                                        }
                                    })
                                   ">
                        </th>
                        @endif
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Folio</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Proveedor</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden md:table-cell">Cuenta</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden lg:table-cell">Método</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Fecha pago</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Monto</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden md:table-cell">Referencia</th>
                        @if($activeTab === 'reconciled')
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Conciliado</th>
                        @endif
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($payments as $pmt)
                    <tr class="hover:bg-gray-50 transition {{ in_array($pmt->id, $selected) ? 'bg-indigo-50/50' : '' }}">
                        @if($activeTab === 'pending')
                        <td class="px-4 py-3">
                            <input type="checkbox" class="row-check rounded border-gray-300"
                                   data-id="{{ $pmt->id }}"
                                   wire:click="toggleSelect({{ $pmt->id }})"
                                   {{ in_array($pmt->id, $selected) ? 'checked' : '' }}>
                        </td>
                        @endif
                        <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ $pmt->folio }}</td>
                        <td class="px-4 py-3 font-medium text-gray-800 max-w-[140px] truncate">
                            {{ $pmt->invoice?->supplier?->name ?? '—' }}
                        </td>
                        <td class="px-4 py-3 hidden md:table-cell text-xs text-gray-500">
                            {{ $pmt->financeAccount?->name ?? '—' }}
                        </td>
                        <td class="px-4 py-3 hidden lg:table-cell text-xs text-gray-500">
                            {{ \App\Models\SupplierPayment::PAYMENT_METHODS[$pmt->payment_method] ?? $pmt->payment_method }}
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-600">{{ $pmt->paid_at?->format('d/m/Y') ?? '—' }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-900">${{ number_format($pmt->amount, 2) }}</td>
                        <td class="px-4 py-3 hidden md:table-cell text-xs text-gray-400">{{ $pmt->reference ?? '—' }}</td>
                        @if($activeTab === 'reconciled')
                        <td class="px-4 py-3">
                            <p class="text-xs text-green-600">{{ $pmt->reconciled_at?->format('d/m/Y') }}</p>
                            <p class="text-xs text-gray-400">{{ $pmt->reconciledBy?->name }}</p>
                            @if($pmt->reconciliation_note)
                            <p class="text-xs text-gray-400 italic truncate max-w-[140px]">{{ $pmt->reconciliation_note }}</p>
                            @endif
                        </td>
                        @endif
                        <td class="px-4 py-3 text-right">
                            @if($activeTab === 'pending')
                            <button wire:click="openConfirm({{ $pmt->id }})"
                                    class="text-xs text-green-600 hover:text-green-800 font-medium px-2 py-1 rounded border border-green-200 hover:bg-green-50 transition">
                                Conciliar
                            </button>
                            @else
                            <button wire:click="revert({{ $pmt->id }})"
                                    class="text-xs text-gray-400 hover:text-orange-600 font-medium px-2 py-1 rounded border border-gray-200 hover:border-orange-200 transition">
                                Revertir
                            </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $activeTab === 'pending' ? 9 : 9 }}" class="px-5 py-10 text-center text-gray-400 text-sm">
                            {{ $activeTab === 'pending' ? 'No hay pagos pendientes de conciliar.' : 'No hay pagos conciliados.' }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($payments->hasPages())
        <div class="px-5 py-3 border-t border-gray-100">{{ $payments->links() }}</div>
        @endif
    </div>

    {{-- Modal: conciliar individual --}}
    @if($showConfirmModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4">
            <h3 class="text-base font-bold text-gray-900 mb-2">Confirmar conciliación</h3>
            <p class="text-sm text-gray-500 mb-4">
                ¿Confirmas que este pago ha sido verificado contra el estado de cuenta bancario?
            </p>
            <div class="mb-4">
                <label class="block text-xs font-medium text-gray-600 mb-1">Nota (opcional)</label>
                <input wire:model="reconciliationNote" type="text"
                       placeholder="Ej. Verificado en extracto junio 2026"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
            </div>
            <div class="flex gap-3">
                <button wire:click="confirmReconcile"
                        class="flex-1 bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                    Sí, conciliar
                </button>
                <button wire:click="$set('showConfirmModal', false)"
                        class="flex-1 text-gray-600 text-sm font-medium px-4 py-2 rounded-lg border border-gray-200 hover:bg-gray-50 transition">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal: conciliación masiva --}}
    @if($showBatchModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4">
            <h3 class="text-base font-bold text-gray-900 mb-2">Conciliación masiva</h3>
            <p class="text-sm text-gray-500 mb-4">
                Se conciliarán <strong class="text-gray-800">{{ count($selected) }} pagos</strong> seleccionados.
            </p>
            <div class="mb-4">
                <label class="block text-xs font-medium text-gray-600 mb-1">Nota para todos (opcional)</label>
                <input wire:model="batchNote" type="text"
                       placeholder="Ej. Conciliación mes de abril 2026"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
            </div>
            <div class="flex gap-3">
                <button wire:click="confirmBatch"
                        class="flex-1 bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                    Conciliar todos
                </button>
                <button wire:click="$set('showBatchModal', false)"
                        class="flex-1 text-gray-600 text-sm font-medium px-4 py-2 rounded-lg border border-gray-200 hover:bg-gray-50 transition">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
    @endif
</div>

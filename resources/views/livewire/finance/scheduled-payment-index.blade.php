<div>
    <x-page-header title="Programación de pagos" description="Agenda, controla y ejecuta pagos futuros y recurrentes">
        <x-slot:actions>
            @can('create finance')
            <button wire:click="openCreate"
                    class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Programar pago
            </button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <x-alert />

    {{-- KPIs --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
        <div class="bg-white rounded-xl border {{ $kpis['overdue'] > 0 ? 'border-l-4 border-l-red-500' : 'border-gray-200' }} p-4 shadow-sm">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Vencidos</p>
            <p class="text-2xl font-bold {{ $kpis['overdue'] > 0 ? 'text-red-600' : 'text-gray-400' }}">{{ $kpis['overdue'] }}</p>
            <p class="text-xs text-gray-400">requieren pago inmediato</p>
        </div>
        <div class="bg-white rounded-xl border {{ $kpis['due_today'] > 0 ? 'border-l-4 border-l-orange-400' : 'border-gray-200' }} p-4 shadow-sm">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Vencen hoy</p>
            <p class="text-2xl font-bold {{ $kpis['due_today'] > 0 ? 'text-orange-600' : 'text-gray-400' }}">{{ $kpis['due_today'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Próximos 7 días</p>
            <p class="text-2xl font-bold text-blue-600">{{ $kpis['due_week'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Total por pagar</p>
            <p class="text-2xl font-bold text-gray-900">${{ number_format($kpis['total_pending'], 0) }}</p>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
        <div class="relative sm:col-span-2">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input wire:model.live.debounce.300ms="search" type="text"
                   placeholder="Folio, concepto o proveedor..."
                   class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
        </div>
        <select wire:model.live="filterStatus" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
            <option value="">Todos los estados</option>
            @foreach(\App\Models\ScheduledPayment::STATUS as $k => $v)
                <option value="{{ $k }}">{{ $v }}</option>
            @endforeach
        </select>
        <select wire:model.live="filterCategory" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
            <option value="">Todas las categorías</option>
            @foreach(\App\Models\ScheduledPayment::CATEGORIES as $k => $v)
                <option value="{{ $k }}">{{ $v }}</option>
            @endforeach
        </select>
    </div>

    {{-- Tabla --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[900px]">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Concepto</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden lg:table-cell">Categoría</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden md:table-cell">Cuenta</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Fecha</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden lg:table-cell">Frecuencia</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Monto</th>
                        <th class="text-center px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Estado</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($payments as $sp)
                    @php
                        $rowBg = match($sp->status) {
                            'overdue' => 'bg-red-50/40',
                            default   => '',
                        };
                    @endphp
                    <tr class="hover:bg-gray-50 transition {{ $rowBg }}">
                        <td class="px-5 py-3">
                            <p class="font-medium text-gray-900">{{ $sp->concept }}</p>
                            @if($sp->supplier)
                            <p class="text-xs text-gray-400">{{ $sp->supplier->name }}</p>
                            @endif
                            <p class="text-xs font-mono text-gray-300">{{ $sp->folio }}</p>
                        </td>
                        <td class="px-5 py-3 hidden lg:table-cell text-xs text-gray-500">
                            {{ \App\Models\ScheduledPayment::CATEGORIES[$sp->category] ?? $sp->category }}
                        </td>
                        <td class="px-5 py-3 hidden md:table-cell text-xs text-gray-500">
                            {{ $sp->financeAccount?->name ?? '—' }}
                        </td>
                        <td class="px-5 py-3 text-xs {{ $sp->status === 'overdue' ? 'text-red-600 font-semibold' : 'text-gray-600' }}">
                            {{ $sp->scheduled_date?->format('d/m/Y') ?? '—' }}
                            @if($sp->status === 'overdue')
                            <div class="text-red-400 font-normal">{{ $sp->scheduled_date->diffForHumans() }}</div>
                            @endif
                            @if($sp->status === 'paid' && $sp->paid_at)
                            <div class="text-green-500 font-normal">Pagado {{ $sp->paid_at->format('d/m/Y') }}</div>
                            @endif
                        </td>
                        <td class="px-5 py-3 hidden lg:table-cell text-xs text-gray-500">
                            {{ \App\Models\ScheduledPayment::FREQUENCIES[$sp->frequency] ?? $sp->frequency }}
                        </td>
                        <td class="px-5 py-3 text-right font-semibold text-gray-900">
                            ${{ number_format($sp->amount, 2) }}
                            <div class="text-xs font-normal text-gray-400">{{ $sp->currency }}</div>
                        </td>
                        <td class="px-5 py-3 text-center">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                                {{ \App\Models\ScheduledPayment::STATUS_COLORS[$sp->status] ?? 'bg-gray-100 text-gray-500' }}">
                                {{ \App\Models\ScheduledPayment::STATUS[$sp->status] ?? $sp->status }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                @if(in_array($sp->status, ['pending', 'overdue']))
                                    @can('create finance')
                                    <button wire:click="openExecute({{ $sp->id }})"
                                            class="text-xs text-green-600 hover:text-green-800 font-medium px-2 py-1 rounded border border-green-200 hover:bg-green-50 transition">
                                        Pagar
                                    </button>
                                    <button wire:click="openEdit({{ $sp->id }})"
                                            class="text-xs text-indigo-500 hover:text-indigo-700 px-2 py-1 rounded border border-indigo-100 hover:bg-indigo-50 transition">
                                        Editar
                                    </button>
                                    <button wire:click="openCancel({{ $sp->id }})"
                                            class="text-xs text-red-400 hover:text-red-600 px-2 py-1 rounded border border-red-100 hover:bg-red-50 transition">
                                        Cancelar
                                    </button>
                                    @endcan
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-10 text-center text-gray-400 text-sm">No hay pagos programados.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($payments->hasPages())
        <div class="px-5 py-3 border-t border-gray-100">{{ $payments->links('vendor.pagination.tailwind') }}</div>
        @endif
    </div>

    {{-- ── Panel lateral: formulario de crear/editar ──────────────────────── --}}
    @if($showForm)
    <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="bg-white rounded-t-2xl sm:rounded-2xl shadow-2xl w-full max-w-lg mx-0 sm:mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="text-base font-bold text-gray-900">
                    {{ $editId ? 'Editar pago programado' : 'Programar nuevo pago' }}
                </h3>
                <button wire:click="$set('showForm', false)" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="px-6 py-5 space-y-4">

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Concepto <span class="text-red-400">*</span></label>
                    <input wire:model="concept" type="text" placeholder="Ej. Renta local, nómina quincenal..."
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 {{ $errors->has('concept') ? 'border-red-400' : '' }}">
                    @error('concept')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Categoría</label>
                        <select wire:model="category"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
                            @foreach(\App\Models\ScheduledPayment::CATEGORIES as $k => $v)
                                <option value="{{ $k }}">{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Frecuencia</label>
                        <select wire:model="frequency"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
                            @foreach(\App\Models\ScheduledPayment::FREQUENCIES as $k => $v)
                                <option value="{{ $k }}">{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Monto <span class="text-red-400">*</span></label>
                        <input wire:model="amount" type="number" min="0.01" step="0.01"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 {{ $errors->has('amount') ? 'border-red-400' : '' }}">
                        @error('amount')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Moneda</label>
                        <select wire:model="currency"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
                            <option value="MXN">MXN</option>
                            <option value="USD">USD</option>
                            <option value="EUR">EUR</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Fecha de pago <span class="text-red-400">*</span></label>
                        <input wire:model="scheduledDate" type="date"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        @error('scheduledDate')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                    @if($frequency !== 'once')
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Fin recurrencia</label>
                        <input wire:model="endDate" type="date"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    </div>
                    @endif
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Cuenta bancaria</label>
                    <select wire:model="financeAccountId"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
                        <option value="">Sin cuenta específica</option>
                        @foreach($accounts as $acc)
                        <option value="{{ $acc['id'] }}">
                            {{ $acc['name'] }} ({{ $acc['currency'] }}) — ${{ number_format($acc['current_balance'], 2) }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Proveedor</label>
                    <select wire:model="supplierId"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
                        <option value="">Sin proveedor</option>
                        @foreach($suppliers as $s)
                        <option value="{{ $s['id'] }}">{{ $s['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Referencia</label>
                    <input wire:model="reference" type="text"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Notas</label>
                    <textarea wire:model="notes" rows="2"
                              class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 resize-none"></textarea>
                </div>

                <div class="flex gap-3 pt-2">
                    <button wire:click="save" wire:loading.attr="disabled"
                            class="flex-1 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white text-sm font-medium px-4 py-2.5 rounded-lg transition">
                        <span wire:loading.remove wire:target="save">
                            {{ $editId ? 'Actualizar' : 'Programar pago' }}
                        </span>
                        <span wire:loading wire:target="save">Guardando...</span>
                    </button>
                    <button wire:click="$set('showForm', false)"
                            class="text-gray-600 text-sm font-medium px-4 py-2.5 rounded-lg border border-gray-200 hover:bg-gray-50 transition">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal: ejecutar pago --}}
    @if($showExecuteModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4">
            <h3 class="text-base font-bold text-gray-900 mb-2">Ejecutar pago</h3>
            <p class="text-sm text-gray-500 mb-4">
                Se registrará un egreso en la cuenta bancaria seleccionada y el pago quedará marcado como pagado.
                @if(($payments->firstWhere('id', $executeId)?->frequency ?? 'once') !== 'once')
                <br><span class="text-indigo-600 font-medium">Este pago es recurrente — se creará automáticamente el siguiente.</span>
                @endif
            </p>
            <div class="mb-4">
                <label class="block text-xs font-medium text-gray-600 mb-1">Notas del pago (opcional)</label>
                <input wire:model="executeNotes" type="text"
                       placeholder="Ej. Transferencia SPEI #12345"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
            </div>
            <div class="flex gap-3">
                <button wire:click="executePayment" wire:loading.attr="disabled"
                        class="flex-1 bg-green-600 hover:bg-green-700 disabled:opacity-60 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                    <span wire:loading.remove wire:target="executePayment">Confirmar pago</span>
                    <span wire:loading wire:target="executePayment">Procesando...</span>
                </button>
                <button wire:click="$set('showExecuteModal', false)"
                        class="flex-1 text-gray-600 text-sm font-medium px-4 py-2 rounded-lg border border-gray-200 hover:bg-gray-50 transition">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal: cancelar --}}
    @if($showCancelModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-sm mx-4">
            <h3 class="text-base font-bold text-gray-900 mb-2">¿Cancelar este pago?</h3>
            <p class="text-sm text-gray-500 mb-4">No se generará ninguna transacción. Puedes volver a programarlo después.</p>
            <div class="flex gap-3">
                <button wire:click="cancelPayment"
                        class="flex-1 bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                    Sí, cancelar
                </button>
                <button wire:click="$set('showCancelModal', false)"
                        class="flex-1 text-gray-600 text-sm font-medium px-4 py-2 rounded-lg border border-gray-200 hover:bg-gray-50 transition">
                    Volver
                </button>
            </div>
        </div>
    </div>
    @endif

</div>

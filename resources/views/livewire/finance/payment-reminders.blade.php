<div>
    <x-page-header title="Recordatorios de pago" description="Control de vencimientos y envío de recordatorios a clientes">
        <x-slot:actions>
            <button wire:click="openBatchModal"
                class="inline-flex items-center gap-2 border border-indigo-300 text-indigo-700 hover:bg-indigo-50 text-sm font-medium px-4 py-2 rounded-lg transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                Enviar recordatorios en lote
            </button>
        </x-slot:actions>
    </x-page-header>

    <x-alert />

    {{-- ── Badges de urgencia ───────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
        <div wire:click="$set('filterUrgency','')"
            class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm cursor-pointer
                   {{ $filterUrgency === '' ? 'ring-2 ring-indigo-400' : 'hover:border-indigo-200' }}">
            <div class="text-xs text-gray-500 mb-1">Todos (próx. 7 días)</div>
            <div class="text-xl font-bold text-gray-900">
                {{ $counts['overdue'] + $counts['due-today'] + $counts['due-soon'] }}
            </div>
            <div class="text-xs text-gray-400">facturas pendientes</div>
        </div>
        <div wire:click="$set('filterUrgency','overdue')"
            class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm cursor-pointer
                   {{ $filterUrgency === 'overdue' ? 'ring-2 ring-red-400' : 'hover:border-red-200' }}">
            <div class="text-xs text-gray-500 mb-1">Vencidas</div>
            <div class="text-xl font-bold text-red-600">{{ $counts['overdue'] }}</div>
            <div class="text-xs text-gray-400">requieren atención inmediata</div>
        </div>
        <div wire:click="$set('filterUrgency','due-today')"
            class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm cursor-pointer
                   {{ $filterUrgency === 'due-today' ? 'ring-2 ring-orange-400' : 'hover:border-orange-200' }}">
            <div class="text-xs text-gray-500 mb-1">Vencen hoy</div>
            <div class="text-xl font-bold text-orange-600">{{ $counts['due-today'] }}</div>
            <div class="text-xs text-gray-400">último día de pago</div>
        </div>
        <div wire:click="$set('filterUrgency','due-soon')"
            class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm cursor-pointer
                   {{ $filterUrgency === 'due-soon' ? 'ring-2 ring-yellow-400' : 'hover:border-yellow-200' }}">
            <div class="text-xs text-gray-500 mb-1">Próximos 7 días</div>
            <div class="text-xl font-bold text-yellow-600">{{ $counts['due-soon'] }}</div>
            <div class="text-xs text-gray-400">por vencer pronto</div>
        </div>
    </div>

    {{-- ── Filtros ──────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-5">
        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input wire:model.live.debounce.300ms="search" type="text"
                placeholder="Buscar por folio o cliente..."
                class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
        </div>
        <select wire:model.live="filterCustomer" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
            <option value="">Todos los clientes</option>
            @foreach($customers as $c)
                <option value="{{ $c->id }}">{{ $c->name }}</option>
            @endforeach
        </select>
    </div>

    {{-- ── Tabla de facturas ────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[820px]">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Cliente</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Folio</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Vencimiento</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide">Saldo</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden lg:table-cell">Último recordatorio</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wide hidden md:table-cell">Email cliente</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($invoices as $inv)
                    @php
                        $balance    = $inv->total - $inv->paid_amount;
                        $daysOverdue = $inv->due_at
                            ? (int) now()->startOfDay()->diffInDays($inv->due_at, false) * -1
                            : 0;
                        $isOverdue  = $daysOverdue > 0;
                        $isDueToday = ! $isOverdue && $daysOverdue === 0;

                        $urgencyColor = $isOverdue
                            ? 'bg-red-50 border-l-4 border-red-400'
                            : ($isDueToday ? 'bg-orange-50 border-l-4 border-orange-400' : '');

                        $primaryEmail = $inv->customer?->emails
                            ->sortByDesc('is_primary')->first()?->email;
                    @endphp
                    <tr class="hover:bg-gray-50 transition {{ $urgencyColor }}">
                        <td class="px-5 py-3">
                            <div class="font-medium text-gray-900 truncate max-w-[160px]">{{ $inv->customer->name ?? '—' }}</div>
                        </td>
                        <td class="px-5 py-3">
                            <a wire:navigate href="{{ route('sales.invoices.show', $inv) }}"
                               class="font-mono text-xs text-indigo-600 hover:underline">{{ $inv->folio }}</a>
                        </td>
                        <td class="px-5 py-3">
                            <div class="text-xs {{ $isOverdue ? 'text-red-600 font-semibold' : ($isDueToday ? 'text-orange-600 font-semibold' : 'text-gray-600') }}">
                                {{ $inv->due_at->format('d/m/Y') }}
                            </div>
                            @if($isOverdue)
                                <div class="text-xs text-red-400">{{ $daysOverdue }} día(s) vencida</div>
                            @elseif($isDueToday)
                                <div class="text-xs text-orange-400">Vence hoy</div>
                            @else
                                <div class="text-xs text-gray-400">{{ abs($daysOverdue) }} día(s) restantes</div>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right font-semibold text-gray-900">
                            ${{ number_format($balance, 2) }}
                        </td>
                        <td class="px-5 py-3 hidden lg:table-cell">
                            @if($inv->reminder_sent_at)
                                <div class="text-xs text-gray-500">{{ $inv->reminder_sent_at->diffForHumans() }}</div>
                                <div class="text-xs text-gray-400">{{ $inv->reminder_count }} envío(s)</div>
                            @else
                                <span class="text-xs text-gray-300">Sin recordatorios</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 hidden md:table-cell">
                            @if($primaryEmail)
                                <span class="text-xs text-gray-500 truncate max-w-[160px] block">{{ $primaryEmail }}</span>
                            @else
                                <span class="text-xs text-red-400 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Sin email
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a wire:navigate href="{{ route('sales.invoices.show', $inv) }}"
                                   class="text-xs text-gray-400 hover:text-indigo-600 transition">Ver</a>
                                @if($primaryEmail)
                                <button
                                    wire:click="sendReminder({{ $inv->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="sendReminder({{ $inv->id }})"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white rounded-lg transition font-medium">
                                    <span wire:loading.remove wire:target="sendReminder({{ $inv->id }})">
                                        Enviar
                                    </span>
                                    <span wire:loading wire:target="sendReminder({{ $inv->id }})">
                                        Enviando…
                                    </span>
                                </button>
                                @else
                                <span class="text-xs text-gray-300 cursor-not-allowed px-3 py-1.5">Sin email</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center">
                            <div class="text-gray-400 text-sm">No hay facturas que requieran recordatorio con los filtros actuales.</div>
                            <div class="text-gray-300 text-xs mt-1">Solo se muestran facturas con vencimiento en los próximos 7 días o ya vencidas.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sendError)
        <div class="px-5 py-3 bg-red-50 border-t border-red-100 text-sm text-red-600">
            {{ $sendError }}
        </div>
        @endif

        @if($invoices->hasPages())
        <div class="px-5 py-3 border-t border-gray-100">{{ $invoices->links('vendor.pagination.tailwind') }}</div>
        @endif
    </div>

    {{-- ── Modal: envío masivo ─────────────────────────────────────────── --}}
    @if($showBatchModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40" wire:click="$set('showBatchModal', false)"></div>
        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md p-6">
            <h3 class="text-base font-semibold text-gray-900 mb-2">Envío masivo de recordatorios</h3>
            <p class="text-sm text-gray-600 mb-4">
                Se enviará un correo de recordatorio a los clientes de
                <span class="font-semibold text-gray-900">{{ count($selectedIds) }} facturas</span>
                con vencimiento pendiente (solo a las que tienen email registrado).
            </p>
            <div class="bg-amber-50 border border-amber-200 rounded-lg px-4 py-3 text-xs text-amber-700 mb-5">
                <strong>Nota:</strong> Los clientes sin dirección de correo registrada serán omitidos automáticamente.
            </div>
            <div class="flex justify-end gap-3">
                <button wire:click="$set('showBatchModal', false)"
                    class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                    Cancelar
                </button>
                <button wire:click="sendBatch" wire:loading.attr="disabled" wire:target="sendBatch"
                    class="px-5 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white rounded-lg transition font-medium flex items-center gap-2">
                    <span wire:loading.remove wire:target="sendBatch">Enviar recordatorios</span>
                    <span wire:loading wire:target="sendBatch" class="flex items-center gap-1.5">
                        <svg class="animate-spin h-3.5 w-3.5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                        </svg>
                        Enviando…
                    </span>
                </button>
            </div>
        </div>
    </div>
    @endif
</div>

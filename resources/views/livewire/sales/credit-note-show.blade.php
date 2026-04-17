<div>
    @php
        $statusColor = match($creditNote->status) {
            'draft'     => 'bg-gray-100 text-gray-600',
            'applied'   => 'bg-green-100 text-green-700',
            'cancelled' => 'bg-red-100 text-red-600',
            default     => 'bg-gray-100 text-gray-500',
        };
        $statusLabel = \App\Models\SaleCreditNote::STATUS[$creditNote->status] ?? $creditNote->status;
    @endphp

    {{-- ── Encabezado ─────────────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-6">
        <div class="flex items-center gap-3 flex-1">
            <a wire:navigate href="{{ route('sales.credit-notes.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-3 flex-wrap">
                    <h1 class="text-xl font-medium text-gray-900">{{ $creditNote->folio }}</h1>
                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                        {{ $statusLabel }}
                    </span>
                </div>
                <p class="text-sm text-gray-500">{{ $creditNote->customer->name ?? '—' }}</p>
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            @if($creditNote->status === 'draft')
                <button wire:click="$set('showApplyModal', true)"
                    class="px-4 py-2 text-sm bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition">
                    Aplicar a factura
                </button>
                <button wire:click="$set('showCancelModal', true)"
                    class="px-4 py-2 text-sm border border-red-200 text-red-600 hover:bg-red-50 rounded-lg transition">
                    Cancelar nota
                </button>
            @endif
        </div>
    </div>

    <x-alert />

    {{-- ── Datos generales ─────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-5">
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Datos de la nota</h2>
            <dl class="grid grid-cols-2 sm:grid-cols-3 gap-x-6 gap-y-3 text-sm">
                <div>
                    <dt class="text-xs text-gray-400">Folio</dt>
                    <dd class="font-mono font-medium text-gray-900">{{ $creditNote->folio }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-400">Fecha</dt>
                    <dd class="text-gray-700">{{ $creditNote->created_at->format('d/m/Y') }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-400">Moneda</dt>
                    <dd class="text-gray-700">{{ $creditNote->currency }}</dd>
                </div>
                <div class="col-span-2 sm:col-span-3">
                    <dt class="text-xs text-gray-400">Motivo</dt>
                    <dd class="text-gray-700">{{ $creditNote->reason ?? '—' }}</dd>
                </div>
                @if($creditNote->invoice)
                <div class="col-span-2 sm:col-span-3">
                    <dt class="text-xs text-gray-400">Factura relacionada</dt>
                    <dd>
                        <a wire:navigate href="{{ route('sales.invoices.show', $creditNote->invoice) }}"
                           class="font-mono text-indigo-600 hover:underline text-sm">{{ $creditNote->invoice->folio }}</a>
                        <span class="text-xs text-gray-400 ml-2">— Saldo pendiente: ${{ number_format($creditNote->invoice->total - $creditNote->invoice->paid_amount, 2) }}</span>
                    </dd>
                </div>
                @endif
                <div>
                    <dt class="text-xs text-gray-400">Creada por</dt>
                    <dd class="text-gray-700">{{ $creditNote->createdBy->name ?? '—' }}</dd>
                </div>
            </dl>
        </div>

        {{-- Resumen financiero --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm flex flex-col justify-between">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Resumen</h2>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between text-gray-500">
                    <span>Subtotal</span>
                    <span>${{ number_format($creditNote->subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between text-gray-500">
                    <span>IVA</span>
                    <span>${{ number_format($creditNote->tax, 2) }}</span>
                </div>
                <div class="flex justify-between font-bold text-gray-900 border-t border-gray-100 pt-2 mt-2 text-base">
                    <span>Total</span>
                    <span>{{ $creditNote->currency }} ${{ number_format($creditNote->total, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Partidas ────────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Partidas</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[600px]">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="text-left px-5 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide">Descripción</th>
                        <th class="text-right px-5 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide">Cantidad</th>
                        <th class="text-right px-5 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide">Precio unit.</th>
                        <th class="text-right px-5 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide">IVA %</th>
                        <th class="text-right px-5 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($creditNote->items as $item)
                    <tr>
                        <td class="px-5 py-3 text-gray-800">{{ $item->description }}</td>
                        <td class="px-5 py-3 text-right text-gray-600">{{ number_format($item->quantity, 2) }}</td>
                        <td class="px-5 py-3 text-right text-gray-600">${{ number_format($item->unit_price, 2) }}</td>
                        <td class="px-5 py-3 text-right text-gray-500">{{ $item->tax_rate }}%</td>
                        <td class="px-5 py-3 text-right font-medium text-gray-900">${{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-8 text-center text-gray-400 text-sm">Sin partidas registradas.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Modal: Aplicar nota de crédito ─────────────────────────────────── --}}
    @if($showApplyModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40" wire:click="$set('showApplyModal', false)"></div>
        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md p-6">
            <h3 class="text-base font-semibold text-gray-900 mb-3">Aplicar nota de crédito</h3>

            @if($applyError)
            <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-3 mb-4">
                {{ $applyError }}
            </div>
            @endif

            <p class="text-sm text-gray-600 mb-2">
                Se aplicarán
                <span class="font-semibold text-gray-900">{{ $creditNote->currency }} ${{ number_format($creditNote->total, 2) }}</span>
                como crédito en la factura
                <span class="font-mono font-semibold text-indigo-600">{{ $creditNote->invoice->folio ?? '—' }}</span>.
            </p>

            @if($creditNote->invoice)
            @php
                $balance = $creditNote->invoice->total - $creditNote->invoice->paid_amount;
                $applied = min($creditNote->total, $balance);
                $remainder = $creditNote->total - $applied;
            @endphp
            <div class="bg-gray-50 rounded-lg px-4 py-3 text-sm space-y-1 mb-4">
                <div class="flex justify-between text-gray-500">
                    <span>Saldo pendiente en factura</span>
                    <span>${{ number_format($balance, 2) }}</span>
                </div>
                <div class="flex justify-between text-gray-500">
                    <span>Crédito a aplicar</span>
                    <span class="text-emerald-600">- ${{ number_format($applied, 2) }}</span>
                </div>
                @if($remainder > 0)
                <div class="flex justify-between text-amber-600 text-xs">
                    <span>Saldo de nota no aplicado</span>
                    <span>${{ number_format($remainder, 2) }}</span>
                </div>
                @endif
                <div class="flex justify-between font-semibold text-gray-900 border-t border-gray-200 pt-1">
                    <span>Nuevo saldo en factura</span>
                    <span>${{ number_format(max(0, $balance - $applied), 2) }}</span>
                </div>
            </div>
            @endif

            <p class="text-xs text-gray-400 mb-5">Esta acción no puede deshacerse fácilmente.</p>

            <div class="flex justify-end gap-3">
                <button wire:click="$set('showApplyModal', false)"
                    class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                    Cancelar
                </button>
                <button wire:click="apply" wire:loading.attr="disabled" wire:target="apply"
                    class="px-5 py-2 text-sm bg-emerald-600 hover:bg-emerald-700 disabled:opacity-60 text-white rounded-lg transition font-medium">
                    <span wire:loading.remove wire:target="apply">Confirmar aplicación</span>
                    <span wire:loading wire:target="apply">Aplicando…</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ── Modal: Cancelar nota ────────────────────────────────────────────── --}}
    @if($showCancelModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40" wire:click="$set('showCancelModal', false)"></div>
        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-sm p-6">
            <h3 class="text-base font-semibold text-gray-900 mb-3">Cancelar nota de crédito</h3>

            @if($cancelError)
            <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-3 mb-4">
                {{ $cancelError }}
            </div>
            @endif

            <p class="text-sm text-gray-600 mb-5">
                ¿Deseas cancelar la nota <span class="font-mono font-semibold">{{ $creditNote->folio }}</span>?
                Esta acción cambiará su estado a <strong>Cancelada</strong>.
            </p>

            <div class="flex justify-end gap-3">
                <button wire:click="$set('showCancelModal', false)"
                    class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                    No, regresar
                </button>
                <button wire:click="cancel" wire:loading.attr="disabled" wire:target="cancel"
                    class="px-5 py-2 text-sm bg-red-600 hover:bg-red-700 disabled:opacity-60 text-white rounded-lg transition font-medium">
                    <span wire:loading.remove wire:target="cancel">Sí, cancelar</span>
                    <span wire:loading wire:target="cancel">Cancelando…</span>
                </button>
            </div>
        </div>
    </div>
    @endif
</div>

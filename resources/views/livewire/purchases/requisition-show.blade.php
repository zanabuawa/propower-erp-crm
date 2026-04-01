<div class="max-w-4xl space-y-5">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('purchases.index') }}" wire:navigate class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-medium text-gray-900">{{ $requisition->folio }}</h1>
                <p class="text-xs text-gray-400 mt-0.5">
                    Solicitado por {{ $requisition->requestedBy->name }}
                    · {{ $requisition->created_at->format('d/m/Y H:i') }}
                    @if($requisition->branch) · {{ $requisition->branch->name }} @endif
                </p>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2 self-start">
            <span class="px-3 py-1 text-xs font-medium rounded-full {{ \App\Models\PurchaseRequisition::STATUS_COLORS[$requisition->status] ?? 'bg-gray-100 text-gray-600' }}">
                {{ \App\Models\PurchaseRequisition::STATUS[$requisition->status] ?? $requisition->status }}
            </span>
            <a href="{{ route('purchases.requisitions.print', $requisition) }}" target="_blank"
                class="inline-flex items-center gap-1.5 text-xs px-3 py-1.5 border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-600 transition"
                title="Imprimir / Guardar PDF">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Imprimir
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-xl px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    {{-- Timeline de estados --}}
    @php
        $steps = [
            ['key' => 'submitted',           'label' => 'Enviada',        'order' => 0],
            ['key' => 'preliminary_quoted',  'label' => 'Cot. preliminar','order' => 1],
            ['key' => 'requester_confirmed', 'label' => 'Confirmada',     'order' => 2],
            ['key' => 'pending_auth',        'label' => 'Autorización',   'order' => 3],
            ['key' => 'ordered',             'label' => 'OC generada',    'order' => 4],
        ];
        $statusOrder = [
            'submitted' => 0, 'preliminary_quoted' => 1, 'requester_returned' => 1,
            'requester_confirmed' => 2, 'final_quoted' => 3, 'pending_auth' => 3,
            'authorized' => 3, 'ordered' => 4,
        ];
        $currentOrder = $statusOrder[$requisition->status] ?? -1;
        $isRejected   = in_array($requisition->status, ['rejected', 'cancelled']);
    @endphp

    @if(!$isRejected)
    <div class="bg-white rounded-xl border border-gray-200 px-5 py-4">
        <div class="flex items-center">
            @foreach($steps as $i => $step)
            <div class="flex items-center {{ $i < count($steps) - 1 ? 'flex-1' : '' }}">
                <div class="flex flex-col items-center">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-medium
                        {{ $currentOrder >= $step['order'] ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-400' }}">
                        @if($currentOrder > $step['order'])
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                        @else
                            {{ $i + 1 }}
                        @endif
                    </div>
                    <span class="text-[10px] text-gray-500 mt-1 whitespace-nowrap">{{ $step['label'] }}</span>
                </div>
                @if($i < count($steps) - 1)
                <div class="flex-1 h-0.5 mx-1 mb-4 {{ $currentOrder > $step['order'] ? 'bg-indigo-400' : 'bg-gray-200' }}"></div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Rechazada --}}
    @if($isRejected)
    <div class="bg-red-50 border border-red-200 rounded-xl p-4">
        <p class="text-sm font-medium text-red-700">Requisición rechazada</p>
        @if($requisition->reject_reason)
            <p class="text-xs text-red-600 mt-1">Motivo: {{ $requisition->reject_reason }}</p>
        @endif
        @if($requisition->rejectedBy)
            <p class="text-xs text-red-500 mt-0.5">
                Por: {{ $requisition->rejectedBy->name }}
                · {{ $requisition->rejected_at?->format('d/m/Y H:i') }}
            </p>
        @endif
    </div>
    @endif

    {{-- Justificación --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-xs text-gray-400 mb-1">Justificación</p>
        <p class="text-sm text-gray-700">{{ $requisition->justification }}</p>
        <div class="flex items-center gap-4 mt-2 text-xs text-gray-400">
            <span>Moneda: {{ $requisition->currency }}</span>
            @if($requisition->needed_by)
                <span>Requerido para: {{ $requisition->needed_by->format('d/m/Y') }}</span>
            @endif
        </div>
    </div>

    {{-- Ítems de la requisición --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100">
            <h2 class="text-sm font-medium text-gray-700">Ítems solicitados</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[480px]">
                <thead class="bg-gray-50 text-xs text-gray-500">
                    <tr>
                        <th class="px-4 py-2 text-left">Descripción</th>
                        <th class="px-4 py-2 text-center">Cant.</th>
                        <th class="px-4 py-2 text-left hidden sm:table-cell">Unidad</th>
                        <th class="px-4 py-2 text-right hidden sm:table-cell">Precio ref.</th>
                        <th class="px-4 py-2 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($requisition->items as $item)
                    <tr>
                        <td class="px-4 py-2 text-gray-800">
                            {{ $item->description }}
                            <p class="text-xs text-gray-400 sm:hidden mt-0.5">{{ $item->unit }} · ${{ number_format($item->unit_price, 2) }}</p>
                        </td>
                        <td class="px-4 py-2 text-center text-gray-600">{{ $item->quantity }}</td>
                        <td class="px-4 py-2 text-gray-500 text-xs hidden sm:table-cell">{{ $item->unit }}</td>
                        <td class="px-4 py-2 text-right text-gray-600 hidden sm:table-cell">${{ number_format($item->unit_price, 2) }}</td>
                        <td class="px-4 py-2 text-right font-medium">${{ number_format($item->quantity * $item->unit_price, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50 text-xs">
                    <tr>
                        <td colspan="4" class="px-4 py-2 text-right text-gray-500 font-medium hidden sm:table-cell">Total estimado</td>
                        <td colspan="2" class="px-4 py-2 text-right text-gray-500 font-medium sm:hidden">Total estimado</td>
                        <td class="px-4 py-2 text-right font-semibold text-gray-800">${{ number_format($requisition->total, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════
         ACCIONES CONDICIONALES POR STATUS + ROL
    ═══════════════════════════════════════════════════ --}}

    {{-- ── COMPRAS: nueva o devuelta → cotizar o rechazar ── --}}
    @if(in_array($requisition->status, ['submitted', 'requester_returned']) && $this->isComprador)

        {{-- Notas del solicitante al devolver --}}
        @if($requisition->status === 'requester_returned' && $requisition->preliminaryQuotation?->requester_notes)
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
            <p class="text-xs font-semibold text-amber-700 mb-1">Comentarios del solicitante:</p>
            <p class="text-sm text-amber-800">{{ $requisition->preliminaryQuotation->requester_notes }}</p>
        </div>
        @endif

        @if(!$showQuotationForm && !$showRejectForm)
        <div class="flex flex-wrap items-center gap-3">
            <button wire:click="openQuotationForm('preliminary')"
                class="px-4 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition">
                {{ $requisition->status === 'requester_returned' ? 'Actualizar cotización preliminar' : 'Crear cotización preliminar' }}
            </button>
            <button wire:click="$set('showRejectForm', true)"
                class="px-4 py-2 text-sm border border-red-200 text-red-600 hover:bg-red-50 rounded-lg transition">
                Rechazar requisición
            </button>
        </div>
        @endif

        @if($showRejectForm)
        <div class="bg-white rounded-xl border border-red-200 p-5 space-y-3">
            <h3 class="text-sm font-medium text-red-700">Rechazar requisición</h3>
            <textarea wire:model="rejectReason" rows="3"
                placeholder="Motivo del rechazo (requerido)..."
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300"></textarea>
            @error('rejectReason') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
            <div class="flex gap-2">
                <button wire:click="rejectRequisition"
                    class="px-4 py-2 text-sm bg-red-600 hover:bg-red-700 text-white rounded-lg transition">
                    Confirmar rechazo
                </button>
                <button wire:click="$set('showRejectForm', false)"
                    class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                    Cancelar
                </button>
            </div>
        </div>
        @endif
    @endif

    {{-- ── Formulario de cotización (compras, cualquier tipo) ── --}}
    @if($showQuotationForm)
    <div class="bg-white rounded-xl border border-indigo-200 p-5 space-y-4">
        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
            <h2 class="text-sm font-medium text-gray-800">
                {{ $quotationType === 'preliminary' ? 'Cotización preliminar' : 'Cotización final' }}
            </h2>
            <button wire:click="$set('showQuotationForm', false)" class="text-gray-400 hover:text-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[540px]">
                <thead class="bg-gray-50 text-xs text-gray-500">
                    <tr>
                        <th class="px-3 py-2 text-left">Descripción *</th>
                        <th class="px-3 py-2 text-center w-20">Cant. *</th>
                        <th class="px-3 py-2 text-center w-20">Unidad</th>
                        <th class="px-3 py-2 text-right w-28">Precio unit. *</th>
                        <th class="px-3 py-2 text-center w-20">IVA %</th>
                        <th class="px-3 py-2 text-right w-28">Subtotal</th>
                        <th class="w-8"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($qItems as $idx => $qi)
                    <tr>
                        <td class="px-2 py-1">
                            <input wire:model.live="qItems.{{ $idx }}.description" type="text"
                                class="w-full border border-gray-200 rounded px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-indigo-300">
                            @error("qItems.{$idx}.description") <p class="text-[10px] text-red-500">{{ $message }}</p> @enderror
                        </td>
                        <td class="px-2 py-1">
                            <input wire:model.live="qItems.{{ $idx }}.quantity" type="number" min="0.01" step="0.01"
                                class="w-full border border-gray-200 rounded px-2 py-1 text-xs text-center focus:outline-none focus:ring-1 focus:ring-indigo-300">
                        </td>
                        <td class="px-2 py-1">
                            <input wire:model="qItems.{{ $idx }}.unit" type="text"
                                class="w-full border border-gray-200 rounded px-2 py-1 text-xs text-center focus:outline-none focus:ring-1 focus:ring-indigo-300">
                        </td>
                        <td class="px-2 py-1">
                            <input wire:model.live="qItems.{{ $idx }}.unit_price" type="number" min="0" step="0.01"
                                class="w-full border border-gray-200 rounded px-2 py-1 text-xs text-right focus:outline-none focus:ring-1 focus:ring-indigo-300">
                        </td>
                        <td class="px-2 py-1">
                            <input wire:model.live="qItems.{{ $idx }}.tax_rate" type="number" min="0" max="100" step="0.01"
                                class="w-full border border-gray-200 rounded px-2 py-1 text-xs text-center focus:outline-none focus:ring-1 focus:ring-indigo-300">
                        </td>
                        <td class="px-2 py-1 text-right text-xs text-gray-600">
                            ${{ number_format(($qi['quantity'] ?? 0) * ($qi['unit_price'] ?? 0), 2) }}
                        </td>
                        <td class="px-1">
                            <button wire:click="removeQItem({{ $idx }})" type="button" class="text-red-400 hover:text-red-600">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @php
            $qSubtotal = collect($qItems)->sum(fn($i) => ($i['quantity'] ?? 0) * ($i['unit_price'] ?? 0));
            $qTax      = collect($qItems)->sum(fn($i) => ($i['quantity'] ?? 0) * ($i['unit_price'] ?? 0) * (($i['tax_rate'] ?? 0) / 100));
            $qTotal    = $qSubtotal + $qTax;
        @endphp

        <div class="flex items-start gap-4">
            <button wire:click="addQItem" type="button"
                class="text-xs text-indigo-600 hover:text-indigo-800 flex items-center gap-1 mt-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Agregar línea
            </button>
            <div class="ml-auto text-right text-xs space-y-0.5 text-gray-600">
                <div>Subtotal: <span class="font-medium">${{ number_format($qSubtotal, 2) }}</span></div>
                <div>IVA: <span class="font-medium">${{ number_format($qTax, 2) }}</span></div>
                <div class="text-base font-semibold text-gray-900">Total: ${{ number_format($qTotal, 2) }}</div>
                @if($quotationType === 'final')
                    @if($qTotal >= 10000)
                        <div class="text-orange-600 font-medium">Requiere: Compras + Admin + Gerencia</div>
                    @elseif($qTotal >= 2000)
                        <div class="text-amber-600 font-medium">Requiere: Compras + Admin</div>
                    @else
                        <div class="text-green-600 font-medium">Requiere: Solo Compras</div>
                    @endif
                @endif
            </div>
        </div>

        <div>
            <label class="block text-xs text-gray-500 mb-1">Notas para el solicitante (opcional)</label>
            <textarea wire:model="qNotes" rows="2"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"></textarea>
        </div>

        <div class="flex gap-3 pt-2 border-t border-gray-100">
            <button wire:click="saveQuotation" type="button"
                class="px-5 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition">
                {{ $quotationType === 'preliminary' ? 'Enviar cotización al solicitante' : 'Enviar cotización final' }}
            </button>
            <button wire:click="$set('showQuotationForm', false)" type="button"
                class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                Cancelar
            </button>
        </div>
    </div>
    @endif

    {{-- ── SOLICITANTE: revisar cotización preliminar ── --}}
    @if($requisition->status === 'preliminary_quoted' && $this->isRequester)
    <div class="bg-white rounded-xl border border-blue-200 overflow-hidden">
        <div class="px-5 py-3 bg-blue-50 border-b border-blue-100 flex items-center gap-2">
            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h2 class="text-sm font-medium text-blue-800">Cotización preliminar recibida de compras</h2>
        </div>

        @if($requisition->preliminaryQuotation)
            @if($requisition->preliminaryQuotation->notes)
            <div class="px-5 pt-4">
                <p class="text-xs text-gray-400 mb-1">Nota de compras:</p>
                <p class="text-sm text-gray-700 bg-gray-50 rounded-lg px-3 py-2">{{ $requisition->preliminaryQuotation->notes }}</p>
            </div>
            @endif

            <div class="overflow-x-auto mt-3">
                <table class="w-full text-sm min-w-[480px]">
                    <thead class="bg-gray-50 text-xs text-gray-500">
                        <tr>
                            <th class="px-4 py-2 text-left">Descripción</th>
                            <th class="px-4 py-2 text-center">Cant.</th>
                            <th class="px-4 py-2 text-left hidden sm:table-cell">Unidad</th>
                            <th class="px-4 py-2 text-right">Precio unit.</th>
                            <th class="px-4 py-2 text-center hidden sm:table-cell">IVA</th>
                            <th class="px-4 py-2 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($requisition->preliminaryQuotation->items as $qi)
                        <tr>
                            <td class="px-4 py-2">{{ $qi->description }}</td>
                            <td class="px-4 py-2 text-center">{{ $qi->quantity }}</td>
                            <td class="px-4 py-2 text-xs text-gray-500 hidden sm:table-cell">{{ $qi->unit }}</td>
                            <td class="px-4 py-2 text-right">${{ number_format($qi->unit_price, 2) }}</td>
                            <td class="px-4 py-2 text-center text-xs text-gray-500 hidden sm:table-cell">{{ $qi->tax_rate }}%</td>
                            <td class="px-4 py-2 text-right font-medium">${{ number_format($qi->subtotal, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 text-xs font-medium">
                        <tr>
                            <td colspan="5" class="px-4 py-2 text-right text-gray-500 hidden sm:table-cell">Subtotal</td>
                            <td colspan="2" class="px-4 py-2 text-right text-gray-500 sm:hidden">Subtotal</td>
                            <td class="px-4 py-2 text-right">${{ number_format($requisition->preliminaryQuotation->subtotal, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="5" class="px-4 py-2 text-right text-gray-500 hidden sm:table-cell">IVA</td>
                            <td colspan="2" class="px-4 py-2 text-right text-gray-500 sm:hidden">IVA</td>
                            <td class="px-4 py-2 text-right">${{ number_format($requisition->preliminaryQuotation->tax, 2) }}</td>
                        </tr>
                        <tr class="text-sm">
                            <td colspan="5" class="px-4 py-2 text-right font-semibold text-gray-800 hidden sm:table-cell">Total</td>
                            <td colspan="2" class="px-4 py-2 text-right font-semibold text-gray-800 sm:hidden">Total</td>
                            <td class="px-4 py-2 text-right font-bold text-gray-900">${{ number_format($requisition->preliminaryQuotation->total, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif

        <div class="px-5 py-4 border-t border-gray-100 space-y-3">
            <p class="text-xs text-gray-500">¿Estás de acuerdo con esta cotización preliminar?</p>
            <div class="flex items-start gap-3 flex-wrap">
                <button wire:click="confirmQuotation" type="button"
                    class="px-4 py-2 text-sm bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition">
                    Confirmar y continuar
                </button>
                <div class="flex-1 min-w-[240px] space-y-2">
                    <textarea wire:model="requesterNotes" rows="2"
                        placeholder="Escribe tus comentarios para devolver la cotización..."
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300"></textarea>
                    @error('requesterNotes') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                    <button wire:click="returnQuotation" type="button"
                        class="px-4 py-2 text-sm border border-orange-300 text-orange-700 hover:bg-orange-50 rounded-lg transition">
                        Devolver con comentarios
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ── COMPRAS: solicitante confirmó → crear cotización final ── --}}
    @if($requisition->status === 'requester_confirmed' && $this->isComprador && !$showQuotationForm)
    <div class="bg-cyan-50 border border-cyan-200 rounded-xl p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <p class="text-sm font-medium text-cyan-800">El solicitante confirmó la cotización preliminar</p>
            <p class="text-xs text-cyan-600 mt-0.5">Ahora puedes crear la cotización final con precios y proveedor definitivos.</p>
        </div>
        <button wire:click="openQuotationForm('final')" type="button"
            class="px-4 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition whitespace-nowrap self-start sm:self-auto">
            Crear cotización final
        </button>
    </div>
    @endif

    {{-- ── Cotización final y proceso de autorización ── --}}
    @if($requisition->finalQuotation && in_array($requisition->status, ['pending_auth', 'authorized', 'ordered']))
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-3 bg-purple-50 border-b border-purple-100 flex items-center justify-between">
            <h2 class="text-sm font-medium text-purple-800">Cotización final</h2>
            <span class="text-xs text-purple-600 font-semibold">
                ${{ number_format($requisition->finalQuotation->total, 2) }} {{ $requisition->currency }}
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[420px]">
                <thead class="bg-gray-50 text-xs text-gray-500">
                    <tr>
                        <th class="px-4 py-2 text-left">Descripción</th>
                        <th class="px-4 py-2 text-center">Cant.</th>
                        <th class="px-4 py-2 text-right hidden sm:table-cell">Precio unit.</th>
                        <th class="px-4 py-2 text-center hidden sm:table-cell">IVA</th>
                        <th class="px-4 py-2 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($requisition->finalQuotation->items as $qi)
                    <tr>
                        <td class="px-4 py-2">
                            {{ $qi->description }}
                            <p class="text-xs text-gray-400 sm:hidden mt-0.5">${{ number_format($qi->unit_price, 2) }} · IVA {{ $qi->tax_rate }}%</p>
                        </td>
                        <td class="px-4 py-2 text-center">{{ $qi->quantity }} {{ $qi->unit }}</td>
                        <td class="px-4 py-2 text-right hidden sm:table-cell">${{ number_format($qi->unit_price, 2) }}</td>
                        <td class="px-4 py-2 text-center text-xs text-gray-500 hidden sm:table-cell">{{ $qi->tax_rate }}%</td>
                        <td class="px-4 py-2 text-right font-medium">${{ number_format($qi->subtotal, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50 text-xs font-medium">
                    <tr>
                        <td colspan="4" class="px-4 py-2 text-right text-gray-500 hidden sm:table-cell">Subtotal</td>
                        <td colspan="2" class="px-4 py-2 text-right text-gray-500 sm:hidden">Subtotal</td>
                        <td class="px-4 py-2 text-right">${{ number_format($requisition->finalQuotation->subtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="4" class="px-4 py-2 text-right text-gray-500 hidden sm:table-cell">IVA</td>
                        <td colspan="2" class="px-4 py-2 text-right text-gray-500 sm:hidden">IVA</td>
                        <td class="px-4 py-2 text-right">${{ number_format($requisition->finalQuotation->tax, 2) }}</td>
                    </tr>
                    <tr class="text-sm">
                        <td colspan="4" class="px-4 py-2 text-right font-semibold text-gray-800 hidden sm:table-cell">Total</td>
                        <td colspan="2" class="px-4 py-2 text-right font-semibold text-gray-800 sm:hidden">Total</td>
                        <td class="px-4 py-2 text-right font-bold text-gray-900">${{ number_format($requisition->finalQuotation->total, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Panel de autorizaciones --}}
        @if($requisition->finalQuotation->approvals->count() > 0)
        <div class="px-5 py-4 border-t border-gray-100">
            <p class="text-xs font-semibold text-gray-600 mb-3 uppercase tracking-wide">Autorizaciones requeridas</p>
            <div class="space-y-2">
                @foreach($requisition->finalQuotation->approvals->sortBy('level') as $approval)
                <div class="flex items-center justify-between py-2 px-3 rounded-lg
                    {{ $approval->status === 'approved' ? 'bg-green-50 border border-green-100' : ($approval->status === 'rejected' ? 'bg-red-50 border border-red-100' : 'bg-gray-50 border border-gray-100') }}">
                    <div class="flex items-center gap-3">
                        <div class="w-6 h-6 rounded-full bg-white border border-gray-200 flex items-center justify-center text-xs font-bold text-gray-500">
                            {{ $approval->level }}
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-800">
                                {{ $approval->user?->name ?? 'Pendiente de ' . ucfirst($approval->role) }}
                            </p>
                            <p class="text-[10px] text-gray-400">Rol: {{ ucfirst($approval->role) }}
                                @if($approval->decided_at) · {{ $approval->decided_at->format('d/m/Y H:i') }} @endif
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($approval->comments)
                            <span class="text-[10px] text-gray-500 italic max-w-[200px] truncate">{{ $approval->comments }}</span>
                        @endif
                        <span class="px-2 py-0.5 text-[10px] rounded-full font-medium
                            {{ $approval->status === 'approved' ? 'bg-green-100 text-green-700' : ($approval->status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">
                            {{ ['pending' => 'Pendiente', 'approved' => 'Autorizado', 'rejected' => 'Rechazado'][$approval->status] }}
                        </span>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Botones de autorización: cualquier usuario con el rol pendiente puede actuar --}}
            @if($this->canApprove)
            <div class="mt-4 pt-3 border-t border-gray-100 space-y-3">
                <p class="text-xs font-medium text-gray-700">Tu rol tiene autorización pendiente en esta cotización:</p>
                <textarea wire:model="approvalComment" rows="2"
                    placeholder="Comentario (opcional)..."
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"></textarea>

                {{-- Firma digital --}}
                <div
                    x-data="{
                        drawing: false,
                        lastX: 0, lastY: 0,
                        init() {
                            const canvas = this.$refs.sigCanvas;
                            const ctx = canvas.getContext('2d');
                            ctx.strokeStyle = '#1e293b';
                            ctx.lineWidth   = 2;
                            ctx.lineCap     = 'round';
                            ctx.lineJoin    = 'round';

                            const pos = (e) => {
                                const r = canvas.getBoundingClientRect();
                                const src = e.touches ? e.touches[0] : e;
                                return { x: src.clientX - r.left, y: src.clientY - r.top };
                            };

                            const start = (e) => {
                                e.preventDefault();
                                this.drawing = true;
                                const p = pos(e);
                                this.lastX = p.x; this.lastY = p.y;
                                ctx.beginPath();
                                ctx.moveTo(p.x, p.y);
                            };
                            const move = (e) => {
                                if (!this.drawing) return;
                                e.preventDefault();
                                const p = pos(e);
                                ctx.lineTo(p.x, p.y);
                                ctx.stroke();
                                this.lastX = p.x; this.lastY = p.y;
                                $wire.set('signatureData', canvas.toDataURL('image/png'));
                            };
                            const stop = () => { this.drawing = false; };

                            canvas.addEventListener('mousedown',  start);
                            canvas.addEventListener('mousemove',  move);
                            canvas.addEventListener('mouseup',    stop);
                            canvas.addEventListener('mouseleave', stop);
                            canvas.addEventListener('touchstart', start, { passive: false });
                            canvas.addEventListener('touchmove',  move,  { passive: false });
                            canvas.addEventListener('touchend',   stop);
                        },
                        clear() {
                            const canvas = this.$refs.sigCanvas;
                            canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
                            $wire.set('signatureData', '');
                        }
                    }"
                    class="space-y-1"
                >
                    <div class="flex items-center justify-between">
                        <label class="text-xs text-gray-500 font-medium">Firma digital <span class="text-red-500">*</span></label>
                        <button type="button" @click="clear()"
                            class="text-[10px] text-gray-400 hover:text-red-500 underline transition">
                            Limpiar firma
                        </button>
                    </div>
                    <div class="border border-gray-300 rounded-lg overflow-hidden bg-white cursor-crosshair select-none"
                         style="touch-action: none;">
                        <canvas x-ref="sigCanvas" width="560" height="120"
                            class="w-full block"
                            style="touch-action: none;"></canvas>
                    </div>
                    <p class="text-[10px] text-gray-400">Dibuja tu firma con el ratón o dedo. Es obligatoria para autorizar.</p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <button wire:click="approveQuotation" type="button"
                        class="px-4 py-2 text-sm bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition">
                        Autorizar
                    </button>
                    <button wire:click="rejectQuotation" type="button"
                        class="px-4 py-2 text-sm border border-red-200 text-red-600 hover:bg-red-50 rounded-lg font-medium transition">
                        Rechazar
                    </button>
                </div>
            </div>
            @endif
        </div>
        @endif
    </div>
    @endif

    {{-- ── Orden de compra generada ── --}}
    @if($requisition->status === 'ordered' && $requisition->order)
    <div class="bg-green-50 border border-green-200 rounded-xl p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <p class="text-sm font-medium text-green-800">Orden de compra generada</p>
            <p class="text-xs text-green-600 mt-0.5">Folio: {{ $requisition->order->folio }}</p>
        </div>
        <a href="{{ route('purchases.orders.show', $requisition->order) }}" wire:navigate
            class="px-4 py-2 text-sm bg-green-700 hover:bg-green-800 text-white rounded-lg transition whitespace-nowrap self-start sm:self-auto">
            Ver orden de compra
        </a>
    </div>
    @endif

    {{-- ── Mensajes de estado para el solicitante ── --}}
    @if($this->isRequester && !$this->isComprador)
        @if($requisition->status === 'submitted')
        <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 text-sm text-amber-700">
            Tu requisición fue enviada. El equipo de compras la está revisando.
        </div>
        @elseif($requisition->status === 'requester_returned')
        <div class="bg-orange-50 border border-orange-200 rounded-xl px-4 py-3 text-sm text-orange-700">
            Devolviste la cotización con comentarios. Compras está revisando tus notas.
        </div>
        @elseif($requisition->status === 'requester_confirmed')
        <div class="bg-cyan-50 border border-cyan-200 rounded-xl px-4 py-3 text-sm text-cyan-700">
            Confirmaste la cotización preliminar. Compras está preparando la cotización final.
        </div>
        @elseif($requisition->status === 'pending_auth')
        <div class="bg-purple-50 border border-purple-200 rounded-xl px-4 py-3 text-sm text-purple-700">
            Tu requisición está en proceso de autorización.
        </div>
        @elseif($requisition->status === 'authorized')
        <div class="bg-green-50 border border-green-200 rounded-xl px-4 py-3 text-sm text-green-700">
            ¡Tu requisición fue aprobada en evaluación! El área de compras generará la orden de compra a la brevedad.
        </div>
        @endif
    @endif

</div>

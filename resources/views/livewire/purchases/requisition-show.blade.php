<div class="max-w-4xl mx-auto space-y-5">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
        <div class="flex items-center gap-3">
            <a wire:navigate href="{{ route('purchases.index') }}" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-medium text-gray-900">{{ $requisition->folio }}</h1>
                <p class="text-xs text-gray-500 mt-0.5">
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
            @if($requisition->priority)
                <span class="px-2.5 py-1 text-xs font-bold rounded-full {{ \App\Models\PurchaseRequisition::PRIORITY_COLORS[$requisition->priority] ?? 'bg-gray-100 text-gray-500' }}">
                    {{ \App\Models\PurchaseRequisition::PRIORITY[$requisition->priority] ?? $requisition->priority }}
                </span>
            @endif
            @if($requisition->requisition_type)
                <span class="px-2.5 py-1 text-xs font-medium rounded-full {{ \App\Models\PurchaseRequisition::REQUISITION_TYPE_COLORS[$requisition->requisition_type] ?? 'bg-gray-100 text-gray-500' }}">
                    {{ \App\Models\PurchaseRequisition::REQUISITION_TYPES[$requisition->requisition_type] ?? $requisition->requisition_type }}
                </span>
            @endif
            <a href="{{ route('purchases.requisitions.print', $requisition) }}" target="_blank"
                class="inline-flex items-center gap-1.5 text-xs px-3 py-1.5 border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-600 transition shadow-sm"
                title="Imprimir / Guardar PDF">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Imprimir
            </a>
        </div>
    </div>

    <x-alert />

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
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm px-5 py-4">
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

    {{-- Justificación + clasificación --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 space-y-3">
        <p class="text-xs text-gray-400 mb-1">Justificación</p>
        <p class="text-sm text-gray-700">{{ $requisition->justification }}</p>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-2 border-t border-gray-100">
            <div>
                <p class="text-[10px] text-gray-400 uppercase tracking-wide">Moneda</p>
                <p class="text-xs font-medium text-gray-700 mt-0.5">{{ $requisition->currency }}</p>
            </div>
            @if($requisition->needed_by)
            <div>
                <p class="text-[10px] text-gray-400 uppercase tracking-wide">Requerido para</p>
                <p class="text-xs font-medium {{ $requisition->needed_by->isPast() && !in_array($requisition->status, ['ordered','cancelled']) ? 'text-red-600' : 'text-gray-700' }} mt-0.5">
                    {{ $requisition->needed_by->format('d/m/Y') }}
                </p>
            </div>
            @endif
            @if($requisition->expense_type)
            <div>
                <p class="text-[10px] text-gray-400 uppercase tracking-wide">Tipo de gasto</p>
                <p class="text-xs font-medium text-gray-700 mt-0.5">
                    {{ \App\Models\PurchaseRequisition::EXPENSE_TYPES[$requisition->expense_type] ?? $requisition->expense_type }}
                </p>
            </div>
            @endif
            @if($requisition->project_name)
            <div>
                <p class="text-[10px] text-gray-400 uppercase tracking-wide">Proyecto</p>
                <p class="text-xs font-medium text-indigo-600 mt-0.5">{{ $requisition->project_name }}</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Ítems de la requisición --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100">
            <h2 class="text-sm font-medium text-gray-700">Ítems solicitados</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[480px]">
                <thead class="bg-gray-50 text-xs text-gray-500">
                    <tr>
                        <th class="px-4 py-2 text-left w-24 hidden sm:table-cell">Tipo</th>
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
                        <td class="px-4 py-2 hidden sm:table-cell">
                            <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-medium
                                {{ \App\Models\PurchaseRequisitionItem::ITEM_TYPE_COLORS[$item->item_type ?? 'product'] ?? 'bg-gray-100 text-gray-500' }}">
                                {{ \App\Models\PurchaseRequisitionItem::ITEM_TYPES[$item->item_type ?? 'product'] ?? 'Producto' }}
                            </span>
                        </td>
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

        {{-- Nota IVA + controles bulk --}}
        <div class="flex flex-col sm:flex-row sm:items-center gap-3 bg-amber-50 border border-amber-200 rounded-lg px-4 py-2.5 mb-3">
            <div class="flex items-start gap-2 flex-1 min-w-0">
                <svg class="w-4 h-4 text-amber-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-xs text-amber-700">
                    <strong>IVA:</strong> por defecto se asume que el precio ya incluye IVA. Marca la casilla en cada partida cuyo precio <em>no</em> incluye IVA para que se calcule el 16% adicional.
                </p>
            </div>
            <div class="flex gap-2 shrink-0">
                <button type="button" wire:click="setAllQIva(true)"
                    class="px-2.5 py-1 text-xs border border-amber-300 text-amber-700 hover:bg-amber-100 rounded-lg transition whitespace-nowrap">
                    Marcar todos
                </button>
                <button type="button" wire:click="setAllQIva(false)"
                    class="px-2.5 py-1 text-xs border border-amber-300 text-amber-700 hover:bg-amber-100 rounded-lg transition whitespace-nowrap">
                    Quitar todos
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[540px]">
                <thead class="bg-gray-50 text-xs text-gray-500">
                    <tr>
                        <th class="px-3 py-2 text-left">Descripción *</th>
                        <th class="px-3 py-2 text-center w-20">Cant. *</th>
                        <th class="px-3 py-2 text-center w-20">Unidad</th>
                        <th class="px-3 py-2 text-right w-28">Precio unit. *</th>
                        <th class="px-3 py-2 text-center w-28">IVA</th>
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
                            @php $ivaNoInc = ($qi['tax_rate'] ?? 0) != 0; @endphp
                            <label class="flex items-center gap-1.5 cursor-pointer justify-center select-none">
                                <input type="checkbox"
                                    wire:click="toggleQItemIva({{ $idx }})"
                                    {{ $ivaNoInc ? 'checked' : '' }}
                                    class="w-3.5 h-3.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                                <span class="text-xs {{ $ivaNoInc ? 'text-gray-700' : 'text-indigo-600 font-medium' }}">
                                    {{ $ivaNoInc ? '+ 16%' : 'Incluido' }}
                                </span>
                            </label>
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
                        <div class="text-emerald-600 font-medium">Requiere: Solo Compras</div>
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
                            <td class="px-4 py-2 text-center text-xs hidden sm:table-cell">
                                @if($qi->tax_rate == 0)
                                    <span class="text-indigo-600 font-medium">Incluido</span>
                                @else
                                    <span class="text-gray-500">+ {{ $qi->tax_rate }}%</span>
                                @endif
                            </td>
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
                    class="px-4 py-2 text-sm bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-medium transition">
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
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
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
                            <p class="text-xs text-gray-400 sm:hidden mt-0.5">
                                ${{ number_format($qi->unit_price, 2) }} · IVA {{ $qi->tax_rate == 0 ? 'incluido' : $qi->tax_rate . '%' }}
                            </p>
                        </td>
                        <td class="px-4 py-2 text-center">{{ $qi->quantity }} {{ $qi->unit }}</td>
                        <td class="px-4 py-2 text-right hidden sm:table-cell">${{ number_format($qi->unit_price, 2) }}</td>
                        <td class="px-4 py-2 text-center text-xs hidden sm:table-cell">
                            @if($qi->tax_rate == 0)
                                <span class="text-indigo-600 font-medium">Incluido</span>
                            @else
                                <span class="text-gray-500">+ {{ $qi->tax_rate }}%</span>
                            @endif
                        </td>
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
                    {{ $approval->status === 'approved' ? 'bg-emerald-50 border border-emerald-100' : ($approval->status === 'rejected' ? 'bg-red-50 border border-red-100' : 'bg-gray-50 border border-gray-100') }}">
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
                            {{ $approval->status === 'approved' ? 'bg-emerald-100 text-emerald-700' : ($approval->status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">
                            {{ ['pending' => 'Pendiente', 'approved' => 'Autorizado', 'rejected' => 'Rechazado'][$approval->status] }}
                        </span>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Botones de autorización: cualquier usuario con el rol pendiente puede actuar --}}
            @if($this->canApprove)
            <div
                x-data="{
                    showModal: false,
                    step: 1,
                    sigMethod: '{{ auth()->user()->signature ? 'saved' : 'draw' }}',
                    hasSavedSig: {{ auth()->user()->signature ? 'true' : 'false' }},
                    drawing: false,

                    openModal() {
                        this.step = 1;
                        this.sigMethod = this.hasSavedSig ? 'saved' : 'draw';
                        this.showModal = true;
                    },

                    goToStep2() {
                        this.step = 2;
                    },

                    goToStep3() {
                        this.step = 3;
                        if (this.sigMethod === 'draw') {
                            this.$nextTick(() => this.initCanvas());
                        }
                    },

                    switchToMethod(method) {
                        this.sigMethod = method;
                        if (method === 'draw') {
                            this.$nextTick(() => this.initCanvas());
                        } else {
                            $wire.set('signatureData', '');
                        }
                    },

                    initCanvas() {
                        const canvas = this.$refs.sigCanvas;
                        if (!canvas) return;
                        // reset para permitir re-inicializar al cambiar método
                        canvas._initialized = false;
                        if (canvas._initialized) return;
                        canvas._initialized = true;
                        const ctx = canvas.getContext('2d');
                        ctx.clearRect(0, 0, canvas.width, canvas.height);
                        ctx.strokeStyle = '#1e293b';
                        ctx.lineWidth   = 2.5;
                        ctx.lineCap     = 'round';
                        ctx.lineJoin    = 'round';

                        let points = [];
                        const SMOOTH = 0.35;

                        const pos = (e) => {
                            const r   = canvas.getBoundingClientRect();
                            const src = e.touches ? e.touches[0] : e;
                            const scaleX = canvas.width  / r.width;
                            const scaleY = canvas.height / r.height;
                            return {
                                x: (src.clientX - r.left) * scaleX,
                                y: (src.clientY - r.top)  * scaleY
                            };
                        };

                        const lerp = (a, b, t) => a + (b - a) * t;

                        const start = (e) => {
                            e.preventDefault();
                            this.drawing = true;
                            points = [];
                            const p = pos(e);
                            points.push(p);
                            ctx.beginPath();
                            ctx.moveTo(p.x, p.y);
                        };

                        const move = (e) => {
                            if (!this.drawing) return;
                            e.preventDefault();
                            const p = pos(e);
                            const prev = points[points.length - 1];
                            const smoothed = {
                                x: lerp(prev.x, p.x, 1 - SMOOTH),
                                y: lerp(prev.y, p.y, 1 - SMOOTH)
                            };
                            points.push(smoothed);
                            if (points.length >= 3) {
                                const p0 = points[points.length - 3];
                                const p1 = points[points.length - 2];
                                const p2 = points[points.length - 1];
                                const mid01 = { x: (p0.x + p1.x) / 2, y: (p0.y + p1.y) / 2 };
                                const mid12 = { x: (p1.x + p2.x) / 2, y: (p1.y + p2.y) / 2 };
                                ctx.beginPath();
                                ctx.moveTo(mid01.x, mid01.y);
                                ctx.quadraticCurveTo(p1.x, p1.y, mid12.x, mid12.y);
                                ctx.stroke();
                            } else {
                                ctx.beginPath();
                                ctx.moveTo(prev.x, prev.y);
                                ctx.lineTo(smoothed.x, smoothed.y);
                                ctx.stroke();
                            }
                            $wire.set('signatureData', canvas.toDataURL('image/png'));
                        };

                        const stop = () => {
                            if (this.drawing && points.length >= 2) {
                                const last = points[points.length - 1];
                                ctx.lineTo(last.x, last.y);
                                ctx.stroke();
                                $wire.set('signatureData', canvas.toDataURL('image/png'));
                            }
                            this.drawing = false;
                            points = [];
                        };

                        canvas.addEventListener('mousedown',  start);
                        canvas.addEventListener('mousemove',  move);
                        canvas.addEventListener('mouseup',    stop);
                        canvas.addEventListener('mouseleave', stop);
                        canvas.addEventListener('touchstart', start, { passive: false });
                        canvas.addEventListener('touchmove',  move,  { passive: false });
                        canvas.addEventListener('touchend',   stop);
                    },

                    clearCanvas() {
                        const canvas = this.$refs.sigCanvas;
                        if (canvas) canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
                        $wire.set('signatureData', '');
                    },

                    closeModal() {
                        this.showModal = false;
                        this.step = 1;
                        this.clearCanvas();
                        $wire.set('approvalComment', '');
                        $wire.set('authPassword', '');
                        $wire.set('otpSent', false);
                        $wire.set('otpVerified', false);
                        $wire.set('otpInput', '');
                        $wire.set('otpError', '');
                    },

                    async confirmPassword() {
                        await $wire.verifyAuthPassword();
                        if (!$wire.__instance?.effects?.errors?.authPassword) {
                            this.goToStep2();
                        }
                    }
                }"
                class="mt-4 pt-3 border-t border-gray-100"
            >
                <p class="text-xs font-medium text-gray-700 mb-3">Tu rol tiene autorización pendiente en esta cotización:</p>
                <div class="flex flex-wrap gap-3">
                    <button type="button" @click="openModal()"
                        class="px-4 py-2 text-sm bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-medium transition flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Autorizar
                    </button>
                    <button wire:click="rejectQuotation" type="button"
                        class="px-4 py-2 text-sm border border-red-200 text-red-600 hover:bg-red-50 rounded-lg font-medium transition">
                        Rechazar
                    </button>
                </div>

                {{-- ── Modal multi-paso ── --}}
                <div
                    x-show="showModal"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4"
                    style="display: none;"
                    @keydown.escape.window="closeModal()"
                    @click.self="closeModal()"
                >
                    <div
                        x-show="showModal"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="bg-white rounded-2xl shadow-2xl w-full max-w-md"
                        @click.stop
                    >
                        {{-- Header --}}
                        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center"
                                    :class="step === 1 ? 'bg-indigo-100' : (step === 2 ? 'bg-violet-100' : 'bg-emerald-100')">
                                    {{-- Paso 1: candado --}}
                                    <svg x-show="step === 1" class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    {{-- Paso 2: escudo --}}
                                    <svg x-show="step === 2" class="w-4 h-4 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                    {{-- Paso 3: pluma --}}
                                    <svg x-show="step === 3" class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 11l6.536-6.536a2 2 0 112.828 2.828L11.828 13.828a4 4 0 01-1.414.94l-3.414.853.853-3.414a4 4 0 01.94-1.414z"/></svg>
                                </div>
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-800"
                                        x-text="step === 1 ? 'Verificar contraseña' : (step === 2 ? 'Verificación en dos pasos' : 'Firma de autorización')"></h3>
                                    <p class="text-[10px] text-gray-400"
                                        x-text="'Paso ' + step + ' de 3'"></p>
                                </div>
                            </div>
                            <button type="button" @click="closeModal()"
                                class="text-gray-400 hover:text-gray-600 transition rounded-lg p-1 hover:bg-gray-100">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        {{-- ── Paso 1: contraseña ── --}}
                        <div x-show="step === 1" class="px-5 py-5 space-y-4">
                            <p class="text-xs text-gray-500">Ingresa tu contraseña para confirmar tu identidad antes de firmar.</p>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Contraseña <span class="text-red-500">*</span></label>
                                <input
                                    wire:model="authPassword"
                                    type="password"
                                    placeholder="••••••••"
                                    @keydown.enter="confirmPassword()"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                                    autocomplete="current-password"
                                >
                                @error('authPassword')
                                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- ── Paso 2: OTP 2FA ── --}}
                        <div x-show="step === 2" class="px-5 py-5 space-y-4">
                            @if(!$otpVerified)
                                @if(!$otpSent)
                                <div class="text-center space-y-3 py-2">
                                    <div class="w-12 h-12 bg-violet-100 rounded-full flex items-center justify-center mx-auto">
                                        <svg class="w-6 h-6 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <p class="text-sm font-medium text-gray-800">Verificación en dos pasos</p>
                                    <p class="text-xs text-gray-500">
                                        Enviaremos un código de 6 dígitos a<br>
                                        <strong>{{ auth()->user()->email }}</strong>
                                    </p>
                                    <button wire:click="requestOtp" type="button"
                                        wire:loading.attr="disabled"
                                        class="inline-flex items-center gap-2 px-5 py-2 text-sm bg-violet-600 hover:bg-violet-700 text-white rounded-lg font-medium transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                        Enviar código
                                    </button>
                                </div>
                                @else
                                <div class="space-y-3">
                                    <p class="text-xs text-gray-500">
                                        Código enviado a <strong>{{ auth()->user()->email }}</strong>.
                                        Ingresa los 6 dígitos que recibiste.
                                    </p>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Código de verificación <span class="text-red-500">*</span></label>
                                        <div class="flex items-center gap-2">
                                            <input
                                                wire:model="otpInput"
                                                type="text"
                                                inputmode="numeric"
                                                maxlength="6"
                                                placeholder="000000"
                                                @keydown.enter="$wire.verifyOtp()"
                                                class="w-36 border border-gray-200 rounded-lg px-3 py-2 text-sm text-center tracking-[0.4em] font-mono focus:outline-none focus:ring-2 focus:ring-violet-300"
                                                autofocus
                                            >
                                            <button wire:click="verifyOtp" type="button"
                                                wire:loading.attr="disabled"
                                                class="px-4 py-2 text-sm bg-violet-600 hover:bg-violet-700 text-white rounded-lg font-medium transition">
                                                Verificar
                                            </button>
                                        </div>
                                        @if($otpError)
                                            <p class="text-xs text-red-500 mt-1.5">{{ $otpError }}</p>
                                        @endif
                                    </div>
                                    <button wire:click="requestOtp" type="button"
                                        class="text-xs text-violet-600 hover:underline">
                                        Reenviar código
                                    </button>
                                </div>
                                @endif
                            @else
                            <div class="text-center py-4 space-y-2">
                                <div class="w-12 h-12 bg-emerald-100 rounded-full flex items-center justify-center mx-auto">
                                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <p class="text-sm font-medium text-emerald-700">Identidad verificada</p>
                                <p class="text-xs text-gray-400">Código correcto. Puedes continuar.</p>
                            </div>
                            @endif
                        </div>

                        {{-- ── Paso 3: firma ── --}}
                        <div x-show="step === 3" class="px-5 py-4 space-y-4">

                            {{-- Selector de método (solo si hay firma guardada) --}}
                            <template x-if="hasSavedSig">
                                <div class="flex gap-2">
                                    <button type="button"
                                        @click="switchToMethod('saved')"
                                        :class="sigMethod === 'saved'
                                            ? 'bg-indigo-50 border-indigo-300 text-indigo-700 font-medium'
                                            : 'border-gray-200 text-gray-500 hover:bg-gray-50'"
                                        class="flex-1 text-xs py-2 px-3 rounded-lg border transition flex items-center justify-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Usar firma registrada
                                    </button>
                                    <button type="button"
                                        @click="switchToMethod('draw')"
                                        :class="sigMethod === 'draw'
                                            ? 'bg-indigo-50 border-indigo-300 text-indigo-700 font-medium'
                                            : 'border-gray-200 text-gray-500 hover:bg-gray-50'"
                                        class="flex-1 text-xs py-2 px-3 rounded-lg border transition flex items-center justify-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 11l6.536-6.536a2 2 0 112.828 2.828L11.828 13.828a4 4 0 01-1.414.94l-3.414.853.853-3.414a4 4 0 01.94-1.414z"/></svg>
                                        Firmar ahora
                                    </button>
                                </div>
                            </template>

                            {{-- Firma guardada --}}
                            <div x-show="sigMethod === 'saved'">
                                <p class="text-xs text-gray-500 mb-2">Se usará tu firma registrada en el sistema:</p>
                                <div class="border border-gray-200 rounded-xl overflow-hidden bg-gray-50 p-2 flex items-center justify-center" style="min-height: 80px;">
                                    @if(auth()->user()->signature)
                                        <img src="{{ auth()->user()->signature }}" alt="Firma registrada" class="max-h-20 object-contain">
                                    @endif
                                </div>
                                <p class="text-[10px] text-gray-400 mt-1">
                                    Registrada el {{ auth()->user()->signature_updated_at?->format('d/m/Y') ?? '—' }}.
                                    <a href="{{ route('users.edit', auth()->user()) }}" wire:navigate class="underline hover:text-indigo-500">Actualizar firma</a>
                                </p>
                            </div>

                            {{-- Panel de dibujo --}}
                            <div x-show="sigMethod === 'draw'">
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="text-xs font-medium text-gray-600">Dibuja tu firma <span class="text-red-500">*</span></label>
                                    <button type="button" @click="clearCanvas()"
                                        class="text-[10px] text-gray-400 hover:text-red-500 underline transition">Limpiar</button>
                                </div>
                                <div class="border-2 border-dashed border-gray-300 rounded-xl overflow-hidden bg-gray-50 cursor-crosshair select-none hover:border-gray-400 transition"
                                     style="touch-action: none;">
                                    <canvas x-ref="sigCanvas" width="800" height="160"
                                        class="w-full block"
                                        style="touch-action: none;"></canvas>
                                </div>
                                <p class="text-[10px] text-gray-400 mt-1">Traza tu firma con el ratón o dedo.</p>
                            </div>

                            {{-- Comentario --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Comentario <span class="text-gray-400">(opcional)</span></label>
                                <textarea wire:model="approvalComment" rows="2"
                                    placeholder="Agrega un comentario sobre tu autorización..."
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-300 resize-none"></textarea>
                            </div>
                        </div>

                        {{-- Footer --}}
                        <div class="flex items-center justify-between gap-3 px-5 py-4 border-t border-gray-100 bg-gray-50 rounded-b-2xl">
                            <button type="button"
                                @click="step === 1 ? closeModal() : (step--)"
                                class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg font-medium transition"
                                x-text="step === 1 ? 'Cancelar' : '← Atrás'">
                            </button>

                            {{-- Paso 1: verificar contraseña --}}
                            <button x-show="step === 1"
                                type="button"
                                @click="confirmPassword()"
                                wire:loading.attr="disabled"
                                class="px-5 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                Verificar y continuar
                            </button>

                            {{-- Paso 2: continuar (solo activo tras OTP verificado) --}}
                            <button x-show="step === 2"
                                type="button"
                                @click="goToStep3()"
                                :disabled="!$wire.otpVerified"
                                :class="$wire.otpVerified
                                    ? 'bg-violet-600 hover:bg-violet-700 text-white'
                                    : 'bg-gray-200 text-gray-400 cursor-not-allowed'"
                                class="px-5 py-2 text-sm rounded-lg font-medium transition flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                Continuar
                            </button>

                            {{-- Paso 3: confirmar autorización --}}
                            <button x-show="step === 3"
                                type="button"
                                @click="closeModal(); $wire.approveQuotation();"
                                class="px-5 py-2 text-sm bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-medium transition flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Confirmar autorización
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endif {{-- canApprove --}}
        </div>
        @endif
    </div>
    @endif

    {{-- ── COMPRAS: requisición autorizada → crear OC ── --}}
    @if($requisition->status === 'authorized' && $this->isComprador)
    <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <p class="text-sm font-semibold text-emerald-800">¡Requisición autorizada!</p>
            <p class="text-xs text-emerald-600 mt-0.5">Ya puedes generar la orden de compra a partir de la cotización final aprobada.</p>
        </div>
        <a href="{{ route('purchases.orders.create', ['quotation' => $requisition->finalQuotation->id]) }}" wire:navigate
            class="inline-flex items-center gap-2 px-4 py-2 text-sm bg-emerald-700 hover:bg-emerald-800 text-white rounded-lg font-medium transition whitespace-nowrap self-start sm:self-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Crear orden de compra
        </a>
    </div>
    @endif

    {{-- ── Orden de compra generada ── --}}
    @if($requisition->status === 'ordered' && $requisition->order)
    <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <p class="text-sm font-medium text-emerald-800">Orden de compra generada</p>
            <p class="text-xs text-emerald-600 mt-0.5">Folio: {{ $requisition->order->folio }}</p>
        </div>
        <a href="{{ route('purchases.orders.show', $requisition->order) }}" wire:navigate
            class="px-4 py-2 text-sm bg-emerald-700 hover:bg-emerald-800 text-white rounded-lg transition whitespace-nowrap self-start sm:self-auto">
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
        <div class="bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 text-sm text-emerald-700">
            ¡Tu requisición fue aprobada en evaluación! El área de compras generará la orden de compra a la brevedad.
        </div>
        @endif
    @endif

</div>
